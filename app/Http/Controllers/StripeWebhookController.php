<?php

namespace App\Http\Controllers;

use App\Models\UserBilling;
use App\Services\BillingProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Receives Stripe webhook events and keeps a user's plan in sync.
 *
 * This is the missing link that makes the whole billing pipeline work: Stripe
 * sends subscription lifecycle events here, and this handler flips
 * user_billing.plan_key in MySQL (the operative source of truth Laravel reads)
 * and mirrors it to Supabase public.profiles.plan_key (the read replica
 * non-Laravel consumers use). Without it, a Pro checkout never propagates and
 * every row stays 'free'.
 *
 * The endpoint is unauthenticated and CSRF-exempt (Stripe is not a logged-in
 * user); authenticity is established by verifying the Stripe signature against
 * the endpoint signing secret.
 */
class StripeWebhookController extends Controller
{
    /**
     * Subscription events we act on. created/updated cover upgrades,
     * downgrades, renewals and cancellations-at-period-end; deleted covers a
     * subscription that has fully ended.
     */
    private const HANDLED_EVENTS = [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
    ];

    public function handle(Request $request)
    {
        $secret = (string) config('billing.webhook_secret');
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // Verify authenticity. If no secret is configured we cannot trust the
        // payload, so reject rather than act on unverified data.
        if ($secret === '') {
            Log::error('StripeWebhook: STRIPE_WEBHOOK_SECRET is not configured; rejecting event.');

            return response()->json(['error' => 'webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, (string) $signature, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('StripeWebhook: invalid payload', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('StripeWebhook: invalid signature', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'invalid signature'], 400);
        }

        // We only act on subscription events, but we still ACK everything else
        // with a 2xx so Stripe does not treat them as failures and disable the
        // endpoint.
        if (in_array($event->type, self::HANDLED_EVENTS, true)) {
            $this->syncSubscription($event->data->object, $event->type);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Resolve the local billing row from the subscription's customer, derive
     * the plan, and write both stores.
     *
     * @param  \Stripe\Subscription  $subscription
     */
    private function syncSubscription($subscription, string $eventType): void
    {
        $customerId = $subscription->customer ?? null;

        if (!$customerId) {
            return;
        }

        $billing = UserBilling::where('stripe_customer_id', $customerId)->first();

        if (!$billing) {
            // The customer exists in Stripe but we have no local row yet
            // (e.g. created out-of-band). The backfill/reconcile command will
            // pick it up; nothing to update here.
            Log::info('StripeWebhook: no local billing row for customer', [
                'customer' => $customerId,
                'event' => $eventType,
            ]);

            return;
        }

        $plan = $this->planForSubscription($subscription, $eventType);

        $priceId = $subscription->items->data[0]->price->id ?? $billing->stripe_price_id;
        $status = $eventType === 'customer.subscription.deleted'
            ? 'canceled'
            : ($subscription->status ?? $billing->stripe_status);

        $billing->update([
            'plan_key' => $plan,
            'stripe_subscription_id' => $subscription->id ?? $billing->stripe_subscription_id,
            'stripe_price_id' => $priceId,
            'stripe_status' => $status,
            'current_period_end' => isset($subscription->current_period_end)
                ? now()->setTimestamp($subscription->current_period_end)
                : $billing->current_period_end,
            'cancel_at_period_end' => $subscription->cancel_at_period_end ?? false,
        ]);

        // Mirror to Supabase for non-Laravel consumers. Best-effort: a Supabase
        // failure must not fail the webhook ACK.
        $supabaseUserId = optional($billing->user)->supabase_user_id;
        BillingProfileService::setPlan($supabaseUserId, $plan);

        Log::info('StripeWebhook: synced plan', [
            'user_id' => $billing->user_id,
            'customer' => $customerId,
            'event' => $eventType,
            'plan' => $plan,
            'status' => $status,
        ]);
    }

    /**
     * Derive the plan key from a subscription. A subscription on the Pro price
     * that is in a live state is 'pro'; anything else (free price, canceled,
     * unpaid, deleted) is 'free'. Fails safe to 'free'.
     *
     * @param  \Stripe\Subscription  $subscription
     */
    private function planForSubscription($subscription, string $eventType): string
    {
        if ($eventType === 'customer.subscription.deleted') {
            return 'free';
        }

        $liveStatuses = ['active', 'trialing', 'past_due'];
        if (!in_array($subscription->status ?? '', $liveStatuses, true)) {
            return 'free';
        }

        $proPriceId = (string) config('billing.pro_price_id');

        foreach ($subscription->items->data ?? [] as $item) {
            if (($item->price->id ?? null) === $proPriceId && $proPriceId !== '') {
                return 'pro';
            }
        }

        return 'free';
    }
}
