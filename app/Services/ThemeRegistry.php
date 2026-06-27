<?php

namespace App\Services;

use App\Models\BladeThemeCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Discovers and resolves blade themes from two sources:
 *
 *   1. "baked"  — folders shipped in the image under resources/themes/<slug>
 *                 (manifest) + resources/views/themes/<slug>.blade.php (view)
 *                 + assets under <root>/assets/themes/<slug> (public).
 *   2. "s3"     — bundles uploaded to the bucket under themes/<slug>/ and
 *                 listed in themes/index.json. These are synced down to local
 *                 disk on demand so Blade can compile the view and the existing
 *                 asset references (public_path('assets/themes/<slug>/...'))
 *                 resolve unchanged. S3 is the source of truth; local disk is a
 *                 disposable cache that re-hydrates after a deploy.
 *
 * Admin overrides (enabled state, Free/Pro tier) live in the blade_theme_catalog
 * table — never in the bundle — so they survive re-syncs and need no redeploy.
 *
 * NOTE: syncing means compiling Blade fetched from object storage, i.e. the
 * bucket must be treated as trusted code. Restrict write access to ops creds.
 */
class ThemeRegistry
{
    private const CACHE_KEY  = 'blade_themes_all';
    private const CACHE_TTL  = 300;
    private const S3_PREFIX  = 'themes';
    private const INDEX_FILE = 'themes/index.json';

    private string $manifestsPath;

    /** Where synced S3 theme views are written (registered as a Blade location). */
    private string $viewCachePath;

    public function __construct()
    {
        $this->manifestsPath = resource_path('themes');
        $this->viewCachePath = storage_path('app/theme-views/themes');
    }

    /* --------------------------------------------------------------------- */
    /*  Discovery                                                             */
    /* --------------------------------------------------------------------- */

    /**
     * All available blade themes keyed by slug, annotated with admin state
     * (tier, enabled, source). By default disabled themes are excluded — pass
     * true to include them (the admin Theme Manager needs the full list).
     */
    public function all(bool $includeDisabled = false): array
    {
        $version = $this->indexVersion();
        $themes  = Cache::remember(self::CACHE_KEY . ':' . $version, self::CACHE_TTL, function () {
            $baked = $this->bakedManifests();
            $s3    = $this->s3Manifests();

            // S3 wins on content when a slug exists in both (it's the managed
            // copy), but we keep baked as a guaranteed fallback.
            $merged = $baked;
            foreach ($s3 as $slug => $manifest) {
                $merged[$slug] = $manifest;
            }

            return $this->annotate($merged, array_keys($s3));
        });

        if ($includeDisabled) {
            return $themes;
        }

        return array_filter($themes, fn ($t) => ($t['enabled'] ?? true) === true);
    }

    /**
     * Full list including disabled themes, for the admin Theme Manager.
     */
    public function allForAdmin(): array
    {
        return $this->all(true);
    }

    /**
     * Single theme manifest by slug (raw manifest content, no admin annotation),
     * or null if not found. Triggers an on-demand sync for S3-only themes.
     */
    public function get(string $slug): ?array
    {
        $baked = $this->manifestsPath . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'manifest.json';
        if (File::exists($baked)) {
            return json_decode(File::get($baked), true) ?: null;
        }

        $all = $this->all(true);

        return $all[$slug] ?? null;
    }

    /**
     * Admin-facing metadata (tier + enabled + source) for a single slug.
     */
    public function meta(string $slug): array
    {
        $all = $this->all(true);
        $t   = $all[$slug] ?? [];

        return [
            'tier'    => $t['tier'] ?? 'free',
            'enabled' => $t['enabled'] ?? true,
            'source'  => $t['source'] ?? 'baked',
        ];
    }

    /** Free / Pro tier for a slug (admin override → manifest → 'free'). */
    public function tier(string $slug): string
    {
        return $this->meta($slug)['tier'] === 'pro' ? 'pro' : 'free';
    }

    /** Whether the theme is enabled (visible to users). */
    public function isEnabled(string $slug): bool
    {
        return (bool) $this->meta($slug)['enabled'];
    }

    /**
     * True when the Blade view for this slug exists, syncing it down from S3
     * first if it is an S3-only theme that has not been cached locally yet.
     */
    public function viewExists(string $slug): bool
    {
        if (view()->exists("themes.{$slug}")) {
            return true;
        }

        if ($this->syncTheme($slug)) {
            // Bust the compiled-view + finder caches so the freshly written file
            // is picked up within this request.
            view()->getFinder()->flush();

            return view()->exists("themes.{$slug}");
        }

        return false;
    }

    /* --------------------------------------------------------------------- */
    /*  Settings resolution (unchanged behaviour)                             */
    /* --------------------------------------------------------------------- */

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

    /* --------------------------------------------------------------------- */
    /*  S3 sync                                                               */
    /* --------------------------------------------------------------------- */

    /**
     * Whether an S3 disk is configured (key + bucket). When false the registry
     * behaves exactly as the original baked-only implementation.
     */
    public function s3Enabled(): bool
    {
        return !empty(config('filesystems.disks.s3.bucket'))
            && !empty(config('filesystems.disks.s3.key'));
    }

