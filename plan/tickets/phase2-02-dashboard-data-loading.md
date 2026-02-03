# Ticket: Phase 2.2 Dashboard Data Loading and Next Departure Logic

Goal
Implement the main Dashboard Livewire component data loading strategy and next departure selection logic.

Scope
1. Load children with daily routine assignments, routine items, and today completion in a single query.
2. Add logic to determine the next departure using departure times and calendar events.
3. Expose computed properties for the view layer.

Implementation Notes
1. Follow the query pattern in the plan to avoid N+1 queries.
2. Use `now()` consistently and make the logic testable with fixed times.
3. Use eager loading and ordered scopes.

Tests
1. Add unit tests for the next departure selection logic.
2. Add a feature test that asserts the dashboard component loads child data without extra queries.

Acceptance Criteria
1. Dashboard loads children and assignments correctly.
2. Next departure logic returns the correct type and time.
3. Tests pass.

Out of Scope
1. UI rendering and styles.
2. Admin data management.
