# Ticket: Phase 1.3 Models, Relationships, and Scopes

Goal
Create the core Eloquent models with relationships, scopes, casts, and helper methods defined in the plan.

Scope
1. `Child` model with `routineAssignments()` relationship and `ordered()` and `dailyAssignments()` scopes.
2. `RoutineItemLibrary` model with `assignments()` relationship.
3. `RoutineAssignment` model with `routineItem()`, `child()`, `assignable()` morph, `completions()` relationship, and `todayCompletion()` helper relationship.
4. `RoutineCompletion` model with `assignment()` relationship.
5. `DepartureTime` model with `assignments()` morphMany, `isApplicableToday()` and `getNextOccurrence()`.
6. `CalendarEvent` model with `assignments()` morphMany, `upcoming()` and `withDepartureTime()` scopes.
7. `Setting` model using string primary key and casting `value` to array.

Implementation Notes
1. Use `php artisan make:model --no-interaction` for each model if not present.
2. Use explicit return types on all methods.
3. Prefer `casts()` method over `$casts` if that is the convention.
4. Match existing naming conventions in sibling models.

Tests
1. Add Pest tests that cover relationship wiring and the key scopes.
2. Add unit tests for `DepartureTime::isApplicableToday()` and `getNextOccurrence()` using fixed dates.

Acceptance Criteria
1. All models exist and follow the plan.
2. Relationship and scope tests pass.
3. No new lint issues after running Pint.

Out of Scope
1. Service classes and Livewire components.
2. Admin UI or dashboard UI.
