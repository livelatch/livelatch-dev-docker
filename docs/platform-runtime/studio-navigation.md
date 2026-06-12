# Studio Navigation

The Studio sidebar navigation is rendered by a Livewire component:

```text
app/Http/Livewire/StudioNavigation.php
resources/views/livewire/studio-navigation.blade.php
```

Livewire is provided by [Laravel Livewire](https://livewire.laravel.com/) and is loaded globally in `resources/views/layouts/sidebar.blade.php`.

## Behavior

The sidebar menu is closed by default. Each top-level section is represented by an icon-led button:

- Home
- Admin, for administrator users only
- Navigation
- LatchDeck
- Account
- Community

Clicking a section icon opens that section and reveals its links. Clicking the same section again closes it.

The section open/close interaction is handled by lightweight client-side JavaScript in `resources/views/layouts/sidebar.blade.php`. Livewire renders the menu structure, but it does not make a network request just to open a folder. This keeps the menu responsive and avoids interfering with HTMX link handling.

The links still use HTMX navigation attributes, so page content continues to load into `#ll-content` without a full browser navigation.

## Editing Navigation

To add, remove, or rename sidebar sections and links, edit:

```text
app/Http/Livewire/StudioNavigation.php
```

Each section has:

```php
[
    'key' => 'navigation',
    'label' => 'Navigation',
    'icon' => 'bi bi-compass-fill',
    'items' => [
        [
            'label' => 'Links',
            'icon' => 'bi bi-link-45deg',
            'url' => url('/studio/links'),
            'active' => request()->segment(2) === 'links',
        ],
    ],
]
```

Use [Bootstrap Icons](https://icons.getbootstrap.com/) class names for section and item icons.

## Theme Support

The navigation uses the sidebar design tokens in `resources/views/layouts/sidebar.blade.php`, including:

```css
--ll-bg
--ll-surface
--ll-surface-solid
--ll-text
--ll-muted
--ll-border
--ll-primary
--ll-primary-2
```

Do not hard-code light backgrounds or text colors in the navigation component. Use the existing `ll-*` classes and CSS variables so light and dark mode stay consistent.

## Community Socials

The Community section includes:

```text
GET /studio/socials
-> resources/views/studio/community/socials.blade.php
```

The first version links to the homepage for each supported social platform until official Livelatch profile URLs are ready.

## Validation

After editing the navigation component or layout, run:

```bash
php -l app/Http/Livewire/StudioNavigation.php
php artisan view:cache
```
