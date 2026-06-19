# LatchDeck Studio (Livelatch client)

This describes the **Livelatch** side of LatchDeck — the human-facing studio UI.
For the overall system see [latchdeck-architecture.md](latchdeck-architecture.md);
for the API see [latchdeck-api.md](latchdeck-api.md).

> Historical note: LatchDeck started as static placeholder Blade views
> (`f756ab2`, `c9ac44b`, `390d5d7`, `3646dd7`). As of 2026-06-19 the studio is a
> live, access-gated client of the LatchDeck (Encore) API.

## How it works

Livelatch holds **no LatchDeck state**. Every screen is driven by a single call
to the Encore API (`GET /access-status/:latchid_user_id`) and rendered by
`App\Http\Controllers\Studio\LatchDeckController`.

- **Service:** `App\Services\LatchDeckService` — thin HTTP client to Encore,
  authenticated with the LatchDeck *service* API key
  (`config('services.latchdeck.*')`). Fails soft (logs + safe defaults) so the
  studio never breaks if LatchDeck is unreachable.
- **Routes** (`routes/web.php`, auth group): `studio.latchdeck` (hub) plus POST
  routes for `request-access`, `cards.store`, `cards.update`, `cards.publish`,
  `cards.unpublish`.
- **Nav:** a single LatchDeck entry (`StudioNavigation`) → the state-driven hub.
- **Views:** `studio/latchdeck/index.blade.php` switches on access status and
  includes `partials/request-access.blade.php` or `partials/deck.blade.php`.

## States the hub renders

| Status | Screen |
| ------ | ------ |
| `not_applied` | Request-access page: what LatchDeck is, free/pro/SDK comparison, request form (`POST /applications`). |
| `pending_review` | Pending banner + card editor. Drafts can be saved; **Publish is disabled**. |
| `active` | Full editor + card grid. Publish enabled; premium rarities shown but locked for free tier. |
| `denied_waitlist` / `restricted` / `revoked` | Status message. |
| (no LatchID / API down) | Friendly fallback. |

## Cards & images

- Create saves a **draft** via the API. Publish/Unpublish call the gated API
  endpoints; tier/approval errors from Encore are surfaced as flash messages.
- Card art is uploaded to Livelatch's **S3** disk (public) and the URL is passed
  to Encore as `image_url_mvp`. Livelatch stores no card data itself.

## Tier sync

`LatchDeckController` maps the Livelatch plan (`User::planKey()` from
`user_billing`) to a LatchDeck tier and **self-heals** it into Encore on page
load when they differ (no billing webhook yet). Pro controls are visible but
disabled for free users.
