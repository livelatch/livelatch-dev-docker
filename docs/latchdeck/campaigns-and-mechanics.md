# Campaigns, Redemption & Mechanics (design)

This is a **design document**, not a description of shipped code. It captures the
agreed shape of LatchDeck's claim/redemption system and the mechanics built on
top of it, so implementation works from a written spec.

For the overall system see
[latchdeck-architecture.md](latchdeck-architecture.md); for card identity see
[card-identity-and-provenance.md](card-identity-and-provenance.md); for the value
score see [rarity-model.md](rarity-model.md).

> Status: planned. The architecture doc lists "Redemptions, claim campaigns,
> real-time" as the deferred reason Encore exists. This doc specs that work.

## Core principle: many gateways, one engine

Cards can be claimed many different ways, but **every gateway sends the same
request to Encore, which runs one internal grant function.** This keeps the rules
in a single place (Encore is the only writer) and means each new redemption
method is configuration, not a new code path.

The shared primitive is **not** "redeem a code". It is:

> **grant `entitlement` to `recipient_identity`, authorized by `actor`, from `source`**

A code is only *one way to resolve which entitlement*. Some gateways (creator
grants) carry no code at all, which is why the engine is built around granting an
entitlement, not around processing a code.

### The gateways

| # | Gateway | How the claim arrives | Actor (who vouches) |
| - | ------- | --------------------- | ------------------- |
| 1 | **Live chat** | An ingest worker reads `/redeem [code]` in the stream chat | `ingest` |
| 2 | **Livelatch block** | A block on the creator's Livelatch page with a redeem field | `user` (logged in) or anonymous + code |
| 3 | **Viewer portal** | "Redeem a code" entry in the LatchDeck viewer portal | `user` (authenticated) |
| 4 | **QR code** | A QR drop (desktop streams on Twitch/YouTube, or printed in a physical space) | `user` after landing |
| 5 | **Creator grant** | Creator enters a viewer's social ID to award a card directly | `creator` |
| 6 | **SDK** | An authorized third-party app redeems via its own API key | `sdk` |

All six call the same endpoint. They differ only on two axes — **how the
recipient is identified** and **who is authorized to act** — which is exactly what
the canonical request carries.

## The canonical request

```jsonc
POST /redeem
{
  "source": "chat|livelatch_block|viewer_portal|qr|creator_grant|sdk",
  "code": "FROG42",            // optional — absent for a direct creator grant
  "campaign_id": "...",        // alternative to code
  "recipient": {
    "type": "latchid_user_id|tiktok_open_id|tiktok_username|youtube_channel_id",
    "value": "..."
  },
  "actor":   { "type": "user|creator|ingest|sdk", "id": "..." },
  "idempotency_key": "...",
  "conditions_met": false       // see "Conditional redemption" below
}
```

Response is one of:

- `granted` — an entitlement (a card instance) was issued.
- `pending` — the recipient identity is not yet linked to a LatchID account; a
  pending claim was parked and will resolve on authentication (see below).
- `rejected` — with a `reason` (`expired`, `exhausted`, `duplicate`,
  `unauthorized`, `ineligible`).

## Resolved vs. deferred identity (two-phase)

Gateways split into two camps and the engine must support both:

- **Resolved** — the recipient is already a logged-in LatchID account (gateway 2
  logged-in, gateway 3). Grant the card now.
- **Deferred** — only a social identity is known (gateway 1 chat username,
  gateway 5 creator-entered social ID, sometimes 4/6). The account has not
  authenticated, so the card cannot be granted yet.

So the engine is two functions:

1. **`redeem()`** — grants immediately if the identity is resolved; otherwise
   **parks a pending claim** keyed to the social identifier.
2. **`reconcile(identity)`** — runs when that identity later authenticates (e.g.
   [TikTok Login Kit](https://developers.tiktok.com/products/login-kit/) OAuth, or
   a LatchID login) and binds pending claims to the now-known account.

### The identity-matching wrinkle

Live chat gives a TikTok `uniqueId` (username) plus an internal webcast `userId`.
TikTok Login Kit (the official OAuth used at redemption) returns an
`open_id`/`union_id` — a **different identifier namespace** that does not equal
the webcast `userId`. Reconciliation therefore matches primarily on **username**,
which can change between the stream and redemption. Capture username, nickname and
numeric id at claim time and accept a small edge-case loss for renamed users. This
is the riskiest part of the chat gateway and should be prototyped first.

## The universal checks the one engine owns

Every gateway funnels through the same validation. This is the entire reason to
centralize:

1. **Entitlement resolves** — a valid code/campaign, or a card the actor is
   allowed to grant.
2. **Campaign is active and in-window** — drops are time-boxed.
3. **Supply remaining — atomic decrement.** Chat and QR drops arrive in bursts; a
   "first 100" drop with thousands of simultaneous `/redeem` attempts must
   decrement supply transactionally or it will over-issue.
4. **Per-identity dedupe** — one claim per identity per campaign.
5. **Idempotency** — chat connectors double-fire events; the same request must not
   double-grant. Dedupe on `idempotency_key` (e.g. `campaign + userId`).
6. **Actor authorization** — where the gateways genuinely diverge:
   - `creator` (gateway 5) may only grant **its own** cards, within tier limits,
     drawing from the campaign's finite supply.
   - `sdk` (gateway 6) is scoped to **its own app's** campaigns by API key (the
     `auth.ts` SDK-key seam reserved in the architecture).
   - `ingest` (gateway 1) may only claim for the campaign tied to the live session
     it is listening to.

