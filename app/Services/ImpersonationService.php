<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Impersonation Mitigation — a LatchID sub-product.
 *
 * Directory of high-value creator/brand names + structural reserved words,
 * stored in Supabase (public.impersonation_names), with attempt counters
 * (people trying to sign up as, or visit /@, a protected name). Enforcement
 * (the `-unverified` suffix + verification) reads `isReserved()`.
 */
class ImpersonationService
{
    private const TABLE = 'impersonation_names';

    /** Normalise to handle form (lowercase, a-z0-9), matching generated handles. */
    public static function normalizeHandle(string $handle): string
    {
        return mb_substr(preg_replace('/[^a-z0-9]+/', '', mb_strtolower(trim($handle))) ?? '', 0, 80);
    }

    /** Whether a handle is in the protected directory (for enforcement). */
    public static function isReserved(string $handle): bool
    {
        $handle = self::normalizeHandle($handle);
        if ($handle === '') {
            return false;
        }

        return !empty(self::get(['handle' => 'eq.' . $handle, 'select' => 'id', 'limit' => '1']));
    }

    /** Full directory for the admin page. */
    public static function directory(): array
    {
        return self::get(['select' => '*', 'order' => 'visit_attempts.desc,signup_attempts.desc,name.asc', 'limit' => '5000']);
    }

    /** Fire-and-forget increment of a 'visit' or 'signup' attempt (no-op if not reserved). */
    public static function recordAttempt(string $handle, string $kind): void
    {
        $handle = self::normalizeHandle($handle);
        if ($handle === '') {
            return;
        }

        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return;
        }

        try {
            // Short timeout — this runs on the public 404 path, never block it.
            Http::withHeaders(self::headers($key))->timeout(2)->post($baseUrl . '/rest/v1/rpc/record_impersonation_attempt', [
                'p_handle' => $handle,
                'p_kind'   => $kind === 'signup' ? 'signup' : 'visit',
            ]);
        } catch (\Throwable $e) {
            Log::warning('ImpersonationService: recordAttempt failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk-add pasted names. Each line is a name ("MrBeast") or "name,platform".
     * Handle is derived from the name. Existing handles are kept (counters not
     * reset). Returns the number of valid lines submitted.
     */
    public static function bulkAdd(string $text, string $defaultPlatform = 'manual'): int
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = array_map('trim', explode(',', $line));
            $name = $parts[0];
            $handle = self::normalizeHandle($name);
            if ($handle === '' || $name === '') {
                continue;
            }
            $rows[$handle] = [
                'name'     => mb_substr($name, 0, 120),
                'handle'   => $handle,
                'platform' => ($parts[1] ?? '') !== '' ? mb_substr($parts[1], 0, 32) : $defaultPlatform,
            ];
        }

        if (empty($rows)) {
            return 0;
        }

        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return 0;
        }

        $submitted = 0;
        foreach (array_chunk(array_values($rows), 500) as $chunk) {
            try {
                // Upsert, ignoring existing handles so counters survive.
                $r = Http::withHeaders(array_merge(self::headers($key), ['Prefer' => 'resolution=ignore-duplicates,return=minimal']))
                    ->post($baseUrl . '/rest/v1/' . self::TABLE . '?on_conflict=handle', $chunk);
                if ($r->successful()) {
                    $submitted += count($chunk);
                }
            } catch (\Throwable $e) {
                Log::warning('ImpersonationService: bulkAdd failed', ['error' => $e->getMessage()]);
            }
        }

        return $submitted;
    }

    /**
     * For each handle, how many live accounts use it or its `-unverified` form
     * (the impersonation signal). One MySQL query. Returns [handle => count].
     *
     * @param  string[]  $handles
     * @return array<string,int>
     */
    public static function similarAccountCounts(array $handles): array
    {
        $handles = array_values(array_filter(array_map('strtolower', $handles)));
        if (empty($handles)) {
            return [];
        }

        $patterns = [];
        foreach ($handles as $h) {
            $patterns[] = $h;
            $patterns[] = $h . '-unverified';
        }

        $counts = [];
        User::whereIn('littlelink_name', $patterns)->pluck('littlelink_name')->each(function ($ln) use (&$counts) {
            $ln = strtolower((string) $ln);
            $base = str_ends_with($ln, '-unverified') ? substr($ln, 0, -strlen('-unverified')) : $ln;
            $counts[$base] = ($counts[$base] ?? 0) + 1;
        });

        return $counts;
    }

    /* --------------------------------------------------------------------- */

    private static function get(array $query): array
    {
        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return [];
        }
        try {
            $r = Http::withHeaders(self::headers($key))->get($baseUrl . '/rest/v1/' . self::TABLE, $query);

            return $r->successful() ? ($r->json() ?: []) : [];
        } catch (\Throwable $e) {
            Log::warning('ImpersonationService: get failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private static function creds(): array
    {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $key = (string) config('services.supabase_service_role_key');

        return ($baseUrl !== '' && $key !== '') ? [$baseUrl, $key] : [null, null];
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
