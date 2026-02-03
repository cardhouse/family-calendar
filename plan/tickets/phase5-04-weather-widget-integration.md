# Ticket: Phase 5.4 Weather Widget Integration

Goal
Render the weather widget in the dashboard header using the configured settings.

Scope
1. Include the weather widget in the dashboard header slot.
2. Pass size and display options based on settings.
3. Ensure no layout regressions on mobile and desktop.

Implementation Notes
1. Retrieve settings with `SettingService` or `Setting` model helpers.
2. Use sensible defaults if settings are missing.

Tests
1. Add a feature test verifying the widget renders when enabled.

Acceptance Criteria
1. Weather widget appears in the header with the correct size.
2. Defaults apply when settings are missing.
3. Test passes.

Out of Scope
1. Weather service or admin settings implementation.