`source` and `actor` are stored on every claim so abuse controls and audit can be
applied per gateway even though processing is shared.

## The live-chat ingest worker

A dedicated **Node ingest service** (not LatchOps — that is operator tooling)
connects to a creator's live room and posts claims to Encore with a service key.

- Library: an unofficial connector such as
  [TikTok-Live-Connector](https://github.com/zerodytrash/TikTok-Live-Connector)
  (Node) or [TikTokLive](https://github.com/isaackogan/TikTokLive) (Python),
  usually requiring a signing service for the webcast connection.
- **Risk:** these are unofficial, ToS-gray, brittle (break when TikTok changes
  signing), and may risk the creator's account. The worker is intentionally the
  **disposable front-end** to the engine — if it breaks, the official
  code/QR/Login-Kit gateways still work. Keep the fragility isolated here.
- The creator's connected `@handle` (already captured via the LatchID TikTok flow)
  tells the worker which room to join. A creator starts a "drop" in LatchDeck
  Studio, which activates the listener for the keyword/code.

## Mechanics built on the engine

These are **attributes on the campaign**, not separate systems.

### Packs (set collection)

- A **pack/set** groups several card definitions (e.g. 5 cards redeemable across 5
  separate streams). Completing the set requires owning all members.
- **Packs are never purchased.** They are earned by attendance/participation,
  which keeps them clear of loot-box regulation and turns collection into
  **appointment viewing** (come back or you can't complete the set).
- **Completion should grant a chase reward** (a master card instance, prestige
  boost, badge, or unlock) or the pull to finish the set is weak.
- **Duplicates are native.** A second redeem mints another instance (see
  [card-identity-and-provenance.md](card-identity-and-provenance.md)). Dupes are
  the fuel for trading — trade spares to complete each other's sets, the
  [Steam trading card](https://steamcommunity.com/tradingcards/) loop.

### Visibility Cards (discoverability — a Pro feature)

LatchDeck doubles as a creator-discovery flywheel.

- Viewers in the portal get a **"redeem a random card"** action, surfacing a card
  from a creator they have never heard of, with that creator's **stream schedule**
  and an option to follow / auto-add the campaign to their portal.
- Opt-in for creators and a **Pro** entitlement — effectively native, collectible
  promoted discovery.
- **Card intent must be first-class.** A Visibility Card is *abundant by design*
  (wide reach is the goal), so it is tagged `promotional` vs `collectible` and the
  rarity model must **not** score it as failed scarcity. See
  [rarity-model.md](rarity-model.md).
- **Weight the "random" pool** by the viewer's collected genres, locale and stream
  timing so the surfaced creator is actually reachable; keep some randomness for
  serendipity.
- **Build discover → attend attribution.** Tracking random-redeem → real stream
  attendance is both the Pro sales pitch ("X new viewers found you") and a strong
  real-demand signal for the rarity model.
- Quality guard: rate-limit how often a creator appears and skip creators the
  viewer already follows, so the button stays worth pressing.

### Conditional redemption (engagement requirements)

Some cards require an action — send X gifts, watch X minutes — before they can be
claimed, via bot integrations such as
[Mix It Up](https://mixitupapp.com/), Streamlabs or StreamElements.

- **Encore defines the requirement** (campaign config: "requires 5 gifts" /
  "30 minutes watched") and **authorizes who may attest** to it.
- **The integration asserts eligibility.** Encore does not hold raw
  watch-time/gift data; the bot does. The bot calls `/redeem` with
  `conditions_met: true`, vouched by its authorized actor key, and Encore checks
  the attestation comes from an actor allowed to vouch for that campaign.
- This drops into the gateway/actor model — the bot is just another trusted actor.
  Gift-gated cards tie redemption to spending (creator revenue); watch-gated cards
  tie it to retention.

## New data (Encore-owned, Supabase)

Sketch only — finalize during implementation. Encore remains the sole writer.

- `latchdeck_campaigns` — a drop: card(s), code/keyword, supply cap, window,
  `intent` (`collectible`/`promotional`), eligibility conditions, attesting actor.
- `latchdeck_claims` — a claim: campaign, recipient identity (resolved or
  deferred), `status` (`pending`/`granted`/`rejected`), `source`, `actor`,
  `idempotency_key`.
- `latchdeck_packs` — set grouping + completion reward.
- Card instances and the ownership ledger live in
  [card-identity-and-provenance.md](card-identity-and-provenance.md).

## References

- LatchDeck architecture: [latchdeck-architecture.md](latchdeck-architecture.md)
- TikTok Login Kit: <https://developers.tiktok.com/products/login-kit/>
- Mix It Up bot: <https://mixitupapp.com/>
