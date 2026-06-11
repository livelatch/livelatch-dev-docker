<?php

namespace App\Services;

use App\Models\User;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\UserThemeSetting;

class ThemeService
{
    private const DEFAULT_THEME_SLUG = 'livelatch-default';
    private const DEFAULT_PRESET = 'default';

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
                'custom_settings' => $this->cleanCustomSettings($settings['custom_settings'] ?? []),
            ]
        );
    }

    public function resolvePublicPreset(User $user): array
    {
        $setting = $this->getUserTheme($user);
        $version = $setting?->themeVersion;
        $presetKey = $setting?->preset ?: self::DEFAULT_PRESET;

        if (!$version) {
            $theme = Theme::where('slug', self::DEFAULT_THEME_SLUG)
                ->with('currentVersion')
                ->first();

            $version = $theme?->currentVersion;
            $presetKey = self::DEFAULT_PRESET;
        }

        $manifest = $version ? $this->getThemeManifest($version) : [];
        $presets = $manifest['presets'] ?? [];
        $preset = $presets[$presetKey] ?? $presets[self::DEFAULT_PRESET] ?? [];

        $customSettings = $setting?->custom_settings ?? [];

        return [
            'theme_version' => $version,
            'preset_key' => isset($presets[$presetKey]) ? $presetKey : self::DEFAULT_PRESET,
            'preset' => array_merge($this->defaultPresetValues(), $preset, $this->cleanCustomSettings($customSettings)),
        ];
    }

    public function getDefaultThemeFontFamilies(): array
    {
        return [
            'Inter' => 'Inter',
            'Poppins' => 'Poppins',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Montserrat' => 'Montserrat',
            'Lato' => 'Lato',
            'Playfair Display' => 'Playfair Display',
            'Merriweather' => 'Merriweather',
            'Oswald' => 'Oswald',
            'Source Sans 3' => 'Source Sans 3',
        ];
    }

    private function defaultPresetValues(): array
    {
        return [
            'primary' => '#2563eb',
            'background' => '#ffffff',
            'text' => '#111827',
            'buttonRadius' => '8px',
            'fontFamily' => 'Inter',
        ];
    }

    private function cleanCustomSettings(array $settings): array
    {
        $allowedKeys = ['primary', 'background', 'text', 'buttonRadius', 'fontFamily'];
        $cleanSettings = array_intersect_key($settings, array_flip($allowedKeys));

        return array_filter($cleanSettings, function ($value) {
            return is_string($value) && trim($value) !== '';
        });
    }
}
