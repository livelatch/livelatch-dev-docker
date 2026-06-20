# Card Identity & Provenance (design)

This is a **design document** for how individual cards are uniquely identified and
how ownership is tracked over time — "minted, but no web3". It underpins trading,
serialized rewards, and the future game-developer SDK.

See also [campaigns-and-mechanics.md](campaigns-and-mechanics.md) (how cards are
claimed) and [rarity-model.md](rarity-model.md) (how value is scored).

> Status: planned. Today the live table is `latchdeck_cards_mvp` (one row per card
> design). The instance + ledger model below is the next layer.

## "Minted, no web3"

Every property people reach for a blockchain to get — a unique permanent ID,
provable ownership, full transfer history, guaranteed scarcity — is achievable in
plain Postgres **because Encore is already the single trusted writer**. You do not
need consensus when you have a central authority that everyone already trusts.

So "minted" here means a **centralized, append-only provenance ledger**. The only
thing given up versus a real blockchain is *trustlessness* (users trust Livelatch,
not math), which a creator platform does not need.

## Card (type) vs. instance (copy)

This is the type/token distinction — the same split NFTs make (contract = card,
tokenId = copy), done in a database:

- **Card** — the design/edition a creator made (today's `latchdeck_cards_mvp`).
- **Card instance** — a *specific issued copy* someone owns, with its own
  permanent unique ID and a serial (#0007 of 10).

**Redeeming mints an instance.** The grant action from the redemption engine
produces a `card_instance`; that is the concrete object that gets an ID.

## ID vs. serial — keep them separate

Two distinct numbers; conflating them causes pain later:

- **Instance ID** — globally unique, opaque, permanent, never reused. Use a
  **UUIDv7** (random-unique *and* time-sortable by mint order). This is the key the
  SDK and trading reference.
- **Serial** — the human-facing flex: **#0007 of 10**, an ordinal *within* an
  edition (1…N). This is what makes #0001 special.
- Optional: a short **cert code** (checksummed, like a
  [PSA](https://www.psacard.com/) grading number) for sharing or verifying a card
  in a URL or out loud.

A copy reads as "Instance `0191f…` · #0001/10" — UUID for machines, serial for
humans.

## Data model

```text
latchdeck_card_instances
  id            uuid (v7)    -- permanent unique ID
  card_id       -> latchdeck card
  serial_no     int          -- #0007, atomically assigned at mint
  minted_at     timestamptz
  origin        text         -- campaign | grant | pack
  reserved      bool         -- held back for creator distribution
  current_owner text         -- recipient identity (set once bound to a LatchID)

latchdeck_ownership_events   -- APPEND-ONLY. never UPDATE or DELETE.
  instance_id   -> latchdeck_card_instances
  from_identity text
  to_identity   text
  reason        text         -- mint | trade | grant
  at            timestamptz
```

The append-only `latchdeck_ownership_events` table **is the "chain"**: it gives
provenance ("who held #0001 before me") and, because Encore never mutates it, it
is tamper-evident by convention. (If cryptographic integrity is ever wanted
without a chain, periodically publish a signed hash / Merkle root of the ledger —
a someday, not a now.)

### Integrity rules (enforced by Encore, at mint time)

- **Serial assignment is transactional** — no two #0007s; a cap of 10 is never
  exceeded. This is the same atomic point as supply decrement in
  [campaigns-and-mechanics.md](campaigns-and-mechanics.md).
- **Creators can reserve serials** — hold back #0001 (and maybe #0002–#0005) as
  `reserved` to hand out their own way, while the rest auto-assign on redemption.
  This is the serialized-reward mechanic, native to the model.

## What this enables

### Game-developer SDK economies

A game needs to (a) read a card and (b) verify ownership. Design for both now:

- An **instance metadata schema** — `{ attributes, rarity_score, creator,
  edition, serial }` (think
  [ERC-721 metadata](https://eips.ethereum.org/EIPS/eip-721), centralized). A
  third-party TCG maps those traits to in-game stats, so a streamer's Legendary
  becomes a powerful unit elsewhere.
- `GET /instances/:id` (read) and an ownership-verification endpoint
  ("does user U own instance X?" / "what does U own?"), gated by the SDK key plus
  the user's LatchID auth — the reserved `auth.ts` SDK-key seam.

### Trading (Steam-style)

- **Doubles are native** — ownership is per-instance, so holding three of the same
  card is just three instance rows. Trading duplicates works out of the box and is
  the natural sink for pack-completion dupes.
- A **trade** is an atomic two-sided ownership transfer recorded as
  `ownership_events`. Both parties confirm; Encore swaps in one transaction — no
  escrow contract needed because it is centralized. Trade-holds / cooldowns can be
  added later for anti-scam.

### Serialized #0001 rewards

Covered by `reserved` serials plus creator-initiated grants (gateway 5). Low
serials are inherently prestigious and feed the copy-level edition bonus in
[rarity-model.md](rarity-model.md).

## Legal watch-items (not blockers)

Keeping cards **non-cashable** is what keeps LatchDeck clear of gambling/securities
issues. Two futures put pressure on the "no monetary value" line:

- **Paid randomized packs** are textbook loot-box mechanics (regulated/banned in
  some regions). Keep packs **earned, not bought** (see
  [campaigns-and-mechanics.md](campaigns-and-mechanics.md)).
- **Real utility in a game or an off-platform secondary market** can make "no
  monetary value" fuzzy — see the
  [CS:GO skins](https://en.wikipedia.org/wiki/Counter-Strike:_Global_Offensive#Skin_gambling)
  precedent where tradeable items were treated as de facto value. Revisit with
  counsel before any official trading-for-value.

## References

- LatchDeck architecture: [latchdeck-architecture.md](latchdeck-architecture.md)
- ERC-721 metadata standard: <https://eips.ethereum.org/EIPS/eip-721>
- Steam trading cards: <https://steamcommunity.com/tradingcards/>
