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

                <div class="ll-ss-seg" role="tablist">
                    <button type="button" id="seg-once" class="on" data-kind="once">One-off</button>
                    <button type="button" id="seg-weekly" data-kind="weekly">Every week</button>
                </div>

                <div id="fields-once">
                    <div class="ll-ss-2">
                        <div class="ll-ss-field"><label>Starts</label><input type="datetime-local" id="f-starts"></div>
                        <div class="ll-ss-field"><label>Ends</label><input type="datetime-local" id="f-ends"></div>
                    </div>
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
        base: '{{ url('/studio/stream-schedule') }}'
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

    // Render the preview times in the viewer's local timezone.
    document.querySelectorAll('[data-utc]').forEach(function (el) {
        try {
            var d = new Date(el.getAttribute('data-utc'));
            el.textContent = d.toLocaleString([], { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        } catch (e) {}
    });

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
        $('f-url').value = ''; $('f-starts').value = ''; $('f-ends').value = ''; $('f-weekday').value = '4';
        $('f-start-time').value = '19:00'; $('f-end-time').value = '21:00';
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
            if ((e.kind || 'once') === 'weekly') {
                setKind('weekly');
                $('f-weekday').value = String(e.weekday != null ? e.weekday : 4);
                $('f-start-time').value = e.start_time || '19:00';
                $('f-end-time').value = e.end_time || '';
            } else {
                setKind('once');
                $('f-starts').value = e.starts_at ? e.starts_at.substring(0, 16) : '';
                $('f-ends').value = e.ends_at ? e.ends_at.substring(0, 16) : '';
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
        if (kind === 'once') { body.starts_at = $('f-starts').value; body.ends_at = $('f-ends').value; }
        else { body.weekday = Number($('f-weekday').value); body.start_time = $('f-start-time').value; body.end_time = $('f-end-time').value; }
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
