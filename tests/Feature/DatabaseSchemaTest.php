<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('creates the core tables with required columns', function () {
    expect(Schema::hasTable('children'))->toBeTrue()
        ->and(Schema::hasColumns('children', [
            'id',
            'name',
            'avatar_color',
            'display_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('routine_item_library'))->toBeTrue()
        ->and(Schema::hasColumns('routine_item_library', [
            'id',
            'name',
            'display_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('departure_times'))->toBeTrue()
        ->and(Schema::hasColumns('departure_times', [
            'id',
            'name',
            'departure_time',
            'applicable_days',
            'is_active',
            'display_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('calendar_events'))->toBeTrue()
        ->and(Schema::hasColumns('calendar_events', [
            'id',
            'name',
            'starts_at',
            'departure_time',
            'category',
            'color',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('routine_assignments'))->toBeTrue()
        ->and(Schema::hasColumns('routine_assignments', [
            'id',
            'routine_item_id',
            'child_id',
            'assignable_type',
            'assignable_id',
            'display_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('routine_completions'))->toBeTrue()
        ->and(Schema::hasColumns('routine_completions', [
            'id',
            'routine_assignment_id',
            'completion_date',
            'completed_at',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('settings'))->toBeTrue()
        ->and(Schema::hasColumns('settings', [
            'key',
            'value',
        ]))->toBeTrue();
});

it('enforces unique routine completions per date', function () {
    $assignment = RoutineAssignment::factory()->create();
    $completionDate = now()->toDateString();

    RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignment->id,
        'completion_date' => $completionDate,
    ]);

    expect(fn () => RoutineCompletion::factory()->create([
        'routine_assignment_id' => $assignment->id,
        'completion_date' => $completionDate,
    ]))->toThrow(QueryException::class);
});

it('enforces unique routine assignments per assignable', function () {
    $child = Child::factory()->create();
    $routineItem = RoutineItemLibrary::factory()->create();
    $event = CalendarEvent::factory()->create();

    RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ]);

    expect(fn () => RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'assignable_type' => CalendarEvent::class,
        'assignable_id' => $event->id,
    ]))->toThrow(QueryException::class);
});
