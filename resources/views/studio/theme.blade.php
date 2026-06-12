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
    $initialFontFamily = $customSettings['fontFamily'] ?? $selectedPresetValues['fontFamily'] ?? 'Inter';
@endphp

<style data-ll-theme-editor-style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Merriweather:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

    #theme-preview {
        background: #f8fafc !important;
        color-scheme: light;
        isolation: isolate;
    }

    #theme-preview,
    #theme-preview * {
        box-sizing: border-box;
    }

    #theme-preview .ll-theme-preview-page {
        min-height: 360px;
        background: var(--ll-background) !important;
        color: var(--ll-text) !important;
        border-radius: 12px;
        padding: 32px 20px;
        font-family: var(--ll-font-family) !important;
        transition: background 160ms ease, color 160ms ease, font-family 160ms ease;
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
        background: var(--ll-primary) !important;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    }

    #theme-preview .ll-theme-preview-heading {
        color: var(--ll-text) !important;
        margin-bottom: 8px;
        font-family: var(--ll-font-family) !important;
    }

    #theme-preview .ll-theme-preview-text {
        color: var(--ll-text) !important;
        opacity: 0.82;
        margin-bottom: 20px;
    }

    #theme-preview .ll-theme-preview-button {
        display: block;
        width: 100%;
        padding: 13px 18px;
        border-radius: var(--ll-button-radius);
        background: var(--ll-primary) !important;
        color: #ffffff !important;
        text-decoration: none;
        font-weight: 700;
        margin-bottom: 14px;
        font-family: var(--ll-font-family) !important;
        transition: background 160ms ease, border-radius 160ms ease;
    }

    #theme-preview .ll-theme-preview-link-card {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
        padding: 14px;
        border-radius: var(--ll-button-radius);
        border: 1px solid rgba(17, 24, 39, 0.14);
        background: rgba(255, 255, 255, 0.28);
        color: var(--ll-text) !important;
        transition: background 160ms ease, color 160ms ease, border-radius 160ms ease;
    }

    #theme-preview .ll-theme-preview-link-card p {
        margin: 2px 0 0;
        color: var(--ll-text) !important;
        opacity: 0.72;
        font-size: 0.9rem;
    }

    #theme-preview .ll-theme-preview-link-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--ll-primary) !important;
        flex: 0 0 auto;
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="card rounded">
                <div class="card-body">
                    <h2 class="mb-2">Theme Settings</h2>
                    <p class="text-muted mb-4">Select a published theme preset and customize the default theme styling for your profile.</p>

                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="alert alert-danger d-none" id="theme-settings-errors" role="alert"></div>

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
                        <form
                            method="post"
                            action="{{ route('editTheme') }}"
                            id="theme-settings-form"
                            data-custom-settings='@json($customSettings)'
                        >
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
                                <select class="form-control" id="preset" name="preset" data-selected-preset="{{ $selectedPreset }}">
                                    @foreach($selectedPresets as $presetKey => $preset)
                                        <option value="{{ $presetKey }}" @selected($selectedPreset === $presetKey)>
                                            {{ Str::headline($presetKey) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="theme-primary" class="form-label">Primary colour</label>
                                    <input type="color" class="form-control form-control-color w-100" id="theme-primary" name="custom_settings[primary]" value="{{ $initialPrimary }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="theme-background" class="form-label">Background colour</label>
                                    <input type="color" class="form-control form-control-color w-100" id="theme-background" name="custom_settings[background]" value="{{ $initialBackground }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="theme-text" class="form-label">Text colour</label>
                                    <input type="color" class="form-control form-control-color w-100" id="theme-text" name="custom_settings[text]" value="{{ $initialText }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="theme-font-family" class="form-label">Font family</label>
                                <select class="form-control" id="theme-font-family" name="custom_settings[fontFamily]">
                                    @foreach($fontFamilies as $fontFamily => $label)
                                        <option value="{{ $fontFamily }}" @selected($initialFontFamily === $fontFamily)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <div
                                    id="theme-preview"
                                    class="border rounded p-4"
                                    style="--ll-primary: {{ $initialPrimary }}; --ll-background: {{ $initialBackground }}; --ll-text: {{ $initialText }}; --ll-button-radius: 8px; --ll-font-family: '{{ $initialFontFamily }}', system-ui, sans-serif;"
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

                            <button type="submit" class="btn btn-primary" id="theme-save-button">Save theme settings</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="theme-saved-modal" tabindex="-1" aria-labelledby="theme-saved-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="theme-saved-modal-label">Theme saved</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                Your theme settings have been saved to your public profile.
            </div>
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
            const themeSelect = document.getElementById('theme-id');
            const presetSelect = document.getElementById('preset');
            const versionInput = document.getElementById('theme-version-id');
            const preview = document.getElementById('theme-preview');
            const previewHeading = document.getElementById('theme-preview-heading');
            const previewText = document.getElementById('theme-preview-text');
            const primaryInput = document.getElementById('theme-primary');
            const backgroundInput = document.getElementById('theme-background');
            const textInput = document.getElementById('theme-text');
            const fontInput = document.getElementById('theme-font-family');
            const saveButton = document.getElementById('theme-save-button');
            const errorBox = document.getElementById('theme-settings-errors');

            if (!form || !themeSelect || !presetSelect || !versionInput || !preview || form.dataset.themeEditorInitialized === 'true') {
                return;
            }

            form.dataset.themeEditorInitialized = 'true';

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

            function getCustomSettings() {
                try {
                    return JSON.parse(form.dataset.customSettings || '{}') || {};
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

            function hideErrors() {
                if (!errorBox) {
                    return;
                }

                errorBox.classList.add('d-none');
                errorBox.innerHTML = '';
            }

            function showErrors(errors) {
                if (!errorBox) {
                    return;
                }

                const messages = [];
                if (typeof errors === 'string') {
                    messages.push(errors);
                } else {
                    Object.keys(errors || {}).forEach(function (key) {
                        const value = errors[key];
                        if (Array.isArray(value)) {
                            messages.push.apply(messages, value);
                        } else {
                            messages.push(value);
                        }
                    });
                }

                errorBox.innerHTML = messages.map(function (message) {
                    return '<div>' + String(message) + '</div>';
                }).join('');
                errorBox.classList.remove('d-none');
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

                applyPresetToControls(true);
                updatePreview();
            }

            function applyPresetToControls(useSavedCustomSettings) {
                const presets = getPresets(getSelectedThemeOption());
                const preset = presets[presetSelect.value] || {};
                const customSettings = useSavedCustomSettings ? getCustomSettings() : {};

                primaryInput.value = customSettings.primary || preset.primary || '#2563eb';
                backgroundInput.value = customSettings.background || preset.background || '#ffffff';
                textInput.value = customSettings.text || preset.text || '#111827';
                fontInput.value = customSettings.fontFamily || preset.fontFamily || 'Inter';
            }

            function updatePreview() {
                const presets = getPresets(getSelectedThemeOption());
                const preset = presets[presetSelect.value] || {};
                const primary = primaryInput.value || preset.primary || '#2563eb';
                const background = backgroundInput.value || preset.background || '#ffffff';
                const text = textInput.value || preset.text || '#111827';
                const buttonRadius = preset.buttonRadius || '8px';
                const fontFamily = fontInput.value || preset.fontFamily || 'Inter';

                preview.style.setProperty('--ll-primary', primary);
                preview.style.setProperty('--ll-background', background);
                preview.style.setProperty('--ll-text', text);
                preview.style.setProperty('--ll-button-radius', buttonRadius);
                preview.style.setProperty('--ll-font-family', '"' + fontFamily + '", system-ui, sans-serif');
                previewHeading.textContent = getSelectedThemeOption().textContent.trim();
                previewText.textContent = formatPresetName(presetSelect.value || 'default') + ' preset preview for your public profile.';
            }

            themeSelect.addEventListener('change', function () {
                presetSelect.dataset.selectedPreset = 'default';
                renderPresets();
            });

            presetSelect.addEventListener('change', function () {
                form.dataset.customSettings = '{}';
                applyPresetToControls(false);
                updatePreview();
            });

            [primaryInput, backgroundInput, textInput, fontInput].forEach(function (input) {
                input.addEventListener('input', updatePreview);
                input.addEventListener('change', updatePreview);
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                hideErrors();
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';

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
                    .then(function (response) {
                        const contentType = response.headers.get('content-type') || '';

                        if (!response.ok) {
                            return response.text().then(function (text) {
                                let data = {};

                                if (contentType.includes('application/json')) {
                                    try {
                                        data = JSON.parse(text);
                                    } catch (error) {
                                        data = {};
                                    }
                                }

                                if (data.errors) {
                                    throw data;
                                }

                                throw {
                                    errors: {
                                        theme: 'Theme settings could not be saved. Server returned HTTP ' + response.status + '.'
                                    }
                                };
                            });
                        }

                        if (contentType.includes('application/json')) {
                            return response.json();
                        }

                        return {
                            message: 'Theme settings saved.',
                            setting: {
                                custom_settings: {
                                    primary: primaryInput.value,
                                    background: backgroundInput.value,
                                    text: textInput.value,
                                    fontFamily: fontInput.value
                                }
                            }
                        };
                    })
                    .then(function (data) {
                        form.dataset.customSettings = JSON.stringify(data.setting?.custom_settings || {
                            primary: primaryInput.value,
                            background: backgroundInput.value,
                            text: textInput.value,
                            fontFamily: fontInput.value
                        });

                        if (window.bootstrap && document.getElementById('theme-saved-modal')) {
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('theme-saved-modal')).show();
                        }
                    })
                    .catch(function (error) {
                        showErrors(error.errors || { theme: 'Theme settings could not be saved.' });
                    })
                    .finally(function () {
                        saveButton.disabled = false;
                        saveButton.textContent = 'Save theme settings';
                    });
            });

            renderPresets();
        }
    };

    window.LivelatchThemeEditor.init();
</script>
@endsection
