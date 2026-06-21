@extends('layouts.sidebar')

@section('content')
@php
    $socials = $socials ?? [];
    $isAdmin = auth()->user() && auth()->user()->role === 'admin';

    // Which third-party embed scripts to load (once) based on active platforms.
    $needScripts = [];

    $youtubeId = function (string $url): ?string {
        if (preg_match('~(?:youtu\.be/|/shorts/|/embed/|[?&]v=)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }
        return null;
    };
    $tiktokId = function (string $url): ?string {
        if (preg_match('~/video/(\d+)~', $url, $m)) {
            return $m[1];
        }
        return null;
    };
@endphp

<style data-ll-socials-style>
    .ll-socials-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
    .ll-socials-head h2 { margin: 0 0 4px; }
    .ll-socials-head p { margin: 0; color: var(--ll-muted); }

    .ll-socials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 18px;
        align-items: start;
    }

    .ll-social-card {
        border: 1px solid var(--ll-border);
        border-radius: 18px;
        background: var(--ll-surface-solid);
        box-shadow: var(--ll-shadow-soft);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .ll-social-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--ll-border);
    }
    .ll-social-icon {
        width: 42px; height: 42px; flex: 0 0 auto;
        border-radius: 12px; display: grid; place-items: center;
        color: #fff; font-size: 1.15rem;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }
    .ll-social-meta { min-width: 0; flex: 1; }
    .ll-social-meta h4 { margin: 0; font-size: 1rem; }
    .ll-social-meta p { margin: 2px 0 0; color: var(--ll-muted); font-size: 0.82rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ll-social-follow {
        flex: 0 0 auto;
        display: inline-flex; align-items: center; gap: 6px;
        min-height: 38px; padding: 0 14px;
        border-radius: 999px; font-weight: 600; font-size: 0.9rem;
        text-decoration: none; color: #fff;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }
    .ll-social-follow:hover { color: #fff; opacity: 0.92; }

    .ll-social-embed { padding: 14px 16px; }
    .ll-social-embed .ratio-16x9 { position: relative; width: 100%; padding-top: 56.25%; }
    .ll-social-embed .ratio-16x9 iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; border-radius: 12px; }
    .ll-social-embed blockquote { margin: 0 auto !important; }

    .ll-social-empty { padding: 24px 16px; text-align: center; color: var(--ll-muted); font-size: 0.9rem; }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card rounded">
        <div class="card-body">
            <div class="ll-socials-head">
                <div>
                    <h2>Livelatch Socials</h2>
                    <p>Follow Livelatch and catch our latest posts across the platforms we use.</p>
                </div>
                @if($isAdmin)
                    <a href="{{ route('admin.socials') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit social links
                    </a>
                @endif
            </div>

            @if(empty($socials))
                <div class="ll-social-empty">
                    No social links yet.
                    @if($isAdmin)
                        <a href="{{ route('admin.socials') }}">Add them in admin</a>.
                    @endif
                </div>
            @else
                <div class="ll-socials-grid">
                    @foreach($socials as $social)
                        @php
                            $post = $social['featured_post_url'] ?? '';
                            $host = $social['profile_url'] ? parse_url($social['profile_url'], PHP_URL_HOST) : '';
                        @endphp
                        <article class="ll-social-card">
                            <div class="ll-social-head">
                                <span class="ll-social-icon"><i class="{{ $social['icon'] }}"></i></span>
                                <div class="ll-social-meta">
                                    <h4>{{ $social['name'] }}</h4>
                                    <p>{{ $social['handle'] ?: $host }}</p>
                                </div>
                                <a class="ll-social-follow" href="{{ $social['profile_url'] }}" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-plus-lg"></i> Follow
                                </a>
                            </div>

                            @if($post)
                                <div class="ll-social-embed">
                                    @switch($social['embed'])
                                        @case('youtube')
                                            @php($vid = $youtubeId($post))
                                            @if($vid)
                                                <div class="ratio-16x9">
                                                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $vid }}"
                                                            title="{{ $social['name'] }} video" loading="lazy"
                                                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                </div>
                                            @endif
                                            @break

                                        @case('tiktok')
                                            @php($needScripts['tiktok'] = true)
                                            <blockquote class="tiktok-embed" cite="{{ $post }}"
                                                @if($tiktokId($post)) data-video-id="{{ $tiktokId($post) }}" @endif
                                                style="max-width: 605px; min-width: 280px;">
                                                <a href="{{ $post }}">View on TikTok</a>
                                            </blockquote>
                                            @break

                                        @case('instagram')
                                            @php($needScripts['instagram'] = true)
                                            <blockquote class="instagram-media" data-instgrm-permalink="{{ $post }}" data-instgrm-version="14"></blockquote>
                                            @break

                                        @case('twitter')
                                            @php($needScripts['twitter'] = true)
                                            <blockquote class="twitter-tweet"><a href="{{ $post }}">View on X</a></blockquote>
                                            @break

                                        @case('threads')
                                            @php($needScripts['threads'] = true)
                                            <blockquote class="text-post-media" data-text-post-permalink="{{ $post }}"></blockquote>
                                            @break

                                        @case('reddit')
                                            @php($needScripts['reddit'] = true)
                                            <blockquote class="reddit-embed-bq" style="height:316px"><a href="{{ $post }}">View on Reddit</a></blockquote>
                                            @break
                                    @endswitch
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($needScripts['tiktok']))
    <script async src="https://www.tiktok.com/embed.js"></script>
@endif
@if(!empty($needScripts['instagram']))
    <script async src="//www.instagram.com/embed.js"></script>
@endif
@if(!empty($needScripts['twitter']))
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
@endif
@if(!empty($needScripts['threads']))
    <script async src="https://www.threads.net/embed.js"></script>
@endif
@if(!empty($needScripts['reddit']))
    <script async src="https://embed.reddit.com/widgets.js" charset="UTF-8"></script>
@endif
@endsection
