# Dashboard Analytics Data

The Studio dashboard is rendered by Livewire:

```text
resources/views/panel/index.blade.php
-> app/Http/Livewire/DashboardAnalytics.php
-> app/Services/DashboardAnalyticsService.php
-> resources/views/livewire/dashboard-analytics.blade.php
```

## Current Source Of Truth

Dashboard click analytics now come from [Supabase](https://supabase.com/), not the old LinkStack `links.click_number` counters and not temporary sample data.

The primary table is configured with:

```env
SUPABASE_PROFILE_LINK_CLICKS_TABLE=livelatch_profile_link_clicks
```

Laravel reads that table through Supabase REST using:

```env
LATCHID_SUPABASE_URL=
LATCHID_SERVICE_ROLE_KEY=
```

The service role key is only used server-side in Laravel. It must never be passed to Blade, JavaScript, or browser requests.

## Click Metrics

`DashboardAnalyticsService` reads recent rows from the click table and filters by the current user:

```text
laravel_user_id = current Laravel user id
or
latchid_user_id = current user's LatchID Supabase user id
or
profile_handle = current user's public handle
```

The dashboard then calculates:

- total clicks
- clicks today
- yesterday comparison
- percentage change since yesterday
- clicked-link count
- average clicks per clicked link
- clicks per link
- last clicked time per link
- a 14-day click chart

Link rows are grouped from Supabase click data. This means newly added profile links appear in dashboard analytics automatically once a visitor clicks them and the redirect flow inserts a new Supabase click row.

## Redirect Links

The dashboard uses the stored destination URL from the Supabase click row for the `Open` action. This intentionally opens the destination directly instead of routing through `/going/{id}` so dashboard owner clicks do not inflate public link analytics.

## Creator Connections

The dashboard also shows active creator connections from the local `social_accounts` table and the LatchID TikTok account lookup.

Supported labels include:

- YouTube / Google
- TikTok
- Discord
- Instagram
- Threads
- Bluesky
- X
- Reddit

If a connection exists but no metric snapshots exist yet, the dashboard shows the account as connected and waits for history before drawing a chart.

## Social Metric Snapshots

Follower/subscriber graphs are optional and data-driven. They are configured with:

```env
SUPABASE_SOCIAL_METRICS_TABLE=livelatch_social_metric_snapshots
```

Recommended Supabase table shape:

```sql
create table if not exists public.livelatch_social_metric_snapshots (
    id uuid primary key default gen_random_uuid(),
    provider text not null,
    metric_name text not null,
    metric_value bigint not null,
    laravel_user_id bigint,
    latchid_user_id uuid,
    captured_at timestamptz not null default now(),
    inserted_at timestamptz not null default now()
);

create index if not exists livelatch_social_metric_snapshots_laravel_user_idx
    on public.livelatch_social_metric_snapshots (laravel_user_id, provider, captured_at);

create index if not exists livelatch_social_metric_snapshots_latchid_user_idx
    on public.livelatch_social_metric_snapshots (latchid_user_id, provider, captured_at);
```

Expected metric names:

```text
youtube: subscribers
tiktok: followers
discord: members
instagram: followers
threads: followers
bluesky: followers
x: followers
reddit: followers
```

Collectors for these snapshots can be added later through scheduled Laravel jobs, Supabase Edge Functions, or another backend worker. The dashboard does not invent follower or subscriber values.

## Failure Behavior

Supabase lookups are best-effort. If config is missing, the table is unavailable, or the REST request fails, the dashboard renders empty live states instead of fake data. This keeps `/dashboard` available while making it obvious that analytics data has not loaded.
