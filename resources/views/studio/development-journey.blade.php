@extends('layouts.sidebar')

@section('content')
@php
    $timeline = [
        [
            'year' => 'Fork foundation',
            'date' => '2026-05',
            'title' => 'The fork stops being stock LinkStack',
            'summary' => 'Early work stabilised the fork, repaired the runtime, and set up the app as a Livelatch platform rather than a simple link-in-bio site.',
            'tags' => ['Runtime', 'Brand', 'Deployment'],
            'accent' => 'var(--ll-primary)',
            'category' => 'foundation',
        ],
        [
            'year' => 'Identity layer',
            'date' => '2026-05 to 2026-06',
            'title' => 'Supabase became the identity backbone',
            'summary' => 'LatchID, session bridging, social account linking, and provider flows made Supabase the source of truth for user identity.',
            'tags' => ['Supabase', 'Auth', 'Accounts'],
            'accent' => 'var(--ll-primary-2)',
            'category' => 'identity',
        ],
        [
            'year' => 'Studio growth',
            'date' => '2026-06',
            'title' => 'Studio moved toward a product suite',
            'summary' => 'Navigation, skeleton loaders, theme handling, dashboards, and placeholder program pages turned the shell into a working control surface.',
            'tags' => ['Studio', 'Navigation', 'UX'],
            'accent' => '#8b5cf6',
            'category' => 'studio',
        ],
        [
            'year' => 'Monetization',
            'date' => '2026-06',
            'title' => 'Billing and future revenue paths hardened',
            'summary' => 'Stripe billing, subscription scaffolding, and affiliate planning established the commercial shape of the platform.',
            'tags' => ['Stripe', 'Affiliate', 'Revenue'],
            'accent' => '#0ea5e9',
            'category' => 'revenue',
        ],
        [
            'year' => 'Creator tooling',
            'date' => '2026-06',
            'title' => 'YouTube, Discord, TikTok, and social surfaces filled out',
            'summary' => 'Creator-facing provider work expanded the platform into a broader social identity and connection system.',
            'tags' => ['YouTube', 'Discord', 'TikTok'],
            'accent' => '#14b8a6',
            'category' => 'studio',
        ],
        [
            'year' => 'Theme engine',
            'date' => '2026-06',
            'title' => 'A second, blade-based theme engine arrived',
            'summary' => 'Alongside the classic CSS-variable themes, a parallel Blade theme system let profiles become full-page, animated experiences (WebGL / Canvas), edited through a manifest-driven Theme Studio (Beta).',
            'tags' => ['Themes', 'WebGL', 'Studio'],
            'accent' => '#f59e0b',
            'category' => 'studio',
        ],
        [
            'year' => 'Theme gallery',
            'date' => '2026-06',
            'title' => 'An immersive theme gallery shipped',
            'summary' => 'A run of immersive base themes landed — Frutiger Aero, a Minecraft elytra flight, Vice City, a Matrix console, a Stark-style HUD and more — each fully editable in the Studio.',
            'tags' => ['Themes', 'Canvas', 'Creator'],
            'accent' => '#ec4899',
            'category' => 'studio',
        ],
        [
            'year' => 'Platform API',
            'date' => '2026-06',
            'title' => 'Encore became the LatchDeck authority',
            'summary' => 'LatchDeck moved onto its own platform-agnostic API: Encore owns access, tiers, and card publishing, writing to Supabase as the single authority — Livelatch and LatchOps are just keyed clients.',
            'tags' => ['Encore', 'LatchDeck', 'API'],
            'accent' => '#6366f1',
            'category' => 'fanservice',
        ],
        [
            'year' => 'Fan layer',
            'date' => '2026-07',
            'title' => 'The fan side of Livelatch took shape',
            'summary' => 'fan.livelatch.com arrived as a working mockup: redeem a creator drop code, mint a serialised card, and show it off in a public cabinet — with one canonical LatchID sign-in modal shared by every Livelatch property.',
            'tags' => ['LatchDeck', 'Fans', 'LatchID'],
            'accent' => '#f97316',
            'category' => 'fanservice',
        ],
        [
            'year' => 'Studio speed',
            'date' => '2026-07',
            'title' => 'Navigation got out of its own way',
            'summary' => 'The Studio dropped its skeleton loaders for View Transition cross-fades and a quiet top progress bar — fast swaps now show no loading chrome at all.',
            'tags' => ['HTMX', 'UX', 'Performance'],
            'accent' => '#22d3ee',
            'category' => 'studio',
        ],
    ];

    $techStack = [
        ['name' => 'Laravel', 'detail' => 'Application orchestration, routes, controllers, views, and local state.', 'icon' => 'bi-braces'],
        ['name' => 'Supabase', 'detail' => 'Auth, identity linking, Edge Functions, and Postgres-backed account data.', 'icon' => 'bi-database-fill'],
        ['name' => 'Stripe', 'detail' => 'Billing, subscriptions, checkout, and portal flows.', 'icon' => 'bi-credit-card-2-front-fill'],
        ['name' => 'HTMX', 'detail' => 'Fast Studio navigation and partial page swaps without full reloads.', 'icon' => 'bi-lightning-fill'],
        ['name' => 'Livewire', 'detail' => 'Interactive navigation structure and dynamic admin surfaces.', 'icon' => 'bi-broadcast'],
        ['name' => 'Three.js', 'detail' => 'WebGL scenes powering immersive blade themes like Portal and Minecraft.', 'icon' => 'bi-badge-3d-fill'],
        ['name' => 'PostgreSQL', 'detail' => 'Primary source of truth for users, plans, and platform data.', 'icon' => 'bi-hdd-network-fill'],
        ['name' => 'Encore', 'detail' => 'The LatchDeck API and single authority for cards, tiers, and redemptions.', 'icon' => 'bi-cpu-fill'],
        ['name' => 'Next.js', 'detail' => 'The fan-facing client for card redemption and public collection cabinets.', 'icon' => 'bi-window-stack'],
    ];
