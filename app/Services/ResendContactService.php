<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Syncs Livelatch users into Resend as audience contacts and sends transactional
 * email (service notices, targeted notification emails) over the Resend HTTP API.
 *
 * Marketing consent is mirrored to Resend's `unsubscribed` flag, which governs
 * whether a contact receives broadcasts: marketing_opt_in === false maps to
 * unsubscribed === true. The remaining preference/identity values are stored as
 * Resend contact properties (the "tags" surfaced in the Resend dashboard).
 *
 * Requires a full-access Resend API key (config services.resend.api_key) with
 * Contacts/Audiences scopes. When the key or audience is not configured, every
 * method degrades to a no-op so the rest of the app keeps working.
 */
class ResendContactService
{
    private const BASE_URL = 'https://api.resend.com';

    /**
     * Contact properties ("tags") defined in Resend. Resend property types are
     * limited to string/number, so booleans are stored as 'true'/'false'.
     *
     * @var array<string, string>
     */
    private const PROPERTIES = [
        'livelatch_user_id' => 'string',
        'plan_key' => 'string',
        'source' => 'string',
        'notification_emails' => 'string',
    ];

    public static function configured(): bool
    {
        return static::apiKey() !== '' && static::audienceId() !== '';
    }

    /**
     * Create or update the Resend contact for a user from their current
     * preferences. Returns the Resend contact id on success, or null.
     *
     * @param  array{marketing_opt_in?:bool,notification_emails?:bool}  $prefs
     */
    public static function syncContact(User $user, array $prefs): ?string
    {
        if (!static::configured()) {
            return null;
        }

        $email = strtolower(trim((string) $user->email));

        if ($email === '') {
            return null;
        }

        $marketingOptIn = (bool) ($prefs['marketing_opt_in'] ?? true);
        $notificationEmails = (bool) ($prefs['notification_emails'] ?? true);

        $payload = [
            'email' => $email,
            'unsubscribed' => !$marketingOptIn,
            'first_name' => (string) ($user->name ?? ''),
            'properties' => [
                'livelatch_user_id' => (string) $user->id,
                'plan_key' => (string) (optional($user->billing)->plan_key ?? 'free'),
                'source' => 'livelatch',
                'notification_emails' => $notificationEmails ? 'true' : 'false',
            ],
        ];

        // Upsert: update-by-email first (idempotent), then create when the contact
        // doesn't exist yet (404). Update-first avoids a misleading "already
        // exists" path and makes re-runs cheap and deterministic.
        $audience = static::audienceId();

        $update = static::request(
            'patch',
            "/audiences/{$audience}/contacts/" . rawurlencode($email),
            $payload,
        );

        if ($update && $update->successful()) {
            return $update->json('data.id') ?? $update->json('id');
        }

        if ($update && $update->status() === 404) {
            $create = static::request('post', "/audiences/{$audience}/contacts", $payload);

            if ($create && $create->successful()) {
                return $create->json('data.id') ?? $create->json('id');
            }
        }

        return null;
    }

    /**
     * Sync a user and persist the resulting Resend contact id + sync timestamp
     * back onto their preference row. Safe to call from signup and preference
     * changes; no-ops when Resend is not configured.
     */
    public static function syncUser(User $user, ?array $prefs = null): void
    {
        if (!static::configured() || empty($user->supabase_user_id)) {
            return;
        }

        $prefs ??= EmailPreferenceService::getFor($user->supabase_user_id);

        $contactId = static::syncContact($user, $prefs);

        if ($contactId !== null) {
            EmailPreferenceService::upsert($user->supabase_user_id, [
                'resend_contact_id' => $contactId,
                'synced_at' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Ensure the custom contact properties used as tags exist in Resend. Idempotent:
     * properties that already exist return a conflict which is ignored. Intended for
     * the backfill command / one-off setup.
     */
    public static function ensureProperties(): void
    {
        if (!static::configured()) {
            return;
        }

        foreach (self::PROPERTIES as $key => $type) {
            static::request('post', '/contact-properties', [
                'key' => $key,
                'type' => $type,
            ]);
        }
    }

    /**
     * Send a transactional email through Resend. Returns the email id on success.
     *
     * @param  array<int, string>  $to
     */
    public static function send(array $to, string $subject, string $html, ?string $text = null): ?string
    {
        if (static::apiKey() === '' || empty($to)) {
            return null;
        }

        $payload = [
            'from' => (string) config('services.resend.from'),
            'to' => array_values($to),
            'subject' => $subject,
            'html' => $html,
            'text' => $text ?? strip_tags($html),
        ];

        $response = static::request('post', '/emails', $payload);

        if ($response && $response->successful()) {
            return $response->json('id');
        }

        return null;
    }

    private static function apiKey(): string
    {
        return trim((string) config('services.resend.api_key'));
    }

    private static function audienceId(): string
    {
        return trim((string) config('services.resend.audience_id'));
    }

    /**
     * Monotonic timestamp (seconds) of the last Resend call in this process, used
     * to keep the backfill loop under Resend's 2 requests/second limit.
     */
    private static ?float $lastRequestAt = null;

    /** Minimum spacing between requests (~1.8 req/s, safely under the 2/s cap). */
    private const MIN_REQUEST_INTERVAL = 0.55;

    /** Statuses that are expected control-flow, not failures worth logging. */
    private const QUIET_STATUSES = [404, 409];

    private static function throttle(): void
    {
        if (self::$lastRequestAt !== null) {
            $elapsed = microtime(true) - self::$lastRequestAt;

            if ($elapsed < self::MIN_REQUEST_INTERVAL) {
                usleep((int) ((self::MIN_REQUEST_INTERVAL - $elapsed) * 1_000_000));
            }
        }

        self::$lastRequestAt = microtime(true);
    }

    private static function request(string $method, string $path, array $body = [])
    {
        $url = self::BASE_URL . $path;
        $attempt = 0;

        try {
            while (true) {
                $attempt++;
                static::throttle();

                $request = Http::withToken(static::apiKey())->acceptJson()->timeout(10);

                $response = match ($method) {
                    'get' => $request->get($url),
                    'post' => $request->post($url, $body),
                    'patch' => $request->patch($url, $body),
                    default => null,
                };

                // Resend rate limit: back off and retry a few times before giving up.
                if ($response && $response->status() === 429 && $attempt < 4) {
                    $retryAfter = (float) ($response->header('Retry-After') ?: 1);
                    usleep((int) (max($retryAfter, 1.0) * 1_000_000));
                    continue;
                }

                if ($response && !$response->successful()
                    && !in_array($response->status(), self::QUIET_STATUSES, true)) {
                    Log::warning('ResendContactService: request failed', [
                        'method' => $method,
                        'path' => $path,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }

                return $response;
            }
        } catch (\Throwable $e) {
            Log::warning('ResendContactService: request threw', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
