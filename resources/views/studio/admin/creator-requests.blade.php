@extends('layouts.sidebar')

@section('content')
<style data-ll-crq-style>
    .ll-crq { display: grid; gap: 18px; }
    .ll-crq-head h1 { color: var(--ll-text); font-size: clamp(1.5rem, 2.4vw, 2rem); margin: 0 0 6px; }
    .ll-crq-head p { color: var(--ll-muted); margin: 0; max-width: 72ch; font-size: .9rem; }

    .ll-crq-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .ll-crq-stat { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: linear-gradient(180deg, color-mix(in srgb, var(--ll-text) 5%, var(--ll-surface-solid)), var(--ll-surface-solid)); box-shadow: var(--ll-shadow-soft); padding: 14px 16px; }
    .ll-crq-stat .v { color: var(--ll-text); font-size: 1.7rem; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
    .ll-crq-stat .l { color: var(--ll-muted); font-size: .76rem; margin-top: 4px; }

    .ll-crq-panel { border: 1px solid var(--ll-border); border-radius: var(--ll-radius); background: var(--ll-surface-solid); box-shadow: var(--ll-shadow-soft); padding: 18px 20px; }
    .ll-crq-panel h2 { color: var(--ll-text); font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .ll-crq-panel h2 i { color: var(--ll-primary); }
    .ll-crq-panel .sub { color: var(--ll-muted); font-size: .82rem; margin: 0 0 14px; }

    .ll-crq-row { display: grid; grid-template-columns: 34px minmax(0,1fr) repeat(3, auto); gap: 14px; align-items: center; padding: 11px 13px; border: 1px solid var(--ll-border); border-radius: 12px; background: color-mix(in srgb, var(--ll-bg-soft) 40%, transparent); }
    .ll-crq-row + .ll-crq-row { margin-top: 8px; }
    .ll-crq-rank { color: var(--ll-muted); font-weight: 800; font-size: .9rem; text-align: center; font-variant-numeric: tabular-nums; }
    .ll-crq-handle { color: var(--ll-text); font-weight: 700; font-size: .98rem; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
    .ll-crq-handle small { display: block; color: var(--ll-muted); font-weight: 500; font-size: .72rem; }
    .ll-crq-metric { text-align: right; }
    .ll-crq-metric .v { color: var(--ll-text); font-weight: 800; font-size: 1.05rem; font-variant-numeric: tabular-nums; }
    .ll-crq-metric.accent .v { color: var(--ll-primary); }
    .ll-crq-metric .l { color: var(--ll-muted); font-size: .66rem; text-transform: uppercase; letter-spacing: .04em; }
    .ll-crq-empty { border: 1px dashed var(--ll-border); border-radius: 14px; padding: 22px; text-align: center; color: var(--ll-muted); }

    @media (max-width: 820px) {
        .ll-crq-row { grid-template-columns: 28px 1fr auto; }
        .ll-crq-row > .ll-crq-metric:nth-child(4) { display: none; }
    }
</style>

@php
    $totalHandles = count($totals);
    $totalUnique = collect($totals)->sum('unique_count');
    $totalAttempts = collect($totals)->sum('attempts');
    $totalEmails = collect($totals)->sum('emails');
@endphp

<div class="container-fluid content-inner mt-n5 py-0 ll-crq">
    <div class="ll-crq-head">
        <h1>Creator Requests</h1>
        <p>When someone visits a Livelatch handle that doesn't exist (e.g. <code>/@mrbeast</code>), they can ask us to invite that creator. This is the demand leaderboard — unique counts are deduped by a salted IP hash (no raw IPs stored), so treat them as a strong signal rather than an exact tally.</p>
    </div>

    <div class="ll-crq-stats">
        <div class="ll-crq-stat"><div class="v">{{ number_format($totalHandles) }}</div><div class="l">Creators requested</div></div>
        <div class="ll-crq-stat"><div class="v">{{ number_format($totalUnique) }}</div><div class="l">Unique requests</div></div>
        <div class="ll-crq-stat"><div class="v">{{ number_format($totalAttempts) }}</div><div class="l">Total attempts</div></div>
        <div class="ll-crq-stat"><div class="v">{{ number_format($totalEmails) }}</div><div class="l">Left an email</div></div>
    </div>

    <section class="ll-crq-panel">
        <h2><i class="bi bi-fire"></i> Most requested</h2>
        <p class="sub">Busiest handles first. "Unique" dedupes repeat visits from the same network; "attempts" counts every ask.</p>

        @forelse($totals as $i => $row)
            <div class="ll-crq-row">
                <div class="ll-crq-rank">{{ $i + 1 }}</div>
                <div class="ll-crq-handle">
                    {{ '@' . ($row['handle'] ?? '?') }}
                    <small>
                        @if(!empty($row['last_seen'])) last asked {{ \Illuminate\Support\Carbon::parse($row['last_seen'])->diffForHumans() }} @endif
                        @if(!empty($row['emails']) && $row['emails'] > 0) · {{ $row['emails'] }} want a heads-up @endif
                    </small>
                </div>
                <div class="ll-crq-metric accent"><div class="v">{{ number_format($row['unique_count'] ?? 0) }}</div><div class="l">unique</div></div>
                <div class="ll-crq-metric"><div class="v">{{ number_format($row['attempts'] ?? 0) }}</div><div class="l">attempts</div></div>
                <div class="ll-crq-metric"><div class="v">{{ number_format($row['emails'] ?? 0) }}</div><div class="l">emails</div></div>
            </div>
        @empty
            <div class="ll-crq-empty">
                No creator requests yet. When someone asks for a handle that isn't on Livelatch, it'll show up here.
            </div>
        @endforelse
    </section>
</div>
@endsection
