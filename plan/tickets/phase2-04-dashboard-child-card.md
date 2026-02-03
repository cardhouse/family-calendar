# Ticket: Phase 2.4 Dashboard Child Card Component

Goal
Implement the per-child checklist card with progress tracking, toggleable items, and a celebration message when complete.

Scope
1. Livewire component for child cards.
2. Display routine items with completion state and progress bar.
3. Actions to toggle completion and persist routine completions.
4. Celebration message when all items are complete.

Implementation Notes
1. Use `RoutineCompletion` with unique date constraint.
2. Keep completion logic in Livewire actions and validate inputs.
3. Use ordered assignments for consistent UI.

Tests
1. Add Livewire tests for toggling an item and verifying completion persistence.
2. Add a test for the completion progress calculation.

Acceptance Criteria
1. Toggling an item creates or removes a completion for today.
2. Progress updates immediately in the UI.
3. Celebration message appears when all items are complete.
4. Tests pass.

Out of Scope
1. Show or hide completed toggle.
2. Drag and drop assignment management.
