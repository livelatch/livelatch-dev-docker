@extends('layouts.sidebar')

@section('content')
<style data-ll-dev-tools-style>
    .ll-dev-tools { display: grid; gap: 18px; }

    .ll-dev-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .ll-dev-header h2 { margin: 0 0 4px; color: var(--ll-text); }
    .ll-dev-header p { margin: 0; color: var(--ll-muted); }

    .ll-dev-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

    .ll-dev-toggle {
        display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 14px;
        border: 1px solid var(--ll-border); border-radius: 999px; background: var(--ll-surface-solid);
        color: var(--ll-text); font-weight: 600; cursor: pointer; transition: border-color 150ms ease, background 150ms ease;
    }
    .ll-dev-toggle .ll-dev-toggle-dot { width: 34px; height: 20px; border-radius: 999px; background: color-mix(in srgb, var(--ll-text) 18%, transparent); position: relative; transition: background 160ms ease; }
    .ll-dev-toggle .ll-dev-toggle-dot::after { content: ""; position: absolute; top: 3px; left: 3px; width: 14px; height: 14px; border-radius: 999px; background: #fff; transition: transform 160ms ease; }
    .ll-dev-toggle.is-on { border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }
    .ll-dev-toggle.is-on .ll-dev-toggle-dot { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-dev-toggle.is-on .ll-dev-toggle-dot::after { transform: translateX(14px); }

    .ll-dev-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 16px; border: 1px solid var(--ll-border); border-radius: var(--ll-button-radius); background: var(--ll-surface-solid); color: var(--ll-text); font-weight: 600; cursor: pointer; transition: transform 150ms ease, border-color 150ms ease; }
    .ll-dev-btn:hover { transform: translateY(-1px); }
    .ll-dev-btn-primary { border: 0; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; box-shadow: 0 12px 26px color-mix(in srgb, var(--ll-primary) 26%, transparent); }

    .ll-dev-grid { display: grid; grid-template-columns: minmax(320px, 420px) minmax(0, 1fr); gap: 18px; align-items: start; }

    .ll-dev-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); }
    .ll-dev-panel-header { padding: 18px; border-bottom: 1px solid var(--ll-border); }
    .ll-dev-panel-header h3 { margin: 0; color: var(--ll-text); font-size: 1.05rem; }
    .ll-dev-panel-header p { margin: 4px 0 0; color: var(--ll-muted); font-size: 0.88rem; }
    .ll-dev-panel-body { padding: 18px; }

    .ll-dev-mode-tabs { display: inline-flex; padding: 4px; gap: 4px; border: 1px solid var(--ll-border); border-radius: 999px; background: color-mix(in srgb, var(--ll-bg-soft) 70%, transparent); margin-bottom: 12px; width: 100%; }
    .ll-dev-mode-tabs button { flex: 1; min-height: 36px; border: 0; border-radius: 999px; color: var(--ll-muted); background: transparent; font-weight: 600; cursor: pointer; }
    .ll-dev-mode-tabs button.is-active { color: #fff; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }

    /* Auto-pair row */
    .ll-dev-pair { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; padding: 10px 12px; border: 1px solid color-mix(in srgb, var(--ll-primary) 22%, var(--ll-border)); border-radius: 12px; background: color-mix(in srgb, var(--ll-primary) 6%, transparent); }
    .ll-dev-pair .ll-dev-pair-text { font-weight: 600; color: var(--ll-text); font-size: 0.88rem; }
    .ll-dev-pair .ll-dev-pair-text span { display: block; color: var(--ll-muted); font-size: 0.74rem; font-weight: 500; }

    .ll-dev-controls { display: grid; gap: 13px; }
    .ll-dev-group-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ll-muted); margin: 8px 0 -2px; }
    .ll-dev-control { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; }
    .ll-dev-control label { display: grid; gap: 2px; margin: 0; font-weight: 600; color: var(--ll-text); font-size: 0.9rem; }
    .ll-dev-control label span { color: var(--ll-muted); font-size: 0.72rem; font-weight: 500; }
    .ll-dev-control input[type="color"] { width: 46px; height: 38px; padding: 3px; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-bg-soft); cursor: pointer; }
    .ll-dev-control input[type="range"] { width: min(160px, 34vw); accent-color: var(--ll-primary); }
    .ll-dev-control input[type="text"], .ll-dev-control select {
        width: min(180px, 42vw); border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-bg-soft);
        color: var(--ll-text); padding: 8px 10px; font-size: 0.84rem; font-family: inherit;
    }
    .ll-dev-font-row { grid-template-columns: 1fr; gap: 6px; }
    .ll-dev-font-row input[type="text"] { width: 100%; }
    .ll-dev-value { min-width: 56px; text-align: right; color: var(--ll-muted); font-size: 0.82rem; font-weight: 700; }

    /* Dual live preview stages */
    .ll-dev-stages { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ll-dev-stage-wrap { display: grid; gap: 8px; }
    .ll-dev-stage-title { display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--ll-text); font-size: 0.9rem; }
    .ll-dev-stage-title.is-editing::after { content: "editing"; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: var(--ll-primary); background: color-mix(in srgb, var(--ll-primary) 12%, transparent); border-radius: 999px; padding: 2px 8px; }

    .ll-dev-stage { border: 1px solid var(--ll-border); border-radius: 22px; background: var(--ll-bg); color: var(--ll-text); padding: 16px; display: grid; gap: 12px; font-family: var(--ll-font-body, var(--ll-font)); overflow: hidden; }
    .ll-dev-stage .lls-hero { border-radius: var(--ll-radius); padding: 18px; border: 1px solid var(--ll-border); background: radial-gradient(circle at 18% 0%, color-mix(in srgb, var(--ll-primary) 22%, transparent), transparent 42%), var(--ll-surface-solid); display: grid; gap: 9px; }
    .ll-dev-stage .lls-chip { width: max-content; padding: 5px 11px; border-radius: 999px; color: #fff; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); font-size: 0.72rem; font-weight: 600; }
    .ll-dev-stage h3, .ll-dev-stage .lls-h1 { margin: 0; color: var(--ll-text); font-size: 1.4rem; font-weight: var(--ll-dev-heading-weight, 700); font-family: var(--ll-font-h1, var(--ll-font)); line-height: 1.1; }
    .ll-dev-stage .lls-h2 { margin: 0; color: var(--ll-text); font-size: 1.02rem; font-weight: 600; font-family: var(--ll-font-h2, var(--ll-font)); }
    .ll-dev-stage p, .ll-dev-stage .lls-body { margin: 0; color: var(--ll-muted); font-size: 0.88rem; font-family: var(--ll-font-body, var(--ll-font)); }
    .ll-dev-stage .lls-type { display: grid; gap: 4px; }
    .ll-dev-stage .lls-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .ll-dev-stage .lls-btn { border: 0; border-radius: var(--ll-button-radius); padding: 9px 14px; font-weight: var(--ll-dev-button-weight, 600); font-family: inherit; cursor: pointer; font-size: 0.85rem; transition: transform var(--ll-anim-speed, 160ms) var(--ll-anim-ease, ease), box-shadow var(--ll-anim-speed, 160ms) var(--ll-anim-ease, ease); }
    .ll-dev-stage .lls-btn-primary { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; }
    .ll-dev-stage .lls-btn-ghost { background: var(--ll-surface-solid); color: var(--ll-text); border: 1px solid var(--ll-border); }
    .ll-dev-stage .lls-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ll-dev-stage .lls-card { border: 1px solid var(--ll-border); border-radius: calc(var(--ll-radius) - 8px); background: var(--ll-surface-solid); padding: 12px; display: grid; gap: 3px; transition: transform var(--ll-anim-speed, 160ms) var(--ll-anim-ease, ease), border-color var(--ll-anim-speed, 160ms) var(--ll-anim-ease, ease); }
    .ll-dev-stage .lls-card strong { color: var(--ll-text); font-size: 0.9rem; }
    .ll-dev-stage .lls-card span { color: var(--ll-muted); font-size: 0.76rem; }
    .ll-dev-stage.has-lift .lls-btn:hover, .ll-dev-stage.has-lift .lls-card:hover { transform: translateY(-3px); }
    .ll-dev-stage.has-lift .lls-card:hover { border-color: color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border)); }
    .ll-dev-stage .lls-soft { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 12px; border-radius: calc(var(--ll-radius) - 10px); background: var(--ll-bg-soft); border: 1px solid var(--ll-border); }
    .ll-dev-stage .lls-soft strong { color: var(--ll-text); font-size: 0.82rem; }
    .ll-dev-stage .lls-accent { padding: 4px 11px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; color: var(--ll-bg); background: var(--ll-primary-3); }
    .ll-dev-stage .lls-field { width: 100%; border: 1px solid var(--ll-border); border-radius: var(--ll-button-radius); background: var(--ll-bg-soft); color: var(--ll-text); padding: 9px 12px; font-size: 0.82rem; font-family: inherit; }

    .ll-dev-instructions { width: 100%; min-height: 240px; resize: vertical; color: var(--ll-text); background: var(--ll-bg-soft); border: 1px solid var(--ll-border); border-radius: 14px; padding: 14px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.84rem; }

    @media (max-width: 1199.98px) {
        .ll-dev-grid { grid-template-columns: 1fr; }
        .ll-dev-stages { grid-template-columns: 1fr; }
    }
