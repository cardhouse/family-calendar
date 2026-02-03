# Ticket: Phase 1.2 Assignment, Completion, and Settings Migrations

Goal
Add the remaining tables for routine assignments, routine completions, and settings with constraints and uniqueness rules.

Scope
1. `routine_assignments` table with `id`, `routine_item_id` (FK), `child_id` (FK), `assignable_type` (nullable), `assignable_id` (nullable), `display_order`.
2. `routine_completions` table with `id`, `routine_assignment_id` (FK), `completion_date`, `completed_at`.
3. `settings` table with `key` (string primary), `value` (json).
4. Unique constraint for routine completions on `routine_assignment_id` + `completion_date`.
5. Unique constraint for routine assignments on `routine_item_id` + `child_id` + `assignable_type` + `assignable_id`.

Implementation Notes
1. Use `php artisan make:migration --no-interaction` for each table.
2. Use `foreignId()->constrained()` for FK columns.
3. Use `nullableMorphs('assignable')` for the polymorphic columns.
4. Add explicit indexes where appropriate.

Tests
1. Add or extend a Pest schema test to verify the tables, columns, and unique constraints.

Acceptance Criteria
1. `php artisan migrate` creates all three tables.
2. The unique constraints exist and are enforced by the database.
3. The schema test passes.

Out of Scope
1. Models, services, factories, or seeders.
