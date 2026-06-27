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
    // Stat-ring source: today's clicks as a share of the peak day (existing data,
    // shown as a gauge instead of a sentence — no new metric).
    $todayClicks = (int) ($analytics['todayClicks'] ?? 0);
    $ringPct = max(4, min(100, $peakDayClicks > 0 ? (int) round($todayClicks / $peakDayClicks * 100) : 4));
    // Live preview source — the user's real public profile (same-origin iframe).
    $handle = $profile['handle'] ?? '';
    $previewUrl = $profile['url'] ?? ($handle !== '' ? url('/@' . $handle) : null);
@endphp

<div class="container-fluid content-inner mt-n5 py-0 ll-dashboard">
    <style data-ll-dashboard-style>
        .ll-dashboard { display: grid; gap: 12px; }

        .ll-dashboard-kicker,
        .ll-dashboard-source,
        .ll-dashboard-chip {
            display: inline-flex; align-items: center; gap: 7px;
            color: var(--ll-muted); font-size: 0.78rem; font-weight: 700;
        }

        .ll-dashboard-card {
            position: relative;
            border: 1px solid color-mix(in srgb, var(--ll-primary) 12%, var(--ll-border));
            border-radius: var(--ll-radius);
            background: linear-gradient(180deg, color-mix(in srgb, var(--ll-text) 5%, var(--ll-surface-solid)), var(--ll-surface-solid));
            box-shadow: var(--ll-shadow-soft), inset 0 1px 0 color-mix(in srgb, var(--ll-text) 9%, transparent);
            padding: 14px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .ll-dashboard-card:hover {
            border-color: color-mix(in srgb, var(--ll-primary) 26%, var(--ll-border));
        }
        .ll-dashboard-card h3 { margin: 0 0 10px; font-size: 0.95rem; }
        .ll-dashboard-card strong,
        .ll-dashboard-card h3,
        .ll-dashboard-link-title { color: var(--ll-text); }
        .ll-dashboard-metric-label,
        .ll-dashboard-link-meta,
        .ll-dashboard-card small,
        .ll-dashboard-card p { color: var(--ll-muted); }

        /* ---- Slim hero ---- */
        .ll-dashboard-hero {
            position: relative;
            display: flex; flex-wrap: wrap; gap: 12px 18px;
            align-items: center; justify-content: space-between;
            border: 1px solid color-mix(in srgb, var(--ll-primary) 30%, var(--ll-border));
            border-radius: var(--ll-radius);
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--ll-primary) 20%, transparent), transparent 40%),
                radial-gradient(circle at bottom right, color-mix(in srgb, var(--ll-primary-2) 14%, transparent), transparent 46%),
                linear-gradient(135deg, var(--ll-surface-solid), color-mix(in srgb, var(--ll-bg-soft) 85%, var(--ll-primary)));
            box-shadow: var(--ll-shadow-soft), inset 0 1px 0 color-mix(in srgb, var(--ll-text) 10%, transparent);
            padding: 14px 18px; overflow: hidden;
        }
        .ll-dashboard-hero h1 { margin: 4px 0 3px; color: var(--ll-text); font-size: clamp(1.4rem, 2.4vw, 1.9rem); line-height: 1.05; }
        .ll-dashboard-hero-msg { color: var(--ll-muted); font-size: 0.85rem; margin: 0; max-width: 52ch; }
        .ll-dashboard-actions { display: flex; flex-wrap: wrap; gap: 8px; }

        .ll-dashboard-action,
        .ll-dashboard-link-action {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            border: 1px solid var(--ll-border); border-radius: var(--ll-button-radius);
            background: var(--ll-surface-solid); color: var(--ll-text);
            font-weight: 700; font-size: 0.85rem; padding: 8px 12px; text-decoration: none;
            transition: transform 160ms ease, border-color 160ms ease;
        }
        .ll-dashboard-action:hover,
        .ll-dashboard-link-action:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--ll-primary) 54%, var(--ll-border)); color: var(--ll-text); }
        .ll-dashboard-action-primary {
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            color: #fff; border-color: transparent;
            box-shadow: 0 8px 20px color-mix(in srgb, var(--ll-primary) 28%, transparent);
        }
        .ll-dashboard-action-primary:hover { color: #fff; }

        /* ---- Two-column body: stats (left) + live preview (right) ---- */
        .ll-dashboard-body {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(300px, 1fr);
            gap: 12px; align-items: start;
        }
        .ll-dashboard-left { display: grid; gap: 12px; align-content: start; }
        .ll-dashboard-duo { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 12px; align-items: start; }

        /* ---- Compact KPI strip ---- */
        .ll-dashboard-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .ll-dashboard-metric { display: grid; grid-template-columns: 38px minmax(0, 1fr); align-items: center; gap: 11px; padding: 11px 12px; }
        .ll-dashboard-metric-icon {
            width: 38px; height: 38px; display: grid; place-items: center; border-radius: 11px; color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 6px 16px color-mix(in srgb, var(--ll-primary) 32%, transparent);
        }
        .ll-dashboard-metric-value { color: var(--ll-text); font-size: 1.45rem; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
        .ll-dashboard-metric-label { font-size: 0.72rem; margin-top: 3px; }

        /* ---- Chart (compact) ---- */
        .ll-dashboard-chart { display: grid; gap: 8px; }
        .ll-line-chart { width: 100%; height: auto; max-height: 150px; display: block; overflow: visible; }
        .ll-line-chart-dot { fill: var(--ll-surface-solid); stroke: var(--ll-primary); stroke-width: 2; transition: r 140ms ease; }
        .ll-line-chart-dot:hover { r: 5; }
        .ll-dashboard-chart-axis { display: flex; align-items: center; justify-content: space-between; gap: 10px; color: var(--ll-muted); font-size: 0.7rem; }
        .ll-dashboard-chart-peak { display: inline-flex; align-items: center; gap: 6px; color: var(--ll-text); font-weight: 700; }

        /* ---- Pulse ring (compact) ---- */
        .ll-dashboard-pulse { display: grid; align-content: start; justify-items: center; text-align: center; }
        .ll-dashboard-pulse h3 { justify-self: start; }
        .ll-dashboard-ring {
            --ring: 50%; width: 124px; height: 124px; margin: 4px auto 12px; border-radius: 50%;
            display: grid; place-items: center;
            background:
                radial-gradient(closest-side, var(--ll-surface-solid) 70%, transparent 71% 100%),
                conic-gradient(var(--ll-primary-2) 0, var(--ll-primary) var(--ring), color-mix(in srgb, var(--ll-text) 12%, transparent) var(--ring) 100%);
            box-shadow: 0 0 26px color-mix(in srgb, var(--ll-primary) 20%, transparent);
        }
        .ll-dashboard-ring-value { display: block; font-size: 1.55rem; font-weight: 800; line-height: 1; color: var(--ll-text); font-variant-numeric: tabular-nums; }
        .ll-dashboard-ring-label { display: block; margin-top: 4px; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ll-muted); }

        /* ---- Scrollable compact lists ---- */
        .ll-dashboard-scroll { max-height: 196px; overflow: auto; scrollbar-width: thin; margin: -2px -4px -2px 0; padding-right: 4px; }
        .ll-dashboard-links { display: grid; gap: 8px; }
        .ll-dashboard-link-row {
            display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center;
            border: 1px solid var(--ll-border); border-radius: var(--ll-radius-sm, 10px);
            background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent); padding: 9px 11px;
        }
        .ll-dashboard-link-title { font-size: 0.86rem; font-weight: 600; }
        .ll-dashboard-link-title, .ll-dashboard-link-meta { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ll-dashboard-link-meta { font-size: 0.72rem; }
        .ll-dashboard-link-stats { display: flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }

        .ll-dashboard-chip { border: 1px solid var(--ll-border); border-radius: 999px; background: var(--ll-surface-solid); padding: 4px 9px; white-space: nowrap; font-size: 0.72rem; }
        .ll-dashboard-chip.up { color: #147a45; border-color: color-mix(in srgb, #22c55e 36%, var(--ll-border)); background: color-mix(in srgb, #22c55e 12%, transparent); }
        .ll-dashboard-chip.down { color: #b42318; border-color: color-mix(in srgb, #ef4444 32%, var(--ll-border)); background: color-mix(in srgb, #ef4444 10%, transparent); }
        [data-ll-theme="dark"] .ll-dashboard-chip.up { color: #4ade80; }
        [data-ll-theme="dark"] .ll-dashboard-chip.down { color: #f87171; }

        .ll-dashboard-social-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .ll-dashboard-social-card { display: grid; gap: 8px; border: 1px solid var(--ll-border); border-radius: var(--ll-radius-sm, 10px); background: color-mix(in srgb, var(--ll-bg-soft) 76%, transparent); padding: 10px; }
        .ll-dashboard-social-card .ll-dashboard-metric-value { font-size: 1.2rem; }
        .ll-dashboard-mini-chart { display: grid; grid-template-columns: repeat(auto-fit, minmax(10px, 1fr)); gap: 5px; align-items: end; min-height: 56px; }
        .ll-dashboard-mini-bar { min-height: 4px; border-radius: 999px 999px 4px 4px; background: linear-gradient(180deg, var(--ll-primary-2), var(--ll-primary)); }

        .ll-dashboard-empty { border: 1px dashed var(--ll-border); border-radius: var(--ll-radius-sm, 10px); padding: 12px; background: color-mix(in srgb, var(--ll-bg-soft) 62%, transparent); color: var(--ll-muted); font-size: 0.82rem; }

        /* ---- Live preview (device frame, mirrors the Theme Studio) ---- */
        .ll-dashboard-preview { position: sticky; top: 84px; display: grid; gap: 10px; }
        .ll-dashboard-preview-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .ll-dashboard-preview-head span { display: inline-flex; align-items: center; gap: 7px; color: var(--ll-text); font-weight: 700; font-size: 0.9rem; }
        .ll-dashboard-preview-head span i { color: var(--ll-primary); }
        .ll-dashboard-stage { display: flex; justify-content: center; }
        .ll-dashboard-scaler { position: relative; }
        .ll-dashboard-frame {
            position: absolute; top: 0; left: 0; transform-origin: top left;
            background: #000; overflow: hidden; border: 9px solid #0b0f1c; border-radius: 34px;
            box-shadow: 0 24px 60px color-mix(in srgb, var(--ll-primary) 16%, rgba(8, 12, 30, 0.42));
        }
        .ll-dashboard-frame iframe { width: 100%; height: 100%; border: 0; display: block; background: #000; }
        .ll-dashboard-preview-empty { display: grid; place-items: center; text-align: center; min-height: 300px; gap: 8px; color: var(--ll-muted); }

        @media (max-width: 1199.98px) {
            .ll-dashboard-body { grid-template-columns: 1fr; }
            .ll-dashboard-preview { position: static; }
        }
        @media (max-width: 767.98px) {
            .ll-dashboard-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ll-dashboard-duo, .ll-dashboard-social-grid { grid-template-columns: 1fr; }
            .ll-dashboard-link-row { grid-template-columns: 1fr; }
            .ll-dashboard-link-stats { justify-content: flex-start; }
        }
    </style>

    <section class="ll-dashboard-hero">
        <div>
            <div class="ll-dashboard-kicker"><i class="bi bi-broadcast-pin"></i> Creator dashboard</div>
            <h1>{{ !empty($handle) ? '@' . $handle : 'Your Livelatch' }}</h1>
            <p class="ll-dashboard-hero-msg">{{ $status['message'] ?? 'Live link performance from Supabase, with your creator connections alongside it.' }}</p>
        </div>
        <div class="ll-dashboard-actions" data-tour="dashboard-actions">
            <a href="{{ url('/studio/links') }}" class="ll-dashboard-action ll-dashboard-action-primary" data-tour="add-links" hx-get="{{ url('/studio/links') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                <i class="bi bi-plus-circle"></i> Add links
            </a>
            <a href="{{ url('/studio/theme') }}" class="ll-dashboard-action" data-tour="tune-profile" hx-get="{{ url('/studio/theme') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                <i class="bi bi-palette"></i> Tune profile
            </a>
            <a href="{{ url('/studio/account/latchid') }}" class="ll-dashboard-action" data-tour="connections" hx-get="{{ url('/studio/account/latchid') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                <i class="bi bi-person-badge"></i> Connections
            </a>
            @if(!empty($previewUrl))
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="ll-dashboard-action" data-tour="view-profile">
                    <i class="bi bi-box-arrow-up-right"></i> View profile
                </a>
            @endif
        </div>
    </section>

    @if(empty($source['isConfigured']))
        <section class="ll-dashboard-empty" role="status">
            Analytics are getting ready. Check back after your profile receives new visits and link clicks.
        </section>
    @endif

    <div class="ll-dashboard-body">
        <div class="ll-dashboard-left">
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

            <div class="ll-dashboard-duo">
                <article class="ll-dashboard-card">
                    <h3>Clicks over the last 14 days</h3>
                    @if(!empty($dailyClicks))
                        @php
                            $days = array_values($dailyClicks);
                            $n = count($days);
                            $cw = 720; $ch = 168; $px = 12; $pt = 12; $pb = 26;
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

                <article class="ll-dashboard-card ll-dashboard-pulse">
                    <h3>Creator pulse</h3>
                    <div class="ll-dashboard-ring" style="--ring: {{ $ringPct }}%;" role="img" aria-label="{{ number_format($todayClicks) }} clicks today, {{ $ringPct }} percent of your peak day">
                        <div>
                            <span class="ll-dashboard-ring-value">{{ number_format($todayClicks) }}</span>
                            <span class="ll-dashboard-ring-label">clicks today</span>
                        </div>
                    </div>
                    @php $delta = $analytics['clickDeltaPercent'] ?? null; @endphp
                    <span class="ll-dashboard-chip {{ $delta !== null && $delta >= 0 ? 'up' : ($delta !== null ? 'down' : '') }}">
                        <i class="bi {{ $delta !== null && $delta >= 0 ? 'bi-arrow-up-right' : ($delta !== null ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>
                        {{ $delta === null ? 'New activity window' : abs($delta) . '% ' . ($delta >= 0 ? 'up' : 'down') . ' since yesterday' }}
                    </span>
                </article>
            </div>

            <div class="ll-dashboard-duo">
                <article class="ll-dashboard-card">
                    <h3>Clicks per link</h3>
                    <div class="ll-dashboard-scroll">
                        <div class="ll-dashboard-links">
                            @forelse($linkRows as $link)
                                @php $linkDelta = $link['deltaPercent'] ?? null; @endphp
                                <div class="ll-dashboard-link-row">
                                    <div>
                                        <span class="ll-dashboard-link-title">{{ $link['title'] ?? 'Untitled link' }}</span>
                                        <span class="ll-dashboard-link-meta">{{ $link['host'] ?? $link['url'] ?? 'Destination unavailable' }}</span>
                                    </div>
                                    <div class="ll-dashboard-link-stats">
                                        <span class="ll-dashboard-chip">{{ number_format((int) ($link['clicks'] ?? 0)) }} clicks</span>
                                        <span class="ll-dashboard-chip {{ $linkDelta !== null && $linkDelta >= 0 ? 'up' : ($linkDelta !== null ? 'down' : '') }}">
                                            {{ $linkDelta === null ? 'New' : abs($linkDelta) . '% ' . ($linkDelta >= 0 ? 'up' : 'down') }}
                                        </span>
                                        @if(!empty($link['url']))
                                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="ll-dashboard-link-action" aria-label="Open {{ $link['title'] ?? 'link' }}">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="ll-dashboard-empty">No link clicks yet. Share your profile and your top links will appear here.</div>
                            @endforelse
                        </div>
                    </div>
                </article>

                <article class="ll-dashboard-card">
                    <h3>Connected channels</h3>
                    <div class="ll-dashboard-scroll">
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
                                        <div class="ll-dashboard-link-meta">{{ !empty($connection['connected_at']) ? 'Connected ' . $connection['connected_at'] : 'Connected' }}</div>
                                    </div>
                                    @if($metric && !empty($points))
                                        <div>
                                            <div class="ll-dashboard-metric-value">{{ number_format((int) ($metric['latest'] ?? 0)) }}</div>
                                            <div class="ll-dashboard-metric-label">{{ $metric['metricLabel'] ?? 'followers' }}</div>
                                        </div>
                                        <div class="ll-dashboard-mini-chart" aria-label="{{ $connection['label'] ?? $provider }} metric chart">
                                            @foreach($points as $point)
                                                @php $height = max(5, ((int) ($point['value'] ?? 0) / $peak) * 52); @endphp
                                                <span class="ll-dashboard-mini-bar" title="{{ $point['label'] ?? '' }}: {{ number_format((int) ($point['value'] ?? 0)) }}" style="height: {{ $height }}px;"></span>
                                            @endforeach
                                        </div>
                                        <span class="ll-dashboard-chip {{ $metricDelta !== null && $metricDelta >= 0 ? 'up' : ($metricDelta !== null ? 'down' : '') }}">
                                            {{ $metricDelta === null ? 'First snapshot' : abs($metricDelta) . '% ' . ($metricDelta >= 0 ? 'up' : 'down') }}
                                        </span>
                                    @else
                                        <p class="mb-0" style="font-size:0.78rem;">Connected. Waiting for snapshots.</p>
                                    @endif
                                </div>
                            @empty
                                <div class="ll-dashboard-empty">No creator channels connected yet. Link them from LatchID to unlock growth charts.</div>
                            @endforelse
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <aside class="ll-dashboard-preview">
            <div class="ll-dashboard-preview-head">
                <span><i class="bi bi-phone"></i> Live preview</span>
                @if(!empty($previewUrl))
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="ll-dashboard-action" style="padding:6px 10px;">
                        <i class="bi bi-box-arrow-up-right"></i> Open
                    </a>
                @endif
            </div>
            <article class="ll-dashboard-card" style="padding:14px;">
                @if(!empty($previewUrl))
                    <div class="ll-dashboard-stage" id="ll-dashboard-stage">
                        <div class="ll-dashboard-scaler" id="ll-dashboard-scaler">
                            <div class="ll-dashboard-frame" id="ll-dashboard-frame">
                                <iframe id="ll-dashboard-iframe" src="{{ $previewUrl }}" title="Your live profile preview" loading="lazy" referrerpolicy="no-referrer"></iframe>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="ll-dashboard-preview-empty">
                        <i class="bi bi-phone" style="font-size:1.6rem;"></i>
                        <strong style="color:var(--ll-text);">No profile yet</strong>
                        <span>Pick a handle to preview your public page here.</span>
                    </div>
                @endif
            </article>
        </aside>
    </div>

    <script>
    window.LLDashFitPreview = function () {
        var stage = document.getElementById('ll-dashboard-stage');
        var scaler = document.getElementById('ll-dashboard-scaler');
        var frame = document.getElementById('ll-dashboard-frame');
        if (!stage || !scaler || !frame) return;
        var W = 390, H = 780;
        var availW = stage.clientWidth || 320;
        var maxH = Math.max(360, Math.min(H, (window.innerHeight || 800) - 250));
        var scale = Math.min(availW / W, maxH / H, 1);
        frame.style.width = W + 'px';
        frame.style.height = H + 'px';
        frame.style.transform = 'scale(' + scale + ')';
        scaler.style.width = (W * scale) + 'px';
        scaler.style.height = (H * scale) + 'px';
    };
    window.LLDashFitPreview();
    if (!window.__llDashPreviewBound) {
        window.__llDashPreviewBound = true;
        window.addEventListener('resize', function () { window.LLDashFitPreview && window.LLDashFitPreview(); });
        document.body.addEventListener('htmx:afterSettle', function () { window.LLDashFitPreview && window.LLDashFitPreview(); });
    }
    </script>
</div>
