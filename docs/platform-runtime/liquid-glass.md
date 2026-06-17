# Liquid Glass

Liquid Glass is an **optional, additive visual layer** for the Livelatch Studio. It frosts existing surfaces (sidebar, topbar, cards, hero, panels) with a translucent blur when enabled. It is purely cosmetic — it does not change layout, data, or behaviour.

It is intentionally built to be **isolated and easy to remove**, so it can be switched off as a feature or deleted entirely without touching core code.

## How it works

- A toggle on the admin **Dev Tools** page (`/admin/dev-tools`) turns it on or off.
- The state is persisted to `localStorage` under the key `livelatch-glass`, exactly like the light/dark theme toggle, so it survives reloads and navigation.
- When on, the `<html>` element gets `data-ll-glass="on"`. All styling is scoped to that attribute.
- The effect respects `prefers-reduced-transparency: reduce` and falls back to solid surfaces.

## Where it lives

```text
resources/views/layouts/liquid-glass.blade.php   # the entire feature: CSS + toggle logic
resources/views/layouts/sidebar.blade.php        # one @include('layouts.liquid-glass') line
resources/views/studio/admin/dev-tools.blade.php # the "Liquid glass" toggle button + handler
```

The partial exposes a small global helper for the toggle:

```js
window.LivelatchGlass.isOn();   // boolean
window.LivelatchGlass.set(true) // on/off + persist
window.LivelatchGlass.toggle(); // flip + persist, returns new state
```

## Removing it cleanly

If you want it gone, do all four steps:

1. Delete `resources/views/layouts/liquid-glass.blade.php`.
2. Remove the `@include('layouts.liquid-glass')` line in `resources/views/layouts/sidebar.blade.php`.
3. Remove the **Liquid glass** toggle button (`#ll-dev-glass`) and its click handler / `syncGlassButton()` calls in `resources/views/studio/admin/dev-tools.blade.php`.
4. Delete this file.

Optionally clear the stored preference in the browser: `localStorage.removeItem('livelatch-glass')`.

After removing, run:

```bash
php artisan view:cache
```

Because the whole feature is gated behind `[data-ll-glass="on"]` and lives in one partial, removing it leaves no dead styles on the default Studio.
