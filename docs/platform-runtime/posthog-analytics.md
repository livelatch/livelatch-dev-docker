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

The snippet is included in both main app layouts:

```text
resources/views/layouts/app.blade.php
resources/views/layouts/sidebar.blade.php
```

This matters because most Studio and admin screens use `layouts.sidebar`, while some Laravel/Livewire screens use `layouts.app`.

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

