# Dashboard Analytics Data (Current + Planned)

The Studio dashboard currently renders through the Livewire analytics component:

```text
resources/views/panel/index.blade.php
-> app/Http/Livewire/DashboardAnalytics.php
-> resources/views/livewire/dashboard-analytics.blade.php
```

## Current status

The dashboard is intentionally running on sample metrics so the `/dashboard` screen stays stable while the analytics pipeline is being reworked.

The UI includes a disclaimer:

```text
Sample analytics data only — Latchalytics is coming soon.
```

This placeholder mode prevents data-provider failures from breaking Studio navigation and login redirects.

## How native analytics was previously gathered

Before the sample-data fallback, dashboard cards were assembled in:

```text
app/Http/Controllers/AdminController.php
```

Main sources were:

- `links` table counts and click totals from `App\Models\Link`
- top-link ranking from `links.click_number`
- account totals and activity windows from `App\Models\User`
- profile visit windows through the `visits(...)` tracker helper

That approach provided real metrics, but it also introduced runtime fragility when analytics providers or tracker assumptions failed.

## Current sample-data structure

`AdminController::index()` now passes:

- sample link totals and click totals
- sample top links
- sample visit windows (`day`, `week`, `month`, `year`)
- sample site and user activity windows
- explicit sample-mode flags for the Livewire view (`isSampleData`, `analyticsNotice`)

The Livewire component still renders real dashboard cards/charts (bars, top-link rows, activity windows), but with controlled sample values.

## Planned rewire (PostHog + Latchalytics)

The intended direction is to replace sample payloads with a dedicated analytics service that reads from PostHog-backed events and internal Latchalytics transforms.

Recommended next step:

1. Create a dashboard analytics service layer that returns a typed payload for Livewire.
2. Keep `DashboardAnalytics` view contracts stable.
3. Switch the service source from sample fixtures to PostHog queries when production metrics are ready.

This allows the dashboard UI to stay stable while analytics storage and event mapping evolve.