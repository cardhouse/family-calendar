# Ticket: Phase 2.1 Dashboard Layout and Route

Goal
Create the primary dashboard layout and route for the family morning dashboard.

Scope
1. `resources/views/layouts/app.blade.php` with full-viewport dark theme base styling.
2. Includes Livewire assets and Alpine support per project conventions.
3. Route `/` pointing to the main Livewire dashboard component.

Implementation Notes
1. Use `php artisan make:livewire --no-interaction Dashboard` to create the component if it does not exist.
2. Check existing layout patterns in `resources/views/layouts` for consistency.
3. Keep the layout minimal and leave UI structure for later tickets.

Tests
1. Add a feature test that asserts the dashboard route returns 200 and renders the Livewire component.

Acceptance Criteria
1. Visiting `/` renders the dashboard Livewire component inside the dark layout.
2. Livewire assets load without errors.
3. The route test passes.

Out of Scope
1. Data loading or UI details.
2. Admin routes.
