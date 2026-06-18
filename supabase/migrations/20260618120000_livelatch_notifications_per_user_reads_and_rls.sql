-- Per-user read state for Livelatch notifications.
--
-- A single notification row (especially a global one with user_id = null) can be
-- read independently by many users, so read state lives in its own table rather
-- than on a shared read_at column on livelatch_notifications.
--
-- Applied to Supabase project yaljyfdfnphxzuhqlbfs on 2026-06-18.

create table if not exists public.livelatch_notification_reads (
    id uuid primary key default gen_random_uuid(),
    notification_id uuid not null
        references public.livelatch_notifications (id) on delete cascade,
    user_id uuid not null
        references auth.users (id) on delete cascade,
    read_at timestamptz not null default now(),
    unique (notification_id, user_id)
);

create index if not exists livelatch_notification_reads_user_id_idx
    on public.livelatch_notification_reads (user_id);

create index if not exists livelatch_notification_reads_notification_id_idx
    on public.livelatch_notification_reads (notification_id);

alter table public.livelatch_notification_reads enable row level security;

-- RLS model
-- ---------
-- All writes (publishing notifications, recording reads) happen server-side via the
-- service-role key, which bypasses RLS. The policies below exist so that any
-- authenticated client (e.g. a future Supabase JS read) can only ever see its own
-- data, and to clear the "RLS enabled, no policy" advisor warning.

-- livelatch_notifications: authenticated users may read their own + global rows.
drop policy if exists "ll_notifications_select_own_or_global" on public.livelatch_notifications;
create policy "ll_notifications_select_own_or_global"
    on public.livelatch_notifications
    for select
    to authenticated
    using (user_id is null or user_id = (select auth.uid()));

-- livelatch_notification_reads: authenticated users may manage only their own rows.
drop policy if exists "ll_notification_reads_select_own" on public.livelatch_notification_reads;
create policy "ll_notification_reads_select_own"
    on public.livelatch_notification_reads
    for select
    to authenticated
    using (user_id = (select auth.uid()));

drop policy if exists "ll_notification_reads_insert_own" on public.livelatch_notification_reads;
create policy "ll_notification_reads_insert_own"
    on public.livelatch_notification_reads
    for insert
    to authenticated
    with check (user_id = (select auth.uid()));

drop policy if exists "ll_notification_reads_update_own" on public.livelatch_notification_reads;
create policy "ll_notification_reads_update_own"
    on public.livelatch_notification_reads
    for update
    to authenticated
    using (user_id = (select auth.uid()))
    with check (user_id = (select auth.uid()));

drop policy if exists "ll_notification_reads_delete_own" on public.livelatch_notification_reads;
create policy "ll_notification_reads_delete_own"
    on public.livelatch_notification_reads
    for delete
    to authenticated
    using (user_id = (select auth.uid()));
