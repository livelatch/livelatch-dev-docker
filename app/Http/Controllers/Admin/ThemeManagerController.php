<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BladeThemeCatalog;
use App\Models\UserBladeThemeSetting;
use App\Services\ThemeRegistry;
use App\Support\Ai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Admin → Theme Manager.
 *
 * Central control surface for blade themes: lists everything available (baked
 * + S3), lets an admin enable/disable each one and flip it between Free and
 * Pro, and accepts new themes as a .zip uploaded straight to the S3 bucket —
 * no redeploy. State (enabled / tier) is stored in blade_theme_catalog; a
 * missing row means enabled + manifest tier, so every existing theme is on by
 * default.
 */
class ThemeManagerController extends Controller
{
    public function __construct(private ThemeRegistry $registry)
    {
    }

    public function index()
    {
        $usage = UserBladeThemeSetting::query()
            ->select('theme_slug', DB::raw('count(*) as total'))
            ->groupBy('theme_slug')
            ->pluck('total', 'theme_slug');

        // Sorted: enabled first, then by name.
        $themes = collect($this->registry->allForAdmin())
            ->sortBy(fn ($t) => [($t['enabled'] ?? true) ? 0 : 1, strtolower($t['name'] ?? $t['slug'])])
            ->values()
            ->all();

        // Raw manifests (un-annotated) keyed by slug — what the editor loads/saves.
        $manifests = [];
        foreach ($themes as $t) {
            $manifests[$t['slug']] = $this->registry->get($t['slug']) ?? [];
        }

        return view('studio.admin.theme-manager', [
            'themes'         => $themes,
            'manifests'      => $manifests,
            'usage'          => $usage,
            's3Enabled'      => $this->registry->s3Enabled(),
            'aiTools'        => config('ai.tools', []),
            'aiCategories'   => config('ai.categories', ['none', 'assisted', 'generated']),
            'aiScopes'       => config('ai.scopes', ['code', 'text']),
            'aiDefaultColor' => config('ai.default_color', '#D97757'),
        ]);
    }

