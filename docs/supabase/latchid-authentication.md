# LatchID Authentication

Supabase became the identity layer for Livelatch during the OAuth integration phase. The key fork milestone is the 2026-05-18 work summarized by `1e4b884`, `071774d`, and the related session flow changes in `LatchIdSessionController`.

## Core responsibility split

- Supabase handles user identity, OAuth, and session verification
- Laravel creates or links the local Livelatch user
- Stripe is provisioned during signup so billing state exists from the start

This split is visible in `app/Http/Controllers/Auth/LatchIdSessionController.php`.

## Current signup flow

```text
OAuth login
-> Supabase session
-> Laravel callback/session handoff
-> Verify Supabase user via access token
-> Find or create local user
-> Create Stripe customer
-> Create free subscription
-> Persist local billing record
-> Log the user into the studio
```

## Important implementation details

- the callback UI lives in `resources/views/auth/latchid-google-callback.blade.php`
- `/api/latchid/session` posts verified Supabase session data into Laravel
- `users.supabase_user_id` is the local bridge to `auth.users.id`
- email mismatches and ID mismatches are rejected before local login
- display names and `littlelink_name` values are generated uniquely for first-time users

## Why this is significant

This is the first real SaaS-style onboarding path in the fork. It means new users are no longer just Laravel records. They enter the system with identity and billing context immediately, which is essential for plan-aware product access later.

## Operational notes

- `SUPABASE_URL` and `SUPABASE_ANON_KEY` are required for the browser/session bridge
- `SUPABASE_SERVICE_ROLE_KEY` appears in notification debugging and server-side notification reads
- if identity behavior changes later, preserve the trust boundary: Laravel should verify the Supabase session, not just trust browser-submitted profile fields
