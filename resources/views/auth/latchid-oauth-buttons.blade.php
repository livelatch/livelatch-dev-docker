@php
    $latchIdProviders = [
        ['name' => 'Google', 'provider' => 'google', 'icon' => 'bi bi-google'],
        ['name' => 'Discord', 'provider' => 'discord', 'icon' => 'bi bi-discord'],
    ];

    $latchIdConfigured = filled(config('services.supabase.url')) && filled(config('services.supabase.anon_key'));
@endphp

@if($latchIdConfigured)
    <p class="text-center my-3">or continue with LatchID</p>
    <div class="d-grid gap-2">
        @foreach($latchIdProviders as $provider)
            <button
                type="button"
                class="btn btn-outline-primary ll-latchid-auth-button"
                data-latchid-provider="{{ $provider['provider'] }}"
            >
                <i class="{{ $provider['icon'] }}"></i>
                Continue with {{ $provider['name'] }}
            </button>
        @endforeach
    </div>
    <div class="alert alert-danger mt-3 d-none" data-latchid-auth-error role="alert"></div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
        <script>
            (function () {
                'use strict';

                var config = {
                    supabaseUrl: @json(config('services.supabase.url')),
                    supabaseAnonKey: @json(config('services.supabase.anon_key')),
                    callbackBaseUrl: @json(url('/callback'))
                };

                function errorBox() {
                    return document.querySelector('[data-latchid-auth-error]');
                }

                function showError(message) {
                    var box = errorBox();

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
                            // PKCE (authorization-code) flow: the provider returns
                            // ?code= instead of a #access_token fragment, so tokens
                            // never land in the URL. Must match the callback client.
                            flowType: 'pkce',
                            detectSessionInUrl: false,
                            persistSession: true,
                            autoRefreshToken: true
                        }
                    });
                }

                document.addEventListener('click', function (event) {
                    var button = event.target.closest('[data-latchid-provider]');

                    if (!button) {
                        return;
                    }

                    var provider = button.getAttribute('data-latchid-provider');
                    var redirectTo = config.callbackBaseUrl + '/' + encodeURIComponent(provider);

                    // Persist the marketing opt-in choice across the OAuth redirect so
                    // the callback can pass it to the session endpoint for new users.
                    // Absent checkbox (e.g. the login page) leaves the stored value
                    // untouched and the server falls back to the opted-in default.
                    try {
                        var marketingBox = document.querySelector('[data-latchid-marketing]');
                        if (marketingBox) {
                            window.localStorage.setItem('ll_marketing_opt_in', marketingBox.checked ? '1' : '0');
                        }
                    } catch (storageError) {
                        // Ignore storage failures; default applies server-side.
                    }

                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');

                    client().auth.signInWithOAuth({
                        provider: provider,
                        options: {
                            redirectTo: redirectTo,
                            scopes: provider === 'discord' ? 'identify email' : undefined
                        }
                    }).then(function (result) {
                        if (result.error) {
                            throw result.error;
                        }
                    }).catch(function (error) {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                        showError(error && error.message ? error.message : 'Could not start LatchID sign in.');
                    });
                });
            }());
        </script>
    @endonce
@endif
