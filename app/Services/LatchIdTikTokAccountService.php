<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LatchIdTikTokAccountService
{
    public function findForLatchId(?string $latchIdUserId): ?array
    {
        $latchIdUserId = trim((string) $latchIdUserId);

        if ($latchIdUserId === '') {
            return null;
        }

        $supabaseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceRoleKey = (string) config('services.supabase_service_role_key');

        if ($supabaseUrl === '' || $serviceRoleKey === '') {
            Log::warning('LatchID TikTok lookup skipped because Supabase service configuration is missing.');

            return null;
        }

        $response = Http::withHeaders([
            'apikey' => $serviceRoleKey,
            'Authorization' => 'Bearer ' . $serviceRoleKey,
        ])->acceptJson()->get($supabaseUrl . '/rest/v1/latchid_tiktok_accounts', [
            'latchid_user_id' => 'eq.' . $latchIdUserId,
            'select' => 'display_name,avatar_url,tiktok_open_id,linked_at',
            'limit' => 1,
        ]);

        if (!$response->successful()) {
            Log::warning('LatchID TikTok account lookup failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $rows = $response->json();

        if (!is_array($rows) || empty($rows[0]) || !is_array($rows[0])) {
            return null;
        }

        return $rows[0];
    }
}
