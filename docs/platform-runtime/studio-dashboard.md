# Studio Dashboard

The Studio dashboard is the first authenticated page for most Livelatch users and is now intentionally self-contained so it remains stable even if legacy analytics services are unavailable.

## Route And Rendering Flow

```text
GET /dashboard
-> App\Http\Controllers\AdminController@index
-> resources/views/panel/index.blade.php
-> <livewire:dashboard-analytics />
-> app/Http/Livewire/DashboardAnalytics.php
-> resources/views/livewire/dashboard-analytics.blade.php
```

The controller now returns the dashboard view directly and does not pre-build legacy statistics payloads.

## Why This Changed

Older dashboard behavior depended on a larger server-side analytics chain before the page could render. This made the post-login experience fragile and harder to reason about.

The current approach keeps startup logic in one Livewire component with local sample data and direct quick links, matching the structure used by newer Studio pages.

## Current Dashboard Structure

The Livewire dashboard view includes:

- a hero section with the signed-in handle
- quick links for common actions:
  - Manage links
  - Edit profile
  - Theme settings
  - View public page
- sample analytics cards (overview metrics)
- sample activity rows
- sample traffic and source-mix bar sections

The screen is designed as a reliable starter surface while deeper analytics can be introduced through targeted components later.

## Theme And Token Usage

The dashboard uses the Studio `--ll-*` design tokens already defined in the sidebar layout:

```css
--ll-primary
--ll-primary-2
--ll-bg
--ll-bg-soft
--ll-surface-solid
--ll-text
--ll-muted
--ll-border
--ll-radius
```

This keeps the dashboard compatible with both light and dark mode and with Dev Tools token experiments.

## Editing Notes

- Add or change dashboard data defaults in `app/Http/Livewire/DashboardAnalytics.php`.
- Update markup/styles in `resources/views/livewire/dashboard-analytics.blade.php`.
- Keep links HTMX-aware for internal Studio pages (`hx-get`, `hx-target`, `hx-indicator`) and use standard external links for public profile opens.
- Avoid reintroducing controller-side dependencies for basic page render unless there is a clear reliability and performance plan.

## Validation

For dashboard-only edits, run:

```bash
php -l app/Http/Livewire/DashboardAnalytics.php
php -l app/Http/Controllers/AdminController.php
```

If a full Laravel runtime is available in the environment, also run:

```bash
php artisan view:cache
```