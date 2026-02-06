<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\WeatherService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::admin')]
    public string $locationQuery = '';

    /**
     * @var list<array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}>
     */
    public array $searchResults = [];

    /**
     * @var array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    public ?array $selectedLocation = null;

    public bool $enabled = true;

    public string $units = 'fahrenheit';

    public string $widgetSize = 'medium';

    public bool $showFeelsLike = true;

    public bool $showPrecipitationAlerts = true;

    /**
     * @var list<array{value: string, label: string}>
     */
    public array $unitOptions = [
        ['value' => 'fahrenheit', 'label' => 'Fahrenheit'],
        ['value' => 'celsius', 'label' => 'Celsius'],
    ];

    /**
     * @var list<array{value: string, label: string}>
     */
    public array $sizeOptions = [
        ['value' => 'compact', 'label' => 'Compact'],
        ['value' => 'medium', 'label' => 'Medium'],
        ['value' => 'large', 'label' => 'Large'],
    ];

    public bool $saved = false;

    public function mount(): void
    {
        $this->enabled = $this->booleanSetting('weather.enabled', true);
        $this->units = $this->normalizeUnits($this->stringSetting('weather.units', 'fahrenheit'));
        $this->widgetSize = $this->normalizeSize($this->stringSetting('weather.widget_size', 'medium'));
        $this->showFeelsLike = $this->booleanSetting('weather.show_feels_like', true);
        $this->showPrecipitationAlerts = $this->booleanSetting('weather.precipitation_alerts', true);
        $this->selectedLocation = $this->locationSetting();
        $this->locationQuery = $this->selectedLocation['label'] ?? '';
    }

    public function updatedLocationQuery(): void
    {
        $this->saved = false;

        if (
            $this->selectedLocation !== null
            && $this->locationQuery !== $this->selectedLocation['label']
        ) {
            $this->selectedLocation = null;
        }

        $query = trim($this->locationQuery);

        if (mb_strlen($query) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = app(WeatherService::class)->searchLocation($query);
    }

    public function updatedUnits(): void
    {
        $this->saved = false;
    }

    public function updatedWidgetSize(): void
    {
        $this->saved = false;
    }

    public function updatedEnabled(): void
    {
        $this->saved = false;
    }

    public function updatedShowFeelsLike(): void
    {
        $this->saved = false;
    }

    public function updatedShowPrecipitationAlerts(): void
    {
        $this->saved = false;
    }

    public function selectLocation(int $index): void
    {
        $candidate = $this->searchResults[$index] ?? null;

        if (! is_array($candidate)) {
            return;
        }

        $location = $this->normalizeLocation($candidate);

        if ($location === null) {
            return;
        }

        $this->selectedLocation = $location;
        $this->locationQuery = $location['label'];
        $this->searchResults = [];
        $this->saved = false;
        $this->resetErrorBag('locationQuery');
    }

    public function clearLocation(): void
    {
        $this->selectedLocation = null;
        $this->locationQuery = '';
        $this->searchResults = [];
        $this->saved = false;
    }

    public function save(): void
    {
        $this->saved = false;

        $validated = $this->validate([
            'enabled' => ['boolean'],
            'units' => ['required', Rule::in(['fahrenheit', 'celsius'])],
            'widgetSize' => ['required', Rule::in(['compact', 'medium', 'large'])],
            'showFeelsLike' => ['boolean'],
            'showPrecipitationAlerts' => ['boolean'],
        ]);

        $location = $this->normalizeLocation($this->selectedLocation);

        if ($validated['enabled'] && $location === null) {
            $this->addError('locationQuery', 'Select a location from the search results before saving.');

            return;
        }

        Setting::set('weather.enabled', $validated['enabled']);
        Setting::set('weather.location', $location);
        Setting::set('weather.units', $validated['units']);
        Setting::set('weather.widget_size', $validated['widgetSize']);
        Setting::set('weather.show_feels_like', $validated['showFeelsLike']);
        Setting::set('weather.precipitation_alerts', $validated['showPrecipitationAlerts']);

        $this->selectedLocation = $location;
        $this->locationQuery = $location['label'] ?? '';
        $this->searchResults = [];
        $this->saved = true;
    }

    private function normalizeUnits(string $units): string
    {
        return $units === 'celsius' ? 'celsius' : 'fahrenheit';
    }

    private function normalizeSize(string $size): string
    {
        if (in_array($size, ['compact', 'medium', 'large'], true)) {
            return $size;
        }

        return 'medium';
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

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="cloud" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Weather</flux:heading>
                <flux:text>Configure location, widget size, and forecast display options.</flux:text>
            </div>
        </div>
    </div>

    <flux:callout icon="cloud">
        <flux:callout.heading>Open-Meteo Forecast</flux:callout.heading>
        <flux:callout.text>
            Search for a location, choose your preferred temperature unit, and decide how much weather detail should be shown on the dashboard.
        </flux:callout.text>
    </flux:callout>

    <div class="rounded-2xl border border-slate-200/80 dark:border-zinc-700 bg-white dark:bg-zinc-900/70 px-6 py-5 shadow-sm">
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="space-y-3">
                <flux:input
                    wire:model.live.debounce.500ms="locationQuery"
                    label="Location"
                    placeholder="Search for city, town, or region"
                    description="Type at least two characters, then choose one of the search results."
                />
                <flux:error name="locationQuery" />

                @if ($searchResults !== [])
                    <div class="max-h-60 overflow-y-auto rounded-xl border border-slate-200 dark:border-zinc-700">
                        @foreach ($searchResults as $index => $result)
                            <button
                                type="button"
                                wire:key="weather-location-{{ $index }}"
                                wire:click="selectLocation({{ $index }})"
                                class="flex w-full items-center justify-between px-3 py-2 text-left transition hover:bg-slate-50 dark:hover:bg-zinc-800"
                            >
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $result['name'] }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $result['admin1'] ?? $result['country'] ?? 'Unknown region' }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if ($selectedLocation !== null)
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" color="sky">{{ $selectedLocation['label'] }}</flux:badge>
                        <flux:button type="button" size="xs" variant="subtle" wire:click="clearLocation">Clear</flux:button>
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:select wire:model="units" label="Temperature units">
                    @foreach ($unitOptions as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="widgetSize" label="Widget size">
                    @foreach ($sizeOptions as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="space-y-3 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/60 dark:bg-zinc-900/50 p-4">
                <flux:field variant="inline">
                    <flux:label>Show weather widget</flux:label>
                    <flux:switch wire:model="enabled" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:label>Show feels-like temperature</flux:label>
                    <flux:switch wire:model="showFeelsLike" />
                </flux:field>

                <flux:field variant="inline">
                    <flux:label>Show precipitation alerts</flux:label>
                    <flux:switch wire:model="showPrecipitationAlerts" />
                </flux:field>
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">Save weather settings</flux:button>
                @if ($saved)
                    <flux:text class="text-sm text-emerald-600">Saved.</flux:text>
                @endif
            </div>
        </form>
    </div>

    <section class="space-y-3 rounded-2xl border border-slate-200/80 dark:border-zinc-700 bg-white dark:bg-zinc-900/70 px-6 py-5 shadow-sm">
        <div>
            <flux:heading size="lg">Preview</flux:heading>
            <flux:text class="mt-1">This reflects your current weather settings selection.</flux:text>
        </div>

        @if ($enabled)
            <livewire:weather-widget
                :size="$widgetSize"
                :units="$units"
                :show-feels-like="$showFeelsLike"
                :show-precipitation-alerts="$showPrecipitationAlerts"
                :location="$selectedLocation"
                :enabled="$enabled"
                wire:key="weather-preview-{{ $widgetSize }}-{{ $units }}-{{ (int) $showFeelsLike }}-{{ (int) $showPrecipitationAlerts }}-{{ (int) $enabled }}"
            />
        @else
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900/50 px-4 py-4 text-sm text-slate-500 dark:text-slate-400">
                Widget is disabled. Enable it to show weather on the dashboard.
            </div>
        @endif
    </section>
</div>
