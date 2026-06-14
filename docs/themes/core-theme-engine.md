# Core Theme Engine

Livelatch now has a free core theme catalogue built on the newer database-backed theme engine.

## What Changed

The old LinkStack folder themes still exist, but the Livelatch free theme experience is now driven by:

```text
app/Support/Themes/LivelatchThemeCatalog.php
database/seeders/ThemeSeeder.php
app/Services/ThemeService.php
resources/views/studio/theme.blade.php
resources/views/linkstack/modules/theme.blade.php
```

The catalogue lives in core code and is seeded into the existing `themes` and `theme_versions` database tables. User choices and overrides are still saved to:

```text
user_theme_settings
```

## Free Core Themes

The free catalogue includes:

- Livelatch Default
- Australia
- Minecraft
- Anime
- Cars
- Horses
- Bliss
- Heavy Metal Music
- Cyberpunk
- Windows 95
- MacOS

Each theme has a `default` preset and five additional presets. Presets define:

```text
primary
background
text
buttonRadius
```

The Studio editor also lets users override:

```text
primary
background
text
buttonRadius
fontFamily
effectIntensity
shapeIntensity
```

## Studio Theme UI

The `/studio/theme` page is now a full-width Theme Studio instead of a narrow form card.

Users can:

- choose a theme from visual cards
- choose presets from styled preset pills
- edit colours through styled colour controls
- choose from five suggested fonts per theme
- type a custom [Google Fonts](https://fonts.google.com/) family name
- tune button radius
- tune motion and texture parameters
- preview the theme in a phone-style profile preview

Saving still uses the existing AJAX `POST /studio/theme` flow and stores settings through `ThemeService::saveUserSettings()`.

## Public Profile Rendering

Public profiles still include:

```text
resources/views/linkstack/modules/theme.blade.php
```

That module now reads the resolved public preset from `ThemeService::resolvePublicPreset()` and emits CSS variables:

```css
--ll-primary
--ll-background
--ll-text
--ll-button-radius
--ll-font-family
--ll-effect-intensity
--ll-shape-intensity
```

The selected theme slug controls the free animated background/effect layer. These effects are CSS-only for now; no animation package is required.

## Seeding

Run this after deploying the code to an environment with a working database driver:

```bash
php artisan db:seed --class=ThemeSeeder
```

The seeder uses `updateOrCreate`, so it can be run again after catalogue edits.

## Why Anime.js Was Not Added Yet

[Anime.js](https://animejs.com/) is a good future option for pro or high-motion themes, but the free theme catalogue currently uses CSS animations. This avoids adding frontend package weight while the theme model, preset format, and public rendering path are still being proven.
