<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\SettingService;
use App\Services\WeatherService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WeatherWidget extends Component
{
    public string $size = 'medium';

    public string $units = 'fahrenheit';

    public bool $showFeelsLike = true;

    public bool $showPrecipitationAlerts = false;

    /**
     * @var array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}|null
     */
    public ?array $location = null;

    /**
     * @var array{
     *     condition: string,
     *     weather_code: int,
     *     is_day: bool,
     *     temperature: float,
     *     feels_like: ?float,
     *     precipitation: ?float,
     *     unit_symbol: string,
     *     location_label: string,
     *     fetched_at: string
     * }|null
     */
    public ?array $weather = null;

    public function mount(
        ?string $size = null,
        ?string $units = null,
        ?bool $showFeelsLike = null,
        ?bool $showPrecipitationAlerts = null,
        ?array $location = null
    ): void {
        $settings = app(SettingService::class);

        $this->size = $this->normalizeSize($size ?? (string) $settings->get('weather.widget_size', 'medium'));
        $this->units = $this->normalizeUnits($units ?? (string) $settings->get('weather.units', 'fahrenheit'));
        $this->showFeelsLike = $showFeelsLike ?? $this->booleanSetting($settings, 'weather.show_feels_like', true);
        $this->showPrecipitationAlerts = $showPrecipitationAlerts
            ?? $this->booleanSetting($settings, 'weather.precipitation_alerts', true);
        $this->location = $location ?? $this->normalizeLocation($settings->get('weather.location'));

        $this->refreshWeather();
    }

    public function refreshWeather(): void
    {
        if (! $this->hasLocationCoordinates()) {
            $this->weather = null;

            return;
        }

        $this->weather = app(WeatherService::class)->getCurrentWeather(
            $this->location ?? [],
            $this->units
        );
    }

    #[Computed]
    public function isCompact(): bool
    {
        return $this->size === 'compact';
    }

    #[Computed]
    public function isLarge(): bool
    {
        return $this->size === 'large';
    }

    public function render(): mixed
    {
        return view('livewire.weather-widget');
    }

    private function normalizeSize(string $size): string
    {
        return in_array($size, ['compact', 'medium', 'large'], true) ? $size : 'medium';
    }

    private function normalizeUnits(string $units): string
    {
        return $units === 'celsius' ? 'celsius' : 'fahrenheit';
    }

    private function booleanSetting(SettingService $settings, string $key, bool $default): bool
    {
        $value = $settings->get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $normalized ?? $default;
        }

        return (bool) $value;
    }

    /**
     * @return array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}|null
     */
    private function normalizeLocation(mixed $location): ?array
    {
        return is_array($location) ? $location : null;
    }

    private function hasLocationCoordinates(): bool
    {
        return is_array($this->location)
            && is_numeric($this->location['latitude'] ?? null)
            && is_numeric($this->location['longitude'] ?? null);
    }
}
