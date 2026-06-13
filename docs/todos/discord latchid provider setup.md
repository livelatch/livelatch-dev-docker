# Discord LatchID Provider Setup

<!-- todo-check
created_at: 2026-06-13T11:56:10+08:00
ask_after: 2026-06-14T11:56:10+08:00
status: open
-->

Configure Discord as a Supabase Auth provider so the Livelatch LatchID Discord login and account-link buttons can complete the OAuth flow.

## Owner Follow-Up

- Create or open the Livelatch application in the Discord Developer Portal.
- Add the Supabase Auth callback URL to the Discord OAuth redirect list:

```text
https://<project-ref>.supabase.co/auth/v1/callback
```

- Enable Discord in Supabase Authentication > Sign In / Providers.
- Add the Discord Client ID and Client Secret to the Supabase Discord provider settings.
- Add these Livelatch callback URLs to the Supabase Auth redirect allow list:

```text
https://dev.livelatch.com/callback/discord
https://dev.livelatch.com/callback/google
```

- Test `Continue with Discord` from login/register and `Connect Discord` from `/studio/latchid`.
