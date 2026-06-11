# Livelatch Fork Summary

This file tracks changes made in the Livelatch fork only. It excludes inherited upstream LinkStack history.

Fork range used for the initial backfill: `upstream/main..main`.

Current coverage: 71 fork commits from `8e19376` on 2026-05-12 through `da2fde9` on 2026-06-10.

## Recent Changes

### 2026-06-11

- Added the first browser-based MVP theme settings flow for `/studio/theme`, routing GET/POST requests to a new `Studio\ThemeController` backed by `ThemeService` and `user_theme_settings`.
- Replaced the legacy theme-management page content with a simple authenticated Blade form for selecting the published Livelatch Default theme preset, saving it through the service layer, and previewing basic manifest colors.
- Connected saved theme presets to public profile rendering through `ThemeService`, emitting `--ll-primary`, `--ll-background`, `--ll-text`, and `--ll-button-radius` CSS variables with a Livelatch Default fallback.
- Expanded the Studio theme preview so preset changes immediately update a mock page background, heading, sample button, and sample link card without auto-saving.
- Added `docs/themes/theme-settings-mvp.md` to document the new route flow, data dependencies, retained legacy behavior, and next steps for the theme overhaul.
- Validation: ran PHP syntax, route, and Blade cache checks for the new controller and `/studio/theme` routes.

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
