# How to create a Livelatch theme

This guide is for **outside creators** who want to build an immersive Livelatch profile theme and hand it to the Livelatch team for review. It covers the file structure, the metadata (manifest), what libraries you can use, how to render links and icons correctly, and how to **bundle and deliver your theme as a `.zip`**.

> These are **blade themes** — full-page, self-contained profile experiences (3D scenes, canvas animation, anything). They are different from the classic colour-preset themes. If you only want to change colours/fonts, you don't need to build a blade theme.

---

## 1. What a theme is

A theme is **three things** that share one short, lowercase **slug** (e.g. `vice`, `minecraft`):

| Part | What it is | Installed to |
|---|---|---|
| `manifest.json` | Metadata + the controls shown in the editor | `resources/themes/<slug>/manifest.json` |
| `theme.blade.php` | The full HTML page for the profile | `resources/views/themes/<slug>.blade.php` |
| `assets/` | Your `style.css` and JS (and any images) | `assets/themes/<slug>/…` |

You deliver these to us in a single `.zip` (see [§8](#8-bundle-and-deliver-your-theme)). We review and install them.

The theme renders the signed-in creator's real profile: their avatar, name, bio, and **links/blocks**.

---

## 2. The slug

Pick a unique, URL-safe slug: lowercase letters, numbers and hyphens only (`my-cool-theme`). It must match in **all three** places: the manifest `slug`, the folder name, and the asset folder name. The page view file is named `<slug>.blade.php`.

---

## 3. `manifest.json` (metadata + editor controls)

The manifest tells the Theme Studio what your theme is and which controls to show. Nothing here changes the page by itself — your `theme.blade.php` reads the resulting values.

```json
{
  "name": "Vice City",
  "slug": "vice",
  "author": "yourhandle",
  "authorName": "Your Name",
  "authorHandle": "@yourhandle",
  "version": "1.0.0",
  "description": "Neon sunset, palm silhouettes and a Pricedown-style title.",
  "preview_gradient": "linear-gradient(160deg, #ff2e8b 0%, #ff9a3c 55%, #2a1a5e 100%)",
  "libraries": [],
  "defaults": {
    "pinkColor": "#ff2e8b",
    "headingFont": "Anton",
    "sunGlow": 50,
    "customCss": ""
  },
  "presets": {
    "default": { "pinkColor": "#ff2e8b", "sunGlow": 50 },
    "miami":   { "pinkColor": "#ff4fa3", "sunGlow": 60 }
  },
  "controls": {
    "colours": [
      { "key": "pinkColor", "label": "Neon pink" }
    ],
    "typography": {
      "heading": { "key": "headingFont", "label": "Heading", "default": "Anton", "options": ["Anton", "Oswald"] },
      "body":    { "key": "bodyFont",    "label": "Body",    "default": "Oswald", "options": ["Oswald", "Inter"] }
    },
    "sliders": {
      "sunGlow": { "label": "Sun glow", "min": 0, "max": 100, "step": 5, "default": 50 }
    },
    "customCss": { "pro": true }
  }
}
```

### Field reference

- **`name`**, **`slug`**, **`author`/`authorName`/`authorHandle`**, **`version`**, **`description`** — shown on the theme card in the editor.
- **`preview_gradient`** — any CSS `background` value; used as the card thumbnail.
- **`libraries`** — informational list of CDN libraries you use (e.g. `["three", "gsap"]`). You still load them yourself in the blade.
- **`defaults`** — the value for every control when a user hasn't changed it.
- **`presets`** — named one-click value sets the editor offers (always include `default`).
- **`controls`** — what the editor renders:
  - **`colours`** — up to **4** `{ key, label }`; rendered as swatch + hex fields.
  - **`typography`** — `heading` and/or `body` slots `{ key, label, default, options[] }`; rendered as font pickers. Your blade builds a Google Fonts request from the chosen families.
  - **`sliders`** — `{ label, min, max, step, default }`; rendered as range inputs (integers).
  - **`customCss`** — `{ "pro": true }` to offer a Pro-gated custom-CSS box.

### How values are sanitised (important)

Whatever the user enters is validated **against your manifest** before it reaches your blade:

- colours must match `#[0-9a-fA-F]{3,8}`,
- typography must be one of your `options` (or alphanumeric/space if you list none),
- sliders are clamped to your `min`/`max` and cast to integers,
- custom CSS is only kept for Pro users and has `<`/`>` stripped.

Anything not declared is dropped. **Always re-validate in the blade too** (defence in depth — see below).

---

## 4. `theme.blade.php` (the page)

This is a complete `<!DOCTYPE html>…</html>` page. It receives:

| Variable | Contains |
|---|---|
| `$user` | `id`, `name`, `littlelink_name`, `littlelink_description`, … |
| `$links` | the creator's links/blocks collection |
| `$settings` | resolved control values (your `defaults` merged with the user's choices) |
| `$manifest` | the parsed manifest |

### Skeleton

```blade
@php
    $s = $settings ?? [];

    // Re-validate every value you use. Colours:
    $pink = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['pinkColor'] ?? '') ? $s['pinkColor'] : '#ff2e8b';
    // Numbers: clamp.
    $sunGlow = max(0, min(100, (int) ($s['sunGlow'] ?? 50)));
    // Fonts: allow letters/numbers/spaces only.
    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Anton';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont'] ?? '') ? $s['bodyFont'] : 'Oswald';

    // Build ONE Google Fonts request from the chosen families — with NO weight
    // axis, so single-weight display fonts don't 400 the whole request.
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    // Pro custom CSS: strip angle brackets so it can't break out of <style>.
    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    // Inline your own CSS/JS from disk so the page never depends on a separate
    // static fetch. (LinkStack serves static files from the PROJECT ROOT, so
    // assets live under assets/themes/<slug>/, NOT public/.)
    $themeCss = is_file(public_path('assets/themes/vice/style.css')) ? file_get_contents(public_path('assets/themes/vice/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/vice/vice.js'))   ? file_get_contents(public_path('assets/themes/vice/vice.js'))   : null;

    $userName = e($user->name ?? '');
    $userBio  = e($user->littlelink_description ?? '');
    $handle   = $user->littlelink_name ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $userName }}</title>
  <meta name="description" content="{{ $userBio }}">

  {{-- Social / OG tags (use the helpers for the avatar) --}}
  <meta property="og:title" content="{{ $userName }}">
  <meta property="og:image" content="{{ profilePreviewImageUrl($user->id) }}">
  @if($handle)<meta property="og:url" content="{{ url('/@' . $handle) }}">@endif

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="{{ $googleFontsUrl }}" rel="stylesheet">

  @if($themeCss !== null)
  <style data-vc-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/vice/style.css') }}">
  @endif

  <style>
    :root {
      --vc-pink: {{ $pink }};
      --vc-heading-font: "{{ $headingFont }}", sans-serif;
      --vc-body-font:    "{{ $bodyFont }}", sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-vc-custom-css>{!! $customCss !!}</style>
  @endif

  {{-- REQUIRED so block types (text/heading/spacer/…) and link icons work --}}
  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <main class="vc-profile">
    <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="vc-avatar" width="110" height="110">
    <h1 class="vc-name">{{ $userName }}</h1>
    @if($userBio)<p class="vc-bio">{{ $userBio }}</p>@endif

    @if(count($links) > 0)
      <nav class="vc-links" aria-label="Links">
        {{-- REQUIRED: renders every block type AND the social icons + click tracking --}}
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'vc-link'])
      </nav>
    @endif
  </main>

  {{-- Your inlined JS (and any CDN libraries) --}}
  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/vice/vice.js') }}"></script>
  @endif

  @stack('linkstack-body-end')
</body>
</html>
```

### Helpers you can use

- `profileImageUrl($user->id)` — the avatar.
- `profilePreviewImageUrl($user->id)` — the OG/meta image.

---

## 5. Rendering links, blocks, and icons (do not skip)

A profile is more than buttons — it also has **blocks** (spacer, text, heading, email, …). **Always** render the link area with the shared partial:

```blade
@include('themes.partials.links', ['links' => $links, 'linkClass' => 'vc-link'])
```

and put these in your `<head>`:

```blade
@include('linkstack.modules.block-libraries', ['links' => $links])
@stack('linkstack-head')
```

and `@stack('linkstack-body-end')` before `</body>`.

If you hand-roll the link loop instead, you will **silently drop every block** and **every social icon**, and break click tracking. Don't.

### Social / link icons (handled for you)

Each link can carry a [Simple Icons](https://simpleicons.org/) brand icon (`custom_icon = "si:<slug>"`). The shared partial renders it as a colour-inheriting mask. Two **universal** controls on the Theme Studio Beta page govern them for *every* theme:

- **Show icons** on/off (default on),
- **Icon colour** — default inherits your link's text `color` (`currentColor`); a chosen colour overrides it.

You don't add controls for these. Your only job: **use the shared partial** and make sure your link buttons (`.vc-link`) set a sensible text `color`, so the default "match link colour" looks right. Style the rendered icon if you like via `.<prefix>-links .ll-theme-si`.

### Block elements

Block items render inside `.ll-theme-block` wrappers (text, `h2`, `.button-spacer`), each with `--ll-i` (its index) for staggered CSS. Style them under your link container, e.g. `.vc-links .ll-theme-block h2 { … }`.

---

## 6. Supported add-ons (libraries)

Blade themes have **no build step**. Load any library from a CDN with a normal `<script>`/`<link>` in your blade. Commonly used and supported:

- **[Three.js](https://threejs.org/)** — WebGL 3D scenes (used by Portal, Minecraft).
- **[GSAP](https://gsap.com/)** — animation/timelines (entrance sequences).
- **[Anime.js](https://animejs.com/)**, **[p5.js](https://p5js.org/)**, **[PixiJS](https://pixijs.com/)**, **[tsParticles](https://particles.js.org/)**, **[Lottie](https://airbnb.io/lottie/)** — animation / particles / vector animation.
- Plain **2D `<canvas>`** and **CSS** — no library needed (preferred for lightweight themes).

Guidance:

- **Pin a version** in the CDN URL (e.g. `three@0.158.0`), don't use `@latest`.
- **List what you use** in the manifest `libraries` array.
- Prefer **one or two** libraries; keep the page fast on mobile.
- Respect **`prefers-reduced-motion`** — pause/disable heavy motion.
- Everything must work over **HTTPS** from a public CDN (unpkg, jsDelivr, cdnjs). No npm install, no bundler, no server code.
- Your own CSS/JS ship **in the zip** (not from a CDN) and get inlined from `assets/themes/<slug>/`.

### Fonts caveat

Build your Google Fonts request with **no weight axis** (`family=Anton`, not `family=Anton:wght@700`). A single unavailable weight makes the **whole** CSS2 request 400. Variable fonts still serve their full range; single-weight display fonts just work. Non-Google fonts (e.g. Pricedown) must be loaded via `@font-face` from a webfont CDN and kept **out** of the Google request, with a Google fallback in the stack.

---

## 7. Security & review rules

Themes run with the creator's page, so we only accept themes from trusted authors and we review every line. Your theme **must**:

- contain **no server-side code** beyond the read-only Blade shown above (no DB writes, no `env()`, no file writes, no shell);
- **escape** all user data (`{{ }}` / `e()`); only use `{!! !!}` for your own inlined asset strings;
- **re-validate** every `$settings` value (hex/clamp/options) before using it in HTML or CSS;
- load third-party libraries only from reputable CDNs over HTTPS;
- not call out to external services with the visitor's data.

Themes that fail review are sent back with notes.

---

## 8. Bundle and deliver your theme

Deliver a single **`.zip`** named after your slug, laid out like this:

```text
<slug>.zip
└── <slug>/
    ├── manifest.json
    ├── theme.blade.php
    └── assets/
        ├── style.css
        ├── <slug>.js          (omit if your theme is CSS-only)
        └── img/               (optional images, referenced via asset())
```

Example for the slug `vice`:

```text
vice.zip
└── vice/
    ├── manifest.json
    ├── theme.blade.php
    └── assets/
        ├── style.css
        └── vice.js
```

On our side those files map to:

| In your zip | Installed to |
|---|---|
| `<slug>/manifest.json` | `resources/themes/<slug>/manifest.json` |
| `<slug>/theme.blade.php` | `resources/views/themes/<slug>.blade.php` |
| `<slug>/assets/*` | `assets/themes/<slug>/*` |

### Before you send

- [ ] Slug is lowercase/hyphenated and identical in the manifest, folder, asset paths, and the `public_path('assets/themes/<slug>/…')` calls in your blade.
- [ ] `manifest.json` is valid JSON and includes a `default` preset.
- [ ] The page is a complete `<!DOCTYPE html>…</html>` document.
- [ ] Links render via `@include('themes.partials.links', …)`, with `block-libraries` + `@stack` wired into the head/body.
- [ ] Every `$settings` value is re-validated in the blade.
- [ ] CDN libraries are version-pinned and listed in `libraries`.
- [ ] Looks good at iPhone, iPad, and Desktop sizes, and respects reduced motion.
- [ ] Tested with a profile that has several links (some with brand icons) plus a text and a spacer block.

Email/share the `.zip` with the Livelatch team. We'll review, install, bust the theme cache, and it'll appear in the Theme Studio.

---

## 9. Reference

- `docs/themes/blade-theme-system.md` — how the system works internally (registry, settings, preview).
- The shipped themes (`portal`, `vice`, `minecraft`, `jarvis`, …) are the best worked examples to copy from.
