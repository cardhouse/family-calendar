<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use Illuminate\Support\Carbon;

it('orders children by display order', function () {
    $first = Child::factory()->create(['display_order' => 2]);
    $second = Child::factory()->create(['display_order' => 1]);

    $ordered = Child::query()->ordered()->get();

    expect($ordered->first()->is($second))->toBeTrue()
        ->and($ordered->last()->is($first))->toBeTrue();
});

it('returns daily assignments without an assignable', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $event = CalendarEvent::factory()->create();

    $daily = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => null,
        'assignable_id' => null,
    ]);

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ]);

    $child->load('dailyAssignments');

    expect($child->dailyAssignments->pluck('id'))->toContain($daily->id)
        ->and($child->dailyAssignments)->toHaveCount(1);
});

it('returns the today completion for a routine assignment', function () {
    $assignment = RoutineAssignment::factory()->create();

    RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignment->id,
        'completion_date' => now()->subDay()->toDateString(),
    ]);

    $todayCompletion = RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignment->id,
        'completion_date' => now()->toDateString(),
    ]);

    $assignment->load('todayCompletion');

    expect($assignment->todayCompletion->is($todayCompletion))->toBeTrue();
});

it('checks departure applicability and next occurrence', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-02 07:30:00'));

    $departure = DepartureTime::factory()->create([
        'departure_time' => '08:00:00',
        'applicable_days' => ['mon'],
        'is_active' => true,
    ]);

    expect($departure->isApplicableToday())->toBeTrue();

    $next = $departure->getNextOccurrence();

    expect($next)->not->toBeNull()
        ->and($next?->toDateTimeString())->toBe('2026-02-02 08:00:00');

    Carbon::setTestNow(Carbon::parse('2026-02-02 09:00:00'));

    $nextAfter = $departure->getNextOccurrence();

    expect($nextAfter)->not->toBeNull()
        ->and($nextAfter?->toDateString())->toBe('2026-02-09');

    Carbon::setTestNow();
});

it('scopes calendar events by upcoming and departure time', function () {
    $pastEvent = CalendarEvent::factory()->create([
        'starts_at' => now()->subDay(),
        'departure_time' => null,
    ]);

    $futureWithDeparture = CalendarEvent::factory()->create([
        'starts_at' => now()->addDay(),
        'departure_time' => '07:30:00',
    ]);

    $futureNoDeparture = CalendarEvent::factory()->create([
        'starts_at' => now()->addDays(2),
        'departure_time' => null,
    ]);

    $upcoming = CalendarEvent::query()->upcoming()->get();

    expect($upcoming->pluck('id'))->not->toContain($pastEvent->id)
        ->and($upcoming->pluck('id'))->toContain($futureWithDeparture->id)
        ->and($upcoming->pluck('id'))->toContain($futureNoDeparture->id);

    $withDeparture = CalendarEvent::query()->withDepartureTime()->get();

    expect($withDeparture->pluck('id'))->toContain($futureWithDeparture->id)
        ->and($withDeparture->pluck('id'))->not->toContain($pastEvent->id)
        ->and($withDeparture->pluck('id'))->not->toContain($futureNoDeparture->id);
});
