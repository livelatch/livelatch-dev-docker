# Seed Core Theme Catalogue

ask_after: 2026-06-15T00:00:00+08:00

Run the theme seeder in the environment that has a working database connection:

```bash
php artisan db:seed --class=ThemeSeeder
```

This creates or updates the free core themes from:

```text
app/Support/Themes/LivelatchThemeCatalog.php
```

The local Codex PHP environment could not run the seeder because the configured PDO database driver is missing.
