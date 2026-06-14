# Supabase Profile Link Clicks Table

<!-- todo-check
created_at: 2026-06-14T13:26:58+08:00
ask_after: 2026-06-15T13:26:58+08:00
status: open
-->

Create the Supabase table used by Laravel to capture public profile link clicks.

## Owner Follow-Up

- Authenticate Supabase MCP or create the table manually in the Supabase SQL editor.
- Use the SQL documented in `docs/supabase/profile-link-clicks.md`.
- Confirm Railway has these variables:

```env
LATCHID_SUPABASE_URL=
LATCHID_SERVICE_ROLE_KEY=
SUPABASE_PROFILE_LINK_CLICKS_TABLE=livelatch_profile_link_clicks
```

- Click a public profile link and confirm a row appears in `public.livelatch_profile_link_clicks`.
- Confirm privacy and retention policy language before using the data for production reporting.

