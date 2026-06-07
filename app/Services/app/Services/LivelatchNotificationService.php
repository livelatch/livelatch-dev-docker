<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LivelatchNotificationService
{
    public static function latestForUser(?string $latchIdUserId, int $limit = 5)
    {
        $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/livelatch_notifications';

        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $limit,
        ];

        if ($latchIdUserId) {
            $query['or'] = '(user_id.eq.' . $latchIdUserId . ',user_id.is.null)';
        } else {
            $query['user_id'] = 'is.null';
        }

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
            'Content-Type' => 'application/json',
        ])->get($url, $query);

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json());
    }

    public static function unreadCount(?string $latchIdUserId): int
    {
        $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/livelatch_notifications';

        $query = [
            'select' => 'id',
            'read_at' => 'is.null',
        ];

        if ($latchIdUserId) {
            $query['or'] = '(user_id.eq.' . $latchIdUserId . ',user_id.is.null)';
        } else {
            $query['user_id'] = 'is.null';
        }

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
            'Prefer' => 'count=exact',
        ])->get($url, $query);

        if (!$response->successful()) {
            return 0;
        }

        $contentRange = $response->header('Content-Range');

        if (!$contentRange || !str_contains($contentRange, '/')) {
            return count($response->json() ?? []);
        }

        return (int) explode('/', $contentRange)[1];
    }
}