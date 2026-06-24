<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ThemeRegistry
{
    private string $manifestsPath;

    public function __construct()
    {
        $this->manifestsPath = resource_path('themes');
    }

    /**
     * All available blade themes, keyed by slug.
     */
    public function all(): array
    {
        return Cache::remember('blade_themes_all', 300, function () {
            if (!File::isDirectory($this->manifestsPath)) {
                return [];
            }

            $themes = [];
            foreach (File::directories($this->manifestsPath) as $dir) {
                $manifestPath = $dir . DIRECTORY_SEPARATOR . 'manifest.json';
                if (!File::exists($manifestPath)) {
                    continue;
                }

                $manifest = json_decode(File::get($manifestPath), true);
                if (!$manifest || empty($manifest['slug'])) {
                    continue;
                }

                $themes[$manifest['slug']] = $manifest;
            }

            return $themes;
        });
    }

    /**
     * Single theme manifest by slug, or null if not found.
     */
    public function get(string $slug): ?array
    {
        $path = $this->manifestsPath . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'manifest.json';

        if (!File::exists($path)) {
            return null;
        }

        return json_decode(File::get($path), true) ?: null;
    }

    /**
     * True when the Blade view for this slug exists.
     */
    public function viewExists(string $slug): bool
    {
        return view()->exists("themes.{$slug}");
    }

    /**
     * Merge user-saved settings over manifest defaults.
     */
    public function resolveSettings(string $slug, array $userSettings): array
    {
        $manifest = $this->get($slug);
        if (!$manifest) {
            return $userSettings;
        }

        $defaults = $manifest['defaults'] ?? [];

        foreach ($manifest['controls']['sliders'] ?? [] as $key => $config) {
            if (!isset($defaults[$key]) && isset($config['default'])) {
                $defaults[$key] = $config['default'];
            }
        }
        foreach ($manifest['controls']['selects'] ?? [] as $key => $config) {
            if (!isset($defaults[$key]) && isset($config['default'])) {
                $defaults[$key] = $config['default'];
            }
        }
        foreach ($manifest['controls']['colours'] ?? [] as $colourDef) {
            $key = is_array($colourDef) ? ($colourDef['key'] ?? '') : $colourDef;
            if ($key && !isset($defaults[$key]) && isset($manifest['presets']['default'][$key])) {
                $defaults[$key] = $manifest['presets']['default'][$key];
            }
        }

        return array_merge($defaults, array_filter($userSettings, fn ($v) => $v !== null));
    }

    /**
     * Clear the cached theme list (call after deploying a new theme).
     */
    public function clearCache(): void
    {
        Cache::forget('blade_themes_all');
    }
}
