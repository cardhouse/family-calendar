<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Header;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders clock and departure countdown', function () {
    $nextDeparture = [
        'timestamp' => now()->addMinutes(45),
        'label' => 'School Run',
        'labels' => ['School Run'],
        'assignments' => collect(),
    ];

    Livewire::test(Header::class, ['nextDeparture' => $nextDeparture])
        ->assertSee('School Run')
        ->assertSee('DashboardTime.clock()', false)
        ->assertSee('DashboardTime.countdown', false);
});

it('renders the weather widget when weather is enabled', function () {
    Setting::set('weather.enabled', true);
    Setting::set('weather.location', [
        'name' => 'Austin',
        'admin1' => 'Texas',
        'country' => 'United States',
        'latitude' => 30.2672,
        'longitude' => -97.7431,
        'timezone' => 'America/Chicago',
    ]);
    Setting::set('weather.units', 'fahrenheit');
    Setting::set('weather.widget_size', 'compact');
    Setting::set('weather.show_feels_like', true);
    Setting::set('weather.precipitation_alerts', true);

    Http::fake([
        'api.open-meteo.com/*' => Http::response([
            'current' => [
                'temperature_2m' => 67.9,
                'apparent_temperature' => 65.1,
                'precipitation' => 0.4,
                'weather_code' => 2,
                'is_day' => 1,
            ],
        ]),
    ]);

    Livewire::test(Header::class)
        ->assertSee('Cloudy')
        ->assertSee('Austin');
});
