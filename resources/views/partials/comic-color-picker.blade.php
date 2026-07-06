{{--
    Comic swatch colour picker — progressive enhancement for every
    input[type="color"] rendered inside the Studio layout.

    Design adapted from uiverse.io/chase2k25/witty-squid-83 (MIT) — see
    docs/credits.md. The native input stays alive (moved inside the "custom"
    swatch, opacity 0) so forms, labels and existing JS listeners keep
    working; preset swatches set input.value and dispatch input/change.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">

<style data-ll-comic-picker-style>
    .ll-comic-picker {
        display: inline-flex;
        background: #ffffff;
        border: 3px solid #000;
        padding: 0.55rem 0.7rem;
        border-radius: 8px;
        box-shadow: 4px 4px 0px rgba(0, 0, 0, 1);
        font-family: "Bangers", cursive;
        vertical-align: middle;
        max-width: 100%;
        /* Escape narrow fixed grid tracks (e.g. the 48px swatch column in
           .ll-colour-row) — ignored by non-grid parents. */
        grid-column: 1 / -1;
        margin: 2px 0;
    }

    .ll-comic-picker.is-disabled {
        opacity: 0.45;
        pointer-events: none;
    }

    .ll-comic-picker .ll-comic-items {
        display: flex;
        flex-wrap: wrap;
        transform-style: preserve-3d;
        transform: perspective(1000px);
    }

    .ll-comic-picker .ll-comic-swatch {
        position: relative;
        flex-shrink: 0;
        width: 34px;
        height: 40px;
        border: none;
        outline: none;
        margin: -3px;
        padding: 0;
        background-color: transparent;
        transition: 300ms ease-out;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .ll-comic-picker .ll-comic-swatch::after {
        position: absolute;
        content: "";
        inset: 0;
        width: 34px;
        height: 34px;
        background: var(--color, #ffffff);
        border-radius: 6px;
        border: 3px solid #000;
        box-shadow: 4px 4px 0 0 #000;
        pointer-events: none;
        transition: 300ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .ll-comic-picker .ll-comic-swatch::before {
        position: absolute;
        content: attr(data-color);
        left: 50%;
        bottom: 50px;
        font-size: 15px;
        letter-spacing: 1px;
        line-height: 1;
        padding: 6px 10px;
        background-color: #fef3c7;
        color: #000;
        border: 3px solid #000;
        border-radius: 6px;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transform-origin: bottom center;
        transition:
            all 300ms cubic-bezier(0.175, 0.885, 0.32, 1.275),
            opacity 300ms ease-out,
            visibility 300ms ease-out;
        transform: translateX(-50%) scale(0.5) translateY(10px);
        white-space: nowrap;
        z-index: 100000;
    }

    .ll-comic-picker .ll-comic-swatch:hover {
        transform: scale(1.5) translateY(-5px);
        z-index: 99999;
    }

    .ll-comic-picker .ll-comic-swatch:hover::before,
    .ll-comic-picker .ll-comic-swatch:focus-visible::before {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) scale(1) translateY(0);
    }

    .ll-comic-picker .ll-comic-swatch:active::after {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0 0 #000;
    }

    .ll-comic-picker .ll-comic-swatch.is-selected::after {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0 0 #000, inset 0 0 0 2px #fff;
    }

    .ll-comic-picker .ll-comic-swatch:hover + * {
        transform: scale(1.3) translateY(-3px);
        z-index: 9999;
    }

    .ll-comic-picker .ll-comic-swatch:hover + * + * {
        transform: scale(1.15);
        z-index: 999;
    }

    .ll-comic-picker .ll-comic-swatch:has(+ *:hover) {
        transform: scale(1.3) translateY(-3px);
        z-index: 9999;
    }

    .ll-comic-picker .ll-comic-swatch:has(+ * + *:hover) {
        transform: scale(1.15);
        z-index: 999;
    }

    /* The "custom" swatch hosts the real native input (kept alive for forms,
       labels, and existing JS) at opacity 0 — clicking it opens the OS picker. */
    .ll-comic-picker .ll-comic-swatch.ll-comic-custom::after {
        background:
            linear-gradient(var(--color, #ffffff), var(--color, #ffffff)) padding-box,
            conic-gradient(#e11d48, #facc15, #10b981, #3b82f6, #8b5cf6, #e11d48) border-box;
        border-color: transparent;
        box-shadow: 4px 4px 0 0 #000, inset 0 0 0 2px #000;
    }

    .ll-comic-picker .ll-comic-swatch.ll-comic-custom input[type="color"] {
        position: absolute;
        inset: 0;
        width: 34px;
        height: 34px;
        opacity: 0;
        cursor: pointer;
        border: none;
        padding: 0;
        z-index: 2;
    }

    @media (prefers-reduced-motion: reduce) {
        .ll-comic-picker .ll-comic-swatch,
        .ll-comic-picker .ll-comic-swatch::after,
        .ll-comic-picker .ll-comic-swatch::before {
            transition: none;
        }
    }
</style>

<script data-ll-comic-picker-script>
    (function () {
        const PRESETS = [
            '#e11d48', '#f472b6', '#fb923c', '#facc15', '#84cc16',
            '#10b981', '#0ea5e9', '#3b82f6', '#8b5cf6', '#a78bfa'
        ];

        function normalize(value) {
            return (value || '').toLowerCase();
        }

        function enhance(input) {
            if (!input || input.dataset.llComic === '1' || !input.isConnected) {
                return;
            }

            input.dataset.llComic = '1';

            const panel = document.createElement('div');
            panel.className = 'll-comic-picker';
            const row = document.createElement('div');
            row.className = 'll-comic-items';
            panel.appendChild(row);

            const swatches = [];

            PRESETS.forEach(function (color) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'll-comic-swatch';
                btn.style.setProperty('--color', color);
                btn.dataset.color = color;
                btn.setAttribute('aria-label', 'Set colour ' + color);
                btn.addEventListener('click', function () {
                    if (input.disabled) return;
                    input.value = color;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    sync();
                });
                swatches.push(btn);
                row.appendChild(btn);
            });

            const custom = document.createElement('span');
            custom.className = 'll-comic-swatch ll-comic-custom';
            custom.setAttribute('role', 'button');
            custom.setAttribute('aria-label', 'Pick a custom colour');
            row.appendChild(custom);

            // Swap the native input into the custom swatch, panel takes its place.
            input.parentNode.insertBefore(panel, input);
            custom.appendChild(input);

            function sync() {
                const value = normalize(input.value);
                swatches.forEach(function (btn) {
                    btn.classList.toggle('is-selected', normalize(btn.dataset.color) === value);
                });
                const isPreset = PRESETS.some(function (c) { return normalize(c) === value; });
                custom.style.setProperty('--color', isPreset ? '#ffffff' : input.value);
                custom.dataset.color = isPreset ? 'CUSTOM…' : input.value;
                custom.classList.toggle('is-selected', !isPreset);
                panel.classList.toggle('is-disabled', input.disabled);
            }

            input.addEventListener('input', sync);
            input.addEventListener('change', sync);

            new MutationObserver(sync).observe(input, { attributes: true, attributeFilter: ['disabled', 'value'] });

            sync();
        }

        function scan(root) {
            const scope = root && root.querySelectorAll ? root : document;
            scope.querySelectorAll('input[type="color"]').forEach(enhance);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { scan(document); });
        } else {
            scan(document);
        }

        // Catch HTMX swaps, Livewire updates, and JS-built inputs (Theme Studio Beta).
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) scan(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    })();
</script>
