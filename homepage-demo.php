<?php
if (!function_exists('ll_public_env')) {
    function ll_public_env($key, $default = '')
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        $envPath = __DIR__ . '/.env';
        if (!is_readable($envPath)) {
            return $default;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$envKey, $envValue] = explode('=', $line, 2);
            if (trim($envKey) !== $key) {
                continue;
            }

            return trim($envValue, " \t\n\r\0\x0B\"'");
        }

        return $default;
    }
}

$llSupabaseUrl = ll_public_env('SUPABASE_URL');
$llSupabaseAnonKey = ll_public_env('SUPABASE_ANON_KEY');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Livelatch Alpha</title>
    <meta name="description" content="Livelatch alpha is a creator profile and audience toolkit with LatchID, themes, analytics, and upcoming LatchDeck experiences.">
    <?php
    if (function_exists('view')) {
        echo view('layouts.posthog')->render();
    }
    ?>
    <style>
        :root {
            --ll-bg: #050814;
            --ll-bg-soft: #0a1226;
            --ll-card: rgba(255, 255, 255, .08);
            --ll-card-strong: rgba(255, 255, 255, .12);
            --ll-border: rgba(255, 255, 255, .16);
            --ll-text: #f8fbff;
            --ll-muted: #b7c4d8;
            --ll-primary: #18d5ff;
            --ll-primary-2: #8c55ff;
            --ll-green: #46f2bd;
            --ll-ink: #071126;
            --ll-radius: 24px;
            --ll-max: 1160px;
            color-scheme: dark;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ll-text);
            background:
                radial-gradient(circle at 15% 8%, rgba(24, 213, 255, .24), transparent 30rem),
                radial-gradient(circle at 85% 4%, rgba(140, 85, 255, .22), transparent 34rem),
                linear-gradient(145deg, #050814, #071126 48%, #0b1730);
            line-height: 1.5;
            overflow-x: hidden;
        }

        a { color: inherit; }
        button { font: inherit; border: 0; }
        .ll-shell {
            width: min(var(--ll-max), calc(100% - 40px));
            margin: 0 auto;
        }

        .ll-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            border-bottom: 1px solid var(--ll-border);
            background: rgba(5, 8, 20, .78);
            backdrop-filter: blur(22px);
        }

        .ll-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 74px;
            gap: 18px;
        }

        .ll-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            text-decoration: none;
        }

        .ll-logo {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            color: #fff;
            box-shadow: 0 18px 50px rgba(24, 213, 255, .24);
        }

        .ll-nav-links,
        .ll-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ll-nav-links a {
            color: var(--ll-muted);
            border-radius: 999px;
            font-weight: 750;
            padding: 10px 12px;
            text-decoration: none;
        }

        .ll-nav-links a:hover { color: var(--ll-text); background: rgba(255,255,255,.08); }

        .ll-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 44px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 850;
            padding: 0 18px;
            text-decoration: none;
            transition: transform 160ms ease, background 160ms ease, border-color 160ms ease;
            white-space: nowrap;
        }

        .ll-button:hover { transform: translateY(-1px); }
        .ll-button-primary {
            background: linear-gradient(135deg, var(--ll-primary), #2d8cff 48%, var(--ll-primary-2));
            color: #fff;
            box-shadow: 0 18px 46px rgba(24, 213, 255, .26);
        }
        .ll-button-ghost {
            border: 1px solid var(--ll-border);
            background: rgba(255,255,255,.07);
            color: var(--ll-text);
        }

        .ll-hero { padding: 84px 0 70px; }
        .ll-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, .82fr);
            gap: 54px;
            align-items: center;
        }

        .ll-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 9px;
            border: 1px solid var(--ll-border);
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: #dff6ff;
            font-size: .84rem;
            font-weight: 850;
            padding: 8px 12px;
        }

        .ll-kicker::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--ll-green);
            box-shadow: 0 0 18px rgba(70, 242, 189, .85);
        }

        h1, h2, h3, p { margin-top: 0; }
        .ll-hero h1 {
            margin: 22px 0 18px;
            max-width: 760px;
            font-size: clamp(3rem, 7vw, 6.2rem);
            line-height: .94;
            letter-spacing: 0;
        }

        .ll-gradient {
            color: transparent;
            background: linear-gradient(135deg, #fff, #b7f2ff 38%, #75a8ff 68%, #ffd2f0);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .ll-copy {
            max-width: 660px;
            color: var(--ll-muted);
            font-size: clamp(1.05rem, 2vw, 1.25rem);
            margin-bottom: 28px;
        }

        .ll-proof-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 28px;
            max-width: 680px;
        }

        .ll-proof,
        .ll-card {
            border: 1px solid var(--ll-border);
            background: var(--ll-card);
            border-radius: var(--ll-radius);
            box-shadow: 0 28px 90px rgba(0,0,0,.24);
        }

        .ll-proof { padding: 14px; }
        .ll-proof strong { display: block; }
        .ll-proof span { color: var(--ll-muted); display: block; font-size: .84rem; margin-top: 3px; }

        .ll-profile-demo {
            position: relative;
            border-radius: 38px;
            border: 1px solid var(--ll-border);
            background: linear-gradient(145deg, rgba(255,255,255,.14), rgba(255,255,255,.04));
            box-shadow: 0 30px 100px rgba(0,0,0,.36);
            padding: 14px;
        }

        .ll-profile-url {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--ll-muted);
            font-size: .82rem;
            font-weight: 800;
            padding: 0 8px 12px;
        }

        .ll-profile-frame {
            width: 100%;
            height: 560px;
            border: 0;
            border-radius: 28px;
            background: #fff;
        }

        .ll-section { padding: 72px 0; }
        .ll-section-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, .55fr);
            gap: 24px;
            align-items: end;
            margin-bottom: 24px;
        }
        .ll-section h2 {
            font-size: clamp(2rem, 4vw, 3.6rem);
            line-height: 1;
            margin-bottom: 0;
        }
        .ll-section-copy { color: var(--ll-muted); margin-bottom: 0; }

        .ll-alpha-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .ll-alpha-card { padding: 20px; }
        .ll-alpha-card h3 { margin-bottom: 8px; }
        .ll-alpha-card p { color: var(--ll-muted); margin-bottom: 0; }

        .ll-plan-table {
            overflow: hidden;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: rgba(255,255,255,.07);
        }
        .ll-plan-row {
            display: grid;
            grid-template-columns: minmax(170px, .8fr) repeat(2, minmax(0, 1fr));
            border-bottom: 1px solid var(--ll-border);
        }
        .ll-plan-row:last-child { border-bottom: 0; }
        .ll-plan-row > div {
            padding: 16px;
            border-right: 1px solid var(--ll-border);
        }
        .ll-plan-row > div:last-child { border-right: 0; }
        .ll-plan-head { background: rgba(255,255,255,.1); font-weight: 900; }
        .ll-plan-name { color: var(--ll-muted); font-weight: 800; }
        .ll-price {
            display: block;
            color: #fff;
            font-size: 2rem;
            font-weight: 950;
        }
        .ll-plan-note { color: var(--ll-muted); font-size: .9rem; }

        .ll-legal-panel {
            min-height: 168px;
            margin-top: 18px;
        }
        .ll-legal-card {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: rgba(255,255,255,.07);
            padding: 20px;
        }
        .ll-legal-card h2 { margin-bottom: 10px; }
        .ll-legal-card p { color: var(--ll-muted); }

        .ll-footer {
            border-top: 1px solid var(--ll-border);
            padding: 42px 0;
            background: rgba(5,8,20,.62);
        }
        .ll-footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: start;
        }
        .ll-footer p { color: var(--ll-muted); margin-bottom: 0; }
        .ll-footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .ll-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            place-items: center;
            padding: 20px;
            background: rgba(0,0,0,.66);
        }
        .ll-modal[aria-hidden="false"] { display: grid; }
        .ll-modal-panel {
            width: min(520px, 100%);
            border: 1px solid var(--ll-border);
            border-radius: 28px;
            background: #0b142a;
            box-shadow: 0 30px 120px rgba(0,0,0,.48);
            padding: 24px;
        }
        .ll-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: start;
        }
        .ll-close {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: #fff;
            cursor: pointer;
            font-size: 1.4rem;
        }
        .ll-provider {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--ll-border);
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            color: var(--ll-text);
            cursor: pointer;
            font-weight: 850;
            padding: 14px;
        }
        .ll-demo-message {
            display: none;
            margin-top: 14px;
            border: 1px solid var(--ll-border);
            border-radius: 16px;
            background: rgba(255,255,255,.08);
            color: var(--ll-muted);
            padding: 12px;
        }
        .ll-demo-message.ll-visible { display: block; }

        @media (max-width: 980px) {
            .ll-hero-grid,
            .ll-section-header,
            .ll-footer-grid {
                grid-template-columns: 1fr;
            }
            .ll-alpha-grid,
            .ll-proof-row {
                grid-template-columns: 1fr;
            }
            .ll-nav-links { display: none; }
            .ll-profile-frame { height: 520px; }
        }

        @media (max-width: 720px) {
            .ll-plan-row {
                grid-template-columns: 1fr;
            }
            .ll-plan-row > div {
                border-right: 0;
                border-bottom: 1px solid var(--ll-border);
            }
            .ll-plan-row > div:last-child { border-bottom: 0; }
            .ll-hero { padding-top: 54px; }
        }
    </style>
