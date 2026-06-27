<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Username blacklist — banned substrings (profanity, slurs, banned persons,
 * NULL/system words). Stored in Supabase (public.username_blacklist).
 *
 * A username is banned when it CONTAINS any blacklisted word (>= 2 chars), or
 * when it is a single character (length rule — we never store every codepoint).
 * Only attempts are tracked. @see UsernameBlacklistController for the admin page.
 */
class UsernameBlacklistService
{
    private const TABLE = 'username_blacklist';

    /**
     * Returns the reason a username is banned (the matched word, or
     * 'single character'), or null when it's allowed. Records the attempt.
     */
    public static function check(string $username): ?string
    {
        $u = trim($username);
        if ($u === '') {
            return null;
        }

        // Single-character handles are always banned (length rule).
        if (mb_strlen($u) <= 1) {
            return 'single character';
        }

        [$baseUrl, $key] = self::creds();
        if (!$baseUrl) {
            return null;
        }

        try {
            $r = Http::withHeaders(self::headers($key))->timeout(3)
                ->post($baseUrl . '/rest/v1/rpc/record_blacklist_match', ['p_username' => mb_strtolower($u)]);
            if ($r->successful()) {
                $word = $r->json(); // the matched word, or null

                return ($word !== null && $word !== '') ? (string) $word : null;
            }
        } catch (\Throwable $e) {
            Log::warning('UsernameBlacklistService: check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /** Full blacklist for the admin page, most-attempted first. */
    public static function directory(): array
    {
        return self::get(['select' => '*', 'order' => 'attempts.desc,word.asc', 'limit' => '10000']);
    }

    /**
     * Bulk-add pasted words. One per line (or "word,kind"). Lowercased; words
     * shorter than 2 chars are skipped (single-char is handled by the rule).
     * Returns the number of valid words submitted.
     */
    public static function bulkAdd(string $text, string $defaultKind = 'custom'): int
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = array_map('trim', explode(',', $line));
            $word = mb_strtolower(trim($parts[0]));
            if (mb_strlen($word) < 2) {
                continue;
            }
            $rows[$word] = [
                'word' => mb_substr($word, 0, 80),
                'kind' => ($parts[1] ?? '') !== '' ? mb_substr($parts[1], 0, 32) : $defaultKind,
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
                $r = Http::withHeaders(array_merge(self::headers($key), ['Prefer' => 'resolution=ignore-duplicates,return=minimal']))
                    ->post($baseUrl . '/rest/v1/' . self::TABLE . '?on_conflict=word', $chunk);
                if ($r->successful()) {
                    $submitted += count($chunk);
                }
            } catch (\Throwable $e) {
                Log::warning('UsernameBlacklistService: bulkAdd failed', ['error' => $e->getMessage()]);
            }
        }

        return $submitted;
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
            Log::warning('UsernameBlacklistService: get failed', ['error' => $e->getMessage()]);

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
