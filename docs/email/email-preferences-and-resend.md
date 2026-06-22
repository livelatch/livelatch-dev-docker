# Email Preferences and Resend Integration

This system gives Livelatch a consent-aware transactional and marketing email
pipeline built on [Resend](https://resend.com). It introduces a single source of
truth for what email a user has agreed to receive, mirrors that consent into
Resend, and adds the plumbing to actually send service notices and notification
emails.

## The classes of email

- **Service emails** — outages, maintenance, security, billing, and action-
  servicing mail such as the Latch On confirm/reminder/expiry emails. These are
  mandatory: every user always receives them, and there is no opt-out. They are
  deliberately *not* represented in the preferences table.
- **Marketing emails** — Livelatch's own product news and announcements. Opt-in,
  but the signup checkbox is pre-checked, so the default is "on". Governs Resend
  broadcasts.
- **Notification emails** — an email copy of a notification sent directly to a
  user (a notification with `user_id` set). Gated by a per-user toggle. Global
  notifications stay in-app only.
- **Creator newsletter emails** — a Pro creator's monthly newsletter to the fans
  who latched onto them. This is its **own consent category, independent of
  Livelatch marketing**: a fan who opted out of Livelatch product news may still
  want a creator's newsletter, and vice-versa. Consent is **per-creator**, stored
  on each `creator_latches` row (`email_opt_in`), *not* in the global
  `user_email_preferences` table. See `docs/fanservice/fanservice-overview.md`.

## Source of truth

Consent lives in Supabase, keyed on the LatchID auth user
(`auth.users.id` = `users.supabase_user_id`):

```sql
public.user_email_preferences (
  user_id uuid primary key references auth.users(id) on delete cascade,
  marketing_opt_in    boolean not null default true,
  notification_emails boolean not null default true,
  resend_contact_id   text,
  synced_at           timestamptz,
  ...
)
```

Row-level security lets an authenticated user read only their own row; all writes
go through the service-role key server-side, mirroring the notifications tables.

A local mirror column, `users.marketing_opt_in`, is also kept. It captures the
choice for users with no Supabase auth user yet (legacy email/password
registrations) and provides a cheap local read without a Supabase round-trip.

## Where consent is captured

- **Homepage auth modal (`homepage-demo.php`)** — the real signup path. The
  "Continue with email" (OTP) step shows the pre-checked marketing checkbox; its
  value is posted to `/api/latchid/session` as `marketing_opt_in`.
- **Legacy register form (`auth/register.blade.php`)** — same checkbox, captured
  by `RegisteredUserController` into the local mirror.
- **LatchID OAuth (Google/Discord)** — the checkbox value is stashed in
  `localStorage` before the OAuth redirect and replayed by the callback into the
  session POST.
- **LatchID settings (`studio/account/latchid.blade.php`)** — an "Email
  preferences" card lets users change marketing and notification toggles at any
  time. Service emails are shown as always-on.

On a *new* LatchID signup, `LatchIdSessionController` writes the Supabase
preference row and syncs the Resend contact. Preference changes from settings do
the same through `EmailPreferenceController`.

## Resend model

Consent is mirrored to Resend so broadcasts and contact data stay in step:

- Marketing consent maps to the Resend contact's `unsubscribed` flag
  (`marketing_opt_in === false` → `unsubscribed === true`).
- Identity/plan/preference values are stored as Resend **contact properties**
  (the "tags"): `livelatch_user_id`, `plan_key`, `source`, `notification_emails`.
  Resend property types are limited to string/number, so the boolean is stored as
  a string.
- Contacts live in a single Resend audience (`RESEND_AUDIENCE_ID`).

Sending and contact management require a **full-access** Resend API key
(`RESEND_FULL_API_KEY`); the same key sends service and notification emails over
the Resend HTTP API. When the key/audience are unset, every Resend call degrades
to a no-op so the app keeps working.

## Code map

| Concern | File |
| --- | --- |
| Preference storage (Supabase REST) | `app/Services/EmailPreferenceService.php` |
| Resend contacts + transactional send | `app/Services/ResendContactService.php` |
| Notification → email hook | `app/Services/LivelatchNotificationService.php` (`maybeEmailNotification`) |
| Settings update | `app/Http/Controllers/Studio/EmailPreferenceController.php` |
| Signup capture (LatchID) | `app/Http/Controllers/Auth/LatchIdSessionController.php` |
| Signup capture (legacy) | `app/Http/Controllers/Auth/RegisteredUserController.php` |
| Backfill existing users → Resend | `app/Console/Commands/SyncResendContacts.php` (`resend:sync-contacts`) |
| Send a service notice to all users | `app/Console/Commands/SendServiceNotice.php` (`livelatch:service-notice`) |
| Supabase schema | `supabase/migrations/20260621090000_user_email_preferences.sql` |
| Local mirror column | `database/migrations/2026_06_21_090500_add_marketing_opt_in_to_users_table.php` |

## Operations

- One-time / periodic: `php artisan resend:sync-contacts` imports all
  LatchID-linked users as Resend contacts with tags. Use `--dry-run` to preview.
- Service notice: `php artisan livelatch:service-notice --subject="..."
  --message="..." [--notify]`. `--notify` also publishes a global in-app
  notification.

## Required environment variables

| Variable | Purpose |
| --- | --- |
| `RESEND_FULL_API_KEY` | Full-access Resend key (Contacts/Audiences + sending) |
| `RESEND_AUDIENCE_ID` | Resend audience that contacts are imported into |
| `RESEND_FROM` | Default From, e.g. `Livelatch <hello@livelatch.com>` |

The sending domain `livelatch.com` is verified in Resend.
