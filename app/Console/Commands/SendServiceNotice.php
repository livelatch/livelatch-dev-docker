<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LivelatchNotificationService;
use App\Services\ResendContactService;
use Illuminate\Console\Command;

/**
 * Sends a service notice (outage, maintenance, security) to every Livelatch user
 * by email. Service emails are mandatory and ignore marketing preferences, so
 * this deliberately does not consult user_email_preferences.
 *
 * Optionally also publishes a matching global in-app notification.
 *
 * Example:
 *   php artisan livelatch:service-notice \
 *     --subject="Scheduled maintenance" \
 *     --message="Livelatch will be briefly unavailable at 02:00 UTC." \
 *     --notify
 */
class SendServiceNotice extends Command
{
    protected $signature = 'livelatch:service-notice
        {--subject= : Email subject line}
        {--message= : Plain-text body of the notice}
        {--notify : Also publish a global in-app notification}
        {--dry-run : List recipients without sending}';

    protected $description = 'Email a mandatory service notice to all users (outage/maintenance/security)';

    public function handle(): int
    {
        $subject = trim((string) $this->option('subject'));
        $message = trim((string) $this->option('message'));

        if ($subject === '' || $message === '') {
            $this->error('Both --subject and --message are required.');
            return Command::FAILURE;
        }

        if (!ResendContactService::configured()) {
            $this->error('Resend is not configured. Set RESEND_FULL_API_KEY and RESEND_AUDIENCE_ID.');
            return Command::FAILURE;
        }

        $html = '<h2 style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:18px;color:#111;">'
            . e($subject) . '</h2>'
            . '<p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#333;">'
            . nl2br(e($message)) . '</p>'
            . '<p style="margin:24px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#888;">'
            . 'This is a service message about your Livelatch account and is sent to all users.</p>';

        $users = User::whereNotNull('email')->get();
        $sent = 0;

        foreach ($users as $user) {
            $email = strtolower(trim((string) $user->email));

            if ($email === '') {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Would email {$email}");
                continue;
            }

            if (ResendContactService::send([$email], $subject, $html, $message) !== null) {
                $sent++;
            } else {
                $this->error("Failed to email {$email}");
            }
        }

        if ($this->option('notify') && !$this->option('dry-run')) {
            LivelatchNotificationService::publish([
                'user_id' => null,
                'type' => 'service',
                'severity' => 'warning',
                'title' => $subject,
                'message' => $message,
            ]);
            $this->info('Published global in-app notification.');
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Service notice sent to {$sent} user(s).");

        return Command::SUCCESS;
    }
}
