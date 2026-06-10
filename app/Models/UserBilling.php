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
}