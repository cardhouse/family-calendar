<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;
use App\Services\NextDepartureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('selects the earliest upcoming departure', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-02 07:00:00'));

    $departure = DepartureTime::factory()->create([
        'name' => 'School',
        'departure_time' => '08:00:00',
        'applicable_days' => ['mon'],
        'is_active' => true,
    ]);

    CalendarEvent::factory()->create([
        'name' => 'Practice',
        'starts_at' => Carbon::parse('2026-02-02 09:00:00'),
        'departure_time' => '08:30:00',
    ]);

    $service = app(NextDepartureService::class);
    $next = $service->determine();

    expect($next)->not->toBeNull()
        ->and($next['label'])->toBe('School')
        ->and($next['timestamp']->toDateTimeString())->toBe('2026-02-02 08:00:00');

    Carbon::setTestNow();
});

it('merges candidates when timestamps match', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-02 07:00:00'));

    $child = Child::factory()->create();
    $item = RoutineItemLibrary::factory()->create();

    $departure = DepartureTime::factory()->create([
        'name' => 'School',
        'departure_time' => '08:00:00',
        'applicable_days' => ['mon'],
        'is_active' => true,
    ]);

    $event = CalendarEvent::factory()->create([
        'name' => 'Camp',
        'starts_at' => Carbon::parse('2026-02-02 09:00:00'),
        'departure_time' => '08:00:00',
    ]);

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $item->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
    ]);

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $item->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ]);

    $service = app(NextDepartureService::class);
    $next = $service->determine();

    expect($next)->not->toBeNull()
        ->and($next['label'])->toBe('Multiple departures')
        ->and($next['labels'])->toMatchArray(['School', 'Camp'])
        ->and($next['assignments'])->toHaveCount(2);

    Carbon::setTestNow();
});
