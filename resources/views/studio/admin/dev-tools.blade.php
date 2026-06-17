@extends('layouts.sidebar')

@section('content')
<style data-ll-dev-tools-style>
    .ll-dev-tools { display: grid; gap: 18px; }

    .ll-dev-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .ll-dev-header h2 { margin: 0 0 4px; color: var(--ll-text); }
    .ll-dev-header p { margin: 0; color: var(--ll-muted); }

    .ll-dev-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

    .ll-dev-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid var(--ll-border);
        border-radius: 999px;
        background: var(--ll-surface-solid);
        color: var(--ll-text);
        font-weight: 600;
        cursor: pointer;
        transition: border-color 150ms ease, background 150ms ease;
    }
    .ll-dev-toggle .ll-dev-toggle-dot {
        width: 34px; height: 20px;
        border-radius: 999px;
        background: color-mix(in srgb, var(--ll-text) 18%, transparent);
        position: relative;
        transition: background 160ms ease;
    }
    .ll-dev-toggle .ll-dev-toggle-dot::after {
        content: ""; position: absolute; top: 3px; left: 3px;
        width: 14px; height: 14px; border-radius: 999px; background: #fff;
        transition: transform 160ms ease;
    }
    .ll-dev-toggle.is-on { border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }
    .ll-dev-toggle.is-on .ll-dev-toggle-dot { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-dev-toggle.is-on .ll-dev-toggle-dot::after { transform: translateX(14px); }

    .ll-dev-btn {
        display: inline-flex; align-items: center; gap: 8px;
        min-height: 40px; padding: 0 16px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-button-radius);
        background: var(--ll-surface-solid);
        color: var(--ll-text);
        font-weight: 600; cursor: pointer;
        transition: transform 150ms ease, border-color 150ms ease;
    }
    .ll-dev-btn:hover { transform: translateY(-1px); }
    .ll-dev-btn-primary {
        border: 0;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        color: #fff;
        box-shadow: 0 12px 26px color-mix(in srgb, var(--ll-primary) 26%, transparent);
    }

    .ll-dev-grid {
        display: grid;
        grid-template-columns: minmax(300px, 400px) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .ll-dev-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
    }
    .ll-dev-panel-header { padding: 18px; border-bottom: 1px solid var(--ll-border); }
    .ll-dev-panel-header h3 { margin: 0; color: var(--ll-text); font-size: 1.05rem; }
    .ll-dev-panel-header p { margin: 4px 0 0; color: var(--ll-muted); font-size: 0.88rem; }
    .ll-dev-panel-body { padding: 18px; }

    /* Mode tabs for which mode the controls edit */
    .ll-dev-mode-tabs {
        display: inline-flex;
        padding: 4px;
        gap: 4px;
        border: 1px solid var(--ll-border);
        border-radius: 999px;
        background: color-mix(in srgb, var(--ll-bg-soft) 70%, transparent);
        margin-bottom: 16px;
        width: 100%;
    }
    .ll-dev-mode-tabs button {
        flex: 1;
        min-height: 36px;
        border: 0;
        border-radius: 999px;
        color: var(--ll-muted);
        background: transparent;
        font-weight: 600;
        cursor: pointer;
    }
    .ll-dev-mode-tabs button.is-active {
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }

    .ll-dev-controls { display: grid; gap: 14px; }
    .ll-dev-group-label {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em;
        text-transform: uppercase; color: var(--ll-muted);
        margin: 6px 0 -4px;
    }
    .ll-dev-control { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; }
    .ll-dev-control label { display: grid; gap: 2px; margin: 0; font-weight: 600; color: var(--ll-text); font-size: 0.92rem; }
    .ll-dev-control label span { color: var(--ll-muted); font-size: 0.74rem; font-weight: 500; }
    .ll-dev-control input[type="color"] {
        width: 46px; height: 38px; padding: 3px;
        border: 1px solid var(--ll-border); border-radius: 12px;
        background: var(--ll-bg-soft); cursor: pointer;
    }
    .ll-dev-control input[type="range"] { width: min(170px, 36vw); accent-color: var(--ll-primary); }
    .ll-dev-value { min-width: 56px; text-align: right; color: var(--ll-muted); font-size: 0.82rem; font-weight: 700; }

    /* Dual live preview stages */
    .ll-dev-stages { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ll-dev-stage-wrap { display: grid; gap: 8px; }
    .ll-dev-stage-title { display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--ll-text); font-size: 0.9rem; }
    .ll-dev-stage-title.is-editing::after {
        content: "editing"; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.04em;
        text-transform: uppercase; color: var(--ll-primary);
        background: color-mix(in srgb, var(--ll-primary) 12%, transparent);
        border-radius: 999px; padding: 2px 8px;
    }

    .ll-dev-stage {
        border: 1px solid var(--ll-border);
        border-radius: 22px;
        background: var(--ll-bg);
        color: var(--ll-text);
        padding: 16px;
        display: grid;
        gap: 12px;
        font-family: var(--ll-font);
        overflow: hidden;
    }
    .ll-dev-stage .lls-hero {
        border-radius: var(--ll-radius);
        padding: 20px;
        border: 1px solid var(--ll-border);
        background:
            radial-gradient(circle at 18% 0%, color-mix(in srgb, var(--ll-primary) 22%, transparent), transparent 42%),
            var(--ll-surface-solid);
        display: grid; gap: 9px;
    }
    .ll-dev-stage .lls-chip {
        width: max-content; padding: 5px 11px; border-radius: 999px; color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 0.72rem; font-weight: 600;
    }
    .ll-dev-stage h3 { margin: 0; color: var(--ll-text); font-size: 1.45rem; font-weight: var(--ll-dev-heading-weight, 700); }
    .ll-dev-stage p { margin: 0; color: var(--ll-muted); font-size: 0.88rem; }
    .ll-dev-stage .lls-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .ll-dev-stage .lls-btn {
        border: 0; border-radius: var(--ll-button-radius); padding: 9px 14px;
        font-weight: var(--ll-dev-button-weight, 600); font-family: inherit; cursor: default; font-size: 0.85rem;
    }
    .ll-dev-stage .lls-btn-primary { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; }
    .ll-dev-stage .lls-btn-ghost { background: var(--ll-surface-solid); color: var(--ll-text); border: 1px solid var(--ll-border); }
    .ll-dev-stage .lls-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ll-dev-stage .lls-card {
        border: 1px solid var(--ll-border); border-radius: calc(var(--ll-radius) - 10px);
        background: var(--ll-surface-solid); padding: 12px; display: grid; gap: 3px;
    }
    .ll-dev-stage .lls-card strong { color: var(--ll-text); font-size: 0.9rem; }
    .ll-dev-stage .lls-card span { color: var(--ll-muted); font-size: 0.76rem; }

    .ll-dev-instructions {
        width: 100%; min-height: 240px; resize: vertical;
        color: var(--ll-text); background: var(--ll-bg-soft);
        border: 1px solid var(--ll-border); border-radius: 14px; padding: 14px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 0.84rem;
    }

    @media (max-width: 1199.98px) {
        .ll-dev-grid { grid-template-columns: 1fr; }
        .ll-dev-stages { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div id="ll-dev-tools" class="ll-dev-tools">
        <div class="ll-dev-header">
            <div>
                <h2>Dev Tools</h2>
                <p>Hand-craft the light and dark Studio themes side by side. Previews are scoped and never change the live site.</p>
            </div>
            <div class="ll-dev-actions">
                <button type="button" class="ll-dev-toggle" id="ll-dev-glass" aria-pressed="false">
                    <span class="ll-dev-toggle-dot" aria-hidden="true"></span>
                    <span><i class="bi bi-droplet-half"></i> Liquid glass</span>
                </button>
                <button type="button" class="ll-dev-btn" id="ll-dev-reset">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="button" class="ll-dev-btn ll-dev-btn-primary" id="ll-dev-generate">
                    <i class="bi bi-terminal"></i> Generate Codex Instructions
                </button>
            </div>
        </div>

        <div class="ll-dev-grid">
            <div class="ll-dev-panel">
                <div class="ll-dev-panel-header">
                    <h3>Token editor</h3>
                    <p id="ll-dev-mode-help">Pick a mode to edit. Both previews update live and stay independent.</p>
                </div>
                <div class="ll-dev-panel-body">
                    <div class="ll-dev-mode-tabs" role="tablist" aria-label="Mode to edit">
                        <button type="button" data-ll-dev-mode="light" role="tab"><i class="bi bi-sun-fill"></i> Light</button>
                        <button type="button" data-ll-dev-mode="dark" role="tab"><i class="bi bi-moon-stars-fill"></i> Dark</button>
                    </div>

                    <div class="ll-dev-controls" id="ll-dev-controls">
                        <div class="ll-dev-group-label">Colour</div>
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
                            <label for="ll-dev-bg-soft">Soft background <span>--ll-bg-soft</span></label>
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
                            <label for="ll-dev-muted">Muted text <span>--ll-muted</span></label>
                            <input id="ll-dev-muted" type="color" data-token="--ll-muted">
                        </div>

                        <div class="ll-dev-group-label">Shape &amp; type (shared)</div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-radius">Surface radius <span>--ll-radius</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-radius" type="range" min="0" max="40" step="1" data-token="--ll-radius" data-unit="px" data-shared="true">
                                <output class="ll-dev-value" for="ll-dev-radius"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-button-radius">Button radius <span>--ll-button-radius</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-button-radius" type="range" min="0" max="32" step="1" data-token="--ll-button-radius" data-unit="px" data-shared="true">
                                <output class="ll-dev-value" for="ll-dev-button-radius"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-heading-weight">Heading weight <span>--ll-dev-heading-weight</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-heading-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-heading-weight" data-shared="true">
                                <output class="ll-dev-value" for="ll-dev-heading-weight"></output>
                            </div>
                        </div>
                        <div class="ll-dev-control">
                            <label for="ll-dev-button-weight">Button weight <span>--ll-dev-button-weight</span></label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="ll-dev-button-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-button-weight" data-shared="true">
                                <output class="ll-dev-value" for="ll-dev-button-weight"></output>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll-dev-preview">
                <div class="ll-dev-stages">
                    <div class="ll-dev-stage-wrap">
                        <div class="ll-dev-stage-title" data-ll-stage-title="light"><i class="bi bi-sun-fill"></i> Light mode</div>
                        <div class="ll-dev-stage" data-ll-theme="light" id="ll-dev-stage-light">
                            <div class="lls-hero">
                                <span class="lls-chip">Livelatch Studio</span>
                                <h3>Your creator space</h3>
                                <p>Cards, buttons, text and surfaces preview with these exact tokens.</p>
                                <div class="lls-btns">
                                    <button type="button" class="lls-btn lls-btn-primary">Primary</button>
                                    <button type="button" class="lls-btn lls-btn-ghost">Secondary</button>
                                </div>
                            </div>
                            <div class="lls-cards">
                                <div class="lls-card"><strong>Surface</strong><span>Panel background &amp; radius</span></div>
                                <div class="lls-card"><strong>Text</strong><span>Heading &amp; muted contrast</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="ll-dev-stage-wrap">
                        <div class="ll-dev-stage-title" data-ll-stage-title="dark"><i class="bi bi-moon-stars-fill"></i> Dark mode</div>
                        <div class="ll-dev-stage" data-ll-theme="dark" id="ll-dev-stage-dark">
                            <div class="lls-hero">
                                <span class="lls-chip">Livelatch Studio</span>
                                <h3>Your creator space</h3>
                                <p>Cards, buttons, text and surfaces preview with these exact tokens.</p>
                                <div class="lls-btns">
                                    <button type="button" class="lls-btn lls-btn-primary">Primary</button>
                                    <button type="button" class="lls-btn lls-btn-ghost">Secondary</button>
                                </div>
                            </div>
                            <div class="lls-cards">
                                <div class="lls-card"><strong>Surface</strong><span>Panel background &amp; radius</span></div>
                                <div class="lls-card"><strong>Text</strong><span>Heading &amp; muted contrast</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ll-dev-panel" style="margin-top: 16px;">
                    <div class="ll-dev-panel-header">
                        <h3>Codex instructions</h3>
                        <p id="ll-dev-copy-status">Generated text will appear here.</p>
                    </div>
                    <div class="ll-dev-panel-body">
                        <textarea id="ll-dev-instructions" class="ll-dev-instructions" readonly></textarea>
                        <div class="ll-dev-actions" style="margin-top: 12px;">
                            <button type="button" class="ll-dev-btn" id="ll-dev-copy">
                                <i class="bi bi-clipboard"></i> Copy instructions
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

        const controls = Array.from(root.querySelectorAll('[data-token]'));
        const modeButtons = Array.from(root.querySelectorAll('[data-ll-dev-mode]'));
        const stageTitles = Array.from(root.querySelectorAll('[data-ll-stage-title]'));
        const stages = {
            light: root.querySelector('#ll-dev-stage-light'),
            dark: root.querySelector('#ll-dev-stage-dark'),
        };
        const instructions = root.querySelector('#ll-dev-instructions');
        const generateButton = root.querySelector('#ll-dev-generate');
        const copyButton = root.querySelector('#ll-dev-copy');
        const resetButton = root.querySelector('#ll-dev-reset');
        const copyStatus = root.querySelector('#ll-dev-copy-status');
        const modeHelp = root.querySelector('#ll-dev-mode-help');
        const glassButton = root.querySelector('#ll-dev-glass');

        const colorTokens = ['--ll-primary', '--ll-primary-2', '--ll-primary-3', '--ll-bg', '--ll-bg-soft', '--ll-surface-solid', '--ll-text', '--ll-muted'];
        const sharedTokens = ['--ll-radius', '--ll-button-radius', '--ll-dev-heading-weight', '--ll-dev-button-weight'];
        const sharedDefaults = { '--ll-radius': '36px', '--ll-button-radius': '18px', '--ll-dev-heading-weight': '600', '--ll-dev-button-weight': '400' };

        const baseline = { light: {}, dark: {}, shared: {} };
        const draft = { light: {}, dark: {}, shared: {} };
        let activeMode = 'light';

        function normalizeHex(value, fallback = '#000000') {
            const text = (value || '').trim();
            if (/^#[0-9a-f]{6}$/i.test(text)) return text;
            if (/^#[0-9a-f]{3}$/i.test(text)) return '#' + text.slice(1).split('').map((c) => c + c).join('');
            const rgb = text.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
            if (!rgb) return fallback;
            return '#' + rgb.slice(1, 4).map((p) => Number(p).toString(16).padStart(2, '0')).join('');
        }

        function numericValue(value, fallback) {
            const parsed = parseInt((value || '').replace(/[^\d.-]/g, ''), 10);
            return Number.isFinite(parsed) ? parsed : fallback;
        }

        // Read true light/dark baselines from offscreen probes so we never touch
        // the live document theme. The Studio stylesheet exposes dark values via
        // the [data-ll-theme="dark"] selector, which matches any element.
        function captureBaseline() {
            ['light', 'dark'].forEach((mode) => {
                const probe = document.createElement('div');
                probe.setAttribute('data-ll-theme', mode);
                probe.style.cssText = 'position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;';
                document.body.appendChild(probe);
                const cs = getComputedStyle(probe);
                colorTokens.forEach((token) => {
                    baseline[mode][token] = normalizeHex(cs.getPropertyValue(token).trim(), '#000000');
                });
                if (mode === 'light') {
                    sharedTokens.forEach((token) => {
                        baseline.shared[token] = cs.getPropertyValue(token).trim() || sharedDefaults[token] || '';
                    });
                }
                document.body.removeChild(probe);
            });

            Object.assign(draft.light, baseline.light);
            Object.assign(draft.dark, baseline.dark);
            Object.assign(draft.shared, baseline.shared);
        }

        function applyStage(mode) {
            const stage = stages[mode];
            if (!stage) return;
            Object.entries(draft[mode]).forEach(([token, value]) => stage.style.setProperty(token, value));
            Object.entries(draft.shared).forEach(([token, value]) => {
                if (value) stage.style.setProperty(token, value);
            });
        }

        function applyAllStages() {
            applyStage('light');
            applyStage('dark');
        }

        function updateOutput(input) {
            const output = root.querySelector('output[for="' + input.id + '"]');
            if (output) output.textContent = input.value + (input.dataset.unit || '');
        }

        function setControlValues() {
            controls.forEach((input) => {
                const token = input.dataset.token;
                const isShared = input.dataset.shared === 'true';
                const current = isShared ? draft.shared[token] : draft[activeMode][token];

                if (input.type === 'color') {
                    input.value = normalizeHex(current, input.value || '#000000');
                } else {
                    input.value = numericValue(current, numericValue(sharedDefaults[token], 0));
                }
                updateOutput(input);
            });
        }

        function applyInput(input) {
            const token = input.dataset.token;
            const isShared = input.dataset.shared === 'true';
            const unit = input.dataset.unit || '';
            const value = input.type === 'range' ? input.value + unit : input.value;

            if (isShared) {
                draft.shared[token] = value;
            } else {
                draft[activeMode][token] = value;
            }

            updateOutput(input);
            applyAllStages();
            buildInstructions();
        }

        function syncModeUi() {
            modeButtons.forEach((b) => b.classList.toggle('is-active', b.dataset.llDevMode === activeMode));
            stageTitles.forEach((t) => t.classList.toggle('is-editing', t.dataset.llStageTitle === activeMode));
            if (modeHelp) {
                modeHelp.textContent = 'Editing ' + activeMode + ' mode. Both previews stay independent — switch tabs to craft the other.';
            }
        }

        function switchMode(mode) {
            activeMode = mode === 'dark' ? 'dark' : 'light';
            setControlValues();
            syncModeUi();
        }

        function formatTokenLines(values) {
            return Object.entries(values).map(([t, v]) => '- `' + t + '`: `' + v + '`').join('\n');
        }

        function buildInstructions() {
            instructions.value = `Task: Apply the approved Livelatch Studio design token changes from the admin Dev Tools preview.

Context:
- The Dev Tools page is preview-only and saves nothing to the live site.
- Light and dark token values were hand-crafted independently in /admin/dev-tools.

Primary target:
- resources/views/layouts/sidebar.blade.php

Requested light mode colour values:
${formatTokenLines(draft.light)}

Requested dark mode colour values:
${formatTokenLines(draft.dark)}

Requested shared shape/type values:
${formatTokenLines(draft.shared)}

Implementation notes:
- Update the :root Studio CSS variables for light mode and the [data-ll-theme="dark"] block for dark mode.
- Keep --ll-button-radius for buttons and --ll-radius for surfaces.
- Convert the temporary font-weight tokens into the stable --ll-dev-heading-weight / --ll-dev-button-weight defaults if approved.
- Do not change public profile theme presets unless asked; those live in ThemeService, ThemeSeeder and resources/views/linkstack/modules/theme.blade.php.
- Run php artisan view:cache after editing, and update summary.md / docs if the token system changes.`;

            copyStatus.textContent = 'Instructions generated from current token values.';
        }

        function resetPreview() {
            Object.assign(draft.light, baseline.light);
            Object.assign(draft.dark, baseline.dark);
            Object.assign(draft.shared, baseline.shared);
            applyAllStages();
            setControlValues();
            buildInstructions();
            copyStatus.textContent = 'Preview reset to the current Studio token values.';
        }

        function syncGlassButton() {
            const on = !!(window.LivelatchGlass && window.LivelatchGlass.isOn());
            if (glassButton) {
                glassButton.classList.toggle('is-on', on);
                glassButton.setAttribute('aria-pressed', on ? 'true' : 'false');
            }
        }

        controls.forEach((input) => input.addEventListener('input', () => applyInput(input)));
        modeButtons.forEach((b) => b.addEventListener('click', () => switchMode(b.dataset.llDevMode)));
        generateButton?.addEventListener('click', buildInstructions);
        resetButton?.addEventListener('click', resetPreview);
        glassButton?.addEventListener('click', () => {
            if (window.LivelatchGlass) {
                window.LivelatchGlass.toggle();
                syncGlassButton();
            }
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

        captureBaseline();
        applyAllStages();
        setControlValues();
        syncModeUi();
        buildInstructions();
        syncGlassButton();
    })();
</script>
@endsection
