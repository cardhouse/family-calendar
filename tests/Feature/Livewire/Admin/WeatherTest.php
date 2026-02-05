<?php

declare(strict_types=1);

use App\Livewire\Admin\Weather;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('updates weather settings', function () {
    $location = [
        'name' => 'Austin',
        'admin1' => 'Texas',
        'country' => 'United States',
        'latitude' => 30.2672,
        'longitude' => -97.7431,
        'timezone' => 'America/Chicago',
    ];

    Livewire::test(Weather::class)
        ->set('selectedLocation', $location)
        ->set('units', 'celsius')
        ->set('widgetSize', 'large')
        ->set('showFeelsLike', false)
        ->set('precipitationAlerts', false)
        ->set('widgetEnabled', true)
        ->call('save')
        ->assertSet('statusMessage', 'Weather settings saved.');

    expect(Setting::get('weather.units'))->toBe('celsius')
        ->and(Setting::get('weather.widget_size'))->toBe('large')
        ->and(Setting::get('weather.show_feels_like'))->toBeFalse()
        ->and(Setting::get('weather.precipitation_alerts'))->toBeFalse()
        ->and(Setting::get('weather.enabled'))->toBeTrue()
        ->and(Setting::get('weather.location'))->toMatchArray($location);
});

it('requires a location when the widget is enabled', function () {
    Livewire::test(Weather::class)
        ->set('selectedLocation', null)
        ->set('widgetEnabled', true)
        ->call('save')
        ->assertHasErrors(['selectedLocation']);
});

it('searches locations and allows selection', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                [
                    'name' => 'Austin',
                    'latitude' => 30.2672,
                    'longitude' => -97.7431,
                    'timezone' => 'America/Chicago',
                    'country' => 'United States',
                    'admin1' => 'Texas',
                ],
            ],
        ]),
    ]);

    Livewire::test(Weather::class)
        ->set('search', 'Austin')
        ->assertSet('searchResults.0.name', 'Austin')
        ->call('selectLocation', 0)
        ->assertSet('selectedLocation.name', 'Austin')
        ->assertSet('searchResults', []);
});
