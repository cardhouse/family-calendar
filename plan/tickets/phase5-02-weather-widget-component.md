# Ticket: Phase 5.2 Weather Widget Component

Goal
Create a Livewire weather widget with size variants and polling.

Scope
1. `resources/views/livewire/weather-widget.blade.php` and component class.
2. Support compact, medium, and large sizes.
3. Poll every 15 minutes with `wire:poll.900s`.
4. Graceful fallback UI when weather is unavailable.

Implementation Notes
1. Use `WeatherService` for data retrieval.
2. Respect user settings for unit and display options when available.
3. Keep styles consistent with the dashboard theme.

Tests
1. Add Livewire tests for each size variant and fallback rendering.

Acceptance Criteria
1. Widget renders correctly at all sizes.
2. Polling updates data without full page reload.
3. Tests pass.

Out of Scope
1. Admin settings page.
