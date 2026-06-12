@extends('layouts.sidebar')

@section('content')
@php
    $socials = [
        ['name' => 'Discord', 'icon' => 'bi bi-discord', 'url' => 'https://discord.com/'],
        ['name' => 'TikTok', 'icon' => 'bi bi-tiktok', 'url' => 'https://www.tiktok.com/'],
        ['name' => 'Instagram', 'icon' => 'bi bi-instagram', 'url' => 'https://www.instagram.com/'],
        ['name' => 'Threads', 'icon' => 'bi bi-threads', 'url' => 'https://www.threads.net/'],
        ['name' => 'Bluesky', 'icon' => 'bi bi-clouds-fill', 'url' => 'https://bsky.app/'],
        ['name' => 'YouTube', 'icon' => 'bi bi-youtube', 'url' => 'https://www.youtube.com/'],
        ['name' => 'X', 'icon' => 'bi bi-twitter-x', 'url' => 'https://x.com/'],
        ['name' => 'Reddit', 'icon' => 'bi bi-reddit', 'url' => 'https://www.reddit.com/'],
    ];
@endphp

<style data-ll-socials-style>
    .ll-socials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
    }

    .ll-social-card {
        min-height: 112px;
        border: 1px solid var(--ll-border);
        border-radius: 16px;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        color: var(--ll-text);
        background: var(--ll-surface-solid);
        text-decoration: none;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .ll-social-card:hover,
    .ll-social-card:focus {
        color: var(--ll-text);
        border-color: rgba(98, 54, 255, 0.42);
        background: rgba(98, 54, 255, 0.06);
        transform: translateY(-2px);
    }

    .ll-social-icon {
        width: 46px;
        height: 46px;
        border-radius: 15px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        font-size: 1.25rem;
        flex: 0 0 auto;
    }

    .ll-social-copy {
        min-width: 0;
        flex: 1;
    }

    .ll-social-copy h4 {
        margin: 0;
        font-size: 1rem;
    }

    .ll-social-copy p {
        margin: 4px 0 0;
        color: var(--ll-muted);
        font-size: 0.86rem;
    }

    .ll-social-arrow {
        color: var(--ll-muted);
        flex: 0 0 auto;
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="mb-1">Livelatch Socials</h2>
                    <p class="text-muted mb-0">Quick links for the social platforms Livelatch will use.</p>
                </div>
            </div>

            <div class="ll-socials-grid">
                @foreach($socials as $social)
                    <a class="ll-social-card" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">
                        <span class="ll-social-icon">
                            <i class="{{ $social['icon'] }}"></i>
                        </span>
                        <span class="ll-social-copy">
                            <h4>{{ $social['name'] }}</h4>
                            <p>{{ parse_url($social['url'], PHP_URL_HOST) }}</p>
                        </span>
                        <span class="ll-social-arrow">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
