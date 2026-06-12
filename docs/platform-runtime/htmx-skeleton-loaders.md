# HTMX Skeleton Loaders

Livelatch Studio uses [HTMX](https://htmx.org/) to swap dashboard screens into the persistent sidebar layout without a full page reload. Screen-level transitions now use themed skeleton loaders instead of a generic spinner.

HTMX supports this through [`hx-indicator`](https://htmx.org/attributes/hx-indicator/). During a request, HTMX adds the `htmx-request` class to the configured indicator element. The Studio layout uses that class to reveal the right skeleton overlay while keeping the previous screen in place until the new screen is ready.

## Shared Partials

Reusable skeleton markup lives in:

```text
resources/views/components/skeleton/page.blade.php
resources/views/components/skeleton/card-grid.blade.php
resources/views/components/skeleton/table.blade.php
resources/views/components/skeleton/profile.blade.php
```

The partials are visual only. They use `aria-hidden="true"` so placeholder rows and cards are not announced by screen readers.

The shared CSS lives in:

```text
resources/views/layouts/sidebar.blade.php
```

The CSS uses `.ll-skeleton`, shimmer animation, and the existing Studio theme variables:

```css
--ll-background
--ll-bg
--ll-text
--ll-primary
--ll-button-radius
```

This keeps skeletons compatible with light mode, dark mode, and future Livelatch Default theme overrides.

## Studio Shell

The main shell keeps all skeleton indicators beside the content target:

```blade
<div id="ll-page-skeleton" class="htmx-indicator ll-content-skeleton" aria-hidden="true">
    <div class="ll-content-skeleton-shell">
        @include('components.skeleton.page')
    </div>
</div>

<section id="ll-content" class="ll-content" aria-live="polite" aria-busy="false">
    @yield('content')
</section>
```

`#ll-content` remains the only screen swap target. The skeleton is an overlay, so the old content stays visible underneath until the new response swaps in.

## Navigation Metadata

Sidebar links are rendered by:

```text
app/Http/Livewire/StudioNavigation.php
resources/views/livewire/studio-navigation.blade.php
```

Each nav item can choose the closest skeleton type:

```php
[
    'label' => 'Cards',
    'icon' => 'bi bi-card-image',
    'url' => url('/studio/latchdeck/cards'),
    'active' => request()->is('studio/latchdeck/cards'),
    'skeleton' => '#ll-card-grid-skeleton',
]
```

If no skeleton is specified, the link falls back to `#ll-page-skeleton`.

## Current Coverage

The first implementation covers:

- Dashboard and generic Studio page swaps with the page skeleton
- Theme, appearance, links, and profile-style screens with the profile skeleton
- LatchDeck overview, cards, settings, and Community Socials with the card grid skeleton
- LatchDeck redemptions, subscriptions, and admin tables with the table skeleton

The current Studio theme preview is client-side JavaScript, and the public profile preview on the Links page is an iframe refresh. They are not HTMX swaps yet. When either preview becomes HTMX-driven, add an `hx-indicator` pointing at `#ll-profile-skeleton` or a more specific local indicator.

## Adding A New Skeleton

1. Create a Blade partial under `resources/views/components/skeleton/`.
2. Add shared styles using `.ll-skeleton` and existing `--ll-*` variables.
3. Add an indicator wrapper in `resources/views/layouts/sidebar.blade.php`.
4. Point HTMX links or forms at the indicator with `hx-indicator`.

Avoid using skeletons for small button actions that do not navigate to a new screen. Keep the existing content in place and use a subtle loading state for those requests.
