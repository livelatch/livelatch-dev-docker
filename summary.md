# Livelatch Fork Summary

This file tracks changes made in the Livelatch fork only. It excludes inherited upstream LinkStack history.

Fork range used for the initial backfill: `upstream/main..main`.

Current coverage: 71 fork commits from `8e19376` on 2026-05-12 through `da2fde9` on 2026-06-10.

## Recent Changes

### 2026-06-21

- Agent: Claude
- Added an email + passkey LatchID sign-in path alongside the existing Google option, using **native Supabase passkeys** (beta, shipped 2026-05-28, usable as a primary sign-in). Decided against Pocket ID (would have been a second IdP needing user-sync) and Laravel-side WebAuthn (Alex intends to leave Laravel one day) — Supabase stays the single source of truth for all users. Captured the decision in agent memory.
- `homepage-demo.php`: reworked the auth modal into three options — *Continue with Google* (unchanged PKCE OAuth redirect), *Sign in with a passkey* (`signInWithPasskey()` for returning users), and *Continue with email* (`signInWithOtp` → 6-digit code → `verifyOtp` → skippable passkey-enroll prompt via `registerPasskey()`). The shared client opts into the beta with `experimental: { passkey: true }`. OTP/passkey logins return the session in-page (no redirect), so a new `completeLogin()` helper POSTs straight to the existing `/api/latchid/session`; added a `<meta name="csrf-token">` so the guest POST passes CSRF. No backend/controller change needed — passkey/OTP sessions flow through the existing Supabase-session pipeline.
- `routes/auth.php`: bypassed the Laravel password login — `GET /login` now `redirect('/')` to the homepage modal, keeping the `login` route name so auth-middleware redirects still work. `POST /login` left intact but effectively dead (no form posts to it).
- `resources/views/studio/account/latchid.blade.php`: added a Passkeys management section (list / add / remove per device) and opted the page's Supabase client into `experimental: { passkey: true }`. Gracefully degrades to a "sign back in on this device" message when no Supabase session is present in local storage (dashboard passkey ops require a live browser session).
- Still required (operator, Supabase dashboard): enable Passkeys (BETA) with Relying Party ID `livelatch.com`; enable Email provider and switch the OTP template to `{{ .Token }}`; configure Spacemail SMTP (`mail.spacemail.com`, `hello@livelatch.com`) + SPF/DKIM. Beta API may change without notice — pin the exact `supabase-js` CDN version (currently `@2`) once verified. End-to-end browser verification deferred until the dashboard settings are on.
- Validation: `php -l homepage-demo.php` and `php -l routes/auth.php` clean; `php artisan view:clear`.

### 2026-06-20

- Agent: Claude
- Captured the LatchDeck claim/redemption product design (worked out in conversation) as three new planning docs under `docs/latchdeck/`: `campaigns-and-mechanics.md` (the "many gateways, one engine" redemption model — six gateways feeding one `grant(entitlement, identity, actor, source)` function, two-phase resolved/deferred identity with Login-Kit reconciliation, atomic supply, the live-chat ingest worker, and the packs/Visibility-Cards/conditional-redemption mechanics), `card-identity-and-provenance.md` (card-type vs. instance-copy, UUIDv7 ID vs. human serial, the append-only ownership-event ledger = "minted, no web3", reserved serials, SDK/trading enablement), and `rarity-model.md` (scarcity × desirability value score, the dilution mechanic as the anti-abuse layer, the v0 geometric-mean equation, percentile→tier presentation, living prestige). All three are marked design/planned, not shipped.
- Overhauled the in-app docs viewer navigation (`resources/views/studio/docs/index.blade.php`) since the library was getting hard to navigate as it grows: left-nav categories now collapse by default and only the active doc's category opens (auto-expanding on search/filter and restoring on clear), each category header shows a doc count badge, nav rows are denser (title-only, summary moved to hover `title`), and the article pane gained an auto-generated "On this page" table of contents built client-side from the rendered `h2`/`h3` headings after each HTMX swap (assigns heading ids, smooth-scrolls). No controller/`DocumentationLibrary` changes.
- Validation: `php artisan view:clear` + `view:cache` compile clean.
- Hardened analytics so OAuth tokens can never be catalogued in PostHog. On the implicit-flow `/callback/*` pages the Supabase/Google access/refresh tokens land in the URL fragment (`#access_token=<jwt>`), and PostHog's default autocapture/pageview/session-replay would otherwise record the full URL.
- `resources/views/layouts/posthog.blade.php`: added a `sanitize_properties` hook (`llSanitizePostHogProperties` + `llRedactAuthFromUrl`) that redacts `access_token`/`refresh_token`/`id_token`/`provider_token`/`token`/`code`/`otp`/`state` from `$current_url`, `$referrer`, `$initial_*`, and `$pathname` (query string and a token-bearing fragment). Because session replay captures the URL through a separate pipeline that `sanitize_properties` does not reach, also detect auth/callback routes (`llIsAuthCallbackPage`) and disable `autocapture`, `capture_pageview`, `capture_pageleave`, and `disable_session_recording` on those transient pages only.
- `app/Services/SupabaseProfileLinkClickService.php`: added a matching server-side `redactAuthParams()` applied to the captured `referer` before it is sent to PostHog/Supabase, so a token leaked via the Referer query string is never stored.
- Fixed a JS crash on pages without a loader element: `loaderInit()` in `assets/js/hope-ui.js` called `loader.classList.add(...)` inside a `setTimeout` even when `document.querySelector('.loader')` returned null (`TypeError: Cannot read properties of null (reading 'classList')`, PostHog error `019ee07c-...`). Added an early-return null guard matching the existing defensive pattern in the file; the script is included on several views (`home`, `linkinfo`, `pages`, `report`, etc.) that lack a `.loader`.
- Validation: `php -l app/Services/SupabaseProfileLinkClickService.php`; `node --check assets/js/hope-ui.js` and reviewed the Blade snippet (the redaction regexes and the callback-page gating). No PHP behavior changed beyond the new redaction helper.

