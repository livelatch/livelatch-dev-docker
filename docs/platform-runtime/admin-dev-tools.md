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

## Approved Studio Baseline Tokens

When a draft is approved, token values are copied into the Studio layout source at:

```text
resources/views/layouts/sidebar.blade.php
```

The current approved implementation also keeps the preview weight controls as stable Studio tokens:

```css
--ll-dev-heading-weight
--ll-dev-button-weight
```

Those values drive the default heading and button weights in the Studio shell.

## Light And Dark Mode

The editor keeps separate colour drafts for light and dark mode. The mode toggle uses the same `data-ll-theme` attribute as the rest of the Studio layout, so the preview matches the real dashboard theme mechanism.

When a colour is changed in the active mode, the opposite mode is auto-generated from the same hue with mode-appropriate lightness. For example, editing a dark-mode background creates a lighter matching background draft for light mode. Radius and font-weight controls are shared across both modes.

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
3. If the token needs extra selectors, update the temporary style block created by `ensureTemporaryStyle()`.
4. Keep the tool view-only unless the product decision changes.

Useful references:

- [CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
