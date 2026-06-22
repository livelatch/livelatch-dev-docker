<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors a user's additive role keys into Supabase (public.profiles.roles) so
 * non-Laravel consumers (Encore, the viewer portal, edge functions) can gate on
 * roles natively without calling back into Laravel or querying Railway MySQL.
 *
 * Railway MySQL (roles + role_user) remains the source of truth: this is a
 * one-way mirror pushed from App\Models\User whenever role membership changes.
 * Twin of BillingProfileService — same service-role REST access, same
 * best-effort/no-op-on-failure behaviour so a Supabase hiccup never breaks an
 * admin save or a Stripe webhook.
 */
class RoleProfileService
{
    private const TABLE = 'profiles';

    /**
     * Upsert profiles.roles for a LatchID user (auth.users id). Returns true on
     * a successful write. No-ops (returns false) when the id or Supabase
     * credentials are missing.
     *
     * @param  string[]  $roleKeys
     */
    public static function setRoles(?string $supabaseUserId, array $roleKeys): bool
    {
        if (!$supabaseUserId) {
            return false;
        }

        $roleKeys = array_values(array_unique(array_filter($roleKeys)));
        sort($roleKeys);

        $response = static::request(
            'patch',
            ['id' => 'eq.' . $supabaseUserId],
            ['roles' => $roleKeys, 'updated_at' => now()->toIso8601String()],
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
            Log::warning('RoleProfileService: Supabase credentials are not configured.');

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
                'patch' => $request->patch($url, $body ?? []),
                default => null,
            };

            if ($response && !$response->successful()) {
                Log::warning('RoleProfileService: Supabase request failed', [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('RoleProfileService: Supabase request threw', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
