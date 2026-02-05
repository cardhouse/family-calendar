<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WeatherService
{
    private const SEARCH_ENDPOINT = 'https://geocoding-api.open-meteo.com/v1/search';

    private const FORECAST_ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    private const CACHE_TTL_SECONDS = 1200;

    private const STALE_CACHE_TTL_SECONDS = 86400;

    private const SEARCH_CACHE_PREFIX = 'weather:search:';

    private const CURRENT_CACHE_PREFIX = 'weather:current:';

    /**
     * @return list<array{name: string, latitude: float, longitude: float, timezone: string, country: string, admin1: ?string}>
     */
    public function searchLocation(string $query): array
    {
        $searchTerm = trim($query);

        if ($searchTerm === '') {
            return [];
        }

        $cacheKey = self::SEARCH_CACHE_PREFIX.md5(Str::lower($searchTerm));

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($searchTerm): array {
            $response = Http::timeout(8)->get(self::SEARCH_ENDPOINT, [
                'name' => $searchTerm,
                'count' => 8,
                'language' => 'en',
                'format' => 'json',
            ]);

            if ($response->failed()) {
                return [];
            }

            $results = $response->json('results');

            if (! is_array($results)) {
                return [];
            }

            return collect($results)
                ->map(function (mixed $result): ?array {
                    if (! is_array($result)) {
                        return null;
                    }

                    $latitude = $result['latitude'] ?? null;
                    $longitude = $result['longitude'] ?? null;

                    if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                        return null;
                    }

                    return [
                        'name' => (string) ($result['name'] ?? 'Unknown'),
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude,
                        'timezone' => (string) ($result['timezone'] ?? 'auto'),
                        'country' => (string) ($result['country'] ?? ''),
                        'admin1' => isset($result['admin1']) ? (string) $result['admin1'] : null,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}  $location
     * @return array{
     *     condition: string,
     *     weather_code: int,
     *     is_day: bool,
     *     temperature: float,
     *     feels_like: ?float,
     *     precipitation: ?float,
     *     unit_symbol: string,
     *     location_label: string,
     *     fetched_at: string
     * }|null
     */
    public function getCurrentWeather(array $location, string $units = 'fahrenheit'): ?array
    {
        if (! $this->hasCoordinates($location)) {
            return null;
        }

        $normalizedUnits = $units === 'celsius' ? 'celsius' : 'fahrenheit';
        $cacheKey = $this->currentCacheKey($location, $normalizedUnits);
        $staleCacheKey = $cacheKey.':stale';

        try {
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return $cached;
            }

            $weather = $this->fetchCurrentWeather($location, $normalizedUnits);

            Cache::put($cacheKey, $weather, self::CACHE_TTL_SECONDS);
            Cache::put($staleCacheKey, $weather, self::STALE_CACHE_TTL_SECONDS);

            return $weather;
        } catch (Throwable) {
            $stale = Cache::get($staleCacheKey);

            return is_array($stale) ? $stale : null;
        }
    }

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}  $location
     * @return array{
     *     condition: string,
     *     weather_code: int,
     *     is_day: bool,
     *     temperature: float,
     *     feels_like: ?float,
     *     precipitation: ?float,
     *     unit_symbol: string,
     *     location_label: string,
     *     fetched_at: string
     * }
     */
    private function fetchCurrentWeather(array $location, string $units): array
    {
        $response = Http::timeout(8)->get(self::FORECAST_ENDPOINT, [
            'latitude' => (float) $location['latitude'],
            'longitude' => (float) $location['longitude'],
            'timezone' => (string) ($location['timezone'] ?? 'auto'),
            'forecast_days' => 1,
            'current' => 'temperature_2m,apparent_temperature,precipitation,weather_code,is_day',
            'temperature_unit' => $units,
            'wind_speed_unit' => $units === 'celsius' ? 'kmh' : 'mph',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to fetch weather forecast.');
        }

        $current = $response->json('current');

        if (! is_array($current)) {
            throw new RuntimeException('Weather response did not include current forecast data.');
        }

        $temperature = $current['temperature_2m'] ?? null;

        if (! is_numeric($temperature)) {
            throw new RuntimeException('Weather response did not include a temperature value.');
        }

        $weatherCode = $current['weather_code'] ?? 0;

        return [
            'condition' => $this->conditionLabel((int) $weatherCode),
            'weather_code' => (int) $weatherCode,
            'is_day' => (bool) ($current['is_day'] ?? true),
            'temperature' => (float) $temperature,
            'feels_like' => is_numeric($current['apparent_temperature'] ?? null)
                ? (float) $current['apparent_temperature']
                : null,
            'precipitation' => is_numeric($current['precipitation'] ?? null)
                ? (float) $current['precipitation']
                : null,
            'unit_symbol' => $units === 'celsius' ? 'C' : 'F',
            'location_label' => $this->locationLabel($location),
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}  $location
     */
    private function hasCoordinates(array $location): bool
    {
        return is_numeric($location['latitude'] ?? null)
            && is_numeric($location['longitude'] ?? null);
    }

    /**
     * @param  array{name?: string, latitude?: float|int|string, longitude?: float|int|string, timezone?: string, country?: string, admin1?: ?string}  $location
     */
    private function currentCacheKey(array $location, string $units): string
    {
        $keyMaterial = implode('|', [
            (string) ($location['latitude'] ?? ''),
            (string) ($location['longitude'] ?? ''),
            (string) ($location['timezone'] ?? ''),
            $units,
        ]);

        return self::CURRENT_CACHE_PREFIX.md5($keyMaterial);
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
        ], fn (mixed $part): bool => is_string($part) && trim($part) !== '');

        return $parts === [] ? 'Saved location' : implode(', ', $parts);
    }

    private function conditionLabel(int $weatherCode): string
    {
        return match (true) {
            $weatherCode === 0 => 'Clear',
            in_array($weatherCode, [1, 2, 3], true) => 'Cloudy',
            in_array($weatherCode, [45, 48], true) => 'Fog',
            in_array($weatherCode, [51, 53, 55, 56, 57], true) => 'Drizzle',
            in_array($weatherCode, [61, 63, 65, 66, 67, 80, 81, 82], true) => 'Rain',
            in_array($weatherCode, [71, 73, 75, 77, 85, 86], true) => 'Snow',
            in_array($weatherCode, [95, 96, 99], true) => 'Thunderstorm',
            default => 'Unknown',
        };
    }
}
