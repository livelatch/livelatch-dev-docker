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
    <title>Livelatch Homepage Demo</title>
    <meta name="description" content="A static Livelatch homepage prototype for the creator platform, LatchID, LatchDeck, and Latchalytics.">
    <?php
    if (function_exists('view')) {
        echo view('layouts.posthog')->render();
    }
    ?>
    <style>
        :root {
            --ll-navy-950: #050814;
            --ll-navy-900: #071126;
            --ll-navy-800: #0d1d3d;
            --ll-panel: rgba(11, 20, 42, 0.72);
            --ll-panel-strong: rgba(13, 25, 54, 0.9);
            --ll-glass: rgba(255, 255, 255, 0.08);
            --ll-line: rgba(255, 255, 255, 0.16);
            --ll-line-soft: rgba(255, 255, 255, 0.1);
            --ll-text: #f7fbff;
            --ll-muted: #b6c4da;
            --ll-ink: #08111f;
            --ll-blue: #2d8cff;
            --ll-cyan: #18d5ff;
            --ll-purple: #8c55ff;
            --ll-magenta: #ff4cb8;
            --ll-plum: #241039;
            --ll-mint: #46f2bd;
            --ll-green: #19b878;
            --ll-forest: #073626;
            --ll-amber: #ffb73f;
            --ll-orange: #ff7a32;
            --ll-gold: #ffe176;
            --ll-radius-xl: 30px;
            --ll-radius-lg: 22px;
            --ll-radius-md: 16px;
            --ll-radius-sm: 12px;
            --ll-shadow: 0 28px 90px rgba(0, 0, 0, 0.38);
            --ll-max: 1180px;
            color-scheme: dark;
        }

        body[data-theme="light"] {
            --ll-panel: rgba(255, 255, 255, 0.76);
            --ll-panel-strong: rgba(255, 255, 255, 0.92);
            --ll-glass: rgba(8, 17, 31, 0.06);
            --ll-line: rgba(8, 17, 31, 0.14);
            --ll-line-soft: rgba(8, 17, 31, 0.09);
            --ll-text: #071126;
            --ll-muted: #52677f;
            --ll-shadow: 0 28px 90px rgba(31, 77, 132, 0.18);
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ll-text);
            background:
                radial-gradient(circle at 15% 6%, rgba(24, 213, 255, 0.22), transparent 27rem),
                radial-gradient(circle at 88% 7%, rgba(140, 85, 255, 0.2), transparent 31rem),
                linear-gradient(145deg, #050814 0%, #071126 44%, #0b1730 100%);
            line-height: 1.5;
            overflow-x: hidden;
            transition: background 220ms ease, color 220ms ease;
        }

        body[data-theme="light"] {
            background:
                radial-gradient(circle at 15% 6%, rgba(24, 213, 255, 0.18), transparent 27rem),
                radial-gradient(circle at 88% 7%, rgba(140, 85, 255, 0.14), transparent 31rem),
                linear-gradient(145deg, #f6fbff 0%, #edf6ff 46%, #f8fbff 100%);
        }

        body::before {
            position: fixed;
            inset: 0;
            z-index: -2;
            pointer-events: none;
            content: "";
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 78px 78px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), transparent 82%);
        }

        body[data-theme="light"]::before {
            background-image:
                linear-gradient(rgba(8, 17, 31, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(8, 17, 31, 0.045) 1px, transparent 1px);
        }

        body.ll-modal-open {
            overflow: hidden;
        }

        a {
            color: inherit;
        }

        button,
        a.ll-button {
            font: inherit;
        }

        button {
            border: 0;
        }

        img {
            max-width: 100%;
        }

        .ll-page {
            position: relative;
            overflow: hidden;
        }

        .ll-shell {
            width: min(var(--ll-max), calc(100% - 40px));
            margin: 0 auto;
        }

        .ll-logo-img {
            display: block;
            width: auto;
            max-height: 44px;
            object-fit: contain;
        }

        .ll-logo-word {
            max-width: 174px;
        }

        .ll-logo-small {
            max-height: 32px;
            max-width: 142px;
        }

        .ll-logo-fallback {
            display: none;
            font-weight: 850;
            letter-spacing: 0;
        }

        .ll-logo-img[hidden] + .ll-logo-fallback {
            display: inline-flex;
        }

        .ll-nav {
            position: sticky;
            top: 0;
            z-index: 30;
            background: rgba(5, 8, 20, 0.72);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(22px);
        }

        body[data-theme="light"] .ll-nav {
            background: rgba(246, 251, 255, 0.78);
            border-bottom-color: rgba(8, 17, 31, 0.1);
        }

        .ll-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 76px;
            gap: 18px;
        }

        .ll-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: max-content;
            text-decoration: none;
        }

        .ll-brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            background: linear-gradient(145deg, rgba(45, 140, 255, 0.2), rgba(24, 213, 255, 0.12));
            box-shadow: 0 18px 42px rgba(24, 213, 255, 0.18);
            overflow: hidden;
        }

        .ll-brand-mark .ll-logo-img {
            width: 32px;
            height: 32px;
        }

        .ll-menu-button {
            display: none;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
        }

        body[data-theme="light"] .ll-menu-button {
            border-color: rgba(8, 17, 31, 0.14);
            background: rgba(8, 17, 31, 0.06);
        }

        .ll-menu-button span,
        .ll-menu-button::before,
        .ll-menu-button::after {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            content: "";
        }

        .ll-menu-button::before {
            transform: translateY(-6px);
        }

        .ll-menu-button::after {
            transform: translateY(6px);
        }

        .ll-nav-links,
        .ll-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ll-nav-links a {
            padding: 10px 12px;
            color: var(--ll-muted);
            font-size: 0.94rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 999px;
            transition: color 180ms ease, background 180ms ease;
        }

        body[data-theme="light"] .ll-nav-links a:hover,
        body[data-theme="light"] .ll-nav-links a:focus-visible {
            background: rgba(8, 17, 31, 0.07);
        }

        .ll-theme-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            padding: 3px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.07);
            cursor: pointer;
        }

        body[data-theme="light"] .ll-theme-toggle {
            border-color: rgba(8, 17, 31, 0.14);
            background: rgba(8, 17, 31, 0.06);
        }

        .ll-theme-toggle span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 36px;
            border-radius: 999px;
            color: var(--ll-muted);
            font-size: 0.82rem;
            font-weight: 900;
        }

        .ll-theme-toggle::before {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 42px;
            height: 36px;
            border-radius: 999px;
            content: "";
            background: linear-gradient(135deg, var(--ll-cyan), var(--ll-blue));
            transition: transform 180ms ease;
        }

        body[data-theme="light"] .ll-theme-toggle::before {
            transform: translateX(42px);
            background: linear-gradient(135deg, var(--ll-gold), #fff4b8);
        }

        .ll-theme-toggle span {
            position: relative;
            z-index: 1;
        }

        body[data-theme="dark"] .ll-theme-dark,
        body[data-theme="light"] .ll-theme-light {
            color: #06111f;
        }

        .ll-nav-links a:hover,
        .ll-nav-links a:focus-visible {
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.08);
        }

        .ll-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            gap: 9px;
            border-radius: 999px;
            color: var(--ll-text);
            text-decoration: none;
            cursor: pointer;
            transition: transform 180ms ease, border-color 180ms ease, background 180ms ease, box-shadow 180ms ease;
            white-space: nowrap;
        }

        .ll-button:hover,
        .ll-button:focus-visible {
            transform: translateY(-1px);
        }

        .ll-button:focus-visible,
        .ll-menu-button:focus-visible,
        .ll-provider:focus-visible,
        .ll-tab:focus-visible,
        .ll-close:focus-visible,
        .ll-link-pill:focus-visible {
            outline: 3px solid rgba(70, 242, 189, 0.62);
            outline-offset: 3px;
        }

        .ll-button-primary {
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(135deg, var(--ll-cyan), var(--ll-blue) 48%, var(--ll-purple));
            box-shadow: 0 18px 46px rgba(24, 213, 255, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            font-weight: 850;
        }

        .ll-button-ghost {
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.07);
            font-weight: 750;
        }

        body[data-theme="light"] .ll-button-ghost {
            border-color: rgba(8, 17, 31, 0.14);
            background: rgba(8, 17, 31, 0.055);
        }

        .ll-button-dark {
            color: var(--ll-ink);
            background: #fff;
            font-weight: 850;
        }

        .ll-hero {
            position: relative;
            padding: 86px 0 90px;
        }

        .ll-hero::after {
            position: absolute;
            top: 120px;
            right: max(-120px, calc((100vw - var(--ll-max)) / 2 - 170px));
            width: 420px;
            height: 420px;
            border-radius: 999px;
            content: "";
            background: radial-gradient(circle, rgba(24, 213, 255, 0.22), transparent 68%);
            filter: blur(4px);
            animation: ll-float 9s ease-in-out infinite;
        }

        .ll-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(390px, 0.92fr);
            align-items: center;
            gap: 48px;
        }

        .ll-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            width: fit-content;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            color: #dceeff;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            font-size: 0.84rem;
            font-weight: 800;
        }

        .ll-pill::before {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--ll-mint);
            box-shadow: 0 0 18px rgba(70, 242, 189, 0.88);
            content: "";
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        .ll-hero h1 {
            max-width: 760px;
            margin: 24px 0 18px;
            font-size: clamp(3.1rem, 7vw, 6.6rem);
            line-height: 0.94;
            letter-spacing: 0;
        }

        .ll-gradient-text {
            background: linear-gradient(135deg, #fff 0%, #b7f2ff 34%, #69a8ff 67%, #ffd2f0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        body[data-theme="light"] .ll-gradient-text {
            background: linear-gradient(135deg, #06111f 0%, #0b5fc8 38%, #12a9cf 62%, #7d43df 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .ll-hero-copy {
            max-width: 650px;
            margin-bottom: 28px;
            color: var(--ll-muted);
            font-size: clamp(1.05rem, 2vw, 1.28rem);
        }

        .ll-hero-actions,
        .ll-inline-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .ll-proof-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-width: 650px;
            margin-top: 30px;
        }

        .ll-proof {
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--ll-radius-md);
            background: rgba(255, 255, 255, 0.06);
        }

        body[data-theme="light"] .ll-proof {
            border-color: rgba(8, 17, 31, 0.1);
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 18px 48px rgba(31, 77, 132, 0.11);
        }

        .ll-proof strong {
            display: block;
            font-size: 1rem;
        }

        .ll-proof span {
            display: block;
            margin-top: 3px;
            color: var(--ll-muted);
            font-size: 0.84rem;
        }

        .ll-mock-stage {
            position: relative;
            min-height: 620px;
        }

        .ll-mock-stage::before {
            position: absolute;
            inset: 42px 2px 20px;
            border-radius: 48px;
            content: "";
            background:
                radial-gradient(circle at 30% 16%, rgba(24, 213, 255, 0.32), transparent 27%),
                radial-gradient(circle at 78% 78%, rgba(255, 76, 184, 0.24), transparent 30%),
                linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            filter: blur(1px);
            box-shadow: 0 44px 120px rgba(0, 0, 0, 0.36);
        }

        .ll-phone {
            position: absolute;
            top: 38px;
            left: 26px;
            width: min(330px, 72vw);
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 36px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.13), rgba(255, 255, 255, 0.04));
            box-shadow: var(--ll-shadow);
            backdrop-filter: blur(22px);
            animation: ll-float 8s ease-in-out infinite;
        }

        .ll-phone-screen {
            padding: 24px 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 27px;
            background:
                radial-gradient(circle at 50% 0%, rgba(45, 140, 255, 0.3), transparent 35%),
                linear-gradient(180deg, rgba(8, 17, 35, 0.98), rgba(8, 12, 25, 0.98));
            color: #f7fbff;
            --ll-muted: #b6c4da;
        }

        .ll-avatar {
            width: 76px;
            height: 76px;
            margin: 0 auto 13px;
            border: 3px solid rgba(255, 255, 255, 0.7);
            border-radius: 999px;
            background:
                linear-gradient(135deg, rgba(24, 213, 255, 0.9), rgba(140, 85, 255, 0.86)),
                #111;
            box-shadow: 0 16px 34px rgba(24, 213, 255, 0.22);
        }

        .ll-phone-screen h3,
        .ll-phone-screen p {
            text-align: center;
        }

        .ll-phone-screen h3 {
            margin-bottom: 4px;
            font-size: 1.35rem;
        }

        .ll-phone-screen p {
            margin-bottom: 16px;
            color: var(--ll-muted);
            font-size: 0.9rem;
        }

        .ll-socials {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .ll-socials span,
        .ll-mini-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 9px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            color: #e8f3ff;
            background: rgba(255, 255, 255, 0.08);
            font-size: 0.76rem;
            font-weight: 800;
        }

        .ll-link-list {
            display: grid;
            gap: 10px;
        }

        .ll-link-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 45px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 15px;
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.08);
            font-size: 0.9rem;
            font-weight: 800;
            text-decoration: none;
            transition: transform 180ms ease, background 180ms ease;
        }

        .ll-link-pill:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.12);
        }

        .ll-powered {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 17px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.78rem;
            font-weight: 750;
        }

        .ll-floating-panel {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: var(--ll-radius-lg);
            background: rgba(10, 19, 42, 0.74);
            box-shadow: var(--ll-shadow);
            backdrop-filter: blur(22px);
            color: #f7fbff;
            --ll-muted: #b6c4da;
        }

        .ll-hero-deck {
            right: 0;
            top: 78px;
            width: 265px;
            padding: 18px;
            animation: ll-float 8s ease-in-out 0.6s infinite;
        }

        .ll-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .ll-panel-heading strong {
            font-size: 0.92rem;
        }

        .ll-card-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .ll-mini-card {
            min-height: 106px;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 16px;
            background:
                linear-gradient(160deg, rgba(140, 85, 255, 0.55), rgba(255, 76, 184, 0.24)),
                rgba(255, 255, 255, 0.07);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .ll-mini-card span {
            display: block;
            margin-top: 54px;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 850;
        }

        .ll-hero-id {
            right: 18px;
            top: 315px;
            width: 242px;
            padding: 17px;
            border-color: rgba(70, 242, 189, 0.26);
            background: linear-gradient(145deg, rgba(7, 54, 38, 0.88), rgba(11, 25, 45, 0.86));
            animation: ll-float 7.5s ease-in-out 1.1s infinite;
        }

        .ll-id-row {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .ll-id-badge {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 13px;
            color: #073626;
            background: linear-gradient(135deg, var(--ll-mint), #c4ffe8);
            font-weight: 950;
        }

        .ll-hero-id p,
        .ll-analytics-panel p {
            margin: 4px 0 0;
            color: var(--ll-muted);
            font-size: 0.82rem;
        }

        .ll-analytics-panel {
            right: 74px;
            bottom: 4px;
            width: 318px;
            padding: 18px;
            border-color: rgba(255, 183, 63, 0.26);
            background: linear-gradient(145deg, rgba(51, 31, 8, 0.88), rgba(12, 22, 42, 0.88));
            animation: ll-float 8.5s ease-in-out 1.8s infinite;
        }

        .ll-bars {
            display: grid;
            gap: 9px;
            margin-top: 16px;
        }

        .ll-bar {
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .ll-bar span {
            display: block;
            width: var(--ll-w);
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--ll-gold), var(--ll-orange));
            animation: ll-grow 1.4s ease both;
        }

        .ll-section {
            padding: 82px 0;
        }

        .ll-section-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 28px;
            margin-bottom: 30px;
        }

        .ll-kicker {
            margin-bottom: 12px;
            color: var(--ll-cyan);
            font-size: 0.82rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .ll-section h2 {
            max-width: 760px;
            margin-bottom: 0;
            font-size: clamp(2.1rem, 4.8vw, 4.3rem);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .ll-section-copy {
            max-width: 560px;
            color: var(--ll-muted);
            font-size: 1.05rem;
        }

        .ll-light-band {
            position: relative;
            color: var(--ll-ink);
            background:
                radial-gradient(circle at 12% 18%, rgba(24, 213, 255, 0.22), transparent 24rem),
                linear-gradient(180deg, #f5fbff, #eaf3ff);
        }

        .ll-light-band .ll-section-copy,
        .ll-light-band .ll-kicker {
            color: #36506d;
        }

        .ll-profile-grid {
            display: grid;
            grid-template-columns: minmax(300px, 420px) minmax(0, 1fr);
            align-items: center;
            gap: 46px;
        }

        .ll-profile-preview {
            padding: 18px;
            border: 1px solid rgba(8, 17, 31, 0.1);
            border-radius: 38px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 28px 80px rgba(31, 77, 132, 0.18);
        }

        .ll-profile-preview .ll-phone-screen {
            color: var(--ll-text);
        }

        .ll-feature-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .ll-feature {
            min-height: 150px;
            padding: 20px;
            border: 1px solid rgba(8, 17, 31, 0.1);
            border-radius: var(--ll-radius-lg);
            background: rgba(255, 255, 255, 0.74);
            box-shadow: 0 18px 48px rgba(31, 77, 132, 0.12);
        }

        .ll-feature strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1.05rem;
        }

        .ll-feature p {
            margin: 0;
            color: #49627c;
        }

        .ll-suite-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .ll-product-card {
            position: relative;
            min-height: 390px;
            padding: 22px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--ll-radius-lg);
            background: var(--ll-panel);
            box-shadow: 0 18px 56px rgba(0, 0, 0, 0.24);
            overflow: hidden;
            transition: transform 180ms ease, border-color 180ms ease;
        }

        body[data-theme="light"] .ll-product-card {
            border-color: rgba(8, 17, 31, 0.1);
            background: rgba(255, 255, 255, 0.78);
        }

        body[data-theme="light"] .ll-product-card p,
        body[data-theme="light"] .ll-micro-stat span {
            color: #52677f;
        }

        .ll-product-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.28);
        }

        .ll-product-card::after {
            position: absolute;
            inset: auto -40px -52px 38px;
            height: 150px;
            border-radius: 999px;
            content: "";
            background: var(--ll-accent);
            filter: blur(50px);
            opacity: 0.32;
        }

        .ll-product-card > * {
            position: relative;
            z-index: 1;
        }

        .ll-product-card h3 {
            margin: 18px 0 8px;
            font-size: 1.32rem;
        }

        .ll-product-card p {
            color: var(--ll-muted);
        }

        .ll-pricing-section {
            position: relative;
            background:
                radial-gradient(circle at 18% 18%, rgba(24, 213, 255, 0.2), transparent 25rem),
                radial-gradient(circle at 84% 28%, rgba(255, 183, 63, 0.12), transparent 24rem);
        }

        .ll-billing-toggle {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            border: 1px solid var(--ll-line);
            border-radius: 999px;
            background: var(--ll-glass);
        }

        .ll-billing-toggle button {
            min-height: 38px;
            padding: 0 15px;
            border-radius: 999px;
            color: var(--ll-muted);
            background: transparent;
            cursor: pointer;
            font-weight: 900;
        }

        .ll-billing-toggle button[aria-pressed="true"] {
            color: #06111f;
            background: linear-gradient(135deg, var(--ll-cyan), #bff6ff);
            box-shadow: 0 12px 28px rgba(24, 213, 255, 0.2);
        }

        .ll-save-note {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            color: #06111f;
            background: linear-gradient(135deg, var(--ll-gold), #fff4b8);
            font-size: 0.78rem;
            font-weight: 950;
        }

        .ll-pricing-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin-top: 28px;
        }

        .ll-price-card {
            position: relative;
            display: flex;
            min-height: 520px;
            padding: 21px;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius-lg);
            background: var(--ll-panel);
            box-shadow: 0 18px 56px rgba(0, 0, 0, 0.2);
            flex-direction: column;
            overflow: hidden;
            transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .ll-price-card:hover {
            transform: translateY(-4px);
            border-color: rgba(24, 213, 255, 0.42);
            box-shadow: 0 26px 74px rgba(0, 0, 0, 0.26);
        }

        .ll-price-card::before {
            position: absolute;
            inset: -60px -70px auto auto;
            width: 170px;
            height: 170px;
            border-radius: 999px;
            content: "";
            background: var(--ll-plan-glow);
            filter: blur(34px);
            opacity: 0.42;
        }

        .ll-price-card > * {
            position: relative;
            z-index: 1;
        }

        .ll-price-card.ll-featured {
            border-color: rgba(24, 213, 255, 0.52);
            box-shadow: 0 28px 90px rgba(24, 213, 255, 0.16);
        }

        .ll-plan-badge {
            display: inline-flex;
            width: fit-content;
            min-height: 29px;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            color: #06111f;
            background: var(--ll-plan-badge);
            font-size: 0.75rem;
            font-weight: 950;
        }

        .ll-price-card h3 {
            margin: 18px 0 8px;
            font-size: 1.24rem;
        }

        .ll-plan-positioning {
            min-height: 48px;
            color: var(--ll-muted);
            font-size: 0.92rem;
        }

        .ll-plan-price {
            margin: 18px 0 4px;
            font-size: 2.35rem;
            font-weight: 950;
            line-height: 1;
        }

        .ll-plan-price small {
            color: var(--ll-muted);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .ll-plan-yearly {
            min-height: 22px;
            color: var(--ll-muted);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .ll-plan-list {
            display: grid;
            gap: 9px;
            margin: 18px 0 20px;
            padding: 0;
            list-style: none;
        }

        .ll-plan-list li {
            display: grid;
            grid-template-columns: 18px 1fr;
            gap: 8px;
            color: var(--ll-muted);
            font-size: 0.86rem;
        }

        .ll-plan-list li::before {
            display: grid;
            width: 18px;
            height: 18px;
            place-items: center;
            border-radius: 999px;
            color: #06111f;
            background: var(--ll-plan-badge);
            content: "✓";
            font-size: 0.7rem;
            font-weight: 950;
        }

        .ll-plan-list.ll-exclusions li::before {
            color: var(--ll-muted);
            background: rgba(255, 255, 255, 0.08);
            content: "–";
        }

        body[data-theme="light"] .ll-plan-list.ll-exclusions li::before {
            background: rgba(8, 17, 31, 0.07);
        }

        .ll-price-card .ll-button {
            margin-top: auto;
        }

        .ll-limit-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .ll-limit-card {
            padding: 18px;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius-md);
            background: var(--ll-glass);
        }

        .ll-limit-card strong {
            display: block;
            margin-bottom: 6px;
        }

        .ll-limit-card p {
            margin: 0;
            color: var(--ll-muted);
            font-size: 0.9rem;
        }

        .ll-comparison-wrap {
            margin-top: 28px;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius-lg);
            background: var(--ll-panel);
            overflow-x: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
        }

        .ll-comparison {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        .ll-comparison th,
        .ll-comparison td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ll-line-soft);
            text-align: center;
        }

        .ll-comparison th:first-child,
        .ll-comparison td:first-child {
            text-align: left;
        }

        .ll-comparison th {
            color: var(--ll-text);
            font-size: 0.82rem;
            text-transform: uppercase;
        }

        .ll-comparison td {
            color: var(--ll-muted);
            font-size: 0.92rem;
            font-weight: 800;
        }

        .ll-check {
            color: var(--ll-mint);
            font-weight: 950;
        }

        .ll-limited {
            color: var(--ll-amber);
        }

        .ll-estimator {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(330px, 1fr);
            gap: 22px;
            margin-top: 28px;
            padding: 24px;
            border: 1px solid var(--ll-line);
            border-radius: var(--ll-radius-xl);
            background:
                radial-gradient(circle at 88% 16%, rgba(70, 242, 189, 0.14), transparent 22rem),
                var(--ll-panel);
            box-shadow: 0 24px 74px rgba(0, 0, 0, 0.2);
        }

        .ll-field-group {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }

        .ll-field-group label {
            font-weight: 900;
        }

        .ll-seat-controls {
            display: grid;
            grid-template-columns: 1fr 82px;
            gap: 12px;
            align-items: center;
        }

        .ll-seat-controls input[type="range"] {
            accent-color: var(--ll-cyan);
            width: 100%;
        }

        .ll-seat-controls input[type="number"] {
            width: 100%;
            min-height: 44px;
            padding: 0 10px;
            border: 1px solid var(--ll-line);
            border-radius: 12px;
            color: var(--ll-text);
            background: var(--ll-glass);
            font: inherit;
            font-weight: 900;
        }

        .ll-estimator-results {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .ll-estimate-box {
            padding: 16px;
            border: 1px solid var(--ll-line-soft);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.065);
        }

        body[data-theme="light"] .ll-estimate-box {
            background: rgba(8, 17, 31, 0.045);
        }

        .ll-estimate-box span {
            display: block;
            color: var(--ll-muted);
            font-size: 0.8rem;
            font-weight: 850;
        }

        .ll-estimate-box strong {
            display: block;
            margin-top: 4px;
            font-size: 1.55rem;
        }

        .ll-estimator-disclaimer {
            margin: 14px 0 0;
            color: var(--ll-muted);
            font-size: 0.84rem;
        }

        .ll-enterprise-message {
            display: none;
            margin-top: 14px;
            padding: 13px;
            border: 1px solid rgba(255, 183, 63, 0.28);
            border-radius: 14px;
            color: var(--ll-text);
            background: rgba(255, 183, 63, 0.1);
            font-weight: 800;
        }

        .ll-enterprise-message.ll-visible {
            display: block;
        }

        .ll-micro-ui {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }

        .ll-micro-line,
        .ll-micro-button {
            height: 15px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.13);
        }

        .ll-micro-button {
            height: 38px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.09);
        }

        .ll-micro-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .ll-micro-stat {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
        }

        .ll-micro-stat strong {
            display: block;
        }

        .ll-micro-stat span {
            color: var(--ll-muted);
            font-size: 0.76rem;
            font-weight: 800;
        }

        .ll-deck-section {
            position: relative;
            background:
                radial-gradient(circle at 22% 14%, rgba(255, 76, 184, 0.23), transparent 26rem),
                radial-gradient(circle at 82% 32%, rgba(140, 85, 255, 0.26), transparent 28rem),
                linear-gradient(145deg, #0c0718, #241039 58%, #100a20);
            color: #f7fbff;
            --ll-muted: #d7c7ee;
        }

        .ll-deck-showcase {
            display: grid;
            grid-template-columns: minmax(280px, 0.82fr) minmax(0, 1fr);
            align-items: center;
            gap: 40px;
        }

        .ll-collector-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            perspective: 1000px;
        }

        .ll-collectible {
            min-height: 280px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            background:
                radial-gradient(circle at 30% 12%, rgba(255, 255, 255, 0.28), transparent 24%),
                linear-gradient(160deg, rgba(140, 85, 255, 0.92), rgba(255, 76, 184, 0.44)),
                #160c2d;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.34);
            transform: rotate(var(--ll-tilt));
            transition: transform 180ms ease;
        }

        .ll-collectible:hover {
            transform: rotate(0deg) translateY(-7px);
        }

        .ll-rarity {
            display: inline-flex;
            padding: 6px 9px;
            border-radius: 999px;
            color: #160c2d;
            background: #fff;
            font-size: 0.72rem;
            font-weight: 950;
        }

        .ll-collectible h3 {
            margin: 132px 0 8px;
            font-size: 1.2rem;
        }

        .ll-collectible p {
            margin: 0;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.88rem;
        }

        .ll-id-section {
            color: var(--ll-ink);
            background:
                radial-gradient(circle at 72% 18%, rgba(70, 242, 189, 0.34), transparent 26rem),
                linear-gradient(180deg, #eafff7, #f8fffc);
        }

        .ll-id-layout,
        .ll-analytics-layout {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(320px, 1fr);
            align-items: center;
            gap: 42px;
        }

        .ll-id-section .ll-kicker {
            color: #177f5b;
        }

        .ll-id-section .ll-section-copy {
            color: #385c51;
        }

        .ll-identity-card {
            padding: 26px;
            border: 1px solid rgba(7, 54, 38, 0.12);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 30px 80px rgba(25, 184, 120, 0.18);
        }

        .ll-identity-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .ll-identity-name {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ll-identity-name .ll-id-badge {
            width: 58px;
            height: 58px;
            border-radius: 18px;
        }

        .ll-identity-name h3 {
            margin-bottom: 2px;
            font-size: 1.5rem;
        }

        .ll-identity-name p,
        .ll-connection p {
            margin: 0;
            color: #426357;
        }

        .ll-verified-chip {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 11px;
            border-radius: 999px;
            color: #073626;
            background: rgba(70, 242, 189, 0.34);
            font-weight: 900;
        }

        .ll-connection-list {
            display: grid;
            gap: 10px;
        }

        .ll-connection {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            border: 1px solid rgba(7, 54, 38, 0.1);
            border-radius: 15px;
            background: rgba(7, 54, 38, 0.055);
        }

        .ll-provider-panel {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }

        .ll-analytics-section {
            background:
                radial-gradient(circle at 80% 12%, rgba(255, 183, 63, 0.2), transparent 27rem),
                linear-gradient(145deg, #120c05, #1b1322 52%, #080d18);
            color: #f7fbff;
            --ll-muted: #dfcdb5;
        }

        .ll-dashboard-card {
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: var(--ll-shadow);
            backdrop-filter: blur(22px);
            color: #f7fbff;
        }

        .ll-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .ll-stat {
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
        }

        .ll-stat span {
            display: block;
            color: var(--ll-muted);
            font-size: 0.78rem;
            font-weight: 850;
        }

        .ll-stat strong {
            display: block;
            margin-top: 5px;
            font-size: 1.5rem;
        }

        .ll-chart {
            height: 190px;
            margin: 18px 0;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            background:
                linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
                rgba(255, 255, 255, 0.055);
            background-size: 100% 25%, 20% 100%, auto;
        }

        .ll-chart svg {
            width: 100%;
            height: 100%;
        }

        .ll-chart path {
            stroke-dasharray: 520;
            stroke-dashoffset: 520;
            animation: ll-draw 1.8s ease forwards;
        }

        .ll-source-list {
            display: grid;
            gap: 10px;
        }

        .ll-source {
            display: grid;
            grid-template-columns: 96px 1fr 44px;
            align-items: center;
            gap: 12px;
            color: var(--ll-muted);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .ll-footer {
            padding: 42px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(5, 8, 20, 0.72);
        }

        body[data-theme="light"] .ll-footer {
            border-top-color: rgba(8, 17, 31, 0.1);
            background: rgba(246, 251, 255, 0.82);
        }

        .ll-footer-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 22px;
            align-items: center;
        }

        .ll-footer p {
            max-width: 650px;
            margin: 12px 0 0;
            color: var(--ll-muted);
        }

        .ll-footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: end;
            gap: 14px;
        }

        .ll-footer-links a {
            color: var(--ll-muted);
            font-size: 0.9rem;
            font-weight: 750;
            text-decoration: none;
        }

        .ll-compliance {
            margin-top: 20px;
            color: rgba(182, 196, 218, 0.68);
            font-size: 0.78rem;
        }

        .ll-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(2, 6, 15, 0.72);
            backdrop-filter: blur(14px);
            color: #f7fbff;
            --ll-muted: #b6c4da;
        }

        .ll-modal[aria-hidden="false"] {
            display: flex;
        }

        .ll-modal-panel {
            width: min(100%, 560px);
            max-height: min(720px, calc(100vh - 44px));
            overflow: auto;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 28px;
            background:
                radial-gradient(circle at 20% 0%, rgba(24, 213, 255, 0.18), transparent 42%),
                linear-gradient(180deg, rgba(13, 25, 54, 0.98), rgba(6, 12, 25, 0.98));
            box-shadow: 0 34px 120px rgba(0, 0, 0, 0.58);
            animation: ll-modal-in 180ms ease both;
        }

        .ll-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 24px 14px;
        }

        .ll-modal-kicker {
            margin-bottom: 6px;
            color: var(--ll-mint);
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .ll-modal-title {
            margin-bottom: 0;
            font-size: 1.8rem;
            line-height: 1.08;
        }

        .ll-close {
            display: grid;
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 999px;
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
            font-size: 1.5rem;
            line-height: 1;
        }

        .ll-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 0 24px 18px;
        }

        .ll-tab {
            min-height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            color: var(--ll-muted);
            background: rgba(255, 255, 255, 0.06);
            cursor: pointer;
            font-weight: 850;
        }

        .ll-tab[aria-selected="true"] {
            color: var(--ll-ink);
            background: linear-gradient(135deg, var(--ll-mint), #dfffee);
        }

        .ll-modal-body {
            padding: 0 24px 24px;
        }

        .ll-modal-body h3 {
            margin-bottom: 8px;
            font-size: 1.32rem;
        }

        .ll-panel-copy,
        .ll-helper {
            color: var(--ll-muted);
        }

        .ll-provider-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .ll-provider {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            min-height: 54px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            color: var(--ll-text);
            background: rgba(255, 255, 255, 0.07);
            cursor: pointer;
            font-weight: 850;
            text-align: left;
        }

        .ll-provider > span {
            display: inline-flex;
            align-items: center;
            gap: 11px;
        }

        .ll-provider small {
            color: var(--ll-muted);
            font-weight: 850;
        }

        .ll-provider[disabled] {
            cursor: not-allowed;
            opacity: 0.58;
        }

        .ll-provider-icon {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 10px;
            color: var(--ll-ink);
            background: #fff;
            font-size: 0.78rem;
            font-weight: 950;
        }

        .ll-demo-message {
            display: none;
            margin-top: 16px;
            padding: 14px;
            border: 1px solid rgba(70, 242, 189, 0.24);
            border-radius: 16px;
            color: #eafff7;
            background: rgba(70, 242, 189, 0.09);
        }

        .ll-demo-message.ll-visible {
            display: block;
        }

        .ll-loading {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .ll-loading::before {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.26);
            border-top-color: var(--ll-mint);
            border-radius: 999px;
            content: "";
            animation: ll-spin 700ms linear infinite;
        }

        .ll-modal-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        @keyframes ll-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        @keyframes ll-grow {
            from { width: 0; }
            to { width: var(--ll-w); }
        }

        @keyframes ll-draw {
            to { stroke-dashoffset: 0; }
        }

        @keyframes ll-modal-in {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes ll-spin {
            to { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 1ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 1ms !important;
            }
        }

        @media (max-width: 1040px) {
            .ll-hero-grid,
            .ll-profile-grid,
            .ll-deck-showcase,
            .ll-id-layout,
            .ll-analytics-layout {
                grid-template-columns: 1fr;
            }

            .ll-mock-stage {
                min-height: 650px;
                max-width: 680px;
                margin: 0 auto;
                width: 100%;
            }

            .ll-suite-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-pricing-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-pricing-grid .ll-price-card:last-child {
                grid-column: 1 / -1;
            }

            .ll-limit-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ll-estimator {
                grid-template-columns: 1fr;
            }

            .ll-collector-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .ll-nav-inner {
                flex-wrap: wrap;
                padding: 12px 0;
            }

            .ll-menu-button {
                display: grid;
                margin-left: auto;
            }

            .ll-nav-links,
            .ll-nav-actions {
                display: none;
                flex: 1 0 100%;
                align-items: stretch;
                justify-content: stretch;
                flex-direction: column;
            }

            .ll-nav.ll-open .ll-nav-links,
            .ll-nav.ll-open .ll-nav-actions {
                display: flex;
            }

            .ll-nav-links a,
            .ll-nav-actions .ll-button {
                justify-content: center;
            }

            .ll-hero {
                padding-top: 52px;
            }

            .ll-section-header {
                display: block;
            }

            .ll-section-copy {
                margin-top: 14px;
            }

            .ll-proof-row,
            .ll-feature-list,
            .ll-stat-grid {
                grid-template-columns: 1fr;
            }

            .ll-source {
                grid-template-columns: 84px 1fr 42px;
            }

            .ll-billing-toggle {
                margin-top: 18px;
            }

            .ll-footer-grid {
                grid-template-columns: 1fr;
            }

            .ll-footer-links {
                justify-content: start;
            }
        }

        @media (max-width: 620px) {
            .ll-shell {
                width: min(100% - 26px, var(--ll-max));
            }

            .ll-logo-word {
                max-width: 140px;
            }

            .ll-hero h1 {
                font-size: clamp(2.7rem, 15vw, 4.2rem);
            }

            .ll-mock-stage {
                min-height: 780px;
            }

            .ll-phone {
                left: 50%;
                transform: translateX(-50%);
            }

            .ll-phone {
                animation: none;
            }

            .ll-floating-panel {
                position: relative;
                inset: auto;
                width: auto;
                margin: 18px 0 0;
            }

            .ll-hero-deck,
            .ll-hero-id,
            .ll-analytics-panel {
                right: auto;
                top: auto;
                bottom: auto;
                animation: none;
            }

            .ll-hero-deck {
                margin-top: 420px;
            }

            .ll-suite-grid,
            .ll-pricing-grid,
            .ll-limit-grid,
            .ll-collector-grid {
                grid-template-columns: 1fr;
            }

            .ll-pricing-grid .ll-price-card:last-child {
                grid-column: auto;
            }

            .ll-price-card {
                min-height: auto;
            }

            .ll-seat-controls,
            .ll-estimator-results {
                grid-template-columns: 1fr;
            }

            .ll-collectible {
                min-height: 230px;
                transform: none;
            }

            .ll-collectible h3 {
                margin-top: 90px;
            }

            .ll-modal {
                padding: 12px;
            }

            .ll-tabs {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-theme="dark">
<div class="ll-page">
    <header class="ll-nav" data-ll-nav>
        <div class="ll-shell ll-nav-inner">
            <a class="ll-brand" href="#home" aria-label="Livelatch home">
                <span class="ll-brand-mark">
                    <img class="ll-logo-img" src="/logos/livelatch_social_icon.png" alt="" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">L</span>
                </span>
                <span>
                    <img class="ll-logo-img ll-logo-word" src="/logos/livelatch_dark.png" alt="Livelatch" data-ll-logo data-logo-context="theme" data-logo-light="/logos/livelatch_light.png" data-logo-dark="/logos/livelatch_dark.png" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">Livelatch</span>
                </span>
            </a>

            <button class="ll-menu-button" type="button" data-ll-menu aria-expanded="false" aria-label="Open menu"><span></span></button>

            <nav class="ll-nav-links" aria-label="Primary navigation">
                <a href="#home">Home</a>
                <a href="#latchdeck">LatchDeck</a>
                <a href="#latchid">LatchID</a>
                <a href="#latchalytics">Latchalytics</a>
                <a href="#pricing">Pricing</a>
                <a href="#alpha">Alpha</a>
            </nav>

            <nav class="ll-nav-actions" aria-label="Account actions">
                <button class="ll-theme-toggle" type="button" data-ll-theme-toggle aria-label="Switch to light mode" aria-pressed="false">
                    <span class="ll-theme-dark" aria-hidden="true">Dark</span>
                    <span class="ll-theme-light" aria-hidden="true">Light</span>
                </button>
                <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="login">Log in</button>
                <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Create LatchID</button>
            </nav>
        </div>
    </header>

    <main id="home">
        <section class="ll-hero" aria-labelledby="llHeroTitle">
            <div class="ll-shell ll-hero-grid">
                <div>
                    <span class="ll-pill">Alpha Experience</span>
                    <h1 id="llHeroTitle"><span class="ll-gradient-text">Your creator home base. Everything, latched together.</span></h1>
                    <p class="ll-hero-copy">Livelatch brings your links, content, community moments, identity, and creator tools into one living profile.</p>
                    <div class="ll-hero-actions">
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Create LatchID</button>
                        <a class="ll-button ll-button-ghost" href="#ecosystem">Explore the ecosystem</a>
                        <a class="ll-button ll-button-ghost" href="#pricing">See pricing</a>
                    </div>
                    <div class="ll-proof-row" aria-label="Livelatch positioning">
                        <div class="ll-proof"><strong>Not just links.</strong><span>A home for the creator world around you.</span></div>
                        <div class="ll-proof"><strong>Online Community as a Service</strong><span>Profiles, identity, moments, and insight in one place.</span></div>
                        <div class="ll-proof"><strong>OAuth first</strong><span>No email/password signup in the LatchID flow.</span></div>
                    </div>
                </div>

                <div class="ll-mock-stage" aria-label="Livelatch creator profile, LatchDeck, LatchID, and Latchalytics preview">
                    <div class="ll-phone">
                        <div class="ll-phone-screen">
                            <div class="ll-avatar" aria-hidden="true"></div>
                            <h3>@alexcreates</h3>
                            <p>Everything I create, all in one place.</p>
                            <div class="ll-socials" aria-label="Connected social channels">
                                <span>TikTok</span><span>Twitch</span><span>YouTube</span><span>Discord</span>
                            </div>
                            <div class="ll-link-list">
                                <a class="ll-link-pill" href="#link-page">Latest video <span>→</span></a>
                                <a class="ll-link-pill" href="#latchdeck">View LatchDeck <span>→</span></a>
                                <a class="ll-link-pill" href="#latchid">Verified LatchID <span>✓</span></a>
                            </div>
                            <div class="ll-powered">
                                <img class="ll-logo-img ll-logo-small" src="/logos/livelatch_dark.png" alt="Powered by Livelatch" data-ll-logo data-logo-context="dark" data-logo-light="/logos/livelatch_light.png" data-logo-dark="/logos/livelatch_dark.png" onerror="this.hidden=true">
                                <span class="ll-logo-fallback">Powered by Livelatch</span>
                            </div>
                        </div>
                    </div>

                    <aside class="ll-floating-panel ll-hero-deck">
                        <div class="ll-panel-heading">
                            <img class="ll-logo-img ll-logo-small" src="/logos/latchdeck_dark.png" alt="LatchDeck" data-ll-logo data-logo-context="dark" data-logo-light="/logos/latchdeck_light.png" data-logo-dark="/logos/latchdeck_dark.png" onerror="this.hidden=true">
                            <span class="ll-logo-fallback">LatchDeck</span>
                            <span class="ll-mini-tag">Live drop</span>
                        </div>
                        <div class="ll-card-strip">
                            <div class="ll-mini-card"><span>Launch Night</span></div>
                            <div class="ll-mini-card"><span>First 100</span></div>
                            <div class="ll-mini-card"><span>Karaoke</span></div>
                        </div>
                    </aside>

                    <aside class="ll-floating-panel ll-hero-id">
                        <div class="ll-id-row">
                            <span class="ll-id-badge">✓</span>
                            <div>
                                <strong>alex#0001</strong>
                                <p>Verified Creator</p>
                            </div>
                        </div>
                    </aside>

                    <aside class="ll-floating-panel ll-analytics-panel">
                        <div class="ll-panel-heading">
                            <img class="ll-logo-img ll-logo-small" src="/logos/latchalytics_dark.png" alt="Latchalytics" data-ll-logo data-logo-context="dark" data-logo-light="/logos/latchalytics_light.png" data-logo-dark="/logos/latchalytics_dark.png" onerror="this.hidden=true">
                            <span class="ll-logo-fallback">Latchalytics</span>
                            <strong>128.7K clicks</strong>
                        </div>
                        <p>Mini source mix</p>
                        <div class="ll-bars" aria-hidden="true">
                            <div class="ll-bar"><span style="--ll-w: 82%"></span></div>
                            <div class="ll-bar"><span style="--ll-w: 58%"></span></div>
                            <div class="ll-bar"><span style="--ll-w: 35%"></span></div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section class="ll-section ll-light-band" id="link-page" aria-labelledby="llProfileTitle">
            <div class="ll-shell ll-profile-grid">
                <div class="ll-profile-preview">
                    <div class="ll-phone-screen">
                        <div class="ll-avatar" aria-hidden="true"></div>
                        <h3>@alexcreates</h3>
                        <p>Artist &amp; Content Creator<br>Everything I create, all in one place.</p>
                        <div class="ll-socials">
                            <span>TikTok</span><span>Twitch</span><span>YouTube</span><span>Discord</span><span>Instagram</span>
                        </div>
                        <div class="ll-link-list">
                            <a class="ll-link-pill" href="#">Watch my latest video <span>→</span></a>
                            <a class="ll-link-pill" href="#">Join my Discord <span>→</span></a>
                            <a class="ll-link-pill" href="#latchdeck">View my LatchDeck <span>→</span></a>
                            <a class="ll-link-pill" href="#">Shop merch <span>→</span></a>
                            <a class="ll-link-pill" href="#">Request a creator <span>→</span></a>
                        </div>
                        <div class="ll-powered">
                            <img class="ll-logo-img ll-logo-small" src="/logos/livelatch_dark.png" alt="Powered by Livelatch" data-ll-logo data-logo-context="dark" data-logo-light="/logos/livelatch_light.png" data-logo-dark="/logos/livelatch_dark.png" onerror="this.hidden=true">
                            <span class="ll-logo-fallback">Powered by Livelatch</span>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="ll-kicker">Creator profile preview</p>
                    <h2 id="llProfileTitle">Not just links. A home.</h2>
                    <p class="ll-section-copy">A Livelatch profile is designed to feel alive: links, drops, social channels, verification, and creator context all working together in one premium destination.</p>
                    <div class="ll-feature-list">
                        <div class="ll-feature"><strong>Living profile</strong><p>Links, content, announcements, community prompts, and creator identity in one focused surface.</p></div>
                        <div class="ll-feature"><strong>Creator friendly</strong><p>Built for streamers, artists, educators, performers, makers, and community-led brands.</p></div>
                        <div class="ll-feature"><strong>Ecosystem aware</strong><p>LatchDeck, LatchID, and Latchalytics can appear where they add context.</p></div>
                        <div class="ll-feature"><strong>Alpha ready</strong><p>A polished standalone demo before Supabase auth and production onboarding are connected.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ll-section" id="ecosystem" aria-labelledby="llSuiteTitle">
            <div class="ll-shell">
                <div class="ll-section-header">
                    <div>
                        <p class="ll-kicker">Product suite</p>
                        <h2 id="llSuiteTitle">One creator ecosystem, four connected layers.</h2>
                    </div>
                    <p class="ll-section-copy">Livelatch is the master platform. LatchDeck captures community memories. LatchID keeps identity portable. Latchalytics turns activity into clarity.</p>
                </div>

                <div class="ll-suite-grid">
                    <article class="ll-product-card" style="--ll-accent: rgba(24, 213, 255, 0.72)">
                        <img class="ll-logo-img ll-logo-small" src="/logos/livelatch_dark.png" alt="Livelatch" data-ll-logo data-logo-context="theme" data-logo-light="/logos/livelatch_light.png" data-logo-dark="/logos/livelatch_dark.png" onerror="this.hidden=true">
                        <span class="ll-logo-fallback">Livelatch</span>
                        <h3>Your creator home base.</h3>
                        <p>Bring links, content, community, identity, and tools together in one living profile.</p>
                        <div class="ll-micro-ui" aria-hidden="true">
                            <span class="ll-micro-line" style="width: 72%"></span>
                            <span class="ll-micro-button"></span>
                            <span class="ll-micro-button"></span>
                            <span class="ll-micro-button"></span>
                        </div>
                    </article>

                    <article class="ll-product-card" style="--ll-accent: rgba(255, 76, 184, 0.72)">
                        <img class="ll-logo-img ll-logo-small" src="/logos/latchdeck_dark.png" alt="LatchDeck" data-ll-logo data-logo-context="theme" data-logo-light="/logos/latchdeck_light.png" data-logo-dark="/logos/latchdeck_dark.png" onerror="this.hidden=true">
                        <span class="ll-logo-fallback">LatchDeck</span>
                        <h3>Community moments.</h3>
                        <p>Create digital cards tied to stream moments, milestones, events, drops, and memories.</p>
                        <div class="ll-card-strip" aria-hidden="true">
                            <div class="ll-mini-card"><span>Launch Night</span></div>
                            <div class="ll-mini-card"><span>First 100</span></div>
                            <div class="ll-mini-card"><span>Survivor</span></div>
                        </div>
                    </article>

                    <article class="ll-product-card" style="--ll-accent: rgba(70, 242, 189, 0.72)">
                        <img class="ll-logo-img ll-logo-small" src="/logos/latchid_dark.png" alt="LatchID" data-ll-logo data-logo-context="theme" data-logo-light="/logos/latchid_light.png" data-logo-dark="/logos/latchid_dark.png" onerror="this.hidden=true">
                        <span class="ll-logo-fallback">LatchID</span>
                        <h3>Universal identity.</h3>
                        <p>A friendly, verified account layer created through trusted social OAuth providers.</p>
                        <div class="ll-micro-ui" aria-hidden="true">
                            <div class="ll-id-row"><span class="ll-id-badge">✓</span><strong>alex#0001</strong></div>
                            <span class="ll-micro-button"></span>
                            <span class="ll-micro-button"></span>
                        </div>
                    </article>

                    <article class="ll-product-card" style="--ll-accent: rgba(255, 183, 63, 0.78)">
                        <img class="ll-logo-img ll-logo-small" src="/logos/latchalytics_dark.png" alt="Latchalytics" data-ll-logo data-logo-context="theme" data-logo-light="/logos/latchalytics_light.png" data-logo-dark="/logos/latchalytics_dark.png" onerror="this.hidden=true">
                        <span class="ll-logo-fallback">Latchalytics</span>
                        <h3>Insight and momentum.</h3>
                        <p>Understand link clicks, engagement, audience behavior, sources, and deck interactions.</p>
                        <div class="ll-micro-stats" aria-hidden="true">
                            <div class="ll-micro-stat"><span>Clicks</span><strong>128.7K</strong></div>
                            <div class="ll-micro-stat"><span>Audience</span><strong>57.3K</strong></div>
                            <div class="ll-micro-stat"><span>Engage</span><strong>24.5K</strong></div>
                            <div class="ll-micro-stat"><span>Deck</span><strong>8.9K</strong></div>
                        </div>
                    </article>
                </div>

                <div class="ll-inline-actions" style="margin-top: 24px">
                    <a class="ll-button ll-button-primary" href="#pricing">Free to start. Priced to grow.</a>
                    <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="signup">Create LatchID</button>
                </div>
            </div>
        </section>

        <section class="ll-section ll-pricing-section" id="pricing" aria-labelledby="llPricingTitle">
            <div class="ll-shell">
                <div class="ll-section-header">
                    <div>
                        <p class="ll-kicker">Pricing</p>
                        <h2 id="llPricingTitle">Free to start. Priced to grow.</h2>
                    </div>
                    <div>
                        <p class="ll-section-copy">Links are the doorway. The community is the home. Start with a real creator profile, then add growth tools when your audience is ready.</p>
                        <div class="ll-billing-toggle" role="group" aria-label="Billing frequency">
                            <button type="button" data-ll-billing="monthly" aria-pressed="true">Monthly</button>
                            <button type="button" data-ll-billing="yearly" aria-pressed="false">Yearly</button>
                            <span class="ll-save-note">2 months free</span>
                        </div>
                    </div>
                </div>

                <div class="ll-pricing-grid" aria-label="Estimated Livelatch pricing plans">
                    <article class="ll-price-card" style="--ll-plan-glow: rgba(24, 213, 255, 0.7); --ll-plan-badge: linear-gradient(135deg, #bff6ff, var(--ll-cyan));">
                        <span class="ll-plan-badge">Free</span>
                        <h3>Free</h3>
                        <p class="ll-plan-positioning">Start your creator home.</p>
                        <div class="ll-plan-price">$0<small>/month</small></div>
                        <p class="ll-plan-yearly">Explore the ecosystem at no cost.</p>
                        <ul class="ll-plan-list">
                            <li>Public Livelatch profile</li>
                            <li>Unlimited basic links</li>
                            <li>Basic profile themes</li>
                            <li>Basic click counts</li>
                            <li>Create a LatchID</li>
                            <li>Limited LatchDeck viewer/card preview</li>
                        </ul>
                        <ul class="ll-plan-list ll-exclusions">
                            <li>No advanced analytics</li>
                            <li>No custom domains</li>
                            <li>No team members</li>
                        </ul>
                        <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="signup">Start free</button>
                    </article>

                    <article class="ll-price-card ll-featured" style="--ll-plan-glow: rgba(140, 85, 255, 0.65); --ll-plan-badge: linear-gradient(135deg, #f1d8ff, var(--ll-magenta));">
                        <span class="ll-plan-badge">Most Popular</span>
                        <h3>Creator Plus</h3>
                        <p class="ll-plan-positioning">For creators building a real community.</p>
                        <div class="ll-plan-price"><span data-ll-price="plus">$7</span><small data-ll-period>/month</small></div>
                        <p class="ll-plan-yearly" data-ll-yearly-note="plus">$70/year with 2 months free.</p>
                        <ul class="ll-plan-list">
                            <li>Everything in Free</li>
                            <li>Advanced themes</li>
                            <li>More profile blocks</li>
                            <li>LatchDeck campaigns</li>
                            <li>Scheduled card drops</li>
                            <li>Latchalytics basic insights</li>
                            <li>Custom social preview cards</li>
                            <li>Remove basic Livelatch branding</li>
                            <li>Early feature access</li>
                        </ul>
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Choose Creator Plus</button>
                    </article>

                    <article class="ll-price-card" style="--ll-plan-glow: rgba(255, 183, 63, 0.72); --ll-plan-badge: linear-gradient(135deg, var(--ll-gold), var(--ll-orange));">
                        <span class="ll-plan-badge">Best for Growth</span>
                        <h3>Creator Pro</h3>
                        <p class="ll-plan-positioning">For serious creators and growing communities.</p>
                        <div class="ll-plan-price"><span data-ll-price="pro">$15</span><small data-ll-period>/month</small></div>
                        <p class="ll-plan-yearly" data-ll-yearly-note="pro">$150/year with 2 months free.</p>
                        <ul class="ll-plan-list">
                            <li>Everything in Creator Plus</li>
                            <li>Custom domain support</li>
                            <li>Advanced Latchalytics</li>
                            <li>Conversion tracking</li>
                            <li>Deeper LatchDeck campaign tools</li>
                            <li>More campaign/card limits</li>
                            <li>Priority support</li>
                            <li>Export analytics data</li>
                            <li>Audience growth insights</li>
                        </ul>
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Choose Creator Pro</button>
                    </article>

                    <article class="ll-price-card" style="--ll-plan-glow: rgba(70, 242, 189, 0.68); --ll-plan-badge: linear-gradient(135deg, #dfffee, var(--ll-mint));">
                        <span class="ll-plan-badge">Teams</span>
                        <h3>Team</h3>
                        <p class="ll-plan-positioning">For creator teams, agencies, studios, and communities.</p>
                        <div class="ll-plan-price"><span data-ll-price="team">$25</span><small data-ll-period>/month</small></div>
                        <p class="ll-plan-yearly" data-ll-yearly-note="team">Includes 2 seats. Extra seats from $8/seat.</p>
                        <ul class="ll-plan-list">
                            <li>Everything in Creator Pro</li>
                            <li>Multi-seat dashboard</li>
                            <li>Shared creator profiles</li>
                            <li>Team roles</li>
                            <li>Approval flows for links/card drops</li>
                            <li>Team analytics</li>
                            <li>Shared asset library</li>
                            <li>Managed creator roster</li>
                            <li>Optional SSO placeholder, future/enterprise</li>
                        </ul>
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Start a team</button>
                    </article>

                    <article class="ll-price-card" style="--ll-plan-glow: rgba(255, 255, 255, 0.48); --ll-plan-badge: linear-gradient(135deg, #fff, #c9d6e8);">
                        <span class="ll-plan-badge">Enterprise</span>
                        <h3>Enterprise / Platinum</h3>
                        <p class="ll-plan-positioning">For large communities, agencies, and managed creator networks.</p>
                        <div class="ll-plan-price">Talk<small> to us</small></div>
                        <p class="ll-plan-yearly">Custom scope and limits.</p>
                        <ul class="ll-plan-list">
                            <li>Managed onboarding</li>
                            <li>Dedicated support</li>
                            <li>Advanced security options</li>
                            <li>Custom integrations</li>
                            <li>White-label or managed mirror options as future-facing demo copy</li>
                            <li>Custom limits</li>
                            <li>Optional self-hosted / managed deployment discussion</li>
                        </ul>
                        <button class="ll-button ll-button-ghost" type="button" data-ll-enterprise>Contact us soon</button>
                        <div class="ll-enterprise-message" data-ll-enterprise-message>Demo only: enterprise contact routing is not connected yet.</div>
                    </article>
                </div>

                <section class="ll-section" aria-labelledby="llFreeLimitsTitle" style="padding-bottom: 0">
                    <div class="ll-section-header">
                        <div>
                            <p class="ll-kicker">Free plan clarity</p>
                            <h2 id="llFreeLimitsTitle">What Free doesn't include</h2>
                        </div>
                        <p class="ll-section-copy">Free gives you a real creator home base, but the growth tools live in the paid plans.</p>
                    </div>
                    <div class="ll-limit-grid">
                        <article class="ll-limit-card"><strong>No advanced Latchalytics</strong><p>Basic click counts are included, deeper source and conversion insight is paid.</p></article>
                        <article class="ll-limit-card"><strong>No custom domain</strong><p>Use Livelatch-hosted profile URLs until upgrading to Pro.</p></article>
                        <article class="ll-limit-card"><strong>No premium themes</strong><p>Free profiles get the essentials, paid plans unlock richer styling.</p></article>
                        <article class="ll-limit-card"><strong>No team seats</strong><p>Collaboration, roles, and approvals live in Team.</p></article>
                        <article class="ll-limit-card"><strong>Limited LatchDeck campaigns</strong><p>Preview the card experience, then launch campaigns on paid plans.</p></article>
                        <article class="ll-limit-card"><strong>Livelatch branding remains</strong><p>Paid plans can reduce or remove basic branding.</p></article>
                        <article class="ll-limit-card"><strong>No priority support</strong><p>Priority help is reserved for Pro, Team, and Enterprise.</p></article>
                    </div>
                </section>

                <section class="ll-section" aria-labelledby="llEstimatorTitle" style="padding-bottom: 0">
                    <div class="ll-section-header">
                        <div>
                            <p class="ll-kicker">Team estimate</p>
                            <h2 id="llEstimatorTitle">Team price estimator</h2>
                        </div>
                        <p class="ll-section-copy">Team includes 2 seats at $25/month. Add more seats at an estimated $8 per seat/month. Yearly billing uses a 2 months free equivalent.</p>
                    </div>
                    <div class="ll-estimator">
                        <div>
                            <div class="ll-field-group">
                                <label for="llSeatRange">Number of seats</label>
                                <div class="ll-seat-controls">
                                    <input id="llSeatRange" type="range" min="2" max="50" value="5" data-ll-seats-range>
                                    <input id="llSeatInput" type="number" min="2" max="50" value="5" data-ll-seats-input aria-label="Number of team seats">
                                </div>
                            </div>
                            <div class="ll-field-group">
                                <span id="llEstimatorBillingLabel">Estimator billing</span>
                                <div class="ll-billing-toggle" role="group" aria-labelledby="llEstimatorBillingLabel">
                                    <button type="button" data-ll-estimator-billing="monthly" aria-pressed="true">Monthly</button>
                                    <button type="button" data-ll-estimator-billing="yearly" aria-pressed="false">Yearly</button>
                                    <span class="ll-save-note">2 months free</span>
                                </div>
                            </div>
                            <p class="ll-estimator-disclaimer">Demo estimate only. Final pricing may change during beta.</p>
                        </div>
                        <div class="ll-estimator-results" aria-live="polite">
                            <div class="ll-estimate-box"><span>Selected seats</span><strong data-ll-estimate="seats">5</strong></div>
                            <div class="ll-estimate-box"><span>Included seats</span><strong data-ll-estimate="included">2</strong></div>
                            <div class="ll-estimate-box"><span>Extra seats</span><strong data-ll-estimate="extra">3</strong></div>
                            <div class="ll-estimate-box"><span>Monthly estimate</span><strong data-ll-estimate="monthly">$49</strong></div>
                            <div class="ll-estimate-box"><span>Yearly estimate</span><strong data-ll-estimate="yearly">$490</strong></div>
                            <div class="ll-estimate-box"><span>Effective monthly</span><strong data-ll-estimate="effective">$40.83</strong></div>
                        </div>
                    </div>
                </section>

                <section class="ll-section" aria-labelledby="llComparisonTitle" style="padding-bottom: 0">
                    <div class="ll-section-header">
                        <div>
                            <p class="ll-kicker">Compare plans</p>
                            <h2 id="llComparisonTitle">Choose the layer that matches your creator stage.</h2>
                        </div>
                    </div>
                    <div class="ll-comparison-wrap">
                        <table class="ll-comparison">
                            <thead>
                                <tr>
                                    <th scope="col">Feature</th>
                                    <th scope="col">Free</th>
                                    <th scope="col">Creator Plus</th>
                                    <th scope="col">Creator Pro</th>
                                    <th scope="col">Team</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Unlimited basic links</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>LatchID</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Basic themes</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Premium themes</td><td>–</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Basic analytics</td><td class="ll-limited">Limited</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Advanced Latchalytics</td><td>–</td><td class="ll-limited">Basic</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>LatchDeck campaigns</td><td class="ll-limited">Preview</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Scheduled card drops</td><td>–</td><td class="ll-check">✓</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Custom domain</td><td>–</td><td>–</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Brand removal</td><td>–</td><td class="ll-limited">Basic</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Team seats</td><td>–</td><td>–</td><td>–</td><td class="ll-check">✓</td></tr>
                                <tr><td>Approval flows</td><td>–</td><td>–</td><td>–</td><td class="ll-check">✓</td></tr>
                                <tr><td>Priority support</td><td>–</td><td>–</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                                <tr><td>Analytics export</td><td>–</td><td>–</td><td class="ll-check">✓</td><td class="ll-check">✓</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>

        <section class="ll-section ll-deck-section" id="latchdeck" aria-labelledby="llDeckTitle">
            <div class="ll-shell ll-deck-showcase">
                <div>
                    <img class="ll-logo-img ll-logo-word" src="/logos/latchdeck_dark.png" alt="LatchDeck" data-ll-logo data-logo-context="dark" data-logo-light="/logos/latchdeck_light.png" data-logo-dark="/logos/latchdeck_dark.png" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">LatchDeck</span>
                    <p class="ll-kicker">Collectible community moments</p>
                    <h2 id="llDeckTitle">Turn stream moments, milestones, and community memories into collectible cards.</h2>
                    <p class="ll-section-copy">Viewers can collect cards as "I was there" moments. The tone is sentimental, special, and community-first without crypto or financial asset language.</p>
                    <div class="ll-inline-actions">
                        <a class="ll-button ll-button-dark" href="#latchdeck">View demo deck</a>
                    </div>
                </div>
                <div class="ll-collector-grid">
                    <article class="ll-collectible" style="--ll-tilt: -4deg"><span class="ll-rarity">Common</span><h3>Launch Night</h3><p>The first live community drop.</p></article>
                    <article class="ll-collectible" style="--ll-tilt: 3deg"><span class="ll-rarity">Rare</span><h3>First 100 Viewers</h3><p>A badge for the earliest crowd.</p></article>
                    <article class="ll-collectible" style="--ll-tilt: -2deg"><span class="ll-rarity">Epic</span><h3>12 Hour Stream Survivor</h3><p>For the people who stayed.</p></article>
                    <article class="ll-collectible" style="--ll-tilt: 4deg"><span class="ll-rarity">Legendary</span><h3>Karaoke Chaos</h3><p>The clip everyone remembers.</p></article>
                </div>
            </div>
        </section>

        <section class="ll-section ll-id-section" id="latchid" aria-labelledby="llIdTitle">
            <div class="ll-shell ll-id-layout">
                <div>
                    <img class="ll-logo-img ll-logo-word" src="/logos/latchid_light.png" alt="LatchID" data-ll-logo data-logo-context="light" data-logo-light="/logos/latchid_light.png" data-logo-dark="/logos/latchid_dark.png" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">LatchID</span>
                    <p class="ll-kicker">Identity layer</p>
                    <h2 id="llIdTitle">Create your LatchID using a trusted social account.</h2>
                    <p class="ll-section-copy">No passwords. No email-first signup. LatchID is the access/passport layer for one identity across your creator world.</p>
                    <div class="ll-provider-panel" aria-label="Demo providers">
                        <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Continue with Google</button>
                    </div>
                </div>

                <aside class="ll-identity-card" aria-label="Mock LatchID identity card">
                    <div class="ll-identity-top">
                        <div class="ll-identity-name">
                            <span class="ll-id-badge">✓</span>
                            <div>
                                <h3>alex#0001</h3>
                                <p>One identity across your creator world.</p>
                            </div>
                        </div>
                        <span class="ll-verified-chip">Verified Creator</span>
                    </div>
                    <div class="ll-connection-list">
                        <div class="ll-connection"><strong>TikTok</strong><p>Connected</p></div>
                        <div class="ll-connection"><strong>Twitch</strong><p>Connected</p></div>
                        <div class="ll-connection"><strong>Discord</strong><p>Connected</p></div>
                        <div class="ll-connection"><strong>YouTube</strong><p>Connected</p></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="ll-section ll-analytics-section" id="latchalytics" aria-labelledby="llAnalyticsTitle">
            <div class="ll-shell ll-analytics-layout">
                <div>
                    <img class="ll-logo-img ll-logo-word" src="/logos/latchalytics_dark.png" alt="Latchalytics" data-ll-logo data-logo-context="dark" data-logo-light="/logos/latchalytics_light.png" data-logo-dark="/logos/latchalytics_dark.png" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">Latchalytics</span>
                    <p class="ll-kicker">Growth and clarity</p>
                    <h2 id="llAnalyticsTitle">Know what is working across links, audience, and moments.</h2>
                    <p class="ll-section-copy">Track link clicks, engagement, audience behavior, top sources, campaign performance, and LatchDeck interactions from one focused insight layer.</p>
                </div>

                <aside class="ll-dashboard-card" aria-label="Mock Latchalytics dashboard">
                    <div class="ll-stat-grid">
                        <div class="ll-stat"><span>Total clicks</span><strong>128.7K</strong></div>
                        <div class="ll-stat"><span>Engagement</span><strong>24.5K</strong></div>
                        <div class="ll-stat"><span>Audience</span><strong>57.3K</strong></div>
                    </div>
                    <div class="ll-chart" aria-hidden="true">
                        <svg viewBox="0 0 500 170" role="img" aria-label="Rising analytics chart">
                            <defs>
                                <linearGradient id="llChartGradient" x1="0" x2="1" y1="0" y2="0">
                                    <stop stop-color="#ffe176"></stop>
                                    <stop offset="0.55" stop-color="#ffb73f"></stop>
                                    <stop offset="1" stop-color="#ff7a32"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M10 128 C80 112 95 54 160 76 S250 138 320 72 S420 22 490 40" fill="none" stroke="url(#llChartGradient)" stroke-width="9" stroke-linecap="round"></path>
                            <path d="M10 128 C80 112 95 54 160 76 S250 138 320 72 S420 22 490 40 L490 170 L10 170 Z" fill="rgba(255,183,63,0.16)"></path>
                        </svg>
                    </div>
                    <div class="ll-source-list">
                        <div class="ll-source"><span>TikTok</span><div class="ll-bar"><span style="--ll-w: 42%"></span></div><strong>42%</strong></div>
                        <div class="ll-source"><span>Instagram</span><div class="ll-bar"><span style="--ll-w: 28%"></span></div><strong>28%</strong></div>
                        <div class="ll-source"><span>YouTube</span><div class="ll-bar"><span style="--ll-w: 16%"></span></div><strong>16%</strong></div>
                        <div class="ll-source"><span>Other</span><div class="ll-bar"><span style="--ll-w: 14%"></span></div><strong>14%</strong></div>
                        <div class="ll-source"><span>Deck redemptions</span><div class="ll-bar"><span style="--ll-w: 64%"></span></div><strong>8.9K</strong></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="ll-section" id="alpha" aria-labelledby="llAlphaTitle">
            <div class="ll-shell">
                <div class="ll-section-header">
                    <div>
                        <p class="ll-kicker">Alpha Experience</p>
                        <h2 id="llAlphaTitle">A static UI prototype for the next Livelatch homepage.</h2>
                    </div>
                    <p class="ll-section-copy">Google sign in now hands off to Supabase LatchID, then returns here to create the Livelatch dashboard session.</p>
                </div>
                <div class="ll-inline-actions">
                    <button class="ll-button ll-button-primary" type="button" data-ll-open-modal="signup">Create LatchID</button>
                    <button class="ll-button ll-button-ghost" type="button" data-ll-open-modal="login">Log in</button>
                </div>
            </div>
        </section>
    </main>

    <footer class="ll-footer">
        <div class="ll-shell">
            <div class="ll-footer-grid">
                <div>
                    <img class="ll-logo-img ll-logo-word" src="/logos/livelatch_dark.png" alt="Livelatch" data-ll-logo data-logo-context="theme" data-logo-light="/logos/livelatch_light.png" data-logo-dark="/logos/livelatch_dark.png" onerror="this.hidden=true">
                    <span class="ll-logo-fallback">Livelatch</span>
                    <p><strong>Livelatch Alpha Experience</strong><br>One ecosystem. Infinite creator moments.</p>
                </div>
                <nav class="ll-footer-links" aria-label="Footer links">
                    <a href="#">Source / Compliance</a>
                    <a href="#">Privacy</a>
                    <a href="#">Terms</a>
                    <a href="#">Contact</a>
                </nav>
            </div>
            <p class="ll-compliance">Livelatch began as a fork of LinkStack and respects its AGPL licensing obligations.</p>
        </div>
    </footer>
</div>

<div class="ll-modal" id="llAuthModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="llModalTitle">
    <div class="ll-modal-panel" role="document">
        <header class="ll-modal-header">
            <div>
                <p class="ll-modal-kicker">LatchID access</p>
                <h2 class="ll-modal-title" id="llModalTitle">Create your LatchID</h2>
            </div>
            <button class="ll-close" type="button" data-ll-close-modal aria-label="Close modal">&times;</button>
        </header>

        <div class="ll-tabs" role="tablist" aria-label="Authentication mode">
            <button class="ll-tab" type="button" role="tab" id="llSignupTab" aria-controls="llSignupPanel" aria-selected="true" data-ll-tab="signup">Create LatchID</button>
            <button class="ll-tab" type="button" role="tab" id="llLoginTab" aria-controls="llLoginPanel" aria-selected="false" data-ll-tab="login">Log in</button>
        </div>

        <div class="ll-modal-body">
            <section id="llSignupPanel" role="tabpanel" aria-labelledby="llSignupTab" data-ll-panel="signup">
                <h3>Create your LatchID</h3>
                <p class="ll-panel-copy">Choose the social account you want to use for your Livelatch identity.</p>
                <div class="ll-provider-list">
                    <button class="ll-provider" type="button" data-ll-provider="Google"><span><span class="ll-provider-icon">G</span>Continue with Google</span><small>OAuth</small></button>
                </div>
                <p class="ll-helper">No passwords. Your LatchID is created through a trusted social login.</p>
                <div class="ll-demo-message" data-ll-demo-message></div>
            </section>

            <section id="llLoginPanel" role="tabpanel" aria-labelledby="llLoginTab" data-ll-panel="login" hidden>
                <h3>Welcome back</h3>
                <p class="ll-panel-copy">Choose the social account linked to your LatchID.</p>
                <div class="ll-provider-list">
                    <button class="ll-provider" type="button" data-ll-provider="Google"><span><span class="ll-provider-icon">G</span>Continue with Google</span><small>OAuth</small></button>
                </div>
                <p class="ll-helper">After authentication, you'll be taken to your dashboard.</p>
                <div class="ll-demo-message" data-ll-demo-message></div>
            </section>
        </div>
    </div>
</div>

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
        var modalTitle = document.getElementById('llModalTitle');
        var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-ll-tab]'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('[data-ll-panel]'));
        var openButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-open-modal]'));
        var closeButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-close-modal]'));
        var menuButton = document.querySelector('[data-ll-menu]');
        var themeButton = document.querySelector('[data-ll-theme-toggle]');
        var themeLogos = Array.prototype.slice.call(document.querySelectorAll('[data-ll-logo]'));
        var billingButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-billing]'));
        var estimatorBillingButtons = Array.prototype.slice.call(document.querySelectorAll('[data-ll-estimator-billing]'));
        var seatRange = document.querySelector('[data-ll-seats-range]');
        var seatInput = document.querySelector('[data-ll-seats-input]');
        var enterpriseButton = document.querySelector('[data-ll-enterprise]');
        var enterpriseMessage = document.querySelector('[data-ll-enterprise-message]');
        var nav = document.querySelector('[data-ll-nav]');
        var lastFocused = null;
        var loadingTimer = null;
        var currentBilling = 'monthly';
        var currentEstimatorBilling = 'monthly';
        var planPrices = {
            plus: { monthly: '$7', yearly: '$70', monthlyNote: '$70/year with 2 months free.', yearlyNote: 'Billed at $70/year. 2 months free.' },
            pro: { monthly: '$15', yearly: '$150', monthlyNote: '$150/year with 2 months free.', yearlyNote: 'Billed at $150/year. 2 months free.' },
            team: { monthly: '$25', yearly: '$250', monthlyNote: 'Includes 2 seats. Extra seats from $8/seat.', yearlyNote: 'Billed from $250/year for 2 seats. 2 months free.' }
        };

        function logoVariantFor(element, theme) {
            var context = element.getAttribute('data-logo-context') || 'theme';
            if (context === 'light' || context === 'dark') {
                return context;
            }
            return theme;
        }

        function syncLogos(theme) {
            themeLogos.forEach(function (logo) {
                var variant = logoVariantFor(logo, theme);
                var nextSource = logo.getAttribute(variant === 'light' ? 'data-logo-light' : 'data-logo-dark');
                if (nextSource && logo.getAttribute('src') !== nextSource) {
                    logo.hidden = false;
                    logo.setAttribute('src', nextSource);
                }
            });
        }

        function setTheme(theme) {
            var nextTheme = theme === 'light' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', nextTheme);
            syncLogos(nextTheme);

            if (themeButton) {
                var light = nextTheme === 'light';
                themeButton.setAttribute('aria-pressed', String(light));
                themeButton.setAttribute('aria-label', light ? 'Switch to dark mode' : 'Switch to light mode');
            }

            try {
                window.localStorage.setItem('ll-homepage-theme', nextTheme);
            } catch (error) {
            }
        }

        function setPressed(buttons, selectedValue, attributeName) {
            buttons.forEach(function (button) {
                button.setAttribute('aria-pressed', String(button.getAttribute(attributeName) === selectedValue));
            });
        }

        function updatePlanPricing(billing) {
            currentBilling = billing === 'yearly' ? 'yearly' : 'monthly';
            setPressed(billingButtons, currentBilling, 'data-ll-billing');

            Object.keys(planPrices).forEach(function (plan) {
                var priceTarget = document.querySelector('[data-ll-price="' + plan + '"]');
                var noteTarget = document.querySelector('[data-ll-yearly-note="' + plan + '"]');
                if (priceTarget) {
                    priceTarget.textContent = planPrices[plan][currentBilling];
                }
                if (noteTarget) {
                    noteTarget.textContent = currentBilling === 'yearly' ? planPrices[plan].yearlyNote : planPrices[plan].monthlyNote;
                }
            });

            Array.prototype.slice.call(document.querySelectorAll('[data-ll-period]')).forEach(function (period) {
                period.textContent = currentBilling === 'yearly' ? '/year' : '/month';
            });
        }

        function money(value, decimals) {
            var fixed = Number(value).toFixed(decimals);
            return '$' + fixed.replace(/\.00$/, '');
        }

        function clampSeats(value) {
            var parsed = parseInt(value, 10);
            if (Number.isNaN(parsed)) {
                return 2;
            }
            return Math.min(50, Math.max(2, parsed));
        }

        function setEstimate(name, value) {
            var target = document.querySelector('[data-ll-estimate="' + name + '"]');
            if (target) {
                target.textContent = value;
            }
        }

        function updateEstimator() {
            var seats = clampSeats(seatInput ? seatInput.value : 5);
            var includedSeats = 2;
            var extraSeats = Math.max(0, seats - includedSeats);
            var monthly = 25 + (extraSeats * 8);
            var yearly = monthly * 10;
            var effective = yearly / 12;

            if (seatRange && seatRange.value !== String(seats)) {
                seatRange.value = seats;
            }
            if (seatInput && seatInput.value !== String(seats)) {
                seatInput.value = seats;
            }

            setEstimate('seats', String(seats));
            setEstimate('included', String(includedSeats));
            setEstimate('extra', String(extraSeats));
            setEstimate('monthly', money(monthly, 0));
            setEstimate('yearly', money(yearly, 0));
            setEstimate('effective', currentEstimatorBilling === 'yearly' ? money(effective, 2) : money(monthly, 0));
        }

        function updateEstimatorBilling(billing) {
            currentEstimatorBilling = billing === 'yearly' ? 'yearly' : 'monthly';
            setPressed(estimatorBillingButtons, currentEstimatorBilling, 'data-ll-estimator-billing');
            updateEstimator();
        }

        function clearDemoMessages() {
            Array.prototype.slice.call(document.querySelectorAll('[data-ll-demo-message]')).forEach(function (message) {
                message.classList.remove('ll-visible');
                message.innerHTML = '';
            });
        }

        function setMode(mode) {
            tabs.forEach(function (tab) {
                var selected = tab.getAttribute('data-ll-tab') === mode;
                tab.setAttribute('aria-selected', String(selected));
            });

            panels.forEach(function (panel) {
                var active = panel.getAttribute('data-ll-panel') === mode;
                panel.hidden = !active;
            });

            modalTitle.textContent = mode === 'login' ? 'Welcome back' : 'Create your LatchID';
            clearDemoMessages();
        }

        function openModal(mode) {
            lastFocused = document.activeElement;
            setMode(mode === 'login' ? 'login' : 'signup');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ll-modal-open');

            window.setTimeout(function () {
                var selectedTab = modal.querySelector('[aria-selected="true"]');
                if (selectedTab) {
                    selectedTab.focus();
                }
            }, 20);
        }

        function closeModal() {
            window.clearTimeout(loadingTimer);
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ll-modal-open');
            clearDemoMessages();

            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        }

        function setProviderBusy(button, busy) {
            button.disabled = busy;
            button.setAttribute('aria-busy', String(busy));
        }

        async function handleProviderClick(button) {
            var provider = button.getAttribute('data-ll-provider');
            var panel = button.closest('[data-ll-panel]');
            var message = panel ? panel.querySelector('[data-ll-demo-message]') : null;

            if (!message) {
                return;
            }

            window.clearTimeout(loadingTimer);
            message.classList.add('ll-visible');

            if (provider !== 'Google') {
                message.innerHTML = '<strong>Google only:</strong> LatchID MVP authentication is currently limited to Google.';
                return;
            }

            if (!latchIdConfig.supabaseUrl || !latchIdConfig.supabaseAnonKey) {
                message.innerHTML = '<strong>Configuration needed:</strong> SUPABASE_URL and SUPABASE_ANON_KEY must be set before Google sign in can start.';
                return;
            }

            if (!window.supabase || !window.supabase.createClient) {
                message.innerHTML = '<strong>Sign in unavailable:</strong> the Supabase browser client could not be loaded.';
                return;
            }

            setProviderBusy(button, true);
            message.innerHTML = '<span class="ll-loading">Connecting to Google</span>';

            try {
                var client = window.supabase.createClient(latchIdConfig.supabaseUrl, latchIdConfig.supabaseAnonKey);
                var result = await client.auth.signInWithOAuth({
                    provider: 'google',
                    options: {
                        redirectTo: latchIdConfig.redirectTo
                    }
                });

                if (result.error) {
                    throw result.error;
                }
            } catch (error) {
                setProviderBusy(button, false);
                message.innerHTML = '<strong>Google sign in failed:</strong> ' + (error && error.message ? error.message : String(error));
                console.error('LatchID Google sign in failed:', error);
            }
        }

        function trapFocus(event) {
            if (modal.getAttribute('aria-hidden') === 'true' || event.key !== 'Tab') {
                return;
            }

            var focusable = Array.prototype.slice.call(modal.querySelectorAll(
                'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
            )).filter(function (element) {
                return element.offsetParent !== null;
            });

            if (!focusable.length) {
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button.getAttribute('data-ll-open-modal'));
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setMode(tab.getAttribute('data-ll-tab'));
            });
        });

        Array.prototype.slice.call(document.querySelectorAll('[data-ll-provider]')).forEach(function (button) {
            button.addEventListener('click', function () {
                handleProviderClick(button);
            });
        });

        if (menuButton && nav) {
            menuButton.addEventListener('click', function () {
                var open = !nav.classList.contains('ll-open');
                nav.classList.toggle('ll-open', open);
                menuButton.setAttribute('aria-expanded', String(open));
            });
        }

        if (themeButton) {
            themeButton.addEventListener('click', function () {
                setTheme(document.body.getAttribute('data-theme') === 'light' ? 'dark' : 'light');
            });
        }

        billingButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var billing = button.getAttribute('data-ll-billing');
                updatePlanPricing(billing);
                updateEstimatorBilling(billing);
            });
        });

        estimatorBillingButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var billing = button.getAttribute('data-ll-estimator-billing');
                updateEstimatorBilling(billing);
                updatePlanPricing(billing);
            });
        });

        if (seatRange && seatInput) {
            seatRange.addEventListener('input', function () {
                seatInput.value = seatRange.value;
                updateEstimator();
            });
            seatInput.addEventListener('input', updateEstimator);
            seatInput.addEventListener('blur', updateEstimator);
        }

        if (enterpriseButton && enterpriseMessage) {
            enterpriseButton.addEventListener('click', function () {
                enterpriseMessage.classList.add('ll-visible');
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }

            trapFocus(event);
        });

        try {
            setTheme(window.localStorage.getItem('ll-homepage-theme') || 'dark');
        } catch (error) {
            setTheme('dark');
        }

        updatePlanPricing('monthly');
        updateEstimatorBilling('monthly');
    }());
</script>
</body>
</html>