</style>

<datalist id="ll-dev-fonts">
    <option value="Inter"></option><option value="Poppins"></option><option value="Roboto"></option>
    <option value="Montserrat"></option><option value="Open Sans"></option><option value="Lato"></option>
    <option value="Sora"></option><option value="Space Grotesk"></option><option value="Outfit"></option>
    <option value="Manrope"></option><option value="Playfair Display"></option><option value="Oswald"></option>
    <option value="Orbitron"></option><option value="Rajdhani"></option><option value="Chakra Petch"></option>
</datalist>

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
                <button type="button" class="ll-dev-btn" id="ll-dev-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                <button type="button" class="ll-dev-btn ll-dev-btn-primary" id="ll-dev-generate"><i class="bi bi-terminal"></i> Generate Codex Instructions</button>
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

                    <div class="ll-dev-pair">
                        <div class="ll-dev-pair-text">Auto-pair light &amp; dark<span>Editing one mode derives the other</span></div>
                        <button type="button" class="ll-dev-toggle is-on" id="ll-dev-autopair" aria-pressed="true">
                            <span class="ll-dev-toggle-dot" aria-hidden="true"></span>
                            <span>On</span>
                        </button>
                    </div>

                    <div class="ll-dev-controls" id="ll-dev-controls">
                        <div class="ll-dev-group-label">Colour</div>
                        <div class="ll-dev-control"><label for="ll-dev-primary">Primary <span>--ll-primary</span></label><input id="ll-dev-primary" type="color" data-token="--ll-primary"></div>
                        <div class="ll-dev-control"><label for="ll-dev-primary-2">Secondary <span>--ll-primary-2</span></label><input id="ll-dev-primary-2" type="color" data-token="--ll-primary-2"></div>
                        <div class="ll-dev-control"><label for="ll-dev-primary-3">Accent <span>--ll-primary-3</span></label><input id="ll-dev-primary-3" type="color" data-token="--ll-primary-3"></div>
                        <div class="ll-dev-control"><label for="ll-dev-bg">Background <span>--ll-bg</span></label><input id="ll-dev-bg" type="color" data-token="--ll-bg"></div>
                        <div class="ll-dev-control"><label for="ll-dev-bg-soft">Soft background <span>--ll-bg-soft</span></label><input id="ll-dev-bg-soft" type="color" data-token="--ll-bg-soft"></div>
                        <div class="ll-dev-control"><label for="ll-dev-surface">Surface <span>--ll-surface-solid</span></label><input id="ll-dev-surface" type="color" data-token="--ll-surface-solid"></div>
                        <div class="ll-dev-control"><label for="ll-dev-text">Text <span>--ll-text</span></label><input id="ll-dev-text" type="color" data-token="--ll-text"></div>
                        <div class="ll-dev-control"><label for="ll-dev-muted">Muted text <span>--ll-muted</span></label><input id="ll-dev-muted" type="color" data-token="--ll-muted"></div>
                        <div class="ll-dev-control"><label for="ll-dev-border">Border <span>--ll-border</span></label><input id="ll-dev-border" type="color" data-token="--ll-border"></div>

                        <div class="ll-dev-group-label">Typography (shared) — type any Google font</div>
                        <div class="ll-dev-control ll-dev-font-row"><label for="ll-dev-font-h1">Heading H1 font <span>--ll-font-h1</span></label><input id="ll-dev-font-h1" type="text" list="ll-dev-fonts" data-token="--ll-font-h1" data-font="true" data-shared="true" placeholder="e.g. Space Grotesk" autocomplete="off"></div>
                        <div class="ll-dev-control ll-dev-font-row"><label for="ll-dev-font-h2">Heading H2 font <span>--ll-font-h2</span></label><input id="ll-dev-font-h2" type="text" list="ll-dev-fonts" data-token="--ll-font-h2" data-font="true" data-shared="true" placeholder="e.g. Sora" autocomplete="off"></div>
                        <div class="ll-dev-control ll-dev-font-row"><label for="ll-dev-font-body">Body / P font <span>--ll-font-body</span></label><input id="ll-dev-font-body" type="text" list="ll-dev-fonts" data-token="--ll-font-body" data-font="true" data-shared="true" placeholder="e.g. Inter" autocomplete="off"></div>
                        <div class="ll-dev-control"><label for="ll-dev-heading-weight">Heading weight <span>--ll-dev-heading-weight</span></label><div class="d-flex align-items-center gap-2"><input id="ll-dev-heading-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-heading-weight" data-shared="true"><output class="ll-dev-value" for="ll-dev-heading-weight"></output></div></div>
                        <div class="ll-dev-control"><label for="ll-dev-button-weight">Button weight <span>--ll-dev-button-weight</span></label><div class="d-flex align-items-center gap-2"><input id="ll-dev-button-weight" type="range" min="400" max="900" step="100" data-token="--ll-dev-button-weight" data-shared="true"><output class="ll-dev-value" for="ll-dev-button-weight"></output></div></div>

                        <div class="ll-dev-group-label">Shape (shared)</div>
                        <div class="ll-dev-control"><label for="ll-dev-radius">Surface radius <span>--ll-radius</span></label><div class="d-flex align-items-center gap-2"><input id="ll-dev-radius" type="range" min="0" max="40" step="1" data-token="--ll-radius" data-unit="px" data-shared="true"><output class="ll-dev-value" for="ll-dev-radius"></output></div></div>
                        <div class="ll-dev-control"><label for="ll-dev-button-radius">Button radius <span>--ll-button-radius</span></label><div class="d-flex align-items-center gap-2"><input id="ll-dev-button-radius" type="range" min="0" max="32" step="1" data-token="--ll-button-radius" data-unit="px" data-shared="true"><output class="ll-dev-value" for="ll-dev-button-radius"></output></div></div>

                        <div class="ll-dev-group-label">Motion (shared)</div>
                        <div class="ll-dev-control"><label for="ll-dev-anim-speed">Transition speed <span>--ll-anim-speed</span></label><div class="d-flex align-items-center gap-2"><input id="ll-dev-anim-speed" type="range" min="0" max="600" step="10" data-token="--ll-anim-speed" data-unit="ms" data-shared="true"><output class="ll-dev-value" for="ll-dev-anim-speed"></output></div></div>
                        <div class="ll-dev-control"><label for="ll-dev-anim-ease">Easing <span>--ll-anim-ease</span></label>
                            <select id="ll-dev-anim-ease" data-token="--ll-anim-ease" data-shared="true">
                                <option value="ease">ease</option>
                                <option value="ease-in-out">ease-in-out</option>
                                <option value="linear">linear</option>
                                <option value="cubic-bezier(0.2, 0.8, 0.2, 1)">smooth</option>
                                <option value="cubic-bezier(0.34, 1.56, 0.64, 1)">overshoot</option>
                            </select>
                        </div>
                        <div class="ll-dev-control"><label>Hover lift <span>preview interaction</span></label>
                            <button type="button" class="ll-dev-toggle" id="ll-dev-lift" aria-pressed="false"><span class="ll-dev-toggle-dot" aria-hidden="true"></span><span>Off</span></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ll-dev-preview">
                <div class="ll-dev-stages">
                    @foreach(['light' => 'bi-sun-fill', 'dark' => 'bi-moon-stars-fill'] as $mode => $icon)
                    <div class="ll-dev-stage-wrap">
                        <div class="ll-dev-stage-title" data-ll-stage-title="{{ $mode }}"><i class="bi {{ $icon }}"></i> {{ ucfirst($mode) }} mode</div>
                        <div class="ll-dev-stage" data-ll-theme="{{ $mode }}" id="ll-dev-stage-{{ $mode }}">
                            <div class="lls-hero">
                                <span class="lls-chip">Livelatch Studio</span>
                                <div class="lls-h1">Your creator space</div>
                                <p>Cards, buttons, text and surfaces preview with these exact tokens.</p>
                                <div class="lls-btns">
                                    <button type="button" class="lls-btn lls-btn-primary">Primary</button>
                                    <button type="button" class="lls-btn lls-btn-ghost">Secondary</button>
                                </div>
                            </div>
                            <div class="lls-type">
                                <div class="lls-h2">A subheading in your H2 font</div>
                                <p class="lls-body">Body copy in the chosen font — the quick brown fox jumps over the lazy dog while 1,234 links get clicked.</p>
                            </div>
                            <div class="lls-cards">
                                <div class="lls-card"><strong>Surface</strong><span>Panel background &amp; radius</span></div>
                                <div class="lls-card"><strong>Border</strong><span>Hairline &amp; card edges</span></div>
                            </div>
                            <div class="lls-soft">
                                <strong>Soft background &amp; inputs</strong>
                                <span class="lls-accent">Accent</span>
                            </div>
                            <input class="lls-field" type="text" value="Field on soft background" readonly aria-label="Soft background field preview">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="ll-dev-panel" style="margin-top: 16px;">
                    <div class="ll-dev-panel-header">
                        <h3>Codex instructions</h3>
                        <p id="ll-dev-copy-status">Generated text will appear here.</p>
                    </div>
                    <div class="ll-dev-panel-body">
                        <textarea id="ll-dev-instructions" class="ll-dev-instructions" readonly></textarea>
                        <div class="ll-dev-actions" style="margin-top: 12px;">
                            <button type="button" class="ll-dev-btn" id="ll-dev-copy"><i class="bi bi-clipboard"></i> Copy instructions</button>
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
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const controls = Array.from(root.querySelectorAll('[data-token]'));
        const modeButtons = Array.from(root.querySelectorAll('[data-ll-dev-mode]'));
        const stageTitles = Array.from(root.querySelectorAll('[data-ll-stage-title]'));
        const stages = { light: root.querySelector('#ll-dev-stage-light'), dark: root.querySelector('#ll-dev-stage-dark') };
        const instructions = root.querySelector('#ll-dev-instructions');
        const generateButton = root.querySelector('#ll-dev-generate');
        const copyButton = root.querySelector('#ll-dev-copy');
        const resetButton = root.querySelector('#ll-dev-reset');
        const copyStatus = root.querySelector('#ll-dev-copy-status');
        const modeHelp = root.querySelector('#ll-dev-mode-help');
        const glassButton = root.querySelector('#ll-dev-glass');
        const autopairButton = root.querySelector('#ll-dev-autopair');
        const liftButton = root.querySelector('#ll-dev-lift');

        const colorTokens = ['--ll-primary', '--ll-primary-2', '--ll-primary-3', '--ll-bg', '--ll-bg-soft', '--ll-surface-solid', '--ll-text', '--ll-muted', '--ll-border'];
        const brandTokens = ['--ll-primary', '--ll-primary-2', '--ll-primary-3'];
        const sharedDefaults = {
            '--ll-radius': '20px', '--ll-button-radius': '12px',
            '--ll-dev-heading-weight': '600', '--ll-dev-button-weight': '400',
            '--ll-font-h1': 'Poppins', '--ll-font-h2': 'Poppins', '--ll-font-body': 'Poppins',
            '--ll-anim-speed': '160ms', '--ll-anim-ease': 'ease',
        };

        const baseline = { light: {}, dark: {}, shared: {} };
        const draft = { light: {}, dark: {}, shared: {} };
        let activeMode = 'light';
        let autoPair = true;

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

        function hexToHsl(hex) {
            hex = normalizeHex(hex, '#000000');
            const r = parseInt(hex.slice(1, 3), 16) / 255, g = parseInt(hex.slice(3, 5), 16) / 255, b = parseInt(hex.slice(5, 7), 16) / 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h = 0, s = 0; const l = (max + min) / 2;
            if (max !== min) {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
                else if (max === g) h = (b - r) / d + 2;
                else h = (r - g) / d + 4;
                h *= 60;
            }
            return { h, s: s * 100, l: l * 100 };
        }
        function hslToHex(h, s, l) {
            s = Math.max(0, Math.min(100, s)) / 100; l = Math.max(0, Math.min(100, l)) / 100;
            const c = (1 - Math.abs(2 * l - 1)) * s, x = c * (1 - Math.abs(((h / 60) % 2) - 1)), m = l - c / 2;
            let r = 0, g = 0, b = 0;
            if (h < 60) { r = c; g = x; } else if (h < 120) { r = x; g = c; } else if (h < 180) { g = c; b = x; }
            else if (h < 240) { g = x; b = c; } else if (h < 300) { r = x; b = c; } else { r = c; b = x; }
            const to = (v) => Math.round((v + m) * 255).toString(16).padStart(2, '0');
            return '#' + to(r) + to(g) + to(b);
        }

        // Derive a sensible counterpart colour for the opposite mode.
        function deriveOpposite(token, hex, toMode) {
            const { h, s, l } = hexToHsl(hex);
            if (brandTokens.includes(token)) {
                const nl = toMode === 'dark' ? Math.min(76, l + 10) : Math.max(40, l - 8);
                const ns = Math.min(100, s + (toMode === 'dark' ? 6 : -4));
                return hslToHex(h, ns, nl);
            }
            let nl = 100 - l;
            const ns = Math.min(s, 32);
            if (toMode === 'dark') nl = Math.max(6, Math.min(94, nl)); else nl = Math.max(4, Math.min(99, nl));
            return hslToHex(h, ns, nl);
        }

        function captureBaseline() {
            ['light', 'dark'].forEach((mode) => {
                const probe = document.createElement('div');
                probe.setAttribute('data-ll-theme', mode);
                probe.style.cssText = 'position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;';
                document.body.appendChild(probe);
                const cs = getComputedStyle(probe);
                colorTokens.forEach((token) => { baseline[mode][token] = normalizeHex(cs.getPropertyValue(token).trim(), mode === 'dark' ? '#101421' : '#ffffff'); });
                if (mode === 'light') {
                    Object.keys(sharedDefaults).forEach((token) => {
                        const v = cs.getPropertyValue(token).trim();
                        baseline.shared[token] = v || sharedDefaults[token];
                    });
                }
                document.body.removeChild(probe);
            });
            Object.assign(draft.light, baseline.light);
            Object.assign(draft.dark, baseline.dark);
            Object.assign(draft.shared, baseline.shared);
        }

        const loadedFonts = {};
        function loadGoogleFont(family) {
            const name = (family || '').trim();
            if (!name || loadedFonts[name.toLowerCase()]) return;
            loadedFonts[name.toLowerCase()] = true;
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(name).replace(/%20/g, '+') + ':wght@400;500;600;700;800&display=swap';
            document.head.appendChild(link);
        }
        function fontStack(name) {
            const n = (name || '').trim();
            return n ? ('"' + n + '", system-ui, sans-serif') : 'var(--ll-font)';
        }

        function applyStage(mode) {
            const stage = stages[mode];
            if (!stage) return;
            Object.entries(draft[mode]).forEach(([token, value]) => stage.style.setProperty(token, value));
            Object.entries(draft.shared).forEach(([token, value]) => {
                if (!value) return;
                if (token === '--ll-font-h1' || token === '--ll-font-h2' || token === '--ll-font-body') {
                    loadGoogleFont(value);
                    stage.style.setProperty(token, fontStack(value));
                } else {
                    stage.style.setProperty(token, value);
                }
            });
        }
        function applyAllStages() { applyStage('light'); applyStage('dark'); }

        function updateOutput(input) {
            const output = root.querySelector('output[for="' + input.id + '"]');
            if (output) output.textContent = input.value + (input.dataset.unit || '');
        }

        function setControlValues() {
            controls.forEach((input) => {
                const token = input.dataset.token;
                const isShared = input.dataset.shared === 'true';
                const current = isShared ? draft.shared[token] : draft[activeMode][token];
                if (input.type === 'color') input.value = normalizeHex(current, input.value || '#000000');
                else if (input.dataset.font === 'true') input.value = (current || sharedDefaults[token] || '').replace(/^["']|["'].*$/g, '');
                else if (input.tagName === 'SELECT') input.value = current || sharedDefaults[token] || '';
                else input.value = numericValue(current, numericValue(sharedDefaults[token], 0));
                updateOutput(input);
            });
        }

        function applyInput(input) {
            const token = input.dataset.token;
            const isShared = input.dataset.shared === 'true';
            const unit = input.dataset.unit || '';
            let value;
            if (input.type === 'range') value = input.value + unit;
            else value = input.value;

            if (isShared) {
                draft.shared[token] = value;
            } else {
                draft[activeMode][token] = value;
                if (autoPair && input.type === 'color') {
                    const other = activeMode === 'light' ? 'dark' : 'light';
                    draft[other][token] = deriveOpposite(token, value, other);
                }
            }
            updateOutput(input);
            applyAllStages();
            buildInstructions();
        }

        function syncModeUi() {
            modeButtons.forEach((b) => b.classList.toggle('is-active', b.dataset.llDevMode === activeMode));
            stageTitles.forEach((t) => t.classList.toggle('is-editing', t.dataset.llStageTitle === activeMode));
            if (modeHelp) modeHelp.textContent = 'Editing ' + activeMode + ' mode' + (autoPair ? ' — the other mode is auto-paired.' : '. Auto-pair is off; modes are independent.');
        }
        function switchMode(mode) { activeMode = mode === 'dark' ? 'dark' : 'light'; setControlValues(); syncModeUi(); }

        function formatTokenLines(values) { return Object.entries(values).map(([t, v]) => '- `' + t + '`: `' + v + '`').join('\n'); }

        function buildInstructions() {
            instructions.value = `Task: Apply the approved Livelatch Studio design tokens from the admin Dev Tools preview.

Context:
- The Dev Tools page is preview-only and saves nothing to the live site.
- Light and dark were crafted in /admin/dev-tools (auto-pair ${autoPair ? 'on' : 'off'}).

Primary target:
- resources/views/layouts/sidebar.blade.php

Light mode colours:
${formatTokenLines(draft.light)}

Dark mode colours:
${formatTokenLines(draft.dark)}

Shared shape / type / motion / fonts:
${formatTokenLines(draft.shared)}

Implementation notes:
- Light values go on [data-ll-theme="light"]; dark values go on the combined :root, [data-ll-theme="dark"] rule (dark is the default).
- Fonts (--ll-font-h1/h2/body) are NEW tokens: add each family to the Studio @import (or a <link>) and apply them to the h1 / h2 / body selectors. Body falls back to --ll-font.
- Motion tokens (--ll-anim-speed/--ll-anim-ease) drive transitions; wire them into the interactive components (buttons, cards, nav) where a transition is hardcoded today.
- Keep --ll-button-radius for buttons and --ll-radius for surfaces.
- Do not change public profile theme presets (ThemeService / ThemeSeeder / linkstack/modules/theme.blade.php) unless asked.
- Run php artisan view:cache after editing; update summary.md / docs if the token system changes.`;
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
            if (glassButton) { glassButton.classList.toggle('is-on', on); glassButton.setAttribute('aria-pressed', on ? 'true' : 'false'); }
        }

        controls.forEach((input) => {
            const evt = input.dataset.font === 'true' ? 'change' : 'input';
            input.addEventListener(evt, () => applyInput(input));
            if (input.dataset.font === 'true') input.addEventListener('input', () => { /* live token, font loads on commit */ });
        });
        modeButtons.forEach((b) => b.addEventListener('click', () => switchMode(b.dataset.llDevMode)));
        generateButton?.addEventListener('click', buildInstructions);
        resetButton?.addEventListener('click', resetPreview);
        autopairButton?.addEventListener('click', () => {
            autoPair = !autoPair;
            autopairButton.classList.toggle('is-on', autoPair);
            autopairButton.setAttribute('aria-pressed', autoPair ? 'true' : 'false');
            autopairButton.querySelector('span:last-child').textContent = autoPair ? 'On' : 'Off';
            syncModeUi();
        });
        liftButton?.addEventListener('click', () => {
            const on = liftButton.getAttribute('aria-pressed') !== 'true';
            liftButton.classList.toggle('is-on', on);
            liftButton.setAttribute('aria-pressed', on ? 'true' : 'false');
            liftButton.querySelector('span:last-child').textContent = on ? 'On' : 'Off';
            stages.light && stages.light.classList.toggle('has-lift', on);
            stages.dark && stages.dark.classList.toggle('has-lift', on);
        });
        glassButton?.addEventListener('click', () => { if (window.LivelatchGlass) { window.LivelatchGlass.toggle(); syncGlassButton(); } });
        copyButton?.addEventListener('click', async () => {
            buildInstructions();
            try { await navigator.clipboard.writeText(instructions.value); copyStatus.textContent = 'Instructions copied.'; }
            catch (error) { instructions.focus(); instructions.select(); copyStatus.textContent = 'Copy blocked by the browser. Select the text manually.'; }
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
