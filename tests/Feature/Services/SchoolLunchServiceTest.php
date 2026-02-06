<?php

declare(strict_types=1);

use App\Services\SchoolLunchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.school_lunch.source_url', 'https://www.schoolnutritionandfitness.com/webmenus2/#/view-no-design?id=menu-123&siteCode=3711');
    config()->set('services.school_lunch.menu_id', null);
    config()->set('services.school_lunch.site_code', null);
    config()->set('services.school_lunch.cache_ttl_minutes', 30);
    config()->set('services.school_lunch.request_timeout', 10);

    Cache::flush();
});

it('returns school lunch details for a school day', function () {
    Http::fake([
        'https://www.schoolnutritionandfitness.com/webmenus2/api/menuController.php/open*' => Http::response([
            'menuType' => [
                'id' => 'menu-type-123',
                'name' => 'Elementary K-4',
            ],
            'site_codes' => [3711],
        ], 200),
        'https://api.schoolnutritionandfitness.com/graphql' => Http::response([
            'data' => [
                'menuType' => [
                    'name' => 'Elementary K-4',
                    'items' => [
                        ['product' => ['name' => 'Chicken Tacos', 'hide_on_calendars' => '0', 'hide_on_web_menu_view' => '0', 'is_ancillary' => false, 'trash' => false]],
                        ['product' => ['name' => 'Cucumber Slices', 'hide_on_calendars' => '0', 'hide_on_web_menu_view' => '0', 'is_ancillary' => false, 'trash' => false]],
                        ['product' => ['name' => 'Chicken Tacos', 'hide_on_calendars' => '0', 'hide_on_web_menu_view' => '0', 'is_ancillary' => false, 'trash' => false]],
                        ['product' => ['name' => 'Hidden Item', 'hide_on_calendars' => '1', 'hide_on_web_menu_view' => '0', 'is_ancillary' => false, 'trash' => false]],
                    ],
                ],
            ],
        ], 200),
    ]);

    $lunch = app(SchoolLunchService::class)->forDate(Carbon::parse('2026-02-06 08:00:00', 'UTC'));

    expect($lunch)->not->toBeNull()
        ->and($lunch)->toHaveKeys(['date', 'date_label', 'menu_name', 'items'])
        ->and($lunch['date'])->toBe('2026-02-06')
        ->and($lunch['menu_name'])->toBe('Elementary K-4')
        ->and($lunch['items'])->toBe(['Chicken Tacos', 'Cucumber Slices']);

    Http::assertSentCount(2);
});

it('skips lunch requests on weekends', function () {
    Http::preventStrayRequests();

    $lunch = app(SchoolLunchService::class)->forDate(Carbon::parse('2026-02-07 08:00:00', 'UTC'));

    expect($lunch)->toBeNull();
    Http::assertNothingSent();
});

it('requires configured site code to match the menu site codes', function () {
    config()->set('services.school_lunch.site_code', '9999');

    Http::fake([
        'https://www.schoolnutritionandfitness.com/webmenus2/api/menuController.php/open*' => Http::response([
            'menuType' => [
                'id' => 'menu-type-123',
                'name' => 'Elementary K-4',
            ],
            'site_codes' => [3711],
        ], 200),
        'https://api.schoolnutritionandfitness.com/graphql' => Http::response([
            'data' => [
                'menuType' => [
                    'name' => 'Elementary K-4',
                    'items' => [
                        ['product' => ['name' => 'Chicken Tacos']],
                    ],
                ],
            ],
        ], 200),
    ]);

    $lunch = app(SchoolLunchService::class)->forDate(Carbon::parse('2026-02-06 08:00:00', 'UTC'));

    expect($lunch)->toBeNull();
    Http::assertSentCount(1);
});
