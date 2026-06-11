<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'status',
        'visibility',
        'pricing_type',
        'current_version_id',
    ];

    public function versions()
    {
        return $this->hasMany(ThemeVersion::class);
    }

    public function currentVersion()
    {
        return $this->belongsTo(
            ThemeVersion::class,
            'current_version_id'
        );
    }

    public function userThemeSettings()
    {
        return $this->hasMany(UserThemeSetting::class);
    }
}