# YouTube API Provider Setup

<!-- todo-check
created_at: 2026-06-13T12:15:16+08:00
ask_after: 2026-06-14T12:15:16+08:00
status: open
-->

Configure Google/Supabase OAuth so Livelatch can request YouTube read-only API access from the LatchID account page.

## Owner Follow-Up

- In Google Cloud Console, enable YouTube Data API v3 for the Livelatch OAuth project.
- Confirm the OAuth consent screen allows this scope:

```text
https://www.googleapis.com/auth/youtube.readonly
```

- Confirm Google OAuth authorized redirect URIs include the Supabase callback:

```text
https://<project-ref>.supabase.co/auth/v1/callback
```

- In Supabase Auth redirect allow list, add:

```text
https://dev.livelatch.com/callback/youtube
```

- In Laravel env, configure the same Google OAuth client credentials so backend token refresh can work:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

- After deployment and migration, test `Connect YouTube` from `/studio/latchid` and confirm a `social_accounts` row exists with `provider_name = youtube`.
