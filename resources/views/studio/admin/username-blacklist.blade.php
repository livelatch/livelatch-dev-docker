@extends('layouts.sidebar')

@section('content')
<style data-ll-bl-style>
    .ll-bl { display: grid; gap: 18px; }
    .ll-bl-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; }
    .ll-bl-head p { color: var(--ll-muted); margin: 0; max-width: 76ch; font-size: .9rem; }

    .ll-bl-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .ll-bl-stat { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: linear-gradient(180deg, color-mix(in srgb, var(--ll-text) 5%, var(--ll-surface-solid)), var(--ll-surface-solid)); box-shadow: var(--ll-shadow-soft); padding: 14px 16px; }
    .ll-bl-stat .v { color: var(--ll-text); font-size: 1.7rem; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
    .ll-bl-stat .l { color: var(--ll-muted); font-size: .76rem; margin-top: 4px; }

    .ll-bl-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-bl-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-bl-panel h2 i { color: var(--ll-primary); }
    .ll-bl-panel .sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 12px; }
    .ll-bl-textarea { width: 100%; min-height: 110px; resize: vertical; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 12px; font-family: ui-monospace, Consolas, monospace; font-size: .84rem; }
    .ll-bl-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; border-radius: var(--ll-button-radius); font-weight: 700; font-size: .9rem; padding: 10px 18px; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; transition: transform .12s ease; }
    .ll-bl-btn:hover { transform: translateY(-1px); }
    .ll-bl-warn { border: 1px solid color-mix(in srgb, #d97706 40%, var(--ll-border)); background: color-mix(in srgb, #d97706 10%, transparent); color: var(--ll-text); border-radius: 12px; padding: 10px 12px; font-size: .82rem; margin-top: 12px; }

    .ll-bl-controls { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 12px; }
    .ll-bl-search { flex: 1 1 220px; min-height: 40px; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 8px 12px; font-size: .88rem; }
    .ll-bl-count { color: var(--ll-muted); font-size: .76rem; font-weight: 700; }

    .ll-bl-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
    .ll-bl-table th { text-align: left; color: var(--ll-muted); font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: 0 12px 6px; white-space: nowrap; }
    .ll-bl-table th[data-sort] { cursor: pointer; user-select: none; }
    .ll-bl-table th[data-sort]:hover { color: var(--ll-text); }
    .ll-bl-table th .arr { opacity: .5; font-size: .8em; }
    .ll-bl-table td { background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); border-top: 1px solid var(--ll-border); border-bottom: 1px solid var(--ll-border); padding: 11px 12px; color: var(--ll-text); font-size: .88rem; vertical-align: middle; }
    .ll-bl-table td:first-child { border-left: 1px solid var(--ll-border); border-radius: 12px 0 0 12px; }
    .ll-bl-table td:last-child { border-right: 1px solid var(--ll-border); border-radius: 0 12px 12px 0; }
    .ll-bl-word { font-family: ui-monospace, Consolas, monospace; font-weight: 700; color: var(--ll-text); }
    .ll-bl-kind { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--ll-muted); border: 1px solid var(--ll-border); border-radius: 999px; padding: 2px 8px; }
    .ll-bl-num { font-variant-numeric: tabular-nums; font-weight: 700; text-align: right; }
    .ll-bl-num.hot { color: var(--ll-danger); }
    .ll-bl-empty { color: var(--ll-muted); padding: 22px; text-align: center; }
    @media (max-width: 760px) { .ll-bl-table .opt { display: none; } }
</style>

@php
    $totalWords = count($directory);
    $totalAttempts = collect($directory)->sum('attempts');
    $everHit = collect($directory)->filter(fn ($r) => ($r['attempts'] ?? 0) > 0)->count();
@endphp

