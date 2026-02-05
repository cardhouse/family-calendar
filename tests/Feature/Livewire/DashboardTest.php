<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
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

    expect($loadedChild->relationLoaded('dailyRoutineAssignments'))->toBeTrue();

    $assignment = $loadedChild->dailyRoutineAssignments->first();

    expect($assignment->relationLoaded('routineItem'))->toBeTrue()
        ->and($assignment->relationLoaded('todayCompletion'))->toBeTrue();

    $events = $component->get('upcomingEvents');

    expect($events)->toHaveCount(3);
});
