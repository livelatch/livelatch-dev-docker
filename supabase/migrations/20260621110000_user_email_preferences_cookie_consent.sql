-- Cookie/analytics consent captured from the homepage banner.
--   * 'all'  -> full analytics + PostHog session replay (persistent cookies)
--   * 'deny' -> no cookies, no session replay (cookieless anonymous analytics)
--   * null   -> no choice recorded yet (treated as 'deny' for behaviour)
-- The authoritative record for anonymous visitors is browser localStorage; this
-- column mirrors the choice for signed-in LatchID users. Applied to Supabase
-- project yaljyfdfnphxzuhqlbfs on 2026-06-21.

alter table public.user_email_preferences
    add column if not exists cookie_consent text
        check (cookie_consent in ('all', 'deny'));
