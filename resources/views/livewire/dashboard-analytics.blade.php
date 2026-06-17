@php
    $profile = $analytics['profile'] ?? [];
    $status = $analytics['status'] ?? ['tone' => 'steady', 'headline' => 'Your dashboard is ready.', 'message' => 'Share your profile to start collecting link clicks.'];
    $linkRows = $analytics['linkRows'] ?? [];
    $dailyClicks = $analytics['dailyClicks'] ?? [];
    $activeConnections = $analytics['activeConnections'] ?? [];
    $socialMetrics = $analytics['socialMetrics'] ?? [];
    $source = $analytics['source'] ?? [];
    $peakDayClicks = max(1, (int) ($analytics['peakDayClicks'] ?? 1));
    $statusIcon = ['up' => 'bi-arrow-up-right', 'down' => 'bi-lightning-charge', 'steady' => 'bi-stars'][$status['tone'] ?? 'steady'] ?? 'bi-stars';
@endphp

<div class="container-fluid content-inner mt-n5 py-0 ll-dashboard">
    <style data-ll-dashboard-style>
        .ll-dashboard {
            display: grid;
            gap: 18px;
        }

        .ll-dashboard-hero {
            border: 1px solid color-mix(in srgb, var(--ll-primary) 28%, var(--ll-border));
            border-radius: calc(var(--ll-radius) + 8px);
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--ll-primary) 20%, transparent), transparent 34%),
                linear-gradient(135deg, var(--ll-surface-solid), color-mix(in srgb, var(--ll-bg-soft) 86%, var(--ll-primary)));
            box-shadow: var(--ll-shadow-soft);
            padding: clamp(20px, 4vw, 34px);
            overflow: hidden;
        }

        .ll-dashboard-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 0.65fr);
            gap: 22px;
            align-items: center;
        }

        .ll-dashboard-kicker,
        .ll-dashboard-source,
        .ll-dashboard-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--ll-muted);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .ll-dashboard-hero h1 {
            margin: 12px 0 10px;
            color: var(--ll-text);
            font-size: clamp(2rem, 4vw, 4rem);
            line-height: 1;
        }

        .ll-dashboard-hero p,
        .ll-dashboard-empty,
        .ll-dashboard-card p {
            color: var(--ll-muted);
        }

        .ll-dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .ll-dashboard-action,
        .ll-dashboard-link-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            font-weight: 700;
            padding: 10px 13px;
            text-decoration: none;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .ll-dashboard-action:hover,
        .ll-dashboard-link-action:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--ll-primary) 54%, var(--ll-border));
            color: var(--ll-text);
        }

        .ll-dashboard-status {
            display: grid;
            gap: 10px;
            border: 1px solid color-mix(in srgb, var(--ll-primary) 32%, var(--ll-border));
            border-radius: calc(var(--ll-radius) + 4px);
            background: color-mix(in srgb, var(--ll-primary) 10%, var(--ll-surface-solid));
            padding: 18px;
        }

        .ll-dashboard-status-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-dashboard-status strong,
        .ll-dashboard-card h3,
        .ll-dashboard-card strong,
        .ll-dashboard-link-title {
            color: var(--ll-text);
        }

        .ll-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .ll-dashboard-main {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 18px;
            align-items: start;
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
            min-height: 132px;
        }

        .ll-dashboard-metric-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-dashboard-metric-value {
            color: var(--ll-text);
            font-size: clamp(1.75rem, 3.5vw, 2.75rem);
            font-weight: 800;
            line-height: 1;
        }

        .ll-dashboard-metric-label,
        .ll-dashboard-link-meta,
        .ll-dashboard-bar-label,
        .ll-dashboard-card small {
            color: var(--ll-muted);
        }

        .ll-dashboard-card h3 {
            margin: 0 0 14px;
            font-size: 1.05rem;
        }

        .ll-dashboard-chart {
            display: grid;
            gap: 12px;
        }

        .ll-line-chart {
            width: 100%;
            height: auto;
            display: block;
            overflow: visible;
        }

        .ll-line-chart-dot {
            fill: var(--ll-surface-solid);
            stroke: var(--ll-primary);
            stroke-width: 2;
            transition: r 140ms ease;
        }

        .ll-line-chart-dot:hover {
            r: 5;
        }

        .ll-dashboard-chart-axis {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--ll-muted);
            font-size: 0.72rem;
        }

        .ll-dashboard-chart-peak {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--ll-text);
            font-weight: 700;
        }

        .ll-dashboard-links {
            display: grid;
            gap: 10px;
        }

        .ll-dashboard-link-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            border: 1px solid var(--ll-border);
            border-radius: calc(var(--ll-radius) - 2px);
            background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent);
            padding: 12px;
        }

        .ll-dashboard-link-title,
        .ll-dashboard-link-meta {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ll-dashboard-link-stats {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ll-dashboard-chip {
            border: 1px solid var(--ll-border);
            border-radius: 999px;
            background: var(--ll-surface-solid);
            padding: 6px 9px;
            white-space: nowrap;
        }

        .ll-dashboard-chip.up {
            color: #147a45;
            border-color: color-mix(in srgb, #22c55e 36%, var(--ll-border));
            background: color-mix(in srgb, #22c55e 12%, transparent);
        }

        .ll-dashboard-chip.down {
            color: #b42318;
            border-color: color-mix(in srgb, #ef4444 32%, var(--ll-border));
            background: color-mix(in srgb, #ef4444 10%, transparent);
        }

        .ll-dashboard-social-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .ll-dashboard-social-card {
            display: grid;
            gap: 12px;
            border: 1px solid var(--ll-border);
            border-radius: calc(var(--ll-radius) - 2px);
            background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent);
            padding: 12px;
        }

        .ll-dashboard-mini-chart {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(12px, 1fr));
            gap: 6px;
            align-items: end;
            min-height: 96px;
        }

        .ll-dashboard-mini-bar {
            min-height: 4px;
            border-radius: 999px 999px 5px 5px;
            background: linear-gradient(180deg, var(--ll-primary-2), var(--ll-primary));
        }

        .ll-dashboard-empty {
            border: 1px dashed var(--ll-border);
            border-radius: var(--ll-radius);
            padding: 16px;
            background: color-mix(in srgb, var(--ll-bg-soft) 62%, transparent);
        }

        @media (max-width: 1199.98px) {
            .ll-dashboard-grid,
            .ll-dashboard-social-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-dashboard-main,
            .ll-dashboard-hero-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .ll-dashboard-grid,
            .ll-dashboard-social-grid {
                grid-template-columns: 1fr;
            }

            .ll-dashboard-link-row {
                grid-template-columns: 1fr;
            }

            .ll-dashboard-link-stats {
                justify-content: flex-start;
            }

            .ll-dashboard-day-chart {
                gap: 5px;
            }
        }
    </style>

    <section class="ll-dashboard-hero">
        <div class="ll-dashboard-hero-grid">
            <div>
                <div class="ll-dashboard-kicker">
                    <i class="bi bi-broadcast-pin"></i>
                    Creator dashboard
                </div>

                <h1>{{ !empty($profile['handle']) ? '@' . $profile['handle'] : 'Your Livelatch' }}</h1>
                <p>Live link performance from Supabase, with your creator connections alongside it.</p>

                <div class="ll-dashboard-actions">
                    <a href="{{ url('/studio/links') }}" class="ll-dashboard-action" hx-get="{{ url('/studio/links') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                        <i class="bi bi-plus-circle"></i>
                        Add links
                    </a>
                    <a href="{{ url('/studio/theme') }}" class="ll-dashboard-action" hx-get="{{ url('/studio/theme') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                        <i class="bi bi-palette"></i>
                        Tune profile
                    </a>
                    <a href="{{ url('/studio/account/latchid') }}" class="ll-dashboard-action" hx-get="{{ url('/studio/account/latchid') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                        <i class="bi bi-person-badge"></i>
                        Connections
                    </a>
                    @if(!empty($profile['url']))
                        <a href="{{ $profile['url'] }}" target="_blank" rel="noopener" class="ll-dashboard-action">
                            <i class="bi bi-box-arrow-up-right"></i>
                            View profile
                        </a>
                    @endif
                </div>
            </div>

            <aside class="ll-dashboard-status" role="status">
                <span class="ll-dashboard-status-icon"><i class="bi {{ $statusIcon }}"></i></span>
                <strong>{{ $status['headline'] ?? 'Your dashboard is ready.' }}</strong>
                <p class="mb-0">{{ $status['message'] ?? 'Keep sharing your profile to build momentum.' }}</p>
                <span class="ll-dashboard-source">
                    <i class="bi bi-activity"></i>
                    Live analytics
                </span>
            </aside>
        </div>
    </section>

    @if(empty($source['isConfigured']))
        <section class="ll-dashboard-empty" role="status">
            Analytics are getting ready. Check back after your profile receives new visits and link clicks.
        </section>
    @endif

    <section class="ll-dashboard-grid">
        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-cursor-fill"></i></span>
            <div>
                <div class="ll-dashboard-metric-value">{{ number_format((int) ($analytics['totalClicks'] ?? 0)) }}</div>
                <div class="ll-dashboard-metric-label">Total clicks</div>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-sunrise"></i></span>
            <div>
                <div class="ll-dashboard-metric-value">{{ number_format((int) ($analytics['todayClicks'] ?? 0)) }}</div>
                <div class="ll-dashboard-metric-label">Clicks today</div>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-link-45deg"></i></span>
            <div>
                <div class="ll-dashboard-metric-value">{{ number_format((int) ($analytics['uniqueLinks'] ?? 0)) }}</div>
                <div class="ll-dashboard-metric-label">Clicked links</div>
            </div>
        </article>

        <article class="ll-dashboard-card ll-dashboard-metric">
            <span class="ll-dashboard-metric-icon"><i class="bi bi-magic"></i></span>
            <div>
                <div class="ll-dashboard-metric-value">{{ number_format((float) ($analytics['averageClicksPerLink'] ?? 0), 1) }}</div>
                <div class="ll-dashboard-metric-label">Average per link</div>
            </div>
        </article>
    </section>

    <section class="ll-dashboard-main">
        <article class="ll-dashboard-card">
            <h3>Clicks over the last 14 days</h3>
            @if(!empty($dailyClicks))
                @php
                    $days = array_values($dailyClicks);
                    $n = count($days);
                    $cw = 720; $ch = 210; $px = 14; $pt = 16; $pb = 30;
                    $iw = $cw - $px * 2; $ih = $ch - $pt - $pb;
                    $baseY = $pt + $ih;
                    $pts = [];
                    foreach ($days as $i => $day) {
                        $clicks = (int) ($day['clicks'] ?? 0);
                        $x = $n > 1 ? $px + ($i / ($n - 1)) * $iw : $cw / 2;
                        $y = $baseY - ($clicks / $peakDayClicks) * $ih;
                        $pts[] = ['x' => round($x, 1), 'y' => round($y, 1), 'c' => $clicks, 'label' => $day['label'] ?? ''];
                    }
                    $line = implode(' ', array_map(fn ($p) => $p['x'] . ',' . $p['y'], $pts));
                    $area = 'M ' . $pts[0]['x'] . ' ' . $baseY
                        . ' L ' . implode(' L ', array_map(fn ($p) => $p['x'] . ' ' . $p['y'], $pts))
                        . ' L ' . $pts[$n - 1]['x'] . ' ' . $baseY . ' Z';
                @endphp
                <div class="ll-dashboard-chart">
                    <svg class="ll-line-chart" viewBox="0 0 {{ $cw }} {{ $ch }}" role="img" aria-label="Line chart of daily clicks over the last 14 days">
                        <defs>
                            <linearGradient id="llClicksFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--ll-primary)" stop-opacity="0.26"></stop>
                                <stop offset="100%" stop-color="var(--ll-primary)" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>
                        <path d="{{ $area }}" fill="url(#llClicksFill)"></path>
                        <polyline points="{{ $line }}" fill="none" stroke="var(--ll-primary)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"></polyline>
                        @foreach($pts as $p)
                            <circle class="ll-line-chart-dot" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3">
                                <title>{{ $p['label'] }}: {{ number_format($p['c']) }} clicks</title>
                            </circle>
                        @endforeach
                    </svg>
                    <div class="ll-dashboard-chart-axis">
                        <span>{{ $pts[0]['label'] ?? '' }}</span>
                        <span class="ll-dashboard-chart-peak"><i class="bi bi-graph-up-arrow"></i> Peak {{ number_format($peakDayClicks) }}/day</span>
                        <span>{{ $pts[$n - 1]['label'] ?? '' }}</span>
                    </div>
                </div>
            @else
                <div class="ll-dashboard-empty">No clicks have been recorded yet.</div>
            @endif
        </article>

        <article class="ll-dashboard-card">
            <h3>Creator pulse</h3>
            <p class="mb-3">Today has {{ number_format((int) ($analytics['todayClicks'] ?? 0)) }} clicks compared with {{ number_format((int) ($analytics['yesterdayClicks'] ?? 0)) }} yesterday.</p>
            @php $delta = $analytics['clickDeltaPercent'] ?? null; @endphp
            <span class="ll-dashboard-chip {{ $delta !== null && $delta >= 0 ? 'up' : ($delta !== null ? 'down' : '') }}">
                <i class="bi {{ $delta !== null && $delta >= 0 ? 'bi-arrow-up-right' : ($delta !== null ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                {{ $delta === null ? 'New activity window' : abs($delta) . '% ' . ($delta >= 0 ? 'up' : 'down') . ' since yesterday' }}
            </span>
        </article>
    </section>

    <section class="ll-dashboard-main">
        <article class="ll-dashboard-card">
            <h3>Clicks per link</h3>
            <div class="ll-dashboard-links">
                @forelse($linkRows as $link)
                    @php $linkDelta = $link['deltaPercent'] ?? null; @endphp
                    <div class="ll-dashboard-link-row">
                        <div>
                            <span class="ll-dashboard-link-title">{{ $link['title'] ?? 'Untitled link' }}</span>
                            <span class="ll-dashboard-link-meta">{{ $link['host'] ?? $link['url'] ?? 'Destination unavailable' }}</span>
                            @if(!empty($link['lastClickedAt']))
                                <small>Last clicked {{ $link['lastClickedAt'] }}</small>
                            @endif
                        </div>
                        <div class="ll-dashboard-link-stats">
                            <span class="ll-dashboard-chip">{{ number_format((int) ($link['clicks'] ?? 0)) }} clicks</span>
                            <span class="ll-dashboard-chip {{ $linkDelta !== null && $linkDelta >= 0 ? 'up' : ($linkDelta !== null ? 'down' : '') }}">
                                {{ $linkDelta === null ? 'New' : abs($linkDelta) . '% ' . ($linkDelta >= 0 ? 'up' : 'down') }}
                            </span>
                            @if(!empty($link['url']))
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="ll-dashboard-link-action">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Open
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="ll-dashboard-empty">
                        No link clicks yet. Share your profile and your top links will appear here as your audience starts clicking.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="ll-dashboard-card">
            <h3>Connected channels</h3>
            <div class="ll-dashboard-social-grid">
                @forelse($activeConnections as $provider => $connection)
                    @php
                        $metric = $socialMetrics[$provider] ?? null;
                        $points = $metric['points'] ?? [];
                        $peak = max(1, (int) ($metric['peak'] ?? 1));
                        $metricDelta = $metric['deltaPercent'] ?? null;
                    @endphp
                    <div class="ll-dashboard-social-card">
                        <div>
                            <strong>{{ $connection['label'] ?? ucfirst($provider) }}</strong>
                            <div class="ll-dashboard-link-meta">
                                {{ !empty($connection['connected_at']) ? 'Connected ' . $connection['connected_at'] : 'Connected' }}
                            </div>
                        </div>

                        @if($metric && !empty($points))
                            <div>
                                <div class="ll-dashboard-metric-value">{{ number_format((int) ($metric['latest'] ?? 0)) }}</div>
                                <div class="ll-dashboard-metric-label">{{ $metric['metricLabel'] ?? 'followers' }}</div>
                            </div>
                            <div class="ll-dashboard-mini-chart" aria-label="{{ $connection['label'] ?? $provider }} metric chart">
                                @foreach($points as $point)
                                    @php $height = max(5, ((int) ($point['value'] ?? 0) / $peak) * 88); @endphp
                                    <span class="ll-dashboard-mini-bar" title="{{ $point['label'] ?? '' }}: {{ number_format((int) ($point['value'] ?? 0)) }}" style="height: {{ $height }}px;"></span>
                                @endforeach
                            </div>
                            <span class="ll-dashboard-chip {{ $metricDelta !== null && $metricDelta >= 0 ? 'up' : ($metricDelta !== null ? 'down' : '') }}">
                                {{ $metricDelta === null ? 'First snapshot' : abs($metricDelta) . '% ' . ($metricDelta >= 0 ? 'up' : 'down') . ' over history' }}
                            </span>
                        @else
                            <p class="mb-0">Connected. Waiting for follower or subscriber snapshots before drawing a graph.</p>
                        @endif
                    </div>
                @empty
                    <div class="ll-dashboard-empty">
                        No creator channels are connected yet. Link your channels from LatchID to unlock growth charts.
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>
