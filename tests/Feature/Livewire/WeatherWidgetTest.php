<?php

declare(strict_types=1);

use App\Livewire\WeatherWidget;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders weather data for each widget size', function (string $size) {
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

    $location = [
        'name' => 'Austin',
        'admin1' => 'Texas',
        'country' => 'United States',
        'latitude' => 30.2672,
        'longitude' => -97.7431,
        'timezone' => 'America/Chicago',
    ];

    Livewire::test(WeatherWidget::class, [
        'size' => $size,
        'location' => $location,
        'units' => 'fahrenheit',
        'showFeelsLike' => true,
        'showPrecipitationAlerts' => true,
    ])
        ->assertSet('size', $size)
        ->assertSee('Cloudy')
        ->assertSee('Austin')
        ->assertSee('Feels')
        ->assertSee('Precipitation now')
        ->assertSee('wire:poll.900s', false);
})->with([
    'compact' => 'compact',
    'medium' => 'medium',
    'large' => 'large',
]);

it('renders fallback when weather cannot be loaded', function () {
    Livewire::test(WeatherWidget::class, [
        'location' => null,
    ])
        ->assertSee('Weather unavailable')
        ->assertSee('Choose a location in Admin Weather');
});
