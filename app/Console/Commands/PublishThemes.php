<?php

namespace App\Console\Commands;

use App\Services\ThemeRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Package baked blade themes and upload them to S3 so they can be loaded (and
 * disabled / re-tiered / removed) without a redeploy.
 *
 *   php artisan themes:publish              # publish every baked theme
 *   php artisan themes:publish singularity  # publish one theme
 *
 * Layout written to the bucket:
 *   themes/index.json                  { "version": "...", "slugs": [...] }
 *   themes/<slug>/manifest.json
 *   themes/<slug>/<slug>.blade.php
 *   themes/<slug>/assets/*             (mirrors public assets/themes/<slug>)
 *
 * Must run where the S3 (AWS_*) credentials are configured — i.e. on Railway,
 * not a local checkout without creds.
 */
class PublishThemes extends Command
{
    protected $signature = 'themes:publish {slug? : A single theme slug; omit to publish all baked themes}
                            {--prune : Remove S3 themes that no longer exist in the baked source}';

    protected $description = 'Upload baked blade themes to the S3 bucket';

    public function handle(ThemeRegistry $registry): int
    {
        if (!$registry->s3Enabled()) {
            $this->error('S3 disk is not configured (AWS_BUCKET / AWS_ACCESS_KEY_ID). Run this where creds exist.');

            return self::FAILURE;
        }

        $disk    = Storage::disk('s3');
        $only    = $this->argument('slug');
        $manifestsDir = resource_path('themes');

        $targets = [];
        foreach (File::directories($manifestsDir) as $dir) {
            $slug = basename($dir);
            if ($only && $slug !== $only) {
                continue;
            }
            if (File::exists($dir . '/manifest.json')) {
                $targets[] = $slug;
            }
        }

        if (empty($targets)) {
            $this->error($only ? "No baked theme found for slug '{$only}'." : 'No baked themes found.');

            return self::FAILURE;
        }

        $published = [];
        foreach ($targets as $slug) {
            $this->line("Publishing <info>{$slug}</info>…");

            $manifestPath = resource_path("themes/{$slug}/manifest.json");
            $viewPath     = resource_path("views/themes/{$slug}.blade.php");

            if (!File::exists($viewPath)) {
                $this->warn("  skipped: view resources/views/themes/{$slug}.blade.php is missing");
                continue;
            }

            $disk->put("themes/{$slug}/manifest.json", File::get($manifestPath));
            $disk->put("themes/{$slug}/{$slug}.blade.php", File::get($viewPath));

            // Mirror public assets (assets/themes/<slug>/**) into the bundle.
            $assetRoot = public_path("assets/themes/{$slug}");
            $assetCount = 0;
            if (File::isDirectory($assetRoot)) {
                foreach (File::allFiles($assetRoot) as $file) {
                    $rel = str_replace('\\', '/', $file->getRelativePathname());
                    $disk->put("themes/{$slug}/assets/{$rel}", File::get($file->getPathname()));
                    $assetCount++;
                }
            }

            $this->line("  uploaded manifest + view + {$assetCount} asset(s)");
            $published[] = $slug;
        }

        // Rebuild the index from whatever is now present on S3 (merged with what
        // we just published) so partial publishes don't drop existing themes.
        $existing = [];
        if (!$this->option('prune') && $disk->exists('themes/index.json')) {
            $idx = json_decode($disk->get('themes/index.json'), true) ?: [];
            $existing = $idx['slugs'] ?? [];
        }
        $slugs = array_values(array_unique(array_merge($existing, $published)));
        sort($slugs);

        $disk->put('themes/index.json', json_encode([
            'version'      => (string) now()->timestamp,
            'published_at' => now()->toIso8601String(),
            'slugs'        => $slugs,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $registry->clearCache();

        $this->info('Published ' . count($published) . ' theme(s). Index now lists ' . count($slugs) . '.');

        return self::SUCCESS;
    }
}
