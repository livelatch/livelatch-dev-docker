@extends('layouts.sidebar')

@section('content')
@php
    $user = auth()->user();
    $tikTokAccount = $tikTokAccount ?? null;
    $tikTokAuthorizeUrl = $tikTokAuthorizeUrl ?? null;
    $linkedProviders = $user
        ? $user->socialAccounts()->pluck('provider_name')->map(fn ($provider) => strtolower($provider))->all()
        : [];
    $hasLatchId = $user && filled($user->supabase_user_id);
    $latchIdConfigured = filled(config('services.supabase.url')) && filled(config('services.supabase.anon_key'));
    $tikTokConnected = !empty($tikTokAccount);
    $connections = [
        ['name' => 'Google and YouTube', 'provider' => 'youtube', 'icon' => 'bi bi-google', 'enabled' => true],
        ['name' => 'Discord', 'provider' => 'discord', 'icon' => 'bi bi-discord', 'enabled' => true],
        ['name' => 'TikTok', 'provider' => 'tiktok', 'icon' => 'bi bi-tiktok', 'enabled' => true],
        ['name' => 'Instagram', 'provider' => 'instagram', 'icon' => 'bi bi-instagram', 'enabled' => false],
        ['name' => 'X', 'provider' => 'x', 'icon' => 'bi bi-twitter-x', 'enabled' => false],
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

    .ll-latchid-status {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        padding: 6px 10px;
        color: var(--ll-muted);
        background: color-mix(in srgb, var(--ll-text) 7%, transparent);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .ll-latchid-status.is-connected {
        color: #0f5132;
        background: rgba(25, 135, 84, 0.16);
    }

    .ll-latchid-avatar {
        width: 44px;
        height: 44px;
        border-radius: 15px;
        object-fit: cover;
        flex: 0 0 auto;
        background: color-mix(in srgb, var(--ll-text) 7%, transparent);
    }

    .ll-latchid-meta {
        color: var(--ll-muted);
        font-size: 0.82rem;
    }

    @media (max-width: 767.98px) {
        .ll-latchid-hero {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-latchid-page">
        @if(request('latchid') === 'linked')
            <div class="alert alert-success mb-0">
                LatchID connection updated.
            </div>
        @endif
        @if(request('tiktok_linked') === '1')
            <div class="alert alert-success mb-0">
                TikTok connected successfully.
            </div>
        @endif
        @if(request('tiktok_error') === '1')
            <div class="alert alert-danger mb-0">
                TikTok connection failed. Please try again.
            </div>
        @endif
        <div class="alert alert-danger mb-0 d-none" data-latchid-link-error role="alert"></div>

        <section class="ll-latchid-hero">
            <div class="d-flex align-items-center gap-3">
                <span class="ll-latchid-mark">
                    <i class="bi bi-person-vcard-fill"></i>
                </span>
                <div>
                    <h2>LatchID</h2>
                    <p>Manage social connections backed by Supabase identity services. Google also grants the YouTube access needed for live stream and video features.</p>
                </div>
            </div>
            <button
                type="button"
                class="btn btn-primary"
                data-latchid-connect="youtube"
                @disabled(!$latchIdConfigured)
            >
                <i class="bi bi-plus-circle"></i>
                Connect Google
            </button>
        </section>

        <section class="ll-latchid-grid">
            @foreach($connections as $connection)
                @php
                    $isConnected = in_array($connection['provider'], $linkedProviders, true)
                        || ($connection['provider'] === 'youtube' && in_array('google', $linkedProviders, true))
                        || ($connection['provider'] === 'youtube' && $hasLatchId && empty($linkedProviders))
                        || ($connection['provider'] === 'tiktok' && $tikTokConnected);
                @endphp
                <article class="ll-latchid-card">
                    <div class="ll-latchid-card-top">
                        @if($connection['provider'] === 'tiktok' && !empty($tikTokAccount['avatar_url']))
                            <img
                                class="ll-latchid-avatar"
                                src="{{ $tikTokAccount['avatar_url'] }}"
                                alt=""
                                referrerpolicy="no-referrer"
                            >
                        @else
                            <span class="ll-latchid-icon">
                                <i class="{{ $connection['icon'] }}"></i>
                            </span>
                        @endif
                        <div>
                            <h3>{{ $connection['name'] }}</h3>
                            <p>
                                @if($connection['enabled'])
                                    @if($connection['provider'] === 'youtube')
                                        Link Google and grant YouTube read access for future live stream and video features.
                                    @else
                                        @if($connection['provider'] === 'tiktok')
                                            @if($tikTokConnected)
                                                {{ $tikTokAccount['display_name'] ?? 'TikTok account linked' }}
                                            @else
                                                Link TikTok through Login Kit for future profile and creator features.
                                            @endif
                                        @else
                                            Link this provider to LatchID for future sign-in options.
                                        @endif
                                    @endif
                                @else
                                    Future LatchID connection.
                                @endif
                            </p>
                            @if($connection['provider'] === 'tiktok' && $tikTokConnected && !empty($tikTokAccount['linked_at']))
                                <div class="ll-latchid-meta">
                                    Linked {{ \Carbon\Carbon::parse($tikTokAccount['linked_at'])->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <span class="ll-latchid-status @if($isConnected) is-connected @endif">
                        <i class="bi @if($isConnected) bi-check-circle-fill @else bi-clock @endif"></i>
                        @if($isConnected)
                            Connected
                        @elseif($connection['enabled'])
                            Available
                        @else
                            Coming soon
                        @endif
                    </span>
                    @if($connection['provider'] === 'tiktok')
                        @if(!$hasLatchId)
                            <button type="button" class="btn btn-light w-100" disabled>
                                LatchID unavailable
                            </button>
                            <p class="mb-0">LatchID is not available for this account yet.</p>
                        @elseif($tikTokConnected)
                            <button type="button" class="btn btn-light w-100" disabled>
                                TikTok connected
                            </button>
                        @elseif(!empty($tikTokAuthorizeUrl))
                            <a class="btn btn-light w-100" href="{{ $tikTokAuthorizeUrl }}">
                                Connect TikTok
                            </a>
                        @else
                            <button type="button" class="btn btn-light w-100" disabled>
                                TikTok unavailable
                            </button>
                        @endif
                    @elseif($connection['enabled'])
                        <button
                            type="button"
                            class="btn btn-light w-100"
                            data-latchid-connect="{{ $connection['provider'] }}"
                            @disabled(!$latchIdConfigured)
                        >
                            @if($isConnected)
                                Reconnect {{ $connection['name'] }}
                            @else
                                Connect {{ $connection['name'] }}
                            @endif
                        </button>
                    @else
                        <button type="button" class="btn btn-light w-100" disabled>
                            Manage connection
                        </button>
                    @endif
                </article>
            @endforeach
        </section>

        <div class="ll-latchid-note">
            TikTok linking is handled through a Supabase Edge Function and stored in LatchID. YouTube API access uses Google OAuth through Supabase Auth with read-only YouTube scopes. Disconnect flows, consent audit history, and richer provider metadata still need a dedicated account-management pass before launch.
        </div>
    </div>
</div>

@if($latchIdConfigured)
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        (function () {
            'use strict';

            var config = {
                supabaseUrl: @json(config('services.supabase.url')),
                supabaseAnonKey: @json(config('services.supabase.anon_key')),
                callbackBaseUrl: @json(url('/callback')),
                returnPath: '/studio/latchid?latchid=linked'
            };

            function showError(message) {
                var box = document.querySelector('[data-latchid-link-error]');

                if (!box) {
                    return;
                }

                box.textContent = message;
                box.classList.remove('d-none');
            }

            function client() {
                if (!window.supabase || !window.supabase.createClient) {
                    throw new Error('Supabase browser client failed to load.');
                }

                return window.supabase.createClient(config.supabaseUrl, config.supabaseAnonKey, {
                    auth: {
                        detectSessionInUrl: false,
                        persistSession: true,
                        autoRefreshToken: true
                    }
                });
            }

            function callbackUrl(provider) {
                return config.callbackBaseUrl
                    + '/' + encodeURIComponent(provider)
                    + '?redirect_to=' + encodeURIComponent(config.returnPath);
            }

            function oauthProvider(provider) {
                if (provider === 'youtube') {
                    return 'google';
                }

                return provider;
            }

            function oauthOptions(provider, redirectTo) {
                var options = {
                    redirectTo: redirectTo
                };

                if (provider === 'discord') {
                    options.scopes = 'identify email';
                }

                if (provider === 'youtube') {
                    options.scopes = 'https://www.googleapis.com/auth/youtube.readonly';
                    options.queryParams = {
                        access_type: 'offline',
                        prompt: 'consent'
                    };
                }

                return options;
            }

            document.addEventListener('click', async function (event) {
                var button = event.target.closest('[data-latchid-connect]');

                if (!button) {
                    return;
                }

                var provider = button.getAttribute('data-latchid-connect');
                var authClient = client();
                var redirectTo = callbackUrl(provider);
                var providerForOAuth = oauthProvider(provider);
                var options = oauthOptions(provider, redirectTo);

                button.disabled = true;
                button.setAttribute('aria-busy', 'true');

                try {
                    var sessionResult = await authClient.auth.getSession();
                    var hasSupabaseSession = sessionResult.data && sessionResult.data.session;
                    var result;

                    if (provider !== 'youtube' && hasSupabaseSession && authClient.auth.linkIdentity) {
                        result = await authClient.auth.linkIdentity({
                            provider: providerForOAuth,
                            options: options
                        });
                    } else {
                        result = await authClient.auth.signInWithOAuth({
                            provider: providerForOAuth,
                            options: options
                        });
                    }

                    if (result.error) {
                        throw result.error;
                    }
                } catch (error) {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                    showError(error && error.message ? error.message : 'Could not connect this LatchID provider.');
                }
            });
        }());
    </script>
@endif
@endsection
