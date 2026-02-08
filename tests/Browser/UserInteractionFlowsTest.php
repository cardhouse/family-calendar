<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use Illuminate\Support\Carbon;

it('lets a user hide and show a dashboard tile from settings', function () {
    CalendarEvent::factory()->create([
        'name' => 'Science Fair',
        'starts_at' => now()->addDay(),
    ]);

    $page = visit('/');

    $page->assertSee('Calendar Lane')
        ->assertPresent('[wire\\:click="moveTileRight(\'events\')"]')
        ->click('[wire\\:click="toggleTileVisibility(\'events\')"]')
        ->assertMissing('[wire\\:click="moveTileRight(\'events\')"]')
        ->click('[wire\\:click="toggleTileVisibility(\'events\')"]')
        ->assertPresent('[wire\\:click="moveTileRight(\'events\')"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-toggle-tile-visibility');
});

it('lets a user create and delete a child from admin', function () {
    $page = visit('/admin/children');

    $page->assertSee('Children')
        ->click('Add child')
        ->type('input[type="text"]', 'Avery Browser')
        ->click('Save child')
        ->assertSee('Avery Browser')
        ->click('Delete')
        ->assertDontSee('Avery Browser')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'admin-children-crud');

    expect(Child::query()->where('name', 'Avery Browser')->exists())->toBeFalse();
});

it('lets a user create a calendar event from admin', function () {
    Carbon::setTestNow('2026-02-10 07:30:00');

    $page = visit('/admin/events');

    $page->assertSee('Events')
        ->click('Add event')
        ->type('input[type="text"]', 'Browser Test Event')
        ->type('input[type="datetime-local"]', '2026-02-10T09:00')
        ->type('input[type="time"]', '08:20')
        ->click('Save event')
        ->assertSee('Browser Test Event')
        ->assertSee('School')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'admin-events-create');

    expect(CalendarEvent::query()->where('name', 'Browser Test Event')->exists())->toBeTrue();

    Carbon::setTestNow();
});

it('lets a user build and clear a routine assignment by dragging', function () {
    Child::factory()->create([
        'name' => 'Mason',
        'display_order' => 1,
    ]);

    $page = visit('/admin/routines');

    $page->assertSee('Routines')
        ->click('Add routine')
        ->type('input[type="text"]', 'Pack backpack')
        ->click('Save routine')
        ->assertSee('Pack backpack')
        ->drag('[draggable="true"]', '[x-data*="isOver"]')
        ->assertSee('1 items')
        ->click('[aria-label="Remove routine"]')
        ->assertSee('0 items')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'admin-routines-drag-assign');
});

it('lets a user reorder dashboard tiles with movement controls', function () {
    $page = visit('/');

    $page->assertSee('Live Snapshot')
        ->click('[wire\\:click="moveTileRight(\'snapshot\')"]')
        ->assertSee('Crew Snapshot')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-tile-reorder-controls');
});

it('lets a user check and uncheck a checklist item on the dashboard', function () {
    $child = Child::factory()->create([
        'name' => 'Avery',
        'display_order' => 1,
    ]);

    $routineItem = RoutineItemLibrary::factory()->create([
        'name' => 'Zip backpack',
        'display_order' => 1,
    ]);

    $assignment = RoutineAssignment::factory()->create([
        'child_id' => $child->id,
        'routine_item_id' => $routineItem->id,
        'display_order' => 1,
    ]);

    $page = visit('/');

    $page->assertSee('Zip backpack')
        ->assertSee('0 of 1 done')
        ->click('Zip backpack')
        ->assertSee('Completed for today')
        ->click('View tasks')
        ->click('Zip backpack')
        ->assertSee('0 of 1 done')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-checklist-toggle-item');

    expect(RoutineCompletion::query()->where('routine_assignment_id', $assignment->id)->exists())->toBeFalse();
});
