<?php

declare(strict_types=1);

use App\Models\Setting;
use Livewire\Component;

return new class extends Component
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $nextDeparture = null;

    /**
     * @var array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null
     */
    public ?array $schoolLunch = null;

    public bool $showWeatherWidget = true;

    public string $weatherWidgetSize = 'medium';

    public string $weatherUnits = 'fahrenheit';

    public bool $weatherShowFeelsLike = true;

    public bool $weatherShowPrecipitationAlerts = true;

    /**
     * @var array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    public ?array $weatherLocation = null;

    /**
     * @param  array<string, mixed>|null  $nextDeparture
     * @param  array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null  $schoolLunch
     */
    public function mount(?array $nextDeparture = null, ?array $schoolLunch = null): void
    {
        if ($nextDeparture !== null) {
            $this->nextDeparture = $nextDeparture;
        }

        if ($schoolLunch !== null) {
            $this->schoolLunch = $schoolLunch;
        }

        $this->showWeatherWidget = $this->booleanSetting('weather.enabled', true);
        $this->weatherWidgetSize = $this->normalizeSize($this->stringSetting('weather.widget_size', 'medium'));
        $this->weatherUnits = $this->normalizeUnits($this->stringSetting('weather.units', 'fahrenheit'));
        $this->weatherShowFeelsLike = $this->booleanSetting('weather.show_feels_like', true);
        $this->weatherShowPrecipitationAlerts = $this->booleanSetting('weather.precipitation_alerts', true);
        $this->weatherLocation = $this->locationSetting();
    }

    private function normalizeSize(string $size): string
    {
        $normalized = strtolower(trim($size));

        if (in_array($normalized, ['compact', 'medium', 'large'], true)) {
            return $normalized;
        }

        return 'medium';
    }

    private function normalizeUnits(string $units): string
    {
        return strtolower(trim($units)) === 'celsius' ? 'celsius' : 'fahrenheit';
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

    /**
     * @return array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    private function locationSetting(): ?array
    {
        $value = Setting::get('weather.location');

        if (! is_array($value)) {
            return null;
        }

        $name = $this->cleanString($value['name'] ?? null);
        $admin1 = $this->cleanString($value['admin1'] ?? null);
        $country = $this->cleanString($value['country'] ?? null);
        $timezone = $this->cleanString($value['timezone'] ?? null);
        $label = $this->cleanString($value['label'] ?? null);
        $latitude = $value['latitude'] ?? null;
        $longitude = $value['longitude'] ?? null;

        if (
            $name === null
            || ! is_numeric($latitude)
            || ! is_numeric($longitude)
        ) {
            return null;
        }

        if ($label === null) {
            /** @var list<string> $parts */
            $parts = array_values(array_filter([$name, $admin1, $country], function (mixed $part): bool {
                return is_string($part) && $part !== '';
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

@php
    $headerColumns = $showWeatherWidget ? 'md:grid-cols-3' : 'md:grid-cols-2';
@endphp

<div class="space-y-4">
    <div class="grid gap-4 {{ $headerColumns }}">
        {{-- Clock Panel --}}
        <div class="flex items-center gap-4 rounded-2xl border border-dash-border bg-dash-card p-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
                <flux:icon name="clock" variant="outline" class="size-6" />
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Right now</p>
                <p class="text-3xl font-extrabold text-slate-100 md:text-4xl" x-data="DashboardTime.clock()" x-text="time"></p>
            </div>
        </div>

        {{-- Departure Countdown Panel --}}
        <div class="flex flex-col justify-center rounded-2xl border border-dash-border bg-dash-card p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Next departure</p>
            @if ($nextDeparture)
                <div class="mt-2 flex items-center gap-2">
                    <flux:icon name="truck" variant="outline" class="size-5 text-amber-400" />
                    <span class="text-lg font-extrabold text-slate-100">{{ $nextDeparture['label'] }}</span>
                </div>
                @if (count($nextDeparture['labels']) > 1)
                    <div class="mt-1 text-sm text-slate-400">
                        {{ implode(' · ', $nextDeparture['labels']) }}
                    </div>
                @endif
                <div
                    x-data="DashboardTime.countdown({{ $nextDeparture['timestamp']->timestamp }})"
                    :class="urgencyClass"
                    class="mt-2 text-4xl font-black md:text-5xl"
                    x-text="timeLeft"
                ></div>
            @else
                <div class="mt-2 text-lg text-slate-400">No departures scheduled.</div>
            @endif
        </div>

        @if ($showWeatherWidget)
            <livewire:weather-widget
                :size="$weatherWidgetSize"
                :units="$weatherUnits"
                :show-feels-like="$weatherShowFeelsLike"
                :show-precipitation-alerts="$weatherShowPrecipitationAlerts"
                :location="$weatherLocation"
                :enabled="$showWeatherWidget"
                wire:key="weather-widget-header"
            />
        @endif
    </div>

    @if ($schoolLunch !== null)
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-900/15 px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar-days" variant="outline" class="size-5 text-emerald-300" />
                    <div class="text-sm font-bold text-emerald-200">School lunch · {{ $schoolLunch['date_label'] }}</div>
                </div>
                <flux:badge size="sm" color="lime">School day</flux:badge>
            </div>
            <div class="mt-1 text-xs text-emerald-100/80">{{ $schoolLunch['menu_name'] }}</div>
            <div class="mt-2 text-sm text-emerald-50">
                {{ implode(' · ', $schoolLunch['items']) }}
            </div>
        </div>
    @endif
</div>
