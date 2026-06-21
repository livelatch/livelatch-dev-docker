<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailPreferenceService;
use App\Services\ResendContactService;
use Illuminate\Console\Command;

/**
 * Imports existing Livelatch users into the Resend audience as contacts, tagging
 * each with their identity/plan/preference properties and applying their
 * marketing opt-in as the Resend unsubscribed flag.
 *
 * Only users linked to LatchID (supabase_user_id present) are synced, because
 * preferences are keyed on the Supabase auth user.
 */
class SyncResendContacts extends Command
{
    protected $signature = 'resend:sync-contacts {--dry-run}';

    protected $description = 'Import/refresh Livelatch users as Resend audience contacts with preference tags';

    public function handle(): int
    {
        if (!ResendContactService::configured()) {
            $this->error('Resend is not configured. Set RESEND_FULL_API_KEY and RESEND_AUDIENCE_ID.');
            return Command::FAILURE;
        }

        if (!$this->option('dry-run')) {
            $this->info('Ensuring Resend contact properties exist...');
            ResendContactService::ensureProperties();
        }

        $users = User::whereNotNull('supabase_user_id')->get();

        if ($users->isEmpty()) {
            $this->info('No LatchID-linked users to sync.');
            return Command::SUCCESS;
        }

        $this->info("Syncing {$users->count()} contact(s) to Resend...");
        $synced = 0;

        foreach ($users as $user) {
            $prefs = EmailPreferenceService::getFor($user->supabase_user_id);

            if ($this->option('dry-run')) {
                $flag = $prefs['marketing_opt_in'] ? 'marketing:on' : 'marketing:off';
                $this->line("Would sync {$user->email} ({$flag})");
                continue;
            }

            $contactId = ResendContactService::syncContact($user, $prefs);

            if ($contactId !== null) {
                EmailPreferenceService::upsert($user->supabase_user_id, [
                    'resend_contact_id' => $contactId,
                    'synced_at' => now()->toIso8601String(),
                ]);
                $synced++;
                $this->info("Synced {$user->email}");
            } else {
                $this->error("Failed to sync {$user->email}");
            }
        }

        $this->info("Done. {$synced} contact(s) synced.");

        return Command::SUCCESS;
    }
}