    /**
     * Toggle enabled state and/or set the Free/Pro tier for one theme.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'slug'    => ['required', 'string', 'max:64'],
            'enabled' => ['nullable', 'boolean'],
            'tier'    => ['nullable', 'in:free,pro'],
        ]);

        $slug = $data['slug'];
        if (!$this->registry->get($slug)) {
            return $this->respond($request, ['error' => 'Unknown theme.'], 404);
        }

        $meta = $this->registry->meta($slug);
        $row  = BladeThemeCatalog::firstOrNew(['slug' => $slug]);
        $row->enabled = array_key_exists('enabled', $data) && $data['enabled'] !== null
            ? (bool) $data['enabled']
            : ($row->exists ? $row->enabled : $meta['enabled']);
        $row->tier   = $data['tier'] ?? $row->tier;
        $row->source = $row->source ?: $meta['source'];
        $row->save();

        $this->registry->clearCache();

        return $this->respond($request, [
            'message' => 'Theme updated.',
            'slug'    => $slug,
            'enabled' => $row->enabled,
            'tier'    => $this->registry->tier($slug),
        ]);
    }

    /**
     * Upload a theme bundle as a .zip and push it to S3.
     *
     * The zip must contain manifest.json (with a slug) and <slug>.blade.php,
     * optionally an assets/ folder — at the archive root or inside a single
     * top-level folder.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'bundle' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        if (!$this->registry->s3Enabled()) {
            return $this->respond($request, ['error' => 'S3 is not configured on this environment.'], 422);
        }

        if (!class_exists(\ZipArchive::class)) {
            return $this->respond($request, ['error' => 'The PHP zip extension is not available.'], 422);
        }

        $tmp = storage_path('app/tmp/theme-' . bin2hex(random_bytes(6)));
        File::ensureDirectoryExists($tmp);

        try {
            $zip = new \ZipArchive();
            if ($zip->open($request->file('bundle')->getRealPath()) !== true) {
                return $this->respond($request, ['error' => 'Could not open the zip archive.'], 422);
            }
            $zip->extractTo($tmp);
            $zip->close();

            // Locate the bundle root (archive root, or a single wrapping folder).
            $base = $tmp;
            if (!File::exists($base . '/manifest.json')) {
                $dirs = File::directories($tmp);
                if (count($dirs) === 1 && File::exists($dirs[0] . '/manifest.json')) {
                    $base = $dirs[0];
                }
            }
            if (!File::exists($base . '/manifest.json')) {
                return $this->respond($request, ['error' => 'manifest.json not found in the zip.'], 422);
            }

            $manifest = json_decode(File::get($base . '/manifest.json'), true);
            $slug = $this->safeSlug((string) ($manifest['slug'] ?? ''));
            if (!$manifest || $slug === '') {
                return $this->respond($request, ['error' => 'manifest.json is invalid or missing a slug.'], 422);
            }

            // Locate the view: prefer <slug>.blade.php, else the only .blade.php.
            $view = $base . "/{$slug}.blade.php";
            if (!File::exists($view)) {
                $blades = glob($base . '/*.blade.php');
                if (count($blades) === 1) {
                    $view = $blades[0];
                } else {
                    return $this->respond($request, ['error' => "Expected {$slug}.blade.php in the zip."], 422);
                }
            }

            // Normalise the AI disclosure block to policy-legal values before storing.
            $manifest['slug'] = $slug;
            $manifest['ai'] = Ai::normalize($manifest['ai'] ?? []);

            $disk = Storage::disk('s3');
            $disk->put("themes/{$slug}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $disk->put("themes/{$slug}/{$slug}.blade.php", File::get($view));

            $assetCount = 0;
            $assetRoot = $base . '/assets';
            if (File::isDirectory($assetRoot)) {
                foreach (File::allFiles($assetRoot) as $file) {
                    $rel = str_replace('\\', '/', $file->getRelativePathname());
                    $disk->put("themes/{$slug}/assets/{$rel}", File::get($file->getPathname()));
                    $assetCount++;
                }
            }

            $this->bumpIndex($disk, $slug);

            // Register catalog row (enabled, source=s3); keep manifest tier unless
            // an override already exists.
            $row = BladeThemeCatalog::firstOrNew(['slug' => $slug]);
            if (!$row->exists) {
                $row->enabled = true;
            }
            $row->source = 's3';
            $row->save();

            $this->registry->clearCache();

            return $this->respond($request, [
                'message' => "Uploaded '{$slug}' ({$assetCount} asset(s)) to S3.",
                'slug'    => $slug,
            ]);
        } catch (\Throwable $e) {
            return $this->respond($request, ['error' => 'Upload failed: ' . $e->getMessage()], 500);
        } finally {
            File::deleteDirectory($tmp);
        }
    }

    /**
     * Save an edited manifest. Writes manifest.json to S3 and ensures the slug
     * is in the index so the S3 copy becomes authoritative (this also lets a
     * baked theme's manifest be edited — its blade stays baked, the manifest is
     * overridden from S3). enabled / Free-Pro live in the catalog and are set
     * from the list, not here.
     */
    public function editManifest(Request $request)
    {
        $data = $request->validate([
            'slug'     => ['required', 'string', 'max:64'],
            'manifest' => ['required', 'array'],
        ]);

        $slug = $this->safeSlug($data['slug']);
        $manifest = $data['manifest'];

        if ($slug === '' || !$this->registry->get($slug)) {
            return $this->respond($request, ['error' => 'Unknown theme.'], 404);
        }
        if ($this->safeSlug((string) ($manifest['slug'] ?? '')) !== $slug) {
            return $this->respond($request, ['error' => 'The manifest slug must stay the same.'], 422);
        }
        if (empty($manifest['name'])) {
            return $this->respond($request, ['error' => 'A theme name is required.'], 422);
        }
        if (!$this->registry->s3Enabled()) {
            return $this->respond($request, ['error' => 'S3 is not configured on this environment.'], 422);
        }

        $manifest['slug'] = $slug;
        $manifest['ai'] = Ai::normalize($manifest['ai'] ?? []);
        if (isset($manifest['tier']) && !in_array($manifest['tier'], ['free', 'pro'], true)) {
            $manifest['tier'] = 'free';
        }

        try {
            $disk = Storage::disk('s3');
            $disk->put("themes/{$slug}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->bumpIndex($disk, $slug);

            $row = BladeThemeCatalog::firstOrNew(['slug' => $slug]);
            if (!$row->exists) {
                $row->enabled = true;
            }
            $row->source = 's3';
            $row->save();

            $this->registry->clearCache();
        } catch (\Throwable $e) {
            return $this->respond($request, ['error' => 'Save failed: ' . $e->getMessage()], 500);
        }

        return $this->respond($request, [
            'message'  => "Saved manifest for '{$slug}'.",
            'slug'     => $slug,
            'aiBadge'  => Ai::badge($manifest),
        ]);
    }

    private function bumpIndex($disk, string $slug): void
    {
        $slugs = [];
        if ($disk->exists('themes/index.json')) {
            $idx = json_decode($disk->get('themes/index.json'), true) ?: [];
            $slugs = $idx['slugs'] ?? [];
        }
        $slugs = array_values(array_unique(array_merge($slugs, [$slug])));
        sort($slugs);

        $disk->put('themes/index.json', json_encode([
            'version'      => (string) now()->timestamp,
            'published_at' => now()->toIso8601String(),
            'slugs'        => $slugs,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function respond(Request $request, array $payload, int $status = 200)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, $status);
        }

        $flash = $payload['message'] ?? ($payload['error'] ?? null);
        $key   = isset($payload['error']) ? 'error' : 'success';

        return back()->with($key, $flash);
    }

    private function safeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower($slug)) ?? '';
    }
}