</head>
<body>
<div class="ll-page">
    <header class="ll-nav">
        <div class="ll-shell ll-nav-inner">
            <a class="ll-brand" href="/">
                <span class="ll-logo">LL</span>
                <span>Livelatch</span>
            </a>

            <nav class="ll-nav-links" aria-label="Main navigation">
                <a href="#alpha">Alpha</a>
                <a href="#profile-demo">Demo</a>
                <a href="#plans">Plans</a>
                <a href="/studio/docs">Documentation</a>
            </nav>

            <div class="ll-nav-actions">
                <button class="ll-button ll-button-primary" type="button" data-ll-open-modal>Get started</button>
            </div>
        </div>
    </header>

    <main>
        <section class="ll-hero">
            <div class="ll-shell ll-hero-grid">
                <div>
                    <span class="ll-kicker">Livelatch Alpha</span>
                    <h1>One creator profile for every moment you make.</h1>
                    <p class="ll-copy">
                        Livelatch Alpha is the early creator workspace for profile links, themes, LatchID connections, live analytics, and the first pieces of LatchDeck.
                    </p>
                    <div class="ll-nav-actions">
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal>Get started</button>
                        <a class="ll-button ll-button-ghost" href="/@alex">Open @alex</a>
                    </div>
                    <div class="ll-proof-row">
                        <div class="ll-proof"><strong>Theme Studio</strong><span>Build a profile that feels like you.</span></div>
                        <div class="ll-proof"><strong>LatchID</strong><span>Connect identity and creator accounts.</span></div>
                        <div class="ll-proof"><strong>Live clicks</strong><span>See what your audience presses.</span></div>
                    </div>
                </div>

                <aside class="ll-profile-demo" id="profile-demo" aria-label="Demo profile preview">
                    <div class="ll-profile-url">
                        <span>dev.livelatch.com/@alex</span>
                        <a href="/@alex">Open</a>
                    </div>
                    <iframe class="ll-profile-frame" src="/@alex" title="Livelatch @alex profile demo" loading="lazy"></iframe>
                </aside>
            </div>
        </section>

        <section class="ll-section" id="alpha">
            <div class="ll-shell">
                <div class="ll-section-header">
                    <div>
                        <p class="ll-kicker">What alpha means</p>
                        <h2>Built in public with creators first.</h2>
                    </div>
                    <p class="ll-section-copy">
                        Alpha access is for shaping the core Livelatch experience before wider release. Expect fast iteration, practical creator tools, and clear feedback loops.
                    </p>
                </div>

                <div class="ll-alpha-grid">
                    <article class="ll-card ll-alpha-card">
                        <h3>Profile and links</h3>
                        <p>Create a public profile, add blocks, tune themes, and share one link with your audience.</p>
                    </article>
                    <article class="ll-card ll-alpha-card">
                        <h3>Creator identity</h3>
                        <p>Use LatchID to connect login, social accounts, and future cross-product features.</p>
                    </article>
                    <article class="ll-card ll-alpha-card">
                        <h3>Audience signals</h3>
                        <p>Track clicks and connected-channel movement as the analytics pipeline expands.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="ll-section" id="plans">
            <div class="ll-shell">
                <div class="ll-section-header">
                    <div>
                        <p class="ll-kicker">Plans</p>
                        <h2>Start free. Upgrade when you need more.</h2>
                    </div>
                    <p class="ll-section-copy">The alpha starts with a generous free experience. Pro is reserved for deeper creator tools as they are enabled.</p>
                </div>

                <div class="ll-plan-table" role="table" aria-label="Livelatch plans">
                    <div class="ll-plan-row ll-plan-head" role="row">
                        <div role="columnheader">Feature</div>
                        <div role="columnheader">Free</div>
                        <div role="columnheader">Pro</div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">Price</div>
                        <div><span class="ll-price">$0</span><span class="ll-plan-note">For alpha creators</span></div>
                        <div><span class="ll-price">$15</span><span class="ll-plan-note">per month, planned</span></div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">Profile links and blocks</div>
                        <div>Included</div>
                        <div>Included with advanced controls</div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">Theme Studio</div>
                        <div>Free core themes</div>
                        <div>Premium themes and advanced effects</div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">Analytics</div>
                        <div>Link clicks and daily movement</div>
                        <div>Deeper reports and creator insights</div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">LatchDeck</div>
                        <div>Alpha previews</div>
                        <div>Card campaigns and redemptions</div>
                    </div>
                    <div class="ll-plan-row" role="row">
                        <div class="ll-plan-name">Support</div>
                        <div>Community feedback</div>
                        <div>Priority creator support</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="ll-footer">
        <div class="ll-shell">
            <div class="ll-footer-grid">
                <div>
                    <a class="ll-brand" href="/">
                        <span class="ll-logo">LL</span>
                        <span>Livelatch</span>
                    </a>
                    <p style="margin-top: 12px;">Livelatch Alpha is a creator profile and audience toolkit built around LatchID.</p>
                </div>
                <nav class="ll-footer-links" aria-label="Footer links">
                    <a class="ll-button ll-button-ghost" href="/studio/docs">Documentation</a>
                    <a class="ll-button ll-button-ghost" href="/legal/privacy" hx-get="/legal/privacy" hx-target="#ll-legal-panel" hx-swap="innerHTML">Privacy</a>
                    <a class="ll-button ll-button-ghost" href="/legal/terms" hx-get="/legal/terms" hx-target="#ll-legal-panel" hx-swap="innerHTML">Terms</a>
                </nav>
            </div>
            <div id="ll-legal-panel" class="ll-legal-panel" aria-live="polite"></div>
        </div>
    </footer>
