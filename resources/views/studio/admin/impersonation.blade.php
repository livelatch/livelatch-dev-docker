@extends('layouts.sidebar')

@section('content')
<style data-ll-im-style>
    .ll-im { display: grid; gap: 18px; }
    .ll-im-head { display: flex; flex-wrap: wrap; gap: 12px 18px; align-items: flex-start; justify-content: space-between; }
    .ll-im-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ll-im-by { font-size: .6rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #fff; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); border-radius: 999px; padding: 4px 10px; }
    .ll-im-head p { color: var(--ll-muted); margin: 0; max-width: 76ch; font-size: .9rem; }

    .ll-im-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .ll-im-stat { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: linear-gradient(180deg, color-mix(in srgb, var(--ll-text) 5%, var(--ll-surface-solid)), var(--ll-surface-solid)); box-shadow: var(--ll-shadow-soft); padding: 14px 16px; }
    .ll-im-stat .v { color: var(--ll-text); font-size: 1.7rem; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
    .ll-im-stat .l { color: var(--ll-muted); font-size: .76rem; margin-top: 4px; }

    .ll-im-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-im-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-im-panel h2 i { color: var(--ll-primary); }
    .ll-im-panel .sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 12px; }
    .ll-im-textarea { width: 100%; min-height: 120px; resize: vertical; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 12px; font-family: ui-monospace, Consolas, monospace; font-size: .84rem; }
    .ll-im-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; border-radius: var(--ll-button-radius); font-weight: 700; font-size: .9rem; padding: 10px 18px; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; transition: transform .12s ease; }
    .ll-im-btn:hover { transform: translateY(-1px); }

    .ll-im-controls { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 12px; }
    .ll-im-search { flex: 1 1 220px; min-height: 40px; border: 1px solid var(--ll-border); border-radius: 12px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 8px 12px; font-size: .88rem; }
    .ll-im-count { color: var(--ll-muted); font-size: .76rem; font-weight: 700; }

    .ll-im-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
    .ll-im-table th { text-align: left; color: var(--ll-muted); font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: 0 12px 6px; white-space: nowrap; }
    .ll-im-table th[data-sort] { cursor: pointer; user-select: none; }
    .ll-im-table th[data-sort]:hover { color: var(--ll-text); }
    .ll-im-table th .arr { opacity: .5; font-size: .8em; }
    .ll-im-table td { background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); border-top: 1px solid var(--ll-border); border-bottom: 1px solid var(--ll-border); padding: 11px 12px; color: var(--ll-text); font-size: .86rem; vertical-align: middle; }
    .ll-im-table td:first-child { border-left: 1px solid var(--ll-border); border-radius: 12px 0 0 12px; }
    .ll-im-table td:last-child { border-right: 1px solid var(--ll-border); border-radius: 0 12px 12px 0; }
    .ll-im-name strong { color: var(--ll-text); }
    .ll-im-name span { color: var(--ll-muted); font-size: .74rem; }
    .ll-im-plat { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--ll-muted); border: 1px solid var(--ll-border); border-radius: 999px; padding: 2px 8px; }
    .ll-im-num { font-variant-numeric: tabular-nums; font-weight: 700; text-align: right; }
    .ll-im-num.hot { color: var(--ll-primary); }
    .ll-im-flag { color: #d97706; font-weight: 800; }
    .ll-im-tell { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-surface-solid); color: var(--ll-text); font-weight: 600; font-size: .76rem; padding: 6px 10px; text-decoration: none; white-space: nowrap; }
    .ll-im-tell:hover { border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); color: var(--ll-text); }
    .ll-im-empty { color: var(--ll-muted); padding: 22px; text-align: center; }
    @media (max-width: 920px) { .ll-im-table .opt { display: none; } }
</style>

@php
    $totalNames = count($directory);
    $totalVisits = collect($directory)->sum('visit_attempts');
    $totalSignups = collect($directory)->sum('signup_attempts');
    $flagged = collect($directory)->filter(fn ($r) => ($similar[$r['handle']] ?? 0) > 0)->count();
@endphp

