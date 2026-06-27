<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client for the platform-agnostic LatchDeck API (served by Encore).
 *
 * Livelatch never touches the LatchDeck Supabase tables directly — Encore is the
 * authority. This service is a thin HTTP wrapper that authenticates with the
 * LatchDeck *service* API key (config services.latchdeck.api_key) and speaks the
 * Encore contract documented in docs/latchdeck/latchdeck-api.md.
 *
 * All methods fail soft: on a transport error or non-2xx response they log and
 * return null (or a safe default) so the studio shell never breaks.
 */
class LatchDeckService
{
    /**
     * Short-lived read cache. The studio shell renders the same Encore reads
     * (access status, card list) on every page load, and each is a live HTTP
     * round-trip to Encore — the main source of slow studio pages. We cache the
     * read results per user for a few seconds so repeat renders + hover-prefetch
     * are cheap, and bust the user's cache whenever they mutate (create/update/
     * publish/unpublish a card, request access, or sync tier). Only *successful*
     * responses are cached, so a transient Encore failure is never sticky.
     */
    private const CACHE_TTL = 45;

    /**
     * Access + entitlement state for a user. Shape:
     *  status: not_applied|pending_review|active|denied_waitlist|restricted|revoked
     *  tier: free|pro|sdk, capabilities: {...}, tutorial_seen, first_card_created, restriction
     */
    public function accessStatus(string $latchIdUserId): ?array
    {
        $key = $this->cacheKey('access', $latchIdUserId);
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $response = $this->request('get', '/access-status/' . rawurlencode($latchIdUserId));
        $data = ($response && $response->successful()) ? $response->json() : null;

        if ($data !== null) {
            Cache::put($key, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Submit an access request. Creates/refreshes the creator as pending_review.
     *
     * @param array{email:string,display_name:string,platform:string,community_context?:string,reason?:string,tier?:string} $payload
     */
    public function requestAccess(string $latchIdUserId, array $payload): ?array
    {
        $body = array_merge($payload, ['latchid_user_id' => $latchIdUserId]);

        $response = $this->request('post', '/applications', $body);

        if ($response && $response->successful()) {
            $this->forgetUser($latchIdUserId);

            return $response->json();
        }

        return null;
    }

    /** All of a user's cards (drafts + published), newest first. */
    public function listCards(string $latchIdUserId): array
    {
        $key = $this->cacheKey('cards', $latchIdUserId);
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $response = $this->request('get', '/mvp/cards/' . rawurlencode($latchIdUserId));

        if (!$response || !$response->successful()) {
            return [];
        }

        $cards = $response->json()['cards_mvp'] ?? [];
        Cache::put($key, $cards, self::CACHE_TTL);

        return $cards;
    }

    /**
     * Create a draft card.
     *
     * @param array{name_mvp:string,short_description_mvp:string,long_description_mvp?:string,rarity_mvp:string,creator_name_mvp:string,image_url_mvp?:string,background_color_mvp?:string} $card
     */
    public function createCard(string $latchIdUserId, array $card): ?array
    {
        $body = array_merge($card, ['latchid_user_id' => $latchIdUserId]);

        $response = $this->request('post', '/mvp/cards', $body);

        if ($response && $response->successful()) {
            $this->forgetUser($latchIdUserId);

            return $response->json();
        }

        return null;
    }

    /** Update an owned card (draft editor). */
    public function updateCard(string $latchIdUserId, string $cardId, array $fields): ?array
    {
        $body = array_merge($fields, ['latchid_user_id' => $latchIdUserId]);

        $response = $this->request('patch', '/mvp/cards/' . rawurlencode($cardId), $body);

        if ($response && $response->successful()) {
            $this->forgetUser($latchIdUserId);

            return $response->json();
        }

        return null;
    }

    /**
     * Publish a card. Returns [card|null, errorMessage|null]; errorMessage carries
     * Encore's reason (e.g. not approved, tier limit) for surfacing in the UI.
     */
    public function publishCard(string $latchIdUserId, string $cardId): array
    {
        $response = $this->request('post', '/mvp/cards/' . rawurlencode($cardId) . '/publish', [
            'latchid_user_id' => $latchIdUserId,
        ]);

        if ($response && $response->successful()) {
            $this->forgetUser($latchIdUserId);

            return [$response->json(), null];
        }

        return [null, $this->errorMessage($response)];
    }

    /** Retract a published card back to draft. */
    public function unpublishCard(string $latchIdUserId, string $cardId): ?array
    {
        $response = $this->request('post', '/mvp/cards/' . rawurlencode($cardId) . '/unpublish', [
            'latchid_user_id' => $latchIdUserId,
        ]);

        if ($response && $response->successful()) {
            $this->forgetUser($latchIdUserId);

            return $response->json();
        }

        return null;
    }

    /** Push the user's entitlement tier (free/pro) into LatchDeck. */
    public function syncTier(string $latchIdUserId, string $tier): bool
    {
        $response = $this->request('post', '/creators/' . rawurlencode($latchIdUserId) . '/tier', [
            'tier' => $tier,
        ]);

        $ok = (bool) ($response && $response->successful());
        if ($ok) {
            $this->forgetUser($latchIdUserId);
        }

        return $ok;
    }

    private function errorMessage($response): string
    {
        if (!$response) {
            return 'LatchDeck is unavailable. Please try again.';
        }

        // Encore returns { code, message, details } on error.
        return $response->json('message') ?: 'Could not complete the request.';
    }

    /** Cache key for a read bucket ('access'|'cards') scoped to a LatchID user. */
    private function cacheKey(string $bucket, string $latchIdUserId): string
    {
        return 'latchdeck:' . $bucket . ':' . sha1($latchIdUserId);
    }

    /** Invalidate a user's cached reads after a mutation. */
    private function forgetUser(string $latchIdUserId): void
    {
        Cache::forget($this->cacheKey('access', $latchIdUserId));
        Cache::forget($this->cacheKey('cards', $latchIdUserId));
    }

    /**
     * Perform a LatchDeck API request. Returns the response, or null when the
     * client is not configured or the request throws.
     */
    private function request(string $method, string $path, array $body = [])
    {
        $baseUrl = rtrim((string) config('services.latchdeck.api_url'), '/');
        $apiKey = (string) config('services.latchdeck.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            Log::warning('LatchDeckService: LatchDeck API is not configured.');

            return null;
        }

        try {
            $request = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(10);

            $url = $baseUrl . $path;

            $response = match ($method) {
                'get' => $request->get($url),
                'post' => $request->post($url, $body),
                'patch' => $request->patch($url, $body),
                default => null,
            };

            if ($response && !$response->successful()) {
                Log::warning('LatchDeckService: request failed', [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::warning('LatchDeckService: request threw', [
                'method' => $method,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
