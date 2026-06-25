@once
<style>
    .ll-ldk {
        --ll-ldk-line: color-mix(in srgb, currentColor 16%, transparent);
        --ll-ldk-fill: color-mix(in srgb, currentColor 6%, transparent);
        width: 100%;
        margin: 6px 0;
        padding: 18px;
        border: 1px solid var(--ll-ldk-line);
        border-radius: 18px;
        background: var(--ll-ldk-fill);
        text-align: center;
    }
    .ll-ldk-head { display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 14px; }
    .ll-ldk-badge {
        font-size: .62rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase;
        padding: 4px 10px; border-radius: 999px;
        background: color-mix(in srgb, currentColor 12%, transparent);
        opacity: .85;
    }
    .ll-ldk-title { margin: 0; font-size: 1.15rem; font-weight: 800; }
    .ll-ldk-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
        margin: 0 auto; max-width: 360px;
    }
    .ll-ldk-card {
        position: relative; aspect-ratio: 3 / 4; border-radius: 12px; overflow: hidden;
        border: 1px solid var(--ll-ldk-line);
        background:
            linear-gradient(150deg, color-mix(in srgb, currentColor 14%, transparent), color-mix(in srgb, currentColor 4%, transparent));
    }
    .ll-ldk-card::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(115deg, transparent 30%, color-mix(in srgb, currentColor 18%, transparent) 50%, transparent 70%);
        transform: translateX(-100%);
        animation: ll-ldk-shine 2.6s ease-in-out infinite;
    }
    .ll-ldk-card:nth-child(2)::after { animation-delay: .35s; }
    .ll-ldk-card:nth-child(3)::after { animation-delay: .7s; }
    .ll-ldk-empty { margin: 14px 0 0; font-size: .85rem; opacity: .7; }
    @keyframes ll-ldk-shine { 0% { transform: translateX(-100%); } 60%, 100% { transform: translateX(100%); } }
    @media (prefers-reduced-motion: reduce) { .ll-ldk-card::after { animation: none; } }
</style>
@endonce

<div class="ll-ldk">
    <div class="ll-ldk-head">
        <span class="ll-ldk-badge">LatchDeck</span>
        <h3 class="ll-ldk-title">{{ $link->title ?: 'My Cards' }}</h3>
    </div>
    <div class="ll-ldk-grid" aria-hidden="true">
        <div class="ll-ldk-card"></div>
        <div class="ll-ldk-card"></div>
        <div class="ll-ldk-card"></div>
    </div>
    <p class="ll-ldk-empty">Collectible cards are on the way — check back soon.</p>
</div>
