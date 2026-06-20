<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Signing in with LatchID</title>
    @include('layouts.posthog')
    <style>
        :root {
            color-scheme: dark;
            --bg: #050814;
            --panel: rgba(13, 25, 54, 0.9);
            --line: rgba(255, 255, 255, 0.16);
            --text: #f7fbff;
            --muted: #b6c4da;
            --accent: #18d5ff;
            --danger: #ff6b8a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 20% 10%, rgba(24, 213, 255, 0.2), transparent 28rem),
                radial-gradient(circle at 80% 15%, rgba(140, 85, 255, 0.18), transparent 30rem),
                linear-gradient(145deg, #050814 0%, #071126 48%, #0b1730 100%);
        }

        main {
            width: min(100%, 460px);
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: var(--panel);
            box-shadow: 0 28px 90px rgba(0, 0, 0, 0.38);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 1.45rem;
            letter-spacing: 0;
        }

        p {
            margin: 0;
            color: var(--muted);
        }

        .status {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 20px;
            color: var(--muted);
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.24);
            border-top-color: var(--accent);
            border-radius: 999px;
            animation: spin 800ms linear infinite;
        }

        .error {
            margin-top: 20px;
            color: var(--danger);
            overflow-wrap: anywhere;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<main>
    <h1>Signing in with LatchID</h1>
    <p>Completing your {{ ucfirst($provider) }} authentication and opening Livelatch.</p>
    <div class="status" id="status"><span class="spinner" aria-hidden="true"></span><span>Checking Supabase session...</span></div>
    <div class="error" id="error" role="alert" hidden></div>
</main>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
    (function () {
        'use strict';

        var config = {
            supabaseUrl: @json($supabaseUrl),
            supabaseAnonKey: @json($supabaseAnonKey),
            sessionEndpoint: @json(url('/api/latchid/session')),
            dashboardUrl: @json(url('/dashboard')),
            provider: @json($provider)
        };

        var status = document.getElementById('status');
        var errorBox = document.getElementById('error');
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showError(message, detail) {
            if (status) {
                status.hidden = true;
            }

            var text = detail ? message + ' ' + detail : message;
            errorBox.textContent = text;
            errorBox.hidden = false;
            console.error('LatchID callback error:', text);
        }

        function urlAuthError() {
            var search = new URLSearchParams(window.location.search);
            var hash = new URLSearchParams(window.location.hash.replace(/^#/, ''));
            return search.get('error_description') || hash.get('error_description') || search.get('error') || hash.get('error');
        }

        function callbackRedirectTo() {
            var search = new URLSearchParams(window.location.search);
            var redirectTo = search.get('redirect_to') || '';

            if (redirectTo.indexOf('/') !== 0 || redirectTo.indexOf('//') === 0 || redirectTo.indexOf('\\') !== -1) {
                return '';
            }

            return redirectTo;
        }

        async function completeSession() {
            var authError = urlAuthError();
            if (authError) {
                throw new Error(authError);
            }

            if (!config.supabaseUrl || !config.supabaseAnonKey) {
                throw new Error('Supabase URL or anon key is missing.');
            }

            if (!window.supabase || !window.supabase.createClient) {
                throw new Error('Supabase browser client failed to load.');
            }

            var client = window.supabase.createClient(config.supabaseUrl, config.supabaseAnonKey, {
                auth: {
                    // PKCE (authorization-code) flow. The verifier stored in
                    // localStorage when sign in started (same origin) is read here
                    // to exchange the ?code= for a session. Keeps tokens out of URLs.
                    flowType: 'pkce',
                    // We exchange the OAuth code manually below, so disable the
                    // automatic URL detection. Running both races to consume the
                    // same single-use code and intermittently fails the sign in.
                    detectSessionInUrl: false,
                    persistSession: true,
                    autoRefreshToken: true
                }
            });

            var code = new URLSearchParams(window.location.search).get('code');
            if (code) {
                var exchange = await client.auth.exchangeCodeForSession(code);
                if (exchange.error) {
                    // The code may already have been consumed (page reload, or an
                    // earlier attempt that established a session). Fall back to an
                    // existing session before treating this as a failure.
                    var existing = await client.auth.getSession();
                    if (!existing.data || !existing.data.session) {
                        throw exchange.error;
                    }
                }
                window.history.replaceState({}, document.title, '/callback/' + encodeURIComponent(config.provider));
            }

            var sessionResult = await client.auth.getSession();
            if (sessionResult.error) {
                throw sessionResult.error;
            }

            var session = sessionResult.data.session;
            if (!session || !session.access_token) {
                throw new Error('Supabase returned no active user session.');
            }

            var userResult = await client.auth.getUser();
            if (userResult.error) {
                throw userResult.error;
            }

            var user = userResult.data.user;
            if (!user) {
                throw new Error('Supabase returned no user.');
            }

            var metadata = user.user_metadata || {};
            var response = await fetch(config.sessionEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    supabase_user_id: user.id,
                    email: user.email,
                    name: metadata.full_name || metadata.name || '',
                    avatar_url: metadata.avatar_url || '',
                    access_token: session.access_token,
                    oauth_provider: config.provider,
                    provider_token: session.provider_token || '',
                    provider_refresh_token: session.provider_refresh_token || '',
                    provider_expires_at: session.expires_at || null,
                    provider_scopes: session.provider_scopes || '',
                    redirect_to: callbackRedirectTo()
                })
            });

            var payload = await response.json().catch(function () {
                return {};
            });

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Laravel session endpoint failed.');
            }

            window.location.assign(payload.redirect || config.dashboardUrl);
        }

        completeSession().catch(function (error) {
            showError('Could not complete LatchID sign in.', error && error.message ? error.message : String(error));
        });
    }());
</script>
</body>
</html>
