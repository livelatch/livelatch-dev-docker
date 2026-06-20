<?php

namespace App\Services;

use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PostHog\PostHog;

class SupabaseProfileLinkClickService
{
    public function record(Link $link, Request $request): void
    {
        $user = User::select('id', 'littlelink_name', 'supabase_user_id')->find($link->user_id);
        $event = $this->buildEvent($link, $request, $user);

        $this->recordInPostHog($event);
        $this->recordInSupabase($link, $event);
    }

    private function recordInSupabase(Link $link, array $event): void
    {
        $supabaseUrl = rtrim((string) config('services.supabase_url'), '/');
        $serviceRoleKey = (string) config('services.supabase_service_role_key');
        $table = trim((string) config('services.supabase_profile_link_clicks_table'));

        if ($supabaseUrl === '' || $serviceRoleKey === '' || $table === '') {
            return;
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            Log::warning('Supabase profile link click capture skipped because the table name is invalid.', [
                'table' => $table,
            ]);

            return;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $serviceRoleKey,
                'Authorization' => 'Bearer ' . $serviceRoleKey,
                'Prefer' => 'return=minimal',
            ])->acceptJson()->post($supabaseUrl . '/rest/v1/' . $table, [
                'link_id' => $event['link_id'],
                'laravel_user_id' => $event['laravel_user_id'],
                'latchid_user_id' => $event['latchid_user_id'],
                'profile_handle' => $event['profile_handle'],
                'link_title' => $event['link_title'],
                'link_url' => $event['link_url'],
                'destination_host' => $event['destination_host'],
                'referer' => $event['referer'],
                'user_agent' => $event['user_agent'],
                'ip_hash' => $event['ip_hash'],
                'clicked_at' => $event['clicked_at'],
            ]);

            if (!$response->successful()) {
                Log::warning('Supabase profile link click capture failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'link_id' => $link->id,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Supabase profile link click capture threw an exception.', [
                'message' => $exception->getMessage(),
                'link_id' => $link->id,
            ]);
        }
    }

    private function recordInPostHog(array $event): void
    {
        if (!config('services.posthog.key')) {
            return;
        }

        try {
            PostHog::capture([
                'distinctId' => $this->postHogDistinctId($event),
                'event' => 'profile_link_clicked',
                'properties' => [
                    'link_id' => $event['link_id'],
                    'laravel_user_id' => $event['laravel_user_id'],
                    'latchid_user_id' => $event['latchid_user_id'],
                    'profile_handle' => $event['profile_handle'],
                    'link_title' => $event['link_title'],
                    'link_url' => $event['link_url'],
                    'destination_host' => $event['destination_host'],
                    'referer' => $event['referer'],
                    'ip_hash' => $event['ip_hash'],
                    'clicked_at' => $event['clicked_at'],
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::warning('PostHog profile link click capture threw an exception.', [
                'message' => $exception->getMessage(),
                'link_id' => $event['link_id'],
            ]);
        }
    }

    private function buildEvent(Link $link, Request $request, ?User $user): array
    {
        return [
            'link_id' => (string) $link->id,
            'laravel_user_id' => $link->user_id,
            'latchid_user_id' => $user?->supabase_user_id,
            'profile_handle' => $user?->littlelink_name,
            'link_title' => $link->title,
            'link_url' => $link->link,
            'destination_host' => $this->hostFromUrl((string) $link->link),
            'referer' => $this->redactAuthParams(Str::limit((string) $request->headers->get('referer'), 2048, '')),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'ip_hash' => $this->hashIp($request->ip()),
            'clicked_at' => now()->toISOString(),
        ];
    }

    private function postHogDistinctId(array $event): string
    {
        if (!empty($event['latchid_user_id'])) {
            return 'latchid:' . $event['latchid_user_id'];
        }

        if (!empty($event['laravel_user_id'])) {
            return 'laravel-user:' . $event['laravel_user_id'];
        }

        return 'anonymous:' . ($event['ip_hash'] ?: 'unknown');
    }

    /**
     * Redact auth material from a URL before it is sent to PostHog. Browsers
     * strip URL fragments from the Referer header, but query-string tokens can
     * still appear; never catalogue a usable token.
     */
    private function redactAuthParams(string $url): string
    {
        if ($url === '') {
            return $url;
        }

        return (string) preg_replace(
            '/([?&](?:access_token|refresh_token|id_token|provider_token|token|code|otp|state)=)[^&#]*/i',
            '$1redacted',
            $url
        );
    }

    private function hostFromUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? Str::limit($host, 255, '') : null;
    }

    private function hashIp(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        return hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}
