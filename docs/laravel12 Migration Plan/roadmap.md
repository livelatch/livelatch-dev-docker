# Laravel 12 Migration Roadmap

This roadmap covers a future Livelatch migration from the current Laravel 9-based LinkStack fork to the Laravel 12 LinkStack work in [JulianPrieber/ls12](https://github.com/JulianPrieber/ls12). Treat this as a porting plan, not a simple dependency update.

## Migration stance

The safest path is to start from the Laravel 12 LinkStack codebase, then port Livelatch product work across in controlled slices.

Avoid a direct Composer-only upgrade in the current branch. Livelatch has changed core LinkStack behavior across routing, controllers, auth, media, billing, the sidebar shell, public profile rendering, and documentation. A direct merge would create broad conflicts without giving a clean runtime baseline.

Recommended working branch:

```text
livelatch-laravel12-port
```

Recommended source direction:

```text
ls12 Laravel 12 base
-> port Livelatch runtime changes
-> port LatchID auth
-> port media and Open Graph
-> port billing
-> port studio/sidebar/product surfaces
-> validate end to end
```

## Known dependency jumps

The Laravel 12 LinkStack code changes several important dependencies:

- [Laravel](https://laravel.com/) `^9.52.4` to `^12.0`
- [Livewire](https://livewire.laravel.com/) `^2.12` to `^3.5`
- [Rappasoft Laravel Livewire Tables](https://rappasoft.com/docs/laravel-livewire-tables) `^2.15` to `^3.0`
- [Spatie Laravel Backup](https://spatie.be/docs/laravel-backup) `^8.1.5` to `^9.1`
- [PHPUnit](https://phpunit.de/) `^9.3` to `^11.5`
- `doctrine/dbal` `^3.0` to `^4.0`
- `symfony/yaml` support expands from `^6.0` to `^6.0|^7.0`

Laravel 12 also uses newer application bootstrap conventions. The target base has middleware and providers registered through `bootstrap/app.php`, so Livelatch runtime changes must be reviewed against that structure.

## High-conflict Livelatch areas

These areas should be assumed to need manual porting:

- `composer.json` and `composer.lock`
- `bootstrap/app.php` and middleware registration
- `routes/web.php` and `routes/home.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/Auth/SocialLoginController.php`
- `app/Models/User.php`
- `app/Functions/functions.php`
- `resources/views/layouts/sidebar.blade.php`
- `resources/views/layouts/app.blade.php`
- public profile views under `resources/views/linkstack/`
- profile/admin Blade views under `resources/views/panel/` and `resources/views/studio/`
- `config/app.php`, `config/filesystems.php`, and `config/services.php`
- local migrations added by Livelatch

The sidebar and `routes/web.php` are especially sensitive because they now carry product identity, HTMX behavior, LatchDeck navigation, notification rendering, billing links, docs links, and account surfaces.

## Phase 1: Baseline branch and inventory

Goal: create a clean Laravel 12 working branch before porting features.

Tasks:

- Add the Laravel 12 LinkStack repository as a comparison remote.
- Create a new branch dedicated to the port.
- Confirm PHP version, extension availability, and deployment compatibility.
- Run `composer install` or `composer update` on the Laravel 12 base.
- Run the base app with an empty or copied development database.
- Capture a file-level inventory of Livelatch changes that need porting.
- Separate product code from experimental/demo code.

Validation:

```bash
php artisan about
php artisan route:list
php artisan migrate:status
```

Exit criteria:

- Laravel 12 base boots locally.
- Routes can be listed.
- No Livelatch code has been ported yet.
- Known dependency and runtime blockers are documented.

## Phase 2: Runtime and configuration

Goal: make the Laravel 12 base match Livelatch deployment expectations.

Tasks:

- Port Railway/runtime configuration such as `railpack.json`.
- Reapply required `.env.example` entries.
- Port filesystem configuration for S3-compatible profile media.
- Reapply service configuration for Supabase, Stripe, and any public callback URLs.
- Review middleware aliases in `bootstrap/app.php`.
- Recreate any Laravel 9 kernel behavior that Laravel 12 now expresses through the new bootstrap file.
- Confirm cache, session, queue, mail, and logging config still match deployment expectations.

Considerations:

- Do not blindly copy old Laravel 9 config files over Laravel 12 config files.
- Prefer Laravel 12 defaults unless Livelatch has a clear product or deployment requirement.
- Make sure `APP_URL`, forced HTTPS behavior, and callback URLs are correct before testing auth.

Validation:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan route:list
```

## Phase 3: Database and models

Goal: preserve local Livelatch data structures on the Laravel 12 base.

Tasks:

- Port `users.supabase_user_id` handling.
- Port `users.profile_image` migration and rendering assumptions.
- Port `user_billing` migration, model, and `User` relationship.
- Confirm LinkStack base migrations have not changed in a way that conflicts with Livelatch columns.
- Confirm existing production data can migrate forward without destructive table rebuilds.
- Check whether local migrations should be squashed or left as fork-specific migration history.

Important identity join:

```text
Supabase auth.users.id
-> Laravel users.supabase_user_id
-> Laravel users.id
-> user_billing.user_id
```

Validation:

```bash
php artisan migrate --pretend
php artisan migrate
php artisan migrate:status
```

## Phase 4: LatchID authentication

Goal: restore LatchID signup/login behavior before porting dependent product surfaces.

Tasks:

- Port `LatchIdSessionController`.
- Port `/callback/google` and `/api/latchid/session`.
- Port the callback Blade view.
- Reapply Supabase service config.
- Confirm Laravel still verifies Supabase sessions server-side.
- Confirm user linking by `supabase_user_id` and email mismatch protection.
- Confirm first-time users are created locally and logged into the studio.
- Confirm Stripe provisioning does not run before the local user exists.

Validation:

```text
Visit homepage
-> click LatchID Google login
-> complete Supabase session
-> post session into Laravel
-> local user linked or created
-> user lands in studio/dashboard
```

## Phase 5: Media and public profile rendering

Goal: restore S3-backed profile image behavior and public profile compatibility.

Tasks:

- Port `MediaController`.
- Port media proxy routes.
- Port `UserController` profile image upload behavior.
- Port avatar resolution helpers in `app/Functions/functions.php`.
- Port `users.profile_image` usage in Blade views.
- Confirm legacy local images, default images, full URLs, and S3 object paths still render correctly.
- Confirm private S3 URLs are not exposed directly in public profile HTML.

Validation:

```text
Upload profile image
-> file stores in S3-compatible storage
-> dashboard avatar renders
-> public profile avatar renders through app URL/proxy
-> missing image falls back safely
```

## Phase 6: Open Graph previews

Goal: restore public profile preview cards after profile rendering is stable.

Tasks:

- Port Open Graph meta changes in `resources/views/linkstack/modules/meta.blade.php`.
- Port preview generation route/controller behavior.
- Port `opengraph.php` only if the internal editor is still wanted.
- Preserve PNG output for generated Open Graph preview cards.
- Recheck Discord, X/Twitter, and generic crawler metadata.

Validation:

```text
Open public profile
-> inspect og:image metadata
-> fetch generated preview image
-> confirm PNG response
-> test a Discord preview manually
```

## Phase 7: Stripe billing

Goal: restore billing after identity and user persistence are stable.

Tasks:

- Port `config/billing.php`.
- Port `UserBilling` model.
- Port `BillingController`.
- Port billing routes.
- Port signup-time billing provisioning.
- Port backfill command for existing users.
- Confirm Stripe metadata still links local user IDs and LatchID IDs.
- Confirm billing dashboard/account views work under Laravel 12.

Validation:

```bash
php artisan list | findstr /i stripe
php artisan billing:backfill-stripe
```

Manual validation:

```text
New LatchID signup
-> local user created
-> Stripe customer created
-> free subscription created
-> user_billing row stored
-> studio subscription page renders
```

## Phase 8: Studio, sidebar, docs, and product areas

Goal: restore the Livelatch studio shell and product navigation.

Tasks:

- Port the rewritten `resources/views/layouts/sidebar.blade.php`.
- Reapply light/dark mode behavior.
- Reapply HTMX-aware navigation helpers.
- Port studio account pages.
- Port LatchDeck placeholder pages and routes.
- Port growth pages.
- Port documentation library service, controller, routes, and docs views.
- Confirm the docs category system can handle the new documentation folders.

Considerations:

- The Laravel 12 base includes Livewire 3, so any Livewire navigation or admin table behavior needs regression testing.
- Livelatch currently leans on Blade and HTMX for several new surfaces. Keep that boundary clear rather than mixing Livewire and HTMX accidentally.

Validation:

```bash
php artisan route:list --path=studio
php artisan route:list --path=studio/docs
```

Manual validation:

```text
Dashboard loads
-> sidebar renders
-> notifications render or fail safely
-> docs load
-> LatchDeck pages load
-> subscription/account pages load
```

## Phase 9: Notifications and Supabase data reads

Goal: restore Livelatch notifications without weakening the LatchID trust boundary.

Tasks:

- Port `LivelatchNotificationService`.
- Fix the current unusual service path if needed: `app/Services/app/Services/LivelatchNotificationService.php`.
- Port notification sidebar rendering.
- Port or retire debug routes intentionally.
- Confirm service role usage is server-side only.
- Confirm global notifications and user-specific notifications both render.

Validation:

```text
Signed-in user with supabase_user_id
-> sidebar requests notifications
-> global notifications appear
-> user-specific notifications appear
-> unread count is correct
-> Supabase failure does not break the studio shell
```

## Phase 10: Cleanup, tests, and deployment rehearsal

Goal: turn a working port into a deployable branch.

Tasks:

- Remove temporary comparison files and obsolete backup files.
- Review `.env.example` for complete Laravel 12, Supabase, Stripe, S3, and Railway settings.
- Run PHP syntax checks over changed app files.
- Run route listing across public, studio, admin, billing, docs, and media paths.
- Run Composer audit if available.
- Run frontend build if assets are changed.
- Test production-like cache commands.
- Test a Railway-style deployment build in a staging environment.

Validation:

```bash
composer validate
php artisan route:list
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## Risk register

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Direct merge overwrites Livelatch sidebar/routes | High | Port feature slices manually |
| Laravel 12 bootstrap changes break middleware | High | Rebuild middleware registration in `bootstrap/app.php` |
| Livewire 3 changes break admin tables or SPA behavior | Medium | Test admin users, link type management, and any `wire:navigate` paths |
| S3 media paths regress | High | Validate upload, proxy, public profile, and fallback paths before billing work |
| LatchID login creates duplicate users | High | Test `supabase_user_id` and email mismatch behavior early |
| Stripe provisioning runs twice | High | Keep idempotent checks around `user_billing` and Stripe customer IDs |
| Supabase notification reads expose service key | High | Keep service role usage server-side only |
| Open Graph PNG generation changes | Medium | Test generated response headers and Discord preview behavior |
| Production data has columns missing from fresh migrations | High | Compare production schema before deployment |

## Decision gates

Use these checkpoints to decide whether the migration is ready to continue:

- Gate 1: Laravel 12 base boots and routes list.
- Gate 2: Livelatch database migrations apply cleanly.
- Gate 3: LatchID login works for new and existing users.
- Gate 4: profile image upload and public rendering work.
- Gate 5: Stripe signup and subscription page work.
- Gate 6: studio sidebar, docs, LatchDeck, and account surfaces work.
- Gate 7: production-like caches and deployment build pass.

Do not proceed to production until all gates pass in staging.

## Open questions

- Should Livelatch keep the current HTMX studio approach, adopt more of LinkStack ls12's Livewire 3 behavior, or keep the two clearly separated?
- Should `latchdeck_cards_mvp` remain a Supabase MVP table, or should the Laravel 12 port wait until the LatchDeck data model is finalized?
- Should the Supabase LatchID schema be captured into migrations before or during the Laravel 12 port?
- Should inherited LinkStack language files be refreshed from ls12 or left alone unless the UI requires them?
- Should the standalone `homepage-demo.php` become a normal Laravel route/view during the port?

## Estimated effort

Rough order of magnitude:

- Rough booting Laravel 12 Livelatch branch: 2 to 4 days
- Core feature port and stabilization: 1 to 2 weeks
- Production-grade migration with staging, regression testing, cleanup, and deployment confidence: 2 to 4 weeks

The estimate assumes no major production data surprises and no decision to redesign the studio during the port.
