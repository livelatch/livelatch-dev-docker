# TikTok Edge Function Return URL

<!-- todo-check
created_at: 2026-06-13T14:44:09+08:00
ask_after: 2026-06-14T14:44:09+08:00
status: open
-->

Update the Supabase Edge Function at `tiktok-oauth` so it carries Livelatch return URLs through TikTok Login Kit and redirects users back to the social connections page after linking.

## Owner Follow-Up

- Fix the TikTok user response parser. The current function can return `tiktok_user_fetch_failed` even when TikTok returns HTTP `200`, `error.code: "ok"`, and a populated `data.user` object.
- Treat this response shape as success:

```json
{
  "data": {
    "user": {
      "display_name": "alexfromperth ",
      "open_id": "-000QDO100vJ4GZWDdkqerdwVqql3Kw0hXui",
      "union_id": "18873391-2ba5-566e-946d-eea494715fe8",
      "avatar_url": "https://..."
    }
  },
  "error": {
    "code": "ok",
    "message": ""
  }
}
```

- The success condition should accept `status === 200`, `tiktok.error.code === "ok"`, and `tiktok.data.user.open_id` being present. The user record should be read from `tiktok.data.user`, not from a flat `tiktok.user` field.
- Upsert `display_name`, `avatar_url`, `tiktok_open_id`, and `linked_at` into `public.latchid_tiktok_accounts` after that parser check passes.
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
