@php
    $ldkOwnerId = (int) ($link->user_id ?? 0);
    $ldkLatchId = $ldkOwnerId ? \App\Models\User::where('id', $ldkOwnerId)->value('supabase_user_id') : null;
    $ldkCards = $ldkLatchId ? app(\App\Services\LatchDeckService::class)->publishedCards($ldkLatchId) : [];
    $ldkViewer = rtrim((string) config('services.latchdeck.viewer_url', ''), '/');
    $ldkSpeedKey = in_array(($link->speed ?? 'slow'), ['slow', 'medium', 'fast'], true) ? $link->speed : 'slow';
    $ldkSpeedPx = ['slow' => 14, 'medium' => 26, 'fast' => 44][$ldkSpeedKey];
@endphp

@once
<style>
    .ll-ldk {
        --ll-ldk-line: color-mix(in srgb, currentColor 16%, transparent);
        --ll-ldk-fill: color-mix(in srgb, currentColor 6%, transparent);
        width: 100%; margin: 6px 0; padding: 18px;
        border: 1px solid var(--ll-ldk-line); border-radius: 18px;
        background: var(--ll-ldk-fill); text-align: center;
    }
    .ll-ldk-head { display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 14px; }
    .ll-ldk-badge { font-size: .62rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; background: color-mix(in srgb, currentColor 12%, transparent); opacity: .85; }
    .ll-ldk-title { margin: 0; font-size: 1.15rem; font-weight: 800; }

    /* Auto-scrolling marquee of image-only tiles. The track is taken OUT OF FLOW
       (absolute) so it can never widen the theme's column — the marquee viewport
       is overflow:hidden and clips/loops the cards instead. */
    .ll-ldk-marquee { position: relative; width: 100%; overflow: hidden; min-height: 236px; }
    .ll-ldk-track2 { position: absolute; top: 0; left: 0; display: flex; gap: 12px; padding-top: 4px; will-change: transform; }
    .ll-ldk-track2.is-centered { left: 50%; transform: translateX(-50%); }
    .ll-ldk-track2.is-scrolling { animation: ll-ldk-marq var(--ldk-dur, 40s) linear infinite; }
    .ll-ldk-marquee:hover .ll-ldk-track2.is-scrolling,
    .ll-ldk-marquee:focus-within .ll-ldk-track2.is-scrolling { animation-play-state: paused; }
    .ll-ldk-marquee.is-manual { overflow-x: auto; scrollbar-width: none; }
    .ll-ldk-marquee.is-manual::-webkit-scrollbar { display: none; }
    .ll-ldk-marquee.is-manual .ll-ldk-track2 { position: static; }
    @keyframes ll-ldk-marq { from { transform: translateX(0); } to { transform: translateX(calc(-1 * var(--ldk-shift, 0px))); } }

    .ll-ldk-card2 { flex: 0 0 auto; width: 150px; cursor: pointer; border: 0; padding: 0; background: none; text-align: left; }
    .ll-ldk-surface { position: relative; width: 100%; aspect-ratio: 5 / 7; border-radius: 14px; overflow: hidden; background: var(--cbg, #160000); color: var(--ctxt, #fff); padding: 12px; display: flex; flex-direction: column; justify-content: flex-end; box-shadow: 0 10px 26px rgba(0,0,0,.32); transition: transform .15s ease; background-size: cover; background-position: center; }
    .ll-ldk-card2:hover .ll-ldk-surface, .ll-ldk-card2:focus-visible .ll-ldk-surface { transform: translateY(-3px); }
    .ll-ldk-surface::before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 35%, rgba(0,0,0,.6)); }
    .ll-ldk-c-name { position: relative; font-weight: 800; font-size: .9rem; line-height: 1.1; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,.6); }
    .ll-ldk-c-meta { position: relative; margin-top: 3px; font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #fff; opacity: .85; }
    .ll-ldk-c-rarity { position: absolute; top: 10px; left: 10px; z-index: 2; font-size: .56rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; padding: 3px 8px; border-radius: 999px; background: rgba(0,0,0,.45); color: #fff; border: 1px solid rgba(255,255,255,.35); }
    .ll-ldk-c-cta { margin-top: 8px; font-size: .62rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; opacity: .7; text-align: center; }

    .ll-ldk-empty { margin: 14px 0 0; font-size: .85rem; opacity: .7; }
    .ll-ldk-placeholder { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 0 auto; max-width: 360px; }
    .ll-ldk-ph { aspect-ratio: 5/7; border-radius: 12px; border: 1px solid var(--ll-ldk-line); background: linear-gradient(150deg, color-mix(in srgb, currentColor 14%, transparent), color-mix(in srgb, currentColor 4%, transparent)); }

    /* ---- Reveal modal: flip a card back to show the full V1 card ---- */
    .ll-ldk-modal { position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center; padding: 20px; }
    .ll-ldk-modal.is-open { display: flex; }
    .ll-ldk-modal-bd { position: absolute; inset: 0; background: rgba(6,8,18,.8); backdrop-filter: blur(4px); }
    .ll-ldk-confetti { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 4; }
    .ll-ldk-reveal { position: relative; z-index: 1; width: 100%; max-width: 300px; display: flex; flex-direction: column; align-items: center; gap: 16px; }

    .ll-ldk-flip { width: 100%; aspect-ratio: 5 / 7; perspective: 1400px; }
    .ll-ldk-flip-inner { position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transform: rotateY(0deg); transition: transform .85s cubic-bezier(.2,.75,.2,1); }
    .ll-ldk-reveal.is-revealed .ll-ldk-flip-inner { transform: rotateY(180deg); }
    .ll-ldk-face { position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden; border-radius: 16px; overflow: hidden; box-shadow: 0 30px 70px rgba(0,0,0,.6); }
    .ll-ldk-face-front { transform: rotateY(180deg); }
    #ll-ldk-front { position: absolute; inset: 0; }

    /* Card back */
    .ll-ldk-back-face { display: grid; place-items: center; background: radial-gradient(circle at 50% 32%, #2a2350, #0d0b1a 70%); border: 1px solid rgba(255,255,255,.12); }
    .ll-ldk-back-logo { display: flex; flex-direction: column; align-items: center; gap: 12px; color: #fff; }
    .ll-ldk-back-logo .mark { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #2afadf, #3a7bd5); display: grid; place-items: center; font-size: 1.7rem; color: #06121a; box-shadow: 0 0 34px rgba(42,250,223,.45); }
    .ll-ldk-back-logo .word { font-weight: 800; letter-spacing: .22em; text-transform: uppercase; font-size: .78rem; opacity: .92; }
    .ll-ldk-back-face::after { content: ""; position: absolute; inset: 0; background: linear-gradient(115deg, transparent 40%, rgba(255,255,255,.14) 50%, transparent 60%); background-size: 200% 100%; animation: ll-ldk-sheen 3s ease-in-out infinite; }

    /* One-shot shine on reveal */
    .ll-ldk-shine { position: absolute; inset: 0; pointer-events: none; opacity: 0; background: linear-gradient(115deg, transparent 35%, rgba(255,255,255,.6) 50%, transparent 65%); background-size: 200% 100%; }
    .ll-ldk-reveal.is-revealed .ll-ldk-shine { animation: ll-ldk-reveal-shine 1.1s ease-out .5s 1; }

    /* Full V1 card (mirrors the editor preview), drawn from the card's data */
    .ll-ldk-fc { position: relative; width: 100%; height: 100%; padding: 18px; background: var(--cbg, #160000); color: var(--ctxt, #fff); display: flex; flex-direction: column; font-family: 'Inter', system-ui, sans-serif; }
    .ll-ldk-fc-head { font-size: 1.7rem; font-weight: 800; line-height: 1.02; text-transform: uppercase; letter-spacing: -.5px; word-break: break-word; }
    .ll-ldk-fc-art { margin-top: 12px; height: 33%; border-radius: 8px; overflow: hidden; background: rgba(0,0,0,.3); }
    .ll-ldk-fc-art img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ll-ldk-fc-rarity { align-self: flex-start; margin-top: 14px; font-size: .62rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; padding: 5px 14px; border-radius: 999px; border: 2px solid currentColor; }
    .ll-ldk-fc-div { height: 5px; margin: 14px 0 10px; background: currentColor; opacity: .85; border-radius: 2px; }
    .ll-ldk-fc-body { font-size: .94rem; line-height: 1.3; font-weight: 600; flex: 1 1 auto; overflow: hidden; word-break: break-word; }
    .ll-ldk-fc-footer { display: flex; justify-content: space-between; gap: 10px; font-size: .6rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .ll-ldk-fc-credit { font-size: .5rem; opacity: .8; margin-bottom: 6px; }
    .ll-ldk-fc-credit a { color: inherit; text-decoration: underline; }
    .ll-ldk-fc-fx { position: absolute; inset: 0; pointer-events: none; opacity: 0; background-size: 200% 200%; }
    .ll-ldk-fc.fx-holo .ll-ldk-fc-fx { opacity: .5; mix-blend-mode: color-dodge; filter: blur(2px); background: conic-gradient(from 0deg,#ff0080,#7928ca,#2afadf,#00ff8f,#ffd700,#ff0080); animation: ll-ldk-holo 6s linear infinite; }
    .ll-ldk-fc.fx-shiny .ll-ldk-fc-fx { opacity: .8; mix-blend-mode: screen; background: linear-gradient(115deg,transparent 30%,rgba(255,255,255,.5) 48%,transparent 62%); animation: ll-ldk-shine2 3.2s ease-in-out infinite; }
    .ll-ldk-fc.fx-foil .ll-ldk-fc-fx { opacity: .4; mix-blend-mode: overlay; background: repeating-linear-gradient(135deg,rgba(255,255,255,.18) 0 2px,transparent 2px 7px),linear-gradient(135deg,#c0c0c0,#8a8a8a,#e8e8e8); animation: ll-ldk-holo 5s linear infinite; }

    .ll-ldk-reveal-cta { width: 100%; display: flex; flex-direction: column; gap: 10px; opacity: 0; transform: translateY(8px); transition: opacity .4s ease, transform .4s ease; }
    .ll-ldk-reveal.is-revealed .ll-ldk-reveal-cta { opacity: 1; transform: none; transition-delay: .7s; }
    .ll-ldk-btn { display: block; width: 100%; padding: 12px; border-radius: 12px; font-weight: 800; text-decoration: none; text-align: center; border: 0; cursor: pointer; background: linear-gradient(135deg,#2afadf,#3a7bd5); color: #06121a; }
    .ll-ldk-btn.is-disabled { background: rgba(255,255,255,.14); color: #fff; cursor: default; }
    .ll-ldk-close-x { background: none; border: 0; color: #fff; opacity: .65; cursor: pointer; font-size: .82rem; }

    @keyframes ll-ldk-holo { 0%{background-position:0% 50%} 100%{background-position:200% 50%} }
    @keyframes ll-ldk-shine2 { 0%{background-position:-150% 0} 100%{background-position:150% 0} }
    @keyframes ll-ldk-sheen { 0%{background-position:-150% 0} 60%,100%{background-position:150% 0} }
    @keyframes ll-ldk-reveal-shine { 0%{opacity:0; background-position:-150% 0} 30%{opacity:.9} 100%{opacity:0; background-position:150% 0} }
    @media (prefers-reduced-motion: reduce) {
        .ll-ldk-flip-inner { transition: none; }
        .ll-ldk-shine, .ll-ldk-back-face::after, .ll-ldk-track2.is-scrolling { animation: none; }
    }
</style>

<div class="ll-ldk-modal" id="ll-ldk-modal" aria-hidden="true">
    <div class="ll-ldk-modal-bd" data-ldk-close></div>
    <canvas class="ll-ldk-confetti" id="ll-ldk-confetti" aria-hidden="true"></canvas>
    <div class="ll-ldk-reveal" id="ll-ldk-reveal">
        <div class="ll-ldk-flip">
            <div class="ll-ldk-flip-inner">
                <div class="ll-ldk-face ll-ldk-back-face">
                    <div class="ll-ldk-back-logo">
                        <span class="mark"><i class="bi bi-collection-fill"></i></span>
                        <span class="word">LatchDeck</span>
                    </div>
                </div>
                <div class="ll-ldk-face ll-ldk-face-front">
                    <div id="ll-ldk-front" role="dialog" aria-modal="true" aria-label="Card"></div>
                    <div class="ll-ldk-shine" aria-hidden="true"></div>
                </div>
            </div>
        </div>
        <div class="ll-ldk-reveal-cta">
            <a class="ll-ldk-btn" id="ll-ldk-go" href="#">Log in to redeem</a>
            <button type="button" class="ll-ldk-close-x" data-ldk-close>Close</button>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('ll-ldk-modal');
    if (!modal) return;
    // Portal to <body> so a transformed/filtered theme ancestor can't trap our
    // position:fixed overlay (otherwise the backdrop won't cover the viewport).
    if (modal.parentNode !== document.body) document.body.appendChild(modal);

    var reveal = document.getElementById('ll-ldk-reveal');
    var front = document.getElementById('ll-ldk-front');
    var go = document.getElementById('ll-ldk-go');
    var canvas = document.getElementById('ll-ldk-confetti');

    // --- Lightweight confetti burst (no library); fades out then clears. ---
    var confettiRAF = null;
    function confetti() {
        if (!canvas) return;
        if (confettiRAF) cancelAnimationFrame(confettiRAF);
        var ctx = canvas.getContext('2d');
        var W = canvas.width = canvas.clientWidth, H = canvas.height = canvas.clientHeight;
        var colors = ['#2afadf', '#3a7bd5', '#ffd700', '#ff3b30', '#7928ca', '#00ff8f', '#ffffff'];
        var parts = [];
        for (var i = 0; i < 130; i++) {
            parts.push({
                x: Math.random() * W, y: -20 - Math.random() * H * 0.4,
                vx: (Math.random() - 0.5) * 3, vy: 2 + Math.random() * 3.5,
                size: 5 + Math.random() * 6, color: colors[(Math.random() * colors.length) | 0],
                rot: Math.random() * Math.PI, vr: (Math.random() - 0.5) * 0.35
            });
        }
        var start = performance.now(), DUR = 2800;
        function frame(now) {
            var t = now - start;
            ctx.clearRect(0, 0, W, H);
            var fade = t > DUR * 0.55 ? Math.max(0, 1 - (t - DUR * 0.55) / (DUR * 0.45)) : 1;
            var alive = false;
            for (var j = 0; j < parts.length; j++) {
                var p = parts[j];
                p.x += p.vx; p.y += p.vy; p.vy += 0.05; p.rot += p.vr;
                if (p.y < H + 20 && fade > 0) alive = true;
                ctx.save();
                ctx.globalAlpha = fade;
                ctx.translate(p.x, p.y); ctx.rotate(p.rot);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                ctx.restore();
            }
            if (t < DUR && alive) { confettiRAF = requestAnimationFrame(frame); }
            else { ctx.clearRect(0, 0, W, H); confettiRAF = null; }
        }
        confettiRAF = requestAnimationFrame(frame);
    }

    function open(card) {
        var tpl = card.querySelector('.ll-ldk-fulltpl');
        front.innerHTML = '';
        if (tpl && tpl.content) front.appendChild(tpl.content.cloneNode(true));

        var url = card.getAttribute('data-redeem') || '';
        if (url) {
            go.textContent = 'Log in to redeem';
            go.setAttribute('href', url);
            go.classList.remove('is-disabled');
            go.removeAttribute('aria-disabled');
        } else {
            go.textContent = 'Redemption coming soon';
            go.setAttribute('href', '#');
            go.classList.add('is-disabled');
            go.setAttribute('aria-disabled', 'true');
        }

        reveal.classList.remove('is-revealed');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        // Beat on the card back, then flip to reveal the front + celebrate.
        requestAnimationFrame(function () {
            setTimeout(function () { reveal.classList.add('is-revealed'); confetti(); }, 300);
        });
    }
    function close() {
        modal.classList.remove('is-open');
        reveal.classList.remove('is-revealed');
        modal.setAttribute('aria-hidden', 'true');
        if (confettiRAF) { cancelAnimationFrame(confettiRAF); confettiRAF = null; }
        if (canvas) { var c = canvas.getContext('2d'); c && c.clearRect(0, 0, canvas.width, canvas.height); }
    }

    document.addEventListener('click', function (e) {
        var card = e.target.closest('[data-ldk-card]');
        if (card) { e.preventDefault(); open(card); return; }
        if (e.target.closest('[data-ldk-close]')) { close(); }
    });
    go.addEventListener('click', function (e) { if (go.classList.contains('is-disabled')) e.preventDefault(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    // Marquee: track is absolute (out of flow) so it never widens the column.
    // If the cards overflow the available width, duplicate + auto-scroll; else center.
    function setupMarquee(m) {
        var track = m.querySelector('[data-ldk-track]');
        if (!track || !track.children.length) return;

        var viewportW = m.clientWidth;
        var trackW = track.scrollWidth;             // width of one copy of the cards
        var firstH = track.children[0].offsetHeight;
        if (firstH) m.style.height = firstH + 'px';  // fit the row (track is absolute)

        var GAP = 12;
        if (trackW <= viewportW) {                   // everything fits → center, no scroll
            track.classList.add('is-centered');
            return;
        }

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Reduced motion: lock width (so it can't widen the page) + manual scroll.
            m.style.width = viewportW + 'px';
            m.style.height = '';
            m.classList.add('is-manual');
            return;
        }

        // Overflowing → duplicate the cards once for a seamless loop, then animate.
        Array.prototype.slice.call(track.children).forEach(function (c) {
            var clone = c.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            track.appendChild(clone);
        });
        var shift = trackW + GAP;                     // one copy width + the gap before the duplicate
        var pxPerSec = parseFloat(m.getAttribute('data-speed')) || 14;
        track.style.setProperty('--ldk-shift', shift + 'px');
        track.style.setProperty('--ldk-dur', (shift / pxPerSec) + 's');
        track.classList.add('is-scrolling');
    }
    document.querySelectorAll('[data-ldk-marquee]').forEach(setupMarquee);
})();
</script>
@endonce

<div class="ll-ldk">
    <div class="ll-ldk-head">
        <span class="ll-ldk-badge">LatchDeck</span>
        <h3 class="ll-ldk-title">{{ $link->title ?: 'My Cards' }}</h3>
    </div>

    @if(empty($ldkCards))
        <div class="ll-ldk-placeholder" aria-hidden="true">
            <div class="ll-ldk-ph"></div><div class="ll-ldk-ph"></div><div class="ll-ldk-ph"></div>
        </div>
        <p class="ll-ldk-empty">Collectible cards are on the way — check back soon.</p>
    @else
        <div class="ll-ldk-marquee" data-ldk-marquee data-speed="{{ $ldkSpeedPx }}">
            <div class="ll-ldk-track2" data-ldk-track>
                @foreach($ldkCards as $c)
                    @php
                        $cBg = $c['background_color_mvp'] ?? '#160000';
                        $cTxt = $c['text_color_mvp'] ?? '#ffffff';
                        $cImg = (!empty($c['image_url_mvp']) && preg_match('#^https://#', (string) $c['image_url_mvp'])) ? $c['image_url_mvp'] : null;
                        $cEffect = in_array(($c['effect_mvp'] ?? 'none'), ['holo','shiny','foil'], true) ? $c['effect_mvp'] : null;
                        $cCode = $c['redeem_code_mvp'] ?? null;
                        $cSupply = $c['supply_cap_mvp'] ?? null;
                        $cBody = $c['long_description_mvp'] ?: ($c['short_description_mvp'] ?? '');
                        $cCredit = $c['image_credit_mvp'] ?? null;
                        $cName = $c['name_mvp'] ?? 'Card';
                        $cRarity = ucfirst($c['rarity_mvp'] ?? 'Common');
                        $cPath = $cCode ? ('/redeem/' . rawurlencode($cCode)) : ('/card/' . rawurlencode($c['id'] ?? ''));
                        $cRedeem = $ldkViewer !== '' ? ($ldkViewer . $cPath) : '';
                        $cTileStyle = "--cbg: {$cBg}; --ctxt: {$cTxt};" . ($cImg ? " background-image: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.35)), url('{$cImg}');" : '');
                    @endphp
                    <button type="button" class="ll-ldk-card2" data-ldk-card data-name="{{ strip_tags($cName) }}" data-redeem="{{ $cRedeem }}">
                        <div class="ll-ldk-surface" style="{{ $cTileStyle }}">
                            <span class="ll-ldk-c-rarity">{{ $cRarity }}</span>
                            <div class="ll-ldk-c-name">{{ $cName }}</div>
                            <div class="ll-ldk-c-meta">@if($cSupply) Limited · {{ $cSupply }} @else Collectible @endif</div>
                        </div>
                        <div class="ll-ldk-c-cta">Tap to reveal</div>

                        {{-- Full V1 card, cloned into the reveal modal on tap --}}
                        <template class="ll-ldk-fulltpl">
                            <div class="ll-ldk-fc {{ $cEffect ? 'fx-' . $cEffect : '' }}" style="--cbg: {{ $cBg }}; --ctxt: {{ $cTxt }};">
                                <div class="ll-ldk-fc-head">{{ $cName }}</div>
                                <div class="ll-ldk-fc-art">@if($cImg)<img src="{{ $cImg }}" alt="">@endif</div>
                                <span class="ll-ldk-fc-rarity">{{ $cRarity }}</span>
                                <div class="ll-ldk-fc-div"></div>
                                <div class="ll-ldk-fc-body">{{ $cBody }}</div>
                                @if($cCredit && ($cCredit['source'] ?? '') === 'unsplash')
                                    <div class="ll-ldk-fc-credit">Photo by <a href="{{ $cCredit['photographer_url'] ?? '#' }}" target="_blank" rel="noopener">{{ $cCredit['photographer'] ?? '' }}</a> on <a href="{{ $cCredit['photo_url'] ?? 'https://unsplash.com' }}" target="_blank" rel="noopener">Unsplash</a></div>
                                @endif
                                <div class="ll-ldk-fc-footer">
                                    <span>{{ $cCode ? strtoupper($cCode) : 'LatchDeck' }}</span>
                                    <span>#0001{{ $cSupply ? ' / ' . $cSupply : '' }}</span>
                                </div>
                                <div class="ll-ldk-fc-fx" aria-hidden="true"></div>
                            </div>
                        </template>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
