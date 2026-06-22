# Role system (additive, Discord/LuckPerms-style)

Livelatch has **three** access concepts. Know which one you're touching:

| Concept | Storage | Source of truth | Drives |
|---|---|---|---|
| Legacy role | `users.role` enum (`user`/`vip`/`admin`) | itself (mirrored from roles for admin) | `admin` middleware, sidebar, admin bar, impersonation, maintenance bypass, `vip` verified badge + extra link types |
| Billing plan | `user_billing.plan_key` (`free`/`pro`) | Stripe → webhook/reconcile | `User::isPro()`, Billing Centre, Latch On gate (mirrored to Supabase `profiles.plan_key`) |
| **Additive roles** | `roles` + `role_user` (Railway MySQL) | **the pivot** | the new unified role layer (this doc) |

The additive system is the new, native home for "what is this user". It **unifies** the other two by reflecting them in:

- `admin` role  ↔ legacy `users.role = 'admin'`
- `pro` / `free` roles ↔ `user_billing.plan_key`

## Schema

- `roles` — seeded catalog: `key` (machine), `label`, `description`, `color`, `is_system`, `is_assignable`, `sort_order`. Seeded by `database/seeders/RoleSeeder.php`.
- `role_user` — many-to-many pivot, unique `(user_id, role_id)`, `assigned_by`.

## The 9 seeded roles

| key | label | system | assignable | notes |
|---|---|---|---|---|
| `admin` | Admin | yes | yes | mirrors legacy `users.role` |
| `pro` | Pro User | yes | **no** | auto from billing |
| `free` | Free User | yes | **no** | auto from billing |
| `preview` | Preview | no | yes | early access to extras — inert |
| `admin_read_only` | Admin Read Only | no | yes | read-only admin for demos — inert |
| `latchops` | Latchops | no | yes | control-plane operator — inert |
| `artist` | Artist | no | yes | verified artist/creator — inert |
| `sdk` | SDK | no | yes | SDK/API access — inert |
| `staff` | Staff | no | yes | Livelatch staff — inert |

"Inert" = attributed to users now, gates nothing yet. Add gating later with `->hasRole('...')`.

## Using it in code

```php
$user->hasRole(Role::ADMIN);          // honours BOTH pivot and legacy column
$user->hasAnyRole(['staff','latchops']);
$user->assignRole(Role::PREVIEW, $adminId);
$user->roles;                          // Eloquent collection of Role
```

Prefer `hasRole()` over reading `users.role` directly in new code. Existing `role == 'admin'` checks were intentionally left untouched and keep working because `hasRole('admin')` and the legacy column stay mirrored.

## Sync rules (don't fight these)

- **pro/free** are auto-managed by `UserBilling::booted()` (saved hook) and `User::created`, both calling `User::syncPlanRoles()`. Never set them by hand — they'd be overwritten on the next billing change. They render locked in the edit-user UI.
- **admin** set via the Manage Users role grid → `syncAssignableRoles()` mirrors it into `users.role`.
- **vip** is *not* in the additive catalog. It lives only in the legacy column and is preserved by `syncLegacyRoleColumn()`; set it via the "Legacy role" select on the edit-user form.

## Management UI

Manage Users (`/admin/users/all`):
- Table shows a coloured **Roles** chip column.
- Edit user (`/admin/edit-user/{id}`): additive role checkbox grid + the legacy role select. Saved by `AdminController::editUser`.

## Install / run

A single `php artisan migrate` does everything: the backfill migration `2026_06_22_120200_backfill_role_user.php` runs `RoleSeeder` then populates `role_user` from existing `users.role` and `plan_key`, so no user loses access. `RoleSeeder` is also wired into `DatabaseSeeder` for fresh installs and is idempotent (re-run to update labels/colours).

## Extending

Add a role: append a row to `RoleSeeder::run()` and re-run the seeder (`php artisan db:seed --class=RoleSeeder`). Add a `Role::` constant for it. To gate something, branch on `$user->hasRole('your_key')`.
