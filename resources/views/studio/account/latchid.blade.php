@extends('layouts.sidebar')

@section('content')
@php
    $connections = [
        ['name' => 'Google', 'icon' => 'bi bi-google', 'status' => 'Connected through LatchID sign-in'],
        ['name' => 'Discord', 'icon' => 'bi bi-discord', 'status' => 'Coming soon'],
        ['name' => 'TikTok', 'icon' => 'bi bi-tiktok', 'status' => 'Coming soon'],
        ['name' => 'Instagram', 'icon' => 'bi bi-instagram', 'status' => 'Coming soon'],
        ['name' => 'YouTube', 'icon' => 'bi bi-youtube', 'status' => 'Coming soon'],
        ['name' => 'X', 'icon' => 'bi bi-twitter-x', 'status' => 'Coming soon'],
    ];
@endphp

<style data-ll-latchid-style>
    .ll-latchid-page {
        display: grid;
        gap: 18px;
    }

    .ll-latchid-hero,
    .ll-latchid-card {
        border: 1px solid var(--ll-border);
        border-radius: var(--ll-radius);
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
    }

    .ll-latchid-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: clamp(20px, 3vw, 32px);
    }

    .ll-latchid-mark {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 20px;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 1.45rem;
    }

    .ll-latchid-hero h2,
    .ll-latchid-hero p {
        margin: 0;
    }

    .ll-latchid-hero p {
        color: var(--ll-muted);
        margin-top: 6px;
        max-width: 760px;
    }

    .ll-latchid-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 14px;
    }

    .ll-latchid-card {
        display: grid;
        gap: 14px;
        padding: 18px;
    }

    .ll-latchid-card-top {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ll-latchid-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 15px;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 1.15rem;
        flex: 0 0 auto;
    }

    .ll-latchid-card h3,
    .ll-latchid-card p {
        margin: 0;
    }

    .ll-latchid-card p {
        color: var(--ll-muted);
        font-size: 0.9rem;
    }

    .ll-latchid-note {
        border: 1px dashed color-mix(in srgb, var(--ll-primary) 42%, var(--ll-border));
        border-radius: var(--ll-radius);
        padding: 18px;
        color: var(--ll-muted);
        background: color-mix(in srgb, var(--ll-primary) 7%, transparent);
    }

    @media (max-width: 767.98px) {
        .ll-latchid-hero {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-latchid-page">
        <section class="ll-latchid-hero">
            <div class="d-flex align-items-center gap-3">
                <span class="ll-latchid-mark">
                    <i class="bi bi-person-vcard-fill"></i>
                </span>
                <div>
                    <h2>LatchID</h2>
                    <p>Manage social connections that will eventually sync through Supabase-backed LatchID identity services.</p>
                </div>
            </div>
            <button type="button" class="btn btn-primary" disabled>
                <i class="bi bi-plus-circle"></i>
                Connect account
            </button>
        </section>

        <section class="ll-latchid-grid">
            @foreach($connections as $connection)
                <article class="ll-latchid-card">
                    <div class="ll-latchid-card-top">
                        <span class="ll-latchid-icon">
                            <i class="{{ $connection['icon'] }}"></i>
                        </span>
                        <div>
                            <h3>{{ $connection['name'] }}</h3>
                            <p>{{ $connection['status'] }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light w-100" disabled>
                        Manage connection
                    </button>
                </article>
            @endforeach
        </section>

        <div class="ll-latchid-note">
            LatchID social connection management is a placeholder in this release. The future implementation should call Supabase-backed identity and connection tables, support disconnect flows, and record consent or sync status where required.
        </div>
    </div>
</div>
@endsection
