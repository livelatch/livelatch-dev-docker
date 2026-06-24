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

public/
  themes/
    portal/
      portal.js           ← Three.js scene
      style.css           ← layout styles
```

Assets in `public/themes/` are served statically; the blade view loads them with `asset()`.

### manifest.json

Each theme ships a manifest that declares everything the Theme Studio needs to generate the editing UI, plus what the blade itself needs to know about its own defaults.

```json
{
  "name": "Portal",
  "slug": "portal",
  "author": "livelatch",
  "version": "1.0.0",
  "description": "...",
  "libraries": ["three", "gsap"],
  "defaults": {
    "portalColor": "#8b5cf6",
    "particleCount": 800
  },
  "presets": {
    "default": { "portalColor": "#8b5cf6", "particleCount": 800 },
    "neon":    { "portalColor": "#00ff88", "particleCount": 1200 }
  },
  "controls": {
    "colours": [
      { "key": "portalColor", "label": "Portal colour" }
    ],
    "sliders": {
      "particleCount": { "label": "Particle density", "min": 100, "max": 2000, "step": 100, "default": 800 }
    }
  }
}
```

- `defaults` — the values used when a user has saved nothing (or `settings = null`).
- `presets` — named preset sets the Studio can offer as one-click options.
- `controls` — what editing controls to render in the Studio.

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
- `public/themes/portal/portal.js` — `window.PortalTheme` IIFE, `init(opts)` → scene
- `public/themes/portal/style.css` — dark glassmorphism profile layout

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

## Writing a new blade theme

1. Create `resources/themes/<slug>/manifest.json` following the schema above.
2. Create `resources/views/themes/<slug>.blade.php` — a full `<!DOCTYPE html>…</html>` page.
3. Put static assets in `public/themes/<slug>/` and reference them with `asset('themes/<slug>/...')`.
4. Call `app(ThemeRegistry::class)->clearCache()` (or wait up to 5 minutes) for the new theme to appear in `ThemeRegistry::all()`.

Themes authored by Alex or trusted creators are stored in the app filesystem and deployed with the app. Third-party or user-uploaded themes are not supported in the current architecture.
