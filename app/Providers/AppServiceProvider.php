<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use PostHog\PostHog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('services.posthog.key')) {
            PostHog::init(config('services.posthog.key'), [
                'host' => config('services.posthog.host'),
            ]);
        }

        Paginator::useBootstrap();
        Validator::extend('isunique', function ($attribute, $value, $parameters, $validator) {
            $value = strtolower($value);
            $query = DB::table($parameters[0])->whereRaw("LOWER({$attribute}) = ?", [$value]);

            if (isset($parameters[1])) {
                $query->where($parameters[1], '!=', $parameters[2]);
            }

            return $query->count() === 0;
        });
        Validator::extend('exturl', function ($attribute, $value, $parameters, $validator) {
            $allowed_schemes = ['http', 'https', 'mailto', 'tel'];
            return in_array(parse_url($value, PHP_URL_SCHEME), $allowed_schemes, true);
        });
        View::addNamespace('blocks', base_path('blocks'));

        // Synced S3 blade themes are written here; registering it as a view
        // location lets them resolve as themes.<slug> alongside baked themes.
        // Baked views (resources/views) are searched first, so they win on a
        // slug collision and S3 acts as the fallback/extension source.
        //
        // The directory must exist before it is registered: `view:cache` (run
        // during the build) scans every view location with Symfony Finder,
        // which throws if a path is missing. Ensure it, and only register it if
        // it is present, so a read-only filesystem can never break the build.
        $themeViews = storage_path('app/theme-views');
        if (!is_dir($themeViews)) {
            @mkdir($themeViews, 0775, true);
        }
        if (is_dir($themeViews)) {
            View::addLocation($themeViews);
        }
    }
}
