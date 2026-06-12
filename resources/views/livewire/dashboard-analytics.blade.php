<div class="container-fluid content-inner mt-n5 py-0 ll-dashboard">
<<<<<<< HEAD
=======
    <section class="ll-hero ll-dashboard-hero">
        <div class="ll-hero-content">
            <div>
                <div class="ll-kicker">
                    <i class="bi bi-activity"></i>
                    Dashboard
                </div>

                <h1>{{ $littlelinkName ? '@' . $littlelinkName : 'Your Livelatch' }}</h1>
                <p>Review profile performance, jump into common tasks, and keep your creator presence moving.</p>
            </div>

            <div class="ll-hero-actions">
                <a href="{{ url('/studio/links') }}" class="ll-hero-button" hx-get="{{ url('/studio/links') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                    <i class="bi bi-link-45deg"></i>
                    <span>{{ __('messages.Links') }}<small>Manage blocks</small></span>
                </a>

                <a href="{{ url('/studio/page') }}" class="ll-hero-button secondary" hx-get="{{ url('/studio/page') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                    <i class="bi bi-person-badge"></i>
                    <span>{{ __('messages.Appearance') }}<small>Edit profile</small></span>
                </a>

                <a href="{{ url('/studio/theme') }}" class="ll-hero-button secondary" hx-get="{{ url('/studio/theme') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                    <i class="bi bi-stars"></i>
                    <span>{{ __('messages.Themes') }}<small>Preview style</small></span>
                </a>

                <a href="{{ url('/@' . $littlelinkName) }}" target="_blank" class="ll-hero-button secondary">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>{{ __('messages.View Page') }}<small>Open public profile</small></span>
                </a>
            </div>
        </div>
    </section>

    @if($isSampleData)
        <section class="ll-dashboard-card ll-dashboard-disclaimer" role="status" aria-live="polite">
            <strong>Sample data active.</strong>
            <span>{{ $analyticsNotice ?: 'Sample analytics data only — Latchalytics is coming soon.' }}</span>
        </section>
    @endif

