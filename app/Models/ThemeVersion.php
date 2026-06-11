<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'version',
        'status',
        's3_asset_prefix',
        'manifest',
    ];

    protected $casts = [
        'manifest' => 'array',
    ];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
}