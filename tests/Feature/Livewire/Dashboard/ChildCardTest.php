<?php

declare(strict_types=1);

use App\Models\Child;
use App\Models\DepartureTime;
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

    $child->load(['routineAssignments' => function ($query) {
        $query
            ->whereNull('assignable_type')
            ->whereNull('assignable_id')
            ->with(['routineItem', 'todayCompletion'])
            ->ordered();
    }]);

    Livewire::test('dashboard.child-card', ['child' => $child, 'activeAssignmentIds' => []])
        ->assertDontSeeHtml('line-through')
        ->call('toggleCompletion', $assignment->id)
        ->assertSeeHtml('line-through');

    expect(RoutineCompletion::query()->where('routine_assignment_id', $assignment->id)->exists())
        ->toBeTrue();

    $child->refresh()->load(['routineAssignments' => function ($query) {
        $query
            ->whereNull('assignable_type')
            ->whereNull('assignable_id')
            ->with(['routineItem', 'todayCompletion'])
            ->ordered();
    }]);

    Livewire::test('dashboard.child-card', ['child' => $child, 'activeAssignmentIds' => []])
        ->call('toggleCompletion', $assignment->id)
        ->assertDontSeeHtml('line-through');

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

    $child->load(['routineAssignments' => function ($query) {
        $query
            ->whereNull('assignable_type')
            ->whereNull('assignable_id')
            ->with(['routineItem', 'todayCompletion'])
            ->ordered();
    }]);

    $component = Livewire::test('dashboard.child-card', ['child' => $child, 'activeAssignmentIds' => []])
        ->dispatch('dashboard:toggle-completed', show: false)
        ->assertSet('showCompleted', false);

    expect($component->get('visibleAssignments'))->toHaveCount(0);
});

it('includes active departure assignments for the child card', function () {
    $child = Child::factory()->create();
    $dailyItem = RoutineItemLibrary::factory()->create(['name' => 'Daily routine']);
    $departureItem = RoutineItemLibrary::factory()->create(['name' => 'Departure routine']);
    $departure = DepartureTime::factory()->create();

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $dailyItem->id,
    ]);

    $departureAssignment = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $departureItem->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
    ]);

    $child->load(['routineAssignments' => function ($query) {
        $query
            ->whereNull('assignable_type')
            ->whereNull('assignable_id')
            ->with(['routineItem', 'todayCompletion'])
            ->ordered();
    }]);

    Livewire::test('dashboard.child-card', [
        'child' => $child,
        'activeAssignmentIds' => [$departureAssignment->id],
    ])
        ->assertSee('Daily routine')
        ->assertSee('Departure routine')
        ->call('toggleCompletion', $departureAssignment->id);

    expect(RoutineCompletion::query()->where('routine_assignment_id', $departureAssignment->id)->exists())
        ->toBeTrue();
});
