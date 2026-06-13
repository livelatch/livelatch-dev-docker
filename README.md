# Livelatch

![AGPL-3.0 License](https://img.shields.io/github/license/livelatch/livelatch-dev-docker)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Flivelatch%2Flivelatch-dev-docker.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Flivelatch%2Flivelatch-dev-docker?ref=badge_shield)
![Status](https://img.shields.io/badge/status-alpha-orange)

Livelatch is a creator-focused Laravel platform for link pages, identity linking, studio tooling, and the next layer of creator/community features.

---

## What This Repo Is

This project began as a fork of LinkStack and now has its own direction, identity, and operating model.

The current codebase includes:

- public creator profiles and link pages
- a Laravel studio and admin surface
- Supabase-backed LatchID identity flows
- Stripe billing foundations
- HTMX-powered page swaps
- Livewire navigation and admin surfaces
- S3/object-storage backed media and previews
- ongoing creator tooling such as LatchDeck, social connections, and affiliate planning

Livelatch is not presented as LinkStack. It is a separate product with a different roadmap and deployment shape.

---

## Core Stack

### Laravel / PHP

Laravel is the app shell, route layer, controller layer, and server-rendered view system.

High-level config:

- install PHP 8.2 with the extensions required by `composer.json`
- run `composer install`
- copy `.env.example` to `.env`
- set `APP_NAME`, `APP_URL`, and `APP_KEY`
- set the database credentials
- run `php artisan key:generate`
- run the migrations for the environment you are deploying

### PostgreSQL

PostgreSQL stores the local application data and powers the app-level tables.

High-level config:

- create a PostgreSQL database
- set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`
- run migrations after the schema is ready
- keep the database available for user profiles, billing state, studio content, and integration metadata

### Supabase

Supabase is the identity layer for LatchID and the home of several connection flows.

TL;DR:

- handles OAuth and identity linking
- backs the TikTok Edge Function flow
- stores linked provider account state through the Laravel server-side integration

High-level config:

- set `SUPABASE_URL` and `SUPABASE_ANON_KEY`
- configure the required Supabase Auth providers and callback URLs
- keep `LATCHID_SUPABASE_URL`, `LATCHID_SERVICE_ROLE_KEY`, and `TIKTOK_OAUTH_AUTHORIZE_URL` server-side only
- do not expose service role credentials to Blade or frontend JavaScript

### Stripe

Stripe is the billing and subscription foundation.

TL;DR:

- powers checkout and portal flows
- stores billing customer/subscription state
- keeps the SaaS layer ready for paid features later

High-level config:

- set the Stripe secret key and any price IDs in env/config
- configure webhooks if the billing flow depends on them
- verify the checkout and portal URLs in the target environment
- keep billing metadata aligned with the local user record and Supabase identity

### HTMX and Livewire

HTMX and Livewire are used to make the studio feel like a product surface instead of a simple static admin page.

TL;DR:

- HTMX handles partial page swaps in the studio
- Livewire powers the section navigation and interactive admin/studio patterns

High-level config:

- keep Blade views organized around partial swaps
- keep HTMX skeletons and the main content container in sync
- make sure Livewire components remain compatible with the current sidebar structure

### Frontend Build

The current build uses Laravel Mix, Tailwind CSS, Alpine.js, and GSAP.

High-level config:

- run `npm install`
- use `npm run dev` while developing
- use `npm run production` for release builds
- keep compiled assets aligned with any Blade or navigation changes

### Storage and Media

The app uses object storage for media and preview-related assets.

High-level config:

- configure the S3-compatible credentials for the target environment
- set the bucket and public/private access behavior as needed
- verify profile media, uploaded assets, and Open Graph previews after deployment

### Mail

Mail is used for transactional and notification flows.

High-level config:

- set the mail transport env vars in `.env`
- verify sender addresses before going live
- test the notification and onboarding paths in staging before launch

---

## External Services

### [Supabase](https://supabase.com)

Used for identity and account linking.

TL;DR:

- auth and OAuth
- linked provider state
- TikTok account linking through Edge Functions

### [Railway](https://railway.com)

Used as the deployment target for the current Laravel app.

TL;DR:

- host the app
- configure app/database/storage services
- keep runtime env vars synced with production

### [Encore](https://encore.dev)

Planned for future backend/service work, not yet a core runtime dependency.

TL;DR:

- likely candidate for future event-driven or service-layer work
- keep it separate from the Laravel request path until it is actually introduced

### [Refferq](https://github.com/Refferq/Refferq)

Planned affiliate platform integration.

TL;DR:

- future affiliate portal and referral engine
- should live as a separate service
- Livelatch should hand off to it instead of reimplementing the whole stack

### [Codex](https://openai.com/codex)

Used as the coding agent for repo work and documentation updates.

TL;DR:

- applies changes to the codebase
- writes doc updates and summary entries
- helps keep the fork history readable

### [Datadog](https://www.datadoghq.com)

Used for observability context and operational monitoring.

TL;DR:

- logs, metrics, and application visibility
- useful for deployment debugging and runtime health
- should track the services that matter most in production

### [FOSSA](https://fossa.com)

Used for license and dependency compliance visibility.

TL;DR:

- tracks license posture
- supports AGPL/compliance review
- should remain in the repo because it documents legal and dependency risk

---

## Local Development

Typical first-run flow:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

If you are testing integrations, configure the relevant provider before exercising the feature.

---

## Configuration Checklist

Before deploying or testing major features, confirm:

- `APP_URL` matches the real host
- database credentials are correct
- Supabase Auth providers and callback URLs are configured
- Stripe keys and price IDs are present if billing is enabled
- storage credentials are present if uploads or previews are enabled
- mail transport settings are present if notifications are enabled
- OAuth provider settings match the current callback routes
- privileged secrets stay server-side only

---

## Relationship to LinkStack

Livelatch started as a fork of LinkStack.

LinkStack remains part of the project history, but Livelatch is now being shaped into its own platform with a different identity, deployment model, and roadmap.

Upstream project links:

- https://linkstack.org
- https://github.com/LinkStackOrg/LinkStack

---

## LatchDeck

LatchDeck is a planned subsystem focused on creator-linked digital collectibles and community engagement.

High-level goals include:

- creator collectible cards
- viewer engagement systems
- event-based digital collections
- supporter interaction mechanics
- future gameplay integrations

---

## Documentation

Useful internal docs:

- [Fork history](docs/livelatch-meta/fork-history.md)
- [Agent summary](docs/livelatch-meta/agent-summary.md)
- [Supabase / LatchID authentication](docs/supabase/latchid-authentication.md)
- [Stripe billing foundation](docs/stripe/stripe-billing-foundation.md)
- [Affiliate program setup](docs/platform-runtime/affiliate-program.md)
- [Development timeline](resources/views/studio/admin/development-timeline.blade.php)

External references:

- https://laravel.com/docs
- https://docs.railway.com
- https://supabase.com/docs
- https://stripe.com/docs

---

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Flivelatch%2Flivelatch-dev-docker.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Flivelatch%2Flivelatch-dev-docker?ref=badge_large)

## Security

Please do not publicly disclose security issues without first contacting the maintainers privately.

Security procedures will expand as the platform matures.

---

## Contributing

Contributions are welcome, but keep changes focused and aligned with the current platform direction.

Before contributing:

- review the license
- avoid committing secrets or credentials
- document major platform changes
- keep pull requests scoped to one theme where possible

---

## License

This project is licensed under the GNU Affero General Public License v3.0.

See the `LICENSE` file for details.

---

## Acknowledgements

Special thanks to:

- the LinkStack maintainers and contributors
- the Laravel community
- the open source ecosystem
- early testers and supporters of Livelatch

Livelatch started from a fork, but it is now being shaped into its own platform.
