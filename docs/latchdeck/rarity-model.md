# Rarity & Value Model (design)

This is a **design document** for how LatchDeck scores the perceived value of a
card. The goal is real *perceived value* — so a viewer feels genuinely special
holding a 1/10 card from a big creator — without the cards carrying monetary
value.

See also [card-identity-and-provenance.md](card-identity-and-provenance.md) (what
gets scored) and [campaigns-and-mechanics.md](campaigns-and-mechanics.md) (how
cards are claimed).

> Status: planned and intentionally tunable. Every constant below is a starting
> point to refine against real data.

## The model is the anti-abuse layer

LatchDeck does **not** police redemptions. A creator can mint 100 "ultra rare"
cards and redeem them with 1,000 fake accounts — and it must not matter. The
defense is not a gate; it is a value function that **only real signals can move.**
If faking scarcity produces a *low* score, abuse is pointless. Everything below is
designed so that genuine creator notoriety and genuine demand are what create
value.

## Core reframe: rarity = scarcity × desirability

"Rarity" conflates two different things, and you need both or the number lies:

- **Scarcity** (supply) — how few exist. A 1/10.
- **Desirability** (demand) — how much people want it: creator fame, how hot the
  drop was.

A 1/10 from an unknown that three people grabbed over a year is *scarce but
worthless*. A 1/10,000 from a massive creator that filled in 60 seconds is *common
but prestigious*. **Perceived value ≈ scarcity × desirability** — a card needs
both, which is exactly the "1/10 of a big creator" feeling.

## Dilution: why flooding fails automatically

The single most important mechanic. A creator can confer prestige only **up to
their real audience**, and minting more cards **splits that prestige across all of
them**:

- Big creator, 3 cards → each is loaded.
- Same creator floods 100 cards → each is worth a fraction, *automatically*,
  whatever label is attached.

An artist who prints infinite copies devalues every copy. The cap on value is the
creator's real notoriety, which cannot be faked — so flooding is self-defeating and
no minting gate is required.

## The components

### Scarcity (S)

Final/known supply, log-scaled so rarity rises sharply as supply shrinks, plus a
**sell-out bonus** when a capped drop actually fills. A card capped at 10 that went
10/10 is scarcer-in-spirit than one capped at 1,000 sitting at 10.

### Prestige (P)

`notoriety ÷ dilution`:

- **Notoriety** = log of aggregated real follower counts across connected
  platforms (TikTok/YouTube/etc.) — followers are power-law, so log them.
- **Dilution** grows with the total cards the creator has ever minted.

This is fame-per-card and the heart of the score.

### Heat (H)

Two real, hard-to-fake demand signals, decayed by recency so "currently hot" ≠
"was hot last year":

- **Redemption velocity** — how fast it filled after publish / campaign start.
- **Oversubscription** — *the secret weapon.* If 5,000 people tried to `/redeem` a
  100-supply card, that 50× demand-beyond-supply is the truest "everyone wanted
  this, almost nobody got it" signal, and far more meaningful than redemptions
  alone. Capture failed/over-cap attempts; do not throw them away.

### Context (C)

Campaign drops (intentional, time-boxed events) get a modifier; **longevity**
matters too (a card still wanted years later is evergreen/legendary).

The creator's "ultra rare" **label is not a free multiplier** — let it act only by
*setting the supply cap*, so declared rarity must be backed by real scarcity to
count. A `promotional` Visibility Card (abundant by design) must score as
intentionally common, not as failed scarcity — see
[campaigns-and-mechanics.md](campaigns-and-mechanics.md).

## Two rarities: card vs. copy

- **Card-level value** — how special the card is (everything above).
- **Copy-level / edition ordinal** — your specific **#7 of 10**. Low edition
  numbers carry cachet (sneaker drops, low-numbered prints). A holder's bragging
  score = card value × an edition bonus for low ordinals.

## Combining: weighted geometric mean

Multiply in the log domain — stable, and the power-law inputs behave:

```text
CardValue   = S^wS · P^wP · H^wH · C^wC
HolderValue = CardValue · EditionBonus(ordinal, supply)

log CardValue = wS·logS + wP·logP + wH·logH + wC·logC
```

Multiplicative means **any factor near zero tanks the score** (no fame → not
prestigious; no demand → not hot), which is the desired behaviour. The weights are
the tuning knobs; initial instinct: **prestige highest** (notoriety is hardest to
fake and what makes a card matter), scarcity and heat close behind, context light.

## v0 equation (to tune)

Every constant is a knob.

```text
S = 1 / log2(supply + 2)                    × (1.5 if sold_out else 1)
P = log10(followers + 10) / log2(cards_minted_by_creator + 2)
H = (oversubscription_ratio·0.7 + fill_velocity·0.3) · recency_decay
C = campaign_bonus · longevity_bonus

CardValue = S^1.0 · P^1.2 · H^0.8 · C^0.5
```

## Presentation: percentiles, not thresholds

Raw `CardValue` is meaningless to a viewer. Map it to a **percentile rank against
the population**, then to tiers (Common → Uncommon → Rare → Epic → Legendary →
Mythic). Percentiles auto-adjust as the platform grows — no fixed thresholds
drifting out of date.

This yields the **per-creator AND all-platform** views as two lenses on the same
card:

- "**Top 1%** of @creator's cards"
- "**Top 0.1%** platform-wide"

Same card, two badges, both compelling.

## Live vs. frozen — and why live is the emotional hook

Make **scarcity essentially frozen** (an edition size is what it is) but let
**prestige and heat be living**. If you owned a 1/10 of someone *before they blew
up*, the card's value should **climb as they grow** — that "I had it early"
appreciation is the exact feeling the model is chasing, and it turns *holding*
cards into a game, not just collecting them. Show a permanent "Edition 7/10"
alongside a dynamic "Value: 94 / Legendary" that moves over time.

## Open tuning questions

- Exact weights `wS, wP, wH, wC` and the recency-decay half-life.
- The `EditionBonus` curve for low serials.
- How aggressively `promotional` cards are excluded from rarity tiers.
- Whether notoriety blends multiple platforms or takes the max.

## References

- LatchDeck architecture: [latchdeck-architecture.md](latchdeck-architecture.md)
- Card identity: [card-identity-and-provenance.md](card-identity-and-provenance.md)
- Campaigns & mechanics: [campaigns-and-mechanics.md](campaigns-and-mechanics.md)
