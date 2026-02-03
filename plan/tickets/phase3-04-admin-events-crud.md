# Ticket: Phase 3.4 Admin Events CRUD

Goal
Implement the admin Events page with list, create, edit, delete, and reordering.

Scope
1. Livewire component for `/admin/events`.
2. Create and edit form with datetime picker, optional departure time, category, and color.
3. Reordering via `display_order` if used for events.

Implementation Notes
1. Validate `starts_at` and optional `departure_time`.
2. Use consistent category and color handling with the dashboard.

Tests
1. Add Livewire tests for create, update, and delete flows.
2. Add a test for the `upcoming()` scope ordering if not already covered.

Acceptance Criteria
1. Admin can manage calendar events.
2. Dashboard footer shows upcoming events based on saved data.
3. Tests pass.

Out of Scope
1. Routine assignment integration for events.
