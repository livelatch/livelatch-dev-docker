# Public Homepage

The public root page is served by `HomeController::home()`. When `homepage-demo.php` exists at the repository root, that file is included and returned as the homepage response.

## Current Alpha Homepage

`homepage-demo.php` is the current Livelatch Alpha landing page. It is a standalone PHP page rather than a Blade view so it can keep serving the public homepage through the existing LinkStack fallback path.

The homepage now includes:

- alpha-focused Livelatch positioning
- a top navigation link to the Studio documentation library at `/studio/docs`
- a single `Get started` action backed by LatchID Google authentication
- a hero preview iframe for `dev.livelatch.com/@alex`
- a Free vs Pro plan table
- footer links for Privacy and Terms loaded through HTMX

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
