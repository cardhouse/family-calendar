<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_PREFIX = 'settings:';

    private const CACHE_TTL_SECONDS = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            $this->cacheKey($key),
            self::CACHE_TTL_SECONDS,
            function () use ($key, $default): mixed {
                $setting = Setting::query()->find($key);

                return $setting?->value ?? $default;
            }
        );
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $cacheKey = $this->cacheKey($key);

        Cache::put($cacheKey, $value, self::CACHE_TTL_SECONDS);
    }

    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }
}
