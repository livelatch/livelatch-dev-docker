<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value store for runtime-toggleable platform settings (see migration).
 * Reads are cached forever and busted on write, so hot-path callers (e.g. the
 * EnsureApproved middleware on every request) don't hit the database each time.
 */
class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    private static function cacheKey(string $key): string
    {
        return 'app_setting:' . $key;
    }

    /** Read a setting's raw string value, falling back to $default. */
    public static function get(string $key, $default = null)
    {
        // Guard so a lookup before the table exists (e.g. mid-migration) is safe.
        if (!Schema::hasTable('app_settings')) {
            return $default;
        }

        $value = Cache::rememberForever(
            self::cacheKey($key),
            fn () => static::query()->where('key', $key)->value('value')
        );

        return $value === null ? $default : $value;
    }

    /** Read a setting as a boolean (handles '1'/'0'/'true'/'false'). */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** Write a setting and bust its cache. */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget(self::cacheKey($key));
    }
}
