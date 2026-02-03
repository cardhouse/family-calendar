# Ticket: Phase 3.3 Admin Departures CRUD

Goal
Implement the admin Departures page with list, create, edit, delete, and reordering.

Scope
1. Livewire component for `/admin/departures` with list view.
2. Create and edit form with time picker, day checkboxes, and active toggle.
3. Reordering via `display_order`.

Implementation Notes
1. Ensure `applicable_days` stores an array of day identifiers.
2. Use validation that enforces valid time and day values.
3. Keep UI consistent with the admin layout.

Tests
1. Add Livewire tests for create, update, delete, and reorder.
2. Add a unit test for `DepartureTime::isApplicableToday()` if not covered elsewhere.

Acceptance Criteria
1. Admin can manage departures and days of week.
2. Order persists.
3. Tests pass.

Out of Scope
1. Dashboard countdown UI.
