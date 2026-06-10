# LatchID Supabase Schema

LatchID is the Livelatch-branded identity and platform-data layer backed by [Supabase](https://supabase.com/). In product language, LatchID should be treated as the user-facing brand term. Supabase is the implementation platform that provides authentication, Postgres storage, REST access, row-level security, and managed schemas for Livelatch.

## Current project shape

- Project API URL: `https://yaljyfdfnphxzuhqlbfs.supabase.co`
- Custom application schema: `public`
- Managed Supabase schemas observed: `auth`, `storage`, `realtime`, `vault`, `extensions`
- Edge Functions: none
- Remote migrations listed by Supabase: none
- Installed extensions currently visible as installed: `pgcrypto`, `plpgsql`, `uuid-ossp`, `pg_stat_statements`, `supabase_vault`

The absence of tracked remote migrations is important. It means the current LatchID public tables should be treated as live project state that needs migration capture before the schema becomes a repeatable deployment dependency.

## Product boundary

LatchID currently sits between three product areas:

- Livelatch uses LatchID for authentication and global product notifications.
- LatchDeck uses LatchID user IDs to model creators, cards, applications, restrictions, and creator status.
- Laravel remains the local orchestration layer that links `auth.users.id` to `users.supabase_user_id`, creates or links local accounts, provisions Stripe billing, and renders the studio experience.

The practical identity join is:

```text
Supabase auth.users.id
-> Laravel users.supabase_user_id
-> Laravel users.id
-> user_billing.user_id and other local Livelatch records
```

LatchDeck also stores LatchID references directly:

```text
auth.users.id
-> public.latchdeck_creators.latchid_user_id
-> public.latchdeck_cards.creator_id
-> public.latchdeck_applications.creator_id
-> public.latchdeck_restrictions.creator_id
-> public.latchdeck_status_history.creator_id
-> public.latchdeck_cards_mvp.creator_id
```

## Core identity tables

### `auth.users`

This is the managed Supabase Auth user table. Livelatch should not write to it directly through normal application flows. The relevant LatchID fields are:

- `id`: canonical LatchID user UUID
- `email`: identity email used during Laravel account linking
- `raw_user_meta_data`: provider profile data such as display name and avatar context
- `raw_app_meta_data`: provider and role metadata
- `last_sign_in_at`, `created_at`, `updated_at`: operational auth timestamps

Laravel stores the `id` value locally as `users.supabase_user_id`.

### `auth.identities`

This managed table links a user to external providers. It currently contains Google identity rows for the project. Relevant fields are:

- `user_id`: foreign key to `auth.users.id`
- `provider`: OAuth provider name
- `provider_id`: provider-side identifier
- `identity_data`: provider payload, including generated `email`

### `public.profiles`

This is the public profile companion table for LatchID users.

- Primary key: `id`
- Foreign key: `id` -> `auth.users.id`
- Current rows: 2
- RLS: enabled

Columns:

- `id`: LatchID user UUID
- `email`: profile email
- `display_name`: display name shown to product surfaces
- `avatar_url`: external or uploaded avatar URL
- `user_type`: defaults to `creator`
- `created_at`, `updated_at`: profile timestamps

This table is useful for Supabase-native profile reads. Laravel still keeps its own user profile fields and should be treated as the studio rendering source unless a flow explicitly reads from LatchID.

## Livelatch notification table

### `public.livelatch_notifications`

This table stores LatchID-backed notifications rendered inside the Livelatch studio sidebar.

- Primary key: `id`
- Current rows: 6
- RLS: enabled
- Foreign keys: none reported

Columns:

- `id`: notification UUID
- `user_id`: nullable LatchID user UUID; `null` represents a global notification
- `source`: source system, default `livelatch`
- `type`: notification type, default `system`
- `severity`: `info`, `success`, `warning`, `danger`, or future UI-supported values
- `title`: notification title
- `message`: optional body copy
- `action_url`: optional studio or product URL
- `icon`: optional Bootstrap icon class consumed by the sidebar UI
- `metadata`: JSON payload for future structured details
- `read_at`: nullable read timestamp
- `created_at`: creation timestamp

Laravel reads this table through Supabase REST in `LivelatchNotificationService` and sidebar debug routes. User-specific reads are keyed by `users.supabase_user_id`, while global reads use `user_id is null`.

## LatchDeck tables

The LatchDeck schema exists in Supabase even though the Laravel LatchDeck studio screens are still mostly scaffolded. These tables show the intended product model.

### `public.latchdeck_creators`

Creator account state for LatchDeck.

- Primary key: `id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: creator UUID
- `latchid_user_id`: LatchID user UUID from `auth.users.id`
- `email`: creator email snapshot
- `display_name`: creator display name snapshot
- `status`: creator lifecycle state, default `pending`
- `tutorial_seen`: onboarding flag
- `first_card_created`: first-card milestone flag
- `created_at`, `updated_at`: creator timestamps

Child tables reference this table through `creator_id`.

### `public.latchdeck_applications`

Creator application workflow for LatchDeck access or review.

- Primary key: `id`
- Foreign key: `creator_id` -> `public.latchdeck_creators.id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: application UUID
- `creator_id`: creator being reviewed
- `platform`: creator platform or community source
- `community_context`: optional audience/community notes
- `reason`: optional application reason
- `status`: review state, default `submitted`
- `submitted_at`: submission timestamp
- `reviewed_at`: nullable review timestamp
- `reviewed_by`: nullable reviewer LatchID UUID
- `decision_reason`: optional reviewer decision notes

### `public.latchdeck_cards`

Full LatchDeck card model.

- Primary key: `id`
- Foreign key: `creator_id` -> `public.latchdeck_creators.id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: card UUID
- `creator_id`: card owner
- `title`: card title
- `description`: optional card description
- `rarity`: default `common`
- `element`: optional category or visual element
- `image_url`: optional card art URL
- `redeem_code`: optional redemption code
- `is_active`: active flag, default `true`
- `created_at`: creation timestamp

### `public.latchdeck_cards_mvp`

MVP-specific card table with explicitly suffixed column names.

- Primary key: `id`
- Foreign key: `creator_id` -> `public.latchdeck_creators.id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: MVP card UUID
- `creator_id`: creator owner
- `latchid_user_id`: direct LatchID user UUID
- `name_mvp`: card name
- `short_description_mvp`: short description
- `long_description_mvp`: optional long description
- `rarity_mvp`: rarity value
- `creator_name_mvp`: creator display name snapshot
- `image_url_mvp`: optional image URL
- `background_color_mvp`: default `#1b1b29`
- `is_active_mvp`: active flag, default `true`
- `created_at_mvp`, `updated_at_mvp`: MVP timestamps

This table looks like a transitional product table. Future implementation should decide whether it remains the MVP storage surface or is migrated into `latchdeck_cards`.

### `public.latchdeck_restrictions`

Creator restriction and appeal state.

- Primary key: `id`
- Foreign key: `creator_id` -> `public.latchdeck_creators.id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: restriction UUID
- `creator_id`: restricted creator
- `restriction_type`: restriction category
- `policy_reference`: optional policy reference
- `appeal_available`: whether the creator can appeal
- `appeal_deadline`: optional appeal deadline
- `message`: optional user-facing message
- `active`: active flag, default `true`
- `created_at`: creation timestamp

### `public.latchdeck_status_history`

Audit-style lifecycle history for creator status changes.

- Primary key: `id`
- Foreign key: `creator_id` -> `public.latchdeck_creators.id`
- Current rows: 0
- RLS: enabled

Columns:

- `id`: history UUID
- `creator_id`: creator whose status changed
- `old_status`: previous status
- `new_status`: new status
- `reason`: optional reason
- `changed_by`: nullable reviewer/admin LatchID UUID
- `changed_at`: change timestamp

## Managed storage and realtime schemas

Supabase storage tables are present but currently empty:

- `storage.buckets`
- `storage.objects`
- `storage.s3_multipart_uploads`
- `storage.s3_multipart_uploads_parts`
- analytics/vector storage support tables

Livelatch currently uses Laravel S3-compatible storage for profile media rather than Supabase Storage. Keep Supabase Storage documented as available infrastructure, not the current media source of truth.

Realtime tables are also present as managed Supabase infrastructure:

- `realtime.subscription`
- `realtime.messages`

No Livelatch or LatchDeck flow currently appears to depend on Supabase Realtime.

## Advisor findings to track

Supabase advisor checks surfaced these issues during documentation review:

- Several public tables have RLS enabled but no policies: `latchdeck_applications`, `latchdeck_cards`, `latchdeck_cards_mvp`, `latchdeck_creators`, `latchdeck_restrictions`, `latchdeck_status_history`, and `livelatch_notifications`.
- `public.handle_new_user()` is a `SECURITY DEFINER` function executable by `anon` and `authenticated` roles.
- Leaked password protection is disabled for Supabase Auth.
- `public.profiles` RLS policies call auth functions in a way Supabase flags as less efficient at scale.
- Multiple LatchDeck and notification indexes are unused, likely because those tables are not yet active in production flows.

References:

- [Supabase RLS policy lint 0008](https://supabase.com/docs/guides/database/database-linter?lint=0008_rls_enabled_no_policy)
- [Supabase security definer lint 0028](https://supabase.com/docs/guides/database/database-linter?lint=0028_anon_security_definer_function_executable)
- [Supabase security definer lint 0029](https://supabase.com/docs/guides/database/database-linter?lint=0029_authenticated_security_definer_function_executable)
- [Supabase leaked password protection](https://supabase.com/docs/guides/auth/password-security#password-strength-and-leaked-password-protection)
- [Supabase RLS performance guidance](https://supabase.com/docs/guides/database/postgres/row-level-security#call-functions-with-select)

These findings should be treated as follow-up hardening work. They were not changed during this documentation pass.

## Implementation guidance

- Use LatchID in user-facing and product architecture docs when describing the branded identity layer.
- Use Supabase when describing the concrete platform, schema, REST, RLS, Auth, or project configuration.
- Keep Laravel as the local source of studio orchestration, session login, billing decisions, and server-rendered product UX.
- Before LatchDeck moves beyond scaffolding, define RLS policies and capture the current public schema in versioned migrations.
- Avoid adding new direct joins from Laravel local IDs into Supabase tables. Prefer `users.supabase_user_id` as the bridge from Laravel into LatchID.
