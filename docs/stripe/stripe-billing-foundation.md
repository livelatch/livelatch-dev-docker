# Stripe Billing Foundation

The Stripe phase is one of the most important milestones in the fork so far. The core commit sequence is `5845b64`, `e5fcb92`, `9cd1587`, `ee8b280`, `845ea1d`, `8be402e`, and `da2fde9`.

## What the billing work established

- local billing persistence through `user_billing`
- a `UserBilling` model tied to the user record
- centralized billing config in `config/billing.php`
- Stripe customer creation for newly created LatchID users
- free-tier subscription creation at signup
- a backfill command for existing users
- a billing controller and dashboard subscription view
- subscription metadata linking Stripe records back to Livelatch and Supabase IDs

## Why this is significant

This turns Livelatch from a user-account project into a SaaS foundation:

- every user can exist in Stripe from the start
- plan-aware features become possible
- invoicing and portal access are no longer hypothetical
- future paid onboarding can evolve from the free-tier flow already in place

## Current source of truth split

- Supabase: identity
- Stripe: billing
- Laravel: orchestration, local state, entitlements, and product access decisions

That split shows up clearly in the signup path and in `BillingController`.

## Current billing surface

- `GET /studio/subscription` shows current plan details and invoices
- `GET /billing/checkout/pro` starts the Pro checkout flow
- `GET /billing/portal` redirects to the Stripe billing portal

## Future implications

This foundation is the prerequisite for:

- paid signup flows
- LatchDeck entitlements
- affiliate attribution and plan-specific rules
- creator subscriptions and broader marketplace economics
