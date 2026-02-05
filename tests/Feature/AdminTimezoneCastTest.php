<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\DepartureTime;
use App\Models\Setting;
use Illuminate\Support\Carbon;

it('stores datetimes in utc and returns them in the admin timezone', function () {
    Setting::set('timezone', 'America/Chicago');

    Carbon::setTestNow(Carbon::create(2026, 2, 5, 0, 0, 0, 'America/Chicago'));

    $localTime = Carbon::create(2026, 2, 5, 7, 30, 0, 'America/Chicago');

    $event = CalendarEvent::query()->create([
        'name' => 'School drop-off',
        'starts_at' => $localTime,
        'departure_time' => null,
        'category' => 'school',
        'color' => '#123456',
    ]);

    expect($event->starts_at?->format('Y-m-d H:i:s'))->toBe('2026-02-05 07:30:00')
        ->and($event->getRawOriginal('starts_at'))->toBe($localTime->copy()->setTimezone('UTC')->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});

it('stores times in utc and returns them in the admin timezone', function () {
    Setting::set('timezone', 'America/Chicago');

    Carbon::setTestNow(Carbon::create(2026, 2, 5, 0, 0, 0, 'America/Chicago'));

    $departure = DepartureTime::query()->create([
        'name' => 'Morning',
        'departure_time' => '07:30:00',
        'applicable_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'is_active' => true,
        'display_order' => 1,
    ]);

    expect($departure->departure_time)->toBe('07:30:00')
        ->and($departure->getRawOriginal('departure_time'))->toBe('13:30:00');

    Carbon::setTestNow();
});
