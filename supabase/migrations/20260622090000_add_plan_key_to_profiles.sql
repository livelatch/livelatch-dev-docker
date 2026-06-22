-- Add a billing plan mirror to public.profiles so non-Laravel consumers
-- (Encore, the viewer portal, edge functions, the Latch On free/Pro gate) can
-- read a creator's plan without calling back into Laravel or querying the
-- Railway MySQL database directly.
--
-- This column is a READ-ONLY MIRROR. Laravel + Stripe remain the source of
-- truth: the Stripe webhook handler (App\Http\Controllers\StripeWebhookController)
-- writes user_billing.plan_key in MySQL and upserts this column in the same
-- request via App\Services\BillingProfileService.

alter table public.profiles
  add column if not exists plan_key text not null default 'free'
  check (plan_key in ('free', 'pro'));

comment on column public.profiles.plan_key is
  'Billing plan mirrored from Laravel user_billing.plan_key via the Stripe webhook handler. Read-only mirror for non-Laravel consumers; Laravel/Stripe remain the source of truth.';