### 2026-06-19

- Agent: Claude
- Overhauled LatchDeck into an access-gated, platform-agnostic alpha across four systems. Encore (`ld/latchdeck`) is the API/authority and already implemented most of the flow; the work made it production-shaped and wired Livelatch + LatchOps as clients. Architecture and contract documented in `docs/latchdeck/latchdeck-architecture.md` and `docs/latchdeck/latchdeck-api.md`.
- Encore (`ld/latchdeck`): added Bearer API-key auth (`auth.ts` — service key for Livelatch, admin key for LatchOps; gateway + `requireAdmin`); added a tier capability skeleton (`tiers.ts`, free/pro/sdk, placeholder limits); reworked cards (`cards_mvp.ts`) so drafts can be created while `pending_review`, with new publish (gated on `active` + tier limit), update, unpublish, and list-including-drafts; `access-status` now returns tier+capabilities; added `POST /creators/:id/tier`; guarded admin endpoints and made approve insert a `livelatch_notifications` row on approval. Coalesced empty reviewer id to null (uuid columns). Typechecked clean (the stale `hello` codegen is regenerated by Encore on deploy).
- Supabase: added `latchdeck_cards_mvp.status_mvp`/`published_at_mvp` and `latchdeck_creators.tier` (+ check constraints, index), applied via MCP and captured at `ld/latchdeck/migrations/20260619_...sql`.
- Livelatch: new `App\Services\LatchDeckService` (Encore HTTP client, `config('services.latchdeck.*')`), `Studio\LatchDeckController`, routes, single state-driven nav entry, and screens — request-access (free/pro/SDK writeup), pending state with draft editor (publish disabled), active editor with pro-locked premium rarities. Card art uploads to the existing S3 disk (public) → `image_url_mvp`. Added `User::planKey()`/`isPro()`; tier self-heals into Encore on load. Routes resolve and Blade compiles.
- LatchOps (`latchops`): added a LatchDeck Encore admin service (`electron/services/latchdeck.ts`) + IPC + preload + `api` wrapper; an Applications review section on the LatchID page (approve/deny via the Encore admin API rather than direct Supabase writes); and Settings fields for the Encore URL, admin API key (secret), and operator LatchID. Both tsc projects typecheck clean.
- Generated two LatchDeck API keys (service + admin) for the operator to install into Encore secrets / Livelatch env / LatchOps settings. Deferred for alpha: redemptions, real-time/campaigns, SDK key issuance, a billing→tier webhook, and inline card editing UI (endpoint exists).
- Validation: `php -l` on new PHP, `php artisan view:cache` + `route:list` (Livelatch); `tsc --noEmit` on Encore and on both LatchOps tsconfigs.

### 2026-06-18

- Agent: Claude
- Rebuilt the Livelatch notification system end to end after it had silently shown nothing. Root causes found: (1) `LivelatchNotificationService` lived at `app/Services/app/Services/…`, breaking PSR-4 autoload so every call threw and the sidebar's `try/catch` swallowed it; (2) it read creds via `env()` (returns null under `config:cache`) instead of `config('services.latchid.*')`; (3) the "View notification center" link pointed at a non-existent `/studio/notifications` route, and there was no read-state / mark-as-read code at all. RLS was a red herring for reads — the service-role key bypasses RLS.
- Supabase: added `public.livelatch_notification_reads` (per-user read state, FKs to `livelatch_notifications` and `auth.users`, unique on `(notification_id, user_id)`) plus RLS policies on it and a `SELECT` policy on `livelatch_notifications` (own + global). Captured as `supabase/migrations/20260618120000_livelatch_notifications_per_user_reads_and_rls.sql`; both tables dropped off the "RLS enabled, no policy" advisor.
- Laravel: moved the service to the correct path `app/Services/LivelatchNotificationService.php`, switched to `config()` creds with real logging, and added `forUser` (annotates `is_read` per user), `unreadCount`, `markAsRead`/`markAsUnread`/`markAllAsRead`, and a `publish()` helper for sending notifications (e.g. invoices). Added `Studio\NotificationController` + auth-protected JSON routes (`studio.notifications[.read|.unread|.readAll]`). Removed the two `/debug-*-notifications` routes.
- UI: the sidebar bell now reflects per-user unread state, and "View notification center" opens a new Bootstrap modal (`#llNotificationCenterModal`) with Unread and Inbox tabs and live mark-as-read that updates the bell.
- Note for runtime: notifications need `LATCHID_SUPABASE_URL` and `LATCHID_SERVICE_ROLE_KEY` (or the `SUPABASE_*` fallbacks) set in the environment `.env`; they are absent from the local dev `.env` here.
- Validation: confirmed the class autoloads, routes resolve (`route:list`), all Blade templates compile (`view:cache`), and exercised the service logic (PostgREST query building, `is_read` annotation, unread count, upsert payload/headers, `publish` payload) via `Http::fake`. LatchOps already writes with the service-role key, so it needs no change. Rewrote `docs/supabase/notifications.md` as the full spec and updated `docs/supabase/latchid-schema.md`.

### 2026-06-17

