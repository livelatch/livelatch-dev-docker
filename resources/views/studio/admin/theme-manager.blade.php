@extends('layouts.sidebar')

@section('content')
<style data-ll-tm-style>
    .ll-tm { display: grid; gap: 20px; }

    .ll-tm-head { display: flex; flex-wrap: wrap; gap: 14px 20px; align-items: flex-end; justify-content: space-between; }
    .ll-tm-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; }
    .ll-tm-head p { color: var(--ll-muted); margin: 0; max-width: 70ch; font-size: .9rem; }
    .ll-tm-s3 {
        display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: .78rem;
        border-radius: 999px; padding: 6px 12px; border: 1px solid var(--ll-border);
    }
    .ll-tm-s3.on  { color: #16a34a; border-color: color-mix(in srgb, #16a34a 45%, var(--ll-border)); background: color-mix(in srgb, #16a34a 10%, transparent); }
    .ll-tm-s3.off { color: #d97706; border-color: color-mix(in srgb, #d97706 45%, var(--ll-border)); background: color-mix(in srgb, #d97706 10%, transparent); }

    .ll-tm-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-tm-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-tm-panel h2 i { color: var(--ll-primary); }
    .ll-tm-panel .ll-tm-sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 14px; }

    /* Upload */
    .ll-tm-upload { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    .ll-tm-file { flex: 1 1 280px; color: var(--ll-text); font-size: .86rem; }
    .ll-tm-btn {
        display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer;
        border-radius: var(--ll-button-radius); font-weight: 700; font-size: .9rem; padding: 10px 18px;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff;
        transition: transform .12s ease, opacity .16s;
    }
    .ll-tm-btn:hover { transform: translateY(-1px); }
    .ll-tm-btn[disabled] { opacity: .55; cursor: default; transform: none; }
    .ll-tm-hint { color: var(--ll-muted); font-size: .76rem; margin: 10px 0 0; }

    /* Table */
    .ll-tm-grid { display: grid; gap: 10px; }
    .ll-tm-row {
        display: grid; grid-template-columns: 44px minmax(0,1.4fr) repeat(3, minmax(0,auto)) minmax(0,auto);
        gap: 14px; align-items: center; padding: 12px 14px;
        border: 1px solid var(--ll-border); border-radius: 14px; background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent);
    }
    .ll-tm-row.is-off { opacity: .62; }
    .ll-tm-swatch { width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--ll-border); }
    .ll-tm-name { min-width: 0; }
    .ll-tm-name strong { color: var(--ll-text); display: block; font-size: .95rem; line-height: 1.2; }
    .ll-tm-name span { color: var(--ll-muted); font-size: .76rem; }
    .ll-tm-meta { display: flex; flex-direction: column; gap: 3px; font-size: .74rem; color: var(--ll-muted); }
    .ll-tm-badge { display: inline-flex; align-items: center; gap: 5px; font-weight: 700; font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; border-radius: 999px; padding: 3px 8px; border: 1px solid var(--ll-border); width: max-content; }
    .ll-tm-badge.src-s3 { color: var(--ll-primary); border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }
    .ll-tm-select { border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-bg-soft); color: var(--ll-text); font-weight: 600; padding: 7px 9px; font-size: .82rem; }

    /* Toggle switch */
    .ll-tm-toggle { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }
    .ll-tm-toggle input { display: none; }
    .ll-tm-toggle .dot { width: 38px; height: 22px; border-radius: 999px; background: color-mix(in srgb, var(--ll-text) 18%, transparent); position: relative; transition: background .16s ease; }
    .ll-tm-toggle .dot::after { content: ""; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 999px; background: #fff; transition: transform .16s ease; }
    .ll-tm-toggle input:checked + .dot { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-tm-toggle input:checked + .dot::after { transform: translateX(16px); }
    .ll-tm-toggle .lbl { font-size: .8rem; font-weight: 600; color: var(--ll-text); }

    .ll-tm-row-status { font-size: .72rem; color: var(--ll-muted); min-height: 1em; }

    @media (max-width: 900px) {
        .ll-tm-row { grid-template-columns: 40px 1fr; }
        .ll-tm-row > .ll-tm-meta, .ll-tm-row > .ll-tm-tier, .ll-tm-row > .ll-tm-enable, .ll-tm-row > .ll-tm-row-status { grid-column: 2 / -1; }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-tm">
    <div class="ll-tm-head">
        <div>
            <h1>Theme Manager</h1>
            <p>Enable or disable themes, set each one Free or Pro, and upload new themes as a .zip straight to the S3 bucket — no redeploy. Disabled themes vanish from the user Themes tab; Pro themes stay previewable for free users but can only be applied on Pro.</p>
        </div>
        @if($s3Enabled)
            <span class="ll-tm-s3 on"><i class="bi bi-cloud-check-fill"></i> S3 connected</span>
        @else
            <span class="ll-tm-s3 off"><i class="bi bi-cloud-slash-fill"></i> S3 not configured</span>
        @endif
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="alert alert-success d-none" id="ll-tm-toast" role="alert"></div>
    <div class="alert alert-danger d-none" id="ll-tm-err" role="alert"></div>

    <section class="ll-tm-panel">
        <h2><i class="bi bi-cloud-arrow-up-fill"></i> Upload a theme</h2>
        <p class="ll-tm-sub">A .zip containing <code>manifest.json</code>, <code>&lt;slug&gt;.blade.php</code> and an optional <code>assets/</code> folder (at the root or inside one wrapping folder).</p>
        <form class="ll-tm-upload" id="ll-tm-upload-form" method="POST" action="{{ route('admin.themeManager.upload') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="bundle" accept=".zip" class="ll-tm-file" required @if(!$s3Enabled) disabled @endif>
            <button type="submit" class="ll-tm-btn" id="ll-tm-upload-btn" @if(!$s3Enabled) disabled @endif><i class="bi bi-upload"></i> Upload to S3</button>
        </form>
        @unless($s3Enabled)
            <p class="ll-tm-hint">Uploading is disabled because no S3 credentials are configured on this environment. Themes still work from the baked source; configure <code>AWS_*</code> to enable S3 delivery.</p>
        @endunless
    </section>

    <section class="ll-tm-panel">
        <h2><i class="bi bi-collection-fill"></i> Themes <span style="color:var(--ll-muted);font-weight:600;font-size:.82rem;">({{ count($themes) }})</span></h2>
        <p class="ll-tm-sub">All themes default to <strong>on</strong> and <strong>Free</strong>. Changes apply immediately.</p>
        <div class="ll-tm-grid">
            @forelse($themes as $t)
                @php
                    $slug = $t['slug'];
                    $tier = ($t['tier'] ?? 'free');
                    $enabled = ($t['enabled'] ?? true);
                    $source = ($t['source'] ?? 'baked');
                    $grad = $t['preview_gradient'] ?? 'linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2))';
                @endphp
                <div class="ll-tm-row {{ $enabled ? '' : 'is-off' }}" data-slug="{{ $slug }}">
                    <span class="ll-tm-swatch" style="background: {{ $grad }}"></span>
                    <div class="ll-tm-name">
                        <strong>{{ $t['name'] ?? $slug }}</strong>
                        <span>{{ $slug }} · {{ ($usage[$slug] ?? 0) }} {{ ($usage[$slug] ?? 0) === 1 ? 'use' : 'uses' }}</span>
                    </div>
                    <div class="ll-tm-meta">
                        <span class="ll-tm-badge {{ $source === 's3' ? 'src-s3' : '' }}">
                            <i class="bi {{ $source === 's3' ? 'bi-cloud-fill' : 'bi-box-seam' }}"></i> {{ strtoupper($source) }}
                        </span>
                    </div>
                    <div class="ll-tm-tier">
                        <select class="ll-tm-select" data-role="tier">
                            <option value="free" @selected($tier === 'free')>Free</option>
                            <option value="pro" @selected($tier === 'pro')>Pro</option>
                        </select>
                    </div>
                    <label class="ll-tm-toggle ll-tm-enable">
                        <input type="checkbox" data-role="enabled" @checked($enabled)>
                        <span class="dot"></span>
                        <span class="lbl">{{ $enabled ? 'On' : 'Off' }}</span>
                    </label>
                    <span class="ll-tm-row-status" data-role="status"></span>
                </div>
            @empty
                <p style="color:var(--ll-muted);">No themes found.</p>
            @endforelse
        </div>
    </section>
</div>

<script>
    window.LL_TM = {
        csrf: '{{ csrf_token() }}',
        updateUrl: '{{ route('admin.themeManager.update') }}'
    };
</script>
@verbatim
<script>
(function () {
    const D = window.LL_TM;
    const toast = (sel, msg) => { const b = document.querySelector(sel); b.textContent = msg; b.classList.remove('d-none'); setTimeout(() => b.classList.add('d-none'), 4000); };

    function post(body) {
        return fetch(D.updateUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrf },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        }).then(async (r) => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw (data.error || ('HTTP ' + r.status));
            return data;
        });
    }

    document.querySelectorAll('.ll-tm-row').forEach(row => {
        const slug = row.dataset.slug;
        const tier = row.querySelector('[data-role=tier]');
        const enabled = row.querySelector('[data-role=enabled]');
        const lbl = row.querySelector('.ll-tm-enable .lbl');
        const status = row.querySelector('[data-role=status]');

        function flash(msg) { status.textContent = msg; setTimeout(() => { status.textContent = ''; }, 2500); }
        function send(payload) {
            status.textContent = 'Saving…';
            post(Object.assign({ slug }, payload))
                .then(() => flash('Saved'))
                .catch(err => { toast('#ll-tm-err', String(err)); status.textContent = ''; });
        }

        tier.addEventListener('change', () => send({ tier: tier.value }));
        enabled.addEventListener('change', () => {
            row.classList.toggle('is-off', !enabled.checked);
            lbl.textContent = enabled.checked ? 'On' : 'Off';
            send({ enabled: enabled.checked ? 1 : 0 });
        });
    });

    const upForm = document.querySelector('#ll-tm-upload-form');
    if (upForm) {
        upForm.addEventListener('submit', () => {
            const btn = document.querySelector('#ll-tm-upload-btn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading…'; }
        });
    }
})();
</script>
@endverbatim
@endsection
