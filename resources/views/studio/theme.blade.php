@extends('layouts.sidebar')

@section('content')
@php
    $selectedThemeId = old('theme_id', $currentSetting?->theme_id ?? $themes->first()?->id);
    $selectedTheme = $themes->firstWhere('id', (int) $selectedThemeId) ?? $themes->first();
    $selectedVersion = $selectedTheme?->currentVersion;
    $selectedVersionId = old('theme_version_id', $currentSetting?->theme_version_id ?? $selectedVersion?->id);
    $selectedPreset = old('preset', $currentSetting?->preset ?? 'default');
    $selectedPresets = $selectedVersion?->manifest['presets'] ?? [];
    $selectedPresetValues = $selectedPresets[$selectedPreset] ?? $selectedPresets['default'] ?? [];
    $customSettings = old('custom_settings', $currentSetting?->custom_settings ?? []);
    $initialPrimary = $customSettings['primary'] ?? $selectedPresetValues['primary'] ?? '#2563eb';
    $initialBackground = $customSettings['background'] ?? $selectedPresetValues['background'] ?? '#ffffff';
    $initialText = $customSettings['text'] ?? $selectedPresetValues['text'] ?? '#111827';
    $initialRadius = $customSettings['buttonRadius'] ?? $selectedPresetValues['buttonRadius'] ?? '8px';
    $initialFontFamily = $customSettings['fontFamily'] ?? $selectedVersion?->manifest['fontFamilies'][0] ?? 'Inter';
    $initialEffect = $customSettings['effectIntensity'] ?? $selectedVersion?->manifest['parameters']['effectIntensity']['default'] ?? 55;
    $initialShape = $customSettings['shapeIntensity'] ?? $selectedVersion?->manifest['parameters']['shapeIntensity']['default'] ?? 45;
    $initialShowIcons = (string) ($customSettings['showIcons'] ?? $selectedPresetValues['showIcons'] ?? '1') !== '0' ? '1' : '0';
    $initialIconColor = $customSettings['iconColor'] ?? $selectedPresetValues['iconColor'] ?? '';
    $initialIconColorOn = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $initialIconColor) ? true : false;
@endphp

