# Ticket: Phase 2.6 Show/Hide Completed Toggle

Goal
Add the show or hide completed items toggle with localStorage persistence and a Livewire event to update all child cards.

Scope
1. Toggle control in the dashboard UI.
2. Persist preference in localStorage.
3. Broadcast a Livewire event to update all child card components.

Implementation Notes
1. Use Alpine for localStorage read and write.
2. Use Livewire event dispatching to notify child components.
3. Ensure toggle state is applied on initial page load.

Tests
1. Add a Livewire test verifying the event is handled and completed items visibility updates.

Acceptance Criteria
1. Toggle persists across page reloads.
2. All child cards update when toggle changes.
3. Test passes.

Out of Scope
1. Admin routines management.
