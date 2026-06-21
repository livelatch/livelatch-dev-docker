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
    <meta name="csrf-token" content="<?php echo function_exists('csrf_token') ? htmlspecialchars(csrf_token(), ENT_QUOTES) : ''; ?>">
    <title>Livelatch — one simple link for everything you share</title>
    <meta name="description" content="Livelatch gives you one friendly page for your links, socials and shops. Set it up in minutes, make it yours, and share a single link everywhere.">
    <link rel="icon" type="image/png" href="/logos/livelatch_social_icon.png">
    <?php
    if (function_exists('view')) {
        echo view('layouts.posthog')->render();
    }
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        :root {
            color-scheme: light;
            --ll-font: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --ll-bg: #ffffff;
            --ll-bg-soft: #d1faff;
            --ll-surface: rgba(255, 255, 255, 0.78);
            --ll-surface-solid: #ffffff;
            --ll-text: #00073d;
            --ll-muted: #6b6885;
            --ll-border: rgba(18, 15, 45, 0.10);
            --ll-shadow: 0 24px 70px rgba(30, 16, 80, 0.10);
            --ll-shadow-soft: 0 12px 34px rgba(30, 16, 80, 0.08);
            --ll-primary: #0092ec;
            --ll-primary-2: #0ce5de;
            --ll-primary-3: #47f1ff;
            --c-livelatch: #0092ec;
            --c-latchid: #1faa3a;
            --c-latchdeck: #9322c4;
            --c-latchalytics: #ff7a1a;
            --ll-radius: 36px;
            --ll-radius-sm: 22px;
            --ll-button-radius: 18px;
            --ll-max: 1160px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--ll-font);
            color: var(--ll-text);
            background:
                radial-gradient(circle at 18% -8%, rgba(98, 54, 255, 0.14), transparent 30%),
                radial-gradient(circle at 88% 2%, rgba(18, 214, 223, 0.16), transparent 28%),
                var(--ll-bg);
            line-height: 1.55;
            overflow-x: hidden;
        }

        a { color: inherit; }
        button { font: inherit; border: 0; }
        h1, h2, h3, p { margin-top: 0; }
        h1, h2, h3 { letter-spacing: -0.01em; line-height: 1.08; }
        img { max-width: 100%; display: block; }

        .ll-shell {
            width: min(var(--ll-max), calc(100% - 40px));
            margin: 0 auto;
        }

        /* Nav */
        .ll-nav {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid var(--ll-border);
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(18px);
        }
        .ll-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 74px;
            gap: 18px;
        }
        .ll-brand { display: inline-flex; align-items: center; text-decoration: none; }
        .ll-brand img { height: 30px; width: auto; }
        .ll-nav-links { display: flex; align-items: center; gap: 4px; }
        .ll-nav-links a {
            color: var(--ll-muted);
            border-radius: 999px;
            font-weight: 600;
            font-size: .95rem;
            padding: 9px 14px;
            text-decoration: none;
            transition: color 140ms ease, background 140ms ease;
        }
        .ll-nav-links a:hover { color: var(--ll-text); background: color-mix(in srgb, var(--ll-primary) 8%, transparent); }
        .ll-nav-actions { display: flex; align-items: center; gap: 10px; }

        /* Buttons */
        .ll-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            border-radius: var(--ll-button-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: .98rem;
            padding: 0 20px;
            text-decoration: none;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
            white-space: nowrap;
        }
        .ll-button-primary {
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            color: #fff;
            box-shadow: 0 16px 34px rgba(0, 146, 236, 0.28);
        }
        .ll-button-primary:hover { transform: translateY(-2px); box-shadow: 0 20px 42px rgba(0, 146, 236, 0.34); }
        .ll-button-ghost {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            box-shadow: var(--ll-shadow-soft);
        }
        .ll-button-ghost:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border)); }
        .ll-button-sm { min-height: 40px; padding: 0 16px; font-size: .92rem; }

        /* Kicker / pills */
        .ll-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            width: fit-content;
            border: 1px solid var(--ll-border);
            border-radius: 999px;
            background: var(--ll-surface-solid);
            color: var(--ll-primary);
            font-size: .82rem;
            font-weight: 600;
            padding: 7px 14px;
            box-shadow: var(--ll-shadow-soft);
        }
        .ll-kicker::before {
            content: "";
            width: 8px; height: 8px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 0 14px rgba(12, 229, 222, 0.8);
        }

        /* Hero */
        .ll-hero { padding: 72px 0 56px; }
        .ll-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(330px, 0.85fr);
            gap: 52px;
            align-items: center;
        }
        .ll-hero h1 {
            margin: 20px 0 18px;
            font-size: clamp(2.5rem, 5.4vw, 4.3rem);
            font-weight: 700;
        }
        .ll-hero h1 .ll-grad {
            background: linear-gradient(120deg, var(--ll-primary), var(--ll-primary-2) 70%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .ll-lead {
            max-width: 560px;
            color: var(--ll-muted);
            font-size: clamp(1.05rem, 1.6vw, 1.2rem);
            margin-bottom: 26px;
        }
        .ll-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .ll-trust {
            margin: 22px 0 0;
            color: var(--ll-muted);
            font-size: .9rem;
            font-weight: 500;
        }

        /* Live profile preview */
        .ll-preview {
            position: relative;
            border-radius: var(--ll-radius);
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow);
            padding: 14px;
        }
        .ll-preview::before {
            content: "";
            position: absolute;
            inset: -14px -14px auto auto;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(12, 229, 222, 0.22), transparent 70%);
            z-index: -1;
        }
        .ll-preview-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--ll-muted);
            font-size: .82rem;
            font-weight: 600;
            padding: 4px 8px 12px;
        }
        .ll-preview-bar a { color: var(--ll-primary); text-decoration: none; font-weight: 600; }
        .ll-preview-frame {
            width: 100%;
            height: 580px;
            border: 0;
            border-radius: var(--ll-radius-sm);
            background: #fff;
        }

        /* Sections */
        .ll-section { padding: 64px 0; }
        .ll-section-head { max-width: 660px; margin-bottom: 40px; }
        .ll-section-head h2 {
            font-size: clamp(1.9rem, 3.4vw, 2.9rem);
            font-weight: 700;
            margin: 14px 0 12px;
        }
        .ll-section-head p { color: var(--ll-muted); font-size: 1.08rem; margin: 0; }

        /* Steps */
        .ll-steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        .ll-step {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            border-radius: var(--ll-radius-sm);
            box-shadow: var(--ll-shadow-soft);
            padding: 26px;
        }
        .ll-step-num {
            display: grid;
            place-items: center;
            width: 44px; height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 16px;
        }
        .ll-step h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: 6px; }
        .ll-step p { color: var(--ll-muted); margin: 0; }

        /* Product family */
        .ll-products {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .ll-product {
            position: relative;
            border: 1px solid color-mix(in srgb, var(--accent) 20%, var(--ll-border));
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--accent) 6%, #fff), var(--ll-surface-solid) 60%);
            border-radius: var(--ll-radius);
            box-shadow: var(--ll-shadow-soft);
            padding: 30px;
            overflow: hidden;
            transition: transform 180ms ease, box-shadow 180ms ease;
        }
        .ll-product:hover { transform: translateY(-3px); box-shadow: var(--ll-shadow); }
        .ll-product::after {
            content: "";
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 26%, transparent), transparent 70%);
        }
        .ll-product-logo { height: 34px; width: auto; margin-bottom: 16px; position: relative; }
        .ll-product p { color: var(--ll-muted); margin: 0; position: relative; }
        .ll-tag {
            position: absolute;
            top: 26px; right: 26px;
            font-size: .72rem;
            font-weight: 600;
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--accent) 30%, transparent);
            border-radius: 999px;
            padding: 4px 11px;
        }

        /* Plans */
        .ll-plans {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
        }
        .ll-plan {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            border-radius: var(--ll-radius);
            box-shadow: var(--ll-shadow-soft);
            padding: 32px;
            display: flex;
            flex-direction: column;
        }
        .ll-plan-featured {
            border: 1.5px solid color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border));
            box-shadow: var(--ll-shadow);
        }
        .ll-plan-name { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 1.15rem; }
        .ll-plan-badge {
            font-size: .72rem; font-weight: 600;
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 12%, #fff);
            border-radius: 999px; padding: 3px 10px;
        }
        .ll-plan-price { margin: 14px 0 4px; font-size: 2.6rem; font-weight: 700; }
        .ll-plan-price span { font-size: 1rem; font-weight: 500; color: var(--ll-muted); }
        .ll-plan-sub { color: var(--ll-muted); margin: 0 0 18px; }
        .ll-plan-list { list-style: none; padding: 0; margin: 0 0 24px; display: grid; gap: 11px; }
        .ll-plan-list li { display: flex; align-items: flex-start; gap: 10px; color: var(--ll-text); }
        .ll-plan-list li::before {
            content: "";
            flex: 0 0 auto;
            width: 20px; height: 20px;
            margin-top: 2px;
            border-radius: 999px;
            background:
                linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='white' d='M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z'/%3E%3C/svg%3E") center / 15px no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='white' d='M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z'/%3E%3C/svg%3E") center / 15px no-repeat;
        }
        .ll-plan .ll-button { margin-top: auto; }
        .ll-plan-note { text-align: center; color: var(--ll-muted); font-size: .9rem; margin-top: 12px; }

        /* Footer */
        .ll-footer {
            border-top: 1px solid var(--ll-border);
            padding: 48px 0 36px;
            margin-top: 24px;
        }
        .ll-footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: start;
        }
        .ll-footer-brand img { height: 28px; width: auto; margin-bottom: 12px; }
        .ll-footer-brand p { color: var(--ll-muted); margin: 0; max-width: 360px; }
        .ll-footer-links { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
        .ll-legal-panel { min-height: 10px; margin-top: 22px; }
        .ll-legal-card {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius-sm);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow-soft);
            padding: 22px;
        }
        .ll-legal-card h2 { margin-bottom: 10px; }
        .ll-legal-card p { color: var(--ll-muted); }
        .ll-colophon {
            display: flex; align-items: center; gap: 10px;
            margin-top: 28px; color: var(--ll-muted); font-size: .88rem;
        }
        .ll-colophon img { width: 24px; height: 24px; border-radius: 8px; }

        /* Modal (light) */
        .ll-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            place-items: center;
            padding: 20px;
            background: rgba(0, 7, 61, 0.42);
            backdrop-filter: blur(4px);
        }
        .ll-modal[aria-hidden="false"] { display: grid; }
        .ll-modal-panel {
            width: min(460px, 100%);
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: var(--ll-surface-solid);
            box-shadow: 0 30px 90px rgba(0, 7, 61, 0.28);
            padding: 28px;
        }
        .ll-modal-header { display: flex; justify-content: space-between; gap: 14px; align-items: start; }
        .ll-modal-header h2 { font-size: 1.5rem; font-weight: 700; margin: 10px 0 6px; }
        .ll-modal-header p { color: var(--ll-muted); margin: 0; }
        .ll-close {
            width: 40px; height: 40px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--ll-primary) 8%, transparent);
            color: var(--ll-text);
            cursor: pointer;
            font-size: 1.4rem;
            flex: 0 0 auto;
        }
        .ll-close:hover { background: color-mix(in srgb, var(--ll-primary) 14%, transparent); }
        .ll-provider {
            width: 100%;
            margin-top: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            cursor: pointer;
            font-weight: 600;
            min-height: 52px;
            padding: 14px;
            box-shadow: var(--ll-shadow-soft);
            transition: transform 150ms ease, border-color 150ms ease;
        }
        .ll-provider:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border)); }
        .ll-provider svg { width: 20px; height: 20px; }
        .ll-modal-fine { color: var(--ll-muted); font-size: .82rem; text-align: center; margin: 16px 0 0; }
        .ll-demo-message {
            display: none;
            margin-top: 14px;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius-sm);
            background: color-mix(in srgb, var(--ll-primary) 6%, #fff);
            color: var(--ll-muted);
            padding: 12px 14px;
        }
        .ll-demo-message.ll-visible { display: block; }
        .ll-or {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0 4px;
            color: var(--ll-muted);
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .ll-or::before, .ll-or::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--ll-border);
        }
        .ll-auth-step { margin-top: 18px; }
        .ll-auth-step[hidden] { display: none; }
        .ll-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 10px 0 4px;
            color: var(--ll-muted);
            font-size: .82rem;
            line-height: 1.4;
            text-align: left;
            cursor: pointer;
        }
        .ll-checkbox input { margin-top: 2px; flex: 0 0 auto; }
        .ll-auth-step h3 { margin: 0 0 6px; font-size: 1.1rem; }
        .ll-auth-step p { margin: 0 0 12px; color: var(--ll-muted); font-size: .92rem; }
        .ll-input {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            min-height: 52px;
            padding: 14px 16px;
            font-size: 1rem;
            font-family: inherit;
        }
        .ll-input:focus {
            outline: none;
            border-color: color-mix(in srgb, var(--ll-primary) 55%, var(--ll-border));
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ll-primary) 18%, transparent);
        }
        .ll-input[data-ll-code] { letter-spacing: .35em; text-align: center; font-size: 1.25rem; }
        .ll-auth-step .ll-button { width: 100%; }
        .ll-auth-actions { display: flex; flex-direction: column; gap: 8px; }

        .ll-cookie {
            position: fixed;
            left: 18px;
            bottom: 18px;
            z-index: 60;
            width: min(380px, calc(100% - 36px));
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius-sm);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow);
            padding: 16px 18px;
        }
        .ll-cookie[hidden] { display: none; }
        .ll-cookie p { margin: 0 0 12px; font-size: 0.86rem; color: var(--ll-muted); }
        .ll-cookie p a { color: var(--ll-primary); text-decoration: underline; }
        .ll-cookie-actions { display: flex; gap: 8px; justify-content: flex-end; }
        @media (prefers-reduced-motion: no-preference) {
            .ll-cookie { animation: ll-cookie-in 220ms ease both; }
            @keyframes ll-cookie-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        }

        @media (max-width: 980px) {
            .ll-hero-grid, .ll-footer-grid { grid-template-columns: 1fr; }
            .ll-steps, .ll-products, .ll-plans { grid-template-columns: 1fr; }
            .ll-nav-links { display: none; }
            .ll-footer-links { justify-content: flex-start; }
        }
        @media (max-width: 600px) {
            .ll-hero { padding-top: 48px; }
            .ll-preview-frame { height: 520px; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { scroll-behavior: auto; }
            .ll-button, .ll-product, .ll-provider { transition: none; }
        }
    </style>
</head>
<body>
    <header class="ll-nav">
        <div class="ll-shell ll-nav-inner">
            <a class="ll-brand" href="/" aria-label="Livelatch home">
                <img src="/logos/livelatch_light.png" alt="Livelatch">
            </a>

            <nav class="ll-nav-links" aria-label="Main navigation">
                <a href="#how">How it works</a>
                <a href="#family">Products</a>
                <a href="#demo">Live demo</a>
                <a href="#plans">Plans</a>
                <a href="/studio/docs">Docs</a>
            </nav>

            <div class="ll-nav-actions">
                <button class="ll-button ll-button-ghost ll-button-sm" type="button" data-ll-open-modal>Log in</button>
                <button class="ll-button ll-button-primary ll-button-sm" type="button" data-ll-open-modal>Get started</button>
            </div>
        </div>
    </header>

    <main>
        <section class="ll-hero">
            <div class="ll-shell ll-hero-grid">
                <div>
                    <span class="ll-kicker">Livelatch · Alpha</span>
                    <h1>Everything you share, behind <span class="ll-grad">one simple link</span>.</h1>
                    <p class="ll-lead">
                        Livelatch gives you one friendly page for your links, socials and shops.
                        Set it up in minutes, make it your own, and share a single link everywhere —
                        no website to build, no clutter.
                    </p>
                    <div class="ll-hero-actions">
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal>Get started — it's free</button>
                        <a class="ll-button ll-button-ghost" href="#demo">See a live page</a>
                    </div>
                    <p class="ll-trust">Free to start · No card needed · Your data stays yours</p>
                </div>

                <aside class="ll-preview" id="demo" aria-label="Live Livelatch profile preview">
                    <div class="ll-preview-bar">
                        <span>dev.livelatch.com/@alex2</span>
                        <a href="/@alex2" target="_blank" rel="noopener">Open ↗</a>
                    </div>
                    <iframe class="ll-preview-frame" src="/@alex2" title="Live Livelatch profile for @alex2" loading="lazy"></iframe>
                </aside>
            </div>
        </section>

        <section class="ll-section" id="how">
            <div class="ll-shell">
                <div class="ll-section-head">
                    <span class="ll-kicker">How it works</span>
                    <h2>Up and running in three easy steps.</h2>
                    <p>No technical know-how needed. If you can fill in a form, you can build a Livelatch page.</p>
                </div>
                <div class="ll-steps">
                    <article class="ll-step">
                        <div class="ll-step-num">1</div>
                        <h3>Create your page</h3>
                        <p>Sign in with Google and claim your own Livelatch link, like <strong>livelatch.com/@you</strong>.</p>
                    </article>
                    <article class="ll-step">
                        <div class="ll-step-num">2</div>
                        <h3>Make it yours</h3>
                        <p>Add your links, pick a theme and style your page in the Theme Studio so it feels like you.</p>
                    </article>
                    <article class="ll-step">
                        <div class="ll-step-num">3</div>
                        <h3>Share one link</h3>
                        <p>Pop your Livelatch link in every bio. One place for your audience to find everything you do.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="ll-section" id="family">
            <div class="ll-shell">
                <div class="ll-section-head">
                    <span class="ll-kicker">The Livelatch family</span>
                    <h2>A few simple tools that work together.</h2>
                    <p>Start with your page today. The rest of the family connects automatically as it arrives.</p>
                </div>
                <div class="ll-products">
                    <article class="ll-product" style="--accent: var(--c-livelatch);">
                        <span class="ll-tag">Live</span>
                        <img class="ll-product-logo" src="/logos/livelatch_light.png" alt="Livelatch">
                        <p>Your public page. One link that holds your content, socials and shops — all beautifully presented and ready to share.</p>
                    </article>
                    <article class="ll-product" style="--accent: var(--c-latchid);">
                        <span class="ll-tag">Live</span>
                        <img class="ll-product-logo" src="/logos/latchid_light.png" alt="LatchID">
                        <p>Your single, simple login. Connect your accounts once with LatchID and you're recognised everywhere across Livelatch.</p>
                    </article>
                    <article class="ll-product" style="--accent: var(--c-latchdeck);">
                        <span class="ll-tag">Coming soon</span>
                        <img class="ll-product-logo" src="/logos/latchdeck_light.png" alt="LatchDeck">
                        <p>Collectible cards for your community. Reward your biggest fans with digital cards they can collect, trade and redeem.</p>
                    </article>
                    <article class="ll-product" style="--accent: var(--c-latchalytics);">
                        <span class="ll-tag">In alpha</span>
                        <img class="ll-product-logo" src="/logos/latchalytics_light.png" alt="Latchalytics">
                        <p>Know what your audience loves. Clear, friendly numbers showing what people click — no spreadsheets required.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="ll-section" id="plans">
            <div class="ll-shell">
                <div class="ll-section-head">
                    <span class="ll-kicker">Plans</span>
                    <h2>Start free. Upgrade only when you need more.</h2>
                    <p>The alpha begins with a genuinely useful free plan. Pro arrives as the deeper creator tools switch on.</p>
                </div>
                <div class="ll-plans">
                    <div class="ll-plan">
                        <div class="ll-plan-name">Free</div>
                        <div class="ll-plan-price">$0</div>
                        <p class="ll-plan-sub">Everything you need to get your page out there.</p>
                        <ul class="ll-plan-list">
                            <li>Your own Livelatch page and link</li>
                            <li>Free core themes in the Theme Studio</li>
                            <li>Link clicks and daily insights</li>
                            <li>LatchID account connections</li>
                            <li>Community support</li>
                        </ul>
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal>Get started free</button>
                    </div>
                    <div class="ll-plan ll-plan-featured">
                        <div class="ll-plan-name">Pro <span class="ll-plan-badge">Planned</span></div>
                        <div class="ll-plan-price">$15 <span>/ month</span></div>
                        <p class="ll-plan-sub">For creators who want to go further.</p>
                        <ul class="ll-plan-list">
                            <li>Everything in Free</li>
                            <li>Premium themes and advanced effects</li>
                            <li>Deeper Latchalytics reports</li>
                            <li>LatchDeck card campaigns and redemptions</li>
                            <li>Priority creator support</li>
                        </ul>
                        <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal>Start with Free for now</button>
                        <p class="ll-plan-note">Pro pricing is planned for a future release.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="ll-footer">
        <div class="ll-shell">
            <div class="ll-footer-grid">
                <div class="ll-footer-brand">
                    <img src="/logos/livelatch_light.png" alt="Livelatch">
                    <p>One friendly link for everything you share. Built for creators, in the open, during alpha.</p>
                </div>
                <nav class="ll-footer-links" aria-label="Footer links">
                    <a class="ll-button ll-button-ghost ll-button-sm" href="/studio/docs">Documentation</a>
                    <a class="ll-button ll-button-ghost ll-button-sm" href="/legal/privacy" hx-get="/legal/privacy" hx-target="#ll-legal-panel" hx-swap="innerHTML">Privacy</a>
                    <a class="ll-button ll-button-ghost ll-button-sm" href="/legal/terms" hx-get="/legal/terms" hx-target="#ll-legal-panel" hx-swap="innerHTML">Terms</a>
                </nav>
            </div>
            <div id="ll-legal-panel" class="ll-legal-panel" aria-live="polite"></div>
            <div class="ll-colophon">
                <img src="/logos/livelatch_social_icon.png" alt="">
                <span>© <?php echo date('Y'); ?> Livelatch · Alpha</span>
            </div>
        </div>
    </footer>

    <div class="ll-modal" id="llAuthModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="llModalTitle">
        <div class="ll-modal-panel" role="document">
            <header class="ll-modal-header">
                <div>
                    <span class="ll-kicker">LatchID</span>
                    <h2 id="llModalTitle">Welcome to Livelatch</h2>
                    <p>Create your LatchID with Google or your email. Email sign-ups can set up a passkey for fast, password-free logins.</p>
                </div>
                <button class="ll-close" type="button" data-ll-close-modal aria-label="Close">&times;</button>
            </header>

            <button class="ll-provider" type="button" data-ll-provider="Google">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.76h3.56c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.56-2.76c-.98.66-2.23 1.06-3.72 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.09a6.6 6.6 0 0 1 0-4.18V7.07H2.18a11 11 0 0 0 0 9.86l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
                <span>Continue with Google</span>
            </button>

            <button class="ll-provider" type="button" data-ll-passkey-signin>
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M3 20c0-3.3 2.7-6 6-6 1.2 0 2.3.35 3.2.95" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 13.5a2.5 2.5 0 0 0-2.5 2.5c0 1 .6 1.9 1.5 2.3V21l1 1 1-1 .8-.8-.8-.8.8-.8-.9-.9c.55-.45.9-1.13.9-1.9A2.5 2.5 0 0 0 17 13.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                <span>Sign in with a passkey</span>
            </button>

            <div class="ll-or" aria-hidden="true">or</div>

            <form class="ll-auth-step" data-ll-email-form>
                <input class="ll-input" type="email" inputmode="email" autocomplete="email" placeholder="you@example.com" aria-label="Email address" data-ll-email required>
                <label class="ll-checkbox">
                    <input type="checkbox" data-ll-marketing>
                    <span>Send me product news and occasional marketing emails (optional). We'll always email you about account and service matters.</span>
                </label>
                <button class="ll-button ll-button-primary" type="submit" data-ll-email-submit>Email me a sign-in code</button>
            </form>

            <form class="ll-auth-step" data-ll-code-form hidden>
                <p>Enter the code we sent to <strong data-ll-email-display></strong>.</p>
                <input class="ll-input" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="8" placeholder="123456" aria-label="Sign-in code" data-ll-code>
                <div class="ll-auth-actions">
                    <button class="ll-button ll-button-primary" type="submit" data-ll-code-submit>Verify code</button>
                    <button class="ll-button ll-button-ghost ll-button-sm" type="button" data-ll-code-back>Use a different email</button>
                </div>
            </form>

            <div class="ll-auth-step" data-ll-passkey-step hidden>
                <h3>Set up a passkey</h3>
                <p>Use Face ID, Touch ID, Windows Hello or a security key to sign in next time — no codes, no passwords.</p>
                <div class="ll-auth-actions">
                    <button class="ll-button ll-button-primary" type="button" data-ll-passkey-enroll>Set up a passkey</button>
                    <button class="ll-button ll-button-ghost ll-button-sm" type="button" data-ll-passkey-skip>Maybe later</button>
                </div>
            </div>

            <div class="ll-demo-message" data-ll-demo-message></div>
            <p class="ll-modal-fine">By continuing you agree to our Terms and Privacy notice.</p>
        </div>
    </div>

    <aside class="ll-cookie" id="llCookie" role="dialog" aria-label="Cookie choices" aria-live="polite" hidden>
        <p>
            We use analytics cookies to understand how Livelatch is used. While we're in
            alpha, allowing this genuinely helps us see what's working and build a better
            product. You can decline and still browse normally. Our cookie use may change —
            if it does, we'll ask you to choose again.
            <a href="/legal/privacy" hx-get="/legal/privacy" hx-target="#ll-legal-panel" hx-swap="innerHTML">Learn more</a>.
        </p>
        <div class="ll-cookie-actions">
            <button class="ll-button ll-button-ghost ll-button-sm" type="button" data-ll-cookie="deny">Decline</button>
            <button class="ll-button ll-button-primary ll-button-sm" type="button" data-ll-cookie="all">Allow all</button>
        </div>
    </aside>

    <script>
        (function () {
            'use strict';
            var banner = document.getElementById('llCookie');
            if (!banner) return;

            // No PostHog (no key) => no analytics cookies => nothing to consent to.
            if (typeof window.llReadCookieConsent !== 'function') return;

            // Only show until the visitor has made a choice (for the current
            // consent version). Lives on the homepage only.
            if (window.llReadCookieConsent()) return;

            banner.hidden = false;
            banner.addEventListener('click', function (event) {
                var button = event.target.closest('[data-ll-cookie]');
                if (!button) return;
                var choice = button.getAttribute('data-ll-cookie');
                if (typeof window.llSetCookieConsent === 'function') {
                    window.llSetCookieConsent(choice);
                }
                banner.hidden = true;
            });
        }());
    </script>

    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        (function () {
            'use strict';

            var latchIdConfig = {
                supabaseUrl: <?php echo json_encode($llSupabaseUrl); ?>,
                supabaseAnonKey: <?php echo json_encode($llSupabaseAnonKey); ?>,
                redirectTo: 'https://dev.livelatch.com/callback/google',
                sessionEndpoint: '/api/latchid/session',
                dashboardUrl: '/dashboard'
            };

            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            var modal = document.getElementById('llAuthModal');
            var openButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-open-modal]'));
            var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-close-modal]'));
            var providerButton = document.querySelector('[data-ll-provider]');
            var passkeySigninButton = document.querySelector('[data-ll-passkey-signin]');
            var emailForm = document.querySelector('[data-ll-email-form]');
            var emailInput = document.querySelector('[data-ll-email]');
            var codeForm = document.querySelector('[data-ll-code-form]');
            var codeInput = document.querySelector('[data-ll-code]');
            var emailDisplay = document.querySelector('[data-ll-email-display]');
            var codeBackButton = document.querySelector('[data-ll-code-back]');
            var passkeyStep = document.querySelector('[data-ll-passkey-step]');
            var passkeyEnrollButton = document.querySelector('[data-ll-passkey-enroll]');
            var passkeySkipButton = document.querySelector('[data-ll-passkey-skip]');
            var message = document.querySelector('[data-ll-demo-message]');
            var lastFocused = null;
            var pendingEmail = '';
            var sharedClient = null;

            function configured() {
                return latchIdConfig.supabaseUrl && latchIdConfig.supabaseAnonKey
                    && window.supabase && window.supabase.createClient;
            }

            // Single client for the email + passkey flows. Opts into the beta
            // passkey API and keeps the session in this page (no redirect needed).
            function getClient() {
                if (!sharedClient) {
                    sharedClient = window.supabase.createClient(latchIdConfig.supabaseUrl, latchIdConfig.supabaseAnonKey, {
                        auth: {
                            flowType: 'pkce',
                            detectSessionInUrl: false,
                            persistSession: true,
                            autoRefreshToken: true,
                            experimental: { passkey: true }
                        }
                    });
                }
                return sharedClient;
            }

            function notify(html, isError) {
                if (!message) return;
                message.classList.add('ll-visible');
                message.innerHTML = html;
                message.style.color = isError ? 'var(--ll-danger, #c0392b)' : '';
            }

            function showStep(step) {
                [emailForm, codeForm, passkeyStep].forEach(function (el) {
                    if (el) el.hidden = (el !== step);
                });
            }

            function openModal() {
                lastFocused = document.activeElement;
                modal.setAttribute('aria-hidden', 'false');
                showStep(emailForm);
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

            // Hand the Supabase session to Laravel, which creates/links the
            // LatchID user and logs them in. Shared by Google's callback page
            // and the in-page email/passkey flows.
            async function completeLogin(session, user) {
                var metadata = (user && user.user_metadata) || {};
                // Marketing opt-in from the email step (unticked by default —
                // explicit opt-in for GDPR). Absent for returning passkey users,
                // where the server keeps their stored preference; default false
                // here so we never silently opt anyone in.
                var marketingBox = document.querySelector('[data-ll-marketing]');
                var marketingOptIn = marketingBox ? marketingBox.checked : false;
                var cookieConsent = (typeof window.llReadCookieConsent === 'function') ? window.llReadCookieConsent() : null;
                var response = await fetch(latchIdConfig.sessionEndpoint, {
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
                        marketing_opt_in: marketingOptIn,
                        cookie_consent: cookieConsent
                    })
                });

                var payload = await response.json().catch(function () { return {}; });
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'We could not finish signing you in.');
                }

                window.location.assign(payload.redirect || latchIdConfig.dashboardUrl);
            }

            async function handleGoogle() {
                if (!message || !providerButton) return;
                message.classList.add('ll-visible');

                if (!configured()) {
                    notify('<strong>Get started is not available yet.</strong> Please refresh and try again.');
                    return;
                }

                providerButton.disabled = true;
                message.textContent = 'Opening Google sign in...';

                try {
                    var client = window.supabase.createClient(latchIdConfig.supabaseUrl, latchIdConfig.supabaseAnonKey, {
                        auth: {
                            // PKCE (authorization-code) flow so the OAuth token is
                            // never placed in the URL fragment. Same origin as the
                            // /callback page, so the verifier round-trips.
                            flowType: 'pkce',
                            detectSessionInUrl: false,
                            persistSession: true,
                            autoRefreshToken: true
                        }
                    });
                    var result = await client.auth.signInWithOAuth({
                        provider: 'google',
                        options: { redirectTo: latchIdConfig.redirectTo }
                    });

                    if (result.error) throw result.error;
                } catch (error) {
                    providerButton.disabled = false;
                    notify('<strong>Google sign in failed.</strong> ' + (error && error.message ? error.message : 'Please try again.'), true);
                }
            }

            async function handleEmailSubmit(event) {
                event.preventDefault();
                if (!configured()) {
                    notify('<strong>Email sign in is not available yet.</strong> Please refresh and try again.', true);
                    return;
                }

                var email = (emailInput.value || '').trim();
                if (!email) return;

                var submit = emailForm.querySelector('[data-ll-email-submit]');
                if (submit) submit.disabled = true;
                notify('Sending your sign-in code...');

                try {
                    var result = await getClient().auth.signInWithOtp({
                        email: email,
                        options: { shouldCreateUser: true }
                    });
                    if (result.error) throw result.error;

                    pendingEmail = email;
                    if (emailDisplay) emailDisplay.textContent = email;
                    showStep(codeForm);
                    notify('We emailed a sign-in code to ' + email + '.');
                    window.setTimeout(function () { if (codeInput) codeInput.focus(); }, 20);
                } catch (error) {
                    notify('<strong>Could not send your code.</strong> ' + (error && error.message ? error.message : 'Please try again.'), true);
                } finally {
                    if (submit) submit.disabled = false;
                }
            }

            async function handleCodeSubmit(event) {
                event.preventDefault();
                var token = (codeInput.value || '').trim();
                if (!token) return;

                var submit = codeForm.querySelector('[data-ll-code-submit]');
                if (submit) submit.disabled = true;
                notify('Verifying your code...');

                try {
                    // New signups verify as type 'email'; fall back to 'signup'
                    // for the first-time confirmation token if 'email' is rejected.
                    var result = await getClient().auth.verifyOtp({
                        email: pendingEmail,
                        token: token,
                        type: 'email'
                    });
                    if (result.error) {
                        var retry = await getClient().auth.verifyOtp({
                            email: pendingEmail,
                            token: token,
                            type: 'signup'
                        });
                        if (retry.error) throw result.error;
                    }

                    // Session established. Offer passkey setup before finishing.
                    showStep(passkeyStep);
                    notify('You are signed in. Set up a passkey for faster logins next time.');
                    window.setTimeout(function () { if (passkeyEnrollButton) passkeyEnrollButton.focus(); }, 20);
                } catch (error) {
                    notify('<strong>That code did not work.</strong> ' + (error && error.message ? error.message : 'Please try again.'), true);
                } finally {
                    if (submit) submit.disabled = false;
                }
            }

            async function finishWithCurrentSession() {
                var sessionResult = await getClient().auth.getSession();
                if (sessionResult.error) throw sessionResult.error;
                var session = sessionResult.data.session;
                var userResult = await getClient().auth.getUser();
                if (userResult.error) throw userResult.error;
                if (!session || !userResult.data.user) {
                    throw new Error('Your session expired. Please sign in again.');
                }
                await completeLogin(session, userResult.data.user);
            }

            async function handlePasskeyEnroll() {
                passkeyEnrollButton.disabled = true;
                if (passkeySkipButton) passkeySkipButton.disabled = true;
                notify('Follow your browser prompt to create a passkey...');

                try {
                    var result = await getClient().auth.registerPasskey();
                    if (result && result.error) throw result.error;
                    notify('Passkey saved. Finishing sign in...');
                    await finishWithCurrentSession();
                } catch (error) {
                    passkeyEnrollButton.disabled = false;
                    if (passkeySkipButton) passkeySkipButton.disabled = false;
                    notify('<strong>Could not set up the passkey.</strong> ' + (error && error.message ? error.message : 'You can try again or continue without one.'), true);
                }
            }

            async function handlePasskeySkip() {
                passkeySkipButton.disabled = true;
                if (passkeyEnrollButton) passkeyEnrollButton.disabled = true;
                notify('Finishing sign in...');
                try {
                    await finishWithCurrentSession();
                } catch (error) {
                    notify('<strong>Could not finish signing you in.</strong> ' + (error && error.message ? error.message : 'Please try again.'), true);
                }
            }

            async function handlePasskeySignin() {
                if (!configured()) {
                    notify('<strong>Passkey sign in is not available yet.</strong> Please refresh and try again.', true);
                    return;
                }
                passkeySigninButton.disabled = true;
                notify('Follow your browser prompt to sign in with a passkey...');

                try {
                    var result = await getClient().auth.signInWithPasskey();
                    if (result && result.error) throw result.error;
                    var data = result && result.data ? result.data : {};
                    if (data.session && data.user) {
                        await completeLogin(data.session, data.user);
                    } else {
                        await finishWithCurrentSession();
                    }
                } catch (error) {
                    passkeySigninButton.disabled = false;
                    notify('<strong>Passkey sign in failed.</strong> ' + (error && error.message ? error.message : 'Try a code instead.'), true);
                }
            }

            openButtons.forEach(function (button) {
                button.addEventListener('click', openModal);
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            if (providerButton) providerButton.addEventListener('click', handleGoogle);
            if (passkeySigninButton) passkeySigninButton.addEventListener('click', handlePasskeySignin);
            if (emailForm) emailForm.addEventListener('submit', handleEmailSubmit);
            if (codeForm) codeForm.addEventListener('submit', handleCodeSubmit);
            if (codeBackButton) codeBackButton.addEventListener('click', function () {
                showStep(emailForm);
                notify('');
                message.classList.remove('ll-visible');
                if (emailInput) emailInput.focus();
            });
            if (passkeyEnrollButton) passkeyEnrollButton.addEventListener('click', handlePasskeyEnroll);
            if (passkeySkipButton) passkeySkipButton.addEventListener('click', handlePasskeySkip);

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
