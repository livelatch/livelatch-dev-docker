@extends('layouts.sidebar')

@section('content')
@php
    use Illuminate\Support\Str;

    $editing = $card !== null;
    $action = $editing
        ? route('studio.latchdeck.cards.update', $card['id'])
        : route('studio.latchdeck.cards.store');

    // Prefill helper: old() (after a failed save) → existing card value → default.
    $v = function (string $key, $default = null) use ($card) {
        return old($key, $card[$key] ?? $default);
    };

    $standardRarities = ['common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare'];
    $premiumRarities = ['epic' => 'Epic', 'legendary' => 'Legendary', 'mythic' => 'Mythic'];
    $effects = ['none' => 'None', 'holo' => 'Holographic', 'shiny' => 'Shiny', 'foil' => 'Foil'];

    $premiumRaritiesAllowed = (bool) ($capabilities['premiumRarities'] ?? false);
    $premiumEffectsAllowed = (bool) ($capabilities['premiumEffects'] ?? false);

    $bg = $v('background_color_mvp', '#160000');
    $txt = $v('text_color_mvp', '#5e0808');
    $effect = $v('effect_mvp', 'none');
    $rarity = $v('rarity_mvp', 'common');
    $name = $v('name_mvp', '');
    $short = $v('short_description_mvp', '');
    $body = $v('long_description_mvp', '');
    $supply = $v('supply_cap_mvp', '');
    $redeem = $v('redeem_code_mvp', '');

    $imgUrl = $card['image_url_mvp'] ?? null;
    $imgSource = old('image_source', $card['image_source_mvp'] ?? '');
    $credit = $card['image_credit_mvp'] ?? null;
    $isUnsplash = ($credit['source'] ?? '') === 'unsplash';
    $unsplashUrlPrefill = $isUnsplash ? $imgUrl : '';
@endphp

