<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('loads children with eager loaded assignments', function () {
    $child = Child::factory()->create(['display_order' => 1]);
    $items = RoutineItemLibrary::factory()->count(2)->create();

    $assignments = $items->map(fn ($item, int $index) => RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $item->id,
        'display_order' => $index + 1,
    ]));

    RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignments->first()->id,
        'completion_date' => now()->toDateString(),
    ]);

    CalendarEvent::factory()->count(4)->create([
        'starts_at' => now()->addDay(),
    ]);

    $component = Livewire::test('dashboard');
    $children = $component->get('children');

    expect($children)->toHaveCount(1);

    $loadedChild = $children->first();

    expect($loadedChild->relationLoaded('routineAssignments'))->toBeTrue();

    $assignment = $loadedChild->routineAssignments->first();

    expect($assignment->relationLoaded('routineItem'))->toBeTrue()
        ->and($assignment->relationLoaded('todayCompletion'))->toBeTrue();

    $events = $component->get('upcomingEvents');

    expect($events)->toHaveCount(3);
});

it('loads departure routines for the next departure on each child card', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-02 07:00:00'));

    $child = Child::factory()->create(['display_order' => 1]);
    $dailyItem = RoutineItemLibrary::factory()->create(['name' => 'Brush teeth']);
    $departureItem = RoutineItemLibrary::factory()->create(['name' => 'Backpack check']);

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $dailyItem->id,
        'display_order' => 1,
    ]);

    $departure = DepartureTime::factory()->create([
        'name' => 'School Bus',
        'departure_time' => '08:00:00',
        'applicable_days' => ['mon'],
        'is_active' => true,
    ]);

    $departureAssignment = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $departureItem->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
        'display_order' => 1,
    ]);

    $component = Livewire::test('dashboard');

    $children = $component->get('children');
    $activeAssignmentIdsByChild = $component->get('activeAssignmentIdsByChild');

    expect($children)->toHaveCount(1)
        ->and($activeAssignmentIdsByChild[$child->id] ?? [])->toContain($departureAssignment->id)
        ->and($children->first()->routineAssignments->pluck('id')->all())->toContain($departureAssignment->id);

    Carbon::setTestNow();
});
