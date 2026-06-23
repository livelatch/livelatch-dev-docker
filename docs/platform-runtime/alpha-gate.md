# Alpha Gate (new-user approval)

During the alpha, every **new** sign-up is held on a waiting screen instead of the
dashboard until an admin approves them. Existing users are unaffected.

## How it works

- A new role **`not_approved`** (catalog key, `is_assignable` so it shows in the
  Manage Users role grid like any other) is the base role for new accounts.
- `LatchIdSessionController@store` assigns it **only in the new-user branch**
  (`$isNewUser`), right after billing/email provisioning, and pushes it to the
  Supabase `profiles.roles` mirror. Free-plan + Supabase provisioning are
  unchanged — this is purely additive.
- The **`approved` middleware** (`App\Http\Middleware\EnsureApproved`, on both
  authed route groups in `routes/web.php`) redirects any `not_approved` holder to
  the holding page `pending` (`resources/views/auth/pending.blade.php` — the alpha
  message + Discord invite + log-out). The holding page and `logout` stay
  reachable so the user isn't trapped. HTMX requests get an `HX-Redirect`.

## Approving a user

In **Manage Users**, edit the user and uncheck the **Not Approved** role, then save
(`syncAssignableRoles` detaches it). They get the dashboard on their next request.

## Kill-switch (toggle gating on/off)

The **Manage Users** page has an **Alpha gating** switch. It writes
`alpha_gate_enabled` (`1`/`0`) into the new **`app_settings`** key/value table via
`App\Models\AppSetting` (cached, read by the middleware on every request). When
**off**, the middleware lets everyone through — even existing `not_approved`
holders — without removing anyone's role, so flipping it back on restores the gate.
Defaults to **on**.

Why a DB setting and not `.env`: the container runs with a cached config and
Railway-injected env, so runtime `.env` edits (the pattern the legacy config
toggles use) don't take effect. `app_settings` (MySQL) is the reliable shared
state.

## Files

- `app/Models/Role.php` — `NOT_APPROVED` const; `database/seeders/RoleSeeder.php`
  entry; `database/migrations/2026_06_23_140000_add_not_approved_role.php`
  (catalog row only — does **not** touch `role_user`, so existing users are safe).
- `app/Http/Controllers/Auth/LatchIdSessionController.php` — assigns the role on
  signup.
- `app/Http/Middleware/EnsureApproved.php` + `app/Http/Kernel.php` (`approved`
  alias) + `routes/web.php` (group middleware + `pending` route).
- `resources/views/auth/pending.blade.php` — holding page.
- `app/Models/AppSetting.php` + `database/migrations/2026_06_23_150000_create_app_settings_table.php`
  — settings store.
- `AdminController@toggleAlphaGate` + route `admin.alphaGate` + the switch in
  `resources/views/panel/users.blade.php`.

## Caveat

Only the **LatchID signup path** assigns `not_approved` (the real sign-up flow).
Admin-created users and the legacy `/register` path are not gated; if legacy
register is ever re-enabled, add `assignRole(Role::NOT_APPROVED)` there too.

## Operator step

Run `php artisan migrate` on the deploy target to create `app_settings` and seed
the `not_approved` catalog row (the two migrations above).