<style>
    .ld-editor { display: grid; grid-template-columns: minmax(300px, 360px) minmax(0, 1fr); gap: 24px; align-items: start; }
    .ld-stagecol { position: sticky; top: 90px; }

    /* --- Live card (V1 template, native 375x525, rendered from data) --- */
    .lc-stage { width: 330px; height: 462px; margin: 0 auto; }
    .lc-card {
        position: relative; width: 375px; height: 525px; transform: scale(.88); transform-origin: top left;
        border-radius: 18px; overflow: hidden; padding: 22px;
        background: var(--lc-bg, #160000); color: var(--lc-txt, #5e0808);
        display: flex; flex-direction: column; box-shadow: 0 24px 60px rgba(0,0,0,.45);
        font-family: 'Inter', system-ui, sans-serif;
    }
    .lc-headline { font-size: 34px; font-weight: 800; line-height: 1.02; text-transform: uppercase; letter-spacing: -.5px; word-break: break-word; }
    .lc-art { margin-top: 14px; height: 150px; border-radius: 8px; overflow: hidden; background: rgba(0,0,0,.35); }
    .lc-art img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .lc-art.has-img img { display: block; }
    .lc-rarity { align-self: flex-start; margin-top: 16px; font-size: 13px; font-weight: 800; letter-spacing: .12em;
        text-transform: uppercase; padding: 5px 14px; border-radius: 999px; border: 2px solid currentColor; }
    .lc-divider { height: 6px; margin: 16px 0 12px; background: currentColor; opacity: .85; border-radius: 2px; }
    .lc-body { font-size: 17px; line-height: 1.32; font-weight: 600; flex: 1 1 auto; overflow: hidden; word-break: break-word; }
    .lc-footer { display: flex; justify-content: space-between; gap: 10px; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .9; }
    .lc-credit { position: absolute; left: 12px; bottom: 40px; font-size: 9px; opacity: .85; max-width: 70%; }
    .lc-credit a { color: inherit; text-decoration: underline; }

    /* Render effects (overlay; pure CSS, browser preview) */
    .lc-fx { position: absolute; inset: 0; border-radius: inherit; pointer-events: none; opacity: 0; background-size: 200% 200%; }
    .lc-effect-holo .lc-fx { opacity: .5; mix-blend-mode: color-dodge; filter: blur(2px);
        background: conic-gradient(from 0deg, #ff0080, #7928ca, #2afadf, #00ff8f, #ffd700, #ff0080); animation: lc-holo 6s linear infinite; }
    .lc-effect-shiny .lc-fx { opacity: .85; mix-blend-mode: screen;
        background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.55) 48%, transparent 62%); animation: lc-shine 3.2s ease-in-out infinite; }
    .lc-effect-foil .lc-fx { opacity: .4; mix-blend-mode: overlay;
        background: repeating-linear-gradient(135deg, rgba(255,255,255,.18) 0 2px, transparent 2px 7px), linear-gradient(135deg, #c0c0c0, #8a8a8a, #e8e8e8); animation: lc-holo 5s linear infinite; }
    @keyframes lc-holo { 0% { background-position: 0% 50%; } 100% { background-position: 200% 50%; } }
    @keyframes lc-shine { 0% { background-position: -150% 0; } 100% { background-position: 150% 0; } }

    /* --- Controls --- */
    .ld-field { margin-bottom: 16px; }
    .ld-tabs { display: inline-flex; gap: 6px; margin-bottom: 12px; }
    .ld-tab { border: 1px solid var(--bs-border-color, #ccc); background: transparent; border-radius: 999px; padding: 6px 14px; font-weight: 600; font-size: .85rem; cursor: pointer; }
    .ld-tab.is-active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
    .ld-tabpane { display: none; }
    .ld-tabpane.is-active { display: block; }
    .ld-unsplash-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; max-height: 320px; overflow: auto; margin-top: 10px; }
    .ld-unsplash-item { position: relative; border: 2px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; aspect-ratio: 3/4; background: rgba(0,0,0,.06); }
    .ld-unsplash-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ld-unsplash-item.is-active { border-color: #0d6efd; }
    .ld-unsplash-cred { position: absolute; left: 0; right: 0; bottom: 0; font-size: 9px; padding: 2px 4px; background: rgba(0,0,0,.55); color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    @media (max-width: 991.98px) {
        .ld-editor { grid-template-columns: 1fr; }
        .ld-stagecol { position: static; }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-collection-fill"></i> {{ $editing ? 'Edit card' : 'New card' }}</h2>
            <p class="text-muted mb-0">Design your card from the V1 template — it's stored as data and drawn live.</p>
        </div>
        <a href="{{ route('studio.latchdeck') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to studio</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="ld-form">
        @csrf

        {{-- Image source + Unsplash attribution travel as hidden fields --}}
        <input type="hidden" name="image_source" id="f-image-source" value="{{ $imgSource }}">
        <input type="hidden" name="unsplash_url" id="f-unsplash-url" value="{{ old('unsplash_url', $unsplashUrlPrefill) }}">
        <input type="hidden" name="unsplash_photographer" id="f-unsplash-photographer" value="{{ old('unsplash_photographer', $credit['photographer'] ?? '') }}">
        <input type="hidden" name="unsplash_photographer_url" id="f-unsplash-photographer-url" value="{{ old('unsplash_photographer_url', $credit['photographer_url'] ?? '') }}">
        <input type="hidden" name="unsplash_photo_url" id="f-unsplash-photo-url" value="{{ old('unsplash_photo_url', $credit['photo_url'] ?? '') }}">
        <input type="hidden" name="unsplash_download_location" id="f-unsplash-download" value="">

        <div class="ld-editor">
            {{-- Live preview --}}
            <div class="ld-stagecol">
                <div class="lc-stage">
                    <div class="lc-card lc-effect-{{ $effect }}" id="lc-card" style="--lc-bg: {{ $bg }}; --lc-txt: {{ $txt }};">
                        <div class="lc-headline" id="lc-headline">{{ $name ?: 'HEADLINE TEXT' }}</div>
                        <div class="lc-art {{ $imgUrl ? 'has-img' : '' }}" id="lc-art">
                            <img id="lc-art-img" src="{{ $imgUrl ?: '' }}" alt="">
                        </div>
                        <div class="lc-rarity" id="lc-rarity">{{ strtoupper($rarity) }}</div>
                        <div class="lc-divider"></div>
                        <div class="lc-body" id="lc-body">{{ $body ?: 'This is where you write up what the card commemorates. It is stored as data and drawn on screen — no image of the card is saved.' }}</div>
                        <div class="lc-footer">
                            <span id="lc-footer-left">{{ $redeem ? strtoupper($redeem) : 'PREVIEW' }}</span>
                            <span id="lc-footer-right">#0001{{ $supply ? ' / ' . $supply : '' }}</span>
                        </div>
                        <div class="lc-credit" id="lc-credit" {{ ($credit && ($credit['source'] ?? '') === 'unsplash') ? '' : 'hidden' }}>
                            Photo by
                            <a id="lc-credit-photographer" href="{{ $credit['photographer_url'] ?? '#' }}" target="_blank" rel="noopener">{{ $credit['photographer'] ?? '' }}</a>
                            on <a href="https://unsplash.com?utm_source={{ config('services.unsplash.utm_source', 'livelatch') }}&utm_medium=referral" target="_blank" rel="noopener">Unsplash</a>
                        </div>
                        <div class="lc-fx" aria-hidden="true"></div>
                    </div>
                </div>
                <p class="text-center text-muted small mt-2">375 × 525 · drawn from your data</p>
            </div>

            {{-- Controls --}}
            <div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="ld-field">
                            <label class="form-label fw-semibold">Headline (name)</label>
                            <input type="text" name="name_mvp" id="i-name" class="form-control" maxlength="120" value="{{ $name }}" required>
                        </div>
                        <div class="ld-field">
                            <label class="form-label fw-semibold">Tagline (short description)</label>
                            <input type="text" name="short_description_mvp" id="i-short" class="form-control" maxlength="255" value="{{ $short }}" required>
                        </div>
                        <div class="ld-field">
                            <label class="form-label fw-semibold">Write-up (body)</label>
                            <textarea name="long_description_mvp" id="i-body" class="form-control" rows="4" maxlength="2000">{{ $body }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Rarity</label>
                                <select name="rarity_mvp" id="i-rarity" class="form-select" required>
                                    @foreach($standardRarities as $val => $label)
                                        <option value="{{ $val }}" @selected($rarity === $val)>{{ $label }}</option>
                                    @endforeach
                                    @foreach($premiumRarities as $val => $label)
                                        <option value="{{ $val }}" @selected($rarity === $val) @disabled(!$premiumRaritiesAllowed)>{{ $label }}{{ $premiumRaritiesAllowed ? '' : ' — Pro' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Effect</label>
                                <select name="effect_mvp" id="i-effect" class="form-select">
                                    @foreach($effects as $val => $label)
                                        @php $isPremiumEffect = $val !== 'none'; @endphp
                                        <option value="{{ $val }}" @selected($effect === $val) @disabled($isPremiumEffect && !$premiumEffectsAllowed)>{{ $label }}{{ ($isPremiumEffect && !$premiumEffectsAllowed) ? ' — Pro' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Background colour</label>
                                <input type="color" name="background_color_mvp" id="i-bg" class="form-control form-control-color w-100" value="{{ $bg }}">
                            </div>
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Text colour</label>
                                <input type="color" name="text_color_mvp" id="i-txt" class="form-control form-control-color w-100" value="{{ $txt }}">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Amount created</label>
                                <input type="number" name="supply_cap_mvp" id="i-supply" class="form-control" min="1" max="1000000" value="{{ $supply }}" placeholder="e.g. 100">
                                <div class="form-text">How many copies exist. Leave blank to decide later.</div>
                            </div>
                            <div class="col-sm-6 ld-field">
                                <label class="form-label fw-semibold">Redeem code</label>
                                <input type="text" name="redeem_code_mvp" id="i-redeem" class="form-control" maxlength="40" value="{{ $redeem }}" placeholder="e.g. STREAM-DROP-1">
                                <div class="form-text">Letters, numbers, <code>. _ -</code>. Must be unique.</div>
                            </div>
                        </div>

                        {{-- Image picker --}}
                        <div class="ld-field">
                            <label class="form-label fw-semibold d-block">Card art</label>
                            <div class="ld-tabs" role="tablist">
                                <button type="button" class="ld-tab {{ $imgSource !== 'unsplash' ? 'is-active' : '' }}" data-imgtab="upload">Upload</button>
                                <button type="button" class="ld-tab {{ $imgSource === 'unsplash' ? 'is-active' : '' }}" data-imgtab="unsplash" @disabled(!$unsplashEnabled)>Search Unsplash{{ $unsplashEnabled ? '' : ' (off)' }}</button>
                            </div>

                            <div class="ld-tabpane {{ $imgSource !== 'unsplash' ? 'is-active' : '' }}" data-imgpane="upload">
                                <input type="file" name="image" id="i-image" class="form-control" accept="image/*">
                                <div class="form-text">PNG/JPG, up to 5&nbsp;MB. Stored on Livelatch S3.</div>
                            </div>

                            <div class="ld-tabpane {{ $imgSource === 'unsplash' ? 'is-active' : '' }}" data-imgpane="unsplash">
                                @if($unsplashEnabled)
                                    <div class="input-group">
                                        <input type="text" id="i-unsplash-q" class="form-control" placeholder="Search photos…" autocomplete="off">
                                        <button type="button" class="btn btn-outline-secondary" id="i-unsplash-go">Search</button>
                                    </div>
                                    <div class="ld-unsplash-grid" id="i-unsplash-grid"></div>
                                    <div class="form-text">Photographer credit is overlaid on the card and saved with it (Unsplash ToS).</div>
                                @else
                                    <div class="text-muted small">Unsplash search isn't configured. Set <code>UNSPLASH_ACCESS_KEY</code> to enable it.</div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-save me-1"></i> {{ $editing ? 'Save changes' : 'Save draft' }}</button>
                            <a href="{{ route('studio.latchdeck') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    var card = document.getElementById('lc-card');
    var unsplashUrl = @json(route('studio.latchdeck.unsplash'));

    function bindText(inputId, targetId, transform, fallback) {
        var input = document.getElementById(inputId), target = document.getElementById(targetId);
        if (!input || !target) return;
        input.addEventListener('input', function () {
            var val = input.value;
            target.textContent = (val && val.length) ? (transform ? transform(val) : val) : fallback;
        });
    }

    bindText('i-name', 'lc-headline', null, 'HEADLINE TEXT');
    bindText('i-body', 'lc-body', null, 'This is where you write up what the card commemorates. It is stored as data and drawn on screen — no image of the card is saved.');
    bindText('i-rarity', 'lc-rarity', function (v) { return v.toUpperCase(); }, 'RARE');

    // Rarity is a <select>, so listen on change too.
    var rarity = document.getElementById('i-rarity');
    if (rarity) rarity.addEventListener('change', function () { document.getElementById('lc-rarity').textContent = rarity.value.toUpperCase(); });

    var bg = document.getElementById('i-bg');
    if (bg) bg.addEventListener('input', function () { card.style.setProperty('--lc-bg', bg.value); });
    var txt = document.getElementById('i-txt');
    if (txt) txt.addEventListener('input', function () { card.style.setProperty('--lc-txt', txt.value); });

    var effect = document.getElementById('i-effect');
    if (effect) effect.addEventListener('change', function () {
        card.className = 'lc-card lc-effect-' + effect.value;
    });

    function updateFooter() {
        var redeem = (document.getElementById('i-redeem').value || '').toUpperCase();
        var supply = document.getElementById('i-supply').value;
        document.getElementById('lc-footer-left').textContent = redeem || 'PREVIEW';
        document.getElementById('lc-footer-right').textContent = '#0001' + (supply ? ' / ' + supply : '');
    }
    ['i-redeem', 'i-supply'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', updateFooter);
    });

    // --- Image tabs ---
    var artBox = document.getElementById('lc-art'), artImg = document.getElementById('lc-art-img');
    var credit = document.getElementById('lc-credit');
    var fSource = document.getElementById('f-image-source');

    document.querySelectorAll('[data-imgtab]').forEach(function (btn) {
        if (btn.disabled) return;
        btn.addEventListener('click', function () {
            var tab = btn.getAttribute('data-imgtab');
            document.querySelectorAll('[data-imgtab]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            document.querySelectorAll('[data-imgpane]').forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-imgpane') === tab); });
        });
    });

    function setArt(url) {
        if (url) { artImg.src = url; artBox.classList.add('has-img'); }
        else { artImg.removeAttribute('src'); artBox.classList.remove('has-img'); }
    }
    function clearUnsplash() {
        ['f-unsplash-url', 'f-unsplash-photographer', 'f-unsplash-photographer-url', 'f-unsplash-photo-url', 'f-unsplash-download'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        credit.hidden = true;
    }

    // Upload preview
    var fileInput = document.getElementById('i-image');
    if (fileInput) fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files[0]) {
            fSource.value = 'upload';
            clearUnsplash();
            setArt(URL.createObjectURL(fileInput.files[0]));
        }
    });

    // Unsplash search
    var grid = document.getElementById('i-unsplash-grid');
    var goBtn = document.getElementById('i-unsplash-go');
    var qInput = document.getElementById('i-unsplash-q');

    function runSearch() {
        if (!qInput || !qInput.value.trim()) return;
        grid.innerHTML = '<div class="text-muted small p-2">Searching…</div>';
        fetch(unsplashUrl + '?q=' + encodeURIComponent(qInput.value.trim()), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                grid.innerHTML = '';
                var results = (data && data.results) || [];
                if (!results.length) { grid.innerHTML = '<div class="text-muted small p-2">No photos found.</div>'; return; }
                results.forEach(function (p) {
                    var item = document.createElement('div');
                    item.className = 'ld-unsplash-item';
                    var img = document.createElement('img');
                    img.src = p.thumb || p.small; img.alt = p.alt || '';
                    var cred = document.createElement('div');
                    cred.className = 'ld-unsplash-cred';
                    cred.textContent = p.photographer || '';
                    item.appendChild(img); item.appendChild(cred);
                    item.addEventListener('click', function () { pickUnsplash(p, item); });
                    grid.appendChild(item);
                });
            })
            .catch(function () { grid.innerHTML = '<div class="text-danger small p-2">Search failed.</div>'; });
    }
    if (goBtn) goBtn.addEventListener('click', runSearch);
    if (qInput) qInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); runSearch(); } });

    function pickUnsplash(p, item) {
        if (fileInput) fileInput.value = '';
        fSource.value = 'unsplash';
        document.getElementById('f-unsplash-url').value = p.regular || p.small || '';
        document.getElementById('f-unsplash-photographer').value = p.photographer || '';
        document.getElementById('f-unsplash-photographer-url').value = p.photographer_url || '';
        document.getElementById('f-unsplash-photo-url').value = p.photo_url || '';
        document.getElementById('f-unsplash-download').value = p.download_location || '';

        setArt(p.small || p.regular);
        // Overlay credit on the card
        var ph = document.getElementById('lc-credit-photographer');
        ph.textContent = p.photographer || '';
        ph.href = p.photographer_url || '#';
        credit.hidden = false;

        grid.querySelectorAll('.ld-unsplash-item').forEach(function (el) { el.classList.toggle('is-active', el === item); });
    }
})();
</script>
@endsection
