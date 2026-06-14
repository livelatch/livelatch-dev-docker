# Profile Link Click Capture

Livelatch captures public profile link presses through the existing LinkStack redirect route:

```text
GET /going/{id}
-> App\Http\Controllers\UserController::clickNumber()
-> App\Services\SupabaseProfileLinkClickService
-> Supabase REST insert
```

This keeps analytics capture server-side. The Supabase service role key is never exposed to Blade or frontend JavaScript.

## Runtime Behavior

When someone clicks a profile link, Laravel still performs the existing LinkStack behavior:

- find the local `links` record
- increment `links.click_number`
- redirect the visitor to the destination URL

Livelatch now also attempts a best-effort Supabase insert before redirecting. If Supabase is not configured, unavailable, or returns an error, the redirect still continues and the failure is logged.

The same click is also sent to [PostHog](https://posthog.com/) as a server-side event when `POSTHOG_API_KEY` is configured.

## Laravel Configuration

The service reads:

```php
config('services.supabase_url')
config('services.supabase_service_role_key')
config('services.supabase_profile_link_clicks_table')
```

The matching environment variables are:

```env
LATCHID_SUPABASE_URL=
LATCHID_SERVICE_ROLE_KEY=
SUPABASE_PROFILE_LINK_CLICKS_TABLE=livelatch_profile_link_clicks
```

`LATCHID_SUPABASE_URL` falls back to `SUPABASE_URL`, and `LATCHID_SERVICE_ROLE_KEY` falls back to `SUPABASE_SERVICE_ROLE_KEY`.

## Suggested Supabase Table

Create this table in the Supabase `public` schema before relying on the capture flow:

```sql
create table if not exists public.livelatch_profile_link_clicks (
  id uuid primary key default gen_random_uuid(),
  link_id text not null,
  laravel_user_id bigint,
  latchid_user_id uuid,
  profile_handle text,
  link_title text,
  link_url text,
  destination_host text,
  referer text,
  user_agent text,
  ip_hash text,
  clicked_at timestamptz not null default now(),
  inserted_at timestamptz not null default now()
);

create index if not exists livelatch_profile_link_clicks_link_id_idx
  on public.livelatch_profile_link_clicks (link_id);

create index if not exists livelatch_profile_link_clicks_laravel_user_id_idx
  on public.livelatch_profile_link_clicks (laravel_user_id);

create index if not exists livelatch_profile_link_clicks_latchid_user_id_idx
  on public.livelatch_profile_link_clicks (latchid_user_id);

create index if not exists livelatch_profile_link_clicks_clicked_at_idx
  on public.livelatch_profile_link_clicks (clicked_at desc);
```

The table is written by Laravel using the Supabase service role key through REST. Do not expose write access to anonymous clients.

## Captured Fields

- `link_id`: local LinkStack/Livelatch link ID
- `laravel_user_id`: local profile owner ID from the `links.user_id` field
- `latchid_user_id`: Supabase/LatchID user ID from `users.supabase_user_id`
- `profile_handle`: public profile handle from `users.littlelink_name`
- `link_title`: link title shown on the profile
- `link_url`: destination URL
- `destination_host`: parsed host from the destination URL when available
- `referer`: request referer, limited to 2048 characters
- `user_agent`: request user agent, limited to 512 characters
- `ip_hash`: HMAC-SHA256 hash of the visitor IP using the Laravel app key
- `clicked_at`: Laravel-side timestamp for the click

## PostHog Event

The server-side PostHog event name is:

```text
profile_link_clicked
```

The event uses this `distinctId` priority:

1. `latchid:{users.supabase_user_id}` when the profile owner has a LatchID/Supabase user ID
2. `laravel-user:{links.user_id}` when no LatchID user ID is available
3. `anonymous:{ip_hash}` as a final fallback

PostHog receives the same core properties used for Supabase analytics:

```text
link_id
laravel_user_id
latchid_user_id
profile_handle
link_title
link_url
destination_host
referer
ip_hash
clicked_at
```

The raw user agent is kept in the Supabase row for server-side diagnostics, but it is not sent as an explicit PostHog property because PostHog already attaches server-side library metadata.

## Privacy Notes

The capture flow stores an IP hash instead of the raw IP address. This supports rough abuse detection or deduplication later without storing raw visitor IPs in Supabase.

Before using this data in production analytics, confirm the privacy policy and retention rules reflect this click capture behavior.
