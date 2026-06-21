-- Optional platform-specific widget identifier for livelatch_socials. Currently
-- used for the Discord native widget (the server/guild ID; the public server
-- widget JSON at /api/guilds/{id}/widget.json must be enabled in Discord's
-- Server Settings -> Widget). Applied to project yaljyfdfnphxzuhqlbfs 2026-06-21.

alter table public.livelatch_socials
    add column if not exists widget_id text;
