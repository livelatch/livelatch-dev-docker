<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LivelatchNotificationService
{
    public static function latestForUser(string $latchIdUserId, int $limit = 5)
{
    $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/livelatch_notifications';

    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
        'Content-Type' => 'application/json',
    ])->get($url, [
        'or' => '(user_id.eq.' . $latchIdUserId . ',user_id.is.null)',
        'select' => '*',
        'order' => 'created_at.desc',
        'limit' => $limit,
    ]);

    if (!$response->successful()) {
        return collect();
    }

    return collect($response->json());
}

    public static function unreadCount(string $latchIdUserId): int
{
    $url = rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/livelatch_notifications';

    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_SERVICE_ROLE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
        'Prefer' => 'count=exact',
    ])->get($url, [
        'or' => '(user_id.eq.' . $latchIdUserId . ',user_id.is.null)',
        'read_at' => 'is.null',
        'select' => 'id',
    ]);

    return (int) ($response->header('Content-Range')
        ? explode('/', $response->header('Content-Range'))[1]
        : 0);
}
}