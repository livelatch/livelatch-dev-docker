# PostHog Analytics

Livelatch uses [PostHog](https://posthog.com/) for product analytics. The current setup has two parts:

- browser analytics through the PostHog JavaScript snippet
- optional server-side PHP initialization through `posthog/posthog-php`

## Environment Variables

Set these variables in Railway and any other deployed environment:

```env
POSTHOG_API_KEY=
POSTHOG_HOST=https://eu.i.posthog.com
```

`POSTHOG_API_KEY` is required for the browser snippet and server-side PHP client to initialize. `POSTHOG_HOST` defaults to `https://eu.i.posthog.com` in `config/services.php`, but it should still be set explicitly in production so the deployment is self-documenting.

## Laravel Config

PostHog config is loaded from `config/services.php`:

```php
'posthog' => [
    'key' => env('POSTHOG_API_KEY'),
    'host' => env('POSTHOG_HOST', 'https://eu.i.posthog.com'),
],
```

The Blade snippet reads these values through:

```php
config('services.posthog.key')
config('services.posthog.host')
```

## Browser Snippet

The JavaScript snippet lives in:

```text
resources/views/layouts/posthog.blade.php
```

It only renders when `config('services.posthog.key')` is present. If the key is missing, empty, or the Laravel config cache is stale, the snippet silently skips rendering.

The snippet explicitly enables:

```js
defaults: '2026-01-30'
autocapture: true
capture_pageview: true
capture_pageleave: true
cross_subdomain_cookie: true
secure_cookie: true
```

`capture_pageleave: true` causes PostHog JS to automatically emit `$pageleave` events. This is also the documented default, but Livelatch sets it explicitly so the behavior is clear.

The snippet is included in the main app layouts and public entry points:

```text
resources/views/layouts/app.blade.php
resources/views/layouts/sidebar.blade.php
resources/views/layouts/guest.blade.php
resources/views/linkstack/modules/meta.blade.php
resources/views/home.blade.php
resources/views/demo.blade.php
resources/views/pages.blade.php
resources/views/report.blade.php
resources/views/linkinfo.blade.php
resources/views/maintenance.blade.php
resources/views/layouts/installing.blade.php
resources/views/layouts/updater.blade.php
resources/views/auth/latchid-oauth-callback.blade.php
resources/views/auth/latchid-google-callback.blade.php
resources/views/vendor/env-editor/layout.blade.php
resources/views/panel/phpinfo.blade.php
```

The standalone marketing homepage is served through `homepage-demo.php`, so it renders the same Blade snippet directly with:

```php
echo view('layouts.posthog')->render();
```

This matters because most Studio and admin screens use `layouts.sidebar`, some Laravel/Livewire screens use `layouts.app`, auth screens use `layouts.guest`, and public profile pages render through the LinkStack profile layout.

## Server-Side Client

`app/Providers/AppServiceProvider.php` initializes the PostHog PHP client only when a key is configured:

```php
if (config('services.posthog.key')) {
    PostHog::init(config('services.posthog.key'), [
        'host' => config('services.posthog.host'),
    ]);
}
```

This avoids initializing the server-side client with a null key in local or incomplete environments.

## Server-Side Events

Public profile link clicks are sent from Laravel to PostHog through:

```text
App\Services\SupabaseProfileLinkClickService
```

The event name is:

```text
profile_link_clicked
```

This event is emitted on the existing `/going/{id}` redirect path, so PostHog receives a click event when a visitor presses a public profile link. The redirect still continues if PostHog capture fails.

## Deployment Checks

After changing PostHog environment variables in Railway:

```bash
php artisan config:clear
php artisan view:clear
```

If config is cached during deployment, rebuild it after the environment variables are available:

```bash
php artisan config:cache
php artisan view:cache
```

To confirm the browser snippet is rendering, view page source on a Studio page and search for:

```text
posthog.init
```

For public coverage, also check:

```text
https://dev.livelatch.com/
https://dev.livelatch.com/@{profile}
https://dev.livelatch.com/login
https://dev.livelatch.com/dashboard
```

Expected browser-side events include `$pageview`, `$pageleave`, and autocaptured click events. Public profile link presses also emit the server-side `profile_link_clicked` event.