</div>

<div class="ll-modal" id="llAuthModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="llModalTitle">
    <div class="ll-modal-panel" role="document">
        <header class="ll-modal-header">
            <div>
                <p class="ll-kicker">LatchID access</p>
                <h2 id="llModalTitle">Get started with Livelatch</h2>
                <p class="ll-section-copy">Use Google to create or open your LatchID account.</p>
            </div>
            <button class="ll-close" type="button" data-ll-close-modal aria-label="Close modal">&times;</button>
        </header>

        <button class="ll-provider" type="button" data-ll-provider="Google">
            <span>Continue with Google</span>
            <small>LatchID</small>
        </button>
        <div class="ll-demo-message" data-ll-demo-message></div>
    </div>
</div>

<script src="https://unpkg.com/htmx.org@2.0.4"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
    (function () {
        'use strict';

        var latchIdConfig = {
            supabaseUrl: <?php echo json_encode($llSupabaseUrl); ?>,
            supabaseAnonKey: <?php echo json_encode($llSupabaseAnonKey); ?>,
            redirectTo: 'https://dev.livelatch.com/callback/google'
        };

        var modal = document.getElementById('llAuthModal');
        var openButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-open-modal]'));
        var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-close-modal]'));
        var providerButton = document.querySelector('[data-ll-provider]');
        var message = document.querySelector('[data-ll-demo-message]');
        var lastFocused = null;

        function openModal() {
            lastFocused = document.activeElement;
            modal.setAttribute('aria-hidden', 'false');
            window.setTimeout(function () {
                if (providerButton) providerButton.focus();
            }, 20);
        }

        function closeModal() {
            modal.setAttribute('aria-hidden', 'true');
            if (message) {
                message.classList.remove('ll-visible');
                message.innerHTML = '';
            }
            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        }

        async function handleGoogle() {
            if (!message || !providerButton) return;
            message.classList.add('ll-visible');

            if (!latchIdConfig.supabaseUrl || !latchIdConfig.supabaseAnonKey) {
                message.innerHTML = '<strong>Get started is not available yet.</strong> Please try again shortly.';
                return;
            }

            if (!window.supabase || !window.supabase.createClient) {
                message.innerHTML = '<strong>Get started is not available yet.</strong> Please refresh and try again.';
                return;
            }

            providerButton.disabled = true;
            message.textContent = 'Opening Google sign in...';

            try {
                var client = window.supabase.createClient(latchIdConfig.supabaseUrl, latchIdConfig.supabaseAnonKey);
                var result = await client.auth.signInWithOAuth({
                    provider: 'google',
                    options: { redirectTo: latchIdConfig.redirectTo }
                });

                if (result.error) throw result.error;
            } catch (error) {
                providerButton.disabled = false;
                message.innerHTML = '<strong>Google sign in failed.</strong> ' + (error && error.message ? error.message : 'Please try again.');
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', openModal);
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        if (providerButton) {
            providerButton.addEventListener('click', handleGoogle);
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });
    }());
</script>
</body>
</html>
