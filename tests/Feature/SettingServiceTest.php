<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;

it('caches settings and invalidates on update', function () {
    Cache::flush();

    $service = app(SettingService::class);

    $key = 'dashboard.theme';

    $value = $service->get($key, 'dark');

    expect($value)->toBe('dark')
        ->and(Cache::has('settings:'.$key))->toBeTrue();

    $service->set($key, 'light');

    expect(Cache::get('settings:'.$key))->toBe('light')
        ->and($service->get($key))->toBe('light');
});

it('exposes static getters and setters on the setting model', function () {
    Setting::set('weather.units', 'fahrenheit');

    expect(Setting::get('weather.units'))->toBe('fahrenheit');
});
