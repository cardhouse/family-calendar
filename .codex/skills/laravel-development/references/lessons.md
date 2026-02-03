# Lessons Learned (Laravel Development)

Use this file to keep short, dated reminders about what to do and what to avoid.

## Do

- 2026-02-03: Add `declare(strict_types=1);` to all PHP files for consistent typing.
- 2026-02-03: Prevent lazy loading in non-production with `Model::preventLazyLoading()`.

## Don't

- 2026-02-03: Do not `Cache::forget()` before `Cache::put()` when updating settings; `put()` overwrites and avoids race windows.

## Notes

- Keep entries short (1 to 2 sentences).
- Include the date (YYYY-MM-DD) and a short context.
