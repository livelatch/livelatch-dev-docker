-- Per-user email preferences (source of truth for marketing/notification consent).
--
-- Two email classes drive sending:
--   * Service emails (outages, maintenance, security, billing) are mandatory and
--     are NOT represented here -- every user always receives them.
--   * Marketing emails are explicit opt-in and default to OFF (marketing_opt_in) --
--     a user with no stored consent is never treated as opted in (GDPR).
--   * Targeted notification emails (a notification with user_id set) are gated by
--     notification_emails.
--
-- Resend mirrors this table: resend_contact_id / synced_at track the linked
-- Resend contact so preference changes can be pushed to Resend topics.
--
-- All writes happen server-side via the service-role key (bypasses RLS). The
-- RLS policy lets an authenticated user read only their own row and clears the
-- "RLS enabled, no policy" advisor warning. Applied to Supabase project
-- yaljyfdfnphxzuhqlbfs on 2026-06-21.

create table if not exists public.user_email_preferences (
    user_id uuid primary key
        references auth.users (id) on delete cascade,
    marketing_opt_in boolean not null default false,
    notification_emails boolean not null default true,
    resend_contact_id text,
    synced_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists user_email_preferences_marketing_idx
    on public.user_email_preferences (marketing_opt_in);

-- Keep updated_at fresh on every write.
create or replace function public.touch_user_email_preferences_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

drop trigger if exists trg_user_email_preferences_updated_at
    on public.user_email_preferences;
create trigger trg_user_email_preferences_updated_at
    before update on public.user_email_preferences
    for each row
    execute function public.touch_user_email_preferences_updated_at();

alter table public.user_email_preferences enable row level security;

-- Authenticated users may read only their own preference row.
drop policy if exists "user_email_preferences_select_own"
    on public.user_email_preferences;
create policy "user_email_preferences_select_own"
    on public.user_email_preferences
    for select
    to authenticated
    using (user_id = (select auth.uid()));
