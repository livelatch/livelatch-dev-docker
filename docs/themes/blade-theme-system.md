# Blade Theme System

Livelatch now supports a second, parallel theme system alongside the existing CSS-variable engine: **blade themes**. These are self-contained, full-page profile experiences built with any JavaScript or animation library the author wants — [Three.js](https://threejs.org/), [GSAP](https://gsap.com/), [Anime.js](https://animejs.com/), [WebGL](https://developer.mozilla.org/en-US/docs/Web/API/WebGL_API), etc.

## Why a second system, not a replacement?

The existing CSS-variable system (see `docs/themes/core-theme-engine.md`) handles the standard link-page look: background, colours, fonts. Blade themes go much further — an author can create an entirely different HTML page structure, run a full 3D scene, play animations, and load any CDN library. They are not an upgrade of the CSS system; they are a separate capability for high-creativity profile pages. Both systems will coexist until there is enough content and tool support to migrate fully if that ever makes sense.

## How it works

### Database

A new table `user_blade_theme_settings` records which blade theme a user has active:

| Column | Type | Purpose |
|---|---|---|
| `user_id` | FK (unique) | One row per user |
| `theme_slug` | string | Matches a theme directory name |
| `settings` | JSON, nullable | User's saved control values |

If a user has no row here the system falls through to the existing render. The table was created by migration `2026_06_24_000001_create_user_blade_theme_settings_table`.

### Directory layout

```
resources/
  themes/
    portal/
      manifest.json       ← control declarations and defaults
    another-theme/
      manifest.json

resources/views/
  themes/
    portal.blade.php      ← complete standalone HTML page

assets/
  themes/
    portal/
      portal.js           ← Three.js scene
      style.css           ← layout styles
```

> **Web root note (important).** LinkStack rebinds `path.public` to the **project root** (`bootstrap/app.php`), so `index.php` and all static assets are served from the project root — *not* `public/`. Files placed in `public/…` are **not** web-accessible. Blade theme assets therefore live in **`assets/themes/<slug>/`** (alongside the existing `assets/linkstack/…`), and `asset('assets/themes/<slug>/…')` resolves correctly. The legacy folder-theme system uses the separate root `themes/` directory; keep blade assets under `assets/themes/` to stay clear of it.

To be resilient regardless of how static files are served, the theme blade **inlines its own CSS/JS** by reading them from disk (`public_path('assets/themes/<slug>/…')`, which resolves to the project root) and emitting `<style>`/`<script>` blocks, falling back to `<link>`/`<script src>` tags only if the files can't be read. Third-party libraries (Three.js, GSAP) are still loaded from their CDNs.

## Theme Studio (Beta) — the editing UI

The editing surface for blade themes lives at **`/studio/themes-beta`** (`Studio\BladeThemeController`, view `studio/themes-beta.blade.php`, nav entry "Themes · Beta" under MyLivelatch). It is deliberately **a separate page** from the existing CSS-variable Theme Studio at `/studio/theme` (`Studio\ThemeController`) — that page is left completely untouched while the blade system is built out. The two systems coexist; a user's public profile renders the blade theme when they have a `user_blade_theme_settings` row and falls through to the CSS-variable theme otherwise.

The page is **manifest-driven**: it reads every installed theme's manifest (`ThemeRegistry::all()`) and builds the controls in the browser from `controls`, so a new theme needs no Studio code — only a manifest. Layout:

- **Base themes carousel** — one card per blade theme: preview gradient, name, `@author`, and a **real usage count** (`count(*)` of `user_blade_theme_settings` rows for that slug). A 5-star rating is shown as a **display-only placeholder** ("ratings soon") — no ratings backend exists yet.
- **Colour panel** — renders one swatch + hex field per entry in `controls.colours` (up to four: e.g. Primary / Secondary / Highlight / Text).
- **Typography panel** — a font `<select>` per `controls.typography` slot (heading, body) with a live Heading 1 / Heading 2 / Paragraph preview.
- **Effects panel** — a range slider per `controls.sliders` entry (e.g. particle density, animation speed).
- **Custom CSS panel** — shown only when the manifest sets `controls.customCss.pro`. A textarea, **Pro-gated**: non-Pro users see a locked "Upgrade to Pro" overlay, and the server only persists `customCss` for Pro users.
- **Live preview** — an `<iframe>` of `GET /studio/themes-beta/preview/{slug}`, which renders the **actual theme** (real Three.js/GSAP scene) for the signed-in user's own profile with the current *unsaved* settings passed as query params. The frame is scaled to a selectable device: **iPhone 17 Pro Max (default)**, iPad Pro, or Desktop. Edits debounce-reload the iframe.
- **Apply / Reset** — Apply `POST`s `{ theme_slug, settings }` to `editBladeTheme` (`updateOrCreate` on `user_blade_theme_settings`); Reset `POST`s `resetBladeTheme`, which deletes the row so the profile reverts to the standard theme.

### Settings sanitisation

`BladeThemeController::sanitize()` validates incoming control values **against the selected theme's manifest** before they are saved or previewed — anything not declared, or failing its type rule, is dropped:

- **colours** → must match `#[0-9a-fA-F]{3,8}`
- **typography** → must be one of the slot's declared `options` (or alphanumeric/space when no options are given)
- **sliders** → integer clamped to the declared `min`/`max`
- **customCss** → only when the manifest opts in *and* the user is Pro; angle brackets stripped (the theme also strips them at render time, inside its `<style>` block)

So stored settings are always theme-legal, and the same sanitiser runs for both the live preview and the saved value.

### manifest.json

Each theme ships a manifest that declares everything the Theme Studio needs to generate the editing UI, plus what the blade itself needs to know about its own defaults.

```json
{
  "name": "Portal",
  "slug": "portal",
  "author": "livelatch",
  "authorHandle": "@livelatch",
  "version": "1.1.0",
  "description": "...",
  "preview_gradient": "linear-gradient(135deg, #0d001a, #8b5cf6)",
  "libraries": ["three", "gsap"],
  "defaults": {
    "portalColor": "#8b5cf6",
    "headingFont": "Poppins",
    "particleCount": 800,
    "customCss": ""
  },
  "presets": {
    "default": { "portalColor": "#8b5cf6", "particleCount": 800 },
    "neon":    { "portalColor": "#00ff88", "particleCount": 1200 }
  },
  "controls": {
    "colours": [
      { "key": "portalColor", "label": "Primary" }
    ],
    "typography": {
      "heading": { "key": "headingFont", "label": "Heading", "default": "Poppins", "options": ["Poppins", "Press Start 2P", "Oswald"] },
      "body":    { "key": "bodyFont",    "label": "Body",    "default": "Poppins", "options": ["Poppins", "Inter", "Roboto"] }
    },
    "sliders": {
      "particleCount": { "label": "Particle density", "min": 100, "max": 2000, "step": 100, "default": 800 }
    },
    "customCss": { "pro": true }
  }
}
```

- `defaults` — the values used when a user has saved nothing (or `settings = null`).
- `presets` — named preset sets the Studio can offer as one-click options.
- `controls` — what editing controls to render in the Studio:
  - `colours` — array of `{ key, label }`; rendered as swatch + hex fields (up to four read well in the layout).
  - `typography` — object of slots (`heading`, `body`), each `{ key, label, default, options[] }`; rendered as font selects with a live preview. The theme builds a Google Fonts request from the chosen families.
  - `sliders` — object of `{ label, min, max, step, default }`; rendered as range inputs.
  - `customCss` — `{ "pro": true }` to expose a Pro-gated custom-CSS textarea for the theme.
- `preview_gradient` / `authorHandle` — cosmetic; used by the Studio carousel cards.

### ThemeRegistry service

`App\Services\ThemeRegistry` is the runtime catalogue. It is resolved via `app()` (no explicit binding needed):

```php
$registry = app(\App\Services\ThemeRegistry::class);

$registry->all();                          // all available themes, keyed by slug
$registry->get('portal');                  // one manifest array
$registry->viewExists('portal');           // true when resources/views/themes/portal.blade.php exists
$registry->resolveSettings('portal', $userSettings); // user overrides merged onto defaults
$registry->clearCache();                   // bust the 5-min manifest cache after deploying a new theme
```

### Profile page intercept

`UserController::littlelink()` (and `littlelinkhome()`) checks for a blade theme before the existing render:

```php
$bladeThemeSetting = UserBladeThemeSetting::where('user_id', $id)->first();
if ($bladeThemeSetting) {
    $registry = app(ThemeRegistry::class);
    $slug = $bladeThemeSetting->theme_slug;
    if ($registry->viewExists($slug)) {
        return view("themes.{$slug}", [
            'user'     => $userinfo,
            'links'    => $links,
            'settings' => $registry->resolveSettings($slug, $bladeThemeSetting->settings ?? []),
            'manifest' => $registry->get($slug),
        ]);
    }
}
// existing return view('linkstack.linkstack', ...) unchanged below
```

If `user_blade_theme_settings` has no row for this user, or the view file is missing, execution falls through to the normal render — existing users are untouched.

### Blade theme structure

A blade theme is a standalone full-HTML page. It receives these variables:

| Variable | Type | Contains |
|---|---|---|
| `$user` | User model | `id`, `name`, `littlelink_name`, `littlelink_description`, `theme`, `role`, `block` |
| `$links` | Collection | All of the user's links (same query as the standard page, with `type_params` decoded) |
| `$settings` | array | Resolved settings (manifest defaults merged with user overrides) |
| `$manifest` | array | The full parsed manifest |

The blade should sanitise `$settings` before using values in HTML or CSS. For colours, validate against a hex pattern:

```php
$color = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['portalColor'] ?? '') ? $s['portalColor'] : '#8b5cf6';
```

For numeric values, clamp with `max()` / `min()` / `(int)`.

Use the existing global helper functions for avatar images:

```blade
profileImageUrl($user->id)        {{-- profile avatar --}}
profilePreviewImageUrl($user->id)  {{-- OG/meta preview image --}}
```

## Portal — reference theme

The first blade theme ships as `portal`. It is a good starting point to copy when building a new theme.

**What it does:** renders a full-screen Three.js WebGL scene behind the profile content — a slowly rotating neon ring (torus geometry) with a particle cloud and 4 000-star backdrop. The profile (avatar, name, bio, links) sits above the canvas. GSAP drives the entrance animation sequence.

**Files:**
- `resources/themes/portal/manifest.json` — 3 colour controls, 2 sliders, 4 named presets
- `resources/views/themes/portal.blade.php` — standalone page, loads CDNs + assets
- `assets/themes/portal/portal.js` — `window.PortalTheme` IIFE, `init(opts)` → scene
- `assets/themes/portal/style.css` — dark glassmorphism profile layout

**Presets:**

| Name | Portal colour | Accent |
|---|---|---|
| Default | `#8b5cf6` (purple) | `#0ce5de` (teal) |
| Neon | `#00ff88` (green) | `#ff00aa` (pink) |
| Fire | `#ff4500` (orange) | `#ff8c00` (amber) |
| Ice | `#00d4ff` (cyan) | `#ffffff` (white) |

**CDN dependencies** (loaded inline in the blade — no npm build step):
- Three.js r158 via `unpkg.com`
- GSAP 3.12.2 via `cdnjs.cloudflare.com`

## Other bundled themes

Both ship with **no external JS** — the animation is a self-contained 2D `<canvas>` script inlined from `assets/themes/<slug>/`, and entrance motion is pure CSS. Good templates for a "no-CDN" theme.

- **Frutiger Aero** (`aero`) — glossy 2009-era skies with rising bubbles (2D canvas) and Windows 7 "Aero glass" buttons (vertical gloss gradient + top-half highlight overlay). Controls: Primary/Sky/Aqua/Text colours, heading/body fonts, bubble density + speed sliders. Presets: default, aqua, sunset, forest.
- **Cute** (`cute`) — pastel kawaii: drifting paw prints + twinkling sparkles (2D canvas, paw colour from settings) and chunky candy buttons with a 3D bottom edge and a 🐾 glyph. Controls: Primary/Secondary/Paws/Text colours, heading/body fonts (Mochiy Pop One, Kosugi Maru, Pacifico…), density + drift sliders. Presets: default, sakura, mint, lavender. The background gradient is derived from the Primary/Secondary colours via `color-mix`, so it stays pastel for any palette.

> **Google Fonts caveat.** Some display/Japanese families (Mochiy Pop One, Pacifico, Kosugi Maru) ship a **single weight**. Requesting an unavailable weight (`:wght@700`) makes the whole CSS2 request 400. So `aero`/`cute` build their font URL with **no weight axis** (`family=Name` only) — variable fonts still serve their full range, single-weight fonts just work. (Portal keeps explicit weights because Poppins/Oswald support them.)

## Activating a blade theme for a user

Insert a row in `user_blade_theme_settings`:

```sql
INSERT INTO user_blade_theme_settings (user_id, theme_slug, settings, created_at, updated_at)
VALUES (123, 'portal', NULL, NOW(), NOW());
```

`settings = NULL` → manifest defaults. To override specific controls:

```sql
-- Neon preset values
INSERT INTO user_blade_theme_settings (user_id, theme_slug, settings, created_at, updated_at)
VALUES (123, 'portal', '{"portalColor":"#00ff88","accentColor":"#ff00aa","particleCount":1200,"animationSpeed":80}', NOW(), NOW());
```

To revert a user to the standard theme, delete their row:

```sql
DELETE FROM user_blade_theme_settings WHERE user_id = 123;
```

## Rendering links and blocks

A profile is more than buttons — LinkStack supports **block types** (`spacer`, `text`, `heading`, `email`, `telephone`, `vcard`, `link`) stored with a `custom_html` flag and rendered through the `blocks::` view namespace (registered in `AppServiceProvider`). A theme that only renders plain link anchors will **silently drop every block** (spacers, text, headings…), because those items have `custom_html` set and usually no `->link` URL.

To render everything, themes delegate the link area to a shared partial instead of hand-rolling the loop:

```blade
<nav class="pt-links">
  @include('themes.partials.links', ['links' => $links, 'linkClass' => 'pt-link'])
</nav>
```

`resources/views/themes/partials/links.blade.php` mirrors the standard renderer: `custom_html` items go through `@includeIf('blocks::' . $link->type . '.display', …)` (wrapped in `.ll-theme-block`, with `--ll-i` index for staggered CSS), plain links/vcards render as themed `<a class="{linkClass} button-click">`, and it carries the same click-tracking script. For block libraries to load, the theme's `<head>` must also include:

```blade
@include('linkstack.modules.block-libraries', ['links' => $links])
@stack('linkstack-head')
```

and `@stack('linkstack-body-end')` before `</body>`. (block-libraries `@push`es to those stacks, so the include must run **before** `@stack('linkstack-head')` renders — hence it lives in `<head>`.) Style the block elements per theme via `.<prefix>-links .ll-theme-block` (text, `h2`, `.button-spacer`).

## Writing a new blade theme

1. Create `resources/themes/<slug>/manifest.json` following the schema above.
2. Create `resources/views/themes/<slug>.blade.php` — a full `<!DOCTYPE html>…</html>` page. Render the link area with `@include('themes.partials.links', …)` (see above) so all block types work.
3. Put static assets in `assets/themes/<slug>/` (project-root web dir — **not** `public/`). Inline them from the blade via `public_path('assets/themes/<slug>/...')`, or reference them with `asset('assets/themes/<slug>/...')`.
4. Call `app(ThemeRegistry::class)->clearCache()` (or wait up to 5 minutes) for the new theme to appear in `ThemeRegistry::all()`.

Themes authored by Alex or trusted creators are stored in the app filesystem and deployed with the app. Third-party or user-uploaded themes are not supported in the current architecture.
