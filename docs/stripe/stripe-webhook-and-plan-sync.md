# Stripe Webhook & Plan Sync (free/pro propagation)

This closes a gap in the [billing foundation](stripe-billing-foundation.md): Stripe
was configured to send webhook events to `https://dev.livelatch.com/stripe/webhook`,
but **no handler existed**, so the endpoint returned HTTP 404. As a result a Pro
checkout never propagated — `user_billing.plan_key` stayed `free` for every user,
and setting a customer to Pro in Stripe did not sync into Livelatch. (Stripe sent
a "webhook endpoint failing / about to be disabled" warning email, which is what
surfaced the bug.)

## What was built

A real webhook handler that keeps **two stores** in sync on every subscription
change:

1. **`user_billing.plan_key`** in Railway MySQL — the operative value Laravel
   reads (`User::isPro()` / `planKey()`).
2. **`public.profiles.plan_key`** in Supabase — a **read-only mirror** so
   non-Laravel consumers (Encore, the viewer portal, edge functions, the planned
   Latch On free/Pro gate) can read plan status natively, with RLS, without
   calling back into Laravel or querying MySQL directly.

```text
Stripe ──POST /stripe/webhook──▶ StripeWebhookController
   (verify signature)
        ├─ resolve UserBilling by stripe_customer_id
        ├─ derive plan: Pro price + live status → 'pro', else 'free'
        ├─ update user_billing  (plan_key, status, price, period_end, cancel flag)  [MySQL]
        └─ BillingProfileService::setPlan(supabase_user_id, plan)                    [Supabase profiles]
```

### Why mirror instead of cross-DB query or polling

- **Not a Supabase→MySQL live query:** that would give every Supabase consumer a
  hard runtime dependency on Railway MySQL being up — the total-failure coupling
  the decoupled stack exists to avoid. Hosted Supabase also has no native MySQL
  FDW.
- **Not a polling edge function:** polling creates a second independent Stripe
  consumer (two sources of truth that can diverge), is laggy, and hits the Stripe
  API on a timer for users whose plan never changed.
- **The mirror keeps one writer** (Laravel, on the webhook it already receives
  from Stripe) and pushes the value to Supabase within seconds of the event. If
  Railway is down, the last-known plan is still readable in Supabase — partial
  failure, not total.

## Plan derivation

`customer.subscription.deleted` → `free`. Otherwise the subscription must be in a
live status (`active`, `trialing`, `past_due`) **and** carry the configured Pro
price (`config('billing.pro_price_id')`) to be `pro`; anything else fails safe to
`free`.

## Security

The route is unauthenticated and CSRF-exempt (Stripe is not a logged-in user).
Authenticity is established by verifying the `Stripe-Signature` header against the
endpoint signing secret with `Stripe\Webhook::constructEvent()`. If
`STRIPE_WEBHOOK_SECRET` is unset the handler rejects events rather than trusting
unverified payloads. Non-subscription events are still ACKed with 200 so Stripe
does not disable the endpoint.

## Reconcile backstop

`php artisan billing:sync-supabase [--dry-run]` mirrors every
`user_billing.plan_key` into `profiles.plan_key`. Use it to (a) fix existing rows
after this change and (b) run daily as a self-heal for any missed webhook.

## Code map

| Concern | File |
| --- | --- |
| Webhook handler | `app/Http/Controllers/StripeWebhookController.php` |
| Route (`POST /stripe/webhook`) | `routes/web.php` |
| CSRF exemption | `app/Http/Middleware/VerifyCsrfToken.php` |
| Supabase mirror service | `app/Services/BillingProfileService.php` |
| Reconcile command | `app/Console/Commands/SyncBillingToSupabase.php` |
| Signup seed (`free`) | `app/Http/Controllers/Auth/LatchIdSessionController.php` (`provisionBilling`) |
| Supabase column | `supabase/migrations/20260622090000_add_plan_key_to_profiles.sql` |

## Required environment variables

| Variable | Purpose |
| --- | --- |
| `STRIPE_WEBHOOK_SECRET` | Stripe endpoint signing secret (`whsec_…`) — verifies webhook authenticity |
| `STRIPE_PRO_PRICE_ID` | Price that means "Pro" when deriving the plan |

## Operational follow-up (runtime, not code)

1. In the Stripe Dashboard, confirm the `…/stripe/webhook` endpoint subscribes to
   `customer.subscription.created/updated/deleted` (and re-enable it if Stripe
   already disabled it), then copy its signing secret.
2. Set `STRIPE_WEBHOOK_SECRET` on Railway.
3. Run `php artisan billing:sync-supabase` once to backfill `profiles.plan_key`
   for the existing rows.
4. Test: set a sandbox customer to Pro → watch `user_billing` and
   `profiles.plan_key` both flip to `pro`.

## References

- Stripe webhooks: <https://stripe.com/docs/webhooks>
- Stripe signature verification: <https://stripe.com/docs/webhooks#verify-events>
