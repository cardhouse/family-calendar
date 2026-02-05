<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\SettingService;
use App\Services\WeatherService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Weather extends Component
{
    public string $search = '';

    /**
     * @var list<array{name: string, latitude: float, longitude: float, timezone: string, country: string, admin1: ?string}>
     */
    public array $searchResults = [];

    /**
     * @var array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}|null
     */
    public ?array $selectedLocation = null;

    public string $units = 'fahrenheit';

    public string $widgetSize = 'medium';

    public bool $showFeelsLike = true;

    public bool $precipitationAlerts = true;

    public bool $widgetEnabled = true;

    public string $statusMessage = '';

    public function mount(): void
    {
        $settings = app(SettingService::class);

        $this->selectedLocation = $this->normalizeLocation($settings->get('weather.location'));
        $this->units = $this->normalizeUnits((string) $settings->get('weather.units', 'fahrenheit'));
        $this->widgetSize = $this->normalizeWidgetSize((string) $settings->get('weather.widget_size', 'medium'));
        $this->showFeelsLike = $this->booleanSetting($settings, 'weather.show_feels_like', true);
        $this->precipitationAlerts = $this->booleanSetting($settings, 'weather.precipitation_alerts', true);
        $this->widgetEnabled = $this->booleanSetting($settings, 'weather.enabled', true);
    }

    public function updatedSearch(string $value): void
    {
        if (strlen(trim($value)) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = app(WeatherService::class)->searchLocation($value);
    }

    public function selectLocation(int $index): void
    {
        $location = $this->searchResults[$index] ?? null;

        if (! is_array($location)) {
            return;
        }

        $this->selectedLocation = $location;
        $this->search = $this->locationLabel($location);
        $this->searchResults = [];
    }

    public function save(): void
    {
        $validated = $this->validate([
            'units' => ['required', 'in:fahrenheit,celsius'],
            'widgetSize' => ['required', 'in:compact,medium,large'],
            'showFeelsLike' => ['boolean'],
            'precipitationAlerts' => ['boolean'],
            'widgetEnabled' => ['boolean'],
        ]);

        if ($validated['widgetEnabled'] && ! $this->hasLocationCoordinates($this->selectedLocation)) {
            $this->addError('selectedLocation', 'Choose a location before enabling the weather widget.');

            return;
        }

        $settings = app(SettingService::class);
        $settings->set('weather.location', $this->selectedLocation);
        $settings->set('weather.units', $validated['units']);
        $settings->set('weather.widget_size', $validated['widgetSize']);
        $settings->set('weather.show_feels_like', $validated['showFeelsLike']);
        $settings->set('weather.precipitation_alerts', $validated['precipitationAlerts']);
        $settings->set('weather.enabled', $validated['widgetEnabled']);

        $this->statusMessage = 'Weather settings saved.';
    }

    public function render(): mixed
    {
        return view('livewire.admin.weather');
    }

    private function normalizeUnits(string $units): string
    {
        return $units === 'celsius' ? 'celsius' : 'fahrenheit';
    }

    private function normalizeWidgetSize(string $size): string
    {
        return in_array($size, ['compact', 'medium', 'large'], true) ? $size : 'medium';
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

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}|null  $location
     */
    private function hasLocationCoordinates(?array $location): bool
    {
        return is_array($location)
            && is_numeric($location['latitude'] ?? null)
            && is_numeric($location['longitude'] ?? null);
    }

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}  $location
     */
    private function locationLabel(array $location): string
    {
        $parts = array_filter([
            $location['name'] ?? null,
            $location['admin1'] ?? null,
            $location['country'] ?? null,
        ], fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        return $parts === [] ? 'Saved location' : implode(', ', $parts);
    }
}
