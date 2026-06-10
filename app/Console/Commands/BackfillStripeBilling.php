<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserBilling;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;

class BackfillStripeBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:backfill-stripe {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Stripe customers and Free subscriptions for users missing billing records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Stripe::setApiKey(config('billing.stripe_secret'));

        $users = User::whereDoesntHave('billing')->get();

        if ($users->count() === 0) {
            $this->info('No users require billing backfill.');
            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} users requiring billing setup.");

        foreach ($users as $user) {

            if ($this->option('dry-run')) {
                $this->line("Would create billing for: {$user->email}");
                continue;
            }

            try {

                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'livelatch_user_id' => $user->id,
                        'supabase_user_id' => $user->supabase_user_id,
                    ],
                ]);

                $subscription = Subscription::create([
                    'customer' => $customer->id,
                    'items' => [[
                        'price' => config('billing.free_price_id'),
                    ]],
                ]);

                UserBilling::create([
                    'user_id' => $user->id,
                    'plan_key' => 'free',
                    'stripe_customer_id' => $customer->id,
                    'stripe_subscription_id' => $subscription->id,
                    'stripe_price_id' => config('billing.free_price_id'),
                    'stripe_status' => $subscription->status,
                    'current_period_end' => isset($subscription->current_period_end)
                        ? now()->setTimestamp($subscription->current_period_end)
                        : null,
                    'cancel_at_period_end' => $subscription->cancel_at_period_end ?? false,
                ]);

                $this->info("Created billing for {$user->email}");

            } catch (\Exception $e) {

                $this->error("Failed for {$user->email}");
                $this->error($e->getMessage());

            }
        }

        $this->info('Backfill complete.');

        return Command::SUCCESS;
    }
}