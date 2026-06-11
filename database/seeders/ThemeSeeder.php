<?php

namespace Database\Seeders;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $theme = Theme::updateOrCreate(
            ['slug' => 'livelatch-default'],
            [
                'name' => 'Livelatch Default',
                'status' => 'published',
                'visibility' => 'public',
                'pricing_type' => 'free',
            ]
        );

        $version = ThemeVersion::updateOrCreate(
            [
                'theme_id' => $theme->id,
                'version' => '1.0.0',
            ],
            [
                'status' => 'published',
                's3_asset_prefix' => 'themes/published/livelatch-default/v1.0.0',
                'manifest' => [
                    'presets' => [
                        'default' => [
                            'primary' => '#2563eb',
                            'background' => '#ffffff',
                            'text' => '#111827',
                            'buttonRadius' => '8px',
                        ],
                        'dark' => [
                            'primary' => '#7c3aed',
                            'background' => '#0b0f1a',
                            'text' => '#f9fafb',
                            'buttonRadius' => '10px',
                        ],
                    ],
                    'editableElements' => [
                        'page' => ['background', 'color'],
                        'button' => ['background', 'color', 'border-radius'],
                        'heading' => ['color', 'font-size'],
                    ],
                ],
            ]
        );

        $theme->update([
            'current_version_id' => $version->id,
        ]);
    }
}