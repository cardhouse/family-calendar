<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('persists weather settings from the admin weather screen', function () {
    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([
            'current_units' => [
                'temperature_2m' => 'C',
                'apparent_temperature' => 'C',
                'precipitation' => 'mm',
            ],
            'current' => [
                'temperature_2m' => 18.3,
                'apparent_temperature' => 17.6,
                'weather_code' => 2,
                'is_day' => 1,
                'precipitation' => 0.0,
            ],
        ], 200),
    ]);

    Livewire::test('admin.weather')
        ->set('enabled', true)
        ->set('units', 'celsius')
        ->set('widgetSize', 'large')
        ->set('showFeelsLike', false)
        ->set('showPrecipitationAlerts', true)
        ->set('selectedLocation', [
            'name' => 'Denver',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'latitude' => 39.7392,
            'longitude' => -104.9903,
            'timezone' => 'America/Denver',
            'label' => 'Denver, Colorado, United States',
        ])
        ->set('locationQuery', 'Denver, Colorado, United States')
        ->call('save')
        ->assertSet('saved', true);

    expect(Setting::get('weather.enabled'))->toBeTrue()
        ->and(Setting::get('weather.units'))->toBe('celsius')
        ->and(Setting::get('weather.widget_size'))->toBe('large')
        ->and(Setting::get('weather.show_feels_like'))->toBeFalse()
        ->and(Setting::get('weather.precipitation_alerts'))->toBeTrue()
        ->and(Setting::get('weather.location'))->toMatchArray([
            'name' => 'Denver',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'latitude' => 39.7392,
            'longitude' => -104.9903,
            'timezone' => 'America/Denver',
            'label' => 'Denver, Colorado, United States',
        ]);
});

it('searches locations while typing and can select a location', function () {
    Http::fake([
        'https://geocoding-api.open-meteo.com/v1/search*' => Http::response([
            'results' => [
                [
                    'name' => 'Denver',
                    'admin1' => 'Colorado',
                    'country' => 'United States',
                    'latitude' => 39.7392,
                    'longitude' => -104.9903,
                    'timezone' => 'America/Denver',
                ],
            ],
        ], 200),
    ]);

    Livewire::test('admin.weather')
        ->set('locationQuery', 'Denver')
        ->assertSet('searchResults.0.name', 'Denver')
        ->call('selectLocation', 0)
        ->assertSet('selectedLocation.name', 'Denver')
        ->assertSet('locationQuery', 'Denver, Colorado, United States');
});

it('requires selecting a location when weather is enabled', function () {
    Livewire::test('admin.weather')
        ->set('enabled', true)
        ->set('locationQuery', '')
        ->set('selectedLocation', null)
        ->call('save')
        ->assertHasErrors(['locationQuery']);
});

it('shows a preview fallback when the widget is disabled', function () {
    Livewire::test('admin.weather')
        ->set('enabled', false)
        ->assertSee('Preview')
        ->assertSee('Widget is disabled. Enable it to show weather on the dashboard.');
});
