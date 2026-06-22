<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors a user's billing plan into Supabase (public.profiles.plan_key) so
 * non-Laravel consumers (Encore, the viewer portal, edge functions, the
 * Latch On free/Pro gate) can read plan status natively without calling back
 * into Laravel or querying the Railway MySQL database directly.
 *
 * Laravel + Stripe remain the source of truth: this is a one-way mirror written
 * from the Stripe webhook handler (and the reconcile command). The MySQL
 * user_billing.plan_key is the operative value; profiles.plan_key is a read
 * replica of it.
 *
 * Access uses the Supabase REST API with the service-role key (bypasses RLS),
 * mirroring EmailPreferenceService. All calls are best-effort and degrade to a
 * no-op when Supabase is unavailable, so a Supabase hiccup never fails a Stripe
 * webhook (which Laravel must still ACK with a 2xx) — the reconcile command
 * catches any drift later.
 */
class BillingProfileService
{
    private const TABLE = 'profiles';

    /**
     * Normalise an arbitrary plan value to the allowed set ('free' | 'pro').
     */
    public static function normalisePlan(?string $planKey): string
    {
        return $planKey === 'pro' ? 'pro' : 'free';
    }

    /**
     * Upsert profiles.plan_key for a LatchID user (auth.users id). Returns true
     * on a successful write. No-ops (returns false) when the id or Supabase
     * credentials are missing.
     */
    public static function setPlan(?string $supabaseUserId, ?string $planKey): bool
    {
        if (!$supabaseUserId) {
            return false;
        }

        $plan = static::normalisePlan($planKey);

        // The profiles row already exists (created at signup, FK to auth.users),
        // so this is a targeted update on the primary key rather than an insert.
        $response = static::request(
            'patch',
            ['id' => 'eq.' . $supabaseUserId],
            ['plan_key' => $plan, 'updated_at' => now()->toIso8601String()],
            ['Prefer' => 'return=minimal'],
        );

        return (bool) ($response && $response->successful());
    }

    /**
     * Perform a Supabase REST request against the profiles table.
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
            Log::warning('BillingProfileService: Supabase credentials are not configured.');

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
                'patch' => $request->patch($url, $body ?? []),
                default => null,
            };

            if ($response && !$response->successful()) {
                Log::warning('BillingProfileService: Supabase request failed', [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('BillingProfileService: Supabase request threw', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
