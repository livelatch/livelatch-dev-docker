-- Livelatch's own social accounts shown on /studio/socials (global platform
-- config, not per-user). One row per platform; the admin pastes a profile URL
-- (Follow button) and an optional featured post URL (embed) per platform.
-- Writes happen server-side via the service-role key (bypasses RLS); the read
-- policy clears the "RLS enabled, no policy" advisor. Applied to Supabase
-- project yaljyfdfnphxzuhqlbfs on 2026-06-21.

create table if not exists public.livelatch_socials (
    platform text primary key,
    handle text,
    profile_url text,
    featured_post_url text,
    display_order integer not null default 0,
    is_active boolean not null default false,
    updated_at timestamptz not null default now()
);

alter table public.livelatch_socials enable row level security;

drop policy if exists "Authenticated can read livelatch socials" on public.livelatch_socials;
create policy "Authenticated can read livelatch socials"
    on public.livelatch_socials for select
    to authenticated
    using (true);