<div class="container-fluid content-inner mt-n5 py-0 ll-bl">
    <div class="ll-bl-head">
        <h1>Username Blacklist</h1>
        <p>Banned words. A signup whose generated username <strong>contains</strong> any of these is blocked and shown a "username banned" page. Single-character usernames are blocked automatically (a length rule — not stored here). Only attempt counts are tracked.</p>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="ll-bl-stats">
        <div class="ll-bl-stat"><div class="v">{{ number_format($totalWords) }}</div><div class="l">Banned words</div></div>
        <div class="ll-bl-stat"><div class="v">{{ number_format($totalAttempts) }}</div><div class="l">Blocked attempts</div></div>
        <div class="ll-bl-stat"><div class="v">{{ number_format($everHit) }}</div><div class="l">Words ever triggered</div></div>
    </div>

    <section class="ll-bl-panel">
        <h2><i class="bi bi-plus-square-fill"></i> Add words</h2>
        <p class="sub">One word per line (or <code>word,kind</code>). Lowercased; matched as a <em>substring</em> of the username. Duplicates ignored.</p>
        <form method="POST" action="{{ route('admin.usernameBlacklist.add') }}">
            @csrf
            <textarea name="words" class="ll-bl-textarea" placeholder="hitler&#10;somebadword,profanity&#10;null,system" required></textarea>
            <div style="margin-top:12px;"><button type="submit" class="ll-bl-btn"><i class="bi bi-slash-circle"></i> Add to blacklist</button></div>
        </form>
        <div class="ll-bl-warn"><i class="bi bi-exclamation-triangle-fill"></i> Substring matching can over-block (e.g. a 3-letter word inside a legitimate name). Keep words specific (3+ characters recommended) to avoid false positives.</div>
    </section>

    <section class="ll-bl-panel">
        <h2><i class="bi bi-slash-circle-fill"></i> Blacklisted words</h2>
        <div class="ll-bl-controls">
            <input type="search" id="ll-bl-search" class="ll-bl-search" placeholder="Search words…" autocomplete="off">
            <span class="ll-bl-count" id="ll-bl-count">{{ $totalWords }} words</span>
        </div>

        @if($totalWords === 0)
            <div class="ll-bl-empty">No words yet. Paste a list above to start blocking usernames.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="ll-bl-table" id="ll-bl-table">
                <thead>
                    <tr>
                        <th data-sort="word">Word <span class="arr"></span></th>
                        <th class="opt">Kind</th>
                        <th data-sort="attempts" style="text-align:right;">Attempts <span class="arr">▼</span></th>
                        <th data-sort="last" class="opt">Last try <span class="arr"></span></th>
                        <th data-sort="date" class="opt">Added <span class="arr"></span></th>
                    </tr>
                </thead>
                <tbody id="ll-bl-body">
                    @foreach($directory as $r)
                        @php
                            $attempts = (int) ($r['attempts'] ?? 0);
                            $last = !empty($r['last_attempt_at']) ? strtotime($r['last_attempt_at']) : 0;
                            $ts = !empty($r['created_at']) ? strtotime($r['created_at']) : 0;
                        @endphp
                        <tr data-word="{{ strtolower($r['word']) }}" data-attempts="{{ $attempts }}" data-last="{{ $last }}" data-date="{{ $ts }}">
                            <td class="ll-bl-word">{{ $r['word'] }}</td>
                            <td class="opt"><span class="ll-bl-kind">{{ $r['kind'] ?? '—' }}</span></td>
                            <td class="ll-bl-num {{ $attempts > 0 ? 'hot' : '' }}">{{ number_format($attempts) }}</td>
                            <td class="opt" style="color:var(--ll-muted);font-size:.78rem;white-space:nowrap;">{{ $last ? \Illuminate\Support\Carbon::createFromTimestamp($last)->diffForHumans() : '—' }}</td>
                            <td class="opt" style="color:var(--ll-muted);font-size:.78rem;white-space:nowrap;">{{ $ts ? \Illuminate\Support\Carbon::createFromTimestamp($ts)->format('d M y') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</div>

@verbatim
<script>
(function () {
    var table = document.getElementById('ll-bl-table');
    if (!table) return;
    var body = document.getElementById('ll-bl-body');
    var search = document.getElementById('ll-bl-search');
    var count = document.getElementById('ll-bl-count');
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr'));
    var sortKey = 'attempts', sortDir = -1;

    function val(tr, key) { return key === 'word' ? tr.dataset.word : Number(tr.dataset[key] || 0); }
    function render() {
        var q = (search.value || '').trim().toLowerCase();
        var shown = rows.filter(function (tr) { return !q || tr.dataset.word.indexOf(q) !== -1; });
        shown.sort(function (a, b) {
            var av = val(a, sortKey), bv = val(b, sortKey);
            if (av < bv) return -1 * sortDir;
            if (av > bv) return 1 * sortDir;
            return a.dataset.word < b.dataset.word ? -1 : 1;
        });
        body.innerHTML = '';
        shown.forEach(function (tr) { body.appendChild(tr); });
        count.textContent = shown.length + (shown.length === 1 ? ' word' : ' words');
        table.querySelectorAll('th[data-sort] .arr').forEach(function (a) { a.textContent = ''; });
        var th = table.querySelector('th[data-sort="' + sortKey + '"] .arr');
        if (th) th.textContent = sortDir === -1 ? '▼' : '▲';
    }
    table.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            var k = th.dataset.sort;
            if (sortKey === k) { sortDir *= -1; } else { sortKey = k; sortDir = (k === 'word') ? 1 : -1; }
            render();
        });
    });
    search.addEventListener('input', render);
    render();
})();
</script>
@endverbatim
@endsection
