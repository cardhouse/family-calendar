# Ticket: Phase 1.1 Core Migrations

Goal
Create the core database tables for children, routine item library, departure times, and calendar events with the columns and indexes from the plan.

Scope
1. `children` table with `id`, `name`, `avatar_color` (hex), `display_order`.
2. `routine_item_library` table with `id`, `name`, `display_order`.
3. `departure_times` table with `id`, `name`, `departure_time` (time), `applicable_days` (json), `is_active`, `display_order`.
4. `calendar_events` table with `id`, `name`, `starts_at` (datetime), `departure_time` (time, nullable), `category`, `color`.

Implementation Notes
1. Use `php artisan make:migration --no-interaction` for each table.
2. Follow existing migration style in `database/migrations` for timestamps and column types.
3. Add indexes for `display_order`, `starts_at`, and `is_active` when appropriate.
4. Keep column defaults consistent and explicit.

Tests
1. Add a Pest feature test that asserts the tables exist and contain the required columns.

Acceptance Criteria
1. Running `php artisan migrate` creates all four tables.
2. Columns match the plan and are typed correctly.
3. The schema test passes.

Out of Scope
1. Model classes and relationships.
2. Factories or seeders.
