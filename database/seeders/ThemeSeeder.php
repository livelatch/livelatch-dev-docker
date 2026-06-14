<?php

namespace Database\Seeders;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Support\Themes\LivelatchThemeCatalog;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (LivelatchThemeCatalog::all() as $definition) {
            $theme = Theme::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'status' => $definition['status'],
                    'visibility' => $definition['visibility'],
                    'pricing_type' => $definition['pricing_type'],
                ]
            );

            $version = ThemeVersion::updateOrCreate(
                [
                    'theme_id' => $theme->id,
                    'version' => $definition['version'],
                ],
                [
                    'status' => 'published',
                    's3_asset_prefix' => $definition['s3_asset_prefix'],
                    'manifest' => $definition['manifest'],
                ]
            );

            $theme->update([
                'current_version_id' => $version->id,
            ]);
        }
    }
}
