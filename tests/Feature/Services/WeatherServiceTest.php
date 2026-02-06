<?php

declare(strict_types=1);

use App\Services\WeatherService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config()->set('services.weather.request_timeout', 8);
});

it('searches locations and caches results for twenty minutes', function () {
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
                [
                    'name' => 'Denver',
                    'admin1' => 'Iowa',
                    'country' => 'United States',
                    'latitude' => 42.6714,
                    'longitude' => -92.3385,
                    'timezone' => 'America/Chicago',
                ],
            ],
        ], 200),
    ]);

    $service = app(WeatherService::class);

    $results = $service->searchLocation('Denver');
    $cached = $service->searchLocation('Denver');

    expect($results)->toHaveCount(2)
        ->and($results[0])->toMatchArray([
            'name' => 'Denver',
            'admin1' => 'Colorado',
            'country' => 'United States',
            'label' => 'Denver, Colorado, United States',
        ])
        ->and($cached)->toBe($results);

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return str_contains($request->url(), 'name=Denver')
            && str_contains($request->url(), 'count=5');
    });
});

it('returns current weather and reuses cached responses', function () {
    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([
            'current_units' => [
                'temperature_2m' => 'F',
                'apparent_temperature' => 'F',
                'precipitation' => 'in',
            ],
            'current' => [
                'temperature_2m' => 72.4,
                'apparent_temperature' => 70.8,
                'weather_code' => 0,
                'is_day' => 1,
                'precipitation' => 0.0,
            ],
        ], 200),
    ]);

    $service = app(WeatherService::class);

    $weather = $service->getCurrentWeather(39.7392, -104.9903, 'fahrenheit');
    $cached = $service->getCurrentWeather(39.7392, -104.9903, 'fahrenheit');

    expect($weather)->not->toBeNull()
        ->and($weather)->toMatchArray([
            'temperature' => 72.4,
            'feels_like' => 70.8,
            'precipitation' => 0.0,
            'precipitation_unit' => 'in',
            'condition' => 'Clear sky',
            'weather_code' => 0,
            'is_day' => true,
            'units' => 'fahrenheit',
        ])
        ->and($cached)->toBe($weather);

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request): bool {
        return str_contains($request->url(), 'temperature_unit=fahrenheit');
    });
});

it('uses stale cache when current weather refresh fails', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-06 08:00:00', 'UTC'));

    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([
            'current_units' => [
                'temperature_2m' => 'F',
                'apparent_temperature' => 'F',
                'precipitation' => 'in',
            ],
            'current' => [
                'temperature_2m' => 68.1,
                'apparent_temperature' => 66.4,
                'weather_code' => 2,
                'is_day' => 1,
                'precipitation' => 0.12,
            ],
        ], 200),
    ]);

    $service = app(WeatherService::class);
    $first = $service->getCurrentWeather(39.7392, -104.9903, 'fahrenheit');

    Carbon::setTestNow(Carbon::parse('2026-02-06 08:21:00', 'UTC'));

    Http::fake([
        'https://api.open-meteo.com/v1/forecast*' => Http::response([], 500),
    ]);

    $fallback = $service->getCurrentWeather(39.7392, -104.9903, 'fahrenheit');

    expect($fallback)->toBe($first);

    Carbon::setTestNow();
});
