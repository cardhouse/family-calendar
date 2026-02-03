# Ticket: Phase 1.4 Setting Service

Goal
Create a dedicated `SettingService` for cached settings access and expose a clean API for reading and writing settings values.

Scope
1. `app/Services/SettingService.php` with `get(string $key, mixed $default = null): mixed` and `set(string $key, mixed $value): void`.
2. Uses cache with a sensible TTL and cache invalidation on update.
3. Integrates with the `Setting` model as the data source.

Implementation Notes
1. Follow the project service class conventions.
2. Use Laravel cache and avoid `env()` outside config files.
3. Keep cache keys consistent and centralized.

Tests
1. Add unit tests verifying cached reads, writes, and invalidation behavior.

Acceptance Criteria
1. Service can read and write settings values.
2. Cache hits are used when available and invalidated on update.
3. Tests pass.

Out of Scope
1. Admin UI for settings.
2. Weather integration.
