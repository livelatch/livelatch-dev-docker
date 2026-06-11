<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\ThemeVersion;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    public function __construct(private ThemeService $themeService)
    {
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $themes = $this->themeService->getAvailableThemes();
        $currentSetting = $this->themeService->getUserTheme($user);

        return view('studio.theme', [
            'themes' => $themes,
            'currentSetting' => $currentSetting,
            'fontFamilies' => $this->themeService->getDefaultThemeFontFamilies(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => ['required', 'exists:themes,id'],
            'theme_version_id' => ['required', 'exists:theme_versions,id'],
            'preset' => ['required', 'string', 'max:50'],
            'custom_settings' => ['nullable', 'array'],
            'custom_settings.primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_settings.background' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_settings.text' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_settings.fontFamily' => ['nullable', Rule::in(array_keys($this->themeService->getDefaultThemeFontFamilies()))],
        ]);

        $themeVersion = ThemeVersion::query()
            ->where('id', $validated['theme_version_id'])
            ->where('theme_id', $validated['theme_id'])
            ->first();

        if (!$themeVersion) {
            return $this->themeError($request, ['theme_version_id' => 'The selected theme version does not belong to this theme.']);
        }

        $manifest = $this->themeService->getThemeManifest($themeVersion);
        $presets = $manifest['presets'] ?? [];

        if (!array_key_exists($validated['preset'], $presets)) {
            return $this->themeError($request, ['preset' => 'The selected preset is not available for this theme.']);
        }

        $setting = $this->themeService->saveUserSettings($request->user(), $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Theme settings saved.',
                'setting' => [
                    'theme_id' => $setting->theme_id,
                    'theme_version_id' => $setting->theme_version_id,
                    'preset' => $setting->preset,
                    'custom_settings' => $setting->custom_settings,
                ],
            ]);
        }

        return back()->with('success', 'Theme settings saved.');
    }

    private function themeError(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $errors], 422);
        }

        return back()
            ->withErrors($errors)
            ->withInput();
    }
}
