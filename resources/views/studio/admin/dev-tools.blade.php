@extends('layouts.sidebar')

@section('content')
<style data-ll-dev-tools-style>
    .ll-dev-tools {
        display: grid;
        gap: 18px;
    }

    .ll-dev-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ll-dev-grid {
        display: grid;
        grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .ll-dev-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
    }

    .ll-dev-panel-header,
    .ll-dev-panel-body {
        padding: 18px;
    }

    .ll-dev-panel-header {
        border-bottom: 1px solid var(--ll-border);
    }

    .ll-dev-panel-header h3,
    .ll-dev-panel-header p {
        margin: 0;
    }

    .ll-dev-panel-header p {
        color: var(--ll-muted);
        font-size: 0.9rem;
        margin-top: 4px;
    }

    .ll-dev-controls {
        display: grid;
        gap: 14px;
    }

    .ll-dev-control {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
    }

    .ll-dev-control label {
        display: grid;
        gap: 3px;
        margin: 0;
        font-weight: 800;
        color: var(--ll-text);
    }

    .ll-dev-control span {
        color: var(--ll-muted);
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ll-dev-control input[type="color"] {
        width: 46px;
        height: 38px;
        padding: 3px;
        border: 1px solid var(--ll-border);
        border-radius: 12px;
        background: var(--ll-bg-soft);
    }

    .ll-dev-control input[type="range"] {
        width: min(180px, 36vw);
        accent-color: var(--ll-primary);
    }

    .ll-dev-value {
        min-width: 62px;
        text-align: right;
        color: var(--ll-muted);
        font-size: 0.82rem;
        font-weight: 800;
    }

    .ll-dev-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ll-dev-mode-toggle {
        display: inline-flex;
        padding: 4px;
        gap: 4px;
        border: 1px solid var(--ll-border);
        border-radius: 999px;
        background: var(--ll-bg-soft);
    }

    .ll-dev-mode-toggle button {
        min-height: 36px;
        padding: 0 14px;
        border: 0;
        border-radius: 999px;
        color: var(--ll-muted);
        background: transparent;
        font-weight: 800;
    }

    .ll-dev-mode-toggle button.is-active {
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }

    .ll-dev-preview {
        display: grid;
        gap: 16px;
    }

    .ll-dev-preview-hero {
        min-height: 220px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        padding: clamp(22px, 4vw, 42px);
        border-radius: var(--ll-radius);
        color: var(--ll-text);
        background:
            radial-gradient(circle at 20% 0%, color-mix(in srgb, var(--ll-primary) 20%, transparent), transparent 34%),
            linear-gradient(135deg, var(--ll-bg-soft), var(--ll-surface-solid));
        border: 1px solid var(--ll-border);
    }

    .ll-dev-preview-hero h2 {
        margin: 0;
        font-size: clamp(2rem, 5vw, 4.3rem);
        line-height: 1;
    }

    .ll-dev-preview-hero p {
        margin: 0;
        color: var(--ll-muted);
        max-width: 640px;
    }

    .ll-dev-preview-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .ll-dev-preview-card {
        min-height: 132px;
        display: grid;
        gap: 10px;
        padding: 16px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
    }

    .ll-dev-preview-chip {
        width: max-content;
        border-radius: 999px;
        padding: 6px 10px;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 0.78rem;
        font-weight: 800;
    }

    .ll-dev-instructions {
        width: 100%;
        min-height: 250px;
        resize: vertical;
        color: var(--ll-text);
        background: var(--ll-bg-soft);
        border: 1px solid var(--ll-border);
        border-radius: 14px;
        padding: 14px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 0.86rem;
    }

    @media (max-width: 991.98px) {
        .ll-dev-grid,
        .ll-dev-preview-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div id="ll-dev-tools" class="ll-dev-tools">
        <div class="ll-dev-header">
            <div>
                <h2 class="mb-1">Dev Tools</h2>
                <p class="text-muted mb-0">Temporary live design token editor for light and dark mode.</p>
            </div>
            <div class="ll-dev-actions">
                <div class="ll-dev-mode-toggle" aria-label="Preview colour mode">
                    <button type="button" data-ll-dev-mode="light">
                        <i class="bi bi-sun-fill"></i>
                        Light
                    </button>
                    <button type="button" data-ll-dev-mode="dark">
                        <i class="bi bi-moon-stars-fill"></i>
                        Dark
                    </button>
                </div>
                <button type="button" class="btn btn-light" id="ll-dev-reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
                <button type="button" class="btn btn-primary" id="ll-dev-generate">
                    <i class="bi bi-terminal"></i>
                    Generate Codex Instructions
                </button>
            </div>
        </div>

        <div class="ll-dev-grid">
            <div class="ll-dev-panel">
                <div class="ll-dev-panel-header">
                    <h3>Live Tokens</h3>
                    <p id="ll-dev-mode-help">Editing the active mode also generates a matching opposite mode.</p>
                </div>
                <div class="ll-dev-panel-body">
                    <div class="ll-dev-controls" id="ll-dev-controls">
                        <div class="ll-dev-control">
                            <label for="ll-dev-primary">Primary <span>--ll-primary</span></label>
                            <input id="ll-dev-primary" type="color" data-token="--ll-primary">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-primary-2">Secondary <span>--ll-primary-2</span></label>
                            <input id="ll-dev-primary-2" type="color" data-token="--ll-primary-2">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-primary-3">Accent <span>--ll-primary-3</span></label>
                            <input id="ll-dev-primary-3" type="color" data-token="--ll-primary-3">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-bg">Background <span>--ll-bg</span></label>
                            <input id="ll-dev-bg" type="color" data-token="--ll-bg">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-bg-soft">Soft Background <span>--ll-bg-soft</span></label>
                            <input id="ll-dev-bg-soft" type="color" data-token="--ll-bg-soft">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-surface">Surface <span>--ll-surface-solid</span></label>
                            <input id="ll-dev-surface" type="color" data-token="--ll-surface-solid">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-text">Text <span>--ll-text</span></label>
                            <input id="ll-dev-text" type="color" data-token="--ll-text">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-muted">Muted Text <span>--ll-muted</span></label>
                            <input id="ll-dev-muted" type="color" data-token="--ll-muted">
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-radius">Surface Radius <span>--ll-radius</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-radius" type="range" min="0" max="36" step="1" data-token="--ll-radius" data-unit="px">
                                <output class="ll-dev-value" for="ll-dev-radius"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-button-radius">Button Radius <span>--ll-button-radius</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-button-radius" type="range" min="0" max="32" step="1" data-token="--ll-button-radius" data-unit="px">
                                <output class="ll-dev-value" for="ll-dev-button-radius"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-heading-weight">Heading Weight <span>temporary rule</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-heading-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-heading-weight">
                                <output class="ll-dev-value" for="ll-dev-heading-weight"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-button-weight">Button Weight <span>temporary rule</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-button-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-button-weight">
                                <output class="ll-dev-value" for="ll-dev-button-weight"></output>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll-dev-preview">
                <div class="ll-dev-preview-hero">
                    <span class="ll-dev-preview-chip">Livelatch Studio</span>
                    <h2>Live Editor Preview</h2>
                    <p>Cards, buttons, text, surfaces, and navigation tokens update immediately.</p>
                    <div class="ll-dev-actions">
                        <button type="button" class="btn btn-primary">
                            <i class="bi bi-check2-circle"></i>
                            Primary
                        </button>
                        <button type="button" class="btn btn-light">
                            <i class="bi bi-sliders"></i>
                            Secondary
                        </button>
                    </div>
                </div>

                <div class="ll-dev-preview-cards">
                    <div class="ll-dev-preview-card">
                        <span class="text-muted">Surface</span>
                        <h4 class="mb-0">Card Radius</h4>
                        <p class="mb-0 text-muted">Uses the same token as dashboard panels.</p>
                    </div>
                    <div class="ll-dev-preview-card">
                        <span class="text-muted">Type</span>
                        <h4 class="mb-0">Font Weight</h4>
                        <p class="mb-0 text-muted">Temporary rules preview heading and button weight.</p>
                    </div>
                    <div class="ll-dev-preview-card">
                        <span class="text-muted">Colour</span>
                        <h4 class="mb-0">Primary Tokens</h4>
                        <p class="mb-0 text-muted">Gradients and highlights react to the selected values.</p>
                    </div>
                </div>

                <div class="ll-dev-panel">
                    <div class="ll-dev-panel-header">
                        <h3>Codex Instructions</h3>
                        <p id="ll-dev-copy-status">Generated text will appear here.</p>
                    </div>
                    <div class="ll-dev-panel-body">
                        <textarea id="ll-dev-instructions" class="ll-dev-instructions" readonly></textarea>
                        <div class="ll-dev-actions mt-3">
                            <button type="button" class="btn btn-light" id="ll-dev-copy">
                                <i class="bi bi-clipboard"></i>
                                Copy Instructions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById('ll-dev-tools');

        if (!root || root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        const docRoot = document.documentElement;
        const body = document.body;
        const controls = Array.from(root.querySelectorAll('[data-token]'));
        const modeButtons = Array.from(root.querySelectorAll('[data-ll-dev-mode]'));
        const instructions = root.querySelector('#ll-dev-instructions');
        const generateButton = root.querySelector('#ll-dev-generate');
        const copyButton = root.querySelector('#ll-dev-copy');
        const resetButton = root.querySelector('#ll-dev-reset');
        const copyStatus = root.querySelector('#ll-dev-copy-status');
        const modeHelp = root.querySelector('#ll-dev-mode-help');
        const temporaryStyleId = 'll-dev-tools-temporary-style';
        const colorTokens = [
            '--ll-primary',
            '--ll-primary-2',
            '--ll-primary-3',
            '--ll-bg',
            '--ll-bg-soft',
            '--ll-surface-solid',
            '--ll-text',
            '--ll-muted',
        ];
        const sharedTokens = [
            '--ll-radius',
            '--ll-button-radius',
            '--ll-dev-heading-weight',
            '--ll-dev-button-weight',
        ];
        const lightnessTargets = {
            light: {
                '--ll-bg': 97,
                '--ll-bg-soft': 100,
                '--ll-surface-solid': 100,
                '--ll-text': 12,
                '--ll-muted': 45,
            },
            dark: {
                '--ll-bg': 7,
                '--ll-bg-soft': 10,
                '--ll-surface-solid': 13,
                '--ll-text': 96,
                '--ll-muted': 72,
            },
        };
        const defaultValues = {
            '--ll-button-radius': '12px',
            '--ll-dev-heading-weight': '700',
            '--ll-dev-button-weight': '800',
        };
        const originalBodyTheme = body.getAttribute('data-ll-theme') || 'light';
        const originalStorageTheme = localStorage.getItem('livelatch-theme');
        const originalInlineValues = {};
        const originalBodyInlineValues = {};
        const originalState = { light: {}, dark: {}, shared: {} };
        const draftState = { light: {}, dark: {}, shared: {} };
        let activeMode = originalBodyTheme === 'dark' ? 'dark' : 'light';

        function computedToken(token) {
            return getComputedStyle(body).getPropertyValue(token).trim()
                || getComputedStyle(docRoot).getPropertyValue(token).trim();
        }

        function normalizeHex(value, fallback = '#000000') {
            const text = (value || '').trim();

            if (/^#[0-9a-f]{6}$/i.test(text)) {
                return text;
            }

            if (/^#[0-9a-f]{3}$/i.test(text)) {
                return '#' + text.slice(1).split('').map((char) => char + char).join('');
            }

            const rgb = text.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/i);

            if (!rgb) {
                return fallback;
            }

            return '#' + rgb.slice(1, 4).map((part) => Number(part).toString(16).padStart(2, '0')).join('');
        }

        function hexToRgb(hex) {
            const normalized = normalizeHex(hex);

            return {
                r: parseInt(normalized.slice(1, 3), 16),
                g: parseInt(normalized.slice(3, 5), 16),
                b: parseInt(normalized.slice(5, 7), 16),
            };
        }

        function rgbToHex(r, g, b) {
            return '#' + [r, g, b].map((part) => Math.max(0, Math.min(255, Math.round(part))).toString(16).padStart(2, '0')).join('');
        }

        function rgbToHsl({ r, g, b }) {
            r /= 255;
            g /= 255;
            b /= 255;

            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h = 0;
            let s = 0;
            const l = (max + min) / 2;

            if (max !== min) {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

                switch (max) {
                    case r:
                        h = (g - b) / d + (g < b ? 6 : 0);
                        break;
                    case g:
                        h = (b - r) / d + 2;
                        break;
                    default:
                        h = (r - g) / d + 4;
                        break;
                }

                h /= 6;
            }

            return { h: h * 360, s: s * 100, l: l * 100 };
        }

        function hslToHex(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;

            if (s === 0) {
                const gray = l * 255;
                return rgbToHex(gray, gray, gray);
            }

            const hueToRgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1 / 6) return p + (q - p) * 6 * t;
                if (t < 1 / 2) return q;
                if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                return p;
            };
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;

            return rgbToHex(
                hueToRgb(p, q, h + 1 / 3) * 255,
                hueToRgb(p, q, h) * 255,
                hueToRgb(p, q, h - 1 / 3) * 255
            );
        }

        function pairedColor(token, value, targetMode) {
            const hsl = rgbToHsl(hexToRgb(value));

            if (lightnessTargets[targetMode][token] !== undefined) {
                const saturation = ['--ll-bg', '--ll-bg-soft', '--ll-surface-solid'].includes(token)
                    ? Math.min(hsl.s, targetMode === 'dark' ? 45 : 28)
                    : hsl.s;

                return hslToHex(hsl.h, saturation, lightnessTargets[targetMode][token]);
            }

            const lightness = targetMode === 'dark'
                ? Math.min(72, Math.max(48, hsl.l + 12))
                : Math.min(64, Math.max(38, hsl.l - 8));

            return hslToHex(hsl.h, hsl.s, lightness);
        }

        function numericValue(value, fallback) {
            const parsed = parseInt((value || '').replace(/[^\d.-]/g, ''), 10);
            return Number.isFinite(parsed) ? parsed : fallback;
        }

        function updateOutput(input) {
            const output = root.querySelector('output[for="' + input.id + '"]');

            if (output) {
                output.textContent = input.value + (input.dataset.unit || '');
            }
        }

        function setBodyTheme(theme, persist = false) {
            body.setAttribute('data-ll-theme', theme);

            if (persist) {
                localStorage.setItem('livelatch-theme', theme);
            }
        }

        function captureModeTokens(mode) {
            setBodyTheme(mode, false);

            colorTokens.forEach((token) => {
                originalState[mode][token] = normalizeHex(computedToken(token), '#000000');
                draftState[mode][token] = originalState[mode][token];
            });
        }

        function captureOriginalState() {
            captureModeTokens('light');
            captureModeTokens('dark');

            colorTokens.concat(sharedTokens).forEach((token) => {
                originalInlineValues[token] = docRoot.style.getPropertyValue(token);
                originalBodyInlineValues[token] = body.style.getPropertyValue(token);
            });

            sharedTokens.forEach((token) => {
                originalState.shared[token] = computedToken(token) || defaultValues[token] || '';
                draftState.shared[token] = originalState.shared[token];
            });

            setBodyTheme(activeMode, false);
        }

        function ensureTemporaryStyle() {
            let style = document.getElementById(temporaryStyleId);

            if (!style) {
                style = document.createElement('style');
                style.id = temporaryStyleId;
                document.head.appendChild(style);
            }

            style.textContent = `
                #ll-content h1,
                #ll-content h2,
                #ll-content h3,
                #ll-content h4,
                .ll-hero h1,
                .ll-brand-title {
                    font-weight: var(--ll-dev-heading-weight, 700) !important;
                }

                #ll-content .btn,
                #ll-content button,
                .ll-hero-button,
                .ll-pill-button,
                .ll-nav-link {
                    border-radius: var(--ll-button-radius, var(--ll-radius)) !important;
                    font-weight: var(--ll-dev-button-weight, 800) !important;
                }

                #ll-content .card,
                #ll-content .iq-card,
                #ll-content .ll-panel,
                #ll-content .ll-dev-panel,
                #ll-content .ll-dev-preview-card {
                    border-radius: var(--ll-radius) !important;
                }
            `;
        }

        function applyDraftToDocument() {
            Object.entries(draftState[activeMode]).forEach(([token, value]) => {
                docRoot.style.setProperty(token, value);
                body.style.setProperty(token, value);
            });

            Object.entries(draftState.shared).forEach(([token, value]) => {
                if (value) {
                    docRoot.style.setProperty(token, value);
                    body.style.setProperty(token, value);
                }
            });

            ensureTemporaryStyle();
        }

        function syncModeButtons() {
            modeButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.llDevMode === activeMode);
            });

            if (modeHelp) {
                modeHelp.textContent = 'Editing ' + activeMode + ' mode. The opposite mode is auto-generated and can be reviewed with the toggle.';
            }
        }

        function applyInput(input) {
            const token = input.dataset.token;
            const unit = input.dataset.unit || '';
            const value = input.type === 'range' ? input.value + unit : input.value;

            if (colorTokens.includes(token)) {
                const oppositeMode = activeMode === 'dark' ? 'light' : 'dark';
                draftState[activeMode][token] = value;
                draftState[oppositeMode][token] = pairedColor(token, value, oppositeMode);
            } else {
                draftState.shared[token] = value;
            }

            updateOutput(input);
            applyDraftToDocument();
            buildInstructions();
            syncModeButtons();
        }

        function setControlValuesFromDraft(bindEvents = false) {
            controls.forEach((input) => {
                const token = input.dataset.token;
                const current = colorTokens.includes(token)
                    ? draftState[activeMode][token]
                    : draftState.shared[token];

                if (input.type === 'color') {
                    input.value = normalizeHex(current, input.value || '#000000');
                } else {
                    input.value = numericValue(current, numericValue(defaultValues[token], 0));
                }

                updateOutput(input);

                if (bindEvents) {
                    input.addEventListener('input', () => applyInput(input));
                }
            });
        }

        function formatTokenLines(values) {
            return Object.entries(values)
                .map(([token, value]) => '- `' + token + '`: `' + value + '`')
                .join('\n');
        }

        function buildInstructions() {
            instructions.value = `Task: Apply the approved Livelatch Studio design token changes from the admin Dev Tools preview.

Context:
- The Dev Tools page is view-only and does not save anything.
- The live preview was tested in /admin/dev-tools.
- The preview has separate light and dark mode colour values.
- Update the real source carefully and keep the existing light/dark mode behavior intact.

Primary target:
- resources/views/layouts/sidebar.blade.php

Requested light mode colour values:
${formatTokenLines(draftState.light)}

Requested dark mode colour values:
${formatTokenLines(draftState.dark)}

Requested shared shape/type values:
${formatTokenLines(draftState.shared)}

Implementation notes:
- Update the matching :root Studio CSS variables for light mode where they already exist.
- Update the matching [data-ll-theme="dark"] Studio CSS variables for dark mode.
- Add --ll-button-radius if needed and use it for dashboard buttons while preserving --ll-radius for surfaces.
- For temporary font-weight preview tokens, convert them into stable CSS rules for headings and buttons if the values are approved.
- Do not change public profile theme presets unless explicitly requested; those are resolved through ThemeService and ThemeSeeder.
- Run php -l on changed Blade/PHP files and php artisan view:cache.
- Update summary.md and relevant docs if the final implementation changes the design-token system.`;

            copyStatus.textContent = 'Instructions generated from current token values.';
        }

        function switchMode(mode) {
            activeMode = mode === 'dark' ? 'dark' : 'light';
            setBodyTheme(activeMode, true);
            applyDraftToDocument();
            setControlValuesFromDraft(false);
            buildInstructions();
            syncModeButtons();
        }

        function resetPreview() {
            Object.keys(draftState.light).forEach((token) => delete draftState.light[token]);
            Object.keys(draftState.dark).forEach((token) => delete draftState.dark[token]);
            Object.keys(draftState.shared).forEach((token) => delete draftState.shared[token]);
            Object.assign(draftState.light, originalState.light);
            Object.assign(draftState.dark, originalState.dark);
            Object.assign(draftState.shared, originalState.shared);

            const style = document.getElementById(temporaryStyleId);

            if (style) {
                style.remove();
            }

            applyDraftToDocument();
            setControlValuesFromDraft(false);
            buildInstructions();
            syncModeButtons();
            copyStatus.textContent = 'Preview reset to the values found when this page loaded.';
        }

        function cleanupPreview() {
            colorTokens.concat(sharedTokens).forEach((token) => {
                if (originalInlineValues[token]) {
                    docRoot.style.setProperty(token, originalInlineValues[token]);
                } else {
                    docRoot.style.removeProperty(token);
                }

                if (originalBodyInlineValues[token]) {
                    body.style.setProperty(token, originalBodyInlineValues[token]);
                } else {
                    body.style.removeProperty(token);
                }
            });

            const style = document.getElementById(temporaryStyleId);

            if (style) {
                style.remove();
            }

            setBodyTheme(originalBodyTheme, false);

            if (originalStorageTheme) {
                localStorage.setItem('livelatch-theme', originalStorageTheme);
            } else {
                localStorage.removeItem('livelatch-theme');
            }

            document.body.removeEventListener('htmx:beforeSwap', cleanupBeforeSwap);
        }

        function cleanupBeforeSwap(event) {
            if (event.detail.target && event.detail.target.id === 'll-content') {
                cleanupPreview();
            }
        }

        generateButton?.addEventListener('click', buildInstructions);
        resetButton?.addEventListener('click', resetPreview);
        modeButtons.forEach((button) => {
            button.addEventListener('click', () => switchMode(button.dataset.llDevMode));
        });
        copyButton?.addEventListener('click', async () => {
            buildInstructions();

            try {
                await navigator.clipboard.writeText(instructions.value);
                copyStatus.textContent = 'Instructions copied.';
            } catch (error) {
                instructions.focus();
                instructions.select();
                copyStatus.textContent = 'Copy blocked by the browser. Select the text manually.';
            }
        });

        captureOriginalState();
        setControlValuesFromDraft(true);
        applyDraftToDocument();
        buildInstructions();
        syncModeButtons();

        document.body.addEventListener('htmx:beforeSwap', cleanupBeforeSwap);
        window.addEventListener('beforeunload', cleanupPreview);
    })();
</script>
@endsection
