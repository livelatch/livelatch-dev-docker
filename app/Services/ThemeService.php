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
            ->orderByRaw("case when slug = 'livelatch-default' then 0 else 1 end")
            ->orderBy('name')
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
        } else {
            $theme = $setting?->theme;
        }

        $manifest = $version ? $this->getThemeManifest($version) : [];
        $presets = $manifest['presets'] ?? [];
        $preset = $presets[$presetKey] ?? $presets[self::DEFAULT_PRESET] ?? [];

        $customSettings = $setting?->custom_settings ?? [];

        return [
            'theme_version' => $version,
            'theme_slug' => $theme?->slug ?? self::DEFAULT_THEME_SLUG,
            'theme_name' => $theme?->name ?? 'Livelatch Default',
            'manifest' => $manifest,
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
            'effectIntensity' => '55',
            'shapeIntensity' => '45',
            // Link button icons: on by default; empty colour means "match the
            // button text colour". Presets may override either value.
            'showIcons' => '1',
            'iconColor' => '',
        ];
    }

    private function cleanCustomSettings(array $settings): array
    {
        $allowedKeys = ['primary', 'background', 'text', 'buttonRadius', 'fontFamily', 'effectIntensity', 'shapeIntensity', 'showIcons', 'iconColor'];
        $cleanSettings = array_intersect_key($settings, array_flip($allowedKeys));

        return collect($cleanSettings)
            ->map(function ($value, string $key) {
                if (in_array($key, ['effectIntensity', 'shapeIntensity'], true)) {
                    return (string) max(0, min(100, (int) $value));
                }

                if ($key === 'buttonRadius') {
                    return preg_match('/^\d{1,2}px$/', (string) $value) ? (string) $value : null;
                }

                if ($key === 'fontFamily') {
                    $font = trim((string) $value);

                    return preg_match('/^[A-Za-z0-9 ]{2,60}$/', $font) ? $font : null;
                }

                if ($key === 'showIcons') {
                    // Keep this key even when "0" (off), so it survives ->filter().
                    return ((string) $value === '0') ? '0' : '1';
                }

                if ($key === 'iconColor') {
                    $hex = trim((string) $value);

                    return preg_match('/^#[0-9A-Fa-f]{6}$/', $hex) ? $hex : null;
                }

                return is_string($value) && trim($value) !== '' ? $value : null;
            })
            ->filter(fn ($value) => $value !== null)
            ->all();
    }
}
