# LatchDeck API contract

The LatchDeck API is served by Encore (`ld/latchdeck`). This is the canonical
contract every client (Livelatch today, SDK apps later) speaks. Base URL for
staging: `https://staging-latchdeck-7iu2.encr.app`.

## Authentication

Every endpoint requires a Bearer API key:

```
Authorization: Bearer <LATCHDECK_SERVICE_API_KEY | LATCHDECK_ADMIN_API_KEY>
```

- Service key: all non-admin endpoints.
- Admin key: everything, including `/admin/*`.
- Missing/invalid key → `401`. Calling `/admin/*` without the admin key → `403`.

Errors use Encore's envelope: `{ "code": "...", "message": "...", "details": ... }`.
Clients should surface `message` (e.g. tier-limit and approval errors).

## Endpoints

### `GET /access-status/:latchid_user_id`
Access + entitlement state.
```json
{
  "status": "not_applied | pending_review | active | denied_waitlist | restricted | revoked",
  "tutorial_seen": false,
  "first_card_created": false,
  "tier": "free | pro | sdk",
  "capabilities": { "maxPublishedCards": 3, "premiumRarities": false, "canPublish": true },
  "restriction": null
}
```
`not_applied` is returned when no creator row exists.

### `POST /applications`
Request access (idempotent on `latchid_user_id`). Creates/refreshes the creator
as `pending_review`.
```json
// request
{ "latchid_user_id": "uuid", "email": "...", "display_name": "...",
  "platform": "Twitch", "community_context": "optional", "reason": "optional",
  "tier": "free|pro|sdk (optional, defaults free)" }
// response
{ "application_id": "uuid", "creator_id": "uuid", "status": "pending_review" }
```

### `POST /creators/:latchid_user_id/tier`
Sync entitlement tier from the calling platform. No-op if the creator hasn't
applied.
```json
// request
{ "tier": "free | pro | sdk" }
// response
{ "ok": true, "latchid_user_id": "uuid", "tier": "pro" }
```

### `POST /mvp/cards`
Create a **draft** card. Allowed for `pending_review` and `active` creators.
```json
// request
{ "latchid_user_id": "uuid", "name_mvp": "...", "short_description_mvp": "...",
  "long_description_mvp": "optional", "rarity_mvp": "common",
  "creator_name_mvp": "...", "image_url_mvp": "optional", "background_color_mvp": "#1b1b29" }
// response: the full card (see card shape below), status_mvp = "draft"
```

### `GET /mvp/cards/:latchid_user_id`
List the creator's cards (drafts + published), newest first.
```json
{ "cards_mvp": [ /* card */ ] }
```

### `PATCH /mvp/cards/:id`
Edit an owned card (draft editor). Body: `{ latchid_user_id, ...any updatable
fields }`. Returns the updated card.

### `POST /mvp/cards/:id/publish`
Publish a draft. Requires creator `active` (`403` otherwise) and within the
tier's `maxPublishedCards` (`429`/resource-exhausted otherwise). Body:
`{ latchid_user_id }`. Returns the card with `status_mvp = "published"`.

### `POST /mvp/cards/:id/unpublish`
Retract to draft. Body: `{ latchid_user_id }`.

### `GET /admin/applications` *(admin key)*
Pending applications (joined with creator) for review.

### `POST /admin/applications/:id/approve` *(admin key)*
Approve: application → `approved`, creator → `active`, writes status history, and
inserts a `livelatch_notifications` row for the user. Body:
`{ admin_latchid_user_id (may be empty), decision_reason? }`.

### `POST /admin/applications/:id/deny` *(admin key)*
Deny: application → `denied`, creator → `denied_waitlist`.

## Card shape (`latchdeck_cards_mvp`)
```json
{
  "id": "uuid", "creator_id": "uuid", "latchid_user_id": "uuid",
  "name_mvp": "...", "short_description_mvp": "...", "long_description_mvp": null,
  "rarity_mvp": "common", "creator_name_mvp": "...",
  "image_url_mvp": null, "background_color_mvp": "#1b1b29",
  "status_mvp": "draft | published", "published_at_mvp": null,
  "is_active_mvp": true, "created_at_mvp": "timestamp"
}
```

## Notes for future SDK clients
- The auth handler (`latchdeck/auth.ts`) is where per-developer keys will map to a
  `sdk` creator/principal.
- Redemption/campaign endpoints will be added here; they only need card IDs
  (content already lives in Supabase).
