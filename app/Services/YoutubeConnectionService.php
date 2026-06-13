<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class YoutubeConnectionService
{
    private const YOUTUBE_API_BASE = 'https://www.googleapis.com/youtube/v3';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function connectionFor(User $user): ?SocialAccount
    {
        return $user->socialAccounts()
            ->where('provider_name', 'youtube')
            ->latest('connected_at')
            ->first();
    }

    public function listRecentVideos(User $user, int $maxResults = 10): array
    {
        $connection = $this->requireConnection($user);

        return $this->authorizedGet($connection, '/search', [
            'part' => 'snippet',
            'forMine' => 'true',
            'type' => 'video',
            'order' => 'date',
            'maxResults' => max(1, min($maxResults, 25)),
        ]);
    }

    public function listLiveBroadcasts(User $user, int $maxResults = 10): array
    {
        $connection = $this->requireConnection($user);

        return $this->authorizedGet($connection, '/liveBroadcasts', [
            'part' => 'id,snippet,status,contentDetails',
            'mine' => 'true',
            'maxResults' => max(1, min($maxResults, 25)),
        ]);
    }

    private function requireConnection(User $user): SocialAccount
    {
        $connection = $this->connectionFor($user);

        if (!$connection || empty($connection->access_token)) {
            throw ValidationException::withMessages([
                'youtube' => 'Connect YouTube before requesting YouTube API data.',
            ]);
        }

        return $connection;
    }

    private function authorizedGet(SocialAccount $connection, string $path, array $query): array
    {
        $connection = $this->refreshIfNeeded($connection);

        $response = Http::withToken($connection->access_token)
            ->acceptJson()
            ->get(self::YOUTUBE_API_BASE . $path, $query);

        if ($response->status() === 401 && !empty($connection->refresh_token)) {
            $connection = $this->refresh($connection);

            $response = Http::withToken($connection->access_token)
                ->acceptJson()
                ->get(self::YOUTUBE_API_BASE . $path, $query);
        }

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'youtube' => 'YouTube API request failed with status ' . $response->status() . '.',
            ]);
        }

        return $response->json() ?? [];
    }

    private function refreshIfNeeded(SocialAccount $connection): SocialAccount
    {
        if (
            empty($connection->refresh_token) ||
            empty($connection->token_expires_at) ||
            (
                $connection->token_expires_at->isFuture() &&
                $connection->token_expires_at->diffInMinutes(now()) > 5
            )
        ) {
            return $connection;
        }

        return $this->refresh($connection);
    }

    private function refresh(SocialAccount $connection): SocialAccount
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw ValidationException::withMessages([
                'youtube' => 'Google OAuth client credentials are not configured in Laravel.',
            ]);
        }

        $response = Http::asForm()->post(self::GOOGLE_TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'youtube' => 'Could not refresh the YouTube access token.',
            ]);
        }

        $payload = $response->json() ?? [];

        $connection->access_token = $payload['access_token'] ?? $connection->access_token;

        if (!empty($payload['expires_in'])) {
            $connection->token_expires_at = now()->addSeconds((int) $payload['expires_in']);
        }

        if (!empty($payload['scope'])) {
            $connection->scopes = array_values(array_filter(preg_split('/\s+/', $payload['scope']) ?: []));
        }

        $connection->save();

        return $connection->fresh();
    }
}