    /**
     * Sync one theme bundle from S3 to local disk:
     *   themes/<slug>/<slug>.blade.php  ->  storage/app/theme-views/themes/<slug>.blade.php
     *   themes/<slug>/assets/*          ->  <root>/assets/themes/<slug>/*   (public)
     *   themes/<slug>/manifest.json     ->  cached in memory (read by s3Manifests)
     *
     * Returns true if the view is present locally afterwards. No-op (false) when
     * S3 is not configured or the theme is not on S3.
     */
    public function syncTheme(string $slug): bool
    {
        if (!$this->s3Enabled()) {
            return false;
        }

        $slug = $this->safeSlug($slug);
        if ($slug === '') {
            return false;
        }

        $viewKey   = self::S3_PREFIX . "/{$slug}/{$slug}.blade.php";
        $localView = $this->viewCachePath . DIRECTORY_SEPARATOR . $slug . '.blade.php';

        try {
            $disk = Storage::disk('s3');
            if (!$disk->exists($viewKey)) {
                return File::exists($localView);
            }

            File::ensureDirectoryExists($this->viewCachePath);
            File::put($localView, $disk->get($viewKey));

            // Assets land where the blades already look for them.
            $assetDir = public_path('assets/themes/' . $slug);
            File::ensureDirectoryExists($assetDir);
            foreach ($disk->files(self::S3_PREFIX . "/{$slug}/assets") as $assetKey) {
                $name = basename($assetKey);
                File::put($assetDir . DIRECTORY_SEPARATOR . $name, $disk->get($assetKey));
            }

            return File::exists($localView);
        } catch (\Throwable $e) {
            Log::warning('ThemeRegistry: failed to sync theme from S3', [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            return File::exists($localView);
        }
    }

    /* --------------------------------------------------------------------- */
    /*  Cache                                                                 */
    /* --------------------------------------------------------------------- */

    /**
     * Clear the cached theme list. Call after an admin enables/disables a theme,
     * changes a tier, or publishes/uploads a bundle.
     */
    public function clearCache(): void
    {
        // The cache key is version-suffixed, so we cannot target every variant;
        // clearing the catalog cache + the current version key covers live use,
        // and stale version keys expire on their own TTL.
        Cache::forget('blade_theme_catalog');
        Cache::forget('blade_themes_index_version');
        Cache::forget(self::CACHE_KEY . ':' . $this->indexVersion());
    }

    /* --------------------------------------------------------------------- */
    /*  Internals                                                             */
    /* --------------------------------------------------------------------- */

    private function bakedManifests(): array
    {
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
    }

    private function s3Manifests(): array
    {
        if (!$this->s3Enabled()) {
            return [];
        }

        try {
            $disk = Storage::disk('s3');
            if (!$disk->exists(self::INDEX_FILE)) {
                return [];
            }
            $index = json_decode($disk->get(self::INDEX_FILE), true) ?: [];
            $slugs = $index['slugs'] ?? [];

            $themes = [];
            foreach ($slugs as $slug) {
                $slug = $this->safeSlug((string) $slug);
                if ($slug === '') {
                    continue;
                }
                $key = self::S3_PREFIX . "/{$slug}/manifest.json";
                if (!$disk->exists($key)) {
                    continue;
                }
                $manifest = json_decode($disk->get($key), true);
                if ($manifest && !empty($manifest['slug'])) {
                    $themes[$manifest['slug']] = $manifest;
                }
            }

            return $themes;
        } catch (\Throwable $e) {
            Log::warning('ThemeRegistry: failed to read themes from S3', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Annotate manifests with admin state. $s3Slugs are the slugs known to live
     * on S3 (used to label source).
     */
    private function annotate(array $themes, array $s3Slugs): array
    {
        $overrides = $this->catalog();
        $s3Set     = array_flip($s3Slugs);

        foreach ($themes as $slug => &$manifest) {
            $row = $overrides[$slug] ?? null;

            $manifestTier = ($manifest['tier'] ?? 'free') === 'pro' ? 'pro' : 'free';
            $tier = $row && $row->tier ? ($row->tier === 'pro' ? 'pro' : 'free') : $manifestTier;

            $manifest['tier']    = $tier;
            $manifest['enabled'] = $row ? (bool) $row->enabled : true;
            $manifest['source']  = isset($s3Set[$slug]) ? 's3' : 'baked';
        }

        return $themes;
    }

    /** Admin override rows keyed by slug; tolerant of the table not existing yet. */
    private function catalog(): array
    {
        return Cache::remember('blade_theme_catalog', self::CACHE_TTL, function () {
            try {
                return BladeThemeCatalog::all()->keyBy('slug')->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /** Current themes/index.json version token (used for cache keys + staleness). */
    private function indexVersion(): string
    {
        return Cache::remember('blade_themes_index_version', 60, function () {
            if (!$this->s3Enabled()) {
                return 'baked';
            }
            try {
                $disk = Storage::disk('s3');
                if (!$disk->exists(self::INDEX_FILE)) {
                    return 'baked';
                }
                $index = json_decode($disk->get(self::INDEX_FILE), true) ?: [];

                return (string) ($index['version'] ?? 'baked');
            } catch (\Throwable $e) {
                return 'baked';
            }
        });
    }

    private function safeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower($slug)) ?? '';
    }
}
