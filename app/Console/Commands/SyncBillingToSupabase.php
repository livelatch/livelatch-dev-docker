<?php

namespace App\Console\Commands;

use App\Models\UserBilling;
use App\Services\BillingProfileService;
use Illuminate\Console\Command;

class SyncBillingToSupabase extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:sync-supabase {--dry-run}';

    /**
     * @var string
     */
    protected $description = 'Mirror every user_billing.plan_key into Supabase profiles.plan_key (reconcile backstop for missed Stripe webhooks)';

    public function handle(): int
    {
        $rows = UserBilling::with('user')->get();

        if ($rows->isEmpty()) {
            $this->info('No billing rows to sync.');

            return Command::SUCCESS;
        }

        $this->info("Syncing {$rows->count()} billing rows to Supabase profiles...");

        $synced = 0;
        $skipped = 0;

        foreach ($rows as $billing) {
            $supabaseUserId = optional($billing->user)->supabase_user_id;
            $plan = BillingProfileService::normalisePlan($billing->plan_key);

            if (!$supabaseUserId) {
                $this->line("  skip user_id={$billing->user_id} (no supabase_user_id)");
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  would set {$supabaseUserId} -> {$plan}");
                continue;
            }

            if (BillingProfileService::setPlan($supabaseUserId, $plan)) {
                $this->line("  {$supabaseUserId} -> {$plan}");
                $synced++;
            } else {
                $this->error("  failed {$supabaseUserId} -> {$plan}");
                $skipped++;
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("Done. Synced {$synced}, skipped {$skipped}.");
        }

        return Command::SUCCESS;
    }
}