<div class="container-fluid content-inner mt-n5 py-0 ll-im">
    <div class="ll-im-head">
        <div>
            <h1>Impersonation Mitigation <span class="ll-im-by">by LatchID</span></h1>
            <p>Protect high-value creator &amp; brand names. A LatchID sub-product — referenced internally, in B2B marketing, and on the website. Tracks who tries to claim or visit a protected name so you can spot impersonation early. Unique attempt counts are deduped server-side; names that match a live account (or its <code>-unverified</code> form) are flagged.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="ll-im-stats">
        <div class="ll-im-stat"><div class="v">{{ number_format($totalNames) }}</div><div class="l">Names protected</div></div>
        <div class="ll-im-stat"><div class="v">{{ number_format($totalVisits) }}</div><div class="l">/@name visit attempts</div></div>
        <div class="ll-im-stat"><div class="v">{{ number_format($totalSignups) }}</div><div class="l">Signup attempts</div></div>
        <div class="ll-im-stat"><div class="v">{{ number_format($flagged) }}</div><div class="l">Flagged (live similar account)</div></div>
    </div>

    <section class="ll-im-panel">
        <h2><i class="bi bi-plus-square-fill"></i> Add names</h2>
        <p class="sub">Paste a list — one name per line (or <code>Name,platform</code>). Each line is added to the directory; handles are derived automatically and duplicates are ignored.</p>
        <form method="POST" action="{{ route('admin.impersonation.add') }}">
            @csrf
            <textarea name="names" class="ll-im-textarea" placeholder="MrBeast&#10;Pokimane,twitch&#10;Some Brand,youtube" required></textarea>
            <div style="margin-top:12px;"><button type="submit" class="ll-im-btn"><i class="bi bi-cloud-arrow-up"></i> Add to directory</button></div>
        </form>
    </section>

    <section class="ll-im-panel">
        <h2><i class="bi bi-shield-lock-fill"></i> Protected names</h2>
        <div class="ll-im-controls">
            <input type="search" id="ll-im-search" class="ll-im-search" placeholder="Search names…" autocomplete="off">
            <span class="ll-im-count" id="ll-im-count">{{ $totalNames }} names</span>
        </div>

        @if($totalNames === 0)
            <div class="ll-im-empty">No names yet. Paste a list above to start protecting names.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="ll-im-table" id="ll-im-table">
                <thead>
                    <tr>
                        <th data-sort="name">Name <span class="arr"></span></th>
                        <th class="opt">Platform</th>
                        <th data-sort="visits" style="text-align:right;">Visits <span class="arr"></span></th>
                        <th data-sort="signups" class="opt" style="text-align:right;">Signups <span class="arr"></span></th>
                        <th data-sort="hits" style="text-align:right;">Hits <span class="arr">▼</span></th>
                        <th data-sort="similar" style="text-align:right;">Similar <span class="arr"></span></th>
                        <th data-sort="date" class="opt">Added <span class="arr"></span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="ll-im-body">
                    @foreach($directory as $r)
                        @php
                            $h = $r['handle'];
                            $visits = (int) ($r['visit_attempts'] ?? 0);
                            $signups = (int) ($r['signup_attempts'] ?? 0);
                            $hits = $visits + $signups;
                            $sim = (int) ($similar[$h] ?? 0);
                            $ts = !empty($r['created_at']) ? strtotime($r['created_at']) : 0;
                        @endphp
                        <tr data-name="{{ strtolower($r['name']) }}" data-handle="{{ $h }}" data-visits="{{ $visits }}" data-signups="{{ $signups }}" data-hits="{{ $hits }}" data-similar="{{ $sim }}" data-date="{{ $ts }}">
                            <td class="ll-im-name"><strong>{{ $r['name'] }}</strong><br><span>{{ '@' . $h }}</span></td>
                            <td class="opt"><span class="ll-im-plat">{{ $r['platform'] ?? '—' }}</span></td>
                            <td class="ll-im-num {{ $visits > 0 ? 'hot' : '' }}">{{ number_format($visits) }}</td>
                            <td class="ll-im-num opt {{ $signups > 0 ? 'hot' : '' }}">{{ number_format($signups) }}</td>
                            <td class="ll-im-num {{ $hits > 0 ? 'hot' : '' }}">{{ number_format($hits) }}</td>
                            <td class="ll-im-num {{ $sim > 0 ? 'll-im-flag' : '' }}">{{ $sim > 0 ? number_format($sim) : '—' }}</td>
                            <td class="opt" style="color:var(--ll-muted);font-size:.78rem;white-space:nowrap;">{{ $ts ? \Illuminate\Support\Carbon::createFromTimestamp($ts)->format('d M y') : '—' }}</td>
                            <td><a class="ll-im-tell" href="https://www.google.com/search?q={{ urlencode('Tell me about ' . $r['name']) }}" target="_blank" rel="noopener"><i class="bi bi-search"></i> Tell me about</a></td>
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
    var table = document.getElementById('ll-im-table');
    if (!table) return;
    var body = document.getElementById('ll-im-body');
    var search = document.getElementById('ll-im-search');
    var count = document.getElementById('ll-im-count');
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr'));
    var sortKey = 'hits', sortDir = -1;

    function val(tr, key) {
        if (key === 'name') return tr.dataset.name;
        return Number(tr.dataset[key] || 0);
    }
    function render() {
        var q = (search.value || '').trim().toLowerCase();
        var shown = rows.filter(function (tr) { return !q || tr.dataset.name.indexOf(q) !== -1 || tr.dataset.handle.indexOf(q) !== -1; });
        shown.sort(function (a, b) {
            var av = val(a, sortKey), bv = val(b, sortKey);
            if (av < bv) return -1 * sortDir;
            if (av > bv) return 1 * sortDir;
            return a.dataset.name < b.dataset.name ? -1 : 1;
        });
        body.innerHTML = '';
        shown.forEach(function (tr) { body.appendChild(tr); });
        count.textContent = shown.length + (shown.length === 1 ? ' name' : ' names');
        table.querySelectorAll('th[data-sort] .arr').forEach(function (a) { a.textContent = ''; });
        var th = table.querySelector('th[data-sort="' + sortKey + '"] .arr');
        if (th) th.textContent = sortDir === -1 ? '▼' : '▲';
    }
    table.querySelectorAll('th[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            var k = th.dataset.sort;
            if (sortKey === k) { sortDir *= -1; } else { sortKey = k; sortDir = (k === 'name') ? 1 : -1; }
            render();
        });
    });
    search.addEventListener('input', render);
    render();
})();
</script>
@endverbatim
@endsection
