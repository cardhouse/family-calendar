<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\WeatherService;
use Illuminate\Support\Carbon;
use Livewire\Component;

return new class extends Component
{
    public string $size = 'medium';

    public string $units = 'fahrenheit';

    public bool $showFeelsLike = true;

    public bool $showPrecipitationAlerts = true;

    public bool $enabled = true;

    /**
     * @var array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    public ?array $location = null;

    /**
     * @var array{temperature: float, feels_like: float, precipitation: float|null, precipitation_unit: string, condition: string, weather_code: int, is_day: bool, units: string, unit_symbol: string, fetched_at: string, location_label: string}|null
     */
    public ?array $weather = null;

    public string $unavailableMessage = 'Weather is unavailable right now.';

    /**
     * @param  array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null  $location
     */
    public function mount(
        ?string $size = null,
        ?string $units = null,
        ?bool $showFeelsLike = null,
        ?bool $showPrecipitationAlerts = null,
        ?array $location = null,
        ?bool $enabled = null
    ): void {
        $this->size = $this->normalizeSize($size ?? $this->stringSetting('weather.widget_size', 'medium'));
        $this->units = $this->normalizeUnits($units ?? $this->stringSetting('weather.units', 'fahrenheit'));
        $this->showFeelsLike = $showFeelsLike ?? $this->booleanSetting('weather.show_feels_like', true);
        $this->showPrecipitationAlerts = $showPrecipitationAlerts ?? $this->booleanSetting('weather.precipitation_alerts', true);
        $this->enabled = $enabled ?? $this->booleanSetting('weather.enabled', true);
        $this->location = $location ?? $this->locationSetting();

        $this->refreshWeather();
    }

    public function refreshWeather(): void
    {
        if (! $this->enabled) {
            $this->weather = null;
            $this->unavailableMessage = 'Weather widget disabled in admin settings.';

            return;
        }

        $location = $this->normalizeLocation($this->location);

        if ($location === null) {
            $this->weather = null;
            $this->unavailableMessage = 'Set a location in admin weather settings.';

            return;
        }

        $this->weather = app(WeatherService::class)->getCurrentWeatherForLocation($location, $this->units);

        if ($this->weather === null) {
            $this->unavailableMessage = 'Unable to load weather right now. Showing this fallback until the next refresh.';
        }
    }

    public function panelClasses(): string
    {
        return match ($this->size) {
            'compact' => 'rounded-2xl border border-dash-border bg-dash-card p-4',
            'large' => 'rounded-2xl border border-dash-border bg-dash-card p-6',
            default => 'rounded-2xl border border-dash-border bg-dash-card p-5',
        };
    }

    public function weatherIconName(): string
    {
        if ($this->weather === null) {
            return 'cloud';
        }

        $weatherCode = $this->weather['weather_code'];

        if (in_array($weatherCode, [95, 96, 99], true)) {
            return 'bolt';
        }

        if (in_array($weatherCode, [71, 73, 75, 77, 85, 86], true)) {
            return 'cloud-snow';
        }

        if (in_array($weatherCode, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82], true)) {
            return 'cloud-rain';
        }

        if (in_array($weatherCode, [0, 1, 2], true)) {
            return $this->weather['is_day'] ? 'sun' : 'moon';
        }

        return 'cloud';
    }

    public function temperatureText(): string
    {
        if ($this->weather === null) {
            return '--';
        }

        return sprintf('%d %s', (int) round($this->weather['temperature']), $this->temperatureUnitLabel());
    }

    public function feelsLikeText(): string
    {
        if ($this->weather === null) {
            return '--';
        }

        return sprintf('%d %s', (int) round($this->weather['feels_like']), $this->temperatureUnitLabel());
    }

    public function locationLabel(): string
    {
        if ($this->weather !== null && is_string($this->weather['location_label'] ?? null)) {
            return $this->weather['location_label'];
        }

        $location = $this->normalizeLocation($this->location);

        return $location['label'] ?? '';
    }

    public function updatedAtText(): ?string
    {
        if (! is_string($this->weather['fetched_at'] ?? null)) {
            return null;
        }

        return Carbon::parse($this->weather['fetched_at'])->diffForHumans();
    }

    public function temperatureUnitLabel(): string
    {
        if ($this->weather !== null && is_string($this->weather['unit_symbol'] ?? null)) {
            return $this->weather['unit_symbol'];
        }

        return $this->units === 'celsius' ? 'C' : 'F';
    }

    /**
     * @param  array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null  $location
     * @return array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    private function normalizeLocation(?array $location): ?array
    {
        if ($location === null) {
            return null;
        }

        $name = $this->cleanString($location['name'] ?? null);
        $admin1 = $this->cleanString($location['admin1'] ?? null);
        $country = $this->cleanString($location['country'] ?? null);
        $timezone = $this->cleanString($location['timezone'] ?? null);
        $latitude = $location['latitude'] ?? null;
        $longitude = $location['longitude'] ?? null;
        $label = $this->cleanString($location['label'] ?? null);

        if (
            $name === null
            || ! is_numeric($latitude)
            || ! is_numeric($longitude)
        ) {
            return null;
        }

        if ($label === null) {
            /** @var list<string> $parts */
            $parts = array_values(array_filter([$name, $admin1, $country], function (mixed $value): bool {
                return is_string($value) && $value !== '';
            }));

            $label = implode(', ', array_unique($parts));
        }

        return [
            'name' => $name,
            'admin1' => $admin1,
            'country' => $country,
            'latitude' => round((float) $latitude, 4),
            'longitude' => round((float) $longitude, 4),
            'timezone' => $timezone,
            'label' => $label,
        ];
    }

    /**
     * @return array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    private function locationSetting(): ?array
    {
        $location = Setting::get('weather.location');

        if (! is_array($location)) {
            return null;
        }

        return $this->normalizeLocation($location);
    }

    private function normalizeUnits(string $units): string
    {
        $normalized = strtolower(trim($units));

        return $normalized === 'celsius' ? 'celsius' : 'fahrenheit';
    }

    private function normalizeSize(string $size): string
    {
        $normalized = strtolower(trim($size));

        if (in_array($normalized, ['compact', 'medium', 'large'], true)) {
            return $normalized;
        }

        return 'medium';
    }

    private function booleanSetting(string $key, bool $default): bool
    {
        $value = Setting::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return $default;
    }

    private function stringSetting(string $key, string $default): string
    {
        $value = Setting::get($key, $default);

        if (! is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? $default : $trimmed;
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
};
?>

<div wire:poll.900s="refreshWeather" data-size="{{ $size }}" class="{{ $this->panelClasses() }}">
    <div class="flex items-start gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
            <flux:icon name="{{ $this->weatherIconName() }}" variant="outline" class="size-6" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Weather</p>
            @if ($weather !== null)
                <div class="mt-1 flex flex-wrap items-end gap-2">
                    <p class="{{ $size === 'compact' ? 'text-2xl' : ($size === 'large' ? 'text-4xl' : 'text-3xl') }} font-extrabold text-slate-100">
                        {{ $this->temperatureText() }}
                    </p>
                    <p class="text-sm font-semibold text-slate-300">{{ $weather['condition'] }}</p>
                </div>
                @if ($showFeelsLike && $size !== 'compact')
                    <p class="mt-1 text-xs text-slate-400">Feels like {{ $this->feelsLikeText() }}</p>
                @endif
                @if ($size !== 'compact' && $this->locationLabel() !== '')
                    <p class="mt-1 text-xs text-slate-400">{{ $this->locationLabel() }}</p>
                @endif
                @if ($size !== 'compact' && $this->updatedAtText() !== null)
                    <p class="mt-1 text-xs text-slate-500">Updated {{ $this->updatedAtText() }}</p>
                @endif
                @if ($showPrecipitationAlerts && $weather['precipitation'] !== null && $weather['precipitation'] > 0)
                    <p class="mt-1 text-xs font-semibold text-sky-300">
                        Precipitation {{ rtrim(rtrim(number_format($weather['precipitation'], 2), '0'), '.') }} {{ $weather['precipitation_unit'] }}
                    </p>
                @endif
            @else
                <p class="mt-1 text-sm text-slate-400">{{ $unavailableMessage }}</p>
                @if ($size === 'large' && $this->locationLabel() !== '')
                    <p class="mt-2 text-xs text-slate-500">{{ $this->locationLabel() }}</p>
                @endif
            @endif
        </div>
    </div>
</div>
