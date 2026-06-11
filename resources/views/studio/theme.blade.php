@extends('layouts.sidebar')

@section('content')
@php
    $selectedThemeId = old('theme_id', $currentSetting?->theme_id ?? $themes->first()?->id);
    $selectedVersionId = old('theme_version_id', $currentSetting?->theme_version_id ?? $themes->first()?->currentVersion?->id);
    $selectedPreset = old('preset', $currentSetting?->preset ?? 'default');
@endphp

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card rounded">
                <div class="card-body">
                    <h2 class="mb-2">Theme Settings</h2>
                    <p class="text-muted mb-4">Select a published theme preset and save it to your profile.</p>

                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if($themes->isEmpty())
                        <div class="alert alert-warning" role="alert">
                            No published themes are available yet.
                        </div>
                    @else
                        <form method="post" action="{{ route('editTheme') }}" id="theme-settings-form">
                            @csrf

                            <input type="hidden" name="theme_version_id" id="theme-version-id" value="{{ $selectedVersionId }}">

                            <div class="mb-3">
                                <label for="theme-id" class="form-label">Current theme</label>
                                <select class="form-control" id="theme-id" name="theme_id">
                                    @foreach($themes as $theme)
                                        @php
                                            $version = $theme->currentVersion;
                                            $presets = $version?->manifest['presets'] ?? [];
                                        @endphp
                                        <option
                                            value="{{ $theme->id }}"
                                            data-version-id="{{ $version?->id }}"
                                            data-presets='@json($presets)'
                                            @selected((int) $selectedThemeId === $theme->id)
                                        >
                                            {{ $theme->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="preset" class="form-label">Preset</label>
                                <select class="form-control" id="preset" name="preset" data-selected-preset="{{ $selectedPreset }}"></select>
                            </div>

                            <div class="mb-4">
                                <div
                                    id="theme-preview"
                                    class="border rounded p-4"
                                    style="--ll-primary: #2563eb; --ll-background: #ffffff; --ll-text: #111827; --ll-button-radius: 8px;"
                                >
                                    <div class="ll-theme-preview-page">
                                        <div class="ll-theme-preview-profile">
                                            <div class="ll-theme-preview-avatar"></div>
                                            <h3 id="theme-preview-heading" class="ll-theme-preview-heading">Livelatch Default</h3>
                                            <p id="theme-preview-text" class="ll-theme-preview-text">Preset preview for your public profile.</p>

                                            <a id="theme-preview-button" class="ll-theme-preview-button" href="#" onclick="return false;">
                                                Sample button
                                            </a>

                                            <div id="theme-preview-link-card" class="ll-theme-preview-link-card">
                                                <span class="ll-theme-preview-link-icon"></span>
                                                <div>
                                                    <strong>Sample link card</strong>
                                                    <p>Creator link preview using this preset.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save theme settings</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('sidebar-scripts')
<style>
    #theme-preview {
        background: #f8fafc;
    }

    #theme-preview .ll-theme-preview-page {
        min-height: 360px;
        background: var(--ll-background);
        color: var(--ll-text);
        border-radius: 12px;
        padding: 32px 20px;
        transition: background 160ms ease, color 160ms ease;
    }

    #theme-preview .ll-theme-preview-profile {
        max-width: 360px;
        margin: 0 auto;
        text-align: center;
    }

    #theme-preview .ll-theme-preview-avatar {
        width: 72px;
        height: 72px;
        border-radius: 999px;
        margin: 0 auto 16px;
        background: var(--ll-primary);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    }

    #theme-preview .ll-theme-preview-heading {
        color: var(--ll-text);
        margin-bottom: 8px;
    }

    #theme-preview .ll-theme-preview-text {
        color: var(--ll-text);
        opacity: 0.82;
        margin-bottom: 20px;
    }

    #theme-preview .ll-theme-preview-button {
        display: block;
        width: 100%;
        padding: 13px 18px;
        border-radius: var(--ll-button-radius);
        background: var(--ll-primary);
        color: #ffffff;
        text-decoration: none;
        font-weight: 700;
        margin-bottom: 14px;
        transition: background 160ms ease, border-radius 160ms ease;
    }

    #theme-preview .ll-theme-preview-link-card {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
        padding: 14px;
        border-radius: var(--ll-button-radius);
        border: 1px solid color-mix(in srgb, var(--ll-text) 18%, transparent);
        background: color-mix(in srgb, var(--ll-background) 86%, var(--ll-primary));
        color: var(--ll-text);
        transition: background 160ms ease, color 160ms ease, border-radius 160ms ease;
    }

    #theme-preview .ll-theme-preview-link-card p {
        margin: 2px 0 0;
        color: var(--ll-text);
        opacity: 0.72;
        font-size: 0.9rem;
    }

    #theme-preview .ll-theme-preview-link-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--ll-primary);
        flex: 0 0 auto;
    }
</style>
<script>
    (function () {
        const themeSelect = document.getElementById('theme-id');
        const presetSelect = document.getElementById('preset');
        const versionInput = document.getElementById('theme-version-id');
        const preview = document.getElementById('theme-preview');
        const previewHeading = document.getElementById('theme-preview-heading');
        const previewText = document.getElementById('theme-preview-text');

        if (!themeSelect || !presetSelect || !versionInput || !preview) {
            return;
        }

        function getSelectedThemeOption() {
            return themeSelect.options[themeSelect.selectedIndex];
        }

        function getPresets(option) {
            try {
                return JSON.parse(option.dataset.presets || '{}');
            } catch (error) {
                return {};
            }
        }

        function formatPresetName(value) {
            return value
                .replace(/[-_]+/g, ' ')
                .replace(/\b\w/g, function (letter) {
                    return letter.toUpperCase();
                });
        }

        function renderPresets() {
            const option = getSelectedThemeOption();
            const presets = getPresets(option);
            const previous = presetSelect.value || presetSelect.dataset.selectedPreset || 'default';

            versionInput.value = option.dataset.versionId || '';
            presetSelect.innerHTML = '';

            Object.keys(presets).forEach(function (presetKey) {
                const presetOption = document.createElement('option');
                presetOption.value = presetKey;
                presetOption.textContent = formatPresetName(presetKey);
                presetOption.selected = presetKey === previous;
                presetSelect.appendChild(presetOption);
            });

            if (!presetSelect.value && presetSelect.options.length > 0) {
                presetSelect.options[0].selected = true;
            }

            updatePreview();
        }

        function updatePreview() {
            const presets = getPresets(getSelectedThemeOption());
            const preset = presets[presetSelect.value] || {};
            const background = preset.background || '#ffffff';
            const text = preset.text || '#111827';
            const primary = preset.primary || '#2563eb';
            const buttonRadius = preset.buttonRadius || '8px';

            preview.style.setProperty('--ll-primary', primary);
            preview.style.setProperty('--ll-background', background);
            preview.style.setProperty('--ll-text', text);
            preview.style.setProperty('--ll-button-radius', buttonRadius);
            previewHeading.textContent = getSelectedThemeOption().textContent.trim();
            previewText.textContent = formatPresetName(presetSelect.value) + ' preset preview for your public profile.';
        }

        themeSelect.addEventListener('change', renderPresets);
        presetSelect.addEventListener('change', updatePreview);
        renderPresets();
    })();
</script>
@endpush
@endsection
