@php
    $ssOwnerId = (int) ($link->user_id ?? 0);
    $ssDays = (int) ($link->days ?? 7);
    if (!in_array($ssDays, [3, 7, 14, 30], true)) { $ssDays = 7; }
    $ssHandle = $ssOwnerId ? \App\Models\User::where('id', $ssOwnerId)->value('littlelink_name') : null;
    $ssUpcoming = ($ssOwnerId && $ssHandle) ? \App\Services\StreamScheduleService::upcomingForUser($ssOwnerId, $ssDays) : [];
    $ssIcs = $ssHandle ? url('/@' . $ssHandle . '/schedule.ics') : null;
    $ssWebcal = $ssIcs ? 'webcal://' . preg_replace('#^https?://#', '', $ssIcs) : null;
    $ssTitle = ($link->title ?? '') !== '' ? $link->title : 'Stream Schedule';
@endphp

@once
<style>
    .ll-ssb { width:100%; margin:6px 0; padding:16px 18px; border:1px solid color-mix(in srgb, currentColor 16%, transparent); border-radius:18px; background: color-mix(in srgb, currentColor 5%, transparent); text-align:left; }
    .ll-ssb-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
    .ll-ssb-title { margin:0; font-size:1.05rem; font-weight:800; display:inline-flex; align-items:center; gap:8px; }
    .ll-ssb-window { font-size:.7rem; font-weight:600; opacity:.6; }
    .ll-ssb-sub { display:inline-flex; align-items:center; gap:7px; font-size:.76rem; font-weight:700; padding:7px 12px; border-radius:999px; text-decoration:none; color:inherit; border:1px solid color-mix(in srgb, currentColor 28%, transparent); background: color-mix(in srgb, currentColor 8%, transparent); }
    .ll-ssb-list { display:grid; gap:8px; }
    .ll-ssb-row { position:relative; display:grid; grid-template-columns:auto 1fr; gap:12px; align-items:center; padding:10px 12px; border-radius:12px; border:1px solid color-mix(in srgb, currentColor 12%, transparent); background: color-mix(in srgb, currentColor 4%, transparent); overflow:hidden; }
    .ll-ssb-row.has-game { color:#fff; border-color: rgba(255,255,255,.22); text-shadow: 0 1px 3px rgba(0,0,0,.6); min-height:64px; background-size:cover; background-position:center; }
    .ll-ssb-date { text-align:center; min-width:42px; }
    .ll-ssb-day { font-size:.64rem; text-transform:uppercase; letter-spacing:.06em; opacity:.7; }
    .ll-ssb-dom { font-size:1.25rem; font-weight:800; line-height:1; }
    .ll-ssb-name { font-weight:700; font-size:.92rem; }
    .ll-ssb-time { font-size:.78rem; opacity:.75; }
    .ll-ssb-tags { display:flex; flex-wrap:wrap; gap:5px; margin-top:6px; }
    .ll-ssb-tag { font-size:.6rem; font-weight:700; padding:2px 8px; border-radius:999px; border:1px solid color-mix(in srgb, currentColor 24%, transparent); background: color-mix(in srgb, currentColor 8%, transparent); }
    .ll-ssb-tag.adult { color:#fff; background:#d11; border-color:#d11; text-shadow:none; }
    .ll-ssb-row.has-game .ll-ssb-tag { border-color: rgba(255,255,255,.4); background: rgba(255,255,255,.16); }
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
        <h3 class="ll-ssb-title"><i class="bi bi-calendar-week-fill"></i> {{ $ssTitle }} <span class="ll-ssb-window">· next {{ $ssDays }} days</span></h3>
        @if($ssWebcal)
            <a class="ll-ssb-sub" href="{{ $ssWebcal }}" rel="nofollow"><i class="bi bi-calendar-plus"></i> Sync to calendar</a>
        @endif
    </div>

    @if(empty($ssUpcoming))
        <div class="ll-ssb-empty">No streams scheduled.</div>
    @else
        <div class="ll-ssb-list">
            @foreach($ssUpcoming as $o)
                @php
                    $ev = $o['event'];
                    $ssGameImg = (!empty($ev['game_image']) && preg_match('#^https://#', (string) $ev['game_image'])) ? $ev['game_image'] : null;
                    $ssRowStyle = $ssGameImg
                        ? "background-image: linear-gradient(90deg, rgba(0,0,0,.74), rgba(0,0,0,.4) 62%, rgba(0,0,0,.15)), url('" . $ssGameImg . "');"
                        : '';
                @endphp
                <div class="ll-ssb-row {{ $ssGameImg ? 'has-game' : '' }}" data-ssutc="{{ $o['start']->format('c') }}" @if($ssRowStyle) style="{{ $ssRowStyle }}" @endif>
                    <div class="ll-ssb-date">
                        <div class="ll-ssb-day" data-ssday>{{ $o['start']->format('D') }}</div>
                        <div class="ll-ssb-dom" data-ssdom>{{ $o['start']->format('j') }}</div>
                    </div>
                    <div>
                        <div class="ll-ssb-name">{{ $ev['title'] ?? 'Stream' }}@if(!empty($ev['platform'])) · {{ ucfirst($ev['platform']) }}@endif</div>
                        <div class="ll-ssb-time"><span data-sstime>{{ $o['start']->format('H:i') }}</span> your time</div>
                        @if(!empty($ev['is_adult']) || !empty($ev['game_name']) || !empty($ev['tags']))
                            <div class="ll-ssb-tags">
                                @if(!empty($ev['is_adult']))<span class="ll-ssb-tag adult">18+</span>@endif
                                @if(!empty($ev['game_name']))<span class="ll-ssb-tag">{{ $ev['game_name'] }}@if(!empty($ev['game_esrb'])) · {{ $ev['game_esrb'] }}@endif</span>@endif
                                @foreach(($ev['tags'] ?? []) as $t)<span class="ll-ssb-tag">{{ $t }}</span>@endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
