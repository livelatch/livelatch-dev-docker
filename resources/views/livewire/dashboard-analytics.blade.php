<div class="container-fluid content-inner mt-n5 py-0 ll-dashboard">
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

    <style data-ll-dashboard-style>
        .ll-dashboard {
            display: grid;
            gap: 18px;
        }

        .ll-dashboard-hero {
            margin-top: 0;
        }

        .ll-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .ll-dashboard-card {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow-soft);
            padding: 18px;
        }

        .ll-dashboard-metric {
            display: grid;
            gap: 12px;
            min-height: 148px;
        }

        .ll-dashboard-metric-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            font-size: 1.15rem;
        }

        .ll-dashboard-metric strong {
            color: var(--ll-text);
            font-size: clamp(1.9rem, 4vw, 3rem);
            line-height: 1;
        }

        .ll-dashboard-metric span,
        .ll-dashboard-card p,
        .ll-dashboard-table small {
            color: var(--ll-muted);
        }

        .ll-dashboard-main {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.75fr);
            gap: 18px;
            align-items: start;
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
            font-size: 1.1rem;
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
            padding: 12px;
            border: 1px solid var(--ll-border);
            border-radius: 16px;
            background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent);
        }

        .ll-dashboard-row strong,
        .ll-dashboard-row small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ll-dashboard-chart {
            display: grid;
            gap: 12px;
        }

        .ll-dashboard-bar {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr) 46px;
            gap: 10px;
            align-items: center;
        }

        .ll-dashboard-bar-track {
            height: 12px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--ll-text) 9%, transparent);
            overflow: hidden;
        }

        .ll-dashboard-bar-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        @media (max-width: 1199.98px) {
            .ll-dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-dashboard-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .ll-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="ll-dashboard-grid">
        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-link-45deg"></i></span>
            <div>
                <strong>{{ number_format($links) }}</strong>
                <span>{{ __('messages.Total Links:') }}</span>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-cursor-fill"></i></span>
            <div>
                <strong>{{ number_format($clicks) }}</strong>
                <span>{{ __('messages.Link Clicks:') }}</span>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-eye-fill"></i></span>
            <div>
                <strong>{{ number_format($pageStats['visitors']['month'] ?? 0) }}</strong>
                <span>Profile visits this month</span>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-graph-up-arrow"></i></span>
            <div>
                <strong>{{ $links > 0 ? number_format($clicks / max($links, 1), 1) : '0.0' }}</strong>
                <span>Average clicks per link</span>
            </div>
        </article>
    </section>

    <section class="ll-dashboard-main">
        <article class="ll-dashboard-card">
            <h3>{{ __('messages.Top Links:') }}</h3>
            <div class="ll-dashboard-table">
                @forelse($toplinks as $link)
                    <div class="ll-dashboard-row">
                        <div>
                            <strong>{{ $link['title'] }}</strong>
                            <small>{{ $link['link'] }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ number_format($link['click_number']) }}</span>
                    </div>
                @empty
                    <p class="mb-0">{{ __('messages.You havenâ€™t added any links yet') }}</p>
                @endforelse
            </div>
        </article>

        <article class="ll-dashboard-card">
            <h3>Visit windows</h3>
            @php
                $visitorBars = [
                    ['label' => 'Today', 'value' => $pageStats['visitors']['day'] ?? 0],
                    ['label' => 'This week', 'value' => $pageStats['visitors']['week'] ?? 0],
                    ['label' => 'This month', 'value' => $pageStats['visitors']['month'] ?? 0],
                    ['label' => 'This year', 'value' => $pageStats['visitors']['year'] ?? 0],
                ];
                $maxVisitors = max(1, ...array_column($visitorBars, 'value'));
            @endphp
            <div class="ll-dashboard-chart">
                @foreach($visitorBars as $bar)
                    <div class="ll-dashboard-bar">
                        <span>{{ $bar['label'] }}</span>
                        <span class="ll-dashboard-bar-track">
                            <span class="ll-dashboard-bar-fill" style="width: {{ max(4, ($bar['value'] / $maxVisitors) * 100) }}%;"></span>
                        </span>
                        <strong>{{ number_format($bar['value']) }}</strong>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    @if(auth()->user()->role == 'admin' && !config('linkstack.single_user_mode'))
        <section class="ll-dashboard-main">
            <article class="ll-dashboard-card">
                <h3>{{ __('messages.Site statistics:') }}</h3>
                <div class="ll-dashboard-grid">
                    <div class="ll-dashboard-metric">
                        <span class="ll-dashboard-metric-icon"><i class="bi bi-share-fill"></i></span>
                        <strong>{{ number_format($siteLinks) }}</strong>
                        <span>{{ __('messages.Total links') }}</span>
                    </div>
                    <div class="ll-dashboard-metric">
                        <span class="ll-dashboard-metric-icon"><i class="bi bi-eye-fill"></i></span>
                        <strong>{{ number_format($siteClicks) }}</strong>
                        <span>{{ __('messages.Total clicks') }}</span>
                    </div>
                    <div class="ll-dashboard-metric">
                        <span class="ll-dashboard-metric-icon"><i class="bi bi-person-fill"></i></span>
                        <strong>{{ number_format($userNumber) }}</strong>
                        <span>{{ __('messages.Total users') }}</span>
                    </div>
                </div>
            </article>

            <article class="ll-dashboard-card">
                <h3>User activity</h3>
                <div class="ll-dashboard-chart">
                    <div class="ll-dashboard-bar">
                        <span>New 30d</span>
                        <span class="ll-dashboard-bar-track"><span class="ll-dashboard-bar-fill" style="width: {{ max(4, ($lastMonthCount / max(1, $userNumber)) * 100) }}%;"></span></span>
                        <strong>{{ number_format($lastMonthCount) }}</strong>
                    </div>
                    <div class="ll-dashboard-bar">
                        <span>New 7d</span>
                        <span class="ll-dashboard-bar-track"><span class="ll-dashboard-bar-fill" style="width: {{ max(4, ($lastWeekCount / max(1, $userNumber)) * 100) }}%;"></span></span>
                        <strong>{{ number_format($lastWeekCount) }}</strong>
                    </div>
                    <div class="ll-dashboard-bar">
                        <span>Active 24h</span>
                        <span class="ll-dashboard-bar-track"><span class="ll-dashboard-bar-fill" style="width: {{ max(4, ($updatedLast24HrsCount / max(1, $userNumber)) * 100) }}%;"></span></span>
                        <strong>{{ number_format($updatedLast24HrsCount) }}</strong>
                    </div>
                </div>
            </article>
        </section>
    @endif
</div>
