@extends('layouts.sidebar')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@xterm/xterm@5.5.0/css/xterm.min.css" />
<style data-ll-shell-style>
    .ll-shell { display: grid; gap: 18px; }

    .ll-shell-header h2 { margin: 0 0 4px; color: var(--ll-text); }
    .ll-shell-header p { margin: 0; color: var(--ll-muted); }

    .ll-shell-warning {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 16px;
        border: 1px solid color-mix(in srgb, #e5484d 45%, var(--ll-border));
        border-radius: var(--ll-radius);
        background: color-mix(in srgb, #e5484d 10%, var(--ll-surface-solid));
        color: var(--ll-text);
    }
    .ll-shell-warning i { color: #e5484d; font-size: 1.2rem; line-height: 1.4; }
    .ll-shell-warning strong { display: block; margin-bottom: 2px; }
    .ll-shell-warning span { color: var(--ll-muted); font-size: 0.88rem; }

    .ll-shell-term {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: #0b0e14;
        box-shadow: var(--ll-shadow-soft);
        padding: 12px;
        min-height: 360px;
    }
    .ll-shell-term .xterm { height: 100%; }

    .ll-shell-form { display: flex; gap: 10px; align-items: stretch; flex-wrap: wrap; }
    .ll-shell-input {
        flex: 1; min-width: 240px;
        min-height: 44px; padding: 0 14px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-button-radius);
        background: var(--ll-bg-soft); color: var(--ll-text);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 0.9rem;
    }
    .ll-shell-input:disabled { opacity: 0.6; }

    .ll-shell-btn {
        display: inline-flex; align-items: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border: 0;
        border-radius: var(--ll-button-radius);
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        color: #fff; font-weight: 600; cursor: pointer;
        transition: transform 150ms ease;
    }
    .ll-shell-btn:hover { transform: translateY(-1px); }
    .ll-shell-btn:disabled { opacity: 0.55; cursor: progress; transform: none; }

    .ll-shell-ghost {
        display: inline-flex; align-items: center; gap: 8px;
        min-height: 44px; padding: 0 16px;
        border: 1px solid var(--ll-border); border-radius: var(--ll-button-radius);
        background: var(--ll-surface-solid); color: var(--ll-text);
        font-weight: 600; cursor: pointer;
    }

    .ll-shell-hint { color: var(--ll-muted); font-size: 0.82rem; margin: 0; }
    .ll-shell-hint code { color: var(--ll-text); }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-shell" id="ll-shell">
        <div class="ll-shell-header">
            <h2><i class="bi bi-terminal-fill"></i> Shell</h2>
            <p>Run a command inside the live Railway container this site is served from. Output streams below.</p>
        </div>

        <div class="ll-shell-warning">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <strong>This runs real commands in production.</strong>
                <span>
                    Every command is recorded to the <code>shell</code> audit log with your account and timestamp.
                    There is no pseudo-terminal — interactive programs (<code>vim</code>, <code>top</code>,
                    <code>artisan tinker</code>) won't work; use <code>railway ssh</code> for those.
                    Commands are killed after 120s.
                </span>
            </div>
        </div>

        <div class="ll-shell-term"><div id="ll-shell-xterm"></div></div>

        <form class="ll-shell-form" id="ll-shell-form" autocomplete="off">
            <input type="text" class="ll-shell-input" id="ll-shell-command"
                   placeholder="e.g.  php artisan migrate --force" spellcheck="false" autofocus>
            <button type="submit" class="ll-shell-btn" id="ll-shell-run">
                <i class="bi bi-play-fill"></i> Run
            </button>
            <button type="button" class="ll-shell-ghost" id="ll-shell-clear">
                <i class="bi bi-eraser"></i> Clear
            </button>
        </form>
        <p class="ll-shell-hint">Runs from the project root via <code>bash -lc</code>. Press <code>↑</code>/<code>↓</code> for history.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@xterm/xterm@5.5.0/lib/xterm.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@xterm/addon-fit@0.10.0/lib/addon-fit.min.js"></script>
<script>
    (() => {
        const root = document.getElementById('ll-shell');
        if (!root || root.dataset.initialized === 'true' || typeof Terminal === 'undefined') return;
        root.dataset.initialized = 'true';

        const csrf = '{{ csrf_token() }}';
        const runUrl = '{{ route('admin.shell.run') }}';

        const term = new Terminal({
            convertEol: true,
            fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace',
            fontSize: 13,
            theme: { background: '#0b0e14', foreground: '#d7dce5' },
            cursorBlink: false,
            disableStdin: true,
        });
        const fit = new FitAddon.FitAddon();
        term.loadAddon(fit);
        term.open(document.getElementById('ll-shell-xterm'));
        try { fit.fit(); } catch (e) {}
        window.addEventListener('resize', () => { try { fit.fit(); } catch (e) {} });
        term.writeln('\x1b[90mLivelatch admin shell — ready.\x1b[0m');

        const form = document.getElementById('ll-shell-form');
        const input = document.getElementById('ll-shell-command');
        const runBtn = document.getElementById('ll-shell-run');
        const clearBtn = document.getElementById('ll-shell-clear');

        const history = [];
        let histIndex = -1;
        let running = false;

        function setRunning(on) {
            running = on;
            input.disabled = on;
            runBtn.disabled = on;
            runBtn.innerHTML = on
                ? '<i class="bi bi-hourglass-split"></i> Running…'
                : '<i class="bi bi-play-fill"></i> Run';
            if (!on) input.focus();
        }

        async function run(command) {
            setRunning(true);
            term.writeln('\x1b[36m$ ' + command + '\x1b[0m');
            try {
                const res = await fetch(runUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'command=' + encodeURIComponent(command),
                });
                if (!res.ok) {
                    term.writeln('\x1b[31m[shell] request failed: HTTP ' + res.status + '\x1b[0m');
                } else {
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        term.write(decoder.decode(value, { stream: true }));
                    }
                }
            } catch (err) {
                term.writeln('\x1b[31m[shell] ' + err + '\x1b[0m');
            } finally {
                term.writeln('');
                setRunning(false);
            }
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const command = input.value.trim();
            if (!command || running) return;
            if (history[history.length - 1] !== command) history.push(command);
            histIndex = history.length;
            input.value = '';
            run(command);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp') {
                if (histIndex > 0) { histIndex--; input.value = history[histIndex]; }
                e.preventDefault();
            } else if (e.key === 'ArrowDown') {
                if (histIndex < history.length - 1) { histIndex++; input.value = history[histIndex]; }
                else { histIndex = history.length; input.value = ''; }
                e.preventDefault();
            }
        });

        clearBtn.addEventListener('click', () => { term.clear(); input.focus(); });
    })();
</script>
@endsection
