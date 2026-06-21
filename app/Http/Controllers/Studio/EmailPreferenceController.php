<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\EmailPreferenceService;
use App\Services\ResendContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets a user manage which non-service emails they receive from LatchID settings.
 * Service emails (outages, maintenance, security) are mandatory and are not
 * exposed here.
 */
class EmailPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'marketing_opt_in' => ['nullable', 'boolean'],
            'notification_emails' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $latchIdUserId = $user?->supabase_user_id;

        if (!$latchIdUserId) {
            return back()->with('status', 'email-preferences-unavailable');
        }

        $marketingOptIn = $request->boolean('marketing_opt_in');
        $notificationEmails = $request->boolean('notification_emails');

        EmailPreferenceService::upsert($latchIdUserId, [
            'marketing_opt_in' => $marketingOptIn,
            'notification_emails' => $notificationEmails,
        ]);

        // Keep the local mirror and Resend contact in step with the new choice.
        $user->forceFill(['marketing_opt_in' => $marketingOptIn])->save();

        ResendContactService::syncUser($user, [
            'marketing_opt_in' => $marketingOptIn,
            'notification_emails' => $notificationEmails,
        ]);

        return back()->with('status', 'email-preferences-updated');
    }
}
