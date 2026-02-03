# Ticket: Phase 3.1 Admin Layout and Routes

Goal
Create the admin layout, navigation shell, and routes for admin pages.

Scope
1. `resources/views/layouts/admin.blade.php` with light theme and sidebar nav.
2. Admin routes for children, departures, events, routines, and weather.
3. Create stub Livewire components for routines and weather pages if they do not exist.

Implementation Notes
1. Use `php artisan make:livewire --no-interaction` for new admin components.
2. Follow existing route patterns in `routes/web.php`.
3. Keep the layout minimal and ready for later expansion.

Tests
1. Add a feature test that asserts each admin route returns 200.

Acceptance Criteria
1. All admin routes resolve and render a component.
2. Layout renders with a sidebar nav.
3. Route tests pass.

Out of Scope
1. CRUD functionality for any admin page.
