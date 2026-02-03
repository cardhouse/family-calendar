# Ticket: Phase 4.3 Routine Assignments for Events and Departures

Goal
Add event and departure assignment buckets to the routines admin page.

Scope
1. Events tab with a selector for event and buckets per child.
2. Departures tab with a selector for departure time and buckets per child.
3. Drag and drop assignments for each selected assignable.

Implementation Notes
1. Use `assignable_type` and `assignable_id` to store links.
2. Update bucket data when the selector changes.
3. Reuse the same Livewire actions for assign and reorder.

Tests
1. Add Livewire tests for assigning items to an event.
2. Add Livewire tests for assigning items to a departure time.

Acceptance Criteria
1. Admin can assign and reorder items for a selected event.
2. Admin can assign and reorder items for a selected departure time.
3. Tests pass.

Out of Scope
1. Duplicate prevention and validation rules.
