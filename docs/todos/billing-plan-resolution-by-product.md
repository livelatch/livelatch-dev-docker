# Switch plan resolution from price ID to product ID

<!-- todo-check
created_at: 2026-06-22T12:00:00+08:00
ask_after: 2026-07-03T22:29:45+08:00
status: open
-->

Today `StripePlanResolver::planForSubscription()` decides "is this Pro?" by
matching a single price ID (`config('billing.pro_price_id')` â† env
`STRIPE_PRO_PRICE_ID`). That works for the alpha's one Pro price, but **does not
scale to multiple prices that all mean Pro** (monthly + annual, promo/launch
prices, currency-specific prices, grandfathered legacy prices). Adding a new
price would otherwise mean adding/maintaining another env var per price â€” the
wrong pattern.

## Target

Derive the plan from a signal that travels with the price, so a new price needs
**zero code/env changes**:

- **Preferred â€” product-based:** map one Stripe **Product** ("Livelatch Pro") â†’
  `pro`, and resolve from `subscription.items[].price.product`. A single
  `STRIPE_PRO_PRODUCT_ID` env var (replacing `STRIPE_PRO_PRICE_ID`). Any number of
  prices under that product resolve to `pro` automatically.
- **Alternative â€” metadata convention:** set `metadata.plan_key = "pro"` on the
  Pro product/prices in Stripe and read that in the resolver. No env var per
  price.

## Checklist

- [ ] Confirm Stripe has a single "Pro" product with its price(s) underneath
      (tidy the Stripe side if current prices are standalone).
- [ ] Add `STRIPE_PRO_PRODUCT_ID` to `config/billing.php` + Railway variables.
- [ ] Update `app/Services/StripePlanResolver.php` to match on
      `price->product` instead of `price->id`. (Single change point â€” the webhook
      handler and `billing:reconcile-stripe` both use this resolver.)
- [ ] May need `'expand' => ['data.items.data.price.product']` (or compare the
      price's `product` id, which is already present without expansion).
- [ ] Re-run `php artisan billing:reconcile-stripe --dry-run` to confirm no
      regressions, then apply.
- [ ] Retire `STRIPE_PRO_PRICE_ID` once nothing references it.

## Owner Follow-Up

- Decide product-based vs metadata-based (product-based recommended).
- Do this **before** creating real/multiple Pro prices (annual, promos), so the
  first non-trivial pricing setup is already on the scalable model.

See `docs/stripe/stripe-webhook-and-plan-sync.md`.
