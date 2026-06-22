<?php

namespace App\Console\Commands;

use App\Models\UserBilling;
use App\Services\BillingProfileService;
use App\Services\StripePlanResolver;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Subscription;

/**
 * One-time / occasional reconcile that pulls the TRUTH from Stripe into both
 * stores. Unlike billing:sync-supabase (which only mirrors MySQL -> Supabase),
 * this queries the Stripe API per customer, derives the real plan, and writes
 * user_billing.plan_key (MySQL) + profiles.plan_key (Supabase).
 *
 * Needed because the webhook endpoint was 404ing historically, so any plan
 * change that happened while it was dead never landed in MySQL. Going forward
 * the webhook keeps things current; run this to backfill the gap or to repair
 * drift after an outage.
 *
 * A customer may hold more than one subscription (signup creates a Free sub,
 * checkoutPro creates a second Pro sub on the same customer), so we scan ALL of
 * the customer's subscriptions and treat them as Pro if any live one is on the
 * Pro price.
 */
class ReconcileStripePlans extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:reconcile-stripe {--dry-run}';

    /**
     * @var string
     */
    protected $description = 'Pull plan status from Stripe into user_billing + Supabase profiles (backfill for events missed while the webhook was down)';

    public function handle(): int
    {
        Stripe::setApiKey(config('billing.stripe_secret'));

        $rows = UserBilling::with('user')->whereNotNull('stripe_customer_id')->get();

        if ($rows->isEmpty()) {
            $this->info('No billing rows with a Stripe customer to reconcile.');

            return Command::SUCCESS;
        }

        $this->info("Reconciling {$rows->count()} customers against Stripe...");

        $changed = 0;
        $unchanged = 0;
        $failed = 0;

        foreach ($rows as $billing) {
            try {
                $resolved = $this->resolveFromStripe($billing->stripe_customer_id);
            } catch (\Throwable $e) {
                $this->error("  {$billing->stripe_customer_id}: Stripe error — {$e->getMessage()}");
                $failed++;
                continue;
            }

            if (!$resolved) {
                $this->line("  {$billing->stripe_customer_id}: no subscriptions found — leaving as '{$billing->plan_key}'");
                $unchanged++;
                continue;
            }

            [$plan, $subscription] = $resolved;
            $was = $billing->plan_key;
            $tag = $plan === $was ? '' : "  (was {$was})";

            if ($this->option('dry-run')) {
                $this->line("  user_id={$billing->user_id}: {$plan}{$tag} [dry-run]");
                $plan === $was ? $unchanged++ : $changed++;
                continue;
            }

            $billing->update([
                'plan_key' => $plan,
                'stripe_subscription_id' => $subscription->id,
                'stripe_price_id' => $subscription->items->data[0]->price->id ?? $billing->stripe_price_id,
                'stripe_status' => $subscription->status,
                'current_period_end' => isset($subscription->current_period_end)
                    ? now()->setTimestamp($subscription->current_period_end)
                    : $billing->current_period_end,
                'cancel_at_period_end' => $subscription->cancel_at_period_end ?? false,
            ]);

            BillingProfileService::setPlan(optional($billing->user)->supabase_user_id, $plan);

            $this->line("  user_id={$billing->user_id}: {$plan}{$tag}");
            $plan === $was ? $unchanged++ : $changed++;
        }

        $this->info("Done. Changed {$changed}, unchanged {$unchanged}, failed {$failed}.");

        return Command::SUCCESS;
    }

    /**
     * Inspect all of a customer's subscriptions and return [plan, subscription]
     * where the chosen subscription is the Pro one if any live Pro exists,
     * otherwise the most relevant (latest) subscription. Returns null when the
     * customer has no subscriptions at all.
     *
     * @return array{0:string,1:\Stripe\Subscription}|null
     */
    private function resolveFromStripe(string $customerId): ?array
    {
        $subscriptions = Subscription::all([
            'customer' => $customerId,
            'status' => 'all',
            'limit' => 100,
            'expand' => ['data.items.data.price'],
        ]);

        $latest = null;
        foreach ($subscriptions->data as $subscription) {
            if ($latest === null) {
                $latest = $subscription;
            }

            if (StripePlanResolver::planForSubscription($subscription) === 'pro') {
                return ['pro', $subscription];
            }
        }

        if ($latest === null) {
            return null;
        }

        return ['free', $latest];
    }
}
