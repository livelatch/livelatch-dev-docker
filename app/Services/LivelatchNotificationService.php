<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads and writes Livelatch notifications stored in Supabase
 * (public.livelatch_notifications) and per-user read state
 * (public.livelatch_notification_reads).
 *
 * All access goes through the Supabase REST API using the service-role key,
 * which bypasses row-level security. Credentials are resolved through
 * config('services.supabase_*') rather than env() directly so they keep
 * working when the Laravel config cache is built (php artisan config:cache).
 *
 * "Read" state is per user: a notification (especially a global one, where
 * user_id is null) is considered read for a given user only if a row exists in
 * livelatch_notification_reads for that (notification_id, user_id) pair.
 */
class LivelatchNotificationService
{
    private const NOTIFICATIONS_TABLE = 'livelatch_notifications';
    private const READS_TABLE = 'livelatch_notification_reads';

    /**
     * Notifications visible to a user (their own + global), newest first, each
     * annotated with an `is_read` boolean for that user.
     */
    public static function forUser(?string $latchIdUserId, int $limit = 30): Collection
    {
        $query = [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $limit,
        ];
        static::applyAudienceFilter($query, $latchIdUserId);

        $response = static::request('get', self::NOTIFICATIONS_TABLE, $query);

        if (!$response || !$response->successful()) {
            return collect();
        }

        $notifications = collect($response->json());

        if ($notifications->isEmpty()) {
            return $notifications;
        }

        $readIds = static::readNotificationIds($latchIdUserId);

        return $notifications->map(function (array $notification) use ($readIds) {
            $notification['is_read'] = in_array($notification['id'] ?? null, $readIds, true);

            return $notification;
        })->values();
    }

    /**
     * Legacy alias used by older callers. Returns the latest notifications for a
     * user, newest first, annotated with `is_read`.
     */
    public static function latestForUser(?string $latchIdUserId, int $limit = 5): Collection
    {
        return static::forUser($latchIdUserId, $limit);
    }

    /**
     * Number of notifications visible to the user that they have not yet read.
     */
    public static function unreadCount(?string $latchIdUserId): int
    {
        // Pull the (small) set of visible notification ids and subtract reads.
        // The notification volume here is intentionally low, so this stays cheap
        // and avoids a fragile not-in() REST filter against another table.
        $query = [
            'select' => 'id',
            'order' => 'created_at.desc',
            'limit' => 200,
        ];
        static::applyAudienceFilter($query, $latchIdUserId);

        $response = static::request('get', self::NOTIFICATIONS_TABLE, $query);

        if (!$response || !$response->successful()) {
            return 0;
        }

        $visibleIds = collect($response->json())->pluck('id')->filter()->all();

        if (empty($visibleIds)) {
            return 0;
        }

        $readIds = static::readNotificationIds($latchIdUserId);

        return count(array_diff($visibleIds, $readIds));
    }

    /**
     * Mark a single notification as read for a user (idempotent upsert).
     */
    public static function markAsRead(?string $latchIdUserId, string $notificationId): bool
    {
        if (!$latchIdUserId || $notificationId === '') {
            return false;
        }

        $response = static::request(
            'post',
            self::READS_TABLE,
            ['on_conflict' => 'notification_id,user_id'],
            [[
                'notification_id' => $notificationId,
                'user_id' => $latchIdUserId,
            ]],
            ['Prefer' => 'resolution=merge-duplicates,return=minimal'],
        );

        return (bool) ($response && $response->successful());
    }

    /**
     * Mark every notification currently visible (and unread) to the user as read.
     * Returns the number of notifications newly marked.
     */
    public static function markAllAsRead(?string $latchIdUserId): int
    {
        if (!$latchIdUserId) {
            return 0;
        }

        $query = [
            'select' => 'id',
            'order' => 'created_at.desc',
            'limit' => 200,
        ];
        static::applyAudienceFilter($query, $latchIdUserId);

        $response = static::request('get', self::NOTIFICATIONS_TABLE, $query);

        if (!$response || !$response->successful()) {
            return 0;
        }

        $visibleIds = collect($response->json())->pluck('id')->filter()->all();
        $unreadIds = array_values(array_diff($visibleIds, static::readNotificationIds($latchIdUserId)));

        if (empty($unreadIds)) {
            return 0;
        }

        $rows = array_map(fn ($id) => [
            'notification_id' => $id,
            'user_id' => $latchIdUserId,
        ], $unreadIds);

        $write = static::request(
            'post',
            self::READS_TABLE,
            ['on_conflict' => 'notification_id,user_id'],
            $rows,
            ['Prefer' => 'resolution=merge-duplicates,return=minimal'],
        );

        return ($write && $write->successful()) ? count($unreadIds) : 0;
    }

    /**
     * Mark a notification as unread again for a user (moves it back out of the
     * inbox). Returns true on success.
     */
    public static function markAsUnread(?string $latchIdUserId, string $notificationId): bool
    {
        if (!$latchIdUserId || $notificationId === '') {
            return false;
        }

        $response = static::request('delete', self::READS_TABLE, [
            'notification_id' => 'eq.' . $notificationId,
            'user_id' => 'eq.' . $latchIdUserId,
        ]);

        return (bool) ($response && $response->successful());
    }

