<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class WeatherService
{
    private const GEOCODING_ENDPOINT = 'https://geocoding-api.open-meteo.com/v1/search';

    private const FORECAST_ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

    private const CACHE_TTL_MINUTES = 20;

    private const STALE_CACHE_TTL_MINUTES = 1440;

    private const CURRENT_WEATHER_CACHE_PREFIX = 'weather:current';

    private const LOCATION_SEARCH_CACHE_PREFIX = 'weather:search';

    /**
     * @return list<array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}>
     */
    public function searchLocation(string $query, int $limit = 5): array
    {
        $normalizedQuery = Str::of($query)->squish()->toString();

        if (mb_strlen($normalizedQuery) < 2) {
            return [];
        }

        $normalizedLimit = max(1, min($limit, 10));
        $cacheKeys = $this->cacheKeys(
            self::LOCATION_SEARCH_CACHE_PREFIX,
            md5(Str::lower($normalizedQuery).'|'.$normalizedLimit)
        );

        $cached = Cache::get($cacheKeys['fresh']);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = $this->httpClient()->get(self::GEOCODING_ENDPOINT, [
                'name' => $normalizedQuery,
                'count' => $normalizedLimit,
                'language' => 'en',
                'format' => 'json',
            ]);

            if (! $response->successful()) {
                $stale = $this->staleCacheValue($cacheKeys['stale']);

                return is_array($stale) ? $stale : [];
            }

            /** @var list<mixed> $results */
            $results = collect($response->json('results', []))
                ->map(function (mixed $result): ?array {
                    return $this->formatLocationResult($result);
                })
                ->filter()
                ->values()
                ->all();

            Cache::put($cacheKeys['fresh'], $results, now()->addMinutes(self::CACHE_TTL_MINUTES));
            Cache::put($cacheKeys['stale'], $results, now()->addMinutes(self::STALE_CACHE_TTL_MINUTES));

            return $results;
        } catch (Throwable) {
            $stale = $this->staleCacheValue($cacheKeys['stale']);

            return is_array($stale) ? $stale : [];
        }
    }

    /**
     * @return array{temperature: float, feels_like: float, precipitation: float|null, precipitation_unit: string, condition: string, weather_code: int, is_day: bool, units: string, unit_symbol: string, fetched_at: string}|null
     */
    public function getCurrentWeather(float $latitude, float $longitude, string $units = 'fahrenheit'): ?array
    {
        $coordinates = $this->normalizeCoordinates($latitude, $longitude);

        if ($coordinates === null) {
            return null;
        }

        $normalizedUnits = $this->normalizeUnits($units);
        $cacheKeys = $this->cacheKeys(
            self::CURRENT_WEATHER_CACHE_PREFIX,
            md5(number_format($coordinates['latitude'], 4, '.', '').'|'.number_format($coordinates['longitude'], 4, '.', '').'|'.$normalizedUnits)
        );

        $cached = Cache::get($cacheKeys['fresh']);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = $this->httpClient()->get(self::FORECAST_ENDPOINT, [
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
                'current' => 'temperature_2m,apparent_temperature,weather_code,is_day,precipitation',
                'temperature_unit' => $normalizedUnits,
                'precipitation_unit' => $normalizedUnits === 'fahrenheit' ? 'inch' : 'mm',
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                return $this->staleCacheValue($cacheKeys['stale']);
            }

            $current = $response->json('current');
            $currentUnits = $response->json('current_units');

            if (! is_array($current) || ! is_array($currentUnits)) {
                return $this->staleCacheValue($cacheKeys['stale']);
            }

            $weather = $this->formatCurrentWeather($current, $currentUnits, $normalizedUnits);

            if ($weather === null) {
                return $this->staleCacheValue($cacheKeys['stale']);
            }

            Cache::put($cacheKeys['fresh'], $weather, now()->addMinutes(self::CACHE_TTL_MINUTES));
            Cache::put($cacheKeys['stale'], $weather, now()->addMinutes(self::STALE_CACHE_TTL_MINUTES));

            return $weather;
        } catch (Throwable) {
            return $this->staleCacheValue($cacheKeys['stale']);
        }
    }

    /**
     * @param  array{name: string, admin1: string|null, country: string|null, latitude: float|int|string, longitude: float|int|string, timezone: string|null, label: string|null}  $location
     * @return array{temperature: float, feels_like: float, precipitation: float|null, precipitation_unit: string, condition: string, weather_code: int, is_day: bool, units: string, unit_symbol: string, fetched_at: string, location_label: string}|null
     */
    public function getCurrentWeatherForLocation(array $location, string $units = 'fahrenheit'): ?array
    {
        $normalizedLocation = $this->normalizeLocationPayload($location);

        if ($normalizedLocation === null) {
            return null;
        }

        $weather = $this->getCurrentWeather(
            $normalizedLocation['latitude'],
            $normalizedLocation['longitude'],
            $units
        );

        if ($weather === null) {
            return null;
        }

        return [
            ...$weather,
            'location_label' => $normalizedLocation['label'],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    private function formatLocationResult(mixed $result): ?array
    {
        if (! is_array($result)) {
            return null;
        }

        $name = $this->cleanString($result['name'] ?? null);
        $country = $this->cleanString($result['country'] ?? null);
        $admin1 = $this->cleanString($result['admin1'] ?? null);
        $timezone = $this->cleanString($result['timezone'] ?? null);
        $latitude = $result['latitude'] ?? null;
        $longitude = $result['longitude'] ?? null;

        if (
            $name === null
            || ! is_numeric($latitude)
            || ! is_numeric($longitude)
        ) {
            return null;
        }

        $formattedLatitude = round((float) $latitude, 4);
        $formattedLongitude = round((float) $longitude, 4);

        return [
            'name' => $name,
            'admin1' => $admin1,
            'country' => $country,
            'latitude' => $formattedLatitude,
            'longitude' => $formattedLongitude,
            'timezone' => $timezone,
            'label' => $this->locationLabel($name, $admin1, $country),
        ];
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $currentUnits
     * @return array{temperature: float, feels_like: float, precipitation: float|null, precipitation_unit: string, condition: string, weather_code: int, is_day: bool, units: string, unit_symbol: string, fetched_at: string}|null
     */
    private function formatCurrentWeather(array $current, array $currentUnits, string $units): ?array
    {
        $temperature = $current['temperature_2m'] ?? null;
        $feelsLike = $current['apparent_temperature'] ?? null;
        $weatherCode = $current['weather_code'] ?? null;
        $isDay = $current['is_day'] ?? null;

        if (
            ! is_numeric($temperature)
            || ! is_numeric($feelsLike)
            || ! is_numeric($weatherCode)
            || ! is_numeric($isDay)
        ) {
            return null;
        }

        $precipitation = $current['precipitation'] ?? null;
        $precipitationValue = is_numeric($precipitation) ? (float) $precipitation : null;
        $precipitationUnit = $this->cleanString($currentUnits['precipitation'] ?? null);

        if ($precipitationUnit === null) {
            $precipitationUnit = $units === 'fahrenheit' ? 'in' : 'mm';
        }

        $code = (int) $weatherCode;

        return [
            'temperature' => round((float) $temperature, 1),
            'feels_like' => round((float) $feelsLike, 1),
            'precipitation' => $precipitationValue !== null ? round($precipitationValue, 2) : null,
            'precipitation_unit' => $precipitationUnit,
            'condition' => $this->weatherCodeLabel($code),
            'weather_code' => $code,
            'is_day' => (int) $isDay === 1,
            'units' => $units,
            'unit_symbol' => $units === 'celsius' ? 'C' : 'F',
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function normalizeCoordinates(float $latitude, float $longitude): ?array
    {
        if (
            $latitude < -90
            || $latitude > 90
            || $longitude < -180
            || $longitude > 180
        ) {
            return null;
        }

        return [
            'latitude' => round($latitude, 4),
            'longitude' => round($longitude, 4),
        ];
    }

    private function normalizeUnits(string $units): string
    {
        $normalized = Str::lower(trim($units));

        return $normalized === 'celsius' ? 'celsius' : 'fahrenheit';
    }

    private function locationLabel(string $name, ?string $admin1, ?string $country): string
    {
        /** @var list<string> $parts */
        $parts = array_values(array_filter([$name, $admin1, $country], function (mixed $value): bool {
            return is_string($value) && $value !== '';
        }));

        return implode(', ', array_unique($parts));
    }

    /**
     * @param  array{name: string, admin1: string|null, country: string|null, latitude: float|int|string, longitude: float|int|string, timezone: string|null, label: string|null}  $location
     * @return array{name: string, admin1: string|null, country: string|null, latitude: float, longitude: float, timezone: string|null, label: string}|null
     */
    private function normalizeLocationPayload(array $location): ?array
    {
        $name = $this->cleanString($location['name'] ?? null);
        $admin1 = $this->cleanString($location['admin1'] ?? null);
        $country = $this->cleanString($location['country'] ?? null);
        $timezone = $this->cleanString($location['timezone'] ?? null);
        $label = $this->cleanString($location['label'] ?? null);
        $latitude = $location['latitude'] ?? null;
        $longitude = $location['longitude'] ?? null;

        if (
            $name === null
            || ! is_numeric($latitude)
            || ! is_numeric($longitude)
        ) {
            return null;
        }

        if ($label === null) {
            $label = $this->locationLabel($name, $admin1, $country);
        }

        return [
            'name' => $name,
            'admin1' => $admin1,
            'country' => $country,
            'latitude' => round((float) $latitude, 4),
            'longitude' => round((float) $longitude, 4),
            'timezone' => $timezone,
            'label' => $label,
        ];
    }

    private function weatherCodeLabel(int $weatherCode): string
    {
        return match ($weatherCode) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45, 48 => 'Fog',
            51, 53, 55, 56, 57 => 'Drizzle',
            61, 63, 65, 66, 67, 80, 81, 82 => 'Rain',
            71, 73, 75, 77, 85, 86 => 'Snow',
            95, 96, 99 => 'Thunderstorm',
            default => 'Weather update',
        };
    }

    /**
     * @return array{fresh: string, stale: string}
     */
    private function cacheKeys(string $prefix, string $identifier): array
    {
        return [
            'fresh' => $prefix.':'.$identifier,
            'stale' => $prefix.':'.$identifier.':stale',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function staleCacheValue(string $cacheKey): ?array
    {
        $stale = Cache::get($cacheKey);

        return is_array($stale) ? $stale : null;
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function httpClient(): PendingRequest
    {
        $timeout = (float) config('services.weather.request_timeout', 8);

        if ($timeout <= 0) {
            $timeout = 8;
        }

        return Http::acceptJson()
            ->timeout($timeout)
            ->retry(1, 250);
    }
}
