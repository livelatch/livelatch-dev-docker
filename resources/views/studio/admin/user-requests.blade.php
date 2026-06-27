@extends('layouts.sidebar')

@section('content')
<style data-ll-ur-style>
    .ll-ur { display: grid; gap: 18px; }
    .ll-ur-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; }
    .ll-ur-head p { color: var(--ll-muted); margin: 0; max-width: 72ch; font-size: .9rem; }

    .ll-ur-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-ur-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
    .ll-ur-panel h2 i { color: var(--ll-primary); }

    .ll-ur-row { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 14px; align-items: center; padding: 14px; border: 1px solid var(--ll-border); border-radius: 14px; background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); }
    .ll-ur-row + .ll-ur-row { margin-top: 10px; }
    .ll-ur-change { display: grid; gap: 8px; min-width: 0; }
    .ll-ur-line { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; color: var(--ll-text); font-weight: 600; font-size: .92rem; }
    .ll-ur-line .lbl { color: var(--ll-muted); font-weight: 700; font-size: .66rem; text-transform: uppercase; letter-spacing: .04em; }
    .ll-ur-old { color: var(--ll-muted); text-decoration: line-through; }
    .ll-ur-new { color: var(--ll-text); border: 1px solid color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); background: color-mix(in srgb, var(--ll-primary) 12%, transparent); border-radius: 999px; padding: 2px 10px; font-weight: 700; }
    .ll-ur-arrow { color: var(--ll-muted); }
    .ll-ur-meta { color: var(--ll-muted); font-size: .76rem; }
    .ll-ur-meta a { color: var(--ll-primary); text-decoration: none; }
    .ll-ur-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .ll-ur-btn { display: inline-flex; align-items: center; gap: 7px; min-height: 38px; padding: 0 16px; border-radius: 11px; border: 1px solid var(--ll-border); background: var(--ll-surface-solid); color: var(--ll-text); font-weight: 700; font-size: .85rem; cursor: pointer; transition: transform .12s ease, opacity .16s; }
    .ll-ur-btn:hover { transform: translateY(-1px); }
    .ll-ur-btn[disabled] { opacity: .5; cursor: default; transform: none; }
    .ll-ur-approve { border: 0; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; }
    .ll-ur-status { font-size: .74rem; color: var(--ll-muted); align-self: center; }
    .ll-ur-empty { border: 1px dashed var(--ll-border); border-radius: 14px; padding: 26px; text-align: center; color: var(--ll-muted); }

    @media (max-width: 760px) { .ll-ur-row { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-ur">
    <div class="ll-ur-head">
        <h1>User Requests</h1>
        <p>Custom name &amp; URL change requests from creators. (Names that match a creator's linked LatchID identity apply automatically and never appear here.) Approving updates their live handle + display name and keeps their old URL working as an alias.</p>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="alert alert-success d-none" id="ll-ur-toast" role="alert"></div>
    <div class="alert alert-danger d-none" id="ll-ur-err" role="alert"></div>

    <section class="ll-ur-panel">
        <h2><i class="bi bi-person-badge-fill"></i> Pending <span style="color:var(--ll-muted);font-weight:600;font-size:.82rem;">({{ count($requests) }})</span></h2>

        @forelse($requests as $r)
            <div class="ll-ur-row" data-id="{{ $r['id'] ?? '' }}">
                <div class="ll-ur-change">
                    @if(!empty($r['requested_handle']))
                        <div class="ll-ur-line"><span class="lbl">URL</span> <span class="ll-ur-old">{{ '@' . ($r['current_handle'] ?? '—') }}</span> <span class="ll-ur-arrow">→</span> <span class="ll-ur-new">{{ '@' . $r['requested_handle'] }}</span></div>
                    @endif
                    @if(!empty($r['requested_display_name']))
                        <div class="ll-ur-line"><span class="lbl">Name</span> <span class="ll-ur-old">{{ $r['current_display_name'] ?? '—' }}</span> <span class="ll-ur-arrow">→</span> <span class="ll-ur-new">{{ $r['requested_display_name'] }}</span></div>
                    @endif
                    <div class="ll-ur-meta">
                        @if(!empty($r['email']))<a href="mailto:{{ $r['email'] }}">{{ $r['email'] }}</a> · @endif
                        user #{{ $r['laravel_user_id'] ?? '?' }}
                        @if(!empty($r['created_at'])) · {{ \Illuminate\Support\Carbon::parse($r['created_at'])->diffForHumans() }}@endif
                    </div>
                </div>
                <div class="ll-ur-actions">
                    <span class="ll-ur-status" data-role="status"></span>
                    <button type="button" class="ll-ur-btn" data-action="reject"><i class="bi bi-x-lg"></i> Reject</button>
                    <button type="button" class="ll-ur-btn ll-ur-approve" data-action="approve"><i class="bi bi-check2"></i> Approve</button>
                </div>
            </div>
        @empty
            <div class="ll-ur-empty">No pending requests. Custom name/URL requests from creators will appear here for review.</div>
        @endforelse
    </section>
</div>

<script>
    window.LL_UR = { csrf: '{{ csrf_token() }}', url: '{{ route('admin.userRequests.decide') }}' };
</script>
@verbatim
<script>
(function () {
    var D = window.LL_UR;
    function toast(sel, msg) { var b = document.querySelector(sel); if (!b) return; b.textContent = msg; b.classList.remove('d-none'); setTimeout(function () { b.classList.add('d-none'); }, 4000); }

    document.querySelectorAll('.ll-ur-row').forEach(function (row) {
        var id = row.dataset.id;
        var status = row.querySelector('[data-role=status]');
        row.querySelectorAll('[data-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.dataset.action;
                row.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
                status.textContent = action === 'approve' ? 'Approving…' : 'Rejecting…';
                fetch(D.url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrf },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id: Number(id), action: action })
                }).then(async function (r) {
                    var d = await r.json().catch(function () { return {}; });
                    if (!r.ok || !d.ok) throw (d.error || ('HTTP ' + r.status));
                    row.style.transition = 'opacity .25s ease'; row.style.opacity = '0';
                    setTimeout(function () { row.remove(); }, 250);
                    toast('#ll-ur-toast', 'Request ' + (action === 'approve' ? 'approved' : 'rejected') + '.');
                }).catch(function (err) {
                    status.textContent = '';
                    row.querySelectorAll('button').forEach(function (b) { b.disabled = false; });
                    toast('#ll-ur-err', String(err));
                });
            });
        });
    });
})();
</script>
@endverbatim
@endsection