!! Decided to trial Claude (Claude Code) in place of ChatGPT/Codex for Livelatch development. This is the first session run with Claude as the agent.
- Agent: Claude
- Reviewed full project context (`summary.md`, `AGENTS.md`, and the entire `docs/` library) to build a working understanding of Livelatch before making changes.
- Ran the AGENTS.md todo-check pass and closed completed todos: Discord LatchID provider setup, the TikTok Edge Function return-URL fix, the Supabase profile link clicks table, and seeding the core theme catalogue.
- Kept three todos open with refreshed context: data collection logic (recorded the owner-confirmed production processor map and GDPR/offboarding approach inline, `ask_after` -> 2026-06-18), YouTube API provider setup (blocked on Google sensitive-scope OAuth verification, `ask_after` -> 2026-07-01), and Supabase social metric snapshots (parked pending a snapshot collector, `ask_after` -> 2026-06-24).
- Validation: documentation/todo maintenance only; no runtime code changed.
- Agent: Claude
- Rebuilt `homepage-demo.php` as a light, Poppins-based public homepage matching the live Studio design tokens (white surfaces, `#0092ec` -> `#0ce5de` blue/teal gradients, deep-navy text, 36px rounded cards, soft glows) in place of the previous dark neon SaaS look.
- Restructured for a non-technical first impression: hero with the live `/@alex2` profile preview, a plain-language three-step "How it works", a Livelatch family product section using the `logos/` wordmarks (Livelatch, LatchID, LatchDeck, Latchalytics) as headings with friendly descriptions, a friendlier two-card Free/Pro pricing block, and a restyled footer using the social icon as the favicon.
- Kept login intact: the Supabase Google OAuth flow, `/callback/google` redirect, HTMX privacy/terms panel, and PostHog render are unchanged; added a nav "Log in" that reuses the same modal and switched the demo profile from `/@alex` to `/@alex2`.
- Validation: ran `php -l homepage-demo.php` (no syntax errors) and confirmed the deployment docroot is the project root (root `index.php`/`.htaccess`) so `/logos/*.png` resolve.
- Agent: Claude
- Fixed the Studio light/dark toggle so it can no longer get stuck: the global toggle now strips inline `--ll-*` overrides and re-asserts the stored theme after every HTMX swap, and the Dev Tools live preview no longer writes to the global document at all.
- Replaced the dashboard "last 14 days" bar chart with an inline SVG line chart (gradient area fill, hover points, peak/day annotation) in `resources/views/livewire/dashboard-analytics.blade.php`.
- Rebuilt the Appearance page (`resources/views/studio/page.blade.php`, `/studio/page`) natively in the Studio shell using `--ll-*` tokens, native switches and inputs, replacing the legacy LinkStack form styling while preserving all field names, the `editPage` action, URL validation, and the CKEditor block.
- Overhauled admin Dev Tools (`resources/views/studio/admin/dev-tools.blade.php`) into a scoped, leak-free editor: light and dark token sets are crafted independently against two side-by-side preview stages rendered via offscreen `[data-ll-theme]` probes, so the live site theme is never touched.
- Added an optional, persisted Liquid Glass visual layer toggled from Dev Tools, isolated in `resources/views/layouts/liquid-glass.blade.php` (included once in the sidebar layout) with removal instructions in `docs/platform-runtime/liquid-glass.md`.
- Validation: ran `php artisan view:cache` (all Blade templates compiled); updated `docs/platform-runtime/admin-dev-tools.md` and added `docs/platform-runtime/liquid-glass.md`.
- Agent: Claude
- Added PostHog user identification for authenticated users in `resources/views/layouts/posthog.blade.php`: an `@auth` `posthog.identify(...)` call placed after the existing `posthog.init(...)` (init left unchanged), sending `email`, `name`, `livelatch_user_id`, and `latchid_user_id` as person properties.
- Set the browser distinct ID to `latchid:{supabase_user_id}` with a `laravel-user:{id}` fallback so browser identity matches the server-side `profile_link_clicked` scheme; corrected the assumption that `users.id` is the Supabase UUID (the UUID is `users.supabase_user_id`). Values are emitted with `@json(...)` for safe JS escaping.
- No sidebar change needed: `resources/views/layouts/sidebar.blade.php` already includes `@include('layouts.posthog')`.
- Validation: ran `php artisan view:cache` (all Blade templates compiled); updated `docs/platform-runtime/posthog-analytics.md`.
- Agent: Claude
- Hardened the LatchID sign-in flow against intermittent "Server Error" / failed sign-ins. In `resources/views/auth/latchid-oauth-callback.blade.php`, disabled Supabase `detectSessionInUrl` so the manual `exchangeCodeForSession` is the single authority (removing a double-exchange race on the single-use code) and added a fallback to an existing session when the code was already consumed.
- In `app/Http/Controllers/Auth/LatchIdSessionController.php`, added a timeout + retry and a connection-failure guard around Supabase token verification, moved Stripe customer/subscription provisioning out of the signup DB transaction into a best-effort idempotent `provisionBilling()` (logs and continues on failure), and wrapped user persistence so a concurrent duplicate insert recovers the existing user instead of returning a 500.
- Validation: ran `php -l app/Http/Controllers/Auth/LatchIdSessionController.php` and `php artisan view:cache`; updated `docs/supabase/latchid-authentication.md`.

### 2026-06-14

