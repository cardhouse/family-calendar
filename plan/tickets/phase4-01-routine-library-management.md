# Ticket: Phase 4.1 Routine Library Management

Goal
Build the left panel of the routines admin page to manage routine library items and ordering.

Scope
1. CRUD for routine library items.
2. Drag or sort ordering using `display_order`.
3. Draggable items for assignment buckets.

Implementation Notes
1. Implement in `app/Livewire/Admin/Routines/Index.php` and its view.
2. Use Livewire for create, update, delete, and reorder actions.
3. Keep the library list optimized for drag and drop.

Tests
1. Add Livewire tests for creating, updating, deleting, and reordering library items.

Acceptance Criteria
1. Library items can be created, edited, deleted, and reordered.
2. Order persists across reloads.
3. Tests pass.

Out of Scope
1. Assignment buckets and drag targets.
