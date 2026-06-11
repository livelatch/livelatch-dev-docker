<?php

namespace App\Services;

use App\Models\User;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\UserThemeSetting;

class ThemeService
{
    public function getAvailableThemes()
    {
        return Theme::where('status', 'published')
            ->where('visibility', 'public')
            ->with('currentVersion')
            ->get();
    }

    public function getUserTheme(User $user): ?UserThemeSetting
    {
        return $user->themeSetting()
            ->with(['theme', 'themeVersion'])
            ->first();
    }

    public function getThemeManifest(ThemeVersion $version): array
    {
        return $version->manifest ?? [];
    }

    public function saveUserSettings(User $user, array $settings): UserThemeSetting
    {
        return UserThemeSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'theme_id' => $settings['theme_id'],
                'theme_version_id' => $settings['theme_version_id'],
                'preset' => $settings['preset'] ?? 'default',
                'custom_settings' => $settings['custom_settings'] ?? [],
            ]
        );
    }
}