- Agent: Codex
- Replaced the temporary dashboard sample data with `DashboardAnalyticsService`, which reads profile link click rows from Supabase as the source of truth and renders creator-focused Livewire analytics for total clicks, daily movement, clicks per link, direct destination opens, and connected social channels.
- Added optional Supabase social metric snapshot configuration for future YouTube subscriber, TikTok follower, and other creator-connection growth charts, plus documentation and an owner todo for creating/populating the snapshot table.
- Validation: ran PHP syntax checks for the dashboard service, Livewire component, dashboard Blade view, and services config.
- Agent: Codex
- Added a core Livelatch theme catalogue with 10 new free themes plus Livelatch Default, each backed by database-seeded manifests, six presets, suggested fonts, editable colour/radius/font/effect parameters, and public profile CSS effect rendering.
- Rebuilt `/studio/theme` into a full-width Theme Studio with visual theme cards, preset pills, styled colour pickers, font examples, custom Google Font input, motion/texture sliders, live phone preview, and the existing AJAX save flow.
- Tightened the Theme Studio UI so theme selection is a compact horizontal chooser with presets in the same panel and a smaller sticky preview, reducing the vertical space used before the editing controls.
- Cleaned user-facing alpha copy across Theme Studio, Dashboard analytics, Manage My Data, Affiliate Program, and LatchDeck placeholder screens so implementation details, testing language, and internal provider references are no longer shown to normal users.
- Updated theme documentation and added an owner todo to run `php artisan db:seed --class=ThemeSeeder` in an environment with the correct PDO driver; local seeding was attempted but failed because this PHP environment lacks the configured database driver.
- Validation: ran PHP syntax checks for the catalog, seeder, theme service, controller, Studio Blade, and public theme module; rebuilt Blade view cache.
- Agent: Codex
- Overhauled `homepage-demo.php` into a Livelatch Alpha homepage with a single Get Started LatchID action, documentation-library nav link, `@alex` profile demo frame, Free vs Pro plan table, and HTMX-loaded public Privacy/Terms footer panels.
- Added public `/legal/privacy` and `/legal/terms` routes with compact HTMX partials plus standalone fallback pages, and documented the homepage/legal route structure in `docs/platform-runtime/public-homepage.md`.
- Validation: ran PHP syntax checks for the homepage, routes, and legal Blade views.

### 2026-06-13

