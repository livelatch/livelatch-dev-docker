<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads/writes Livelatch's own social accounts shown on /studio/socials, stored
 * in Supabase (public.livelatch_socials). Global platform config, not per-user.
 * All access is server-side via the service-role key (bypasses RLS).
 */
class LivelatchSocialService
{
    private const TABLE = 'livelatch_socials';

    /**
     * Canonical platforms Livelatch supports, with display metadata and the
     * embed strategy used on the socials page. Order here is the default order.
     *
     * embed: youtube | tiktok | instagram | twitter | threads | reddit | none
     */
    public static function platforms(): array
    {
        return [
            'youtube' => ['name' => 'YouTube', 'icon' => 'bi bi-youtube', 'embed' => 'youtube'],
            'tiktok' => ['name' => 'TikTok', 'icon' => 'bi bi-tiktok', 'embed' => 'tiktok'],
            'instagram' => ['name' => 'Instagram', 'icon' => 'bi bi-instagram', 'embed' => 'instagram'],
            'x' => ['name' => 'X', 'icon' => 'bi bi-twitter-x', 'embed' => 'twitter'],
            'threads' => ['name' => 'Threads', 'icon' => 'bi bi-threads', 'embed' => 'threads'],
            'reddit' => ['name' => 'Reddit', 'icon' => 'bi bi-reddit', 'embed' => 'reddit'],
            'bluesky' => ['name' => 'Bluesky', 'icon' => 'bi bi-clouds-fill', 'embed' => 'none'],
            'discord' => ['name' => 'Discord', 'icon' => 'bi bi-discord', 'embed' => 'none'],
        ];
    }

    /**
     * All stored rows keyed by platform. Missing platforms are absent.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function all(): array
    {
        $response = static::request('get', [
            'select' => 'platform,handle,profile_url,featured_post_url,display_order,is_active',
            'order' => 'display_order.asc',
        ]);

        if (!$response || !$response->successful()) {
            return [];
        }

        $rows = $response->json();
        if (!is_array($rows)) {
            return [];
        }

        $byPlatform = [];
        foreach ($rows as $row) {
            if (!empty($row['platform'])) {
                $byPlatform[$row['platform']] = $row;
            }
        }

        return $byPlatform;
    }

    /**
     * Active socials merged with platform metadata, ready for the socials page,
     * ordered by display_order then the canonical order.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function activeForDisplay(): array
    {
        $stored = static::all();
        $platforms = static::platforms();
        $out = [];

        foreach ($platforms as $key => $meta) {
            $row = $stored[$key] ?? null;
            if (!$row || empty($row['is_active']) || empty($row['profile_url'])) {
                continue;
            }

            $out[] = [
                'platform' => $key,
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'embed' => $meta['embed'],
                'handle' => $row['handle'] ?? '',
                'profile_url' => $row['profile_url'],
                'featured_post_url' => $row['featured_post_url'] ?? '',
                'display_order' => (int) ($row['display_order'] ?? 0),
            ];
        }

        usort($out, fn ($a, $b) => $a['display_order'] <=> $b['display_order']);

        return $out;
    }

    /**
     * Replace the stored config with the supplied per-platform rows. Each entry
     * is an upsert keyed on platform. Unknown platforms are ignored.
     *
     * @param array<string,array<string,mixed>> $rows
     */
    public static function saveAll(array $rows): bool
    {
        $platforms = static::platforms();
        $payload = [];

        foreach ($rows as $platform => $attrs) {
            if (!isset($platforms[$platform])) {
                continue;
            }

            $profileUrl = static::cleanUrl($attrs['profile_url'] ?? null);
            $featuredUrl = static::cleanUrl($attrs['featured_post_url'] ?? null);

            $payload[] = [
                'platform' => $platform,
                'handle' => trim((string) ($attrs['handle'] ?? '')) ?: null,
                'profile_url' => $profileUrl,
                'featured_post_url' => $featuredUrl,
                'display_order' => (int) ($attrs['display_order'] ?? 0),
                // Only active when it actually has a profile link to point at.
                'is_active' => !empty($attrs['is_active']) && $profileUrl !== null,
                'updated_at' => now()->toIso8601String(),
            ];
        }

        if (empty($payload)) {
            return true;
        }

        $response = static::request(
            'post',
            ['on_conflict' => 'platform'],
            $payload,
            ['Prefer' => 'resolution=merge-duplicates,return=minimal'],
        );

        return (bool) ($response && $response->successful());
    }

    private static function cleanUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private static function request(
        string $method,
        array $query = [],
        ?array $body = null,
        array $extraHeaders = [],
    ) {
        $baseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceKey = (string) config('services.supabase_service_role_key');

        if ($baseUrl === '' || $serviceKey === '') {
            Log::warning('LivelatchSocialService: Supabase credentials are not configured.');

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
                Log::warning('LivelatchSocialService: Supabase request failed', [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('LivelatchSocialService: Supabase request error', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
