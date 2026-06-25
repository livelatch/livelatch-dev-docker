@extends('layouts.sidebar')

@section('content')
<style data-ll-bt-style>
    @import url('https://fonts.googleapis.com/css2?family=Anton&family=Baloo+2:wght@400;500;600;700;800&family=Chakra+Petch:wght@400;500;600;700&family=Cinzel:wght@400;500;600;700&family=Cinzel+Decorative:wght@400;700&family=Comfortaa:wght@400;500;600;700&family=Exo+2:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Kosugi+Maru&family=Lato:wght@400;700&family=MedievalSharp&family=Mochiy+Pop+One&family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800&family=Oswald:wght@400;500;600;700&family=Pacifico&family=Pixelify+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Press+Start+2P&family=Rajdhani:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Roboto+Mono:wght@400;500;700&family=Share+Tech+Mono&family=Silkscreen:wght@400;700&family=Source+Code+Pro:wght@400;500;600;700&family=Tomorrow:wght@400;500;600;700&family=VT323&display=swap');

    .ll-bt-page { display: grid; gap: 20px; }

    /* ---- Header ---- */
    .ll-bt-top {
        display: flex; flex-wrap: wrap; gap: 14px 20px;
        align-items: flex-end; justify-content: space-between;
    }
    .ll-bt-top h1 {
        color: var(--ll-text);
        font-size: clamp(1.6rem, 2.5vw, 2.15rem);
        line-height: 1;
        margin: 0 0 7px;
        display: flex;
        align-items: center;
        gap: 11px;
    }
    .ll-bt-beta {
        font-size: .58rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        border-radius: 999px;
        padding: 4px 9px;
        transform: translateY(-3px);
    }
    .ll-bt-top p { color: var(--ll-muted); margin: 0; max-width: 62ch; font-size: .9rem; }

    .ll-bt-actions { display: inline-flex; gap: 10px; align-items: center; }
    .ll-bt-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border: 0; cursor: pointer;
        border-radius: var(--ll-button-radius);
        font-weight: 700; font-size: .92rem; padding: 11px 18px;
        transition: transform .12s ease, box-shadow .16s ease, color .16s, opacity .16s;
    }
    .ll-bt-btn:hover { transform: translateY(-1px); }
    .ll-bt-btn-save { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; box-shadow: var(--ll-shadow-soft); }
    .ll-bt-btn-reset { background: transparent; color: var(--ll-muted); border: 1px solid var(--ll-border); }
    .ll-bt-btn-reset:hover { color: var(--ll-text); border-color: color-mix(in srgb, var(--ll-text) 28%, var(--ll-border)); }
    .ll-bt-btn[disabled] { opacity: .6; cursor: default; transform: none; }

    /* ---- Layout ---- */
    .ll-bt-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.32fr) minmax(330px, .68fr);
        gap: 20px;
        align-items: start;
    }
    .ll-bt-main { display: grid; gap: 20px; min-width: 0; }

    /* ---- Panels ---- */
    .ll-bt-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
        padding: 18px 20px;
        min-width: 0;
    }
    /* Panel header: title + right-aligned hint */
    .ll-bt-phead {
        display: flex; align-items: baseline; justify-content: space-between;
        gap: 14px; margin: 0 0 14px;
    }
    .ll-bt-phead h2 {
        color: var(--ll-text); font-size: 1rem; margin: 0;
        display: flex; align-items: center; gap: 9px;
    }
    .ll-bt-phead h2 i { color: var(--ll-primary); }
    .ll-bt-hint { color: var(--ll-muted); font-size: .76rem; text-align: right; }

    /* ---- Base theme carousel ---- */
    .ll-bt-carousel { position: relative; }
    .ll-bt-track {
        display: flex; gap: 12px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        padding: 2px 2px 10px;
        scrollbar-width: thin;
    }
    .ll-bt-card {
        flex: 0 0 176px; width: 176px;
        scroll-snap-align: start;
        border: 1px solid var(--ll-border);
        border-radius: 16px;
        background: color-mix(in srgb, var(--ll-bg-soft) 45%, transparent);
        cursor: pointer;
        padding: 9px;
        text-align: left;
        display: grid; gap: 6px;
        transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
    }
    .ll-bt-card:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--ll-primary) 30%, var(--ll-border)); }
    .ll-bt-card.is-active {
        border-color: color-mix(in srgb, var(--ll-primary) 65%, var(--ll-border));
        box-shadow: 0 8px 26px color-mix(in srgb, var(--ll-primary) 16%, transparent);
    }
    .ll-bt-thumb {
        height: 84px; border-radius: 11px; position: relative; overflow: hidden;
        display: flex; align-items: flex-end; padding: 7px;
    }
    .ll-bt-thumb .ll-bt-used {
        font-size: .65rem; font-weight: 700; color: #fff;
        background: rgba(0,0,0,.42); border-radius: 999px; padding: 3px 8px;
        backdrop-filter: blur(4px); display: inline-flex; gap: 4px; align-items: center;
    }
    .ll-bt-card-name { color: var(--ll-text); font-weight: 700; font-size: .88rem; line-height: 1.15; }
    .ll-bt-card-author { color: var(--ll-muted); font-size: .73rem; margin-top: -2px; }
    .ll-bt-stars { color: #f5b301; font-size: .74rem; letter-spacing: 1px; }
    .ll-bt-stars span { color: var(--ll-muted); font-size: .65rem; margin-left: 4px; letter-spacing: 0; }

    .ll-bt-arrow {
        position: absolute; top: 28px; z-index: 3;
        width: 34px; height: 34px; border-radius: 50%;
        border: 1px solid var(--ll-border); background: var(--ll-surface-solid);
        color: var(--ll-text); cursor: pointer; box-shadow: var(--ll-shadow-soft);
        display: flex; align-items: center; justify-content: center; opacity: .92;
    }
    .ll-bt-arrow:hover { opacity: 1; }
    .ll-bt-arrow-prev { left: -10px; }
    .ll-bt-arrow-next { right: -10px; }

    /* Presets sit under the carousel, on a divider, labelled */
    .ll-bt-preset-row {
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        margin-top: 12px; padding-top: 14px;
        border-top: 1px solid color-mix(in srgb, var(--ll-border) 65%, transparent);
    }
    .ll-bt-preset-label { color: var(--ll-muted); font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .ll-bt-presets { display: flex; flex-wrap: wrap; gap: 7px; }
    .ll-bt-pill {
        border: 1px solid var(--ll-border); border-radius: 999px;
        background: transparent; color: var(--ll-text);
        cursor: pointer; font-weight: 700; font-size: .8rem; padding: 6px 13px;
        transition: border-color .14s, background .14s, color .14s;
    }
    .ll-bt-pill:hover { border-color: color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border)); }
    .ll-bt-pill.is-active { border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border)); background: color-mix(in srgb, var(--ll-primary) 12%, transparent); color: var(--ll-primary); }

    /* ---- Customise card: one panel, hairline-divided sections (no nested boxes) ---- */
    .ll-bt-customise { padding: 4px 20px; }
    .ll-bt-sec { padding: 18px 0; border-top: 1px solid color-mix(in srgb, var(--ll-border) 65%, transparent); }
    .ll-bt-sec:first-child { border-top: 0; }
    .ll-bt-sec[hidden] { display: none; }
    .ll-bt-sec-title {
        display: flex; align-items: center; gap: 8px;
        color: var(--ll-muted); font-size: .72rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em; margin: 0 0 14px;
    }
    .ll-bt-sec-title i { color: var(--ll-primary); font-size: .95rem; }
    .ll-bt-sec-title .ll-bt-pro-tag { margin-left: auto; }

    /* Colour — borderless swatch chips in an auto-fill grid */
    .ll-bt-colours { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 14px 16px; }
    .ll-bt-colour { border: 0; background: transparent; padding: 0; }
    .ll-bt-colour label { display: block; color: var(--ll-muted); font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 7px; }
    .ll-bt-colour-row { display: grid; grid-template-columns: 34px 1fr; gap: 8px; align-items: center; }
    .ll-bt-colour-row input[type="color"] { appearance: none; -webkit-appearance: none; border: 1px solid var(--ll-border); width: 34px; height: 34px; border-radius: 9px; padding: 0; cursor: pointer; background: none; }
    .ll-bt-colour-row input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
    .ll-bt-colour-row input[type="color"]::-webkit-color-swatch { border: 0; border-radius: 8px; }
    .ll-bt-hex { border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-bg-soft); color: var(--ll-text); font-family: ui-monospace, Consolas, monospace; font-size: .76rem; font-weight: 700; padding: 8px; width: 100%; text-transform: uppercase; }

    /* Typography — selects beside a live preview */
    .ll-bt-typo { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start; }
    .ll-bt-type-field { margin-bottom: 12px; }
    .ll-bt-type-field:last-child { margin-bottom: 0; }
    .ll-bt-type-field label { display: block; color: var(--ll-muted); font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
    .ll-bt-select { width: 100%; border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-bg-soft); color: var(--ll-text); font-weight: 600; padding: 9px 10px; }
    .ll-bt-type-preview { background: color-mix(in srgb, var(--ll-bg-soft) 55%, transparent); border: 1px solid color-mix(in srgb, var(--ll-border) 65%, transparent); border-radius: 12px; padding: 14px 16px; }
    .ll-bt-type-preview .h1 { font-size: 1.4rem; font-weight: 800; color: var(--ll-text); line-height: 1.1; margin: 0 0 4px; }
    .ll-bt-type-preview .h2 { font-size: 1.05rem; font-weight: 700; color: var(--ll-text); margin: 0 0 6px; }
    .ll-bt-type-preview p { color: var(--ll-muted); font-size: .86rem; margin: 0; }

    /* Effects */
    .ll-bt-slider { margin-bottom: 16px; }
    .ll-bt-slider:last-child { margin-bottom: 0; }
    .ll-bt-slider .ll-bt-slider-head { display: flex; justify-content: space-between; color: var(--ll-muted); font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
    .ll-bt-slider .ll-bt-slider-head strong { color: var(--ll-text); }
    .ll-bt-range { width: 100%; accent-color: var(--ll-primary); }

    /* Link icons section */
    .ll-bt-switch { display: flex; align-items: center; gap: 9px; cursor: pointer; color: var(--ll-text); font-weight: 600; font-size: .9rem; margin: 0 0 10px; }
    .ll-bt-switch input { width: 16px; height: 16px; accent-color: var(--ll-primary); flex: 0 0 auto; }
    .ll-bt-icon-color { padding-top: 4px; }
    .ll-bt-icon-color-pick { display: flex; align-items: center; gap: 10px; padding-left: 25px; }
    .ll-bt-icon-color-pick input[type="color"] { appearance: none; -webkit-appearance: none; border: 1px solid var(--ll-border); width: 34px; height: 34px; border-radius: 9px; padding: 0; cursor: pointer; background: none; }
    .ll-bt-icon-color-pick input[type="color"]:disabled { opacity: .45; cursor: default; }
    .ll-bt-icon-color-pick input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
    .ll-bt-icon-color-pick input[type="color"]::-webkit-color-swatch { border: 0; border-radius: 8px; }
    .ll-bt-icon-color-val { color: var(--ll-muted); font-size: .8rem; font-weight: 600; }

    /* Custom CSS */
    .ll-bt-css-wrap { position: relative; }
    .ll-bt-css { width: 100%; min-height: 120px; border: 1px solid var(--ll-border); border-radius: 12px; background: #0e1424; color: #e6edff; font-family: ui-monospace, Consolas, monospace; font-size: .82rem; padding: 12px; resize: vertical; }
    .ll-bt-pro-tag { font-size: .58rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #fff; background: linear-gradient(135deg, #f59e0b, #f5b301); border-radius: 999px; padding: 3px 8px; }
    .ll-bt-css-lock { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; background: color-mix(in srgb, var(--ll-surface-solid) 86%, transparent); backdrop-filter: blur(2px); text-align: center; color: var(--ll-muted); padding: 14px; }

    /* ---- Preview ---- */
    .ll-bt-preview-shell { position: sticky; top: 88px; }
    .ll-bt-devices { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 14px; }
    .ll-bt-device-btn { border: 1px solid var(--ll-border); border-radius: 999px; background: transparent; color: var(--ll-muted); cursor: pointer; font-weight: 700; font-size: .76rem; padding: 6px 11px; display: inline-flex; gap: 6px; align-items: center; transition: border-color .14s, color .14s, background .14s; }
    .ll-bt-device-btn:hover { color: var(--ll-text); }
    .ll-bt-device-btn.is-active { border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border)); color: var(--ll-primary); background: color-mix(in srgb, var(--ll-primary) 10%, transparent); }

    .ll-bt-stage { display: flex; justify-content: center; align-items: flex-start; min-height: 200px; }
    .ll-bt-scaler { position: relative; }
    .ll-bt-frame { position: absolute; top: 0; left: 0; transform-origin: top left; background: #000; overflow: hidden; box-shadow: 0 24px 60px rgba(8,12,30,.32); }
    .ll-bt-frame.has-bezel { border: 10px solid #0b0f1c; }
    .ll-bt-frame iframe { width: 100%; height: 100%; border: 0; display: block; background: #000; }
    .ll-bt-preview-meta { text-align: center; color: var(--ll-muted); font-size: .74rem; margin: 12px 0 0; }
    .ll-bt-preview-foot { text-align: center; color: var(--ll-muted); font-size: .72rem; margin: 4px 0 0; opacity: .82; }

    @media (max-width: 1199.98px) {
        .ll-bt-layout { grid-template-columns: 1fr; }
        .ll-bt-preview-shell { position: static; }
    }
    @media (max-width: 680px) {
        .ll-bt-top { align-items: flex-start; }
        .ll-bt-typo { grid-template-columns: 1fr; }
        .ll-bt-customise { padding: 4px 16px; }
        .ll-bt-panel { padding: 16px; }
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
                <div class="ll-bt-phead">
                    <h2><i class="bi bi-boxes"></i> Base theme</h2>
                    <span class="ll-bt-hint">Authored by Livelatch — every edit derives from one of these.</span>
                </div>
                <div class="ll-bt-carousel">
                    <button type="button" class="ll-bt-arrow ll-bt-arrow-prev" id="ll-bt-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="ll-bt-arrow ll-bt-arrow-next" id="ll-bt-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                    <div class="ll-bt-track" id="ll-bt-track"></div>
                </div>
                <div class="ll-bt-preset-row">
                    <span class="ll-bt-preset-label">Presets</span>
                    <div class="ll-bt-presets" id="ll-bt-presets"></div>
                </div>
            </section>

            <section class="ll-bt-panel ll-bt-customise">
                <div class="ll-bt-sec">
                    <div class="ll-bt-sec-title"><i class="bi bi-palette"></i> Colour</div>
                    <div class="ll-bt-colours" id="ll-bt-colours"></div>
                </div>

                <div class="ll-bt-sec" id="ll-bt-typography-panel">
                    <div class="ll-bt-sec-title"><i class="bi bi-fonts"></i> Typography</div>
                    <div class="ll-bt-typo">
                        <div id="ll-bt-type-fields"></div>
                        <div class="ll-bt-type-preview">
                            <div class="h1" id="ll-bt-type-h1">Heading 1</div>
                            <div class="h2" id="ll-bt-type-h2">Heading 2</div>
                            <p id="ll-bt-type-p">Paragraph — the quick brown fox jumps over the lazy dog.</p>
                        </div>
                    </div>
                </div>

                <div class="ll-bt-sec" id="ll-bt-effects-panel">
                    <div class="ll-bt-sec-title"><i class="bi bi-sliders"></i> Effects</div>
                    <div id="ll-bt-sliders"></div>
                </div>

                <div class="ll-bt-sec" id="ll-bt-icons-panel">
                    <div class="ll-bt-sec-title"><i class="bi bi-link-45deg"></i> Link icons</div>
                    <label class="ll-bt-switch">
                        <input type="checkbox" id="ll-bt-show-icons" checked>
                        <span>Show icons on links</span>
                    </label>
                    <div class="ll-bt-icon-color" id="ll-bt-icon-color-row">
                        <label class="ll-bt-switch">
                            <input type="checkbox" id="ll-bt-icon-color-toggle">
                            <span>Custom icon colour</span>
                        </label>
                        <div class="ll-bt-icon-color-pick">
                            <input type="color" id="ll-bt-icon-color" value="#ffffff" disabled>
                            <span class="ll-bt-icon-color-val" id="ll-bt-icon-color-val">Match link colour</span>
                        </div>
                    </div>
                    <p class="ll-bt-hint" style="text-align:left;margin-top:6px;">Icons come from each link's Simple Icon. Off = no icons; custom colour off = matches the link's text colour.</p>
                </div>

                <div class="ll-bt-sec" id="ll-bt-css-panel" hidden>
                    <div class="ll-bt-sec-title"><i class="bi bi-code-slash"></i> Custom CSS <span class="ll-bt-pro-tag">Pro</span></div>
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
                </div>
            </section>
        </div>

        <aside class="ll-bt-preview-shell">
            <section class="ll-bt-panel">
                <div class="ll-bt-phead">
                    <h2><i class="bi bi-phone"></i> Live preview</h2>
                </div>
                <div class="ll-bt-devices" id="ll-bt-devices"></div>
                <div class="ll-bt-stage" id="ll-bt-stage">
                    <div class="ll-bt-scaler" id="ll-bt-scaler">
                        <div class="ll-bt-frame has-bezel" id="ll-bt-frame">
                            <iframe id="ll-bt-iframe" title="Theme preview" referrerpolicy="no-referrer"></iframe>
                        </div>
                    </div>
                </div>
                <p class="ll-bt-preview-meta" id="ll-bt-preview-meta">iPhone 17 Pro Max · 440 × 956</p>
                <p class="ll-bt-preview-foot">Preview reflects unsaved edits. Nothing is public until you press Apply theme.</p>
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
        syncIconControls();
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

    // ---- Link icons (universal — applies to every blade theme) ----
    function syncIconControls() {
        const show = $('#ll-bt-show-icons');
        const colToggle = $('#ll-bt-icon-color-toggle');
        const col = $('#ll-bt-icon-color');
        const colVal = $('#ll-bt-icon-color-val');
        if (!show) return;
        const showVal = String(state.settings.showLinkIcons == null ? '1' : state.settings.showLinkIcons) !== '0';
        show.checked = showVal;
        const c = state.settings.linkIconColor || '';
        const hasC = /^#[0-9a-fA-F]{3,8}$/.test(c);
        colToggle.checked = hasC;
        col.value = hasC ? c : '#ffffff';
        col.disabled = !hasC;
        colVal.textContent = hasC ? c : 'Match link colour';
    }
    function bindIconControls() {
        const show = $('#ll-bt-show-icons');
        const colToggle = $('#ll-bt-icon-color-toggle');
        const col = $('#ll-bt-icon-color');
        const colVal = $('#ll-bt-icon-color-val');
        if (!show) return;
        show.addEventListener('change', () => {
            state.settings.showLinkIcons = show.checked ? '1' : '0';
            schedulePreview();
        });
        function applyColor() {
            if (colToggle.checked) {
                col.disabled = false;
                state.settings.linkIconColor = col.value;
                colVal.textContent = col.value;
            } else {
                col.disabled = true;
                delete state.settings.linkIconColor;
                colVal.textContent = 'Match link colour';
            }
            schedulePreview();
        }
        colToggle.addEventListener('change', applyColor);
        col.addEventListener('input', applyColor);
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
    bindIconControls();
    syncIconControls();
    fitDevice();
    refreshPreview();
    window.addEventListener('resize', fitDevice);
})();
</script>
@endverbatim
@endsection