- Agent: Codex
- Expanded `docs/livelatch-meta/agent-summary.md` so it now mirrors every dated section in `summary.md` instead of only summarizing the latest day.
- Validation: updated the documentation companion page only; no runtime code changed.
- Agent: Codex
- Rewrote the root `README.md` to reflect the current Livelatch stack and project direction, keeping the license/FOSSA badges while adding high-level setup guidance for Laravel, PostgreSQL, Supabase, Stripe, HTMX/Livewire, storage, mail, and the external services used or planned by the project.
- Validation: updated the repository overview documentation only; no runtime code changed.
- Agent: Codex
- Added `docs/livelatch-meta/agent-summary.md` as a readable companion to `summary.md`, plus an admin-facing `/admin/development-timeline` page that presents the fork history, core stack, and a clickable pitch-deck-style timeline.
- Wired the development timeline into the admin navigation and added lightweight interaction so the active milestone and stack summary update when a user clicks through the timeline.
- Validation: ran `php -l routes\web.php`, `php -l app\Http\Livewire\StudioNavigation.php`, and `php artisan view:cache`.
- Agent: Codex
- Added `docs/platform-runtime/affiliate-program.md` to document the future affiliate stack around Refferq, including the recommended split between Livelatch and a separate affiliate portal, setup prerequisites, and the planned handoff flow.
- Validation: reviewed the existing affiliate placeholder route and Refferq's public repository documentation; no runtime code was changed.
- Agent: Codex
- Patched the deployed Supabase Edge Function `tiktok-oauth` from MCP version 17 to version 20 so TikTok user info responses with HTTP 200, `error.code: "ok"`, and `data.user.open_id` are treated as successful and the callback now redirects back to trusted Livelatch return URLs.
- Updated the function's TikTok account upsert payload to read `tiktok_open_id`, `display_name`, and `avatar_url` from `tiktok.data.user`, include `linked_at`, and preserve the existing OAuth/token save flow while adding success and error redirects to `tiktok_linked=1` and `tiktok_error=1`.
- Validation: fetched the deployed function back through Supabase MCP and confirmed version 20 is active with the corrected parser, trusted return URL handling, and redirect behavior; no service role key was added to Blade or frontend JavaScript.
- Agent: Codex
- Added Discord as a LatchID login option through Supabase Auth by generalizing the OAuth callback to `/callback/{provider}` for Google and Discord and adding shared LatchID OAuth buttons to login and registration.
- Updated the LatchID session bridge so an already-authenticated Laravel user can link a verified Supabase/LatchID account without creating a duplicate local user, and mirrored available Supabase identities into `social_accounts`.
- Reworked `/studio/latchid` so Discord can be connected from the account area using Supabase `linkIdentity` when a browser Supabase session exists, with an OAuth fallback for legacy/local sessions.
- Updated `docs/supabase/latchid-authentication.md` and added `docs/todos/discord latchid provider setup.md` for the required owner-side Discord/Supabase provider configuration.
- Validation: ran PHP syntax checks for changed routes, controller, and views; ran `php artisan view:cache`; confirmed `/callback/{provider}` and `/api/latchid/session` routes with `php artisan route:list`.
- Added YouTube API connection groundwork by allowing `/callback/youtube`, wiring the LatchID YouTube card to Google OAuth with the YouTube read-only scope, storing provider tokens on encrypted `social_accounts` fields, and adding `YoutubeConnectionService` for future server-side video/live broadcast reads.
- Updated `.env.example`, LatchID authentication docs, and `docs/todos/youtube api provider setup.md` with Google Cloud, Supabase redirect, and Laravel token-refresh setup requirements.
- Validation: ran PHP syntax checks for the YouTube service, social account model, migration, LatchID controller, callback view, and account view; ran `php artisan view:cache` and `php artisan route:list --path=callback`; attempted `php artisan migrate --pretend` but local PHP failed before migration execution because the configured PDO driver is missing.
- Removed the separate YouTube card from the LatchID account view and changed the Google card copy/action to explain that Google also grants YouTube API access for future live stream and video features.
- Validation: ran `php -l resources/views/studio/account/latchid.blade.php` and `php artisan view:cache`.
- Enabled the TikTok LatchID account card after Supabase Login Kit setup by allowing `/callback/tiktok`, accepting `tiktok` in the LatchID session bridge, requesting `user.info.basic`, and documenting the TikTok redirect/scope requirements.
- Validation: ran PHP syntax checks for routes, the LatchID controller, and the LatchID account view; ran `php artisan view:cache` and `php artisan route:list --path=callback`.
- Updated the TikTok account connection to call Supabase provider `tiktok-loginkit` while normalizing the local Livelatch `social_accounts.provider_name` value back to `tiktok`.
- Validation: ran PHP syntax checks for the LatchID controller and account view; ran `php artisan view:cache`.
- Corrected the TikTok Supabase provider call to use the required custom OAuth provider identifier `custom:tiktok-loginkit`.
- Validation: ran `php -l resources/views/studio/account/latchid.blade.php` and `php artisan view:cache`.
- Reworked TikTok account linking to use the Supabase Edge Function flow instead of Supabase Auth providers: `/studio/latchid` now queries `public.latchid_tiktok_accounts` server-side through Supabase REST, shows connected TikTok profile details, and links disconnected users to the Edge Function authorize URL with `latchid_user_id` and `return_url`.
- Added LatchID TikTok service/controller plumbing, `LATCHID_SUPABASE_URL`, `LATCHID_SERVICE_ROLE_KEY`, and `TIKTOK_OAUTH_AUTHORIZE_URL` config, and a todo for the Edge Function `return_url` callback behavior because the deployed function source was not accessible in this repo/session.
- Validation: ran PHP syntax checks for the TikTok service, Studio LatchID controller, services config, routes, and account view; ran `php artisan view:cache`, `php artisan route:list --path=studio/latchid`, and `php artisan route:list --path=callback`.
- Hardened Studio light/dark mode across browsers by applying the selected theme to `<html>` before CSS loads, mirroring runtime toggles to `<html>` and `<body>` with `data-ll-theme`, `data-bs-theme`, and `color-scheme`, removing the hardcoded body light theme, and correcting dark-mode surface/text tokens.
- Updated Admin Dev Tools preview switching to use the same root/body theme sync and storage guards so temporary preview mode behaves consistently in Chrome, Safari, and Edge.
- Validation: ran PHP syntax checks for the sidebar layout and Dev Tools view, `node --check assets/js/detect-dark-mode.js`, and `php artisan view:cache`.
- Removed device `prefers-color-scheme` handling from Livelatch theme loading so the site no longer switches light/dark mode based on the user's OS or browser setting; explicit Livelatch theme selection is now the source of truth with light as the fallback.
- Updated the legacy Hope UI setting plugin, admin PHP info panel, and fallback LinkStack skeleton CSS to avoid automatic OS-driven dark mode.
- Validation: searched app-owned assets/views for remaining device theme hooks, ran PHP syntax checks for changed Blade views, ran JS syntax checks for changed theme scripts, and rebuilt Blade view cache.
- Updated the TikTok Edge Function owner todo with the observed `tiktok_user_fetch_failed` parser bug where TikTok returns HTTP 200 and `error.code: "ok"` with `data.user`, but the function still treats the lookup as failed.
- Validation: confirmed the Edge Function source is not present in this repo and Supabase MCP access requires authentication before remote function retrieval or deployment.
- Added the PostHog browser snippet to the Studio/sidebar layout, guarded the server-side PostHog PHP init behind `services.posthog.key`, and documented `POSTHOG_API_KEY` plus `POSTHOG_HOST` in `.env.example`.
- Validation: ran PHP syntax checks for the changed provider and sidebar Blade layout, then rebuilt Blade view cache.
- Added `docs/platform-runtime/posthog-analytics.md` documenting PostHog environment variables, Laravel config, Blade snippet placement, server-side initialization, and deployment cache checks.
- Validation: reviewed the new documentation entry and rebuilt Blade view cache.
- Added `docs/SEO Research/babylovegrowth-ai.md` as an early research note on BabyLoveGrowth.ai, including public positioning, possible Livelatch relevance, evaluation questions, SEO themes to test, and cautions around automated content/backlinks.
- Validation: reviewed the new SEO research note and confirmed the `docs/SEO Research` folder contents.
- Added server-side Supabase profile link click capture on the existing `/going/{id}` redirect path, preserving local `links.click_number` increments while inserting best-effort analytics rows through the Supabase REST API.
- Added `SUPABASE_PROFILE_LINK_CLICKS_TABLE` configuration, documented the Supabase table contract in `docs/supabase/profile-link-clicks.md`, and added a todo for creating/verifying the remote Supabase table because MCP is not authenticated in this session.
- Validation: ran PHP syntax checks for the new service, changed controller, and services config; rebuilt Laravel config and Blade caches.
- Extended the profile link click capture path to also emit a server-side PostHog `profile_link_clicked` event with link, profile, destination, referer, IP hash, and timestamp properties.
- Updated the Supabase profile-click and PostHog analytics docs with the event name, distinct ID strategy, and property list.
- Validation: ran PHP syntax checks for the click capture service and rebuilt Laravel config and Blade caches.
- Explicitly enabled PostHog JS `capture_pageleave`, pageview capture, autocapture, secure cookies, and current defaults in the shared browser snippet so `$pageleave` events are collected consistently.
- Expanded PostHog browser coverage across public profiles, auth/guest pages, maintenance/install/update pages, public static pages, the fallback home/demo/report/linkinfo pages, standalone admin utility pages, and the standalone `homepage-demo.php` marketing homepage.
- Updated `docs/platform-runtime/posthog-analytics.md` with the browser config, layout coverage, and expected event checks.
- Validation: ran PHP syntax checks for `homepage-demo.php` and the PHP info Blade view, rebuilt Blade view cache, and rebuilt Laravel config cache.

