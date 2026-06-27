<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin override row for a blade theme (enabled state + Free/Pro tier).
 *
 * Keyed by the theme slug, not an auto-increment id. A missing row is the
 * default state: enabled, with the tier taken from the theme's manifest.
 *
 * @see \App\Services\ThemeRegistry
 */
class BladeThemeCatalog extends Model
{
    protected $table = 'blade_theme_catalog';

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['slug', 'enabled', 'tier', 'source', 'sort_order'];

    protected $casts = [
        'enabled'    => 'boolean',
        'sort_order' => 'integer',
    ];
}
