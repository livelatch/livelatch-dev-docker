<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme_id',
        'theme_version_id',
        'preset',
        'custom_settings',
    ];

    protected $casts = [
        'custom_settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function themeVersion()
    {
        return $this->belongsTo(ThemeVersion::class);
    }
}