### 2026-06-12

- Agent: Codex
- Replaced the expanded Studio sidebar menu with a Livewire-powered icon section rail that is closed by default and reveals section links on click while preserving HTMX page loading.
- Fixed the Livewire sidebar section toggle 500 by removing the component view's dependency on the layout-local `llHtmxAttrs()` helper and rendering HTMX attributes directly.
- Removed Livewire AJAX from sidebar folder toggles so expanding menu sections is instant, animated, and does not interfere with HTMX page navigation.
- Renamed the Growth sidebar section to Community and added a new `/studio/socials` page linking to Discord, TikTok, Instagram, Threads, Bluesky, YouTube, X, and Reddit homepages.
- Added global sidebar layout theme rules for additional Bootstrap surfaces including alerts, pagination, list groups, `btn-light`, and light badges to reduce light/dark mode mismatches across Studio pages.
- Added `docs/platform-runtime/studio-navigation.md` documenting how the Livewire navigation is structured, edited, and validated.
- Added reusable HTMX skeleton loader partials for page, card-grid, table, and profile screen transitions, wired Studio navigation indicators to the appropriate skeletons, and documented the pattern in `docs/platform-runtime/htmx-skeleton-loaders.md`.
- Updated HTMX skeleton transitions so screen navigation hides the old `#ll-content` while the selected skeleton is showing instead of layering the skeleton over visible content.
- Added a universal reduced-motion-aware fade-and-settle transition for HTMX-loaded Studio content after skeleton loading completes.
- Added an admin-only `/admin/dev-tools` Studio screen for live, view-only design-token experiments with generated Codex instructions for applying approved CSS changes later.
- Extended Admin Dev Tools with light/dark mode-aware colour drafts, automatic opposite-mode colour pairing, preview toggles, and cleanup for temporary browser-only CSS values.
- Added Manage My Data compliance rendering from `docs/compliance/privacy.md` and `docs/compliance/tos.md`, plus placeholder data export, account deletion, and source-view actions.
- Added a `docs/todos/` owner follow-up workflow to `AGENTS.md` with 24-hour `ask_after` metadata and created the initial data collection logic todo.
- Reworked Studio navigation so Dashboard is a direct top-level link, renamed Navigation to MyLivelatch, kept Admin at the bottom, and hid legacy Config, Footer Pages, and Site Customization entries.
- Rebuilt the dashboard as a Livewire analytics screen with a dashboard-only quick-action hero, metric cards, top links, visit windows, and admin analytics panels.
- Fixed the dashboard 500 by removing legacy LinkStack analytics and visit queries from `/dashboard`; the dashboard now renders a Livewire demo analytics page with made-up values only.
- Fixed Admin Dev Tools dark-mode previews by applying temporary design-token values to both `:root` and `body`, and isolated the Studio theme preview so dashboard light/dark mode does not override the phone-style preview.
- Rebuilt the Studio add/edit block page to replace the old modal picker with an inline searchable block library and settings panel, avoiding the dashboard backdrop conflict.
- Moved block picker behavior into the swapped page content with plain JavaScript so it initializes correctly when loaded through the Studio sidebar.
- Added `docs/blocks/block-system.md` documenting the current block architecture and how to edit, add, remove, and validate blocks.
- Combined the Studio Links and Add Link navigation into one `/studio/links` manager with current links, drag-and-drop rearranging, inline add-block controls, and a phone-sized public profile preview.
- Updated the Studio sidebar so Links is the single Navigation entry for link/block management, with old add-more saves returning to the combined Links page.
- Updated the block system docs with the combined Links workflow, Sortable reorder endpoint, and live preview behavior.
- Validation: ran PHP syntax, Blade cache, and `/studio/add-link` plus `/studio/links` route checks for the rebuilt block and links editors.
- Agent: BitsAI
- Switched `/dashboard` to a sample-data analytics payload in `AdminController::index()` so login redirect and HTMX dashboard rendering stay stable while native analytics is being rewired.
- Added a dashboard disclaimer in the Livewire analytics view stating metrics are sample data and that Latchalytics is coming soon.
- Added `docs/platform-runtime/dashboard-analytics-data.md` documenting how native clicks/views were gathered, what sample mode now provides, and the planned PostHog/Latchalytics service rewire.
- Validation: ran `php -l app/Http/Controllers/AdminController.php`, `php -l app/Http/Livewire/DashboardAnalytics.php`, `php -l resources/views/panel/index.blade.php`, and `php -l resources/views/livewire/dashboard-analytics.blade.php`; attempted `php artisan view:cache` but it failed in this sandbox because `vendor/autoload.php` is missing.
- Agent: BitsAI
- Applied approved Studio design token values from Admin Dev Tools into `resources/views/layouts/sidebar.blade.php` for light and dark mode palettes, including new shared `--ll-button-radius` and approved heading/button weight tokens.
- Updated Studio button radius usage so dashboard and layout button classes use `--ll-button-radius` while surface components continue using `--ll-radius`.
- Updated `docs/platform-runtime/admin-dev-tools.md` with an approved-baseline note explaining how preview token values are promoted into the sidebar layout and how stable heading/button weight tokens are used.
- Validation: ran `php -l resources/views/layouts/sidebar.blade.php`; attempted `php artisan view:cache` but it failed in this sandbox because `vendor/autoload.php` is missing.
- Fixed a `/dashboard` 500 risk by hardening dashboard visit analytics so missing handles or visit-tracker failures now fall back to zeroed stats instead of crashing the page render.
- Fixed Studio content overlap on pages using `content-inner mt-n5` by neutralizing the negative top margin inside `#ll-content` so top text no longer sits under the sticky top bar.
- Validation: ran `php -l app/Http/Controllers/AdminController.php` and `php -l resources/views/layouts/sidebar.blade.php` and reviewed diffs for the dashboard stats guard and sidebar spacing rule.

