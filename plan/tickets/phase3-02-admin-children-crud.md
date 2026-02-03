# Ticket: Phase 3.2 Admin Children CRUD

Goal
Implement the admin Children page with list, create, edit, delete, and reorder capabilities.

Scope
1. Livewire component for `/admin/children` with list view.
2. Create and edit modal with name and avatar color picker.
3. Reordering support using `display_order`.

Implementation Notes
1. Use a Form Request or dedicated validation rules consistent with project conventions.
2. Use Livewire actions for create, update, delete, and reorder.
3. Persist `display_order` changes.

Tests
1. Add Livewire tests for create, update, delete, and reorder flows.

Acceptance Criteria
1. Admin can manage children without page reloads.
2. Order persists and is reflected on the dashboard.
3. Tests pass.

Out of Scope
1. Routine assignment management.
