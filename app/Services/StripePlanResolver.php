<?php

namespace App\Services;

/**
 * Single definition of "what plan is this Stripe subscription?" so the webhook
 * handler and the Stripe reconcile command can never disagree.
 *
 * A subscription on the configured Pro price in a live status is 'pro';
 * anything else (free price, canceled, unpaid, incomplete) fails safe to 'free'.
 *
 * NOTE: matches on a single price ID for the alpha. This does not scale to
 * multiple Pro prices (monthly/annual/promo). Planned switch to product-based
 * resolution — see docs/todos/billing-plan-resolution-by-product.md. This is the
 * single change point: the webhook handler and billing:reconcile-stripe both use
 * this method.
 */
class StripePlanResolver
{
    private const LIVE_STATUSES = ['active', 'trialing', 'past_due'];

    /**
     * @param  \Stripe\Subscription  $subscription
     */
    public static function planForSubscription($subscription): string
    {
        if (!in_array($subscription->status ?? '', self::LIVE_STATUSES, true)) {
            return 'free';
        }

        $proPriceId = (string) config('billing.pro_price_id');

        if ($proPriceId === '') {
            return 'free';
        }

        foreach ($subscription->items->data ?? [] as $item) {
            if (($item->price->id ?? null) === $proPriceId) {
                return 'pro';
            }
        }

        return 'free';
    }
}
