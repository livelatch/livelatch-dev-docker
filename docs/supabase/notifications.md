# Livelatch Notifications

The notification system lets Livelatch (and trusted tooling such as LatchOps) show
messages to users inside the studio shell — announcements, maintenance notices,
and product events such as invoices. All notifications and their per-user read
state live in Supabase. Laravel reads and writes them through the Supabase REST
API and renders the studio UI.

This document is the source of truth for the system as rebuilt on 2026-06-18.

## Data model

Two tables in the Supabase `public` schema.

### `public.livelatch_notifications`

One row per notification.

| Column        | Notes                                                                 |
| ------------- | --------------------------------------------------------------------- |
| `id`          | UUID, primary key                                                     |
| `user_id`     | LatchID user UUID (`auth.users.id` / `users.supabase_user_id`). **`null` = global**, shown to every user |
| `source`      | Origin system, e.g. `livelatch`, `admin`, `latchops`. Default `livelatch` |
| `type`        | Free-form category, e.g. `system`, `announcement`, `maintenance`, `billing`. Default `system` |
| `severity`    | `info` \| `success` \| `warning` \| `danger` — drives the icon colour |
| `title`       | Headline (required)                                                   |
| `message`     | Optional body copy                                                    |
| `action_url`  | Optional link the user can open                                       |
| `icon`        | Optional Bootstrap icon class (e.g. `bi-receipt`). Defaults to `bi-bell-fill` |
| `metadata`    | Optional JSON for structured detail                                   |
| `read_at`     | **Deprecated.** Legacy single-reader column. Read state is now per-user (see below). Leave `null`. |
| `created_at`  | Creation timestamp                                                    |

### `public.livelatch_notification_reads`

Per-user read state. Because a single global notification is seen by many users,
read state cannot live on the notification row — it lives here, one row per
(notification, user) that has been read.

| Column            | Notes                                                        |
| ----------------- | ------------------------------------------------------------ |
| `id`              | UUID, primary key                                            |
| `notification_id` | FK → `livelatch_notifications.id`, `on delete cascade`       |
| `user_id`         | FK → `auth.users.id`, `on delete cascade`                    |
| `read_at`         | When the user read it, defaults to `now()`                   |
|                   | `unique (notification_id, user_id)` — marking read is idempotent |

A notification is **read for a user** when a matching row exists here, and
**unread** otherwise. Marking unread deletes the row.

The migration is captured at
[`supabase/migrations/20260618120000_livelatch_notifications_per_user_reads_and_rls.sql`](../../supabase/migrations/20260618120000_livelatch_notifications_per_user_reads_and_rls.sql).

## Row-level security

RLS is enabled on both tables. **All application reads and writes go through the
service-role key, which bypasses RLS** (see Laravel + LatchOps below). The
policies exist so that any *authenticated* client (e.g. a future Supabase JS
read) is still constrained, and to satisfy the Supabase advisor:

- `livelatch_notifications`: authenticated users may `SELECT` rows that are
  global (`user_id is null`) or their own (`user_id = auth.uid()`). No
  insert/update/delete for authenticated — **only the service role writes.**
- `livelatch_notification_reads`: authenticated users may select/insert/update/
  delete only their own rows (`user_id = auth.uid()`).

> A historical "new row violates row-level security policy" error came from
> writing with the anon/publishable key. Always write with the **service-role
> key**, which is not subject to RLS.

## Laravel integration

### Configuration

Credentials are resolved through `config('services.supabase_url')` and
`config('services.supabase_service_role_key')` (the same flat keys the other
Supabase-backed services use), **not** `env()` directly — `env()` returns `null`
once `php artisan config:cache` has run.
Required `.env` keys (see `.env.example`):

```
LATCHID_SUPABASE_URL=https://yaljyfdfnphxzuhqlbfs.supabase.co   # falls back to SUPABASE_URL
LATCHID_SERVICE_ROLE_KEY=<service-role key>                     # falls back to SUPABASE_SERVICE_ROLE_KEY
```

If these are missing the service logs a warning and degrades to "no
notifications" rather than erroring.

### Service: `App\Services\LivelatchNotificationService`

| Method                                   | Purpose                                                                 |
| ---------------------------------------- | ----------------------------------------------------------------------- |
| `forUser($userId, $limit = 30)`          | Notifications visible to a user (own + global), newest first, each annotated with `is_read` for that user |
| `unreadCount($userId)`                   | Count of visible notifications the user has not read                    |
| `markAsRead($userId, $notificationId)`   | Idempotent upsert into `livelatch_notification_reads`                   |
| `markAsUnread($userId, $notificationId)` | Deletes the read row (moves back to unread)                             |
| `markAllAsRead($userId)`                 | Marks every currently-visible unread notification as read               |
| `publish($attributes)`                   | Inserts a new notification (server-side senders / admin tooling)        |

`$userId` is the LatchID UUID, i.e. `Auth::user()->supabase_user_id`.

### HTTP endpoints (studio, auth-protected)

| Method & path                          | Route name                      | Action     |
| -------------------------------------- | ------------------------------- | ---------- |
| `GET  /studio/notifications`           | `studio.notifications`          | JSON: `{ unread_count, unread[], read[] }` |
| `POST /studio/notifications/read-all`  | `studio.notifications.readAll`  | Mark all read |
| `POST /studio/notifications/{id}/read` | `studio.notifications.read`     | Mark one read |
| `POST /studio/notifications/{id}/unread` | `studio.notifications.unread` | Mark one unread |

Backed by `App\Http\Controllers\Studio\NotificationController`.

### UI

- **Bell dropdown** (`resources/views/layouts/sidebar.blade.php`): a quick peek at
  the latest few notifications, with an unread dot on the bell.
- **Notification center modal** (`#llNotificationCenterModal`, same file): opened
  from "View notification center". Two tabs — **Unread** (the main list) and
  **Inbox** (read). Marking an item read moves it from Unread to Inbox; the bell
  dot and unread badge update live. Data is loaded from the JSON endpoint each
  time the modal opens.

## How to send a notification

### From Laravel (e.g. when an invoice is issued)

```php
use App\Services\LivelatchNotificationService;

LivelatchNotificationService::publish([
    'user_id'    => $user->supabase_user_id, // or null for everyone
    'source'     => 'billing',
    'type'       => 'billing',
    'severity'   => 'info',
    'title'      => 'Your invoice is ready',
    'message'    => 'Invoice #1043 for £9.00 is available.',
    'action_url' => url('/studio/subscription'),
    'icon'       => 'bi-receipt',
]);
```

### From LatchOps

LatchOps → LatchID → "Send notification" writes to
`public.livelatch_notifications` (`source: latchops`) using the **service-role
key** configured in LatchOps Settings.

### Directly in SQL

```sql
insert into public.livelatch_notifications (user_id, source, type, severity, title, message)
values (null, 'admin', 'announcement', 'info', 'Welcome', 'Thanks for joining.');
```

## Design notes & future work

- Read state is per-user, so global announcements scale to many users correctly.
- Writes are service-role only; do not expose write paths to the anon key.
- `livelatch_notifications.read_at` is retained for backward compatibility but is
  no longer the read signal — prefer `livelatch_notification_reads`.
- Possible next steps: pagination/"load more" in the modal, expiry/auto-archive of
  old notifications, and a typed `metadata` contract per `type`.
