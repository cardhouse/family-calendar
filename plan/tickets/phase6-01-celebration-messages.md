# Ticket: Phase 6.1 Celebration Messages and Confetti

Goal
Add randomized celebration messages and optional confetti when a child completes all routine items.

Scope
1. Add a list of celebration messages in the child card component.
2. Display a random message when progress reaches 100%.
3. Optional confetti animation (Alpine or CSS) gated behind a simple flag.

Implementation Notes
1. Keep messages configurable in the component class.
2. Ensure the message does not flash repeatedly on every render.
3. Keep animations lightweight for older tablets.

Tests
1. Add a Livewire test that asserts a celebration message appears when all items are complete.

Acceptance Criteria
1. A message appears only when a child completes all items.
2. Animation does not degrade performance.
3. Tests pass.

Out of Scope
1. Global settings for messages.
