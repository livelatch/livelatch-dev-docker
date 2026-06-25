# Block System

Livelatch uses the inherited [LinkStack](https://linkstack.org/) block model for profile content. Blocks are file-based modules stored under `blocks/`, then selected from the Studio add-block page.

## Current Studio Flow

Blocks are added from the combined Links manager:

```text
GET /studio/links
-> UserController::showLinks()
-> resources/views/studio/links.blade.php
```

This screen shows:

- the current link/block list
- drag-and-drop rearranging
- an add-block section
- a phone-sized live preview of the public profile

The older add-only modal flow has been replaced in the sidebar by the combined Links page. The old `/studio/add-link` route may still exist for compatibility, but normal navigation should point users to `/studio/links`.

When a user selects a block, the page loads the matching settings form from:

```text
GET /studio/linkparamform_part/{typename}/{linkid}
-> LinkTypeViewController::getParamForm()
-> blocks/{typename}/form.blade.php
```

Saving a new block posts to:

```text
POST /studio/edit-link
-> UserController::saveLink()
```

For custom block types, `saveLink()` includes:

```php
blocks/{typename}/handler.php
```

and calls:

```php
handleLinkType($request, $linkType)
```

The handler returns validation rules and the prepared link data. After saving, the user returns to `/studio/links`, where the new item appears in the current links list and can be rearranged.

## Rearranging Blocks

The current links list on `/studio/links` uses [SortableJS](https://sortablejs.github.io/Sortable/) through the bundled `assets/js/Sortable.min.js` file.

When a user drags items, the page posts the new order to:

```text
POST /studio/sort-link
-> UserController::sortLinks()
```

The `links.order` field is updated and the phone preview is refreshed.

## Live Preview

The combined Links manager embeds the public profile in an iframe:

```text
/@{littlelink_name}
```

The preview uses the **same device switcher as the Theme Studio** (iPhone 17 Pro Max / iPad Pro / Desktop): a pixel-sized, scaled bezel frame with a `label · w × h` readout (`.ll-lp-*` classes mirroring the Studio's `.ll-bt-*`). It is read-only, refreshes after successful reorder actions, and can be refreshed manually from the preview panel.

## Block Folder Shape

A normal block folder looks like this:

```text
blocks/example/
  config.yml
  form.blade.php
  handler.php
  display.blade.php
```

The current block folders include:

- `blocks/link` — the unified **Links** block (see below)
- `blocks/email`
- `blocks/heading`
- `blocks/text`
- `blocks/spacer`
- `blocks/latchdeck` — boilerplate LatchDeck card section (see below)
- `blocks/telephone` — **hidden** (legacy, kept for existing links)
- `blocks/vcard` — **hidden** (legacy, kept for existing links)

`predefined` is special. It is added in code by `App\Models\LinkType::get()` and uses `resources/views/components/pageitems/predefined-form.blade.php` instead of a folder under `blocks/`. It is now **hidden** — its functionality is merged into the unified Links block.

## Block visibility (the `hidden` flag)

`App\Models\LinkType::get()` exposes a `hidden` flag per block, read from `hidden: true` in a block's `config.yml` (or set in code for the hardcoded `predefined` entry). Hidden blocks:

- are **filtered out of the Studio "Add a block" picker** (`links.blade.php` does `$blockCards = $LinkTypes->filter(fn ($lt) => !$lt->hidden)`),
- but remain fully **resolvable** by `findByTypename()`, `getParamForm()`, and `blocks::{type}.display`, so any links already created with that type keep rendering and stay editable.

This is the non-destructive way to retire a block: set `hidden: true` rather than deleting the folder. `predefined`, `vcard`, and `telephone` are hidden this way. The visible order is controlled by `$custom_order` in `LinkType::get()`: `link, email, heading, spacer, text, latchdeck` (hidden types sort to the end).

## Unified "Links" block (Simple Icons)

`blocks/link/form.blade.php` merges the old *Predefined* and *Custom Link* types into one block:

- a **Platform** `<select>` — `Custom` plus a curated list of creator platforms, each carrying a [Simple Icons](https://simpleicons.org/) `slug` + brand `hex` as `data-` attributes;
- for **Custom**, a searchable **Simple Icons picker** that filters the bundled library and also accepts any free-typed slug (live-previewed via `https://cdn.simpleicons.org/<slug>`);
- the legacy "use the website favicon" option still maps to `button_id` 2.

The form is injected into `#link_params` via AJAX, so its interactivity lives in the **parent** `resources/views/studio/links.blade.php` and re-initialises on the `contentLoaded` event (inline `<script>` in an AJAX-injected partial does not execute). The bundled icon library travels with the form as a `<script type="application/json" data-ll-icon-library>` blob.

The chosen icon is stored in `links.custom_icon` as `si:<slug>` — **identity only**. Whether icons show, and what colour they are, is a **theme setting** (see below), not per-link. Rendering detects the `si:` prefix:

- public profile: `resources/views/linkstack/elements/buttons.blade.php` (the `custom` case) emits a `<span class="icon ll-si-icon">` whose `-webkit-mask`/`mask` is the Simple Icons SVG, so the glyph is tinted by `background-color` (a CSS mask, not a coloured image);
- Studio list row: `links.blade.php` shows a small `cdn.simpleicons.org` preview.

Legacy FontAwesome `custom_icon` values (e.g. `fa-...`) still render through the old `<i class="fa …">` path; an empty `custom_icon` renders no icon. `UserController::saveLink()` persists `custom_icon` for the `link` type (unless the favicon option is used); `LinkTypeViewController::getParamForm()` passes `custom_icon` back for editing.

### Icon visibility + colour (theme settings)

Link-button icons are controlled from the **Themes page** (`/studio/theme`), not per-link, via two `custom_settings` carried through `App\Services\ThemeService` and its presets:

- `showIcons` (`'1'`/`'0'`, default `'1'`) — turns icons on/off for all link buttons.
- `iconColor` (hex, default empty) — empty means **match the button text colour**; a hex tints the masked Simple Icons.

These resolve via `ThemeService::resolvePublicPreset()` (defaults ← preset ← custom) and are emitted by `resources/views/linkstack/modules/theme.blade.php`: it sets `--ll-icon-color` (only when a colour is chosen; `.ll-si-icon` falls back to `currentColor`) and, when `showIcons` is off, hides `.container .button .icon, .container .button .ll-si-icon`. Presets in a theme version's `manifest.presets` may set `showIcons`/`iconColor` so different presets ship different icon treatments. Validation lives in `ThemeController::update()` and `ThemeService::cleanCustomSettings()`.

## LatchDeck block

`blocks/latchdeck/` (`custom_html: true`) is a single on/off toggle — adding the block turns the section on, removing it turns it off. `display.blade.php` is a self-contained, theme-adaptive (currentColor-based) boilerplate card section: a shimmering 3-card placeholder + a "cards coming soon" empty state, ready to render real cards once LatchDeck card data exists. It renders in both classic themes (via the buttons loop) and blade themes (via `resources/views/themes/partials/links.blade.php`).

## Editing a Block

To edit how a block appears in the Studio form, update:

```text
blocks/{typename}/form.blade.php
```

To edit how submitted values are validated or saved, update:

```text
blocks/{typename}/handler.php
```

To edit how a custom HTML block appears on the public profile, update:

```text
blocks/{typename}/display.blade.php
```

Blocks with `custom_html: true` in `config.yml` render through their `display.blade.php` file on the public profile. Blocks with `custom_html: false` generally render through the standard LinkStack button renderer.

## Adding a Block

1. Create a new folder:

```text
blocks/my-block/
```

2. Add `config.yml`:

```yaml
id: 20
typename: my-block
icon: "bi bi-stars"
custom_html: true
```

Use a unique `typename`. The folder name, `typename`, and view references should match.

3. Add `form.blade.php` with the Studio fields:

```php
<label for="title" class="form-label">Title</label>
<input type="text" name="title" value="{{ $title }}" class="form-control" required>
```

4. Add `handler.php`:

```php
<?php

function handleLinkType($request, $linkType) {
    $rules = [
        'title' => ['required', 'string', 'max:255'],
    ];

    $linkData = [
        'title' => $request->title,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
```

Values matching columns in the `links` table are saved directly. Extra values are JSON encoded into `links.type_params`.

5. If `custom_html: true`, add `display.blade.php`:

```php
<div class="fadein">
    <h2>{{ $link->title }}</h2>
</div>
```

6. If the block needs assets, reference them from the block folder with:

```php
{{ block_asset('asset-name.css') }}
```

`block_asset()` serves files through:

```text
GET /block-asset/{type}?asset=...
```

Allowed asset extensions are controlled in `LinkTypeViewController::blockAsset()`.

## Removing a Block

**Preferred (non-destructive):** set `hidden: true` in the block's `config.yml`. It disappears from the Studio picker but stays resolvable, so existing links of that type keep rendering and remain editable. This is how `predefined`, `vcard`, and `telephone` are retired.

To remove a block from the Studio picker entirely, remove or rename its `config.yml`.

To fully remove the block implementation, delete the block folder after confirming no saved links still use that block type.

Before deleting a block that has been used in production, check the `links` table for rows where:

```text
links.type = {typename}
```

If rows exist, either migrate them to another block type or keep the display files available so old profiles do not break.

## Important Files

- `app/Models/LinkType.php` discovers blocks from `blocks/*/config.yml`.
- `app/Http/Controllers/LinkTypeViewController.php` loads block forms and serves block assets.
- `app/Http/Controllers/UserController.php` saves links and calls each block handler.
- `resources/views/studio/edit-link.blade.php` renders the Studio add/edit block interface.
- `resources/views/linkstack/elements/buttons.blade.php` renders profile blocks.

## Validation

After editing block views or handlers, run:

```bash
php artisan view:cache
```

For PHP files, run:

```bash
php -l blocks/{typename}/handler.php
```
