<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A grantable role in the additive (Discord/LuckPerms-style) role system.
 *
 * The catalog is seeded by RoleSeeder. Most roles are inert today — they exist
 * to be attributed to users now and gate access later, in the portal and beyond.
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'description',
        'color',
        'is_system',
        'is_assignable',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_assignable' => 'boolean',
    ];

    // Canonical role keys, so callers never hard-code loose strings.
    public const ADMIN = 'admin';
    public const PRO = 'pro';
    public const FREE = 'free';
    public const PREVIEW = 'preview';
    public const ADMIN_READ_ONLY = 'admin_read_only';
    public const LATCHOPS = 'latchops';
    public const ARTIST = 'artist';
    public const SDK = 'sdk';
    public const STAFF = 'staff';

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