<style data-ll-theme-studio-style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Merriweather:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Press+Start+2P:wght@400&family=Rajdhani:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Roboto+Mono:wght@400;500;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

    .ll-theme-studio {
        display: grid;
        gap: 14px;
    }

    .ll-theme-top {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: end;
    }

    .ll-theme-top h1 {
        color: var(--ll-text);
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        line-height: 1;
        margin: 0 0 6px;
    }

    .ll-theme-top p,
    .ll-theme-muted {
        color: var(--ll-muted);
    }

    .ll-theme-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: var(--ll-button-radius);
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        color: #fff;
        font-weight: 800;
        padding: 12px 18px;
        box-shadow: var(--ll-shadow-soft);
    }

    .ll-theme-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
        gap: 18px;
        align-items: start;
    }

    .ll-theme-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
        padding: 14px;
    }

    .ll-theme-panel h2 {
        color: var(--ll-text);
        font-size: .98rem;
        margin: 0 0 10px;
    }

    .ll-theme-grid {
        display: flex;
        gap: 10px;
        margin: 0 -4px;
        overflow-x: auto;
        padding: 2px 4px 10px;
        scroll-snap-type: x proximity;
        scrollbar-width: thin;
    }

    .ll-theme-card {
        border: 1px solid var(--ll-border);
        border-radius: calc(var(--ll-radius) + 2px);
        background: color-mix(in srgb, var(--ll-bg-soft) 70%, transparent);
        color: var(--ll-text);
        cursor: pointer;
        display: grid;
        flex: 0 0 176px;
        gap: 8px;
        min-height: 118px;
        padding: 9px;
        scroll-snap-align: start;
        text-align: left;
        transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        width: 176px;
    }

    .ll-theme-card:hover,
    .ll-theme-card.is-active {
        border-color: color-mix(in srgb, var(--ll-primary) 52%, var(--ll-border));
        background: color-mix(in srgb, var(--ll-primary) 8%, var(--ll-surface-solid));
        transform: translateY(-1px);
    }

    .ll-theme-swatch {
        border-radius: 12px;
        min-height: 48px;
        overflow: hidden;
        position: relative;
    }

    .ll-theme-swatch::after {
        content: "";
        position: absolute;
        inset: 10px;
        border: 1px solid rgba(255,255,255,.45);
        border-radius: 10px;
        background: rgba(255,255,255,.24);
        box-shadow: 0 14px 30px rgba(15,23,42,.18);
    }

    .ll-theme-card strong,
    .ll-theme-control strong {
        color: var(--ll-text);
    }

    .ll-theme-card p {
        color: var(--ll-muted);
        display: none;
        font-size: .8rem;
        line-height: 1.3;
        margin: 0;
    }

    .ll-theme-card small {
        color: var(--ll-muted);
        display: block;
        font-size: .75rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ll-theme-presets,
    .ll-theme-fonts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ll-theme-pill {
        border: 1px solid var(--ll-border);
        border-radius: 999px;
        background: var(--ll-surface-solid);
        color: var(--ll-text);
        cursor: pointer;
        font-weight: 700;
        padding: 7px 10px;
    }

    .ll-theme-pill.is-active {
        border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border));
        background: color-mix(in srgb, var(--ll-primary) 12%, transparent);
        color: var(--ll-primary);
    }

    .ll-theme-controls {
        display: grid;
        gap: 12px;
    }

    .ll-theme-picker-panel {
        display: grid;
        gap: 12px;
    }

    .ll-theme-picker-head {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .ll-theme-picker-head p {
        color: var(--ll-muted);
        font-size: .86rem;
        margin: 0;
    }

    .ll-theme-presets-row {
        align-items: center;
        border-top: 1px solid var(--ll-border);
        display: grid;
        gap: 10px;
        grid-template-columns: auto minmax(0, 1fr);
        padding-top: 12px;
    }

    .ll-theme-presets-row > span {
        color: var(--ll-muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .ll-theme-colours {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ll-colour-control,
    .ll-theme-control {
        border: 1px solid var(--ll-border);
        border-radius: calc(var(--ll-radius) - 2px);
        background: color-mix(in srgb, var(--ll-bg-soft) 74%, transparent);
        padding: 12px;
    }

    .ll-colour-control label,
    .ll-theme-control label {
        color: var(--ll-muted);
        display: block;
        font-size: .78rem;
        font-weight: 800;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .ll-colour-row {
        align-items: center;
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 10px;
    }

    .ll-colour-row input[type="color"] {
        appearance: none;
        border: 0;
        border-radius: 14px;
        cursor: pointer;
        height: 48px;
        overflow: hidden;
        padding: 0;
        width: 48px;
    }

    .ll-colour-row input[type="color"]::-webkit-color-swatch-wrapper {
        padding: 0;
    }

    .ll-colour-row input[type="color"]::-webkit-color-swatch {
        border: 0;
        border-radius: 14px;
    }

    .ll-colour-value {
        color: var(--ll-text);
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-weight: 800;
    }

    .ll-theme-range {
        accent-color: var(--ll-primary);
        width: 100%;
    }

    .ll-theme-custom-font {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        margin-top: 10px;
    }

    .ll-theme-custom-font input {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-button-radius);
        background: var(--ll-surface-solid);
        color: var(--ll-text);
        padding: 10px 12px;
    }

    .ll-theme-preview-shell {
        position: sticky;
        top: 92px;
    }

    #theme-preview {
        --ll-preview-primary: {{ $initialPrimary }};
        --ll-preview-background: {{ $initialBackground }};
        --ll-preview-text: {{ $initialText }};
        --ll-preview-radius: {{ $initialRadius }};
        --ll-preview-font: "{{ $initialFontFamily }}", system-ui, sans-serif;
        --ll-preview-effect: {{ ((int) $initialEffect) / 100 }};
        --ll-preview-shape: {{ ((int) $initialShape) / 100 }};
        border: 1px solid var(--ll-border);
        border-radius: 34px;
        background: #101827;
        box-shadow: var(--ll-shadow-soft);
        margin: 0 auto;
        max-width: 342px;
        padding: 10px;
    }

    .ll-theme-preview-screen {
        background:
            radial-gradient(circle at 18% 12%, color-mix(in srgb, var(--ll-preview-primary) 34%, transparent), transparent 28%),
            linear-gradient(135deg, var(--ll-preview-background), color-mix(in srgb, var(--ll-preview-primary) 12%, var(--ll-preview-background)));
        border-radius: 26px;
        color: var(--ll-preview-text);
        font-family: var(--ll-preview-font);
        min-height: 500px;
        overflow: hidden;
        padding: 24px 18px;
        position: relative;
    }

    .ll-theme-preview-screen::before,
    .ll-theme-preview-screen::after {
        content: "";
        pointer-events: none;
        position: absolute;
        inset: 0;
    }

    .ll-theme-preview-screen::before {
        background-image:
            radial-gradient(circle, color-mix(in srgb, var(--ll-preview-primary) 26%, transparent) 1px, transparent 2px),
            linear-gradient(120deg, transparent 0 22px, color-mix(in srgb, var(--ll-preview-primary) 10%, transparent) 22px 25px);
        background-size: calc(28px - (var(--ll-preview-shape) * 10px)) calc(28px - (var(--ll-preview-shape) * 10px)), 72px 72px;
        opacity: calc(.12 + (var(--ll-preview-effect) * .28));
    }

    .ll-theme-preview-screen::after {
        background: radial-gradient(circle at 82% 16%, rgba(255,255,255,.34), transparent 16%);
        animation: ll-theme-preview-drift 12s ease-in-out infinite alternate;
        opacity: calc(.18 + (var(--ll-preview-effect) * .24));
    }

    @keyframes ll-theme-preview-drift {
        to { transform: translate3d(16px, -12px, 0); }
    }

    .ll-theme-preview-profile {
        display: grid;
        gap: 11px;
        position: relative;
        text-align: center;
        z-index: 1;
    }

    .ll-theme-avatar {
        background: var(--ll-preview-primary);
        border: 3px solid rgba(255,255,255,.42);
        border-radius: 999px;
        height: 66px;
        margin: 0 auto;
        width: 66px;
    }

    .ll-theme-preview-profile h3 {
        color: var(--ll-preview-text);
        font-family: var(--ll-preview-font);
        margin: 0;
    }

    .ll-theme-preview-profile p {
        color: var(--ll-preview-text);
        margin: 0;
        opacity: .76;
    }

    .ll-theme-preview-link {
        background: var(--ll-preview-primary);
        border-radius: var(--ll-preview-radius);
        box-shadow: 0 14px 34px color-mix(in srgb, var(--ll-preview-primary) 26%, transparent);
        color: #fff;
        display: block;
        font-weight: 800;
        padding: 14px;
        text-decoration: none;
    }

    .ll-theme-preview-card {
        align-items: center;
        background: color-mix(in srgb, #ffffff 24%, transparent);
        border: 1px solid color-mix(in srgb, var(--ll-preview-text) 18%, transparent);
        border-radius: var(--ll-preview-radius);
        display: flex;
        gap: 12px;
        padding: 14px;
        text-align: left;
    }

    .ll-theme-preview-card span {
        background: var(--ll-preview-primary);
        border-radius: 12px;
        height: 38px;
        width: 38px;
    }

    .ll-theme-alert {
        border-radius: var(--ll-radius);
    }

    @media (max-width: 1199.98px) {
        .ll-theme-layout,
        .ll-theme-top {
            grid-template-columns: 1fr;
        }

        .ll-theme-preview-shell {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .ll-theme-grid,
        .ll-theme-colours {
            grid-template-columns: 1fr;
        }

        .ll-theme-card {
            flex-basis: 152px;
            width: 152px;
        }

        .ll-theme-presets-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-theme-studio">
    <div class="ll-theme-top">
        <div>
            <h1>Theme Studio</h1>
            <p class="mb-0">Choose a theme, pick a preset, then tune colours, font, radius, and motion for your public profile.</p>
        </div>
        @if(!$themes->isEmpty())
            <button type="submit" form="theme-settings-form" class="ll-theme-save" id="theme-save-button">
                <i class="bi bi-check2-circle"></i>
                Save theme
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success ll-theme-alert" role="alert">{{ session('success') }}</div>
    @endif

    <div class="alert alert-danger d-none ll-theme-alert" id="theme-settings-errors" role="alert"></div>

    @if($errors->any())
        <div class="alert alert-danger ll-theme-alert" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($themes->isEmpty())
        <div class="alert alert-warning ll-theme-alert" role="alert">No published themes are available yet.</div>
    @else
        <form method="post" action="{{ route('editTheme') }}" id="theme-settings-form" data-custom-settings='@json($customSettings)'>
            @csrf
            <input type="hidden" name="theme_id" id="theme-id" value="{{ $selectedTheme?->id }}">
            <input type="hidden" name="theme_version_id" id="theme-version-id" value="{{ $selectedVersionId }}">
            <input type="hidden" name="preset" id="theme-preset" value="{{ $selectedPreset }}">

            <div class="ll-theme-layout">
                <div class="ll-theme-controls">
                    <section class="ll-theme-panel ll-theme-picker-panel">
                        <div class="ll-theme-picker-head">
                            <div>
                                <h2>Choose a theme</h2>
                                <p>Browse available looks for your profile.</p>
                            </div>
                        </div>
                        <div class="ll-theme-grid" id="theme-card-grid">
                            @foreach($themes as $theme)
                                @php
                                    $version = $theme->currentVersion;
                                    $manifest = $version?->manifest ?? [];
                                    $presets = $manifest['presets'] ?? [];
                                    $defaultPreset = $presets['default'] ?? reset($presets) ?: [];
                                @endphp
                                <button
                                    type="button"
                                    class="ll-theme-card @if((int) $selectedThemeId === $theme->id) is-active @endif"
                                    data-theme-card
                                    data-theme-id="{{ $theme->id }}"
                                    data-theme-slug="{{ $theme->slug }}"
                                    data-version-id="{{ $version?->id }}"
                                    data-theme-name="{{ $theme->name }}"
                                    data-description="{{ $manifest['description'] ?? '' }}"
                                    data-preview-gradient="{{ $manifest['previewGradient'] ?? 'linear-gradient(135deg, #2563eb, #22d3ee)' }}"
                                    data-presets='@json($presets)'
                                    data-fonts='@json($manifest['fontFamilies'] ?? array_values($fontFamilies))'
                                    data-parameters='@json($manifest['parameters'] ?? [])'
                                >
                                    <span class="ll-theme-swatch" style="background: {{ $manifest['previewGradient'] ?? 'linear-gradient(135deg, #2563eb, #22d3ee)' }};"></span>
                                    <span>
                                        <strong>{{ $theme->name }}</strong>
                                        <small>{{ $theme->pricing_type === 'free' ? 'Included' : ucfirst($theme->pricing_type) }}</small>
                                        <p>{{ $manifest['description'] ?? 'Livelatch theme.' }}</p>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <div class="ll-theme-presets-row">
                            <span>Presets</span>
                            <div class="ll-theme-presets" id="theme-preset-list"></div>
                        </div>
                    </section>

                    <section class="ll-theme-panel">
                        <h2>Style controls</h2>
                        <div class="ll-theme-colours">
                            <div class="ll-colour-control">
                                <label for="theme-primary">Primary</label>
                                <div class="ll-colour-row">
                                    <input type="color" id="theme-primary" name="custom_settings[primary]" value="{{ $initialPrimary }}">
                                    <span class="ll-colour-value" data-colour-value="theme-primary">{{ $initialPrimary }}</span>
                                </div>
                            </div>
                            <div class="ll-colour-control">
                                <label for="theme-background">Background</label>
                                <div class="ll-colour-row">
                                    <input type="color" id="theme-background" name="custom_settings[background]" value="{{ $initialBackground }}">
                                    <span class="ll-colour-value" data-colour-value="theme-background">{{ $initialBackground }}</span>
                                </div>
                            </div>
                            <div class="ll-colour-control">
                                <label for="theme-text">Text</label>
                                <div class="ll-colour-row">
                                    <input type="color" id="theme-text" name="custom_settings[text]" value="{{ $initialText }}">
                                    <span class="ll-colour-value" data-colour-value="theme-text">{{ $initialText }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ll-theme-panel">
                        <h2>Link icons</h2>
                        <div class="ll-theme-control">
                            <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                                <input type="checkbox" id="theme-show-icons" {{ $initialShowIcons === '1' ? 'checked' : '' }}>
                                Show icons on link buttons
                            </label>
                            <input type="hidden" name="custom_settings[showIcons]" id="theme-show-icons-value" value="{{ $initialShowIcons }}">
                        </div>
                        <div class="ll-colour-control mt-3" id="theme-icon-color-wrap">
                            <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                                <input type="checkbox" id="theme-icon-color-toggle" {{ $initialIconColorOn ? 'checked' : '' }}>
                                Custom icon colour
                            </label>
                            <div class="ll-colour-row mt-2">
                                <input type="color" id="theme-icon-color" value="{{ $initialIconColorOn ? $initialIconColor : '#ffffff' }}" {{ $initialIconColorOn ? '' : 'disabled' }}>
                                <span class="ll-colour-value" id="theme-icon-color-value">{{ $initialIconColorOn ? $initialIconColor : 'Match button text' }}</span>
                            </div>
                            <input type="hidden" name="custom_settings[iconColor]" id="theme-icon-color-input" value="{{ $initialIconColorOn ? $initialIconColor : '' }}">
                            <p class="ll-theme-muted mt-2 mb-0" style="font-size:.8rem;">Off = icons match your button text colour. Presets can set a colour too.</p>
                        </div>
                    </section>

                    <section class="ll-theme-panel">
                        <h2>Typography</h2>
                        <div class="ll-theme-fonts" id="theme-font-list"></div>
                        <div class="ll-theme-custom-font">
                            <input type="text" id="theme-custom-font" placeholder="Add a Google Font name">
                            <button type="button" class="ll-theme-pill" id="theme-custom-font-apply">Use font</button>
                        </div>
                        <input type="hidden" id="theme-font-family" name="custom_settings[fontFamily]" value="{{ $initialFontFamily }}">
                    </section>

                    <section class="ll-theme-panel">
                        <h2>Shape and motion</h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="ll-theme-control">
                                    <label for="theme-radius">Button radius</label>
                                    <input class="ll-theme-range" type="range" min="0" max="32" step="1" id="theme-radius" value="{{ (int) $initialRadius }}">
                                    <input type="hidden" id="theme-radius-value" name="custom_settings[buttonRadius]" value="{{ $initialRadius }}">
                                    <strong id="theme-radius-label">{{ $initialRadius }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ll-theme-control">
                                    <label for="theme-effect">Motion</label>
                                    <input class="ll-theme-range" type="range" min="0" max="100" id="theme-effect" name="custom_settings[effectIntensity]" value="{{ $initialEffect }}">
                                    <strong id="theme-effect-label">{{ $initialEffect }}%</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ll-theme-control">
                                    <label for="theme-shape">Texture</label>
                                    <input class="ll-theme-range" type="range" min="0" max="100" id="theme-shape" name="custom_settings[shapeIntensity]" value="{{ $initialShape }}">
                                    <strong id="theme-shape-label">{{ $initialShape }}%</strong>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="ll-theme-preview-shell">
                    <section class="ll-theme-panel">
                        <h2>Live preview</h2>
                        <div id="theme-preview">
                            <div class="ll-theme-preview-screen" id="theme-preview-screen">
                                <div class="ll-theme-preview-profile">
                                    <div class="ll-theme-avatar"></div>
                                    <h3 id="theme-preview-heading">{{ $selectedTheme?->name ?? 'Livelatch Default' }}</h3>
                                    <p id="theme-preview-copy">{{ Str::headline($selectedPreset) }} preset preview for your public profile.</p>
                                    <a class="ll-theme-preview-link" href="#" onclick="return false;">Latest drop</a>
                                    <a class="ll-theme-preview-link" href="#" onclick="return false;">Watch live</a>
                                    <div class="ll-theme-preview-card">
                                        <span></span>
                                        <div>
                                            <strong>Creator link card</strong>
                                            <p class="mb-0">Your links stay clear and easy to tap.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="ll-theme-muted mt-3 mb-0">Preview changes do not save until you press Save theme.</p>
                    </section>
                </aside>
            </div>
        </form>
    @endif
</div>

<div class="modal fade" id="theme-saved-modal" tabindex="-1" aria-labelledby="theme-saved-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="theme-saved-modal-label">Theme saved</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">Your theme settings have been saved to your public profile.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.LivelatchThemeEditor = window.LivelatchThemeEditor || {
        init: function () {
            const form = document.getElementById('theme-settings-form');
            if (!form || form.dataset.themeEditorInitialized === 'true') return;
            form.dataset.themeEditorInitialized = 'true';

            const cards = Array.from(document.querySelectorAll('[data-theme-card]'));
            const themeInput = document.getElementById('theme-id');
            const versionInput = document.getElementById('theme-version-id');
            const presetInput = document.getElementById('theme-preset');
            const presetList = document.getElementById('theme-preset-list');
            const fontList = document.getElementById('theme-font-list');
            const fontInput = document.getElementById('theme-font-family');
            const customFont = document.getElementById('theme-custom-font');
            const customFontApply = document.getElementById('theme-custom-font-apply');
            const primaryInput = document.getElementById('theme-primary');
            const backgroundInput = document.getElementById('theme-background');
            const textInput = document.getElementById('theme-text');
            const radiusRange = document.getElementById('theme-radius');
            const radiusValue = document.getElementById('theme-radius-value');
            const radiusLabel = document.getElementById('theme-radius-label');
            const effectInput = document.getElementById('theme-effect');
            const effectLabel = document.getElementById('theme-effect-label');
            const shapeInput = document.getElementById('theme-shape');
            const shapeLabel = document.getElementById('theme-shape-label');
            const showIconsCheckbox = document.getElementById('theme-show-icons');
            const showIconsValue = document.getElementById('theme-show-icons-value');
            const iconColorToggle = document.getElementById('theme-icon-color-toggle');
            const iconColorInput = document.getElementById('theme-icon-color');
            const iconColorHidden = document.getElementById('theme-icon-color-input');
            const iconColorValue = document.getElementById('theme-icon-color-value');

            function syncIconColor() {
                const on = iconColorToggle.checked;
                iconColorInput.disabled = !on;
                iconColorHidden.value = on ? iconColorInput.value : '';
                iconColorValue.textContent = on ? iconColorInput.value : 'Match button text';
            }
            const preview = document.getElementById('theme-preview');
            const previewScreen = document.getElementById('theme-preview-screen');
            const previewHeading = document.getElementById('theme-preview-heading');
            const previewCopy = document.getElementById('theme-preview-copy');
            const saveButton = document.getElementById('theme-save-button');
            const errorBox = document.getElementById('theme-settings-errors');

            function parseJson(value, fallback) {
                try { return JSON.parse(value || ''); } catch (error) { return fallback; }
            }

            function activeCard() {
                return cards.find(card => card.classList.contains('is-active')) || cards[0];
            }

            function currentPresets() {
                return parseJson(activeCard()?.dataset.presets, {});
            }

            function formatName(value) {
                return String(value || '').replace(/[-_]+/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
            }

            function setColourLabels() {
                [primaryInput, backgroundInput, textInput].forEach(input => {
                    const label = document.querySelector('[data-colour-value="' + input.id + '"]');
                    if (label) label.textContent = input.value;
                });
            }

            function setPresetValues(presetKey, keepCustom) {
                const preset = currentPresets()[presetKey] || currentPresets().default || {};
                const custom = keepCustom ? parseJson(form.dataset.customSettings, {}) : {};

                primaryInput.value = custom.primary || preset.primary || '#2563eb';
                backgroundInput.value = custom.background || preset.background || '#ffffff';
                textInput.value = custom.text || preset.text || '#111827';

                const radius = custom.buttonRadius || preset.buttonRadius || '8px';
                radiusRange.value = parseInt(radius, 10) || 8;
                radiusValue.value = (parseInt(radiusRange.value, 10) || 0) + 'px';

                effectInput.value = custom.effectIntensity || '55';
                shapeInput.value = custom.shapeIntensity || '45';

                const showIcons = (custom.showIcons != null ? custom.showIcons : (preset.showIcons != null ? preset.showIcons : '1'));
                showIconsCheckbox.checked = String(showIcons) !== '0';
                showIconsValue.value = showIconsCheckbox.checked ? '1' : '0';

                const iconColor = (custom.iconColor != null ? custom.iconColor : (preset.iconColor != null ? preset.iconColor : ''));
                const hasIconColor = /^#[0-9A-Fa-f]{6}$/.test(String(iconColor));
                iconColorToggle.checked = hasIconColor;
                iconColorInput.value = hasIconColor ? iconColor : '#ffffff';
                syncIconColor();

                setColourLabels();
                updatePreview();
            }

            function renderPresets(selectedKey, keepCustom) {
                const presets = currentPresets();
                const keys = Object.keys(presets);
                const selected = keys.includes(selectedKey) ? selectedKey : (keys.includes('default') ? 'default' : keys[0]);
                presetList.innerHTML = '';
                presetInput.value = selected || 'default';

                keys.forEach(key => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'll-theme-pill' + (key === presetInput.value ? ' is-active' : '');
                    button.textContent = formatName(key);
                    button.addEventListener('click', () => {
                        presetInput.value = key;
                        form.dataset.customSettings = '{}';
                        renderPresets(key, false);
                    });
                    presetList.appendChild(button);
                });

                setPresetValues(presetInput.value, keepCustom);
            }

            function renderFonts(selectedFont) {
                const fonts = parseJson(activeCard()?.dataset.fonts, ['Inter', 'Poppins', 'Roboto', 'Open Sans', 'Montserrat']).slice(0, 5);
                const selected = selectedFont || fonts[0] || 'Inter';
                fontInput.value = selected;
                fontList.innerHTML = '';

                fonts.forEach(font => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'll-theme-pill' + (font === selected ? ' is-active' : '');
                    button.textContent = font;
                    button.style.fontFamily = '"' + font + '", system-ui, sans-serif';
                    button.addEventListener('click', () => {
                        fontInput.value = font;
                        renderFonts(font);
                        updatePreview();
                    });
                    fontList.appendChild(button);
                });
            }

            function updatePreview() {
                const card = activeCard();
                const radius = (parseInt(radiusRange.value, 10) || 0) + 'px';
                radiusValue.value = radius;
                radiusLabel.textContent = radius;
                effectLabel.textContent = effectInput.value + '%';
                shapeLabel.textContent = shapeInput.value + '%';
                preview.style.setProperty('--ll-preview-primary', primaryInput.value);
                preview.style.setProperty('--ll-preview-background', backgroundInput.value);
                preview.style.setProperty('--ll-preview-text', textInput.value);
                preview.style.setProperty('--ll-preview-radius', radius);
                preview.style.setProperty('--ll-preview-font', '"' + (fontInput.value || 'Inter') + '", system-ui, sans-serif');
                preview.style.setProperty('--ll-preview-effect', (parseInt(effectInput.value, 10) || 0) / 100);
                preview.style.setProperty('--ll-preview-shape', (parseInt(shapeInput.value, 10) || 0) / 100);
                previewScreen.dataset.theme = card?.dataset.themeSlug || 'livelatch-default';
                previewHeading.textContent = card?.dataset.themeName || 'Livelatch Default';
                previewCopy.textContent = formatName(presetInput.value || 'default') + ' preset preview for your public profile.';
                setColourLabels();
            }

            function selectTheme(card, keepCustom) {
                cards.forEach(item => item.classList.toggle('is-active', item === card));
                themeInput.value = card.dataset.themeId || '';
                versionInput.value = card.dataset.versionId || '';
                renderFonts(keepCustom ? fontInput.value : parseJson(card.dataset.fonts, ['Inter'])[0]);
                renderPresets(keepCustom ? presetInput.value : 'default', keepCustom);
            }

            function hideErrors() {
                errorBox?.classList.add('d-none');
                if (errorBox) errorBox.innerHTML = '';
            }

            function showErrors(errors) {
                if (!errorBox) return;
                const messages = [];
                Object.values(errors || { theme: 'Theme settings could not be saved.' }).forEach(value => {
                    Array.isArray(value) ? messages.push(...value) : messages.push(value);
                });
                errorBox.innerHTML = messages.map(message => '<div>' + String(message) + '</div>').join('');
                errorBox.classList.remove('d-none');
            }

            cards.forEach(card => card.addEventListener('click', () => {
                form.dataset.customSettings = '{}';
                selectTheme(card, false);
            }));

            [primaryInput, backgroundInput, textInput, radiusRange, effectInput, shapeInput].forEach(input => {
                input.addEventListener('input', updatePreview);
                input.addEventListener('change', updatePreview);
            });

            showIconsCheckbox.addEventListener('change', () => {
                showIconsValue.value = showIconsCheckbox.checked ? '1' : '0';
            });
            iconColorToggle.addEventListener('change', syncIconColor);
            iconColorInput.addEventListener('input', syncIconColor);

            customFontApply.addEventListener('click', () => {
                const font = customFont.value.trim().replace(/[^A-Za-z0-9 ]/g, '');
                if (!font) return;
                fontInput.value = font;
                renderFonts(font);
                updatePreview();
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                hideErrors();
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving';

                const csrfToken = form.querySelector('input[name="_token"]')?.value
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: new FormData(form)
                })
                    .then(response => {
                        if (response.ok) return response.headers.get('content-type')?.includes('application/json') ? response.json() : {};
                        return response.json().then(data => { throw data; }).catch(error => { throw error.errors ? error : { errors: { theme: 'Theme settings could not be saved.' } }; });
                    })
                    .then(data => {
                        form.dataset.customSettings = JSON.stringify(data.setting?.custom_settings || {});
                        if (window.bootstrap && document.getElementById('theme-saved-modal')) {
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('theme-saved-modal')).show();
                        }
                    })
                    .catch(error => showErrors(error.errors))
                    .finally(() => {
                        saveButton.disabled = false;
                        saveButton.innerHTML = '<i class="bi bi-check2-circle"></i> Save theme';
                    });
            });

            selectTheme(activeCard(), true);
            fontInput.value = '{{ $initialFontFamily }}';
            renderFonts(fontInput.value);
            updatePreview();
        }
    };

    window.LivelatchThemeEditor.init();
</script>
@endsection
