<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\UserBilling;

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
    }
}
