@extends('layouts.sidebar')

@section('content')
<style data-ll-tm-style>
    .ll-tm { display: grid; gap: 20px; }

    .ll-tm-head { display: flex; flex-wrap: wrap; gap: 14px 20px; align-items: flex-end; justify-content: space-between; }
    .ll-tm-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; }
    .ll-tm-head p { color: var(--ll-muted); margin: 0; max-width: 70ch; font-size: .9rem; }
    .ll-tm-s3 { display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: .78rem; border-radius: 999px; padding: 6px 12px; border: 1px solid var(--ll-border); }
    .ll-tm-s3.on  { color: #16a34a; border-color: color-mix(in srgb, #16a34a 45%, var(--ll-border)); background: color-mix(in srgb, #16a34a 10%, transparent); }
    .ll-tm-s3.off { color: #d97706; border-color: color-mix(in srgb, #d97706 45%, var(--ll-border)); background: color-mix(in srgb, #d97706 10%, transparent); }

    .ll-tm-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-tm-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-tm-panel h2 i { color: var(--ll-primary); }
    .ll-tm-panel .ll-tm-sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 14px; }

    .ll-tm-upload { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    .ll-tm-file { flex: 1 1 280px; color: var(--ll-text); font-size: .86rem; }
    .ll-tm-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; border-radius: var(--ll-button-radius); font-weight: 700; font-size: .9rem; padding: 10px 18px; background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); color: #fff; transition: transform .12s ease, opacity .16s; }
    .ll-tm-btn:hover { transform: translateY(-1px); }
    .ll-tm-btn[disabled] { opacity: .55; cursor: default; transform: none; }
    .ll-tm-btn-ghost { border: 1px solid var(--ll-border); background: var(--ll-surface-solid); color: var(--ll-text); border-radius: 10px; padding: 9px 16px; font-weight: 600; cursor: pointer; }
    .ll-tm-hint { color: var(--ll-muted); font-size: .76rem; margin: 10px 0 0; }

    .ll-tm-grid { display: grid; gap: 10px; }
    .ll-tm-row { display: grid; grid-template-columns: 44px minmax(0,1fr) auto auto auto; gap: 14px; align-items: center; padding: 12px 14px; border: 1px solid var(--ll-border); border-radius: 14px; background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); }
    .ll-tm-row.is-off { opacity: .62; }
    .ll-tm-swatch { width: 44px; height: 44px; border-radius: 10px; border: 1px solid var(--ll-border); }
    .ll-tm-name { min-width: 0; }
    .ll-tm-name strong { color: var(--ll-text); display: block; font-size: .95rem; line-height: 1.2; }
    .ll-tm-name .ll-tm-sub-line { color: var(--ll-muted); font-size: .76rem; }
    .ll-tm-badges { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 6px; }
    .ll-tm-badge { display: inline-flex; align-items: center; gap: 5px; font-weight: 700; font-size: .62rem; text-transform: uppercase; letter-spacing: .04em; border-radius: 999px; padding: 3px 8px; border: 1px solid var(--ll-border); color: var(--ll-muted); }
    .ll-tm-badge.src-s3 { color: var(--ll-primary); border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }
    .ll-tm-row-status { font-size: .7rem; color: var(--ll-muted); }

    /* AI badge — white coin keeps even a #000000 (Copilot) brand icon readable */
    .ll-ai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 2px 9px 2px 3px; border-radius: 999px; font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: var(--ll-text); border: 1px solid color-mix(in srgb, var(--ai) 50%, var(--ll-border)); background: color-mix(in srgb, var(--ai) 13%, transparent); }
    .ll-ai-coin { width: 16px; height: 16px; border-radius: 50%; background: #fff; display: grid; place-items: center; flex: 0 0 auto; }
    .ll-ai-i { width: 11px; height: 11px; background: var(--ai); -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; -webkit-mask-size: contain; mask-size: contain; }

    .ll-tm-select { border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-bg-soft); color: var(--ll-text); font-weight: 600; padding: 7px 9px; font-size: .82rem; }
    .ll-tm-edit { display: inline-flex; align-items: center; gap: 6px; min-height: 34px; padding: 0 12px; border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-surface-solid); color: var(--ll-text); font-weight: 600; font-size: .8rem; cursor: pointer; transition: border-color .14s, transform .14s; }
    .ll-tm-edit:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }

    .ll-tm-toggle { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }
    .ll-tm-toggle input { display: none; }
    .ll-tm-toggle .dot { width: 38px; height: 22px; border-radius: 999px; background: color-mix(in srgb, var(--ll-text) 18%, transparent); position: relative; transition: background .16s ease; }
    .ll-tm-toggle .dot::after { content: ""; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 999px; background: #fff; transition: transform .16s ease; }
    .ll-tm-toggle input:checked + .dot { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-tm-toggle input:checked + .dot::after { transform: translateX(16px); }
    .ll-tm-toggle .lbl { font-size: .8rem; font-weight: 600; color: var(--ll-text); }

    /* Manifest editor modal */
    .ll-tm-modal { position: fixed; inset: 0; z-index: 1200; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .ll-tm-modal[hidden] { display: none; }
    .ll-tm-modal-backdrop { position: absolute; inset: 0; background: rgba(2,4,16,.62); }
    .ll-tm-modal-panel { position: relative; width: min(700px, 100%); max-height: 88vh; display: flex; flex-direction: column; background: var(--ll-surface-solid); border: 1px solid var(--ll-border); border-radius: 18px; box-shadow: 0 40px 120px rgba(0,0,0,.5); overflow: hidden; }
    .ll-tm-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 15px 18px; border-bottom: 1px solid var(--ll-border); }
    .ll-tm-modal-head h3 { margin: 0; color: var(--ll-text); font-size: 1.05rem; display: flex; align-items: baseline; gap: 9px; }
    .ll-tm-modal-head h3 .slug { color: var(--ll-muted); font-weight: 600; font-size: .8rem; }
    .ll-tm-x { border: 0; background: transparent; color: var(--ll-muted); font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 2px 8px; border-radius: 8px; }
    .ll-tm-x:hover { color: var(--ll-text); background: color-mix(in srgb, var(--ll-text) 8%, transparent); }
    .ll-tm-modal-body { padding: 16px 18px; overflow: auto; display: grid; gap: 14px; }
    .ll-tm-modal-foot { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 13px 18px; border-top: 1px solid var(--ll-border); }
    .ll-tm-modal-foot #ed-modal-status { margin-right: auto; font-size: .8rem; }
    .ll-tm-field { display: grid; gap: 5px; }
    .ll-tm-field label { color: var(--ll-text); font-weight: 600; font-size: .82rem; }
    .ll-tm-field label small { color: var(--ll-muted); font-weight: 500; }
    .ll-tm-field input[type=text], .ll-tm-field textarea, .ll-tm-field select { width: 100%; border: 1px solid var(--ll-border); border-radius: 10px; background: var(--ll-bg-soft); color: var(--ll-text); padding: 9px 11px; font-size: .86rem; font-family: inherit; }
    .ll-tm-field textarea { resize: vertical; min-height: 56px; }
    .ll-tm-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .ll-tm-section { border: 1px solid color-mix(in srgb, var(--ll-primary) 18%, var(--ll-border)); border-radius: 14px; padding: 14px; display: grid; gap: 12px; }
    .ll-tm-section > h4 { margin: 0; color: var(--ll-text); font-size: .92rem; display: flex; align-items: center; gap: 8px; }
    .ll-tm-section > h4 i { color: var(--ll-primary); }
    .ll-tm-checks { display: flex; flex-wrap: wrap; gap: 9px 16px; }
    .ll-tm-check { display: inline-flex; align-items: center; gap: 7px; color: var(--ll-text); font-size: .84rem; font-weight: 600; cursor: pointer; }
    .ll-tm-dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; border: 1px solid color-mix(in srgb, var(--ll-text) 30%, transparent); flex: 0 0 auto; }
    .ll-tm-color-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ll-tm-color-row input[type=color] { width: 42px; height: 36px; border: 1px solid var(--ll-border); border-radius: 9px; background: var(--ll-bg-soft); padding: 3px; cursor: pointer; }
    .ll-tm-color-row input[type=text] { width: 110px; text-transform: uppercase; font-family: ui-monospace, Consolas, monospace; }
    .ll-tm-mini { border: 0; background: transparent; color: var(--ll-primary); font-weight: 700; font-size: .78rem; cursor: pointer; }
    .ll-tm-raw { width: 100%; min-height: 150px; resize: vertical; font-family: ui-monospace, Consolas, monospace; font-size: .78rem; border: 1px solid var(--ll-border); border-radius: 10px; background: #0e1424; color: #e6edff; padding: 11px; }
    .ll-tm-adv summary { cursor: pointer; color: var(--ll-muted); font-weight: 700; font-size: .8rem; }
    .is-hidden { display: none !important; }

    @media (max-width: 900px) {
        .ll-tm-row { grid-template-columns: 40px 1fr; }
        .ll-tm-row > .ll-tm-tier, .ll-tm-row > .ll-tm-enable, .ll-tm-row > .ll-tm-editcell { grid-column: 2 / -1; justify-self: start; }
        .ll-tm-2col { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0 ll-tm">
    <div class="ll-tm-head">
        <div>
            <h1>Theme Manager</h1>
            <p>Enable or disable themes, set each one Free or Pro, edit a theme's manifest, and upload new themes as a .zip straight to S3 — no redeploy. AI badges follow the <a href="{{ url('/studio/docs/policies/ai-use-policy') }}">AI Use Policy</a>.</p>
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
            <p class="ll-tm-hint">Uploading is disabled because no S3 credentials are configured on this environment. Configure <code>AWS_*</code> to enable S3 delivery and manifest editing.</p>
        @endunless
    </section>

    <section class="ll-tm-panel">
        <h2><i class="bi bi-collection-fill"></i> Themes <span style="color:var(--ll-muted);font-weight:600;font-size:.82rem;">({{ count($themes) }})</span></h2>
        <p class="ll-tm-sub">Click <strong>Edit</strong> to change a theme's manifest (name, AI disclosure &amp; badge, tags…). Enabled and Free/Pro are set with the row controls.</p>
        <div class="ll-tm-grid">
            @forelse($themes as $t)
                @php
                    $slug = $t['slug'];
                    $tier = ($t['tier'] ?? 'free');
                    $enabled = ($t['enabled'] ?? true);
                    $source = ($t['source'] ?? 'baked');
                    $grad = $t['preview_gradient'] ?? 'linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2))';
                    $badge = ai_badge($t);
                @endphp
                <div class="ll-tm-row {{ $enabled ? '' : 'is-off' }}" data-slug="{{ $slug }}">
                    <span class="ll-tm-swatch" style="background: {{ $grad }}"></span>
                    <div class="ll-tm-name">
                        <strong data-role="name">{{ $t['name'] ?? $slug }}</strong>
                        <span class="ll-tm-sub-line">{{ $slug }} · {{ ($usage[$slug] ?? 0) }} {{ ($usage[$slug] ?? 0) === 1 ? 'use' : 'uses' }}</span>
                        <div class="ll-tm-badges">
                            <span class="ll-tm-badge {{ $source === 's3' ? 'src-s3' : '' }}">
                                <i class="bi {{ $source === 's3' ? 'bi-cloud-fill' : 'bi-box-seam' }}"></i> {{ strtoupper($source) }}
                            </span>
                            <span data-role="ai-badge">
                                @if($badge)
                                    <span class="ll-ai-badge" style="--ai: {{ $badge['color'] }}" title="{{ $badge['label'] }}{{ $badge['tools'] ? ' · ' . implode(', ', $badge['tools']) : '' }}">
                                        @if($badge['iconUrl'])
                                            <span class="ll-ai-coin"><span class="ll-ai-i" style="-webkit-mask-image:url('{{ $badge['iconUrl'] }}');mask-image:url('{{ $badge['iconUrl'] }}')"></span></span>
                                        @else
                                            <span class="ll-ai-coin"><i class="bi bi-robot" style="color: {{ $badge['color'] }}; font-size:.7rem;"></i></span>
                                        @endif
                                        {{ $badge['label'] }}
                                    </span>
                                @endif
                            </span>
                            <span class="ll-tm-row-status" data-role="status"></span>
                        </div>
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
                    <div class="ll-tm-editcell">
                        <button type="button" class="ll-tm-edit" data-edit="{{ $slug }}" @unless($s3Enabled) disabled title="Editing needs S3 configured" @endunless><i class="bi bi-pencil-square"></i> Edit</button>
                    </div>
                </div>
            @empty
                <p style="color:var(--ll-muted);">No themes found.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="ll-tm-modal" id="ll-tm-modal" hidden>
    <div class="ll-tm-modal-backdrop" data-close></div>
    <div class="ll-tm-modal-panel" role="dialog" aria-modal="true" aria-labelledby="ed-modal-title">
        <div class="ll-tm-modal-head">
            <h3 id="ed-modal-title">Edit theme <span class="slug" id="ed-modal-slug"></span></h3>
            <button type="button" class="ll-tm-x" data-close aria-label="Close">&times;</button>
        </div>
        <div class="ll-tm-modal-body">
            <div class="ll-tm-field"><label for="ed-name">Name</label><input type="text" id="ed-name"></div>
            <div class="ll-tm-field"><label for="ed-desc">Description</label><textarea id="ed-desc"></textarea></div>
            <div class="ll-tm-2col">
                <div class="ll-tm-field"><label for="ed-author-name">Author name</label><input type="text" id="ed-author-name"></div>
                <div class="ll-tm-field"><label for="ed-author-handle">Author handle</label><input type="text" id="ed-author-handle"></div>
            </div>
            <div class="ll-tm-2col">
                <div class="ll-tm-field"><label for="ed-version">Version</label><input type="text" id="ed-version" placeholder="1.0.0"></div>
                <div class="ll-tm-field"><label for="ed-tags">Tags <small>comma separated</small></label><input type="text" id="ed-tags" placeholder="Space, Canvas"></div>
            </div>
            <div class="ll-tm-field"><label for="ed-gradient">Preview gradient <small>CSS background</small></label><input type="text" id="ed-gradient"></div>

            <div class="ll-tm-section">
                <h4><i class="bi bi-robot"></i> AI disclosure</h4>
                <div class="ll-tm-field"><label for="ed-ai-category">Category</label>
                    <select id="ed-ai-category">
                        <option value="none">No AI</option>
                        <option value="assisted">AI Assisted (sellable)</option>
                        <option value="generated">AI Generated (free only)</option>
                    </select>
                </div>
                <div id="ed-ai-details" class="is-hidden" style="display:grid; gap:12px;">
                    <div class="ll-tm-field">
                        <label>Tools used</label>
                        <div class="ll-tm-checks">
                            @foreach($aiTools as $name => $cfg)
                                <label class="ll-tm-check"><input type="checkbox" data-ai-tool="{{ $name }}"><span class="ll-tm-dot" style="background: {{ $cfg['color'] }}"></span> {{ $name }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ll-tm-field">
                        <label>Scope <small>art / video / audio are never allowed</small></label>
                        <div class="ll-tm-checks">
                            @foreach($aiScopes as $scope)
                                <label class="ll-tm-check"><input type="checkbox" data-ai-scope="{{ $scope }}"> {{ ucfirst($scope) }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ll-tm-field"><label for="ed-ai-notes">Notes <small>optional</small></label><textarea id="ed-ai-notes" placeholder="How AI was used"></textarea></div>
                    <div class="ll-tm-field">
                        <label>Badge colour <small>Simple Icons hex — defaults per tool</small></label>
                        <div class="ll-tm-color-row">
                            <input type="color" id="ed-ai-color" value="{{ $aiDefaultColor }}">
                            <input type="text" id="ed-ai-color-hex" value="{{ $aiDefaultColor }}" maxlength="7">
                            <button type="button" class="ll-tm-mini" id="ed-ai-color-reset">Default for tool</button>
                        </div>
                        <div style="margin-top:6px;"><span style="color:var(--ll-muted);font-size:.76rem;margin-right:8px;">Preview:</span><span id="ed-badge-preview"></span></div>
                    </div>
                </div>
            </div>

            <details class="ll-tm-adv">
                <summary>Advanced — raw manifest JSON</summary>
                <p style="color:var(--ll-muted);font-size:.78rem;margin:8px 0;">Edit nested fields (controls, presets, defaults, tier) here. This is the source of truth on save; the fields above write into it.</p>
                <textarea class="ll-tm-raw" id="ed-raw" spellcheck="false"></textarea>
            </details>
        </div>
        <div class="ll-tm-modal-foot">
            <span id="ed-modal-status"></span>
            <button type="button" class="ll-tm-btn-ghost" data-close>Cancel</button>
            <button type="button" class="ll-tm-btn" id="ed-modal-save"><i class="bi bi-check2-circle"></i> Save manifest</button>
        </div>
    </div>
</div>

<script>
    window.LL_TM = {
        csrf: '{{ csrf_token() }}',
        updateUrl: '{{ route('admin.themeManager.update') }}',
        manifestUrl: '{{ route('admin.themeManager.manifest') }}',
        manifests: @json($manifests),
        aiTools: @json($aiTools),
        aiDefaultColor: '{{ $aiDefaultColor }}'
    };
</script>
@verbatim
<script>
(function () {
    const D = window.LL_TM;
    const manifests = D.manifests || {};
    const aiTools = D.aiTools || {};
    const defColor = (D.aiDefaultColor || '#D97757').toUpperCase();
    const byId = (id) => document.getElementById(id);
    const toast = (sel, msg) => { const b = document.querySelector(sel); if (!b) return; b.textContent = msg; b.classList.remove('d-none'); setTimeout(() => b.classList.add('d-none'), 4000); };

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': D.csrf },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        }).then(async (r) => { const d = await r.json().catch(() => ({})); if (!r.ok) throw (d.error || ('HTTP ' + r.status)); return d; });
    }

    /* ---- row controls (enable / tier) ---- */
    document.querySelectorAll('.ll-tm-row').forEach(row => {
        const slug = row.dataset.slug;
        const tier = row.querySelector('[data-role=tier]');
        const enabled = row.querySelector('[data-role=enabled]');
        const lbl = row.querySelector('.ll-tm-enable .lbl');
        const status = row.querySelector('[data-role=status]');
        function flash(msg) { status.textContent = msg; setTimeout(() => { status.textContent = ''; }, 2500); }
        function send(payload) {
            status.textContent = 'Saving…';
            postJson(D.updateUrl, Object.assign({ slug }, payload)).then(() => flash('Saved')).catch(err => { toast('#ll-tm-err', String(err)); status.textContent = ''; });
        }
        tier.addEventListener('change', () => send({ tier: tier.value }));
        enabled.addEventListener('change', () => { row.classList.toggle('is-off', !enabled.checked); lbl.textContent = enabled.checked ? 'On' : 'Off'; send({ enabled: enabled.checked ? 1 : 0 }); });
    });

    const upForm = document.querySelector('#ll-tm-upload-form');
    if (upForm) upForm.addEventListener('submit', () => { const b = byId('ll-tm-upload-btn'); if (b) { b.disabled = true; b.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading…'; } });

    /* ---- AI badge compute / render (mirrors App\Support\Ai) ---- */
    function computeBadge(m) {
        const ai = (m && m.ai) || {};
        if (ai.category !== 'assisted' && ai.category !== 'generated') return null;
        const tools = Array.isArray(ai.tools) ? ai.tools.filter(Boolean) : [];
        let slug = null, color = null;
        for (const t of tools) { if (aiTools[t]) { slug = aiTools[t].slug; color = aiTools[t].color; break; } }
        if (typeof ai.badgeColor === 'string' && /^#[0-9a-fA-F]{6}$/.test(ai.badgeColor)) color = ai.badgeColor.toUpperCase();
        color = (color || defColor).toUpperCase();
        return { category: ai.category, label: ai.category === 'generated' ? 'AI Generated' : 'AI Assisted', color, slug, iconUrl: slug ? 'https://cdn.simpleicons.org/' + slug : null, tools };
    }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
    function badgeHtml(b) {
        if (!b) return '';
        const coin = b.iconUrl
            ? '<span class="ll-ai-coin"><span class="ll-ai-i" style="-webkit-mask-image:url(\'' + b.iconUrl + '\');mask-image:url(\'' + b.iconUrl + '\')"></span></span>'
            : '<span class="ll-ai-coin"><i class="bi bi-robot" style="color:' + b.color + ';font-size:.7rem"></i></span>';
        return '<span class="ll-ai-badge" style="--ai:' + b.color + '" title="' + esc(b.label + (b.tools.length ? ' · ' + b.tools.join(', ') : '')) + '">' + coin + b.label + '</span>';
    }

    /* ---- manifest editor ---- */
    const modal = byId('ll-tm-modal');
    let cur = { slug: null, m: null };
    function ensureAi() { if (!cur.m.ai || typeof cur.m.ai !== 'object') cur.m.ai = { category: 'none', tools: [], scope: [] }; }
    function status(msg, err) { const s = byId('ed-modal-status'); s.textContent = msg || ''; s.style.color = err ? 'var(--ll-danger)' : 'var(--ll-muted)'; }
    function refreshRaw() { byId('ed-raw').value = JSON.stringify(cur.m, null, 2); }
    function effectiveColor() { const b = computeBadge(cur.m); return (b && b.color) || defColor; }
    function syncColorInputs() { const c = effectiveColor(); byId('ed-ai-color').value = c; byId('ed-ai-color-hex').value = c.toUpperCase(); }
    function refreshBadgePreview() {
        const b = computeBadge(cur.m);
        byId('ed-badge-preview').innerHTML = b ? badgeHtml(b) : '<span style="color:var(--ll-muted);font-size:.8rem">No badge — category is No AI.</span>';
    }
    function toggleAiFields() { byId('ed-ai-details').classList.toggle('is-hidden', byId('ed-ai-category').value === 'none'); }

    function fillFields() {
        const m = cur.m, ai = m.ai || {};
        byId('ed-name').value = m.name || '';
        byId('ed-desc').value = m.description || '';
        byId('ed-author-name').value = m.authorName || '';
        byId('ed-author-handle').value = m.authorHandle || '';
        byId('ed-version').value = m.version || '';
        byId('ed-tags').value = Array.isArray(m.tags) ? m.tags.join(', ') : (m.tags || '');
        byId('ed-gradient').value = m.preview_gradient || '';
        byId('ed-ai-category').value = ['none', 'assisted', 'generated'].indexOf(ai.category) !== -1 ? ai.category : 'none';
        modal.querySelectorAll('[data-ai-tool]').forEach(cb => { cb.checked = Array.isArray(ai.tools) && ai.tools.indexOf(cb.dataset.aiTool) !== -1; });
        modal.querySelectorAll('[data-ai-scope]').forEach(cb => { cb.checked = Array.isArray(ai.scope) && ai.scope.indexOf(cb.dataset.aiScope) !== -1; });
        byId('ed-ai-notes').value = ai.notes || '';
        toggleAiFields();
        syncColorInputs();
        refreshBadgePreview();
        byId('ed-modal-slug').textContent = cur.slug;
    }

    function bindText(id, key) {
        byId(id).addEventListener('input', (e) => { const v = e.target.value; if (v === '') delete cur.m[key]; else cur.m[key] = v; refreshRaw(); });
    }
    function readTools() { return Array.from(modal.querySelectorAll('[data-ai-tool]:checked')).map(cb => cb.dataset.aiTool); }
    function readScope() { return Array.from(modal.querySelectorAll('[data-ai-scope]:checked')).map(cb => cb.dataset.aiScope); }

    function bindEditor() {
        bindText('ed-name', 'name');
        bindText('ed-desc', 'description');
        bindText('ed-author-name', 'authorName');
        bindText('ed-author-handle', 'authorHandle');
        bindText('ed-version', 'version');
        bindText('ed-gradient', 'preview_gradient');
        byId('ed-tags').addEventListener('input', (e) => { cur.m.tags = e.target.value.split(',').map(s => s.trim()).filter(Boolean); refreshRaw(); });

        byId('ed-ai-category').addEventListener('change', (e) => { ensureAi(); cur.m.ai.category = e.target.value; toggleAiFields(); syncColorInputs(); refreshBadgePreview(); refreshRaw(); });
        modal.querySelectorAll('[data-ai-tool]').forEach(cb => cb.addEventListener('change', () => { ensureAi(); cur.m.ai.tools = readTools(); syncColorInputs(); refreshBadgePreview(); refreshRaw(); }));
        modal.querySelectorAll('[data-ai-scope]').forEach(cb => cb.addEventListener('change', () => { ensureAi(); cur.m.ai.scope = readScope(); refreshRaw(); }));
        byId('ed-ai-notes').addEventListener('input', (e) => { ensureAi(); if (e.target.value) cur.m.ai.notes = e.target.value; else delete cur.m.ai.notes; refreshRaw(); });

        function setColor(v) { v = (v || '').toUpperCase(); if (!/^#[0-9A-F]{6}$/.test(v)) return; ensureAi(); cur.m.ai.badgeColor = v; byId('ed-ai-color').value = v; byId('ed-ai-color-hex').value = v; refreshBadgePreview(); refreshRaw(); }
        byId('ed-ai-color').addEventListener('input', (e) => setColor(e.target.value));
        byId('ed-ai-color-hex').addEventListener('change', (e) => setColor(e.target.value));
        byId('ed-ai-color-reset').addEventListener('click', () => { ensureAi(); delete cur.m.ai.badgeColor; syncColorInputs(); refreshBadgePreview(); refreshRaw(); });

        byId('ed-raw').addEventListener('change', () => {
            try { const parsed = JSON.parse(byId('ed-raw').value); cur.m = parsed; ensureAi(); fillFields(); status(''); }
            catch (err) { status('Invalid JSON: ' + err.message, true); }
        });

        byId('ed-modal-save').addEventListener('click', save);
        modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeModal));
    }

    function openEditor(slug) {
        const src = manifests[slug];
        if (!src) { toast('#ll-tm-err', 'No manifest loaded for ' + slug + '.'); return; }
        cur.slug = slug;
        cur.m = JSON.parse(JSON.stringify(src));
        ensureAi();
        fillFields();
        refreshRaw();
        status('');
        modal.removeAttribute('hidden');
        document.addEventListener('keydown', onEsc);
    }
    function closeModal() { modal.setAttribute('hidden', ''); document.removeEventListener('keydown', onEsc); }
    function onEsc(e) { if (e.key === 'Escape') closeModal(); }

    function save() {
        if (!cur.m || !cur.slug) return;
        if (!cur.m.name) { status('A name is required.', true); return; }
        const btn = byId('ed-modal-save'); const orig = btn.innerHTML; btn.disabled = true; btn.innerHTML = 'Saving…'; status('');
        postJson(D.manifestUrl, { slug: cur.slug, manifest: cur.m })
            .then((d) => {
                manifests[cur.slug] = cur.m;
                const row = document.querySelector('.ll-tm-row[data-slug="' + cur.slug + '"]');
                if (row) {
                    const nameEl = row.querySelector('[data-role=name]'); if (nameEl) nameEl.textContent = cur.m.name;
                    const badgeEl = row.querySelector('[data-role=ai-badge]'); if (badgeEl) badgeEl.innerHTML = badgeHtml(d.aiBadge || computeBadge(cur.m));
                }
                closeModal();
                toast('#ll-tm-toast', d.message || 'Saved.');
            })
            .catch((err) => status(String(err), true))
            .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
    }

    document.querySelectorAll('[data-edit]').forEach(b => b.addEventListener('click', () => { if (!b.disabled) openEditor(b.dataset.edit); }));
    bindEditor();
})();
</script>
@endverbatim
@endsection
