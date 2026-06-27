@extends('layouts.sidebar')

@section('content')
<style data-ll-ss-style>
    .ll-ss { display: grid; gap: 18px; }
    .ll-ss h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ll-ss-pro { font-size: .6rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #1a1205; background: linear-gradient(135deg, #ffd66b, #f5b301); border-radius: 999px; padding: 4px 9px; }
    .ll-ss-lead { color: var(--ll-muted); margin: 0; max-width: 72ch; font-size: .9rem; }
    .ll-ss-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-ss-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-ss-panel h2 i { color: var(--ll-primary); }
    .ll-ss-panel .sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 14px; }
    .ll-ss-grid { display: grid; grid-template-columns: minmax(320px, 420px) minmax(0, 1fr); gap: 16px; align-items: start; }
    .ll-ss-field { display: grid; gap: 5px; margin-bottom: 12px; }
    .ll-ss-field label { color: var(--ll-text); font-weight: 600; font-size: .82rem; }
    .ll-ss-field input, .ll-ss-field select { width: 100%; border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 9px 11px; font-size: .86rem; font-family: inherit; }
    .ll-ss-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ll-ss-seg { display: inline-flex; border: 1px solid var(--ll-border); border-radius: 999px; padding: 3px; gap: 3px; margin-bottom: 12px; }
    .ll-ss-seg button { border: 0; background: transparent; color: var(--ll-muted); border-radius: 999px; padding: 6px 14px; font-weight: 700; font-size: .82rem; cursor: pointer; }
    .ll-ss-seg button.on { color: #fff; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-ss-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; border-radius: var(--ll-button-radius); font-weight: 700; font-size: .88rem; padding: 10px 16px; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; transition: transform .12s ease; }
    .ll-ss-btn:hover { transform: translateY(-1px); }
    .ll-ss-btn.ghost { background: var(--ll-surface-solid); color: var(--ll-text); border: 1px solid var(--ll-border); }
    .ll-ss-sync { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .ll-ss-url { flex: 1 1 280px; border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-bg-soft); color: var(--ll-muted); padding: 9px 11px; font-family: ui-monospace, Consolas, monospace; font-size: .78rem; }
    .ll-ss-row { display: grid; grid-template-columns: 38px minmax(0,1fr) auto; gap: 12px; align-items: center; border: 1px solid var(--ll-border); border-radius: 12px; background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); padding: 11px 13px; }
    .ll-ss-row + .ll-ss-row { margin-top: 8px; }
    .ll-ss-ic { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-ss-when { color: var(--ll-text); font-weight: 700; font-size: .9rem; }
    .ll-ss-meta { color: var(--ll-muted); font-size: .76rem; }
    .ll-ss-acts { display: flex; gap: 6px; }
    .ll-ss-iconbtn { border: 1px solid var(--ll-border); background: var(--ll-surface-solid); color: var(--ll-text); border-radius: 9px; padding: 6px 10px; cursor: pointer; font-size: .8rem; }
    .ll-ss-empty { border: 1px dashed var(--ll-border); border-radius: 12px; padding: 18px; text-align: center; color: var(--ll-muted); font-size: .85rem; }
    .ll-ss-upsell { text-align: center; padding: 40px 24px; }
    .ll-ss-upsell .big { font-size: 2.4rem; }
    @media (max-width: 1100px) { .ll-ss-grid { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-ss">
    <div>
        <h1><i class="bi bi-calendar-week-fill" style="color:var(--ll-primary);"></i> Stream Schedule <span class="ll-ss-pro">Pro</span></h1>
        <p class="ll-ss-lead">Set your streams once and fans can subscribe to a calendar that auto-updates when you change a time. Times show in each viewer's own timezone.</p>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="alert alert-success d-none" id="ll-ss-toast"></div>
    <div class="alert alert-danger d-none" id="ll-ss-err"></div>

    @unless($isPro)
        <section class="ll-ss-panel ll-ss-upsell">
            <div class="big">📅</div>
            <h2 style="justify-content:center;">Stream Schedule is a Pro feature</h2>
            <p class="sub" style="margin: 8px auto 16px; max-width: 46ch;">Upgrade to publish a stream schedule on your profile and give fans a one-tap, auto-updating calendar subscription.</p>
            <a href="{{ url('/studio/subscription') }}" class="ll-ss-btn" style="text-decoration:none;">Upgrade to Pro</a>
        </section>
    @else
        @php $upcoming = \App\Services\StreamScheduleService::upcoming($events, 7); @endphp

        <section class="ll-ss-panel">
            <h2><i class="bi bi-calendar-plus"></i> Sync to calendar</h2>
            <p class="sub">Share these so fans never miss a stream. iPhone is one tap; Google/Outlook use "add by URL". When you edit your schedule, subscribers update automatically (on their calendar's refresh cycle).</p>
            <div class="ll-ss-sync">
                <input type="text" class="ll-ss-url" id="ll-ss-icsurl" value="{{ $icsUrl }}" readonly>
                <a class="ll-ss-btn" href="{{ $webcal }}"><i class="bi bi-apple"></i> Subscribe (iPhone)</a>
                <button type="button" class="ll-ss-btn ghost" id="ll-ss-copy"><i class="bi bi-clipboard"></i> Copy feed URL</button>
                <a class="ll-ss-btn ghost" href="{{ $icsUrl }}" download><i class="bi bi-download"></i> Download .ics</a>
            </div>
        </section>

        <div class="ll-ss-grid">
            <section class="ll-ss-panel">
                <h2 id="ll-ss-formtitle"><i class="bi bi-plus-circle"></i> Add a stream</h2>
                <div class="ll-ss-field"><label>Stream name</label><input type="text" id="f-title" maxlength="120" placeholder="Warzone ranked grind"></div>
                <div class="ll-ss-2">
                    <div class="ll-ss-field"><label>Platform</label>
                        <select id="f-platform">
                            <option value="">—</option>
                            @foreach(config('creator_platforms') as $key => $p)
                                <option value="{{ $key }}">{{ $p['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ll-ss-field"><label>Reminder</label>
                        <select id="f-reminder">
                            <option value="">None</option>
                            <option value="5">5 min before</option>
                            <option value="15">15 min before</option>
                            <option value="30">30 min before</option>
                            <option value="60">1 hour before</option>
                            <option value="1440">1 day before</option>
                        </select>
                    </div>
                </div>
                <div class="ll-ss-field"><label>Link (optional)</label><input type="text" id="f-url" maxlength="400" placeholder="https://twitch.tv/you"></div>

                <div class="ll-ss-field">
                    <label>Show game <span style="color:var(--ll-muted);font-weight:500;">optional — adds art &amp; ESRB rating</span></label>
                    <div id="f-game-chip" style="display:none; align-items:center; gap:10px; padding:8px 10px; border:1px solid var(--ll-border); border-radius:10px; background:var(--ll-bg-soft);">
                        <img id="f-game-chip-img" src="" alt="" style="width:42px;height:42px;border-radius:8px;object-fit:cover;flex:0 0 auto;">
                        <div style="flex:1;min-width:0;"><div id="f-game-chip-name" style="font-weight:700;font-size:.86rem;color:var(--ll-text);"></div><div id="f-game-chip-esrb" style="font-size:.72rem;color:var(--ll-muted);"></div></div>
                        <button type="button" id="f-game-clear" class="ll-ss-btn ghost" style="padding:6px 10px;">Clear</button>
                    </div>
                    @if($rawgEnabled)
                        <div id="f-game-pick" style="position:relative;">
                            <input type="text" id="f-game-search" placeholder="Search a game…" autocomplete="off">
                            <div id="f-game-results" style="display:none; position:absolute; z-index:5; left:0; right:0; top:100%; margin-top:4px; background:var(--ll-surface-solid); border:1px solid var(--ll-border); border-radius:10px; box-shadow:var(--ll-shadow); max-height:240px; overflow:auto;"></div>
                        </div>
                    @else
                        <p class="ll-ss-meta">Game search is off — set <code>RAWG_API_KEY</code> to enable it.</p>
                    @endif
                    <input type="hidden" id="f-game-name"><input type="hidden" id="f-game-image"><input type="hidden" id="f-game-esrb"><input type="hidden" id="f-game-rawg-id">
                </div>

                <div class="ll-ss-2">
                    <div class="ll-ss-field"><label>Tags <span style="color:var(--ll-muted);font-weight:500;">comma separated</span></label><input type="text" id="f-tags" maxlength="200" placeholder="12 hour challenge, new to this"></div>
                    <div class="ll-ss-field"><label>Audience</label>
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:600;font-size:.86rem;color:var(--ll-text);cursor:pointer;padding-top:8px;"><input type="checkbox" id="f-adult" style="width:auto;"> Mark as 18+</label>
                    </div>
                </div>

                <div class="ll-ss-seg" role="tablist">
                    <button type="button" id="seg-once" class="on" data-kind="once">One-off</button>
                    <button type="button" id="seg-weekly" data-kind="weekly">Every week</button>
                </div>

                <div id="fields-once">
                    <div class="ll-ss-field"><label>Date</label><input type="date" id="f-date"></div>
                    <div class="ll-ss-2">
                        <div class="ll-ss-field"><label>From</label><input type="time" id="f-once-start" value="19:00"></div>
                        <div class="ll-ss-field"><label>To</label><input type="time" id="f-once-end" value="21:00"></div>
                    </div>
                    <p class="ll-ss-meta" id="f-tz-note-once"></p>
                </div>
                <div id="fields-weekly" style="display:none;">
                    <div class="ll-ss-field"><label>Day</label>
                        <select id="f-weekday">
                            <option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option>
                            <option value="3">Wednesday</option><option value="4" selected>Thursday</option><option value="5">Friday</option><option value="6">Saturday</option>
                        </select>
                    </div>
                    <div class="ll-ss-2">
                        <div class="ll-ss-field"><label>From</label><input type="time" id="f-start-time" value="19:00"></div>
                        <div class="ll-ss-field"><label>To</label><input type="time" id="f-end-time" value="21:00"></div>
                    </div>
                    <p class="ll-ss-meta" id="f-tz-note"></p>
                </div>

                <input type="hidden" id="f-id" value="">
                <input type="hidden" id="f-timezone" value="">
                <div style="display:flex; gap:8px; margin-top:6px;">
                    <button type="button" class="ll-ss-btn" id="ll-ss-save">Save stream</button>
                    <button type="button" class="ll-ss-btn ghost" id="ll-ss-cancel" style="display:none;">Cancel</button>
                </div>
            </section>

            <div style="display:grid; gap:16px;">
                <section class="ll-ss-panel">
                    <h2><i class="bi bi-calendar3"></i> Next 7 days <span class="ll-ss-meta" style="font-weight:500;">(your timezone)</span></h2>
                    @if(empty($upcoming))
                        <div class="ll-ss-empty">Nothing scheduled in the next 7 days. Add a stream to see it here and on your profile.</div>
                    @else
                        @foreach($upcoming as $o)
                            <div class="ll-ss-row">
                                <span class="ll-ss-ic"><i class="bi bi-broadcast"></i></span>
                                <div>
                                    <div class="ll-ss-when" data-utc="{{ $o['start']->format('c') }}" data-utc-end="{{ $o['end']->format('c') }}">{{ $o['start']->format('D j M, H:i') }} UTC</div>
                                    <div class="ll-ss-meta">{{ $o['event']['title'] ?? 'Stream' }}@if(!empty($o['event']['platform'])) · {{ ucfirst($o['event']['platform']) }}@endif</div>
                                </div>
                                <span></span>
                            </div>
                        @endforeach
                    @endif
                </section>

                <section class="ll-ss-panel">
                    <h2><i class="bi bi-list-task"></i> All streams</h2>
                    <div id="ll-ss-list">
                        @forelse($events as $e)
                            <div class="ll-ss-row" data-id="{{ $e['id'] }}">
                                <span class="ll-ss-ic"><i class="bi bi-{{ ($e['kind'] ?? 'once') === 'weekly' ? 'arrow-repeat' : 'calendar-event' }}"></i></span>
                                <div>
                                    <div class="ll-ss-when">{{ $e['title'] ?? 'Stream' }}@if(!empty($e['platform'])) · {{ ucfirst($e['platform']) }}@endif</div>
                                    <div class="ll-ss-meta">
                                        @if(($e['kind'] ?? 'once') === 'weekly')
                                            Every {{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][(int)($e['weekday'] ?? 0)] }} {{ $e['start_time'] ?? '' }}@if(!empty($e['end_time']))–{{ $e['end_time'] }}@endif ({{ $e['timezone'] ?? 'UTC' }})
                                        @else
                                            {{ !empty($e['starts_at']) ? \Illuminate\Support\Carbon::parse($e['starts_at'])->format('D j M Y, H:i T') : 'No date' }}
                                        @endif
                                    </div>
                                </div>
                                <div class="ll-ss-acts">
                                    <button type="button" class="ll-ss-iconbtn" data-edit='@json($e)'><i class="bi bi-pencil"></i></button>
                                    <button type="button" class="ll-ss-iconbtn" data-del="{{ $e['id'] }}"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        @empty
                            <div class="ll-ss-empty">No streams yet.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    @endunless
</div>

@if($isPro)
<script>
    window.LL_SS = {
        csrf: '{{ csrf_token() }}',
        store: '{{ route('streamSchedule.store') }}',
        base: '{{ url('/studio/stream-schedule') }}',
        games: '{{ route('streamSchedule.games') }}'
    };
</script>
@verbatim
<script>
(function () {
    var D = window.LL_SS;
    if (!D) return;
    var $ = function (id) { return document.getElementById(id); };
    var toast = function (sel, msg) { var b = document.querySelector(sel); if (!b) return; b.textContent = msg; b.classList.remove('d-none'); setTimeout(function () { b.classList.add('d-none'); }, 4000); };
    var tz = (Intl && Intl.DateTimeFormat) ? Intl.DateTimeFormat().resolvedOptions().timeZone : 'UTC';
    if ($('f-timezone')) $('f-timezone').value = tz;
    if ($('f-tz-note')) $('f-tz-note').textContent = 'Times are in your timezone: ' + tz;
    if ($('f-tz-note-once')) $('f-tz-note-once').textContent = 'Times are in your timezone: ' + tz;
    function dDate(d) { var p = function (n) { return String(n).padStart(2, '0'); }; return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()); }
    if ($('f-date') && !$('f-date').value) $('f-date').value = dDate(new Date());

    // Render the preview times in the viewer's local timezone.
    document.querySelectorAll('[data-utc]').forEach(function (el) {
        try {
            var d = new Date(el.getAttribute('data-utc'));
            el.textContent = d.toLocaleString([], { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        } catch (e) {}
    });

    // ---- Game picker (RAWG) ----
    function gameSet(g) {
        $('f-game-name').value = g.name || '';
        $('f-game-image').value = g.image || '';
        $('f-game-esrb').value = g.esrb || '';
        $('f-game-rawg-id').value = g.id || '';
        $('f-game-chip-img').src = g.image || '';
        $('f-game-chip-img').style.display = g.image ? '' : 'none';
        $('f-game-chip-name').textContent = g.name || '';
        $('f-game-chip-esrb').textContent = g.esrb ? ('ESRB: ' + g.esrb) : 'No ESRB rating';
        $('f-game-chip').style.display = 'flex';
        if ($('f-game-pick')) $('f-game-pick').style.display = 'none';
    }
    function gameClear() {
        ['f-game-name', 'f-game-image', 'f-game-esrb', 'f-game-rawg-id'].forEach(function (id) { if ($(id)) $(id).value = ''; });
        if ($('f-game-chip')) $('f-game-chip').style.display = 'none';
        if ($('f-game-pick')) $('f-game-pick').style.display = '';
        if ($('f-game-search')) $('f-game-search').value = '';
        if ($('f-game-results')) $('f-game-results').style.display = 'none';
    }
    if ($('f-game-clear')) $('f-game-clear').addEventListener('click', gameClear);
    if ($('f-game-search')) {
        var gtimer;
        $('f-game-search').addEventListener('input', function () {
            clearTimeout(gtimer);
            var q = this.value.trim(), box = $('f-game-results');
            if (q.length < 2) { box.style.display = 'none'; return; }
            gtimer = setTimeout(function () {
                fetch(D.games + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        box.innerHTML = '';
                        if (!list || !list.length) { box.style.display = 'none'; return; }
                        list.forEach(function (g) {
                            var row = document.createElement('button');
                            row.type = 'button';
                            row.style.cssText = 'display:flex;align-items:center;gap:10px;width:100%;text-align:left;border:0;background:transparent;color:var(--ll-text);padding:8px 10px;cursor:pointer;';
                            row.onmouseover = function () { row.style.background = 'color-mix(in srgb, var(--ll-primary) 12%, transparent)'; };
                            row.onmouseout = function () { row.style.background = 'transparent'; };
                            row.innerHTML = (g.image ? '<img src="' + g.image + '" style="width:34px;height:34px;border-radius:6px;object-fit:cover;flex:0 0 auto;">' : '') +
                                '<span style="min-width:0;"><span style="display:block;font-weight:600;font-size:.84rem;">' + (g.name || '') + '</span>' +
                                '<span style="display:block;font-size:.72rem;color:var(--ll-muted);">' + (g.released ? g.released.substring(0, 4) + ' · ' : '') + (g.esrb || 'No ESRB') + '</span></span>';
                            row.addEventListener('click', function () { gameSet(g); box.style.display = 'none'; });
                            box.appendChild(row);
                        });
                        box.style.display = '';
                    }).catch(function () { box.style.display = 'none'; });
            }, 300);
        });
        document.addEventListener('click', function (ev) { var box = $('f-game-results'); if (box && $('f-game-pick') && !$('f-game-pick').contains(ev.target)) box.style.display = 'none'; });
    }

    var kind = 'once';
    function setKind(k) {
        kind = k;
        $('seg-once').classList.toggle('on', k === 'once');
        $('seg-weekly').classList.toggle('on', k === 'weekly');
        $('fields-once').style.display = k === 'once' ? '' : 'none';
        $('fields-weekly').style.display = k === 'weekly' ? '' : 'none';
    }
    $('seg-once').addEventListener('click', function () { setKind('once'); });
    $('seg-weekly').addEventListener('click', function () { setKind('weekly'); });

    function resetForm() {
        $('f-id').value = ''; $('f-title').value = ''; $('f-platform').value = ''; $('f-reminder').value = '';
        $('f-url').value = ''; $('f-weekday').value = '4';
        $('f-date').value = dDate(new Date()); $('f-once-start').value = '19:00'; $('f-once-end').value = '21:00';
        $('f-start-time').value = '19:00'; $('f-end-time').value = '21:00';
        $('f-tags').value = ''; $('f-adult').checked = false; gameClear();
        setKind('once'); $('ll-ss-cancel').style.display = 'none';
        $('ll-ss-formtitle').innerHTML = '<i class="bi bi-plus-circle"></i> Add a stream';
    }
    $('ll-ss-cancel').addEventListener('click', resetForm);

    document.querySelectorAll('[data-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var e;
            try { e = JSON.parse(btn.getAttribute('data-edit')); } catch (x) { return; }
            $('f-id').value = e.id || '';
            $('f-title').value = e.title || '';
            $('f-platform').value = e.platform || '';
            $('f-reminder').value = e.reminder_minutes || '';
            $('f-url').value = e.url || '';
            $('f-tags').value = Array.isArray(e.tags) ? e.tags.join(', ') : (e.tags || '');
            $('f-adult').checked = !!e.is_adult;
            if (e.game_name) { gameSet({ name: e.game_name, image: e.game_image, esrb: e.game_esrb, id: e.game_rawg_id }); } else { gameClear(); }
            if ((e.kind || 'once') === 'weekly') {
                setKind('weekly');
                $('f-weekday').value = String(e.weekday != null ? e.weekday : 4);
                $('f-start-time').value = e.start_time || '19:00';
                $('f-end-time').value = e.end_time || '';
            } else {
                setKind('once');
                $('f-date').value = e.starts_at ? e.starts_at.substring(0, 10) : dDate(new Date());
                $('f-once-start').value = e.starts_at ? e.starts_at.substring(11, 16) : '19:00';
                $('f-once-end').value = e.ends_at ? e.ends_at.substring(11, 16) : '';
            }
            $('ll-ss-cancel').style.display = '';
            $('ll-ss-formtitle').innerHTML = '<i class="bi bi-pencil"></i> Edit stream';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    document.querySelectorAll('[data-del]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Remove this stream?')) return;
            post(D.base + '/' + btn.getAttribute('data-del') + '/delete', {}).then(reloadSoon).catch(function (err) { toast('#ll-ss-err', String(err)); });
        });
    });

    $('ll-ss-save').addEventListener('click', function () {
        var title = ($('f-title').value || '').trim();
        if (!title) { toast('#ll-ss-err', 'Give your stream a name.'); return; }
        var body = {
            title: title, platform: $('f-platform').value, url: ($('f-url').value || '').trim(),
            reminder_minutes: $('f-reminder').value, kind: kind, timezone: tz
        };
        if (kind === 'once') {
            var date = $('f-date').value, st = $('f-once-start').value, et = $('f-once-end').value;
            if (!date || !st) { toast('#ll-ss-err', 'Pick a date and start time.'); return; }
            body.starts_at = date + 'T' + st;
            if (et) {
                var endDate = date;
                if (et <= st) { var nd = new Date(date + 'T00:00'); nd.setDate(nd.getDate() + 1); endDate = dDate(nd); }
                body.ends_at = endDate + 'T' + et;
            } else { body.ends_at = ''; }
        } else { body.weekday = Number($('f-weekday').value); body.start_time = $('f-start-time').value; body.end_time = $('f-end-time').value; }
        body.is_adult = $('f-adult').checked ? 1 : 0;
        body.tags = ($('f-tags').value || '').trim();
        body.game_name = $('f-game-name').value;
        body.game_image = $('f-game-image').value;
        body.game_esrb = $('f-game-esrb').value;
        body.game_rawg_id = $('f-game-rawg-id').value;
        var id = $('f-id').value;
        var url = id ? (D.base + '/' + id) : D.store;
        var btn = $('ll-ss-save'); btn.disabled = true; btn.textContent = 'Saving…';
        post(url, body).then(reloadSoon).catch(function (err) { btn.disabled = false; btn.textContent = 'Save stream'; toast('#ll-ss-err', String(err)); });
    });

    $('ll-ss-copy').addEventListener('click', function () {
        var u = $('ll-ss-icsurl');
        u.select();
        navigator.clipboard ? navigator.clipboard.writeText(u.value).then(function () { toast('#ll-ss-toast', 'Feed URL copied.'); }) : document.execCommand('copy');
    });

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrf },
            credentials: 'same-origin', body: JSON.stringify(body)
        }).then(async function (r) { var d = await r.json().catch(function () { return {}; }); if (!r.ok || !d.ok) throw (d.message || ('HTTP ' + r.status)); return d; });
    }
    function reloadSoon() { toast('#ll-ss-toast', 'Saved.'); setTimeout(function () { window.location.reload(); }, 500); }
})();
</script>
@endverbatim
@endif
@endsection
