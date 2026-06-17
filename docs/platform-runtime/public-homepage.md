# Public Homepage

The public root page is served by `HomeController::home()`. When `homepage-demo.php` exists at the repository root, that file is included and returned as the homepage response.

## Current Alpha Homepage

`homepage-demo.php` is the current Livelatch Alpha landing page. It is a standalone PHP page rather than a Blade view so it can keep serving the public homepage through the existing LinkStack fallback path.

The homepage now includes:

- a light, Poppins-based design that mirrors the live Studio design tokens (white surfaces, the `#0092ec` -> `#0ce5de` blue/teal gradient, deep-navy text, 36px rounded cards, and soft glows) rather than the earlier dark theme
- a top navigation with `Log in` and `Get started`, plus links to How it works, Products, Live demo, Plans, and the documentation library at `/studio/docs`
- a hero with plain-language, non-technical copy and a live preview iframe for `dev.livelatch.com/@alex2`
- a three-step "How it works" explainer aimed at non-technical creators
- a "Livelatch family" section that uses the product wordmarks in `logos/` (Livelatch, LatchID, LatchDeck, Latchalytics) as headings with friendly descriptions
- a two-card Free vs Pro pricing block
- footer links for Privacy and Terms loaded through HTMX, plus the social-icon favicon

The `logos/` assets resolve at `/logos/<name>.png` because the deployment docroot is the project root (root `index.php` and `.htaccess`). The page uses the `_light` logo variants because the redesigned page has a light background.

## LatchID Auth

The homepage reads:

```env
SUPABASE_URL=
SUPABASE_ANON_KEY=
```

The `Get started` modal uses the Supabase browser client to start Google OAuth and returns to:

```text
https://dev.livelatch.com/callback/google
```

## Legal Pages

The footer legal links use HTMX to load:

```text
/legal/privacy
/legal/terms
```

When requested by HTMX, those routes return compact partial cards from:

```text
resources/views/public/legal-partial.blade.php
```

When opened directly, they render a standalone page through:

```text
resources/views/public/legal-page.blade.php
```

The public legal copy is intentionally concise and alpha-ready. The longer compliance documents under `docs/compliance/` remain internal working documents until they are reviewed for formal publication.
