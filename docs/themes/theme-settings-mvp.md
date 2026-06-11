# Theme Settings MVP

The first browser-based theme settings flow introduces the new database-backed theme system into the Livelatch studio without removing the legacy LinkStack theme renderer yet.

## What exists now

- `GET /studio/theme` shows a simple theme settings form for signed-in users.
- `POST /studio/theme` saves the selected theme version and preset to `user_theme_settings`.
- The page currently expects the seeded `livelatch-default` theme and its `v1.0.0` published version.
- Presets are read from the selected `ThemeVersion` manifest.
- A Studio-only preview panel uses the manifest preset values for background, text, primary color, and button radius.
- Public profile rendering can resolve a user's saved theme setting through `ThemeService` and emit CSS variables for the selected preset.

This is intentionally small. It proves the browser-to-controller-to-service-to-database path before adding S3 uploads, marketplace logic, color pickers, or advanced CSS editing.

## Studio preview

The `/studio/theme` page includes a preview panel that updates immediately when the preset dropdown changes. It does not save automatically.

The preview applies these CSS variables to a mock profile surface:

```css
--ll-primary
--ll-background
--ll-text
--ll-button-radius
```

The preview shows:

- page background
- heading text
- sample button
- sample link card

This lets the user see the current preset before clicking save while keeping the saved public profile setting as the source of truth.

## Current route flow

```text
/studio/theme
-> Studio\ThemeController@edit
-> ThemeService::getAvailableThemes()
-> ThemeService::getUserTheme()
-> resources/views/studio/theme.blade.php
```

Saving follows:

```text
POST /studio/theme
-> Studio\ThemeController@update
-> validate theme_id, theme_version_id, preset
-> confirm selected version belongs to selected theme
-> confirm preset exists in ThemeVersion manifest
-> ThemeService::saveUserSettings()
-> user_theme_settings row updated
```

## Data dependencies

The MVP depends on these existing tables and models:

- `themes`
- `theme_versions`
- `user_theme_settings`
- `App\Models\Theme`
- `App\Models\ThemeVersion`
- `App\Models\UserThemeSetting`
- `App\Services\ThemeService`

The `User` model exposes a `themeSetting()` relationship so the service can load the current user's saved setting.

## Legacy behavior retained

The old LinkStack theme methods still exist in `UserController`, including:

- legacy theme selection through `users.theme`
- theme ZIP upload
- custom background upload
- local theme folder handling

The new `/studio/theme` route now uses `Studio\ThemeController`, but the legacy rendering system has not been removed. Public profile rendering now reads `user_theme_settings` for CSS-variable presets while the old LinkStack theme CSS and `users.theme` folder behavior remain in place.

## Public rendering

Public profile rendering now resolves theme presets through `ThemeService::resolvePublicPreset()`.

When a user has a saved `UserThemeSetting`, the service reads:

```text
UserThemeSetting
-> ThemeVersion
-> manifest.presets[preset]
```

When no setting exists, it falls back to:

```text
livelatch-default
-> default preset
```

The selected preset is passed into `resources/views/linkstack/modules/theme.blade.php`, which emits:

```css
--ll-primary
--ll-background
--ll-text
--ll-button-radius
```

Those variables are applied to the public page background, text, and LinkStack buttons while the legacy LinkStack theme CSS remains loaded.

## Next steps

- Test the form with a signed-in user and confirm `user_theme_settings.preset` changes.
- Test public profiles for users with and without saved theme settings.
- Add S3-backed custom background uploads.
- Add token controls for colors, typography, button radius, and background options.
- Add an element-scoped CSS editor after the structured preset flow is stable.
