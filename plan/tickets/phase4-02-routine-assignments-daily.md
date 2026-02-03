# Ticket: Phase 4.2 Routine Assignments for Daily Buckets

Goal
Implement the daily assignment buckets per child in the routines admin page.

Scope
1. Daily tab with one bucket per child.
2. Drag and drop from the library into a child bucket.
3. Reordering assignments within a bucket using `display_order`.

Implementation Notes
1. Use HTML5 drag and drop with Alpine for the drop zones.
2. Use Livewire actions to create assignments and reorder them.
3. Use `assignable_type` and `assignable_id` as null for daily assignments.

Tests
1. Add Livewire tests for assigning a routine item to a child.
2. Add tests for reordering assignments within a bucket.

Acceptance Criteria
1. Items can be assigned to children in the daily tab.
2. Items can be reordered within the child bucket.
3. Tests pass.

Out of Scope
1. Event and departure assignment tabs.
