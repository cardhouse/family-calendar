<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Child;
use App\Models\DepartureTime;
use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use App\Models\RoutineItemLibrary;
use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $children = Child::factory()
            ->count(3)
            ->state(new Sequence(
                ['display_order' => 1],
                ['display_order' => 2],
                ['display_order' => 3],
            ))
            ->create();

        $routineItems = RoutineItemLibrary::factory()
            ->count(9)
            ->state(new Sequence(
                ['display_order' => 1],
                ['display_order' => 2],
                ['display_order' => 3],
                ['display_order' => 4],
                ['display_order' => 5],
                ['display_order' => 6],
                ['display_order' => 7],
                ['display_order' => 8],
                ['display_order' => 9],
            ))
            ->create();

        $departures = DepartureTime::factory()
            ->count(2)
            ->state(new Sequence(
                ['display_order' => 1],
                ['display_order' => 2],
            ))
            ->create();

        $events = CalendarEvent::factory()->count(4)->create();

        $children->each(function (Child $child) use ($routineItems): void {
            $routineItems->take(5)->values()->each(function (RoutineItemLibrary $item, int $index) use ($child): void {
                RoutineAssignment::factory()->create([
                    'child_id' => $child->id,
                    'routine_item_id' => $item->id,
                    'display_order' => $index + 1,
                ]);
            });
        });

        $firstEvent = $events->first();
        $firstDeparture = $departures->first();

        if ($firstEvent !== null) {
            $routineItems->take(2)->values()->each(function (RoutineItemLibrary $item, int $index) use ($children, $firstEvent): void {
                RoutineAssignment::factory()->create([
                    'child_id' => $children->first()->id,
                    'routine_item_id' => $item->id,
                    'assignable_type' => CalendarEvent::class,
                    'assignable_id' => $firstEvent->id,
                    'display_order' => $index + 1,
                ]);
            });
        }

        if ($firstDeparture !== null) {
            $routineItems->skip(2)->take(2)->values()->each(function (RoutineItemLibrary $item, int $index) use ($children, $firstDeparture): void {
                RoutineAssignment::factory()->create([
                    'child_id' => $children->last()->id,
                    'routine_item_id' => $item->id,
                    'assignable_type' => DepartureTime::class,
                    'assignable_id' => $firstDeparture->id,
                    'display_order' => $index + 1,
                ]);
            });
        }

        $firstAssignment = RoutineAssignment::query()->first();

        if ($firstAssignment !== null) {
            RoutineCompletion::factory()->create([
                'routine_assignment_id' => $firstAssignment->id,
                'completion_date' => now()->toDateString(),
                'completed_at' => now(),
            ]);
        }

        $weatherSettings = [
            'weather.enabled' => true,
            'weather.units' => 'fahrenheit',
            'weather.widget_size' => 'medium',
            'weather.show_feels_like' => true,
            'weather.precipitation_alerts' => true,
            'weather.location' => [
                'name' => 'Denver',
                'admin1' => 'Colorado',
                'country' => 'United States',
                'latitude' => 39.7392,
                'longitude' => -104.9903,
                'timezone' => 'America/Denver',
                'label' => 'Denver, Colorado, United States',
            ],
        ];

        foreach ($weatherSettings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
