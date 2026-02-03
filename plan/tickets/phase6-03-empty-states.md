# Ticket: Phase 6.3 Empty States

Goal
Add friendly empty states for the dashboard when core data is missing.

Scope
1. Empty state for no children configured.
2. Empty state for no routines assigned.
3. Empty state for weather unavailable.

Implementation Notes
1. Keep empty states consistent with the dashboard theme.
2. Provide simple next-step hints for the admin.

Tests
1. Add Livewire tests that render the empty states when data is missing.

Acceptance Criteria
1. Empty states appear in the correct scenarios.
2. Messages are clear and concise.
3. Tests pass.

Out of Scope
1. Admin data creation flows.
