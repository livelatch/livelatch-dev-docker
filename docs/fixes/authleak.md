# Auth token leak into PostHog — incident & fix

**Date:** 2026-06-20
**Severity:** High (live OAuth credentials stored in analytics)
**Status:** Contained. Code fix pending deploy; one manual step (revoke Supabase session) outstanding.

## Summary

Google sign-in used the OAuth **implicit flow**, which returns the Supabase session
in the URL **fragment** of the callback page:

```
https://dev.livelatch.com/callback/google#access_token=<JWT>&provider_token=ya29.<google>&refresh_token=<token>&token_type=bearer
```

PostHog captured that URL through **two** independent pipelines:

1. **Analytics events** — `$current_url` / `$referrer` on `$pageview` / `$pageleave`.
2. **Session replay** — the recorded page URL (`start_url` / visited pages). This is a
   *separate* pipeline; client-side property sanitisation does **not** reach it.

As a result, `access_token` (JWT), Google `provider_token`, and `refresh_token` for the
test/internal accounts were stored in PostHog (project 201558, EU cloud).

Scope: only internal/test accounts (no real users yet). Data stayed within our own
PostHog instance. Access/provider tokens are short-lived (1h, expired); the
`refresh_token` is the item worth revoking.

## Root cause

`signInWithOAuth` was started with a Supabase client that did **not** set
`flowType: 'pkce'`, so it defaulted to implicit flow → token in the URL fragment.

## Fixes applied

### PostHog side — via MCP (no repo / deploy needed)

- **Deleted** the 13 session recordings that had captured a token in their URLs.
  Re-scan (`visited_page icontains access_token`) returns zero.
- **Session-replay URL blocklist:** set project `session_recording_url_blocklist_config`
  to `[{ "url": "/callback", "matching": "regex" }]`. PostHog delivers this to the SDK via
  remote config, so replay now pauses on `/callback*` immediately — no snippet change.

> The earlier in-snippet PostHog guards (a `sanitize_properties` redactor +
> `disable_session_recording` on `/callback`) were **reverted** from
> `resources/views/layouts/posthog.blade.php`. Replay is now handled by the PostHog
> URL blocklist (above), and the PKCE change below removes tokens from URLs entirely,
> so event-property redaction is no longer needed. The snippet is back to its original
> state.

### App side — code (the durable root-cause fix: PKCE)

Switched Google/Discord OAuth from implicit to **PKCE (authorization-code) flow**, so the
provider returns `?code=<single-use>` instead of a `#access_token` fragment. The token
never appears in the URL — which also protects browser history, server logs, and
`Referer` headers, not just PostHog.

`flowType: 'pkce'` added to every Supabase browser client involved in the flow:

| File | Role |
| --- | --- |
| `resources/views/auth/latchid-oauth-buttons.blade.php` | App login/register sign-in buttons (initiation) |
| `homepage-demo.php` | Public homepage "Continue with Google" (initiation) |
| `resources/views/auth/latchid-oauth-callback.blade.php` | `/callback/{provider}` — exchanges the `?code=` for a session |

All three must use PKCE together: initiation stores a `code_verifier` in `localStorage`
that the callback reads back during `exchangeCodeForSession`. This works because all
pages are served from the same origin (`dev.livelatch.com`), so they share `localStorage`.
The callback already had the `?code=` exchange path; the implicit/fragment fallback is now
dead code.

### App side — defense in depth (already in place)

- `app/Services/SupabaseProfileLinkClickService.php`: the `referer` sent with the
  `profile_link_clicked` event is run through `redactAuthParams()` to strip any
  `access_token` / `refresh_token` / `code` / etc. query params before it reaches PostHog.

## Outstanding / follow-ups

- [ ] **Deploy** the app code changes (PKCE + referer scrub) to take effect. They are
      local only until deployed (Railway).
- [ ] **Revoke the exposed Supabase session** for the `alex` test account
      (Supabase dashboard → Auth → Users → sign out / revoke) to invalidate the captured
      `refresh_token`. Cannot be done via repo or PostHog.
- [ ] **Manual test after deploy:** sign in with **Google and Discord** from both the app
      login page and the homepage demo. Confirm the callback URL comes back as `?code=…`
      with **no** `#access_token`, the session establishes, and you land on `/dashboard`.
- [ ] Optional: delete the ~67 historical events that captured a token in `$current_url`
      (the access tokens in them are already expired). Not done automatically.

## Verification

- PostHog replay blocklist: project 201558 → `session_recording_url_blocklist_config`
  contains `/callback`.
- No recordings with tokens:
  `SELECT count() FROM ... ` / recordings filter `visited_page icontains access_token` → 0.
- After deploy: the callback URL contains `?code=` only; PostHog `$current_url` on
  `/callback` no longer contains `access_token`.
