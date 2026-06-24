<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBladeThemeSetting extends Model
{
    protected $fillable = ['user_id', 'theme_slug', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
