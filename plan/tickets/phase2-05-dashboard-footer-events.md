# Ticket: Phase 2.5 Dashboard Footer Events

Goal
Add the dashboard footer component showing the next three upcoming events with countdowns.

Scope
1. `resources/views/livewire/dashboard/footer.blade.php` with event list layout.
2. Countdown display updating every minute via Alpine.
3. Use the `CalendarEvent` upcoming scope to fetch data.

Implementation Notes
1. Limit to three events and include name and time.
2. Handle events without departure time gracefully.

Tests
1. Add a Livewire feature test that renders upcoming events in the correct order.

Acceptance Criteria
1. Footer shows up to three future events.
2. Countdown updates every minute.
3. Test passes.

Out of Scope
1. Admin CRUD for events.
2. Weather integration.
