# Ticket: Phase 5.3 Weather Settings Admin Page

Goal
Build the admin weather settings page to configure location, units, widget size, and display toggles.

Scope
1. Livewire component at `/admin/weather`.
2. Location search with autocomplete using `WeatherService::searchLocation()`.
3. Settings for units, widget size, feels-like toggle, and precipitation alerts.
4. Persist settings via `SettingService` and the `settings` table.

Implementation Notes
1. Debounce search input to avoid excessive API calls.
2. Store settings with clear keys such as `weather.location`, `weather.units`.
3. Use validation for settings inputs.

Tests
1. Add Livewire tests for updating settings.
2. Add a test for location search results using HTTP fakes.

Acceptance Criteria
1. Admin can configure weather settings and see them persist.
2. Settings are stored in the `settings` table via the service.
3. Tests pass.

Out of Scope
1. Dashboard widget integration.
