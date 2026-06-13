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
- `/callback/google`, `/callback/discord`, and `/callback/youtube` use the same LatchID callback route
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

## TikTok Login Kit connection

TikTok is implemented as a LatchID account connection through a Supabase Edge Function and TikTok Login Kit. It is currently exposed from `/studio/latchid`, not as a primary login button on the public auth form.

Implementation details:

- The TikTok card is rendered by `Studio\LatchIdController`.
- Laravel checks `public.latchid_tiktok_accounts` through Supabase REST on the server side.
- The lookup filters `latchid_user_id = users.supabase_user_id`.
- The selected fields are `display_name`, `avatar_url`, `tiktok_open_id`, and `linked_at`.
- The service role key is only used by `LatchIdTikTokAccountService`; it must not be exposed to Blade or frontend JavaScript.
- If no account is found, the TikTok button links to the Edge Function authorize endpoint with `latchid_user_id` and `return_url`.

```text
https://yaljyfdfnphxzuhqlbfs.functions.supabase.co/tiktok-oauth/authorize
```

- The Edge Function is responsible for TikTok OAuth, upserting `public.latchid_tiktok_accounts`, and redirecting back to the supplied return URL.
- The requested TikTok Login Kit scope remains:

```text
user.info.basic
```

Operational setup:

1. In the [TikTok for Developers](https://developers.tiktok.com/) app, enable Login Kit.
2. Register the Edge Function callback URL required by the `tiktok-oauth` function as the TikTok Login Kit redirect URI.
3. Configure Laravel env:

```env
LATCHID_SUPABASE_URL=
LATCHID_SERVICE_ROLE_KEY=
TIKTOK_OAUTH_AUTHORIZE_URL=https://yaljyfdfnphxzuhqlbfs.functions.supabase.co/tiktok-oauth/authorize
```

4. The Edge Function should redirect successful callbacks to:

```text
{return_url}?tiktok_linked=1
```

5. When possible, Edge Function failures should redirect to:

```text
{return_url}?tiktok_error=1
```

6. Confirm the TikTok app has access to `user.info.basic`.

## YouTube API connection

YouTube is not treated as a separate login provider in LatchID. YouTube API access uses the Google OAuth provider through Supabase Auth with YouTube scopes. This lets a creator connect YouTube from `/studio/latchid` so future Livelatch features can read their channel videos and live broadcast data.

Implementation details:

- The `/studio/latchid` YouTube card starts Supabase OAuth with provider `google`.
- The callback route is `/callback/youtube`, which tells Laravel to store the result as a `youtube` connection.
- The requested scope is:

```text
https://www.googleapis.com/auth/youtube.readonly
```

- The request includes `access_type=offline` and `prompt=consent` so Google can return a refresh token when the user grants access.
- Provider access and refresh tokens are stored on `social_accounts` with encrypted Laravel casts.
- YouTube connections use a prefixed provider ID such as `youtube:<google-provider-id>` so they do not collide with normal Google login records.
- `App\Services\YoutubeConnectionService` is the backend integration point for future features. It can list recent videos and live broadcasts for the authenticated user's connected YouTube account.

Current YouTube API surfaces prepared:

- Recent videos use the YouTube Data API `search.list` endpoint with `forMine=true` and `type=video`.
- Live broadcasts use the YouTube Live Streaming API `liveBroadcasts.list` endpoint with `mine=true`.
- The service refreshes expired Google provider tokens using Laravel `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.

Do not expose raw provider tokens to Blade views or browser code after callback completion. Future controllers should call `YoutubeConnectionService` from the server side.

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

## YouTube provider setup

YouTube access uses the same Google OAuth provider in Supabase. Google Cloud and Supabase must both allow the OAuth redirects before the `/studio/latchid` YouTube connection button can complete.

Operational setup:

1. In [Google Cloud Console](https://console.cloud.google.com/), enable the YouTube Data API v3 for the Livelatch OAuth project.
2. Confirm the OAuth consent screen includes the YouTube read-only scope:

```text
https://www.googleapis.com/auth/youtube.readonly
```

3. In the Google OAuth client, keep the Supabase Auth callback URL in the authorized redirect URI list:

```text
https://<project-ref>.supabase.co/auth/v1/callback
```

4. In Supabase Auth URL configuration, allow the app callback URL:

```text
https://dev.livelatch.com/callback/youtube
```

5. Add the same Google OAuth client credentials to Laravel env so server-side token refresh can work:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

## Operational notes

- `SUPABASE_URL` and `SUPABASE_ANON_KEY` are required for the browser/session bridge
- `SUPABASE_SERVICE_ROLE_KEY` appears in notification debugging and server-side notification reads
- `LATCHID_SUPABASE_URL` and `LATCHID_SERVICE_ROLE_KEY` are used for server-side LatchID table lookups such as TikTok account status
- `TIKTOK_OAUTH_AUTHORIZE_URL` points the Studio TikTok connect button to the Supabase Edge Function authorize endpoint
- if identity behavior changes later, preserve the trust boundary: Laravel should verify the Supabase session, not just trust browser-submitted profile fields
- Discord client credentials are stored in Supabase provider settings, not in Laravel `.env`, for the current LatchID implementation
- YouTube API token refresh requires Laravel `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` because the backend service refreshes Google provider tokens server-side

## References

- [Supabase Discord Auth](https://supabase.com/docs/guides/auth/social-login/auth-discord)
- [Supabase Identity Linking](https://supabase.com/docs/guides/auth/auth-identity-linking)
- [Discord OAuth2](https://docs.discord.com/developers/topics/oauth2)
- [TikTok Login Kit for Web](https://developers.tiktok.com/doc/login-kit-web)
- [Supabase signInWithOAuth](https://supabase.com/docs/reference/javascript/auth-signinwithoauth)
- [YouTube Data API videos.list](https://developers.google.com/youtube/v3/docs/videos/list)
- [YouTube LiveBroadcasts list](https://developers.google.com/youtube/v3/live/docs/liveBroadcasts/list)
- [Google OAuth offline access](https://developers.google.com/identity/protocols/oauth2/web-server#offline)
