# Ticket: Phase 2.7 Alpine Time Helpers

Goal
Create shared Alpine helpers for the clock, departure countdown, and event countdown.

Scope
1. `clock()` helper that updates every second.
2. `countdown(targetTimestamp)` helper with urgency color states.
3. `eventCountdown(targetTimestamp)` helper that updates every minute.

Implementation Notes
1. Place helpers in `resources/js/app.js` or an existing Alpine utilities file.
2. Keep helpers free of Livewire-specific assumptions.
3. Avoid global namespace collisions by namespacing when possible.

Tests
1. Add a minimal JS test only if the project has JS testing set up.
2. Otherwise, add a Livewire feature test that asserts the data attributes are present for Alpine initialization.

Acceptance Criteria
1. Helpers are available to dashboard components.
2. Countdown behavior matches the plan thresholds.
3. Dashboard renders without JS console errors.

Out of Scope
1. Weather widgets and admin UI.
