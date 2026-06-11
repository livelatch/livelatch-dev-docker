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
                                <div id="theme-preview" class="border rounded p-4">
                                    <h3 id="theme-preview-heading" class="mb-3">Livelatch Default</h3>
                                    <p id="theme-preview-text" class="mb-3">A simple preview using the selected preset values.</p>
                                    <span id="theme-preview-button" class="d-inline-block px-4 py-2">Sample button</span>
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
<script>
    (function () {
        const themeSelect = document.getElementById('theme-id');
        const presetSelect = document.getElementById('preset');
        const versionInput = document.getElementById('theme-version-id');
        const preview = document.getElementById('theme-preview');
        const previewHeading = document.getElementById('theme-preview-heading');
        const previewText = document.getElementById('theme-preview-text');
        const previewButton = document.getElementById('theme-preview-button');

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

            preview.style.background = background;
            preview.style.color = text;
            previewHeading.style.color = text;
            previewText.style.color = text;
            previewButton.style.background = primary;
            previewButton.style.color = '#ffffff';
            previewButton.style.borderRadius = buttonRadius;
        }

        themeSelect.addEventListener('change', renderPresets);
        presetSelect.addEventListener('change', updatePreview);
        renderPresets();
    })();
</script>
@endpush
@endsection
