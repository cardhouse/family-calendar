# Ticket: Phase 2.3 Dashboard Header Component

Goal
Build the dashboard header component with a real-time clock, a weather slot, and a departure countdown display.

Scope
1. `resources/views/livewire/dashboard/header.blade.php` layout and styles.
2. Real-time clock with second updates via Alpine.
3. Departure countdown display with urgency color states.

Implementation Notes
1. Create a Livewire child component if needed for separation.
2. Keep the weather slot as a placeholder for later integration.
3. Follow the color thresholds from the plan.

Tests
1. Add a Livewire feature test that renders the header and shows a formatted time and departure label when data is present.

Acceptance Criteria
1. Clock updates every second without a full page reload.
2. Countdown changes color based on remaining time.
3. Component test passes.

Out of Scope
1. Weather integration logic.
2. Footer event list.
