<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\UserBilling;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'profile_image',
        'password',
        'provider',
        'provider_id',
        'supabase_user_id',
        'email_verified_at',
        'littlelink_name',
        'marketing_opt_in',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'marketing_opt_in' => 'boolean',
    ];
    public function billing()
    {
        return $this->hasOne(UserBilling::class);
    }

    /** The user's billing plan key ('free' or 'pro'). */
    public function planKey(): string
    {
        return optional($this->billing)->plan_key === 'pro' ? 'pro' : 'free';
    }

    public function isPro(): bool
    {
        return $this->planKey() === 'pro';
    }

    /* ------------------------------------------------------------------ */
    /*  Additive role system (Discord/LuckPerms-style)                    */
    /*                                                                    */
    /*  role_user is the source of truth. The legacy users.role enum is   */
    /*  kept mirrored for admin/vip so existing checks never break, and   */
    /*  hasRole() honours both so the transition is seamless.             */
    /* ------------------------------------------------------------------ */

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /** True if the user holds the given role key. */
    public function hasRole(string $key): bool
    {
        // Legacy compatibility: the admin enum value is authoritative for admin,
        // even before/if the pivot row exists.
        if ($key === Role::ADMIN && $this->role === 'admin') {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('key', $key);
        }

        return $this->roles()->where('key', $key)->exists();
    }

    public function hasAnyRole(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->hasRole($key)) {
                return true;
            }
        }
        return false;
    }

    /** Grant a role by key (idempotent). */
    public function assignRole(string $key, ?int $assignedBy = null): void
    {
        $role = Role::where('key', $key)->first();
        if ($role) {
            $this->roles()->syncWithoutDetaching([
                $role->id => ['assigned_by' => $assignedBy],
            ]);
        }
    }

    /**
     * Replace the user's *assignable* roles with the given set of keys, then
     * mirror admin/vip back into the legacy users.role column. System roles
     * that are not hand-assignable (pro/free) are left untouched here — they
     * are managed automatically from billing via syncPlanRoles().
     */
    public function syncAssignableRoles(array $keys, ?int $assignedBy = null): void
    {
        $assignable = Role::where('is_assignable', true)->get();

        $targetIds = $assignable->whereIn('key', $keys)
            ->mapWithKeys(fn ($r) => [$r->id => ['assigned_by' => $assignedBy]])
            ->all();

        // Only detach/attach within the assignable set; never disturb pro/free.
        $this->roles()->syncWithoutDetaching($targetIds);
        $toDetach = $assignable->whereNotIn('key', $keys)->pluck('id')->all();
        if (!empty($toDetach)) {
            $this->roles()->detach($toDetach);
        }

        $this->syncLegacyRoleColumn();
    }

    /**
     * Keep the legacy users.role enum in lockstep with the pivot. The roles
     * grid is the source of truth for 'admin'. 'vip' lives only in the legacy
     * column (it is not part of the additive catalog), so it is preserved here
     * rather than clobbered. Precedence: admin > vip > user.
     */
    public function syncLegacyRoleColumn(): void
    {
        $this->load('roles');

        if ($this->roles->contains('key', Role::ADMIN)) {
            $legacy = 'admin';
        } elseif ($this->role === 'vip') {
            $legacy = 'vip';
        } else {
            $legacy = 'user';
        }

        if ($this->role !== $legacy) {
            $this->forceFill(['role' => $legacy])->saveQuietly();
        }
    }

    /**
     * Reflect the billing plan into pro/free role membership. Called by the
     * UserBilling observer so these roles can never drift from Stripe.
     */
    public function syncPlanRoles(): void
    {
        $isPro = $this->planKey() === 'pro';
        $this->assignRole($isPro ? Role::PRO : Role::FREE);

        $drop = Role::where('key', $isPro ? Role::FREE : Role::PRO)->first();
        if ($drop) {
            $this->roles()->detach($drop->id);
        }
    }
    public function visits()
    {
        return visits($this)->relation();
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }
    public function themeSetting()
    {
        return $this->hasOne(\App\Models\UserThemeSetting::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (config('linkstack.disable_random_user_ids') != 'true') {
                if (is_null(User::first())) {
                    $user->id = 1;
                } else {
                    $numberOfDigits = config('linkstack.user_id_length') ?? 6;
    
                    $minIdValue = 10**($numberOfDigits - 1);
                    $maxIdValue = 10**$numberOfDigits - 1;
    
                    do {
                        $randomId = rand($minIdValue, $maxIdValue);
                    } while (User::find($randomId));
    
                    $user->id = $randomId;
                }
            }
        });

        // Give every new user their plan role (free by default, until billing
        // says otherwise). Guarded so it is a no-op before the roles table
        // exists (e.g. mid-migration on a fresh install).
        static::created(function ($user) {
            if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                $user->syncPlanRoles();
            }
        });
    }
}
