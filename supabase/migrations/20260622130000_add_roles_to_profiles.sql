-- Add a roles mirror to public.profiles so non-Laravel consumers (Encore, the
-- viewer portal, edge functions) can gate on a user's additive roles natively
-- without calling back into Laravel or querying the Railway MySQL database.
--
-- This column is a READ-ONLY MIRROR. Railway MySQL (roles + role_user) remains
-- the source of truth: Laravel pushes the user's role-key set here via
-- App\Services\RoleProfileService whenever role membership changes
-- (admin edits, billing-driven pro/free sync).
--
-- Stored as an array of role keys, e.g. '{admin,pro,staff}'. Mirrors the
-- `key` column of public roles in the Laravel `roles` catalog.

alter table public.profiles
  add column if not exists roles text[] not null default '{}';

comment on column public.profiles.roles is
  'Additive role keys mirrored from Laravel role_user via RoleProfileService. Read-only mirror for non-Laravel consumers; Railway MySQL (roles/role_user) remains the source of truth.';
