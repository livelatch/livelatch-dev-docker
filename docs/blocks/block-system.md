# Block System

Livelatch uses the inherited [LinkStack](https://linkstack.org/) block model for profile content. Blocks are file-based modules stored under `blocks/`, then selected from the Studio add-block page.

## Current Studio Flow

The add-block page is:

```text
GET /studio/add-link
-> UserController::AddUpdateLink()
-> resources/views/studio/edit-link.blade.php
```

The page now shows an inline block library instead of the older modal picker. This avoids the dashboard modal backdrop issue and makes block selection searchable.

When a user selects a block, the page loads the matching settings form from:

```text
GET /studio/linkparamform_part/{typename}/{linkid}
-> LinkTypeViewController::getParamForm()
-> blocks/{typename}/form.blade.php
```

Saving posts to:

```text
POST /studio/edit-link
-> UserController::saveLink()
```

For custom block types, `saveLink()` includes:

```php
blocks/{typename}/handler.php
```

and calls:

```php
handleLinkType($request, $linkType)
```

The handler returns validation rules and the prepared link data.

## Block Folder Shape

A normal block folder looks like this:

```text
blocks/example/
  config.yml
  form.blade.php
  handler.php
  display.blade.php
```

The current block folders include:

- `blocks/link`
- `blocks/email`
- `blocks/telephone`
- `blocks/vcard`
- `blocks/heading`
- `blocks/text`
- `blocks/spacer`

`predefined` is special. It is added in code by `App\Models\LinkType::get()` and uses `resources/views/components/pageitems/predefined-form.blade.php` instead of a folder under `blocks/`.

## Editing a Block

To edit how a block appears in the Studio form, update:

```text
blocks/{typename}/form.blade.php
```

To edit how submitted values are validated or saved, update:

```text
blocks/{typename}/handler.php
```

To edit how a custom HTML block appears on the public profile, update:

```text
blocks/{typename}/display.blade.php
```

Blocks with `custom_html: true` in `config.yml` render through their `display.blade.php` file on the public profile. Blocks with `custom_html: false` generally render through the standard LinkStack button renderer.

## Adding a Block

1. Create a new folder:

```text
blocks/my-block/
```

2. Add `config.yml`:

```yaml
id: 20
typename: my-block
icon: "bi bi-stars"
custom_html: true
```

Use a unique `typename`. The folder name, `typename`, and view references should match.

3. Add `form.blade.php` with the Studio fields:

```php
<label for="title" class="form-label">Title</label>
<input type="text" name="title" value="{{ $title }}" class="form-control" required>
```

4. Add `handler.php`:

```php
<?php

function handleLinkType($request, $linkType) {
    $rules = [
        'title' => ['required', 'string', 'max:255'],
    ];

    $linkData = [
        'title' => $request->title,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
```

Values matching columns in the `links` table are saved directly. Extra values are JSON encoded into `links.type_params`.

5. If `custom_html: true`, add `display.blade.php`:

```php
<div class="fadein">
    <h2>{{ $link->title }}</h2>
</div>
```

6. If the block needs assets, reference them from the block folder with:

```php
{{ block_asset('asset-name.css') }}
```

`block_asset()` serves files through:

```text
GET /block-asset/{type}?asset=...
```

Allowed asset extensions are controlled in `LinkTypeViewController::blockAsset()`.

## Removing a Block

To remove a block from the Studio picker, remove or rename its `config.yml`.

To fully remove the block implementation, delete the block folder after confirming no saved links still use that block type.

Before deleting a block that has been used in production, check the `links` table for rows where:

```text
links.type = {typename}
```

If rows exist, either migrate them to another block type or keep the display files available so old profiles do not break.

## Important Files

- `app/Models/LinkType.php` discovers blocks from `blocks/*/config.yml`.
- `app/Http/Controllers/LinkTypeViewController.php` loads block forms and serves block assets.
- `app/Http/Controllers/UserController.php` saves links and calls each block handler.
- `resources/views/studio/edit-link.blade.php` renders the Studio add/edit block interface.
- `resources/views/linkstack/elements/buttons.blade.php` renders profile blocks.

## Validation

After editing block views or handlers, run:

```bash
php artisan view:cache
```

For PHP files, run:

```bash
php -l blocks/{typename}/handler.php
```
