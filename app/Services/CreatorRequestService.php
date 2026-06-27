<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * "Creator requests" — when a visitor hits a non-existent /@handle we let them
 * signal that the creator should join Livelatch. Stored in Supabase
 * (public.creator_requests) as a demand signal.
 *
 * Uniqueness is a salted hash of (IP + handle), never the raw IP — same privacy
 * stance as livelatch_profile_link_clicks.ip_hash. The number is a soft demand
 * signal, not a hard count: shared networks collapse together and VPNs can
 * inflate, but it's enough to spot real pull for a handle. Repeat hits bump an
 * `attempts` counter instead of creating new rows.
 */
class CreatorRequestService
{
    /** Normalise a typed @handle to its stored form (lowercase, handle-safe). */
    public static function normalizeHandle(string $handle): string
    {
        $handle = ltrim(trim($handle), '@');
        $handle = preg_replace('/[^A-Za-z0-9._-]/', '', $handle) ?? '';

        return strtolower(mb_substr($handle, 0, 80));
    }

    /**
     * Record a request for a non-existent creator. Dedupes per (handle, salted
     * IP hash). Returns true on a successful write.
     */
    public static function record(string $handle, Request $request, ?string $email = null): bool
    {
        $handle = self::normalizeHandle($handle);
        if ($handle === '') {
            return false;
        }

        $fingerprint = hash_hmac('sha256', $request->ip() . '|' . $handle, (string) config('app.key'));

        $response = self::rpc('record_creator_request', [
            'p_handle'      => $handle,
            'p_fingerprint' => $fingerprint,
            'p_email'       => $email ?: null,
            'p_user_agent'  => mb_substr((string) $request->userAgent(), 0, 400),
            'p_referer'     => mb_substr((string) $request->headers->get('referer', ''), 0, 400),
        ]);

        return (bool) ($response && $response->successful());
    }

    /**
     * Aggregated demand totals (one row per handle) for the admin page, busiest
     * first. Returns [] when Supabase is unconfigured or unreachable.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function totals(): array
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $key = (string) config('services.supabase_service_role_key');
        if ($baseUrl === '' || $key === '') {
            return [];
        }

        try {
            $response = Http::withHeaders(self::headers($key))
                ->get($baseUrl . '/rest/v1/creator_request_totals', ['order' => 'unique_count.desc,last_seen.desc']);

            return $response->successful() ? ($response->json() ?: []) : [];
        } catch (\Throwable $e) {
            Log::warning('CreatorRequestService: totals read failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private static function rpc(string $fn, array $params)
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $key = (string) config('services.supabase_service_role_key');
        if ($baseUrl === '' || $key === '') {
            Log::warning('CreatorRequestService: Supabase is not configured.');

            return null;
        }

        try {
            return Http::withHeaders(self::headers($key))->post($baseUrl . '/rest/v1/rpc/' . $fn, $params);
        } catch (\Throwable $e) {
            Log::warning('CreatorRequestService: rpc threw', ['fn' => $fn, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private static function headers(string $key): array
    {
        return [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ];
    }
}
