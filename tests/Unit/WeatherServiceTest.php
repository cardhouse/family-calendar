<?php

declare(strict_types=1);

use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('searches locations and normalizes results', function () {
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

    $results = app(WeatherService::class)->searchLocation('Austin');

    expect($results)->toHaveCount(1)
        ->and($results[0]['name'])->toBe('Austin')
        ->and($results[0]['timezone'])->toBe('America/Chicago')
        ->and($results[0]['country'])->toBe('United States');
});

it('returns current weather and caches the response', function () {
    Cache::flush();

    Http::fake([
        'api.open-meteo.com/*' => Http::response([
            'current' => [
                'temperature_2m' => 61.3,
                'apparent_temperature' => 59.9,
                'precipitation' => 0.0,
                'weather_code' => 1,
                'is_day' => 1,
            ],
        ]),
    ]);

    $location = [
        'name' => 'Austin',
        'admin1' => 'Texas',
        'country' => 'United States',
        'latitude' => '30.2672',
        'longitude' => '-97.7431',
        'timezone' => 'America/Chicago',
    ];

    $service = app(WeatherService::class);

    $first = $service->getCurrentWeather($location, 'fahrenheit');
    $second = $service->getCurrentWeather($location, 'fahrenheit');

    expect($first)->not->toBeNull()
        ->and($first['condition'])->toBe('Cloudy')
        ->and($first['unit_symbol'])->toBe('F')
        ->and($second)->toBe($first);

    Http::assertSentCount(1);
});

it('falls back to stale cache when the API fails', function () {
    Cache::flush();

    $location = [
        'name' => 'Austin',
        'admin1' => 'Texas',
        'country' => 'United States',
        'latitude' => '30.2672',
        'longitude' => '-97.7431',
        'timezone' => 'America/Chicago',
    ];

    Http::fake([
        'api.open-meteo.com/*' => Http::response([
            'current' => [
                'temperature_2m' => 61.3,
                'apparent_temperature' => 59.9,
                'precipitation' => 0.0,
                'weather_code' => 1,
                'is_day' => 1,
            ],
        ]),
    ]);

    $service = app(WeatherService::class);
    $fresh = $service->getCurrentWeather($location, 'fahrenheit');

    $cacheKey = 'weather:current:'.md5('30.2672|-97.7431|America/Chicago|fahrenheit');
    Cache::forget($cacheKey);

    Http::fake([
        'api.open-meteo.com/*' => Http::response([], 500),
    ]);

    $fallback = $service->getCurrentWeather($location, 'fahrenheit');

    expect($fallback)->toBe($fresh);
});
