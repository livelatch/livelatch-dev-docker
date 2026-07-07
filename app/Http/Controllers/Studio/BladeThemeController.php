<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\UserBladeThemeSetting;
use App\Services\ThemeRegistry;
use App\Support\Ai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Theme Studio (Beta) — the editing surface for the new blade-based theme
 * system (self-contained full-page themes with JS/WebGL, e.g. Portal).
 *
 * This lives at /studio/themes-beta and is intentionally separate from the
 * existing CSS-variable Theme Studio (Studio\ThemeController at /studio/theme),
 * which is left untouched while the new system is built out. A user's blade
 * theme choice is stored in user_blade_theme_settings; the public profile
 * render (UserController) checks that table first and falls through to the
 * CSS-variable theme when there is no row.
 */
class BladeThemeController extends Controller
{
    public function __construct(private ThemeRegistry $registry)
    {
    }

    public function edit(Request $request)
    {
        $user = $request->user();

        // Real usage counts per theme slug for the "times used" badge.
        $usage = UserBladeThemeSetting::query()
            ->select('theme_slug', DB::raw('count(*) as total'))
            ->groupBy('theme_slug')
            ->pluck('total', 'theme_slug');

        $current = UserBladeThemeSetting::where('user_id', $user->id)->first();

        // Attach the consumer-facing AI badge (or null) to each theme so the
        // card grid can show it next to the Pro chip.
        $themes = array_map(function ($t) {
            $t['aiBadge'] = Ai::badge($t);

            return $t;
        }, array_values($this->registry->all()));

        return view('studio.themes-beta', [
            'themes'  => $themes,
            'usage'   => $usage,
            'current' => $current,
            'isPro'   => $this->isPro($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'theme_slug'         => ['required', 'string', 'max:64'],
            'settings'           => ['nullable', 'array'],
            'settings.customCss' => ['nullable', 'string', 'max:5000'],
        ]);

        $slug = (string) $request->input('theme_slug');

        if (!$this->registry->get($slug) || !$this->registry->viewExists($slug)) {
            return $this->error($request, ['theme_slug' => 'That theme is not available.']);
        }

        if (!$this->registry->isEnabled($slug)) {
            return $this->error($request, ['theme_slug' => 'That theme is not currently available.']);
        }

        // Pro themes can be previewed by anyone but only applied by Pro users.
        if ($this->registry->tier($slug) === 'pro' && !$this->isPro($user)) {
            return $this->error($request, ['theme_slug' => 'That is a Pro theme — upgrade to apply it to your profile.']);
        }

        // NB: read settings from input(), not validated(). Declaring the nested
        // rule `settings.customCss` makes validated() return ONLY that key under
        // `settings`, silently dropping every other control (colours, sliders,
        // fonts). sanitize() validates every key against the manifest anyway.
        $settings = $this->sanitize($slug, (array) $request->input('settings', []), $user);

        $setting = UserBladeThemeSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['theme_slug' => $slug, 'settings' => $settings]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Theme applied to your public profile.',
                'setting' => [
                    'theme_slug' => $setting->theme_slug,
                    'settings'   => $setting->settings,
                ],
            ]);
        }

        return back()->with('success', 'Theme applied to your public profile.');
    }

    /**
     * Remove the user's blade theme so the public profile reverts to the
     * standard CSS-variable theme (UserController fall-through).
     */
    public function reset(Request $request)
    {
        UserBladeThemeSetting::where('user_id', $request->user()->id)->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Reverted to your standard theme.']);
        }

        return back()->with('success', 'Reverted to your standard theme.');
    }

    /**
     * Live preview rendered in the Studio iframe. Always the authenticated
     * user's own profile, with the (unsaved) settings supplied as query
     * params — sanitised through the same path as a real save.
     */
    public function preview(Request $request, string $slug)
    {
        $user = $request->user();

        if (!$this->registry->get($slug) || !$this->registry->viewExists($slug)) {
            abort(404);
        }

        $incoming = (array) $request->query('s', []);
        $settings = $this->registry->resolveSettings($slug, $this->sanitize($slug, $incoming, $user));

        return view("themes.{$slug}", [
            'user'     => $user,
            'links'    => $this->loadLinks($user->id),
            'settings' => $settings,
            'manifest' => $this->registry->get($slug),
        ]);
    }

    /**
     * Validate/clamp incoming control values against the theme's manifest.
     * Anything not declared by the manifest (or failing its type rule) is
     * dropped, so the stored settings are always theme-legal.
     */
    private function sanitize(string $slug, array $input, $user): array
    {
        $manifest = $this->registry->get($slug) ?? [];
        $controls = $manifest['controls'] ?? [];
        $out = [];

        // Colours — hex only.
        foreach ($controls['colours'] ?? [] as $colour) {
            $key = $colour['key'] ?? null;
            if ($key && isset($input[$key]) && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $input[$key])) {
                $out[$key] = (string) $input[$key];
            }
        }

        // Typography — must be one of the declared options (or alnum/space fallback).
        foreach ($controls['typography'] ?? [] as $slot) {
            $key = $slot['key'] ?? null;
            if (!$key || !isset($input[$key])) {
                continue;
            }
            $value = trim((string) $input[$key]);
            $options = $slot['options'] ?? [];
            if ((!empty($options) && in_array($value, $options, true))
                || (empty($options) && preg_match('/^[A-Za-z0-9 ]{2,40}$/', $value))) {
                $out[$key] = $value;
            }
        }

        // Sliders — clamp to the declared range.
        foreach ($controls['sliders'] ?? [] as $key => $cfg) {
            if (!isset($input[$key])) {
                continue;
            }
            $min = (int) ($cfg['min'] ?? 0);
            $max = (int) ($cfg['max'] ?? 100);
            $out[$key] = max($min, min($max, (int) $input[$key]));
        }

        // Text controls — plain text, angle brackets stripped, clamped to the
        // manifest maxlength (hard cap 4000). Themes must still escape on
        // render ({{ }}); this only guarantees the stored value is inert.
        foreach ($controls['texts'] ?? [] as $key => $cfg) {
            if (!is_string($key) || !isset($input[$key])) {
                continue;
            }
            $max = max(1, min(4000, (int) (($cfg['maxlength'] ?? null) ?: 200)));
            $value = str_replace(['<', '>'], '', (string) $input[$key]);
            $out[$key] = mb_substr($value, 0, $max);
        }

        // Custom CSS — Pro-gated; angle brackets stripped (defence in depth,
        // the theme also strips them at render time).
        if (!empty($controls['customCss']['pro']) && isset($input['customCss']) && $this->isPro($user)) {
            $css = str_replace(['<', '>'], '', (string) $input['customCss']);
            $out['customCss'] = mb_substr($css, 0, 5000);
        }

        // Universal link-icon controls — apply to every blade theme, so they are
        // accepted regardless of what the theme's manifest declares. Icons are
        // rendered by the shared themes.partials.links partial.
        if (isset($input['showLinkIcons'])) {
            $out['showLinkIcons'] = ((string) $input['showLinkIcons'] === '0') ? '0' : '1';
        }
        if (isset($input['linkIconColor']) && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $input['linkIconColor'])) {
            $out['linkIconColor'] = (string) $input['linkIconColor'];
        }

        return $out;
    }

    /**
     * The user's links, loaded the same way the public profile does
     * (UserController::littlelink) so the preview matches production.
     */
    private function loadLinks(int $userId)
    {
        $links = DB::table('links')
            ->join('buttons', 'buttons.id', '=', 'links.button_id')
            ->select('links.*', 'buttons.name')
            ->where('user_id', $userId)
            ->orderBy('up_link', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        foreach ($links as $link) {
            if (!empty($link->type_params)) {
                $typeParams = json_decode($link->type_params, true);
                if (is_array($typeParams)) {
                    foreach ($typeParams as $key => $value) {
                        $link->$key = $value;
                    }
                }
            }
        }

        return $links;
    }

    private function isPro($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('pro')) {
            return true;
        }

        return optional($user->billing)->plan_key === 'pro';
    }

    private function error(Request $request, array $errors)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['errors' => $errors], 422);
        }

        return back()->withErrors($errors)->withInput();
    }
}
