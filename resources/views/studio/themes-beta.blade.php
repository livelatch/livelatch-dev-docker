@extends('layouts.sidebar')

@section('content')
<style data-ll-bt-style>
    @import url('https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Comfortaa:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Kosugi+Maru&family=Lato:wght@400;700&family=Mochiy+Pop+One&family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Pacifico&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Press+Start+2P&family=Rajdhani:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap');

    .ll-bt-page { display: grid; gap: 14px; }

    .ll-bt-top {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: end;
    }
    .ll-bt-top h1 {
        color: var(--ll-text);
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        line-height: 1;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ll-bt-beta {
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        border-radius: 999px;
        padding: 5px 10px;
        transform: translateY(-4px);
    }
    .ll-bt-top p { color: var(--ll-muted); margin: 0; }

    .ll-bt-actions { display: inline-flex; gap: 10px; align-items: center; }
    .ll-bt-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border: 0; cursor: pointer;
        border-radius: var(--ll-button-radius);
        font-weight: 800; padding: 12px 18px;
    }
    .ll-bt-btn-save { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; box-shadow: var(--ll-shadow-soft); }
    .ll-bt-btn-reset { background: var(--ll-surface-solid); color: var(--ll-muted); border: 1px solid var(--ll-border); }
    .ll-bt-btn[disabled] { opacity: .6; cursor: default; }

    .ll-bt-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.32fr) minmax(340px, .68fr);
        gap: 18px;
        align-items: start;
    }
    .ll-bt-main { display: grid; gap: 14px; min-width: 0; }

    .ll-bt-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
        padding: 16px;
        min-width: 0;
    }
    .ll-bt-panel h2 {
        color: var(--ll-text);
        font-size: 1rem;
        margin: 0 0 4px;
        display: flex; align-items: center; gap: 8px;
    }
    .ll-bt-panel .ll-bt-sub { color: var(--ll-muted); font-size: .84rem; margin: 0 0 12px; }

    /* ---- Base theme carousel ---- */
    .ll-bt-carousel { position: relative; }
    .ll-bt-track {
        display: flex; gap: 12px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        padding: 4px 2px 12px;
        scrollbar-width: thin;
    }
    .ll-bt-card {
        flex: 0 0 188px; width: 188px;
        scroll-snap-align: start;
        border: 1px solid var(--ll-border);
        border-radius: 22px;
        background: var(--ll-surface-solid);
        cursor: pointer;
        padding: 10px;
        text-align: left;
        display: grid; gap: 8px;
        transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
    }
    .ll-bt-card:hover { transform: translateY(-2px); }
    .ll-bt-card.is-active {
        border-color: color-mix(in srgb, var(--ll-primary) 60%, var(--ll-border));
        box-shadow: 0 10px 30px color-mix(in srgb, var(--ll-primary) 18%, transparent);
    }
    .ll-bt-thumb {
        height: 92px; border-radius: 14px; position: relative; overflow: hidden;
        display: flex; align-items: flex-end; padding: 8px;
    }
    .ll-bt-thumb .ll-bt-used {
        font-size: .68rem; font-weight: 700; color: #fff;
        background: rgba(0,0,0,.42); border-radius: 999px; padding: 3px 8px;
        backdrop-filter: blur(4px);
    }
    .ll-bt-card-name { color: var(--ll-text); font-weight: 700; font-size: .92rem; line-height: 1.1; }
    .ll-bt-card-author { color: var(--ll-muted); font-size: .76rem; }
    .ll-bt-stars { color: #f5b301; font-size: .8rem; letter-spacing: 1px; }
    .ll-bt-stars span { color: var(--ll-muted); font-size: .7rem; margin-left: 4px; }

    .ll-bt-arrow {
        position: absolute; top: 38px; z-index: 3;
        width: 36px; height: 36px; border-radius: 50%;
        border: 1px solid var(--ll-border); background: var(--ll-surface-solid);
        color: var(--ll-text); cursor: pointer; box-shadow: var(--ll-shadow-soft);
        display: flex; align-items: center; justify-content: center;
    }
    .ll-bt-arrow-prev { left: -8px; }
    .ll-bt-arrow-next { right: -8px; }

    .ll-bt-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
    .ll-bt-pill {
        border: 1px solid var(--ll-border); border-radius: 999px;
        background: var(--ll-surface-solid); color: var(--ll-text);
        cursor: pointer; font-weight: 700; font-size: .82rem; padding: 7px 12px;
    }
    .ll-bt-pill.is-active { border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border)); background: color-mix(in srgb, var(--ll-primary) 12%, transparent); color: var(--ll-primary); }

    /* ---- Styling grid ---- */
    .ll-bt-styling { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
    @media (max-width: 1500px) { .ll-bt-styling { grid-template-columns: 1fr 1fr; } }

    .ll-bt-colours { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ll-bt-colour { border: 1px solid var(--ll-border); border-radius: 16px; padding: 10px; background: color-mix(in srgb, var(--ll-bg-soft) 60%, transparent); }
    .ll-bt-colour label { display: block; color: var(--ll-muted); font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 7px; }
    .ll-bt-colour-row { display: grid; grid-template-columns: 36px 1fr; gap: 8px; align-items: center; }
    .ll-bt-colour-row input[type="color"] { appearance: none; -webkit-appearance: none; border: 0; width: 36px; height: 36px; border-radius: 10px; padding: 0; cursor: pointer; background: none; }
    .ll-bt-colour-row input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
    .ll-bt-colour-row input[type="color"]::-webkit-color-swatch { border: 0; border-radius: 10px; }
    .ll-bt-hex { border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-surface-solid); color: var(--ll-text); font-family: ui-monospace, Consolas, monospace; font-size: .78rem; font-weight: 700; padding: 7px 8px; width: 100%; text-transform: uppercase; }

    /* ---- Typography ---- */
    .ll-bt-type-field { margin-bottom: 12px; }
    .ll-bt-type-field label { display: block; color: var(--ll-muted); font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
    .ll-bt-select { width: 100%; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-surface-solid); color: var(--ll-text); font-weight: 600; padding: 9px 10px; }
    .ll-bt-type-preview { border-top: 1px solid var(--ll-border); padding-top: 12px; margin-top: 4px; }
    .ll-bt-type-preview .h1 { font-size: 1.5rem; font-weight: 800; color: var(--ll-text); line-height: 1.1; margin: 0 0 4px; }
    .ll-bt-type-preview .h2 { font-size: 1.1rem; font-weight: 700; color: var(--ll-text); margin: 0 0 6px; }
    .ll-bt-type-preview p { color: var(--ll-muted); font-size: .9rem; margin: 0; }

    /* ---- Sliders / effects ---- */
    .ll-bt-slider { margin-bottom: 14px; }
    .ll-bt-slider .ll-bt-slider-head { display: flex; justify-content: space-between; color: var(--ll-muted); font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
    .ll-bt-slider .ll-bt-slider-head strong { color: var(--ll-text); }
    .ll-bt-range { width: 100%; accent-color: var(--ll-primary); }

    /* ---- Custom CSS ---- */
    .ll-bt-css-wrap { position: relative; }
    .ll-bt-css { width: 100%; min-height: 120px; border: 1px solid var(--ll-border); border-radius: 14px; background: #0e1424; color: #e6edff; font-family: ui-monospace, Consolas, monospace; font-size: .82rem; padding: 12px; resize: vertical; }
    .ll-bt-pro-tag { font-size: .6rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #fff; background: linear-gradient(135deg, #f59e0b, #f5b301); border-radius: 999px; padding: 3px 8px; }
    .ll-bt-css-lock { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 14px; background: color-mix(in srgb, var(--ll-surface-solid) 86%, transparent); backdrop-filter: blur(2px); text-align: center; color: var(--ll-muted); padding: 14px; }

    /* ---- Preview ---- */
    .ll-bt-preview-shell { position: sticky; top: 88px; display: grid; gap: 12px; }
    .ll-bt-devices { display: flex; gap: 8px; flex-wrap: wrap; }
    .ll-bt-device-btn { border: 1px solid var(--ll-border); border-radius: 999px; background: var(--ll-surface-solid); color: var(--ll-muted); cursor: pointer; font-weight: 700; font-size: .78rem; padding: 7px 12px; display: inline-flex; gap: 6px; align-items: center; }
    .ll-bt-device-btn.is-active { border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border)); color: var(--ll-primary); background: color-mix(in srgb, var(--ll-primary) 10%, transparent); }

    .ll-bt-stage { display: flex; justify-content: center; align-items: flex-start; min-height: 200px; }
    .ll-bt-scaler { position: relative; }
    .ll-bt-frame { position: absolute; top: 0; left: 0; transform-origin: top left; background: #000; overflow: hidden; box-shadow: 0 24px 60px rgba(8,12,30,.32); }
    .ll-bt-frame.has-bezel { border: 10px solid #0b0f1c; }
    .ll-bt-frame iframe { width: 100%; height: 100%; border: 0; display: block; background: #000; }
    .ll-bt-preview-meta { text-align: center; color: var(--ll-muted); font-size: .76rem; }

    @media (max-width: 1199.98px) {
        .ll-bt-layout, .ll-bt-top { grid-template-columns: 1fr; }
        .ll-bt-preview-shell { position: static; }
    }
    @media (max-width: 680px) {
        .ll-bt-styling { grid-template-columns: 1fr; }
        .ll-bt-colours { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-bt-page">
    <div class="ll-bt-top">
        <div>
            <h1>Theme Studio <span class="ll-bt-beta">Beta</span></h1>
            <p>Pick an immersive base theme, then make it yours — colours, type, motion and (Pro) custom CSS.</p>
        </div>
        <div class="ll-bt-actions">
            <button type="button" class="ll-bt-btn ll-bt-btn-reset" id="ll-bt-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            <button type="button" class="ll-bt-btn ll-bt-btn-save" id="ll-bt-save"><i class="bi bi-check2-circle"></i> Apply theme</button>
        </div>
    </div>

    <div class="alert alert-success ll-bt-alert d-none" id="ll-bt-toast" role="alert"></div>
    <div class="alert alert-danger ll-bt-alert d-none" id="ll-bt-error" role="alert"></div>

    @if(empty($themes))
        <div class="alert alert-warning" role="alert">No blade themes are installed yet. Add one under <code>resources/themes/</code>.</div>
    @else
    <div class="ll-bt-layout">
        <div class="ll-bt-main">
            <section class="ll-bt-panel">
                <h2><i class="bi bi-boxes"></i> Base themes</h2>
                <p class="ll-bt-sub">These are authored by Livelatch and shipped in the app. Every edit you make derives from one of these.</p>
                <div class="ll-bt-carousel">
                    <button type="button" class="ll-bt-arrow ll-bt-arrow-prev" id="ll-bt-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="ll-bt-arrow ll-bt-arrow-next" id="ll-bt-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                    <div class="ll-bt-track" id="ll-bt-track"></div>
                </div>
                <div class="ll-bt-presets" id="ll-bt-presets"></div>
            </section>

            <div class="ll-bt-styling">
                <section class="ll-bt-panel">
                    <h2><i class="bi bi-palette"></i> Colour</h2>
                    <p class="ll-bt-sub">Make it yours.</p>
                    <div class="ll-bt-colours" id="ll-bt-colours"></div>
                </section>

                <section class="ll-bt-panel" id="ll-bt-typography-panel">
                    <h2><i class="bi bi-fonts"></i> Typography</h2>
                    <div id="ll-bt-type-fields"></div>
                    <div class="ll-bt-type-preview">
                        <div class="h1" id="ll-bt-type-h1">Heading 1</div>
                        <div class="h2" id="ll-bt-type-h2">Heading 2</div>
                        <p id="ll-bt-type-p">Paragraph — the quick brown fox jumps over the lazy dog.</p>
                    </div>
                </section>

                <section class="ll-bt-panel" id="ll-bt-effects-panel">
                    <h2><i class="bi bi-sliders"></i> Effects</h2>
                    <p class="ll-bt-sub">Animations &amp; motion.</p>
                    <div id="ll-bt-sliders"></div>
                </section>
            </div>

            <section class="ll-bt-panel" id="ll-bt-css-panel" hidden>
                <h2><i class="bi bi-code-slash"></i> Custom CSS <span class="ll-bt-pro-tag">Pro</span></h2>
                <p class="ll-bt-sub">Target this theme's classes for fine-grained control. See the theme docs for available selectors.</p>
                <div class="ll-bt-css-wrap">
                    <textarea class="ll-bt-css" id="ll-bt-css" placeholder=".pt-link { letter-spacing: .04em; }" spellcheck="false"></textarea>
                    @unless($isPro)
                    <div class="ll-bt-css-lock">
                        <i class="bi bi-lock-fill" style="font-size:1.4rem;"></i>
                        <strong>Custom CSS is a Pro feature</strong>
                        <a href="{{ url('/studio/subscription') }}" class="ll-bt-pill is-active">Upgrade to Pro</a>
                    </div>
                    @endunless
                </div>
            </section>
        </div>

        <aside class="ll-bt-preview-shell">
            <section class="ll-bt-panel">
                <h2><i class="bi bi-phone"></i> Live preview</h2>
                <div class="ll-bt-devices" id="ll-bt-devices"></div>
                <div class="ll-bt-stage" id="ll-bt-stage">
                    <div class="ll-bt-scaler" id="ll-bt-scaler">
                        <div class="ll-bt-frame has-bezel" id="ll-bt-frame">
                            <iframe id="ll-bt-iframe" title="Theme preview" referrerpolicy="no-referrer"></iframe>
                        </div>
                    </div>
                </div>
                <p class="ll-bt-preview-meta" id="ll-bt-preview-meta">iPhone 17 Pro Max · 440 × 956</p>
                <p class="ll-bt-preview-meta">Preview reflects unsaved edits. Nothing is public until you press Apply theme.</p>
            </section>
        </aside>
    </div>
    @endif
</div>

<script>
    window.LL_BT_DATA = {
        themes: @json($themes),
        usage: @json($usage),
        current: @json($current),
        isPro: @json($isPro),
        csrf: '{{ csrf_token() }}',
        urls: {
            update: '{{ route('editBladeTheme') }}',
            reset: '{{ route('resetBladeTheme') }}',
            previewBase: '{{ route('bladeThemePreview', ['slug' => '__SLUG__']) }}'
        }
    };
</script>

@verbatim
<script>
(function () {
    const D = window.LL_BT_DATA;
    if (!D || !D.themes || !D.themes.length) return;

    const $ = (sel) => document.querySelector(sel);
    const themesBySlug = {};
    D.themes.forEach(t => { themesBySlug[t.slug] = t; });

    const DEVICES = {
        phone:   { label: 'iPhone 17 Pro Max', w: 440, h: 956, bezel: true },
        tablet:  { label: 'iPad Pro',          w: 834, h: 1194, bezel: true },
        desktop: { label: 'Desktop',           w: 1280, h: 800, bezel: false },
    };

    // ---- State ----
    const startSlug = (D.current && themesBySlug[D.current.theme_slug]) ? D.current.theme_slug : D.themes[0].slug;
    const state = {
        slug: startSlug,
        settings: {},
        device: 'phone',
    };

    function manifest() { return themesBySlug[state.slug] || {}; }
    function controls() { return (manifest().controls) || {}; }

    // Resolve effective settings: manifest defaults <- saved (if same slug) <- in-memory edits
    function defaultsFor(slug) {
        return Object.assign({}, (themesBySlug[slug] || {}).defaults || {});
    }
    function resetSettingsToTheme(slug) {
        const base = defaultsFor(slug);
        if (D.current && D.current.theme_slug === slug && D.current.settings) {
            Object.assign(base, D.current.settings);
        }
        state.settings = base;
    }

    // ---- Carousel ----
    function gradientFor(t) {
        return t.preview_gradient || 'linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2))';
    }
    function usageFor(slug) { return (D.usage && D.usage[slug]) ? D.usage[slug] : 0; }

    function renderCarousel() {
        const track = $('#ll-bt-track');
        track.innerHTML = '';
        D.themes.forEach(t => {
            const used = usageFor(t.slug);
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'll-bt-card' + (t.slug === state.slug ? ' is-active' : '');
            card.dataset.slug = t.slug;
            card.innerHTML =
                '<span class="ll-bt-thumb" style="background:' + gradientFor(t) + '">' +
                    '<span class="ll-bt-used"><i class="bi bi-people-fill"></i> ' + used + (used === 1 ? ' use' : ' uses') + '</span>' +
                '</span>' +
                '<span class="ll-bt-card-name">' + escapeHtml(t.name || t.slug) + '</span>' +
                '<span class="ll-bt-card-author">by ' + escapeHtml(t.authorHandle || ('@' + (t.author || 'livelatch'))) + '</span>' +
                '<span class="ll-bt-stars">★★★★★ <span>ratings soon</span></span>';
            card.addEventListener('click', () => selectTheme(t.slug));
            track.appendChild(card);
        });
    }

    function selectTheme(slug) {
        if (!themesBySlug[slug]) return;
        state.slug = slug;
        resetSettingsToTheme(slug);
        renderCarousel();
        renderPresets();
        renderColours();
        renderTypography();
        renderSliders();
        renderCustomCss();
        schedulePreview();
    }

    // ---- Presets ----
    function renderPresets() {
        const wrap = $('#ll-bt-presets');
        wrap.innerHTML = '';
        const presets = manifest().presets || {};
        Object.keys(presets).forEach(key => {
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.className = 'll-bt-pill';
            pill.textContent = titleCase(key);
            pill.addEventListener('click', () => {
                Object.assign(state.settings, presets[key]);
                renderColours();
                renderTypography();
                renderSliders();
                markActivePreset(pill);
                schedulePreview();
            });
            wrap.appendChild(pill);
        });
    }
    function markActivePreset(active) {
        document.querySelectorAll('#ll-bt-presets .ll-bt-pill').forEach(p => p.classList.toggle('is-active', p === active));
    }

    // ---- Colour controls ----
    function renderColours() {
        const wrap = $('#ll-bt-colours');
        wrap.innerHTML = '';
        const colours = controls().colours || [];
        colours.forEach(c => {
            const val = normaliseHex(state.settings[c.key] || (defaultsFor(state.slug)[c.key]) || '#000000');
            state.settings[c.key] = val;
            const field = document.createElement('div');
            field.className = 'll-bt-colour';
            field.innerHTML =
                '<label>' + escapeHtml(c.label || c.key) + '</label>' +
                '<div class="ll-bt-colour-row">' +
                    '<input type="color" value="' + val + '">' +
                    '<input type="text" class="ll-bt-hex" value="' + val + '" maxlength="9">' +
                '</div>';
            const colour = field.querySelector('input[type=color]');
            const hex = field.querySelector('.ll-bt-hex');
            colour.addEventListener('input', () => { hex.value = colour.value; state.settings[c.key] = colour.value; schedulePreview(); });
            hex.addEventListener('change', () => {
                const v = normaliseHex(hex.value);
                hex.value = v; colour.value = v; state.settings[c.key] = v; schedulePreview();
            });
            wrap.appendChild(field);
        });
    }

    // ---- Typography ----
    function renderTypography() {
        const panel = $('#ll-bt-typography-panel');
        const fields = $('#ll-bt-type-fields');
        const typo = controls().typography;
        fields.innerHTML = '';
        if (!typo || !Object.keys(typo).length) { panel.hidden = true; return; }
        panel.hidden = false;

        Object.keys(typo).forEach(slotKey => {
            const slot = typo[slotKey];
            const key = slot.key;
            const current = state.settings[key] || slot.default || (slot.options && slot.options[0]) || 'Poppins';
            state.settings[key] = current;
            const field = document.createElement('div');
            field.className = 'll-bt-type-field';
            const opts = (slot.options || [current]).map(o =>
                '<option value="' + escapeHtml(o) + '"' + (o === current ? ' selected' : '') + ' style="font-family:\'' + escapeHtml(o) + '\'">' + escapeHtml(o) + '</option>'
            ).join('');
            field.innerHTML = '<label>' + escapeHtml(slot.label || slotKey) + '</label><select class="ll-bt-select" data-typo="' + slotKey + '">' + opts + '</select>';
            const select = field.querySelector('select');
            select.addEventListener('change', () => { state.settings[key] = select.value; applyTypePreview(); schedulePreview(); });
            fields.appendChild(field);
        });
        applyTypePreview();
    }
    function applyTypePreview() {
        const typo = controls().typography || {};
        const headingKey = typo.heading && typo.heading.key;
        const bodyKey = typo.body && typo.body.key;
        const heading = (headingKey && state.settings[headingKey]) || 'Poppins';
        const body = (bodyKey && state.settings[bodyKey]) || 'Poppins';
        $('#ll-bt-type-h1').style.fontFamily = '"' + heading + '", system-ui, sans-serif';
        $('#ll-bt-type-h2').style.fontFamily = '"' + heading + '", system-ui, sans-serif';
        $('#ll-bt-type-p').style.fontFamily = '"' + body + '", system-ui, sans-serif';
    }

    // ---- Sliders ----
    function renderSliders() {
        const panel = $('#ll-bt-effects-panel');
        const wrap = $('#ll-bt-sliders');
        const sliders = controls().sliders || {};
        wrap.innerHTML = '';
        if (!Object.keys(sliders).length) { panel.hidden = true; return; }
        panel.hidden = false;

        Object.keys(sliders).forEach(key => {
            const cfg = sliders[key];
            const val = (state.settings[key] != null) ? state.settings[key] : (cfg.default != null ? cfg.default : cfg.min || 0);
            state.settings[key] = val;
            const row = document.createElement('div');
            row.className = 'll-bt-slider';
            row.innerHTML =
                '<div class="ll-bt-slider-head"><span>' + escapeHtml(cfg.label || key) + '</span><strong data-out>' + val + '</strong></div>' +
                '<input type="range" class="ll-bt-range" min="' + (cfg.min || 0) + '" max="' + (cfg.max || 100) + '" step="' + (cfg.step || 1) + '" value="' + val + '">';
            const range = row.querySelector('input');
            const out = row.querySelector('[data-out]');
            range.addEventListener('input', () => { out.textContent = range.value; state.settings[key] = Number(range.value); });
            range.addEventListener('change', () => { state.settings[key] = Number(range.value); schedulePreview(); });
            wrap.appendChild(row);
        });
    }

    // ---- Custom CSS ----
    function renderCustomCss() {
        const panel = $('#ll-bt-css-panel');
        const ta = $('#ll-bt-css');
        const enabled = !!(controls().customCss && controls().customCss.pro);
        panel.hidden = !enabled;
        if (!enabled) return;
        ta.value = state.settings.customCss || '';
        ta.disabled = !D.isPro;
        if (D.isPro && !ta.dataset.bound) {
            ta.dataset.bound = '1';
            let t;
            ta.addEventListener('input', () => {
                state.settings.customCss = ta.value;
                clearTimeout(t); t = setTimeout(schedulePreview, 700);
            });
        }
    }

    // ---- Preview ----
    let previewTimer;
    function schedulePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 450);
    }
    function buildPreviewUrl() {
        const base = D.urls.previewBase.replace('__SLUG__', encodeURIComponent(state.slug));
        const params = new URLSearchParams();
        Object.keys(state.settings).forEach(k => {
            const v = state.settings[k];
            if (v !== undefined && v !== null && v !== '') params.append('s[' + k + ']', v);
        });
        const qs = params.toString();
        return qs ? (base + '?' + qs) : base;
    }
    function refreshPreview() {
        $('#ll-bt-iframe').src = buildPreviewUrl();
    }

    // ---- Devices ----
    function renderDevices() {
        const wrap = $('#ll-bt-devices');
        wrap.innerHTML = '';
        Object.keys(DEVICES).forEach(key => {
            const d = DEVICES[key];
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'll-bt-device-btn' + (key === state.device ? ' is-active' : '');
            const icon = key === 'phone' ? 'bi-phone' : (key === 'tablet' ? 'bi-tablet' : 'bi-display');
            btn.innerHTML = '<i class="bi ' + icon + '"></i> ' + d.label;
            btn.addEventListener('click', () => { state.device = key; renderDevices(); fitDevice(); });
            wrap.appendChild(btn);
        });
    }
    function fitDevice() {
        const d = DEVICES[state.device];
        const stage = $('#ll-bt-stage');
        const scaler = $('#ll-bt-scaler');
        const frame = $('#ll-bt-frame');
        const availW = stage.clientWidth || 360;
        const maxH = 660;
        const scale = Math.min(availW / d.w, maxH / d.h, 1);
        frame.classList.toggle('has-bezel', !!d.bezel);
        const radius = d.bezel ? (state.device === 'phone' ? 46 : 26) : 12;
        frame.style.width = d.w + 'px';
        frame.style.height = d.h + 'px';
        frame.style.borderRadius = radius + 'px';
        frame.style.transform = 'scale(' + scale + ')';
        scaler.style.width = (d.w * scale) + 'px';
        scaler.style.height = (d.h * scale) + 'px';
        $('#ll-bt-preview-meta').textContent = d.label + ' · ' + d.w + ' × ' + d.h;
    }

    // ---- Carousel arrows ----
    function bindArrows() {
        const track = $('#ll-bt-track');
        $('#ll-bt-prev').addEventListener('click', () => track.scrollBy({ left: -210, behavior: 'smooth' }));
        $('#ll-bt-next').addEventListener('click', () => track.scrollBy({ left: 210, behavior: 'smooth' }));
    }

    // ---- Save / Reset ----
    function toast(el, msg) {
        const box = $(el);
        box.textContent = msg;
        box.classList.remove('d-none');
        setTimeout(() => box.classList.add('d-none'), 4000);
    }
    function bindActions() {
        $('#ll-bt-save').addEventListener('click', () => {
            const btn = $('#ll-bt-save');
            btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Applying';
            postJson(D.urls.update, { theme_slug: state.slug, settings: state.settings })
                .then(() => { D.current = { theme_slug: state.slug, settings: Object.assign({}, state.settings) }; toast('#ll-bt-toast', 'Theme applied to your public profile.'); })
                .catch(err => toast('#ll-bt-error', firstError(err)))
                .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2-circle"></i> Apply theme'; });
        });
        $('#ll-bt-reset').addEventListener('click', () => {
            if (!confirm('Revert your public profile to the standard theme?')) return;
            postJson(D.urls.reset, {})
                .then(() => { D.current = null; toast('#ll-bt-toast', 'Reverted to your standard theme.'); })
                .catch(err => toast('#ll-bt-error', firstError(err)));
        });
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrf },
            credentials: 'same-origin',
            redirect: 'manual',
            body: JSON.stringify(body)
        }).then(async (r) => {
            // A redirect (opaque/0 or 3xx) means the request was intercepted
            // (auth/CSRF/middleware) rather than handled — treat as failure.
            if (r.type === 'opaqueredirect' || (r.status >= 300 && r.status < 400)) {
                throw { status: r.status || 302, message: 'Request was redirected — likely a session/permission issue.' };
            }
            const text = await r.text();
            let data = {};
            try { data = text ? JSON.parse(text) : {}; } catch (e) { data = { _notJson: true, _raw: text.slice(0, 300) }; }
            if (!r.ok || data._notJson) {
                throw { status: r.status, data };
            }
            return data;
        });
    }
    function firstError(err) {
        console.error('[themes-beta] save failed:', err);
        if (err && err.data && err.data.errors) {
            const k = Object.keys(err.data.errors)[0];
            const v = err.data.errors[k];
            return Array.isArray(v) ? v[0] : v;
        }
        if (err && err.status === 419) return 'Session expired — refresh the page and try again.';
        if (err && err.status === 401) return 'You are signed out — refresh and sign in again.';
        if (err && err.status === 403) return 'Not allowed to save this theme.';
        if (err && err.status === 500) return 'Server error — the user_blade_theme_settings table may not be migrated yet.';
        if (err && err.message) return err.message;
        if (err && err.status) return 'Save failed (HTTP ' + err.status + ').';
        return 'Could not save — check your connection.';
    }

    // ---- Helpers ----
    function escapeHtml(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
    function titleCase(s) { return String(s || '').replace(/[-_]+/g, ' ').replace(/\b\w/g, m => m.toUpperCase()); }
    function normaliseHex(v) {
        v = String(v || '').trim();
        if (!v.startsWith('#')) v = '#' + v;
        return /^#[0-9a-fA-F]{3,8}$/.test(v) ? v : '#000000';
    }

    // ---- Boot ----
    resetSettingsToTheme(state.slug);
    renderCarousel();
    renderPresets();
    renderColours();
    renderTypography();
    renderSliders();
    renderCustomCss();
    renderDevices();
    bindArrows();
    bindActions();
    fitDevice();
    refreshPreview();
    window.addEventListener('resize', fitDevice);
})();
</script>
@endverbatim
@endsection
