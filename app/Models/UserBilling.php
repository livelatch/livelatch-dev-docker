<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBilling extends Model
{
    use HasFactory;

    protected $table = 'user_billing';

    protected $fillable = [
        'user_id',
        'plan_key',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'stripe_status',
        'current_period_end',
        'cancel_at_period_end',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // Whenever billing changes, reflect plan_key into pro/free role
        // membership so the role system never drifts from Stripe.
        static::saved(function (UserBilling $billing) {
            $user = $billing->user;
            if ($user) {
                $user->syncPlanRoles();
            }
        });
    }
}