### 2026-06-11

- Added the first browser-based MVP theme settings flow for `/studio/theme`, routing GET/POST requests to a new `Studio\ThemeController` backed by `ThemeService` and `user_theme_settings`.
- Replaced the legacy theme-management page content with a simple authenticated Blade form for selecting the published Livelatch Default theme preset, saving it through the service layer, and previewing basic manifest colors.
- Connected saved theme presets to public profile rendering through `ThemeService`, emitting `--ll-primary`, `--ll-background`, `--ll-text`, and `--ll-button-radius` CSS variables with a Livelatch Default fallback.
- Expanded the Studio theme preview so preset changes immediately update a mock page background, heading, sample button, and sample link card without auto-saving.
- Added default-theme color and Google Font family controls backed by `user_theme_settings.custom_settings`, with public profile rendering now merging saved overrides into the selected preset.
- Fixed `/studio/theme` initialization when loaded from the HTMX sidebar by keeping the editor script inside the swapped page content, and changed theme saves to an AJAX request that shows a success modal instead of refreshing the page.
- Hardened the Studio theme AJAX save request with an explicit CSRF header, same-origin credentials, and clearer server-status error handling for failed saves.
- Fixed a false-negative Studio theme save state where settings persisted but the browser still showed an error by accepting successful non-JSON responses and broadening controller JSON detection for AJAX requests.
- Added `docs/themes/theme-settings-mvp.md` to document the new route flow, data dependencies, retained legacy behavior, and next steps for the theme overhaul.
- Validation: ran PHP syntax, route, and Blade cache checks for the theme controller, service, Studio theme view, public theme module, and `/studio/theme` routes.

### 2026-06-10

!! Today marks a significant milestone with stripe integration. The user onboarding process is now functional and one step, or should I say one leap towards an MVP. When a new user is created, they are automatically assigned the free tier in Stripe (this will change based on paid sign ups in the future). There is a functioning accounts panel in the dashboard that pulls data from stripe. 

!!CHANGES MADE IN ASSISTANCE WITH CHATGPT
- Added `docs/laravel12 Migration Plan/roadmap.md` with a staged Laravel 12 LinkStack migration roadmap covering dependency jumps, high-conflict Livelatch areas, porting phases, validation gates, risks, and open decisions.
- Validation: reviewed existing docs layout, created documentation only, and confirmed the working tree was clean before editing.
- Reviewed the live Supabase LatchID project schema and added `docs/supabase/latchid-schema.md` documenting LatchID as the branded identity/data layer, Supabase as the backing platform, custom public tables, managed schemas, Livelatch joins, LatchDeck relations, and advisor follow-ups.
- Validation: inspected existing Supabase docs for style, queried Supabase tables, migrations, extensions, Edge Functions, project URL, and security/performance advisors; no database changes were made.
- Added the first Stripe billing foundation for Livelatch so every LatchID user can be represented in Stripe from signup onward.
- Created the `user_billing` persistence layer and related Laravel model/relationships to store Stripe customer, subscription, plan, and billing-status data locally.
- Added billing configuration, Stripe SDK integration, signup-time billing provisioning, and a backfill command for existing users to create Stripe customers and free subscriptions safely.
- Added billing routes/controller groundwork for subscription management, checkout, portal access, and invoice retrieval to support future paid plans and entitlements.
- Validation: existing users were backfilled and checked in Stripe and the local database; billing flow behavior was reviewed end to end for future signup handling.
- Reformatted `summary.md` to match `AGENTS.md` requirements, compressed the oversized narrative entry into concise bullets, preserved owner `!!` notes, and kept the fork-history section separate.
- Validation: reviewed `summary.md` structure against `AGENTS.md` conventions for headings, bullet style, fork-only scope, and note preservation.
- Updated the top-of-file fork coverage line so the commit count and end-of-range hash/date reflect the current `upstream/main..HEAD` range.
- Validation: checked `git -c safe.directory=G:/repos/livelatch-dev-docker rev-list --count upstream/main..HEAD` and `git -c safe.directory=G:/repos/livelatch-dev-docker log --reverse --format="%h %cs" upstream/main..HEAD`.
- Added a new studio documentation library powered by filesystem-scanned Markdown under `docs/`, with category discovery from folders, article discovery from `.md` files, and a default welcome page based on significant fork milestones.
- Added `DocumentationLibrary` and `DocumentationController`, new `/studio/docs` routes, HTMX article loading, JS-driven live search suggestions, category filtering, and a new Documentation entry in the studio sidebar.
- Authored initial docs for Livelatch meta, Supabase, LatchDeck, Stripe, Encore, platform runtime, and Open Graph so the library starts with the major architectural themes from the fork history.
- Validation: ran `php -l app\Services\DocumentationLibrary.php`, `php -l app\Http\Controllers\DocumentationController.php`, and `php artisan route:list --path=studio/docs`.
- Updated `AGENTS.md` so significant changes now trigger documentation review/update expectations, using the new 2-of-5 significance rule and explicit documentation-writing standards.
- Added `agent-docs-instructions.md` as an agent-only workflow index covering significance checks, doc maintenance steps, and current documentation areas for future sessions.
- Validation: reviewed the new instruction text against the existing `AGENTS.md` summary rules and confirmed the workflow still preserves owner approval for broader documentation cleanup or formatting changes.

### 2026-06-07

