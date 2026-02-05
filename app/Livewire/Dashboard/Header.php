<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\SettingService;
use Livewire\Component;

class Header extends Component
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $nextDeparture = null;

    public bool $weatherEnabled = true;

    public string $weatherSize = 'medium';

    public string $weatherUnits = 'fahrenheit';

    public bool $weatherShowFeelsLike = true;

    public bool $weatherPrecipitationAlerts = true;

    /**
     * @var array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}|null
     */
    public ?array $weatherLocation = null;

    public function mount(): void
    {
        $settings = app(SettingService::class);

        $this->weatherEnabled = $this->booleanSetting($settings, 'weather.enabled', true);
        $this->weatherSize = $this->normalizeSize((string) $settings->get('weather.widget_size', 'medium'));
        $this->weatherUnits = $this->normalizeUnits((string) $settings->get('weather.units', 'fahrenheit'));
        $this->weatherShowFeelsLike = $this->booleanSetting($settings, 'weather.show_feels_like', true);
        $this->weatherPrecipitationAlerts = $this->booleanSetting($settings, 'weather.precipitation_alerts', true);

        $location = $settings->get('weather.location');
        $this->weatherLocation = is_array($location) ? $location : null;
    }

    public function render(): mixed
    {
        return view('livewire.dashboard.header');
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
}
