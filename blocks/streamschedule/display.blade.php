@php
    $ssOwnerId = (int) ($link->user_id ?? 0);
    $ssHandle = $ssOwnerId ? \App\Models\User::where('id', $ssOwnerId)->value('littlelink_name') : null;
    $ssUpcoming = ($ssOwnerId && $ssHandle) ? \App\Services\StreamScheduleService::upcomingForUser($ssOwnerId, 7) : [];
    $ssIcs = $ssHandle ? url('/@' . $ssHandle . '/schedule.ics') : null;
    $ssWebcal = $ssIcs ? 'webcal://' . preg_replace('#^https?://#', '', $ssIcs) : null;
    $ssTitle = ($link->title ?? '') !== '' ? $link->title : 'Stream Schedule';
@endphp

@once
<style>
    .ll-ssb { width:100%; margin:6px 0; padding:16px 18px; border:1px solid color-mix(in srgb, currentColor 16%, transparent); border-radius:18px; background: color-mix(in srgb, currentColor 5%, transparent); text-align:left; }
    .ll-ssb-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
    .ll-ssb-title { margin:0; font-size:1.05rem; font-weight:800; display:inline-flex; align-items:center; gap:8px; }
    .ll-ssb-sub { display:inline-flex; align-items:center; gap:7px; font-size:.76rem; font-weight:700; padding:7px 12px; border-radius:999px; text-decoration:none; color:inherit; border:1px solid color-mix(in srgb, currentColor 28%, transparent); background: color-mix(in srgb, currentColor 8%, transparent); }
    .ll-ssb-list { display:grid; gap:8px; }
    .ll-ssb-row { display:grid; grid-template-columns:auto 1fr; gap:12px; align-items:center; padding:10px 12px; border-radius:12px; border:1px solid color-mix(in srgb, currentColor 12%, transparent); background: color-mix(in srgb, currentColor 4%, transparent); }
    .ll-ssb-date { text-align:center; min-width:42px; }
    .ll-ssb-day { font-size:.64rem; text-transform:uppercase; letter-spacing:.06em; opacity:.7; }
    .ll-ssb-dom { font-size:1.25rem; font-weight:800; line-height:1; }
    .ll-ssb-name { font-weight:700; font-size:.92rem; }
    .ll-ssb-time { font-size:.78rem; opacity:.75; }
    .ll-ssb-empty { font-size:.85rem; opacity:.7; text-align:center; padding:6px 0; }
</style>
<script>
(function () {
    function fmt() {
        document.querySelectorAll('[data-ssutc]').forEach(function (el) {
            try {
                var d = new Date(el.getAttribute('data-ssutc'));
                var day = el.querySelector('[data-ssday]'), dom = el.querySelector('[data-ssdom]'), time = el.querySelector('[data-sstime]');
                if (day) day.textContent = d.toLocaleDateString([], { weekday: 'short' });
                if (dom) dom.textContent = d.toLocaleDateString([], { day: 'numeric' });
                if (time) time.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch (e) {}
        });
    }
    if (document.readyState !== 'loading') { fmt(); } else { document.addEventListener('DOMContentLoaded', fmt); }
})();
</script>
@endonce

<div class="ll-ssb">
    <div class="ll-ssb-head">
        <h3 class="ll-ssb-title"><i class="bi bi-calendar-week-fill"></i> {{ $ssTitle }}</h3>
        @if($ssWebcal)
            <a class="ll-ssb-sub" href="{{ $ssWebcal }}" rel="nofollow"><i class="bi bi-calendar-plus"></i> Sync to calendar</a>
        @endif
    </div>

    @if(empty($ssUpcoming))
        <div class="ll-ssb-empty">No streams scheduled this week.</div>
    @else
        <div class="ll-ssb-list">
            @foreach($ssUpcoming as $o)
                <div class="ll-ssb-row" data-ssutc="{{ $o['start']->format('c') }}">
                    <div class="ll-ssb-date">
                        <div class="ll-ssb-day" data-ssday>{{ $o['start']->format('D') }}</div>
                        <div class="ll-ssb-dom" data-ssdom>{{ $o['start']->format('j') }}</div>
                    </div>
                    <div>
                        <div class="ll-ssb-name">{{ $o['event']['title'] ?? 'Stream' }}@if(!empty($o['event']['platform'])) · {{ ucfirst($o['event']['platform']) }}@endif</div>
                        <div class="ll-ssb-time"><span data-sstime>{{ $o['start']->format('H:i') }}</span> your time</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
