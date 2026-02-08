# Front-End Verification Guidelines

-   Use Pest Browser tests for UI smoke coverage whenever dashboard or component visuals change.
-   Capture screenshots during front-end work to validate layout, spacing, and regressions before finalizing.
-   For visual checks, include at least one light-mode and one dark-mode screenshot when relevant.
-   Treat screenshots as a debugging and QA tool: compare before/after when changing Livewire or Flux UI interactions.
-   Keep screenshot artifacts out of git by honoring `/tests/Browser/Screenshots` in `.gitignore`.
