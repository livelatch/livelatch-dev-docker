@extends('layouts.sidebar')

@section('content')
<style data-ll-shell-style>
    .ll-shellpage { display: grid; gap: 18px; min-width: 0; max-width: 100%; }
    .ll-shellpage > * { min-width: 0; max-width: 100%; }

    .ll-shell-header h2 { margin: 0 0 4px; color: var(--ll-text); }
    .ll-shell-header p { margin: 0; color: var(--ll-muted); }

    /* Favourites */
    .ll-shell-favs {
        display: grid; gap: 10px;
        padding: 14px 16px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
    }
    .ll-shell-favs-title { font-weight: 600; color: var(--ll-text); display: inline-flex; align-items: center; gap: 8px; }
    .ll-shell-favs-title i { color: var(--ll-primary); }
    .ll-shell-favs-list { display: flex; flex-wrap: wrap; gap: 8px; min-width: 0; }
    .ll-shell-favs-empty { color: var(--ll-muted); font-size: 0.85rem; }

    .ll-fav {
        display: inline-flex; align-items: center; gap: 6px;
        max-width: 100%;
        border: 1px solid var(--ll-border); border-radius: 999px;
        background: var(--ll-bg-soft); color: var(--ll-text);
        padding: 5px 5px 5px 14px;
    }
    .ll-fav:hover { border-color: color-mix(in srgb, var(--ll-primary) 50%, var(--ll-border)); }
    .ll-fav .ll-fav-run {
        cursor: pointer; max-width: 360px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 0.82rem;
    }
    .ll-fav .ll-fav-del {
        flex: none; border: 0; background: transparent; color: var(--ll-muted);
        width: 22px; height: 22px; border-radius: 999px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;
    }
    .ll-fav .ll-fav-del:hover { color: #e5484d; background: color-mix(in srgb, #e5484d 14%, transparent); }
    .ll-shell-favs-add { display: flex; gap: 8px; min-width: 0; }

    .ll-shell-warning {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 14px 16px;
        border: 1px solid color-mix(in srgb, #e5484d 45%, var(--ll-border));
        border-radius: var(--ll-radius);
        background: color-mix(in srgb, #e5484d 10%, var(--ll-surface-solid));
        color: var(--ll-text);
    }
    .ll-shell-warning i { color: #e5484d; font-size: 1.2rem; line-height: 1.4; flex: none; }
    .ll-shell-warning strong { display: block; margin-bottom: 2px; }
    .ll-shell-warning span { color: var(--ll-muted); font-size: 0.88rem; }

    .ll-shell-out {
        margin: 0;
        height: 420px;
        width: 100%;
        box-sizing: border-box;
        overflow: auto;
        padding: 14px 16px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: #0b0e14;
        color: #d7dce5;
        box-shadow: var(--ll-shadow-soft);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 13px;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
        tab-size: 4;
    }
    .ll-shell-out .ll-cmd { color: #56b6ff; }
    .ll-shell-out .ll-err { color: #ff6b6b; }
    .ll-shell-out .ll-meta { color: #7c8595; }

    .ll-shell-form { display: flex; gap: 10px; align-items: stretch; flex-wrap: wrap; min-width: 0; }
    .ll-shell-input {
        flex: 1; min-width: 200px;
        min-height: 44px; padding: 0 14px;
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-button-radius);
        background: var(--ll-bg-soft); color: var(--ll-text);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 0.9rem;
    }
    .ll-shell-input:disabled { opacity: 0.6; }

    .ll-shell-btn {
        display: inline-flex; align-items: center; gap: 8px; flex: none;
        min-height: 44px; padding: 0 18px; border: 0;
        border-radius: var(--ll-button-radius);
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        color: #fff; font-weight: 600; cursor: pointer;
        transition: transform 150ms ease;
    }
    .ll-shell-btn:hover { transform: translateY(-1px); }
    .ll-shell-btn:disabled { opacity: 0.55; cursor: progress; transform: none; }

    .ll-shell-ghost {
        display: inline-flex; align-items: center; gap: 8px; flex: none;
        min-height: 44px; padding: 0 16px;
        border: 1px solid var(--ll-border); border-radius: var(--ll-button-radius);
        background: var(--ll-surface-solid); color: var(--ll-text);
        font-weight: 600; cursor: pointer;
    }

    .ll-shell-hint { color: var(--ll-muted); font-size: 0.82rem; margin: 0; }
    .ll-shell-hint code { color: var(--ll-text); }

    /* Audit log */
    .ll-audit {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        overflow: hidden;
    }
    .ll-audit-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 14px 16px; border-bottom: 1px solid var(--ll-border); flex-wrap: wrap;
    }
    .ll-audit-head h3 { margin: 0; color: var(--ll-text); font-size: 1.02rem; display: inline-flex; align-items: center; gap: 8px; }
    .ll-audit-head p { margin: 2px 0 0; color: var(--ll-muted); font-size: 0.82rem; }
    .ll-audit-scroll { overflow-x: auto; max-height: 420px; overflow-y: auto; }
    table.ll-audit-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .ll-audit-table th, .ll-audit-table td {
        text-align: left; padding: 9px 14px; border-bottom: 1px solid var(--ll-border);
        white-space: nowrap; vertical-align: top;
    }
    .ll-audit-table th {
        position: sticky; top: 0; background: var(--ll-surface-solid); z-index: 1;
        color: var(--ll-muted); font-weight: 700; font-size: 0.72rem;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .ll-audit-table td { color: var(--ll-text); }
    .ll-audit-table td.ll-audit-cmd {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        white-space: pre-wrap; word-break: break-word; max-width: 520px; color: #56b6ff;
    }
    .ll-audit-table td.ll-audit-cwd {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        color: var(--ll-muted); font-size: 0.78rem;
    }
    .ll-audit-muted { color: var(--ll-muted); }
    .ll-audit-empty { padding: 24px 16px; color: var(--ll-muted); text-align: center; }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-shellpage" id="ll-shell">
        <div class="ll-shell-header">
            <h2><i class="bi bi-terminal-fill"></i> Shell</h2>
            <p>Run a command inside the live Railway container this site is served from. Output streams below.</p>
        </div>

        <div class="ll-shell-favs">
            <span class="ll-shell-favs-title"><i class="bi bi-star-fill"></i> Favourites</span>
            <div class="ll-shell-favs-list" id="ll-shell-favs-list"></div>
            <form class="ll-shell-favs-add" id="ll-shell-favs-add" autocomplete="off">
                <input type="text" class="ll-shell-input" id="ll-shell-fav-input"
                       placeholder="Save a command as a favourite…" spellcheck="false">
                <button type="submit" class="ll-shell-ghost"><i class="bi bi-star"></i> Save</button>
            </form>
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

        <pre class="ll-shell-out" id="ll-shell-out"><span class="ll-meta">Livelatch admin shell — ready.</span>
</pre>

        <form class="ll-shell-form" id="ll-shell-form" autocomplete="off">
            <input type="text" class="ll-shell-input" id="ll-shell-command"
                   placeholder="e.g.  php artisan migrate --force" spellcheck="false">
            <button type="submit" class="ll-shell-btn" id="ll-shell-run">
                <i class="bi bi-play-fill"></i> Run
            </button>
            <button type="button" class="ll-shell-ghost" id="ll-shell-clear">
                <i class="bi bi-eraser"></i> Clear
            </button>
        </form>
        <p class="ll-shell-hint">Working dir: <code id="ll-shell-cwd">project root</code> — <code>cd</code> persists like an SSH session. Press <code>↑</code>/<code>↓</code> for history.</p>

        <div class="ll-audit">
            <div class="ll-audit-head">
                <div>
                    <h3><i class="bi bi-clipboard-data"></i> Audit log</h3>
                    <p>Commands run from this shell (from Supabase <code>shell_audit_log</code>). Sign-ups, billing changes and more will join this feed later.</p>
                </div>
                <button type="button" class="ll-shell-ghost" id="ll-audit-refresh">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="ll-audit-scroll">
                <table class="ll-audit-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>User</th>
                            <th>IP</th>
                            <th>Dir</th>
                            <th>Command</th>
                        </tr>
                    </thead>
                    <tbody id="ll-audit-body">
                        <tr><td colspan="5" class="ll-audit-empty">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById('ll-shell');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const csrf = '{{ csrf_token() }}';
        const runUrl = '{{ route('admin.shell.run') }}';
        const auditUrl = '{{ route('admin.shell.audit') }}';
        const FAV_KEY = 'll_shell_favourites';
        const CWD_KEY = 'll_shell_cwd';
        const CWD_MARK = '__LLCWD__';

        const out = document.getElementById('ll-shell-out');
        const form = document.getElementById('ll-shell-form');
        const input = document.getElementById('ll-shell-command');
        const runBtn = document.getElementById('ll-shell-run');
        const clearBtn = document.getElementById('ll-shell-clear');
        const favList = document.getElementById('ll-shell-favs-list');
        const favForm = document.getElementById('ll-shell-favs-add');
        const favInput = document.getElementById('ll-shell-fav-input');
        const cwdLabel = document.getElementById('ll-shell-cwd');

        const history = [];
        let histIndex = -1;
        let running = false;

        // Remembered working directory (emulates a persistent SSH session).
        let cwd = '';
        try { cwd = localStorage.getItem(CWD_KEY) || ''; } catch (e) {}
        function setCwd(dir) {
            cwd = dir || '';
            try { localStorage.setItem(CWD_KEY, cwd); } catch (e) {}
            if (cwdLabel) cwdLabel.textContent = cwd || 'project root';
        }
        setCwd(cwd);

        function atBottom() {
            return out.scrollHeight - out.scrollTop - out.clientHeight < 40;
        }
        function append(text, cls) {
            const stick = atBottom();
            const node = cls ? document.createElement('span') : document.createTextNode(text);
            if (cls) { node.className = cls; node.textContent = text; }
            out.appendChild(node);
            if (stick) out.scrollTop = out.scrollHeight;
        }

        function setRunning(on) {
            running = on;
            input.disabled = on;
            runBtn.disabled = on;
            runBtn.innerHTML = on
                ? '<i class="bi bi-hourglass-split"></i> Running…'
                : '<i class="bi bi-play-fill"></i> Run';
            if (!on) input.focus({ preventScroll: true });
        }

        // Process streamed output line by line so we can intercept the trailing
        // CWD_MARK line (the shell's resulting directory) and hide it. Complete
        // lines render immediately; only a trailing partial line is held until
        // the next newline or stream end, which keeps output responsive.
        function makeLineFilter() {
            let buf = '';
            function consume(line, withNewline) {
                if (line.startsWith(CWD_MARK)) {
                    setCwd(line.slice(CWD_MARK.length).trim());
                    return;
                }
                append(line + (withNewline ? '\n' : ''));
            }
            return {
                push(text) {
                    buf += text;
                    let idx;
                    while ((idx = buf.indexOf('\n')) !== -1) {
                        consume(buf.slice(0, idx), true);
                        buf = buf.slice(idx + 1);
                    }
                },
                end() {
                    if (buf) consume(buf, false);
                    buf = '';
                },
            };
        }

        async function streamRun(command) {
            setRunning(true);
            append((cwd || '~') + ' $ ' + command + '\n', 'll-cmd');
            const filter = makeLineFilter();
            try {
                const res = await fetch(runUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'command=' + encodeURIComponent(command) + '&cwd=' + encodeURIComponent(cwd),
                });
                if (!res.ok) {
                    append('[shell] request failed: HTTP ' + res.status + '\n', 'll-err');
                } else {
                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        filter.push(decoder.decode(value, { stream: true }));
                    }
                    filter.end();
                }
            } catch (err) {
                filter.end();
                append('[shell] ' + err + '\n', 'll-err');
            } finally {
                append('\n');
                setRunning(false);
                // The command was just audited server-side — refresh the feed.
                loadAudit();
            }
        }

        // --- Audit log ---
        const auditBody = document.getElementById('ll-audit-body');
        const auditRefresh = document.getElementById('ll-audit-refresh');

        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        function fmtWhen(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return isNaN(d) ? esc(iso) : d.toLocaleString();
        }
        function auditRow(e) {
            const who = e.email || e.name || ('user ' + (e.laravel_user_id ?? '?'));
            return '<tr>'
                + '<td class="ll-audit-muted">' + esc(fmtWhen(e.created_at)) + '</td>'
                + '<td>' + esc(who) + '</td>'
                + '<td class="ll-audit-muted">' + esc(e.ip || '') + '</td>'
                + '<td class="ll-audit-cwd">' + esc(e.cwd || '') + '</td>'
                + '<td class="ll-audit-cmd">' + esc(e.command || '') + '</td>'
                + '</tr>';
        }
        async function loadAudit() {
            try {
                const res = await fetch(auditUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                const rows = (data.entries || []);
                auditBody.innerHTML = rows.length
                    ? rows.map(auditRow).join('')
                    : '<tr><td colspan="5" class="ll-audit-empty">No audit entries yet.</td></tr>';
            } catch (err) {
                auditBody.innerHTML = '<tr><td colspan="5" class="ll-audit-empty">Could not load the audit log (' + esc(err.message) + ').</td></tr>';
            }
        }
        auditRefresh.addEventListener('click', loadAudit);

        function execute(command) {
            command = (command || '').trim();
            if (!command || running) return;
            if (history[history.length - 1] !== command) history.push(command);
            histIndex = history.length;
            input.value = command;
            streamRun(command);
        }

        // --- Favourites (localStorage, per-browser) ---
        function loadFavs() {
            try { return JSON.parse(localStorage.getItem(FAV_KEY)) || []; } catch (e) { return []; }
        }
        function saveFavs(favs) {
            localStorage.setItem(FAV_KEY, JSON.stringify(favs));
        }
        function renderFavs() {
            const favs = loadFavs();
            favList.innerHTML = '';
            if (!favs.length) {
                const empty = document.createElement('span');
                empty.className = 'll-shell-favs-empty';
                empty.textContent = 'No favourites yet — save a command below.';
                favList.appendChild(empty);
                return;
            }
            favs.forEach((cmd, i) => {
                const chip = document.createElement('span');
                chip.className = 'll-fav';

                const label = document.createElement('span');
                label.className = 'll-fav-run';
                label.textContent = cmd;
                label.title = 'Run: ' + cmd;
                label.addEventListener('click', () => execute(cmd));

                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'll-fav-del';
                del.title = 'Remove favourite';
                del.innerHTML = '<i class="bi bi-x-lg"></i>';
                del.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const arr = loadFavs();
                    arr.splice(i, 1);
                    saveFavs(arr);
                    renderFavs();
                });

                chip.appendChild(label);
                chip.appendChild(del);
                favList.appendChild(chip);
            });
        }

        favForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const cmd = favInput.value.trim();
            if (!cmd) return;
            const arr = loadFavs();
            if (!arr.includes(cmd)) arr.push(cmd);
            saveFavs(arr);
            favInput.value = '';
            renderFavs();
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const command = input.value.trim();
            input.value = '';
            execute(command);
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

        clearBtn.addEventListener('click', () => {
            out.textContent = '';
            input.focus({ preventScroll: true });
        });

        renderFavs();
        loadAudit();
    })();
</script>
@endsection
