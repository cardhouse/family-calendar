# Ticket: Phase 1.5 Factories and Seeders

Goal
Create factories for all new models and update the database seeder with sample data that matches the plan.

Scope
1. Factories for `Child`, `RoutineItemLibrary`, `RoutineAssignment`, `RoutineCompletion`, `DepartureTime`, `CalendarEvent`, `Setting`.
2. Update `DatabaseSeeder` to create 2 to 3 children, 8 to 10 routine items, 2 to 3 departures, 3 to 5 events, and sample assignments.

Implementation Notes
1. Use `php artisan make:factory --no-interaction` for each factory.
2. Follow existing factory conventions in `database/factories`.
3. Ensure display ordering is set on seeded records.
4. Avoid hardcoding dates that will become stale.

Tests
1. Add a Pest feature test that runs the seeder and asserts minimum record counts for each table.

Acceptance Criteria
1. `php artisan migrate:fresh --seed` completes successfully.
2. Seeded data includes all entities listed in the plan.
3. Seeder test passes.

Out of Scope
1. UI work or Livewire components.
