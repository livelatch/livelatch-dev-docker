-- Durable, off-box audit trail for the admin Shell page (/admin/shell).
-- Every command run is recorded here BEFORE it executes, in addition to the
-- container-local `shell` log channel (storage/logs/shell-*.log), which is
-- ephemeral on Railway. Written via the Supabase REST API with the service-role
-- key from App\Services\ShellAuditService (best-effort; never blocks a command).

create table if not exists public.shell_audit_log (
  id              bigint generated always as identity primary key,
  created_at      timestamptz not null default now(),
  laravel_user_id bigint,
  email           text,
  name            text,
  ip              text,
  cwd             text,
  command         text not null
);

comment on table public.shell_audit_log is
  'Audit trail of commands run from the Laravel admin Shell page (/admin/shell). Service-role writes only; not user-facing.';

create index if not exists shell_audit_log_created_at_idx
  on public.shell_audit_log (created_at desc);

-- RLS on with no policies: the anon/authenticated roles get no access at all.
-- Only the service-role key (used server-side by ShellAuditService) can read or
-- write, which bypasses RLS. This keeps the command history out of reach of the
-- client entirely.
alter table public.shell_audit_log enable row level security;