@endphp

<style data-ll-dev-timeline-style>
    .ll-dev-timeline {
        display: grid;
        gap: 18px;
    }

    .ll-dev-timeline-hero,
    .ll-dev-timeline-panel {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
    }

    .ll-dev-timeline-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(22px, 3.8vw, 38px);
        background:
            radial-gradient(circle at 15% 10%, color-mix(in srgb, var(--ll-primary) 18%, transparent), transparent 28%),
            radial-gradient(circle at 88% 20%, color-mix(in srgb, var(--ll-primary-2) 16%, transparent), transparent 24%),
            linear-gradient(135deg, color-mix(in srgb, var(--ll-bg-soft) 72%, white), var(--ll-surface-solid));
    }

    .ll-dev-timeline-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(250px, 0.8fr);
        gap: 18px;
        align-items: end;
    }

    .ll-dev-timeline-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 7px 12px;
        border-radius: 999px;
        color: var(--ll-text);
        background: color-mix(in srgb, var(--ll-text) 7%, transparent);
        font-size: 0.8rem;
        font-weight: 800;
    }

    .ll-dev-timeline-hero h2 {
        margin: 14px 0 10px;
        font-size: clamp(2rem, 3.8vw, 3.7rem);
        line-height: 0.98;
    }

    .ll-dev-timeline-hero p {
        margin: 0;
        color: var(--ll-muted);
        max-width: 840px;
    }

    .ll-dev-timeline-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ll-dev-timeline-metric {
        padding: 14px;
        border-radius: 18px;
        background: color-mix(in srgb, var(--ll-surface-solid) 82%, var(--ll-bg-soft));
        border: 1px solid var(--ll-border);
    }

    .ll-dev-timeline-metric strong {
        display: block;
        font-size: 1.4rem;
        line-height: 1;
        margin-bottom: 6px;
    }

    .ll-dev-timeline-metric span {
        color: var(--ll-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .ll-dev-timeline-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .ll-dev-timeline-chip {
        border: 1px solid var(--ll-border);
        border-radius: 999px;
        padding: 8px 12px;
        background: var(--ll-surface-solid);
        color: var(--ll-muted);
        font-size: 0.84rem;
        font-weight: 800;
    }

    .ll-dev-timeline-chip.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }

    .ll-dev-timeline-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
        gap: 18px;
        align-items: start;
    }

    .ll-dev-timeline-list {
        display: grid;
        gap: 12px;
    }

    .ll-dev-timeline-item {
        position: relative;
        display: grid;
        gap: 8px;
        text-align: left;
        width: 100%;
        padding: 16px 16px 16px 18px;
        border: 1px solid var(--ll-border);
        border-radius: 22px;
        background: var(--ll-surface-solid);
        color: var(--ll-text);
        overflow: hidden;
        transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
    }

    .ll-dev-timeline-item::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--ll-timeline-accent, var(--ll-primary));
    }

    .ll-dev-timeline-item:hover,
    .ll-dev-timeline-item.is-active {
        transform: translateY(-2px);
        border-color: color-mix(in srgb, var(--ll-primary) 35%, var(--ll-border));
        box-shadow: 0 18px 40px rgba(30, 16, 80, 0.10);
    }

    .ll-dev-timeline-item-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
    }

    .ll-dev-timeline-item small {
        color: var(--ll-muted);
        font-weight: 800;
    }

    .ll-dev-timeline-item h3 {
        margin: 0;
        font-size: 1.02rem;
    }

    .ll-dev-timeline-item p {
        margin: 0;
        color: var(--ll-muted);
        font-size: 0.9rem;
    }

    .ll-dev-timeline-tagrow {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ll-dev-timeline-tag {
        padding: 5px 9px;
        border-radius: 999px;
        color: var(--ll-text);
        background: color-mix(in srgb, var(--ll-text) 7%, transparent);
        font-size: 0.72rem;
        font-weight: 800;
    }

    .ll-dev-timeline-detail {
        position: sticky;
        top: 18px;
        display: grid;
        gap: 16px;
    }

    .ll-dev-timeline-detail-card {
        border-radius: var(--ll-radius);
        border: 1px solid var(--ll-border);
        background: linear-gradient(180deg, color-mix(in srgb, var(--ll-bg-soft) 45%, transparent), var(--ll-surface-solid));
        box-shadow: var(--ll-shadow-soft);
        padding: clamp(18px, 3vw, 28px);
    }

    .ll-dev-timeline-detail-card h3 {
        margin: 8px 0 10px;
        font-size: 1.75rem;
    }

    .ll-dev-timeline-detail-card p {
        color: var(--ll-muted);
        margin: 0;
    }

    .ll-dev-timeline-stack {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .ll-dev-timeline-tech {
        padding: 16px;
        border-radius: 20px;
        border: 1px solid var(--ll-border);
        background: var(--ll-surface-solid);
        min-height: 132px;
    }

    .ll-dev-timeline-tech i {
        display: inline-grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        margin-bottom: 12px;
    }

    .ll-dev-timeline-tech h4 {
        margin: 0 0 6px;
        font-size: 1rem;
    }

    .ll-dev-timeline-tech p {
        margin: 0;
        color: var(--ll-muted);
        font-size: 0.86rem;
    }

    .ll-dev-timeline-footer {
        border: 1px dashed color-mix(in srgb, var(--ll-primary) 36%, var(--ll-border));
        border-radius: var(--ll-radius);
        padding: 18px;
        color: var(--ll-muted);
        background: color-mix(in srgb, var(--ll-primary) 7%, transparent);
    }

    .ll-dev-timeline-enter {
        opacity: 0;
        transform: translateY(10px);
    }

    .ll-dev-timeline-enter.is-visible {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    @media (max-width: 991.98px) {
        .ll-dev-timeline-hero-grid,
        .ll-dev-timeline-grid {
            grid-template-columns: 1fr;
        }

        .ll-dev-timeline-detail {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .ll-dev-timeline-metrics,
        .ll-dev-timeline-stack {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-dev-timeline">
        <section class="ll-dev-timeline-hero ll-dev-timeline-enter">
            <div class="ll-dev-timeline-hero-grid">
                <div>
                    <span class="ll-dev-timeline-eyebrow">
                        <i class="bi bi-hourglass-split"></i>
                        Development Journey
                    </span>
                    <h2>Livelatch history, technologies, and the path that shaped the platform.</h2>
                    <p>
                        This page is meant to be used when explaining the product to other people.
                        It compresses the major fork milestones into a browsable journey and keeps the core stack visible alongside it.
                    </p>
                </div>

                <div class="ll-dev-timeline-metrics">
                    <div class="ll-dev-timeline-metric">
                        <strong>{{ count($timeline) }}</strong>
                        <span>major milestones</span>
                    </div>
                    <div class="ll-dev-timeline-metric">
                        <strong>{{ count($techStack) }}</strong>
                        <span>core technologies</span>
                    </div>
                    <div class="ll-dev-timeline-metric">
                        <strong>1</strong>
                        <span>story to present</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="ll-dev-timeline-panel ll-dev-timeline-enter">
            <div class="p-3 p-md-4 border-bottom" style="border-color: var(--ll-border) !important;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h3 class="mb-1">Interactive milestones</h3>
                        <p class="mb-0 text-muted">Click any milestone to move the spotlight.</p>
                    </div>
                    <div class="ll-dev-timeline-toolbar" aria-label="Timeline filters">
                        <button type="button" class="ll-dev-timeline-chip is-active" data-filter="all">All</button>
                        <button type="button" class="ll-dev-timeline-chip" data-filter="identity">Identity</button>
                        <button type="button" class="ll-dev-timeline-chip" data-filter="studio">Studio</button>
                        <button type="button" class="ll-dev-timeline-chip" data-filter="revenue">Revenue</button>
                        <button type="button" class="ll-dev-timeline-chip" data-filter="fanservice">Fan layer</button>
                    </div>
                </div>
            </div>
            <div class="p-3 p-md-4">
                <div class="ll-dev-timeline-grid">
                    <div class="ll-dev-timeline-list" id="ll-dev-timeline-list">
                        @foreach($timeline as $index => $item)
                            <button
                                type="button"
                                class="ll-dev-timeline-item ll-dev-timeline-enter @if($index === 0) is-active @endif"
                                data-timeline-item
                                data-category="{{ $item['category'] ?? 'foundation' }}"
                                data-title="{{ $item['title'] }}"
                                data-date="{{ $item['date'] }}"
                                data-summary="{{ $item['summary'] }}"
                                data-tags="{{ implode('|', $item['tags']) }}"
                                data-accent="{{ $item['accent'] }}"
                                @if($index === 0) data-active="true" @endif
                                style="--ll-timeline-accent: {{ $item['accent'] }};"
                            >
                                <div class="ll-dev-timeline-item-header">
                                    <small>{{ $item['date'] }}</small>
                                    <span class="badge text-bg-light">0{{ $index + 1 }}</span>
                                </div>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['summary'] }}</p>
                                <div class="ll-dev-timeline-tagrow">
                                    @foreach($item['tags'] as $tag)
                                        <span class="ll-dev-timeline-tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="ll-dev-timeline-detail">
                        <div class="ll-dev-timeline-detail-card" id="ll-dev-timeline-detail">
                            <span class="ll-dev-timeline-eyebrow" id="ll-dev-timeline-date">
                                <i class="bi bi-calendar3"></i>
                                {{ $timeline[0]['date'] }}
                            </span>
                            <h3 id="ll-dev-timeline-title">{{ $timeline[0]['title'] }}</h3>
                            <p id="ll-dev-timeline-summary">{{ $timeline[0]['summary'] }}</p>
                        </div>

                        <div class="ll-dev-timeline-panel p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <h3 class="mb-1">Core technologies</h3>
                                    <p class="mb-0 text-muted">The stack that carries the platform.</p>
                                </div>
                                <span class="ll-dev-timeline-eyebrow">
                                    <i class="bi bi-box-seam"></i>
                                    Stack
                                </span>
                            </div>
                            <div class="ll-dev-timeline-stack">
                                @foreach($techStack as $tech)
                                    <article class="ll-dev-timeline-tech">
                                        <i class="bi {{ $tech['icon'] }}"></i>
                                        <h4>{{ $tech['name'] }}</h4>
                                        <p>{{ $tech['detail'] }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ll-dev-timeline-footer ll-dev-timeline-enter">
            Livelatch is best explained as a platform stack in motion: Laravel coordinates the app, Supabase handles identity and edge logic, Stripe handles billing, Encore serves the LatchDeck API, and the fan layer is growing into its own front door at fan.livelatch.com. This page exists so that story can be shown, not just told.
        </section>
    </div>
</div>

<script>
    (() => {
        const root = document.querySelector('.ll-dev-timeline');

        if (!root || root.dataset.initialized === 'true') {
            return;
        }

        root.dataset.initialized = 'true';

        const items = Array.from(root.querySelectorAll('[data-timeline-item]'));
        const filters = Array.from(root.querySelectorAll('[data-filter]'));
        const title = root.querySelector('#ll-dev-timeline-title');
        const summary = root.querySelector('#ll-dev-timeline-summary');
        const date = root.querySelector('#ll-dev-timeline-date');
        const observers = root.querySelectorAll('.ll-dev-timeline-enter');

        function setActive(item) {
            items.forEach((entry) => {
                entry.classList.toggle('is-active', entry === item);
            });

            if (title) {
                title.textContent = item.dataset.title || '';
            }

            if (summary) {
                summary.textContent = item.dataset.summary || '';
            }

            if (date) {
                date.innerHTML = '<i class="bi bi-calendar3"></i> ' + (item.dataset.date || '');
            }
        }

        function applyFilter(filter) {
            items.forEach((item) => {
                const category = item.dataset.category || 'all';
                const isVisible = filter === 'all' || category === filter;

                item.style.display = isVisible ? '' : 'none';
            });
        }

        items.forEach((item) => {
            item.addEventListener('click', () => setActive(item));
        });

        filters.forEach((button) => {
            button.addEventListener('click', () => {
                filters.forEach((candidate) => candidate.classList.remove('is-active'));
                button.classList.add('is-active');
                applyFilter(button.dataset.filter || 'all');

                const firstVisible = items.find((item) => item.style.display !== 'none');

                if (firstVisible) {
                    setActive(firstVisible);
                }
            });
        });

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.15 });

            observers.forEach((element) => observer.observe(element));
        } else {
            observers.forEach((element) => element.classList.add('is-visible'));
        }
    })();
</script>
@endsection
