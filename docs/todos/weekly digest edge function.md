# Weekly Stats Digest (Edge Function â†’ Resend)

<!-- todo-check
created_at: 2026-06-21T12:00:00+08:00
ask_after: 2026-07-03T22:29:45+08:00
status: open
-->

Build the weekly Latchalytics stats digest as a scheduled **Supabase Edge Function calling the Resend API** â€” deliberately NOT in Laravel, so it stays portable when Livelatch moves off Laravel (matches the existing TikTok Edge Function pattern).

Treated as **service-class** email (a user's own stats), so it does not require marketing consent â€” but must include an unsubscribe / frequency control.

## Checklist

- Scheduled Edge Function (weekly) that pulls per-user stats (PostHog / `DashboardAnalyticsService` equivalent data) and sends via Resend.
- Content: headline number + week-over-week delta, best-performing link, one insight/nudge. Free users get a basic 3-line digest; Pro unlocks the richer breakdown (gated on `User::isPro()` equivalent).
- Respect `user_email_preferences` (and provide a per-user digest opt-out / unsubscribe link).
- Send from the verified Resend domain; mind the free-tier daily cap.
- Ship in-app first / validate numbers on Ben's account before turning on email.

## Owner Follow-Up

- Confirm `RESEND_*` is configured and the sending domain is verified before enabling sends.
- Decide free-vs-Pro content split for the digest.
