# Livelatch Fork Summary

This file tracks changes made in the Livelatch fork only. It excludes inherited upstream LinkStack history.

Fork range used for the initial backfill: `upstream/main..main`.

Current coverage: 71 fork commits from `8e19376` on 2026-05-12 through `da2fde9` on 2026-06-10.

## Recent Changes

### 2026-06-13

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
