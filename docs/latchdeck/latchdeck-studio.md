# LatchDeck Studio

LatchDeck is present in the fork today mostly as a studio surface and navigational product area rather than a fully implemented feature set. The key commit sequence is `f756ab2`, `c9ac44b`, `390d5d7`, and `3646dd7`.

## What exists now

- dedicated studio routes for `latchdeck`, `cards`, `redemptions`, and `settings`
- placeholder Blade views for those areas
- navigation grouped under a distinct LatchDeck section in the studio sidebar
- HTMX-aware page loading inside the updated sidebar shell

## Why this is significant

This work signals a product shift:

- Livelatch is no longer just a single settings page
- the dashboard is becoming a hub for multiple creator products
- LatchDeck is treated as a first-class product area even before the full business logic exists

## Current interpretation

Right now, LatchDeck should be read as scaffolding with direction:

- routes exist
- layout patterns exist
- user expectations are being set
- deeper product logic is still pending

## Design implication

Because the LatchDeck area already exists in navigation, future implementation should preserve continuity:

- keep URLs stable where possible
- upgrade placeholder views into real feature modules
- let HTMX or similar partial loading continue to reduce full-shell refreshes

## Related future dependencies

LatchDeck will likely intersect with:

- billing entitlements from Stripe
- identity from LatchID
- creator growth tooling
- analytics or redemption workflows across the broader platform
