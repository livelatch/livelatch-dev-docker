<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardAnalyticsService
{
    public function forUser(User $user): array
    {
        $clickRows = $this->fetchProfileClicks($user);
        $activeConnections = $this->activeConnections($user);
        $socialMetrics = $this->fetchSocialMetrics($user, array_keys($activeConnections));

        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        $todayClicks = $clickRows->filter(fn (array $row) => $this->clickedAt($row)?->gte($today))->count();
        $yesterdayClicks = $clickRows
            ->filter(fn (array $row) => ($clickedAt = $this->clickedAt($row)) && $clickedAt->gte($yesterday) && $clickedAt->lt($today))
            ->count();

        $linkRows = $this->linkRows($clickRows);
        $dailyClicks = $this->dailyClicks($clickRows, 14);
        $totalClicks = $clickRows->count();

        return [
            'profile' => [
                'handle' => $user->littlelink_name,
                'url' => $user->littlelink_name ? url('/@' . $user->littlelink_name) : null,
            ],
            'status' => $this->statusCopy($todayClicks, $yesterdayClicks),
            'totalClicks' => $totalClicks,
            'todayClicks' => $todayClicks,
            'yesterdayClicks' => $yesterdayClicks,
            'clickDeltaPercent' => $this->percentChange($todayClicks, $yesterdayClicks),
            'uniqueLinks' => $linkRows->count(),
            'averageClicksPerLink' => $linkRows->count() > 0 ? round($totalClicks / $linkRows->count(), 1) : 0,
            'linkRows' => $linkRows->values()->all(),
            'dailyClicks' => $dailyClicks,
            'peakDayClicks' => max(1, ...array_column($dailyClicks, 'clicks')),
            'activeConnections' => $activeConnections,
            'socialMetrics' => $socialMetrics,
            'source' => [
                'isConfigured' => $this->isConfigured(),
                'clickTable' => config('services.supabase_profile_link_clicks_table'),
                'socialMetricsTable' => config('services.supabase_social_metrics_table'),
            ],
        ];
    }

    private function fetchProfileClicks(User $user): Collection
    {
        if (!$this->isConfigured()) {
            return collect();
        }

        $filters = ['laravel_user_id.eq.' . $user->id];

        if (!empty($user->supabase_user_id)) {
            $filters[] = 'latchid_user_id.eq.' . $user->supabase_user_id;
        }

        if (!empty($user->littlelink_name) && preg_match('/^[A-Za-z0-9_.-]+$/', $user->littlelink_name)) {
            $filters[] = 'profile_handle.eq.' . $user->littlelink_name;
        }

        $response = $this->supabaseRequest(config('services.supabase_profile_link_clicks_table'), [
            'select' => 'link_id,laravel_user_id,latchid_user_id,profile_handle,link_title,link_url,destination_host,clicked_at',
            'or' => '(' . implode(',', $filters) . ')',
            'order' => 'clicked_at.desc',
            'limit' => 5000,
        ]);

        if (!$response || !$response->successful()) {
            $this->logFailure('Dashboard Supabase click lookup failed.', $response);

            return collect();
        }

        $rows = $response->json();

        return is_array($rows) ? collect($rows)->filter(fn ($row) => is_array($row))->values() : collect();
    }

    private function linkRows(Collection $clickRows): Collection
    {
        return $clickRows
            ->groupBy(fn (array $row) => (string) ($row['link_id'] ?? 'unknown'))
            ->map(function (Collection $rows, string $linkId) {
                $first = $rows->first();
                $today = now()->startOfDay();
                $yesterday = now()->subDay()->startOfDay();
                $todayClicks = $rows->filter(fn (array $row) => $this->clickedAt($row)?->gte($today))->count();
                $yesterdayClicks = $rows
                    ->filter(fn (array $row) => ($clickedAt = $this->clickedAt($row)) && $clickedAt->gte($yesterday) && $clickedAt->lt($today))
                    ->count();

                return [
                    'link_id' => $linkId,
                    'title' => $first['link_title'] ?? 'Untitled link',
                    'url' => $first['link_url'] ?? null,
                    'host' => $first['destination_host'] ?? null,
                    'clicks' => $rows->count(),
                    'todayClicks' => $todayClicks,
                    'yesterdayClicks' => $yesterdayClicks,
                    'deltaPercent' => $this->percentChange($todayClicks, $yesterdayClicks),
                    'lastClickedAt' => $this->clickedAt($first)?->diffForHumans(),
                ];
            })
            ->sortByDesc('clicks')
            ->take(12)
            ->values();
    }

    private function dailyClicks(Collection $clickRows, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $clickRows) {
                $day = $start->copy()->addDays($offset);
                $nextDay = $day->copy()->addDay();

                return [
                    'label' => $day->format('M j'),
                    'date' => $day->toDateString(),
                    'clicks' => $clickRows
                        ->filter(fn (array $row) => ($clickedAt = $this->clickedAt($row)) && $clickedAt->gte($day) && $clickedAt->lt($nextDay))
                        ->count(),
                ];
            })
            ->all();
    }

    private function activeConnections(User $user): array
    {
        $connections = SocialAccount::where('user_id', $user->id)
            ->get()
            ->filter(fn (SocialAccount $account) => filled($account->provider_name))
            ->mapWithKeys(fn (SocialAccount $account) => [
                $account->provider_name => [
                    'provider' => $account->provider_name,
                    'label' => $this->providerLabel($account->provider_name),
                    'connected_at' => optional($account->connected_at ?: $account->created_at)->toDateString(),
                ],
            ])
            ->all();

        if (!empty($user->supabase_user_id) && app(LatchIdTikTokAccountService::class)->findForLatchId($user->supabase_user_id)) {
            $connections['tiktok'] = [
                'provider' => 'tiktok',
                'label' => 'TikTok',
                'connected_at' => null,
            ];
        }

        return $connections;
    }

    private function fetchSocialMetrics(User $user, array $providers): array
    {
        if (!$this->isConfigured() || empty($providers)) {
            return [];
        }

        $filters = ['laravel_user_id.eq.' . $user->id];

        if (!empty($user->supabase_user_id)) {
            $filters[] = 'latchid_user_id.eq.' . $user->supabase_user_id;
        }

        $providerFilter = collect($providers)
            ->map(fn ($provider) => preg_replace('/[^A-Za-z0-9_-]/', '', (string) $provider))
            ->filter()
            ->implode(',');

        if ($providerFilter === '') {
            return [];
        }

        $response = $this->supabaseRequest(config('services.supabase_social_metrics_table'), [
            'select' => 'provider,metric_name,metric_value,captured_at',
            'or' => '(' . implode(',', $filters) . ')',
            'provider' => 'in.(' . $providerFilter . ')',
            'order' => 'captured_at.asc',
            'limit' => 1000,
        ]);

        if (!$response || !$response->successful()) {
            return [];
        }

        $rows = $response->json();

        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row) && isset($row['provider'], $row['metric_name'], $row['metric_value'], $row['captured_at']))
            ->groupBy('provider')
            ->map(function (Collection $providerRows, string $provider) {
                $metricName = $this->preferredMetricName($provider, $providerRows);
                $selectedRows = $providerRows->where('metric_name', $metricName);

                $metricRows = $selectedRows
                    ->sortBy('captured_at')
                    ->map(fn (array $row) => [
                        'label' => Carbon::parse($row['captured_at'])->format('M j'),
                        'value' => (int) $row['metric_value'],
                    ])
                    ->values()
                    ->all();

                $first = $metricRows[0]['value'] ?? 0;
                $latest = $metricRows[array_key_last($metricRows)]['value'] ?? 0;

                return [
                    'provider' => $provider,
                    'label' => $this->providerLabel($provider),
                    'metricLabel' => $this->metricLabel($metricName),
                    'latest' => $latest,
                    'deltaPercent' => $this->percentChange($latest, $first),
                    'points' => $metricRows,
                    'peak' => max(1, ...array_column($metricRows, 'value')),
                ];
            })
            ->all();
    }

    private function supabaseRequest(string $table, array $query): ?\Illuminate\Http\Client\Response
    {
        $table = trim($table);

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return null;
        }

        return Http::withHeaders([
            'apikey' => (string) config('services.supabase_service_role_key'),
            'Authorization' => 'Bearer ' . (string) config('services.supabase_service_role_key'),
        ])->acceptJson()->get(rtrim((string) config('services.supabase_url'), '/') . '/rest/v1/' . $table, $query);
    }

    private function isConfigured(): bool
    {
        return filled(config('services.supabase_url')) && filled(config('services.supabase_service_role_key'));
    }

    private function clickedAt(array $row): ?Carbon
    {
        return !empty($row['clicked_at']) ? Carbon::parse($row['clicked_at']) : null;
    }

    private function percentChange(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusCopy(int $todayClicks, int $yesterdayClicks): array
    {
        $delta = $this->percentChange($todayClicks, $yesterdayClicks);

        if ($todayClicks > $yesterdayClicks) {
            return [
                'tone' => 'up',
                'headline' => 'Well done, your links are moving.',
                'message' => $delta === null ? 'You already have clicks today.' : $delta . '% up since yesterday.',
            ];
        }

        if ($todayClicks === $yesterdayClicks) {
            return [
                'tone' => 'steady',
                'headline' => 'You are holding steady.',
                'message' => 'Share one fresh link or update your top card to push today higher.',
            ];
        }

        return [
            'tone' => 'down',
            'headline' => 'Today is quieter, but there is still time.',
            'message' => $delta === null ? 'No clicks yet today.' : abs($delta) . '% down since yesterday. Try posting your profile where your audience is active.',
        ];
    }

    private function providerLabel(string $provider): string
    {
        return [
            'youtube' => 'YouTube',
            'google' => 'Google',
            'discord' => 'Discord',
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
            'threads' => 'Threads',
            'bluesky' => 'Bluesky',
            'x' => 'X',
            'reddit' => 'Reddit',
        ][$provider] ?? ucfirst($provider);
    }

    private function metricLabel(string $metric): string
    {
        return [
            'subscribers' => 'subscribers',
            'followers' => 'followers',
            'members' => 'members',
        ][$metric] ?? str_replace('_', ' ', $metric);
    }

    private function preferredMetricName(string $provider, Collection $rows): string
    {
        $preferred = [
            'youtube' => 'subscribers',
            'google' => 'subscribers',
            'tiktok' => 'followers',
            'instagram' => 'followers',
            'threads' => 'followers',
            'bluesky' => 'followers',
            'x' => 'followers',
            'reddit' => 'followers',
            'discord' => 'members',
        ][$provider] ?? null;

        if ($preferred && $rows->contains('metric_name', $preferred)) {
            return $preferred;
        }

        return (string) ($rows->first()['metric_name'] ?? 'followers');
    }

    private function logFailure(string $message, ?\Illuminate\Http\Client\Response $response): void
    {
        Log::warning($message, [
            'status' => $response?->status(),
            'body' => $response?->body(),
        ]);
    }
}
