# LatchDeck Architecture (alpha)

LatchDeck lets creators design collectible cards and (later) run claim/redemption
campaigns. It is **platform-agnostic**: Livelatch is the first client, but the
same API is meant to be used by other apps and games in future.

This document describes the alpha system as built on 2026-06-19.

## The three systems

| System | Repo | Role |
| ------ | ---- | ---- |
| **Encore** | `ld/latchdeck` | The LatchDeck **API and authority**. Owns all business logic (access, cards, tiers, publishing) and talks to the database. Deployed on Encore Cloud (`https://staging-latchdeck-7iu2.encr.app`). |
| **Supabase** | project `yaljyfdfnphxzuhqlbfs` | The **data store**. Encore connects over a direct Postgres connection (`SUPABASE_DB_URL`). |
| **Livelatch** | `livelatch-dev-docker` | One **human-facing client**. A thin UI over the Encore API; holds no LatchDeck state of its own. |
| **LatchOps** | `latchops` | **Operator tooling**. Reviews and approves/denies applications via the Encore admin API. |

```
Livelatch (Laravel)  ──Bearer service key──▶  Encore API  ──direct Postgres──▶  Supabase
LatchOps (Electron)  ──Bearer admin key────▶  Encore API (/admin/*)
                          (future) SDK app ──Bearer per-app key──▶ Encore API
```

**Key principle:** nothing except Encore writes the LatchDeck tables. Livelatch
and LatchOps never touch Supabase's `latchdeck_*` tables directly — they call the
Encore API. This is what keeps LatchDeck portable: a game developer would call
the same endpoints with their own key.

## Data model (Supabase, owned by Encore)

- `latchdeck_creators` — one row per creator. `status` (`not_applied` is implicit
  / no row, `pending_review`, `active`, `denied_waitlist`, `restricted`,
  `revoked`), `tier` (`free`/`pro`/`sdk`), `tutorial_seen`, `first_card_created`.
- `latchdeck_applications` — access requests. `status`, `platform`,
  `community_context`, `reason`, `reviewed_by`, `decision_reason`.
- `latchdeck_cards_mvp` — the live card table. Card content plus
  `status_mvp` (`draft`/`published`) and `published_at_mvp`.
- `latchdeck_status_history`, `latchdeck_restrictions` — lifecycle/audit.

Schema changes for alpha are captured in
`ld/latchdeck/migrations/20260619_card_draft_publish_and_creator_tier.sql`.

> RLS is **not** the enforcement layer here. Encore connects as the database
> owner (direct Postgres), so RLS is bypassed by design — all rules live in the
> Encore API code, which is the single writer.

## Access lifecycle

1. **not_applied** — no creator row. Livelatch shows the *Request access* page
   (what LatchDeck is + free/pro/SDK comparison + a request form).
2. **pending_review** — `POST /applications` created the creator + an
   application. The creator can **design and save draft cards**, but **publishing
   is disabled**.
3. **active** — an operator approved the application in LatchOps. The creator can
   **publish** within their tier limits. On approval, Encore also writes a
   `livelatch_notifications` row so the user sees an in-product "approved" message.
4. **denied_waitlist / restricted / revoked** — Livelatch shows the appropriate
   status message.

## Cards: draft → publish

- Cards are always **created as drafts** (`createCardMvp`), allowed for
  `pending_review` and `active` creators.
- **Publishing** (`publishCardMvp`) requires the creator to be `active` and to be
  within their tier's `maxPublishedCards`. This gate lives in Encore, so it holds
  for every client (Livelatch, future SDK apps).
- `unpublishCardMvp` retracts a card to draft.

## Tiers (entitlement)

`free`, `pro`, `sdk` — see `ld/latchdeck/latchdeck/tiers.ts`. The capability
numbers are **placeholders** (skeleton) so the gating mechanism exists end to
end; tune them once product rules are set.

- `free` / `pro` are **human** creators. Livelatch is the source of truth for the
  plan (Stripe `user_billing.plan_key`) and **syncs** it into LatchDeck:
  - on application (`tier` in the request body), and
  - self-healing on each LatchDeck page load (`POST /creators/:id/tier`) when the
    Livelatch plan and LatchDeck tier disagree (there is no billing webhook yet).
  - Pro-only controls are shown but locked in the Livelatch UI for free users.
- `sdk` is **reserved** for application/developer clients authenticated by their
  own API key. Not issued in alpha — Encore is where that will live.

## Authentication & keys

All Encore endpoints require a Bearer API key (no public access). Two keys today,
configured as Encore secrets:

| Key (Encore secret) | Held by | Scope |
| ------------------- | ------- | ----- |
| `LATCHDECK_SERVICE_API_KEY` | Livelatch (`LATCHDECK_SERVICE_API_KEY` env) | All non-admin endpoints |
| `LATCHDECK_ADMIN_API_KEY` | LatchOps (Settings → admin key) | Adds `/admin/*` (approve/deny, list applications) |

The admin key is a superset of the service key. The auth handler
(`ld/latchdeck/latchdeck/auth.ts`) is the seam where future **per-developer SDK
keys** will be validated.

For alpha, Livelatch is trusted to send the correct `latchid_user_id` (it
authenticates the user via its own session). Admin actions additionally require
the admin key.

### Where each key goes

- **Encore Cloud** (dashboard → Secrets, or `encore secret set`):
  `LATCHDECK_SERVICE_API_KEY`, `LATCHDECK_ADMIN_API_KEY` (and the existing
  `SUPABASE_DB_URL`).
- **Livelatch** (`.env` / Railway vars): `LATCHDECK_API_URL`,
  `LATCHDECK_SERVICE_API_KEY` (the service key). Read via
  `config('services.latchdeck.*')`.
- **LatchOps** (Settings UI, stored in `latchops-data` only): LatchDeck Encore API
  URL, LatchDeck admin API key, optional operator LatchID UUID.

## Card images

Card art is uploaded by Livelatch to its existing **S3** disk (public visibility)
under `latchdeck/cards/{latchid_user_id}/`, and the resulting URL is sent to
Encore as `image_url_mvp`. The S3 bucket must allow public reads so cards can
render anywhere (platform-agnostic). LatchDeck/Encore only stores the URL.

## Deferred (not in alpha)

- Redemptions, claim campaigns, real-time (these are the reason Encore exists —
  it handles this better than Supabase per the product research).
- SDK API-key issuance and the developer/`sdk` tier in practice.
- A Stripe webhook syncing `plan_key` → tier (today it self-heals on page load).
- Inline editing UI for existing cards (the `PATCH /mvp/cards/:id` endpoint exists
  and is wired in `LatchDeckService`, but the studio currently focuses on
  create + publish/unpublish).

## File map

- Encore: `ld/latchdeck/latchdeck/{auth,tiers,latchdeck,cards_mvp}.ts`
- Livelatch: `app/Services/LatchDeckService.php`,
  `app/Http/Controllers/Studio/LatchDeckController.php`,
  `resources/views/studio/latchdeck/{index,partials/request-access,partials/deck}.blade.php`,
  routes in `routes/web.php`, nav in `app/Http/Livewire/StudioNavigation.php`.
- LatchOps: `electron/services/latchdeck.ts`, IPC in `electron/main.ts`/`preload.ts`,
  UI in `src/pages/LatchId.tsx` + `src/pages/Settings.tsx`.
- API contract: [latchdeck-api.md](latchdeck-api.md).