!! finished current ui overhaul of sidebar.blade.php
!! added new menu items
!! now there is a lightmode/dark mode switch, removing linkstacks dashboard UI configurability
!! started working on notification system, it works in supabase, but I have not been able to get it working on the website yet, this is still a work in progress.

### 2026-06-06

!! development of livelatch has resumed. It was on pause while I was working on an University assignment.
!! restored inactive supabase project and fixed google auth settings
!! laravel/lang had a security warning, as it is not used in this project, it was removed. I need to look in to updating laravel as linkstack devs are doing that on their branch. I can not simply merge their changes as livelatch has modified too much of the base code.
!! started UI overhaul of sidebar.blade.php

### 2026-05-18

!!Manual DB change made:
!!users.supabase_user_id CHAR(36) NULL UNIQUE
!!Purpose:
!!Bridge Laravel users to Supabase LatchID auth.users.id
- Wired the existing `homepage-demo.php` LatchID Google button to Supabase Auth using `SUPABASE_URL` and `SUPABASE_ANON_KEY`, with `/` now serving that demo homepage through `HomeController` when the file exists.
- Added `/callback/google` and session-backed `POST /api/latchid/session`; the callback completes the Supabase browser session, posts the Supabase user details and access token to Laravel, links or creates the local user, logs them in, and redirects to `/dashboard`.
- Updated `config/services.php`, `.env.example`, `app/Models/User.php`, `app/Http/Controllers/Auth/LatchIdSessionController.php`, `routes/web.php`, and `resources/views/auth/latchid-google-callback.blade.php`; Google is the only enabled MVP provider.
- Validation: visit `https://dev.livelatch.com`, complete Google auth through the LatchID modal, confirm `users.supabase_user_id` is populated, and verify the user lands on `/dashboard`.

### 2026-05-16

- Expanded `homepage-demo.php` with static pricing plans, yearly/monthly toggles, a comparison table, and a client-side Team seat estimator to explore the Livelatch marketing direction without backend changes.
- Reworked the standalone homepage concept around exported `/logos` assets, broader product-suite sections, and improved gamer/streamer visual polish while keeping auth flows demo-only.
- Added theme-aware logo handling plus a light/dark mode switcher with persisted preference for the homepage demo.
- Validation: ran `php -l homepage-demo.php`; syntax passed, with the existing local PHP timezone warning for configured value `+8`.

### 2026-05-14

- Added `AGENTS.md` with repo-level instructions covering scoped fork work, required `summary.md` updates, owner `!!` note handling, and PNG output expectations for generated Open Graph images.
- Replaced the previous full-repository digest with a fork-only summary based on `upstream/main..main` and expanded the 2026-05-14 entry to list the full fork-specific commit range.
- Kept the summary focused on Livelatch-specific deployment, S3 media, dashboard avatar, and Open Graph work rather than inherited upstream history.

## Fork History Digest

### 2026-05-12

15 commits: `8e19376` through `81c76d8`.

- Platform identity: reworked the README to present the project as Livelatch and removed or renamed install docs that no longer fit the Railway-oriented setup.
- Composer and runtime alignment: updated `composer.json` and `composer.lock`, added S3-compatible dependencies, kept `composer.lock.old` during recovery work, and restored Laravel language/runtime files needed after dependency changes.
- Railway deployment: added `railpack.json`, adjusted app config and routes to avoid post-deploy Artisan/runtime errors, and added a clean redeploy trigger commit.
- Routing and UI cleanup: repaired route/view wiring across `routes/web.php`, `routes/home.php`, the PHP info view, and sidebar-related layout work.
- Representative commits: `8e19376`, `faeb738`, `8de0844`, `7a27f03`, `2fa2778`, `78024b8`, `81c76d8`.

### 2026-05-14

12 commits: `6b5e32b` through `c45776a`.

Commit list:
- `6b5e32b` - Update `AdminController.php`.
- `a06a373` - Add S3 implementation for profile photo storage.
- `94fb68f` - Fix profile photo rendering in the UI.
- `6e46408` - Resolve the correct S3 path for profile photos and add the `profile_image` storage path.
- `7d6435d` - Add profile image media caching/proxy logic to avoid exposing AWS credentials.
- `41707fc` - Add dashboard and studio rendering logic for S3-backed profile photos.
- `f2d2138` - Edit README.
- `750cab6` - Add dynamic Open Graph and Twitter Card metadata for public profile pages.
- `67e21b9` - Add the internal Open Graph editor.
- `cc59f68` - Add the generated Open Graph card design.
- `e2209c7` - Fix Discord compatibility for Open Graph previews.
- `c45776a` - Fix PNG preview resolution and rendering quality.

- Private S3 profile image storage: moved profile photo writes from local `assets/img` paths to Laravel S3 uploads, added safer path handling, and introduced `users.profile_image` plus related controller/model changes.
- Profile image rendering: added shared resolution logic for empty/default images, legacy local files, S3 object paths, and full URLs so dashboard, studio, public, sidebar, and admin views render avatars safely.
- Private media proxy: added `MediaController` and public media routes to serve private S3-backed profile images through app URLs with cache headers and fallback behavior.
- Open Graph and Discord previews: added dynamic profile metadata, a generated preview route, the internal `opengraph.php` editor, PNG-based Discord previews, and higher-quality rendering behavior.
!! I need to look in to font rendering for opengraph, please provide some suggestions for this.
- Documentation: made a small README edit after the media/Open Graph work.
- All commits for this date are listed above.

## Ongoing Summary Rules

- After each future code/config/doc change, append a dated entry under **Recent Changes**.
- Keep entries concise and focused on what changed, why it changed, affected files or work areas, and validation performed.
- Preserve owner-authored `!!` lines exactly unless explicitly asked to rewrite them.
- As this file grows, compress older detailed entries into shorter dated summaries instead of removing history.
