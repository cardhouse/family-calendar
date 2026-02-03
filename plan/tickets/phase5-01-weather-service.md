# Ticket: Phase 5.1 Weather Service

Goal
Create a WeatherService that uses the Open-Meteo APIs with caching and graceful fallback.

Scope
1. `app/Services/WeatherService.php` with `getCurrentWeather()` and `searchLocation()`.
2. Geocoding via `https://geocoding-api.open-meteo.com/v1/search`.
3. Forecast via `https://api.open-meteo.com/v1/forecast`.
4. Cache for 20 minutes and fallback to stale cache on API errors.

Implementation Notes
1. Use Laravel HTTP client with timeouts and error handling.
2. Centralize cache keys and TTL constants.
3. Keep service methods deterministic for testing with faked HTTP responses.

Tests
1. Add unit tests using HTTP fakes for geocoding and forecast calls.
2. Add a test ensuring stale cache is used on failure.

Acceptance Criteria
1. Service returns structured weather data.
2. Cache behavior matches the plan.
3. Tests pass.

Out of Scope
1. UI components or admin settings.
