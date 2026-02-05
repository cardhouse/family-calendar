<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;
use Livewire\Livewire;

it('creates a routine library item', function () {
    Livewire::test('admin.routines')
        ->call('openLibraryCreate')
        ->set('libraryName', 'Brush Teeth')
        ->call('saveLibrary');

    expect(RoutineItemLibrary::query()->where('name', 'Brush Teeth')->exists())->toBeTrue();
});

it('updates a routine library item', function () {
    $routineItem = RoutineItemLibrary::factory()->create(['name' => 'Wake Up']);

    Livewire::test('admin.routines')
        ->call('openLibraryEdit', $routineItem->id)
        ->set('libraryName', 'Wake Up Gentle')
        ->call('saveLibrary');

    expect($routineItem->refresh()->name)->toBe('Wake Up Gentle');
});

it('deletes a routine library item', function () {
    $routineItem = RoutineItemLibrary::factory()->create();

    Livewire::test('admin.routines')
        ->call('deleteLibrary', $routineItem->id);

    expect(RoutineItemLibrary::query()->whereKey($routineItem->id)->exists())->toBeFalse();
});

it('reorders routine library items', function () {
    $first = RoutineItemLibrary::factory()->create(['display_order' => 1]);
    $second = RoutineItemLibrary::factory()->create(['display_order' => 2]);
    $third = RoutineItemLibrary::factory()->create(['display_order' => 3]);

    Livewire::test('admin.routines')
        ->call('reorderLibrary', $third->id, 0);

    $ordered = RoutineItemLibrary::query()->ordered()->get();

    expect($ordered->first()->is($third))->toBeTrue()
        ->and($ordered->last()->is($second))->toBeTrue();
});

it('assigns a routine to a child daily bucket', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();

    Livewire::test('admin.routines')
        ->call('assignRoutine', $routineItem->id, $child->id);

    $assignment = RoutineAssignment::query()
        ->where('child_id', $child->id)
        ->where('routine_item_id', $routineItem->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment?->assignable_type)->toBeNull()
        ->and($assignment?->assignable_id)->toBeNull();
});

it('reorders assignments within a bucket', function () {
    $child = Child::factory()->create();

    $first = RoutineAssignment::factory()->create(['child_id' => $child->id, 'display_order' => 1]);
    $second = RoutineAssignment::factory()->create(['child_id' => $child->id, 'display_order' => 2]);
    $third = RoutineAssignment::factory()->create(['child_id' => $child->id, 'display_order' => 3]);

    Livewire::test('admin.routines')
        ->call('reorderAssignment', $third->id, 0);

    $ordered = RoutineAssignment::query()
        ->where('child_id', $child->id)
        ->whereNull('assignable_type')
        ->whereNull('assignable_id')
        ->ordered()
        ->get();

    expect($ordered->first()->is($third))->toBeTrue()
        ->and($ordered->last()->is($second))->toBeTrue();
});

it('assigns a routine to an event bucket', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $event = CalendarEvent::factory()->create();

    Livewire::test('admin.routines')
        ->set('activeTab', 'events')
        ->set('selectedEventId', $event->id)
        ->call('assignRoutine', $routineItem->id, $child->id);

    expect(RoutineAssignment::query()->where([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ])->exists())->toBeTrue();
});

it('assigns a routine to a departure bucket', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $departure = DepartureTime::factory()->create();

    Livewire::test('admin.routines')
        ->set('activeTab', 'departures')
        ->set('selectedDepartureId', $departure->id)
        ->call('assignRoutine', $routineItem->id, $child->id);

    expect(RoutineAssignment::query()->where([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
    ])->exists())->toBeTrue();
});

it('prevents duplicate daily routine assignments', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();

    Livewire::test('admin.routines')
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->assertHasErrors(['assignment']);

    expect(RoutineAssignment::query()->where('child_id', $child->id)->count())->toBe(1);
});

it('prevents duplicate event routine assignments', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $event = CalendarEvent::factory()->create();

    Livewire::test('admin.routines')
        ->set('activeTab', 'events')
        ->set('selectedEventId', $event->id)
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->assertHasErrors(['assignment']);

    expect(RoutineAssignment::query()->where([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ])->count())->toBe(1);
});

it('prevents duplicate departure routine assignments', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $departure = DepartureTime::factory()->create();

    Livewire::test('admin.routines')
        ->set('activeTab', 'departures')
        ->set('selectedDepartureId', $departure->id)
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->call('assignRoutine', $routineItem->id, $child->id)
        ->assertHasErrors(['assignment']);

    expect(RoutineAssignment::query()->where([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
    ])->count())->toBe(1);
});