    /**
     * Publish a notification. Pass user_id = null for a global notification, or a
     * LatchID user UUID (users.supabase_user_id) to target one user. Intended for
     * server-side senders such as billing/invoice flows and admin tooling.
     *
     * @param  array{user_id?:?string,source?:string,type?:string,severity?:string,title:string,message?:?string,action_url?:?string,icon?:?string,metadata?:?array}  $attributes
     */
    public static function publish(array $attributes): ?array
    {
        if (empty($attributes['title'])) {
            return null;
        }

        $payload = [
            'user_id' => $attributes['user_id'] ?? null,
            'source' => $attributes['source'] ?? 'livelatch',
            'type' => $attributes['type'] ?? 'system',
            'severity' => $attributes['severity'] ?? 'info',
            'title' => $attributes['title'],
            'message' => $attributes['message'] ?? null,
            'action_url' => $attributes['action_url'] ?? null,
            'icon' => $attributes['icon'] ?? null,
            'metadata' => $attributes['metadata'] ?? null,
        ];

        $response = static::request(
            'post',
            self::NOTIFICATIONS_TABLE,
            [],
            [$payload],
            ['Prefer' => 'return=representation'],
        );

        if (!$response || !$response->successful()) {
            return null;
        }

        $notification = $response->json()[0] ?? null;

        // Targeted notifications (user_id set) are also emailed when the user has
        // notification emails enabled. Global notifications stay in-app only.
        if ($notification && !empty($payload['user_id'])) {
            static::maybeEmailNotification($payload['user_id'], $notification);
        }

        return $notification;
    }

    /**
     * Best-effort: email a targeted notification to the user when their
     * preferences allow it. Failures never affect notification publishing.
     */
    private static function maybeEmailNotification(string $latchIdUserId, array $notification): void
    {
        try {
            $prefs = EmailPreferenceService::getFor($latchIdUserId);

            if (empty($prefs['notification_emails'])) {
                return;
            }

            $user = User::where('supabase_user_id', $latchIdUserId)->first();
            $email = $user ? strtolower(trim((string) $user->email)) : '';

            if ($email === '') {
                return;
            }

            $title = (string) ($notification['title'] ?? 'New notification');
            $message = trim((string) ($notification['message'] ?? ''));
            $actionUrl = trim((string) ($notification['action_url'] ?? ''));

            $html = '<h2 style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:18px;color:#111;">'
                . e($title) . '</h2>';

            if ($message !== '') {
                $html .= '<p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#333;">'
                    . nl2br(e($message)) . '</p>';
            }

            if ($actionUrl !== '') {
                $html .= '<p style="margin:0 0 16px;"><a href="' . e($actionUrl)
                    . '" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#2563eb;">View in Livelatch</a></p>';
            }

            $html .= '<p style="margin:24px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#888;">'
                . 'You receive these because notification emails are on in your LatchID settings.</p>';

            ResendContactService::send([$email], $title, $html, $message !== '' ? $message : $title);
        } catch (\Throwable $e) {
            Log::warning('LivelatchNotificationService: notification email failed', [
                'user_id' => $latchIdUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notification ids the user has already read.
     *
     * @return array<int, string>
     */
    private static function readNotificationIds(?string $latchIdUserId): array
    {
        if (!$latchIdUserId) {
            return [];
        }

        $response = static::request('get', self::READS_TABLE, [
            'select' => 'notification_id',
            'user_id' => 'eq.' . $latchIdUserId,
            'limit' => 500,
        ]);

        if (!$response || !$response->successful()) {
            return [];
        }

        return collect($response->json())->pluck('notification_id')->filter()->all();
    }

    /**
     * Restrict a query to the rows a user is allowed to see: their own
     * notifications plus global ones (user_id is null).
     */
    private static function applyAudienceFilter(array &$query, ?string $latchIdUserId): void
    {
        if ($latchIdUserId) {
            $query['or'] = '(user_id.eq.' . $latchIdUserId . ',user_id.is.null)';
        } else {
            $query['user_id'] = 'is.null';
        }
    }

    /**
     * Perform a Supabase REST request, returning the response or null when the
     * service is not configured / the request throws.
     */
    private static function request(
        string $method,
        string $table,
        array $query = [],
        ?array $body = null,
        array $extraHeaders = [],
    ) {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceKey = (string) config('services.supabase_service_role_key');

        if ($baseUrl === '' || $serviceKey === '') {
            Log::warning('LivelatchNotificationService: Supabase credentials are not configured.');

            return null;
        }

        $url = $baseUrl . '/rest/v1/' . $table;

        try {
            $request = Http::withHeaders(array_merge([
                'apikey' => $serviceKey,
                'Authorization' => 'Bearer ' . $serviceKey,
                'Content-Type' => 'application/json',
            ], $extraHeaders));

            if ($query) {
                $url .= '?' . static::buildQueryString($query);
            }

            $response = match ($method) {
                'get' => $request->get($url),
                'post' => $request->post($url, $body ?? []),
                'delete' => $request->delete($url),
                default => null,
            };

            if ($response && !$response->successful()) {
                Log::warning('LivelatchNotificationService: Supabase request failed', [
                    'method' => $method,
                    'table' => $table,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('LivelatchNotificationService: Supabase request threw', [
                'method' => $method,
                'table' => $table,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build a PostgREST query string without urlencoding the operator commas
     * inside values such as or=(...) which PostgREST needs to parse literally.
     */
    private static function buildQueryString(array $query): string
    {
        $parts = [];

        foreach ($query as $key => $value) {
            $parts[] = rawurlencode((string) $key) . '=' . str_replace(
                ['%28', '%29', '%2C', '%2A'],
                ['(', ')', ',', '*'],
                rawurlencode((string) $value),
            );
        }

        return implode('&', $parts);
    }
}
