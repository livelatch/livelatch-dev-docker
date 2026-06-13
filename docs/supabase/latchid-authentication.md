# LatchID Authentication

Supabase became the identity layer for Livelatch during the OAuth integration phase. The key fork milestone is the 2026-05-18 work summarized by `1e4b884`, `071774d`, and the related session flow changes in `LatchIdSessionController`.

## Core responsibility split

- Supabase handles user identity, OAuth, and session verification
- Laravel creates or links the local Livelatch user
- Stripe is provisioned during signup so billing state exists from the start

This split is visible in `app/Http/Controllers/Auth/LatchIdSessionController.php`.

## Current signup flow

```text
Google or Discord OAuth login
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

- the callback UI lives in `resources/views/auth/latchid-oauth-callback.blade.php`
- `/callback/google` and `/callback/discord` both use the same LatchID callback route
- login and registration include `resources/views/auth/latchid-oauth-buttons.blade.php` when Supabase is configured
- `/api/latchid/session` posts verified Supabase session data into Laravel
- `users.supabase_user_id` is the local bridge to `auth.users.id`
- email mismatches and ID mismatches are rejected before local login
- display names and `littlelink_name` values are generated uniquely for first-time users
- Supabase identities exposed by the verified user response are mirrored into Laravel `social_accounts` when provider and provider ID are available

## Why this is significant

This is the first real SaaS-style onboarding path in the fork. It means new users are no longer just Laravel records. They enter the system with identity and billing context immediately, which is essential for plan-aware product access later.

## Discord login and linking

Discord is implemented as a LatchID provider through [Supabase Auth](https://supabase.com/docs/guides/auth/social-login/auth-discord), not as a separate Laravel Socialite provider. Supabase handles the Discord OAuth exchange, and Laravel only accepts the result after verifying the Supabase access token.

Browser entry points:

- Login and registration show `Continue with Discord` when `SUPABASE_URL` and `SUPABASE_ANON_KEY` are configured.
- The Discord flow redirects to `/callback/discord`.
- The callback exchanges the Supabase OAuth code, posts the verified session to `/api/latchid/session`, and redirects to `/dashboard` by default.

Account linking:

- `/studio/latchid` shows a Discord connection action in the account area.
- If the browser already has a Supabase session, the page attempts Supabase manual identity linking with `linkIdentity`.
- If the user is logged into Laravel but does not have a Supabase browser session, the page starts Discord OAuth and the callback links the verified LatchID user ID to the current Laravel account.
- The callback accepts a relative `redirect_to` value so successful account-linking flows can return to `/studio/latchid?latchid=linked`.

The disconnect and consent-history flows are still future work. Supabase documents unlinking through `getUserIdentities()` and `unlinkIdentity()`, but Livelatch should add its own user confirmation, audit text, and fallback account-safety checks before exposing disconnect buttons.

## Discord provider setup

Discord requires a Discord application and Supabase provider configuration before the buttons will work. The Supabase Discord guide says the provider setup needs a Discord application, Discord OAuth credentials in the Supabase dashboard, and client code that calls `signInWithOAuth` with `discord`.

Operational setup:

1. In the [Discord Developer Portal](https://discord.com/developers/applications), create or open the Livelatch application.
2. Add the Supabase callback URL from Authentication > Sign In / Providers > Discord:

```text
https://<project-ref>.supabase.co/auth/v1/callback
```

3. Copy the Discord Client ID and Client Secret into the Supabase Discord provider settings.
4. In Supabase Auth URL configuration, allow the app callback URLs used by Livelatch:

```text
https://dev.livelatch.com/callback/discord
https://dev.livelatch.com/callback/google
```

5. For Discord, keep scopes minimal. The current browser flow requests `identify email`, which matches the user identity and email needs for LatchID account creation/linking.

## Operational notes

- `SUPABASE_URL` and `SUPABASE_ANON_KEY` are required for the browser/session bridge
- `SUPABASE_SERVICE_ROLE_KEY` appears in notification debugging and server-side notification reads
- if identity behavior changes later, preserve the trust boundary: Laravel should verify the Supabase session, not just trust browser-submitted profile fields
- Discord client credentials are stored in Supabase provider settings, not in Laravel `.env`, for the current LatchID implementation

## References

- [Supabase Discord Auth](https://supabase.com/docs/guides/auth/social-login/auth-discord)
- [Supabase Identity Linking](https://supabase.com/docs/guides/auth/auth-identity-linking)
- [Discord OAuth2](https://docs.discord.com/developers/topics/oauth2)
