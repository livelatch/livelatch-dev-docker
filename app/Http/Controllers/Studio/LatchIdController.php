<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Services\EmailPreferenceService;
use App\Services\LatchIdTikTokAccountService;
use Illuminate\Http\Request;

class LatchIdController extends Controller
{
    public function edit(Request $request, LatchIdTikTokAccountService $tikTokAccounts)
    {
        $user = $request->user();
        $latchIdUserId = $user?->supabase_user_id;
        $tikTokAccount = $tikTokAccounts->findForLatchId($latchIdUserId);
        $emailPreferences = EmailPreferenceService::getFor($latchIdUserId);

        return view('studio.account.latchid', [
            'tikTokAccount' => $tikTokAccount,
            'tikTokAuthorizeUrl' => $this->tikTokAuthorizeUrl($latchIdUserId, url('/studio/latchid')),
            'emailPreferences' => $emailPreferences,
        ]);
    }

    private function tikTokAuthorizeUrl(?string $latchIdUserId, string $returnUrl): ?string
    {
        $latchIdUserId = trim((string) $latchIdUserId);
        $authorizeUrl = trim((string) config('services.tiktok_oauth_authorize_url'));

        if ($latchIdUserId === '' || $authorizeUrl === '') {
            return null;
        }

        return $authorizeUrl . '?' . http_build_query([
            'latchid_user_id' => $latchIdUserId,
            'return_url' => $returnUrl,
        ]);
    }
}
