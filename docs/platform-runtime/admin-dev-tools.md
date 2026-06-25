# Admin Dev Tools

The admin Dev Tools screen is a preview-only editor for hand-crafting the Livelatch Studio design tokens before asking Codex to make source changes.

Route:

```text
GET /admin/dev-tools
```

View:

```text
resources/views/studio/admin/dev-tools.blade.php
```

The route is registered inside the existing admin middleware group in `routes/web.php`, so only authenticated admin users can open it.

## Light and dark, side by side

The editor shows **two live preview stages** at once — one light, one dark — so you can design both Studio themes independently in a single pass. A mode tab (Light / Dark) chooses which set of colour tokens the controls edit; the shape/type tokens are shared across both.

Each preview stage exercises **every** editable token so a change is always visible: the hero/cards cover `--ll-bg`, `--ll-surface-solid`, `--ll-text`, `--ll-muted`, `--ll-primary`/`--ll-primary-2`, `--ll-radius`, `--ll-button-radius` and the heading/button weights; a dedicated "soft surface" row + readonly field cover `--ll-bg-soft`, and an accent pill covers `--ll-primary-3`.

The editor covers:

```css
--ll-primary
--ll-primary-2
--ll-primary-3
--ll-bg
--ll-bg-soft
--ll-surface-solid
--ll-text
--ll-muted
--ll-radius
--ll-button-radius
--ll-dev-heading-weight
--ll-dev-button-weight
```

## Why the preview is scoped (and cannot leak)

Earlier the preview wrote token values and `data-ll-theme` directly onto `document.documentElement`. If its cleanup did not fire on HTMX navigation, those inline overrides leaked onto the real page and the light/dark toggle could get "stuck" (the toggle flipped but the background did not).

The current editor never touches the live document. Instead:

- Baselines for both modes are read from **offscreen probe elements** that carry `data-ll-theme="light"` / `data-ll-theme="dark"`. This works because the Studio stylesheet exposes dark values through the `[data-ll-theme="dark"]` selector, which matches any element.
- Edits are applied as inline CSS variables on the **two preview stage containers only**, not on `:root` or `<body>`.

As defence in depth, the global theme switch in `resources/views/layouts/sidebar.blade.php` also strips any stray inline `--ll-*` overrides and re-asserts the stored theme after every HTMX swap.

## Generated Codex Instructions

The `Generate Codex Instructions` button writes a prompt listing the hand-crafted light values, dark values, and shared shape/type values, then tells Codex to apply them to:

```text
resources/views/layouts/sidebar.blade.php
```

It also reminds Codex that public profile theme presets are separate — those are resolved through `ThemeService`, `ThemeSeeder`, and `resources/views/linkstack/modules/theme.blade.php`.

## Liquid Glass toggle

Dev Tools also hosts the **Liquid glass** toggle, an optional frosted-surface visual layer for the Studio. It is persisted like the light/dark theme and is fully isolated so it can be removed cleanly. See [Liquid Glass](liquid-glass.md).

## Editing the tool

To add another colour control, add an `<input data-token="--ll-...">` (use `data-shared="true"` for tokens that apply to both modes, and `data-unit="px"` for pixel ranges). The script wires it up automatically.

Useful references:

- [CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [Bootstrap Icons](https://icons.getbootstrap.com/)
