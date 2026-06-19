# Encore

Encore is now a **live part of the platform**: it serves the LatchDeck API.

As of 2026-06-19 the Encore service (`ld/latchdeck`, deployed on Encore Cloud at
`https://staging-latchdeck-7iu2.encr.app`) is the **authority for LatchDeck** —
access/applications, creator tiers, and MVP card create/draft/publish — backed by
a direct Postgres connection to Supabase. It is the platform-agnostic API that
Livelatch (and future SDK clients) call.

See [latchdeck-architecture.md](../latchdeck/latchdeck-architecture.md) and
[latchdeck-api.md](../latchdeck/latchdeck-api.md) for the full design and contract.

## Why Encore (not just Supabase)

Encore owns the logic that benefits from a real backend — entitlement gating,
publishing rules, and (planned) real-time card/redemption campaigns — which the
product research found Encore handles better than Supabase. Supabase remains the
data store; Encore is the brain and the public API surface.

## Current status

- Auth: every endpoint requires a Bearer API key (`auth.ts`); service key
  (Livelatch) and admin key (LatchOps). Per-developer SDK keys are the next step.
- Implemented: access status, applications, approve/deny, tier sync, MVP card
  create/list/update/publish/unpublish.
- Data: schema changes captured in `ld/latchdeck/migrations/`.

## Next

- Per-developer (`sdk` tier) API keys and developer onboarding.
- Redemption / claim / real-time campaign endpoints (reference cards by ID).
- Tune tier capability limits in `latchdeck/tiers.ts` (currently placeholders).
