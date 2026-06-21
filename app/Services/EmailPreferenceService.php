<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads and writes per-user email preferences stored in Supabase
 * (public.user_email_preferences). This is the source of truth for marketing
 * and notification-email consent; Resend is kept in sync from here.
 *
 * Service emails (outages, maintenance, security, billing) are mandatory and
 * are intentionally not represented here -- every user always receives them.
 *
 * Access uses the Supabase REST API with the service-role key (bypasses RLS),
 * mirroring LivelatchNotificationService. Credentials resolve through
 * config('services.supabase_*') so they survive php artisan config:cache.
 */
class EmailPreferenceService
{
    private const TABLE = 'user_email_preferences';

    /**
     * Default preferences applied when a user has no row yet. Marketing is
     * explicit opt-in and defaults to OFF (the signup checkbox is unticked, per
     * GDPR — a user with no stored consent must never be treated as opted in);
     * notification emails (activity on the user's own account) default to ON.
     *
     * @return array{marketing_opt_in:bool,notification_emails:bool}
     */
    public static function defaults(): array
    {
        return [
            'marketing_opt_in' => false,
            'notification_emails' => true,
            'cookie_consent' => null,
        ];
    }

    /**
     * Current preferences for a LatchID user (auth.users id). Returns defaults
     * (without persisting) when no row exists or Supabase is unavailable, so
     * callers always get a usable shape.
     *
     * @return array{marketing_opt_in:bool,notification_emails:bool,resend_contact_id:?string,synced_at:?string,exists:bool}
     */
    public static function getFor(?string $latchIdUserId): array
    {
        $defaults = static::defaults() + [
            'resend_contact_id' => null,
            'synced_at' => null,
            'exists' => false,
        ];

        if (!$latchIdUserId) {
            return $defaults;
        }

        $response = static::request('get', [
            'select' => 'marketing_opt_in,notification_emails,cookie_consent,resend_contact_id,synced_at',
            'user_id' => 'eq.' . $latchIdUserId,
            'limit' => 1,
        ]);

        if (!$response || !$response->successful()) {
            return $defaults;
        }

        $row = $response->json()[0] ?? null;

        if (!$row) {
            return $defaults;
        }

        return [
            'marketing_opt_in' => (bool) ($row['marketing_opt_in'] ?? false),
            'notification_emails' => (bool) ($row['notification_emails'] ?? true),
            'cookie_consent' => $row['cookie_consent'] ?? null,
            'resend_contact_id' => $row['resend_contact_id'] ?? null,
            'synced_at' => $row['synced_at'] ?? null,
            'exists' => true,
        ];
    }

    /**
     * Create or update a user's preferences (idempotent upsert on user_id).
     * Only the keys present in $attributes are written; pass any of
     * marketing_opt_in, notification_emails, resend_contact_id, synced_at.
     */
    public static function upsert(?string $latchIdUserId, array $attributes): bool
    {
        if (!$latchIdUserId) {
            return false;
        }

        $payload = ['user_id' => $latchIdUserId];

        foreach (['marketing_opt_in', 'notification_emails'] as $boolKey) {
            if (array_key_exists($boolKey, $attributes)) {
                $payload[$boolKey] = (bool) $attributes[$boolKey];
            }
        }

        foreach (['resend_contact_id', 'synced_at'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $payload[$key] = $attributes[$key];
            }
        }

        if (array_key_exists('cookie_consent', $attributes)) {
            $consent = $attributes['cookie_consent'];
            $payload['cookie_consent'] = in_array($consent, ['all', 'deny'], true) ? $consent : null;
        }

        $response = static::request(
            'post',
            ['on_conflict' => 'user_id'],
            [$payload],
            ['Prefer' => 'resolution=merge-duplicates,return=minimal'],
        );

        return (bool) ($response && $response->successful());
    }

    /**
     * Perform a Supabase REST request against the preferences table.
     */
    private static function request(
        string $method,
        array $query = [],
        ?array $body = null,
        array $extraHeaders = [],
    ) {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceKey = (string) config('services.supabase_service_role_key');

        if ($baseUrl === '' || $serviceKey === '') {
            Log::warning('EmailPreferenceService: Supabase credentials are not configured.');

            return null;
        }

        $url = $baseUrl . '/rest/v1/' . self::TABLE;

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        try {
            $request = Http::withHeaders(array_merge([
                'apikey' => $serviceKey,
                'Authorization' => 'Bearer ' . $serviceKey,
                'Content-Type' => 'application/json',
            ], $extraHeaders));

            $response = match ($method) {
                'get' => $request->get($url),
                'post' => $request->post($url, $body ?? []),
                default => null,
            };

            if ($response && !$response->successful()) {
                Log::warning('EmailPreferenceService: Supabase request failed', [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('EmailPreferenceService: Supabase request threw', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
