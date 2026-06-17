{{--
    Liquid Glass — optional Studio visual layer
    ------------------------------------------------------------------
    This is a self-contained, additive visual effect toggled from the
    admin Dev Tools page. It never changes layout or behaviour, only the
    appearance of existing surfaces when [data-ll-glass="on"] is present
    on the <html> element.

    It is deliberately isolated so it can be removed cleanly. To remove:
      1. Delete this file.
      2. Remove the @include('layouts.liquid-glass') line in
         resources/views/layouts/sidebar.blade.php.
      3. Remove the "Liquid glass" toggle button + its handler in
         resources/views/studio/admin/dev-tools.blade.php.
      4. Delete docs/platform-runtime/liquid-glass.md.
    Full notes: docs/platform-runtime/liquid-glass.md
--}}
<style data-ll-liquid-glass>
    [data-ll-glass="on"] .ll-sidebar,
    [data-ll-glass="on"] .ll-topbar,
    [data-ll-glass="on"] #ll-content .card,
    [data-ll-glass="on"] #ll-content .ll-dashboard-card,
    [data-ll-glass="on"] #ll-content .ll-dashboard-hero,
    [data-ll-glass="on"] #ll-content .ll-dashboard-status,
    [data-ll-glass="on"] #ll-content .ll-appearance-card,
    [data-ll-glass="on"] #ll-content .ll-dev-panel {
        background: color-mix(in srgb, var(--ll-surface-solid) 58%, transparent) !important;
        backdrop-filter: blur(18px) saturate(165%);
        -webkit-backdrop-filter: blur(18px) saturate(165%);
        border-color: color-mix(in srgb, var(--ll-text) 10%, transparent) !important;
        box-shadow:
            0 18px 50px color-mix(in srgb, var(--ll-text) 14%, transparent),
            inset 0 1px 0 color-mix(in srgb, #ffffff 45%, transparent) !important;
    }

    [data-ll-glass="on"][data-ll-theme="dark"] .ll-sidebar,
    [data-ll-glass="on"][data-ll-theme="dark"] .ll-topbar,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .card,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .ll-dashboard-card,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .ll-dashboard-hero,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .ll-dashboard-status,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .ll-appearance-card,
    [data-ll-glass="on"][data-ll-theme="dark"] #ll-content .ll-dev-panel {
        background: color-mix(in srgb, var(--ll-surface-solid) 48%, transparent) !important;
        box-shadow:
            0 18px 50px rgba(0, 0, 0, 0.4),
            inset 0 1px 0 color-mix(in srgb, #ffffff 14%, transparent) !important;
    }

    @media (prefers-reduced-transparency: reduce) {
        [data-ll-glass="on"] .ll-sidebar,
        [data-ll-glass="on"] .ll-topbar,
        [data-ll-glass="on"] #ll-content .card,
        [data-ll-glass="on"] #ll-content .ll-dashboard-card,
        [data-ll-glass="on"] #ll-content .ll-dashboard-hero,
        [data-ll-glass="on"] #ll-content .ll-dashboard-status,
        [data-ll-glass="on"] #ll-content .ll-appearance-card,
        [data-ll-glass="on"] #ll-content .ll-dev-panel {
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            background: var(--ll-surface-solid) !important;
        }
    }
</style>
<script data-ll-liquid-glass-init>
    (function () {
        var root = document.documentElement;
        var key = 'livelatch-glass';

        function read() {
            try {
                return window.localStorage && window.localStorage.getItem(key) === 'on';
            } catch (error) {
                return false;
            }
        }

        function store(on) {
            try {
                if (window.localStorage) {
                    window.localStorage.setItem(key, on ? 'on' : 'off');
                }
            } catch (error) {
                // Storage can be blocked in private or restricted browser modes.
            }
        }

        function apply(on) {
            if (on) {
                root.setAttribute('data-ll-glass', 'on');
            } else {
                root.removeAttribute('data-ll-glass');
            }
        }

        // Apply the persisted state immediately, mirroring the light/dark toggle.
        apply(read());

        window.LivelatchGlass = {
            isOn: function () {
                return root.getAttribute('data-ll-glass') === 'on';
            },
            set: function (on) {
                apply(on);
                store(on);
            },
            toggle: function () {
                var next = !this.isOn();
                this.set(next);
                return next;
            }
        };
    }());
</script>
