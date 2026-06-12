# Admin Dev Tools

The admin Dev Tools screen is a view-only browser editor for testing Livelatch Studio design tokens before asking Codex to make source changes.

Route:

```text
GET /admin/dev-tools
```

View:

```text
resources/views/studio/admin/dev-tools.blade.php
```

Sidebar entry:

```text
app/Http/Livewire/StudioNavigation.php
```

The route is registered inside the existing admin middleware group in `routes/web.php`, so only authenticated admin users can open it.

## What It Edits

The page temporarily changes CSS variables on `document.documentElement` and injects a temporary browser-only style tag. It does not POST, write files, update the database, or persist changes. Temporary values are cleaned up when leaving the page through HTMX navigation or a full browser unload.

The current editor covers Studio-facing tokens such as:

```css
--ll-bg
--ll-bg-soft
--ll-surface-solid
--ll-text
--ll-muted
--ll-primary
--ll-primary-2
--ll-primary-3
--ll-radius
--ll-button-radius
```

It also previews heading and button font-weight changes through temporary CSS rules.

## Light And Dark Mode

The editor keeps separate colour drafts for light and dark mode. The mode toggle uses the same `data-ll-theme` attribute as the rest of the Studio layout, so the preview matches the real dashboard theme mechanism.

The current version is explicit and guided:

- Light and dark token values are edited independently.
- The guided setup asks for 3 identity colours:
  - Identity Primary
  - Identity Secondary
  - Identity Accent
- The guided setup also asks for both primary font colours:
  - Light mode primary font (`--ll-text` for light)
  - Dark mode primary font (`--ll-text` for dark)
- `Apply guided starter palette` seeds both modes from those identity values using a stable base palette.
- `Copy active mode to opposite` mirrors one mode into the other on demand.

This makes dark-mode drafting predictable instead of inferred.

## Generated Codex Instructions

The `Generate Codex Instructions` button writes a prompt into the page textarea. The prompt lists light-mode colour values, dark-mode colour values, and shared shape/type values, then tells Codex to apply the approved changes to:

```text
resources/views/layouts/sidebar.blade.php
```

The generated prompt also reminds Codex that public profile theme presets are separate. Public profile colours are resolved through `ThemeService`, `ThemeSeeder`, and `resources/views/linkstack/modules/theme.blade.php`.

## Editing The Tool

To add another preview control:

1. Add an input in `resources/views/studio/admin/dev-tools.blade.php` with `data-token`.
2. Use `data-unit="px"` for pixel-based range controls.
3. Use `data-mode="light"` or `data-mode="dark"` when a token should target a specific mode only.
4. If the token needs extra selectors, update the temporary style block created by `ensureTemporaryStyle()`.
5. Keep the tool view-only unless the product decision changes.

Useful references:

- [CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