<<<<<<< HEAD
>>>>>>> main
=======
>>>>>>> main
    <style data-ll-dashboard-style>
        .ll-dashboard {
            display: grid;
            gap: 18px;
        }

        .ll-dashboard-hero,
        .ll-dashboard-card {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow-soft);
        }

        .ll-dashboard-hero {
            padding: clamp(20px, 3vw, 30px);
            display: grid;
            gap: 18px;
        }

        .ll-dashboard-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-dashboard-hero h1,
        .ll-dashboard-hero p {
            margin: 0;
        }

        .ll-dashboard-hero p {
            color: var(--ll-muted);
            max-width: 760px;
        }

        .ll-dashboard-links {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .ll-dashboard-link {
            display: grid;
            gap: 4px;
            border: 1px solid var(--ll-border);
            border-radius: 14px;
            padding: 13px;
            color: inherit;
            text-decoration: none;
            background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent);
        }

        .ll-dashboard-link:hover {
            border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border));
        }

        .ll-dashboard-link small {
            color: var(--ll-muted);
        }

        .ll-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .ll-dashboard-card {
            padding: 18px;
        }

        .ll-dashboard-metric {
            display: grid;
            gap: 10px;
            min-height: 128px;
        }

        .ll-dashboard-metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 1rem;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-dashboard-metric strong {
            font-size: clamp(1.55rem, 3vw, 2.2rem);
            line-height: 1;
            color: var(--ll-text);
        }

        .ll-dashboard-metric span {
            color: var(--ll-muted);
            font-weight: 700;
        }

        .ll-dashboard-metric small {
            color: var(--ll-primary);
            font-weight: 800;
        }

        .ll-dashboard-main {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
            gap: 18px;
        }

        .ll-dashboard-disclaimer {
            display: grid;
            gap: 4px;
            border-left: 6px solid var(--ll-primary);
        }

        .ll-dashboard-disclaimer strong {
            color: var(--ll-text);
            font-size: 0.95rem;
        }

        .ll-dashboard-disclaimer span {
            color: var(--ll-muted);
            font-size: 0.88rem;
        }

        .ll-dashboard-disclaimer {
            display: grid;
            gap: 4px;
            border-left: 6px solid var(--ll-primary);
        }

        .ll-dashboard-disclaimer strong {
            color: var(--ll-text);
            font-size: 0.95rem;
        }

        .ll-dashboard-disclaimer span {
            color: var(--ll-muted);
            font-size: 0.88rem;
        }

        .ll-dashboard-card h3 {
            margin: 0 0 14px;
            font-size: 1.04rem;
        }

        .ll-dashboard-table {
            display: grid;
            gap: 10px;
        }

        .ll-dashboard-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            border: 1px solid var(--ll-border);
            border-radius: 14px;
            padding: 11px 12px;
            background: color-mix(in srgb, var(--ll-bg-soft) 80%, transparent);
        }

        .ll-dashboard-row small {
            color: var(--ll-muted);
            font-weight: 700;
        }

        .ll-dashboard-pill {
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.8rem;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-dashboard-bars {
            display: grid;
            gap: 10px;
        }

        .ll-dashboard-bar {
            display: grid;
            grid-template-columns: 80px minmax(0, 1fr) 48px;
            gap: 10px;
            align-items: center;
        }

        .ll-dashboard-bar-track {
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: color-mix(in srgb, var(--ll-text) 10%, transparent);
        }

        .ll-dashboard-bar-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        @media (max-width: 1199.98px) {
            .ll-dashboard-links,
            .ll-dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-dashboard-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .ll-dashboard-links,
            .ll-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="ll-dashboard-hero">
        <div>
            <span class="ll-dashboard-kicker">
                <i class="bi bi-grid-1x2-fill"></i>
                Dashboard
            </span>
            <h1 class="mt-2 mb-2">Welcome back, {{ '@' . $displayHandle }}</h1>
            <p>This dashboard is self-contained and loads a starter analytics snapshot directly from Livewire so the first post-login page stays stable and fast.</p>
        </div>

        <div class="ll-dashboard-links">
            @foreach($quickLinks as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="ll-dashboard-link"
                    @if($link['external'])
                        target="_blank"
                    @else
                        hx-get="{{ $link['url'] }}"
                        hx-target="#ll-content"
                        hx-select="#ll-content > *"
                        hx-push-url="true"
                        hx-swap="innerHTML"
                        hx-indicator="#ll-profile-skeleton"
                    @endif
                >
                    <strong><i class="{{ $link['icon'] }}"></i> {{ $link['title'] }}</strong>
                    <small>{{ $link['hint'] }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="ll-dashboard-grid">
        @foreach($overviewCards as $card)
            <article class="ll-dashboard-card ll-dashboard-metric">
                <span class="ll-dashboard-metric-icon"><i class="{{ $card['icon'] }}"></i></span>
                <div>
                    <strong>{{ number_format($card['value'], isset($card['suffix']) ? 1 : 0) }}{{ $card['suffix'] ?? '' }}</strong>
                    <span>{{ $card['label'] }}</span>
                </div>
                <small>{{ $card['delta'] }}</small>
            </article>
        @endforeach
    </section>

    <section class="ll-dashboard-main">
        <article class="ll-dashboard-card">
            <h3>Recent studio activity</h3>
            <div class="ll-dashboard-table">
                @foreach($sampleActivity as $activity)
                    <div class="ll-dashboard-row">
                        <div>
                            <strong>{{ $activity['title'] }}</strong>
                            <small>{{ $activity['meta'] }}</small>
                        </div>
                        <span class="ll-dashboard-pill">{{ $activity['state'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="ll-dashboard-card">
            @php
                $maxTraffic = max(1, ...array_column($sampleTraffic, 'value'));
                $maxBreakdown = max(1, ...array_column($sampleBreakdown, 'value'));
            @endphp
            <h3>Sample traffic window</h3>
            <div class="ll-dashboard-bars">
                @foreach($sampleTraffic as $traffic)
                    <div class="ll-dashboard-bar">
                        <span>{{ $traffic['label'] }}</span>
                        <span class="ll-dashboard-bar-track">
                            <span class="ll-dashboard-bar-fill" style="width: {{ ($traffic['value'] / $maxTraffic) * 100 }}%"></span>
                        </span>
                        <strong>{{ $traffic['value'] }}</strong>
                    </div>
                @endforeach
            </div>

            <h3 class="mt-4">Sample source mix</h3>
            <div class="ll-dashboard-bars">
                @foreach($sampleBreakdown as $source)
                    <div class="ll-dashboard-bar">
                        <span>{{ $source['label'] }}</span>
                        <span class="ll-dashboard-bar-track">
                            <span class="ll-dashboard-bar-fill" style="width: {{ ($source['value'] / $maxBreakdown) * 100 }}%"></span>
                        </span>
                        <strong>{{ $source['value'] }}%</strong>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
</div>
