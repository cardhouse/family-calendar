<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use App\Models\Setting;
use Illuminate\Support\Carbon;

it('captures a rich dashboard screenshot with countdown and mixed progress', function () {
    seedDashboardScenario();

    $page = visit('/');

    $page->assertSee('Morning Bus')
        ->assertSee('Mila')
        ->assertSee('Noah')
        ->assertSee('Iris')
        ->assertSee('Completed for today')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-smoke-rich', fullPage: true);

    Carbon::setTestNow();
});

it('captures rich dashboard in dark mode', function () {
    seedDashboardScenario();

    $page = visit('/')->inDarkMode();

    $page->assertSee('Morning Bus')
        ->assertSee('Mila')
        ->assertSee('Noah')
        ->assertSee('Iris')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-smoke-rich-dark', fullPage: true);

    Carbon::setTestNow();
});

it('captures completed and in-progress checklist cards together', function () {
    seedDashboardScenario();

    $page = visit('/');

    $page->assertSee('Completed for today')
        ->assertSee('2 of 4 done')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->screenshot(filename: 'dashboard-completed-and-active', fullPage: true);

    Carbon::setTestNow();
});

function seedDashboardScenario(): void
{
    Carbon::setTestNow(Carbon::parse('2026-02-16 07:30:00', 'America/New_York'));

    Setting::set('timezone', 'America/New_York');
    Setting::set('weather.enabled', false);

    $mila = Child::factory()->create([
        'name' => 'Mila',
        'avatar_color' => '#f97316',
        'display_order' => 1,
    ]);

    $noah = Child::factory()->create([
        'name' => 'Noah',
        'avatar_color' => '#3b82f6',
        'display_order' => 2,
    ]);

    $iris = Child::factory()->create([
        'name' => 'Iris',
        'avatar_color' => '#22c55e',
        'display_order' => 3,
    ]);

    $taskNames = [
        'Get dressed',
        'Brush teeth',
        'Pack backpack',
        'Water bottle',
    ];

    $routineItems = collect($taskNames)->map(function (string $name, int $index): RoutineItemLibrary {
        return RoutineItemLibrary::factory()->create([
            'name' => $name,
            'display_order' => $index + 1,
        ]);
    });

    $allAssignments = collect([$mila, $noah, $iris])->flatMap(function (Child $child) use ($routineItems) {
        return $routineItems->map(function (RoutineItemLibrary $item, int $index) use ($child): RoutineAssignment {
            return RoutineAssignment::factory()->create([
                'child_id' => $child->id,
                'routine_item_id' => $item->id,
                'display_order' => $index + 1,
            ]);
        });
    });

    $allAssignments
        ->where('child_id', $mila->id)
        ->each(function (RoutineAssignment $assignment): void {
            RoutineCompletion::factory()->create([
                'routine_assignment_id' => $assignment->id,
                'completion_date' => now()->toDateString(),
                'completed_at' => now(),
            ]);
        });

    $allAssignments
        ->where('child_id', $noah->id)
        ->take(2)
        ->each(function (RoutineAssignment $assignment): void {
            RoutineCompletion::factory()->create([
                'routine_assignment_id' => $assignment->id,
                'completion_date' => now()->toDateString(),
                'completed_at' => now(),
            ]);
        });

    $departure = DepartureTime::factory()->create([
        'name' => 'Morning Bus',
        'departure_time' => '08:10:00',
        'applicable_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'is_active' => true,
        'display_order' => 1,
    ]);

    RoutineAssignment::factory()->create([
        'child_id' => $iris->id,
        'routine_item_id' => $routineItems->first()->id,
        'assignable_type' => DepartureTime::class,
        'assignable_id' => $departure->id,
        'display_order' => 1,
    ]);

    CalendarEvent::factory()->create([
        'name' => 'Band practice',
        'starts_at' => now()->addHours(6),
        'departure_time' => '13:30:00',
        'category' => 'sports',
        'color' => '#f59e0b',
    ]);

    CalendarEvent::factory()->create([
        'name' => 'Parent conference',
        'starts_at' => now()->addDay()->setTime(17, 0),
        'departure_time' => null,
        'category' => 'school',
        'color' => '#38bdf8',
    ]);

    CalendarEvent::factory()->create([
        'name' => 'Family dinner',
        'starts_at' => now()->addDays(2)->setTime(18, 30),
        'departure_time' => null,
        'category' => 'family',
        'color' => '#34d399',
    ]);
}
