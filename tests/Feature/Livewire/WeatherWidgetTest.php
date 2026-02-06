<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders the compact weather widget variant', function () {
    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([
            'current_units' => [
                'temperature_2m' => 'F',
                'apparent_temperature' => 'F',
                'precipitation' => 'in',
            ],
            'current' => [
                'temperature_2m' => 72.0,
                'apparent_temperature' => 70.0,
                'weather_code' => 0,
                'is_day' => 1,
                'precipitation' => 0.0,
            ],
        ], 200),
    ]);

    Livewire::test('weather-widget', [
        'size' => 'compact',
        'units' => 'fahrenheit',
        'showFeelsLike' => true,
        'showPrecipitationAlerts' => true,
        'enabled' => true,
        'location' => [
            'name' => 'Denver',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'latitude' => 39.7392,
            'longitude' => -104.9903,
            'timezone' => 'America/Denver',
            'label' => 'Denver, Colorado, United States',
        ],
    ])
        ->assertSee('data-size="compact"', false)
        ->assertSee('wire:poll.900s', false)
        ->assertSee('72 F')
        ->assertSee('Clear sky')
        ->assertDontSee('Feels like');
});

it('renders medium and large variants with matching details', function () {
    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([
            'current_units' => [
                'temperature_2m' => 'C',
                'apparent_temperature' => 'C',
                'precipitation' => 'mm',
            ],
            'current' => [
                'temperature_2m' => 18.4,
                'apparent_temperature' => 17.5,
                'weather_code' => 3,
                'is_day' => 1,
                'precipitation' => 1.6,
            ],
        ], 200),
    ]);

    Livewire::test('weather-widget', [
        'size' => 'medium',
        'units' => 'celsius',
        'showFeelsLike' => true,
        'showPrecipitationAlerts' => true,
        'enabled' => true,
        'location' => [
            'name' => 'Boulder',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'latitude' => 40.015,
            'longitude' => -105.2705,
            'timezone' => 'America/Denver',
            'label' => 'Boulder, Colorado, United States',
        ],
    ])
        ->assertSee('data-size="medium"', false)
        ->assertSee('18 C')
        ->assertSee('Feels like 18 C')
        ->assertSee('Precipitation 1.6 mm');

    Livewire::test('weather-widget', [
        'size' => 'large',
        'units' => 'celsius',
        'showFeelsLike' => true,
        'showPrecipitationAlerts' => true,
        'enabled' => true,
        'location' => [
            'name' => 'Boulder',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'latitude' => 40.015,
            'longitude' => -105.2705,
            'timezone' => 'America/Denver',
            'label' => 'Boulder, Colorado, United States',
        ],
    ])
        ->assertSee('data-size="large"', false)
        ->assertSee('Boulder, Colorado, United States');
});

it('renders a fallback state when no location is configured', function () {
    Livewire::test('weather-widget', [
        'enabled' => true,
        'location' => null,
    ])
        ->assertSee('Set a location in admin weather settings.');
});
