<?php

declare(strict_types=1);

use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use Livewire\Livewire;

it('toggles routine completion for a child assignment', function () {
    $child = Child::factory()->create();
    $item = RoutineItemLibrary::factory()->create();

    $assignment = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $item->id,
    ]);

    $child->load(['dailyRoutineAssignments' => function ($query) {
        $query->with(['routineItem', 'todayCompletion'])->ordered();
    }]);

    Livewire::test('dashboard.child-card', ['child' => $child])
        ->call('toggleCompletion', $assignment->id);

    expect(RoutineCompletion::query()->where('routine_assignment_id', $assignment->id)->exists())
        ->toBeTrue();

    $child->refresh()->load(['dailyRoutineAssignments' => function ($query) {
        $query->with(['routineItem', 'todayCompletion'])->ordered();
    }]);

    Livewire::test('dashboard.child-card', ['child' => $child])
        ->call('toggleCompletion', $assignment->id);

    expect(RoutineCompletion::query()->where('routine_assignment_id', $assignment->id)->exists())
        ->toBeFalse();
});

it('updates visibility when show completed is toggled', function () {
    $child = Child::factory()->create();
    $item = RoutineItemLibrary::factory()->create();

    $assignment = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $item->id,
    ]);

    RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignment->id,
        'completion_date' => now()->toDateString(),
    ]);

    $child->load(['dailyRoutineAssignments' => function ($query) {
        $query->with(['routineItem', 'todayCompletion'])->ordered();
    }]);

    $component = Livewire::test('dashboard.child-card', ['child' => $child])
        ->dispatch('dashboard:toggle-completed', show: false)
        ->assertSet('showCompleted', false);

    expect($component->get('visibleAssignments'))->toHaveCount(0);
});
