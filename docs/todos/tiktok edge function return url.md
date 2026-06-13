# TikTok Edge Function Return URL

<!-- todo-check
created_at: 2026-06-13T14:44:09+08:00
ask_after: 2026-06-14T14:44:09+08:00
status: open
-->

Update the Supabase Edge Function at `tiktok-oauth` so it carries Livelatch return URLs through TikTok Login Kit and redirects users back to the social connections page after linking.

## Owner Follow-Up

- On `GET /authorize`, accept a `return_url` query parameter alongside `latchid_user_id`.
- Store `return_url` inside the OAuth `state` value with `latchid_user_id`.
- Keep the existing TikTok OAuth and Supabase insert/upsert logic intact.
- After a successful callback save, redirect to:

```text
{return_url}?tiktok_linked=1
```

- If callback processing fails after the state can be read, redirect to:

```text
{return_url}?tiktok_error=1
```

- If no trusted `return_url` is available, fall back to the current Edge Function behavior.
- Only allow trusted Livelatch return URL origins before redirecting.
