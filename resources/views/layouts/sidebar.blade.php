@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\User;
use App\Services\LivelatchNotificationService;

$usrhandl = Auth::user()->littlelink_name ?? null;
$profileUrl = $usrhandl ? url('/@'.$usrhandl) : url('/studio/page');
$userRole = optional(auth()->user())->role;

$latchIdUserId = Auth::user()->supabase_user_id ?? null;
$livelatchNotifications = collect();
$livelatchUnreadNotificationCount = 0;

try {
    $livelatchNotifications = LivelatchNotificationService::forUser($latchIdUserId, 8);
    $livelatchUnreadNotificationCount = LivelatchNotificationService::unreadCount($latchIdUserId);
} catch (\Throwable $e) {
    $livelatchNotifications = collect();
    $livelatchUnreadNotificationCount = 0;
}

$GLOBALS['activenotify'] = ($livelatchNotifications->count() > 0 || $livelatchUnreadNotificationCount > 0);

function llActive($segment, $value) {
    return Request::segment($segment) == $value ? 'active' : '';
}

function llHtmxAttrs($url, $indicator = '#ll-page-skeleton') {
    return 'href="' . e($url) . '" hx-get="' . e($url) . '" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="' . e($indicator) . '"';
}
@endphp
<!doctype html>
@include('layouts.lang')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }}</title>

    <script src="{{ asset('assets/js/detect-dark-mode.js') }}"></script>
    @include('layouts.liquid-glass')
    <base href="{{ url()->current() }}" />

    @include('layouts.analytics')
    @include('layouts.posthog')
    @stack('sidebar-stylesheets')
    @livewireStyles
    {{-- Legacy LinkStack notifications disabled. Livelatch notifications are rendered directly in this layout. --}}

    @php
        if (auth()->check()) {
            auth()->user()->touch();
        }
    @endphp

    @if(file_exists(base_path("assets/linkstack/images/").findFile('favicon')))
        <link rel="icon" type="image/png" href="{{ asset('assets/linkstack/images/'.findFile('favicon')) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/linkstack/images/logo.svg') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/aos/dist/aos.css') }}" />

    @include('layouts.fonts')

    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />

    @if(file_exists(base_path("assets/dashboard-themes/dashboard.css")))
        <link rel="stylesheet" href="{{ asset('assets/dashboard-themes/dashboard.css') }}" />
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/linkstack/css/hover-min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/linkstack/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/external-dependencies/bootstrap-icons.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

        /* Dark is the default theme: its values live on :root so dark wins even
           before JS runs. Light is an explicit [data-ll-theme="light"] override.
           Theme-agnostic tokens (font, radii, widths) stay on :root. */
        :root {
            color-scheme: dark;
            --ll-font: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --ll-bg: #000421;
            --ll-bg-soft: #000421;
            --ll-surface: rgba(16, 16, 31, 0.78);
            --ll-surface-solid: #000421;
            --ll-text: #f8fbff;
            --ll-muted: #aeb8cf;
            --ll-border: rgba(255, 255, 255, 0.12);
            --ll-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
            --ll-shadow-soft: 0 12px 34px rgba(0, 0, 0, 0.22);
            --ll-primary: #16a6ff;
            --ll-primary-2: #25f4ee;
            --ll-primary-3: #b3f9ff;
            --ll-danger: #ef4444;
            --ll-success: #22c55e;
            /* Tighter geometry for a more production / broadcast-tool feel. */
            --ll-radius: 20px;
            --ll-radius-sm: 10px;
            --ll-button-radius: 12px;
            --ll-dev-heading-weight: 600;
            --ll-dev-button-weight: 400;
            --ll-sidebar-width: 292px;
            --ll-topbar-height: 74px;
        }

        :root[data-ll-theme="light"],
        [data-ll-theme="light"] {
            color-scheme: light;
            --ll-bg: #ffffff;
            --ll-bg-soft: #ffffff;
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
        }

        * { box-sizing: border-box; }

        html,
        body {
            min-height: 100%;
            font-family: var(--ll-font);
            background:
                radial-gradient(circle at 18% -12%, color-mix(in srgb, var(--ll-primary) 16%, transparent), transparent 28%),
                radial-gradient(circle at 90% 10%, rgba(18, 214, 223, 0.10), transparent 26%),
                var(--ll-bg);
            color: var(--ll-text);
        }

        body {
            margin: 0;
            overflow-x: hidden;
        }

        body, p, div, span, a, button, input, textarea, select, label {
            font-family: var(--ll-font);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--ll-font);
            font-weight: var(--ll-dev-heading-weight, 700);
            color: var(--ll-text);
        }

        button,
        .btn,
        .ll-pill-button,
        .ll-icon-button,
        .ll-hero-button,
        .ll-hero-quick-link {
            font-weight: var(--ll-dev-button-weight, 700);
        }

        a { color: inherit; }

        #loading { background: var(--ll-bg); }

        .ll-shell {
            min-height: 100vh;
            display: flex;
        }

        .ll-sidebar {
            width: var(--ll-sidebar-width);
            position: fixed;
            inset: 16px auto 16px 16px;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--ll-border);
            border-radius: 28px;
            background: var(--ll-surface);
            backdrop-filter: blur(22px);
            box-shadow: var(--ll-shadow);
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .ll-sidebar-brand {
            min-height: 86px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--ll-border);
        }

        .ll-brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background:
                radial-gradient(circle at 30% 20%, rgba(255,255,255,0.8), transparent 18%),
                linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 14px 28px color-mix(in srgb, var(--ll-primary) 32%, transparent);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .ll-brand-mark img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .ll-brand-copy { min-width: 0; }

        .ll-brand-title {
            font-family: var(--ll-font, 'Poppins', system-ui, sans-serif);
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--ll-text);
            margin: 0;
            line-height: 1;
        }

        .ll-brand-subtitle {
            font-size: 0.78rem;
            color: var(--ll-muted);
            margin: 0.25rem 0 0;
            font-weight: 500;
        }

        .ll-brand-badge {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            border-radius: 999px;
            padding: 3px 9px;
            line-height: 1;
        }

        .ll-sidebar-close,
        .ll-mobile-menu {
            width: 42px;
            height: 42px;
            border: 1px solid var(--ll-border);
            border-radius: 14px;
            color: var(--ll-text);
            background: var(--ll-surface-solid);
            display: grid;
            place-items: center;
        }

        .ll-sidebar-close {
            margin-left: auto;
            display: none;
        }

        .ll-sidebar-body {
            padding: 18px;
            overflow-y: auto;
        }

        .ll-nav-section-button {
            width: 100%;
            margin: 20px 0 8px;
            padding: 0 12px;
            border: 0;
            background: transparent;
            color: var(--ll-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.11em;
        }

        .ll-nav-section-button i {
            font-size: 0.72rem;
            transition: transform 0.18s ease;
        }

        .ll-nav-section-button[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        .ll-nav-section {
            margin: 20px 0 8px;
            padding: 0 12px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            color: var(--ll-muted);
        }

        .ll-nav-rail {
            display: grid;
            gap: 5px;
        }

        .ll-nav-group {
            border: 1px solid transparent;
            border-radius: 12px;
            transition: border-color 0.18s ease, background 0.18s ease;
        }

        .ll-nav-group.is-open,
        .ll-nav-group.is-active {
            border-color: color-mix(in srgb, var(--ll-primary) 22%, var(--ll-border));
            background: color-mix(in srgb, var(--ll-primary) 6%, transparent);
        }

        .ll-nav-group-button {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 11px;
            padding: 7px 9px;
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) 16px;
            align-items: center;
            gap: 10px;
            color: var(--ll-text);
            background: transparent;
            text-align: left;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .ll-nav-group-button:hover,
        .ll-nav-group-button:focus {
            background: color-mix(in srgb, var(--ll-primary) 9%, transparent);
            color: var(--ll-text);
        }

        .ll-nav-group-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 12%, transparent);
            border: 1px solid color-mix(in srgb, var(--ll-primary) 18%, transparent);
            font-size: 1.05rem;
        }

        .ll-nav-group.is-active .ll-nav-group-icon,
        .ll-nav-group.is-open .ll-nav-group-icon {
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            border-color: transparent;
            box-shadow: 0 6px 18px color-mix(in srgb, var(--ll-primary) 38%, transparent);
        }

        .ll-nav-group-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .ll-nav-group-chevron {
            color: var(--ll-muted);
            font-size: 0.72rem;
            transition: transform 0.18s ease;
        }

        .ll-nav-group.is-open .ll-nav-group-chevron {
            transform: rotate(90deg);
        }

        .ll-nav-panel {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0 6px 0 54px;
            transform: translateY(-4px);
            transition: max-height 0.22s ease, opacity 0.18s ease, padding 0.18s ease, transform 0.18s ease;
        }

        .ll-nav-group.is-open .ll-nav-panel {
            max-height: 360px;
            opacity: 1;
            padding-bottom: 6px;
            transform: translateY(0);
        }

        .ll-nav-panel .ll-nav-link {
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity 0.16s ease, transform 0.16s ease, background 0.18s ease, color 0.18s ease;
        }

        .ll-nav-group.is-open .ll-nav-panel .ll-nav-link {
            opacity: 1;
            transform: translateX(0);
        }

        .ll-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ll-nav-link {
            position: relative;
            min-height: 42px;
            margin-bottom: 4px;
            padding: 0 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: var(--ll-muted);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .ll-nav-link i,
        .ll-nav-link svg {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
        }

        .ll-nav-link:hover {
            color: var(--ll-text);
            background: color-mix(in srgb, var(--ll-primary) 9%, transparent);
            transform: translateX(2px);
        }

        .ll-nav-link.active,
        .ll-nav-link.bg-soft-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), color-mix(in srgb, var(--ll-primary-2) 86%, var(--ll-primary)));
            box-shadow: inset 0 0 0 1px color-mix(in srgb, #ffffff 18%, transparent),
                        0 8px 22px color-mix(in srgb, var(--ll-primary) 34%, transparent);
        }

        .ll-nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--ll-primary) 14%, transparent);
            color: var(--ll-primary);
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .ll-nav-link.active .ll-nav-badge {
            background: rgba(255,255,255,0.22);
            color: #fff;
        }

        .ll-sidebar-footer {
            margin-top: auto;
            padding: 18px;
        }

        .ll-user-chip {
            border: 1px solid var(--ll-border);
            border-radius: 20px;
            padding: 12px;
            background: color-mix(in srgb, var(--ll-primary) 6%, transparent);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .ll-avatar {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.65);
            box-shadow: 0 10px 18px rgba(18, 15, 45, 0.12);
        }

        .ll-user-name {
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
            color: var(--ll-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ll-user-role {
            font-size: 0.76rem;
            margin: 0;
            color: var(--ll-muted);
        }

        .ll-main {
            width: 100%;
            min-height: 100vh;
            margin-left: calc(var(--ll-sidebar-width) + 24px);
            padding: 16px 18px 0 0;
            transition: margin 0.25s ease;
        }

        .ll-topbar {
            height: var(--ll-topbar-height);
            position: sticky;
            top: 16px;
            z-index: 1020;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 18px;
            border: 1px solid var(--ll-border);
            border-radius: 24px;
            background: var(--ll-surface);
            backdrop-filter: blur(22px);
            box-shadow: var(--ll-shadow-soft);
        }

        .ll-topbar-spacer { flex: 1; }

        .ll-pill-button,
        .ll-icon-button {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }

        .ll-pill-button {
            min-height: 42px;
            padding: 0 16px;
            border-radius: var(--ll-button-radius, 999px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .ll-pill-button.primary {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 14px 28px color-mix(in srgb, var(--ll-primary) 24%, transparent);
        }

        .ll-journey-link {
            min-height: 42px;
            padding: 0 15px;
            border-radius: var(--ll-button-radius, 15px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            font-weight: 700;
            font-size: 0.9rem;
            white-space: nowrap;
            transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }
        .ll-journey-link:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--ll-primary) 32%, transparent); }
        .ll-journey-link i { font-size: 1rem; color: var(--ll-primary); }

        @media (max-width: 991.98px) {
            .ll-journey-link .ll-journey-text { display: none; }
            .ll-journey-link { width: 42px; padding: 0; justify-content: center; }
        }

        .ll-icon-button {
            width: 42px;
            height: 42px;
            border-radius: var(--ll-button-radius, 15px);
            display: grid;
            place-items: center;
            position: relative;
        }

        .ll-pill-button:hover,
        .ll-icon-button:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--ll-primary) 32%, transparent);
        }

        .ll-dot {
            position: absolute;
            right: 9px;
            top: 9px;
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: var(--ll-danger);
            box-shadow: 0 0 0 3px var(--ll-surface-solid);
        }

        .ll-theme-toggle {
            min-width: 84px;
            height: 42px;
            border: 1px solid var(--ll-border);
            border-radius: 999px;
            padding: 4px;
            background: var(--ll-surface-solid);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ll-theme-toggle button {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--ll-muted);
            display: grid;
            place-items: center;
        }

        [data-ll-theme="light"] .ll-theme-toggle [data-theme-choice="light"],
        [data-ll-theme="dark"] .ll-theme-toggle [data-theme-choice="dark"] {
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-profile-trigger {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            border-radius: 18px;
            padding: 6px 10px 6px 6px;
            min-height: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .ll-profile-trigger img {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            object-fit: cover;
        }

        .ll-profile-text {
            display: block;
            min-width: 0;
        }

        .ll-profile-text strong,
        .ll-profile-text span {
            display: block;
            max-width: 130px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ll-profile-text strong {
            color: var(--ll-text);
            font-size: 0.84rem;
            line-height: 1.1;
        }

        .ll-profile-text span {
            color: var(--ll-muted);
            font-size: 0.72rem;
            margin-top: 2px;
        }

        .ll-hero {
            position: relative;
            overflow: hidden;
            margin-top: 16px;
            border-radius: 26px;
            padding: clamp(18px, 3vw, 28px);
            color: var(--ll-text);
            background:
                radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--ll-primary) 18%, transparent), transparent 30%),
                linear-gradient(135deg, var(--ll-surface-solid), var(--ll-bg-soft));
            border: 1px solid var(--ll-border);
            box-shadow: var(--ll-shadow-soft);
            isolation: isolate;
        }

        .ll-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(color-mix(in srgb, var(--ll-text) 5%, transparent) 1px, transparent 1px),
                linear-gradient(90deg, color-mix(in srgb, var(--ll-text) 5%, transparent) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at 55% 45%, black 0%, transparent 78%);
            z-index: -2;
        }

        .ll-hero::after {
            display: none;
        }

        .ll-hero-content {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(340px, 1.05fr);
            gap: clamp(16px, 3vw, 28px);
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .ll-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid var(--ll-border);
            border-radius: 999px;
            background: color-mix(in srgb, var(--ll-primary) 9%, transparent);
            color: var(--ll-primary);
            font-weight: 700;
            font-size: 0.82rem;
            margin-bottom: 12px;
        }

        .ll-hero h1 {
            color: var(--ll-text);
            font-size: clamp(1.7rem, 4vw, 3.1rem);
            font-weight: 800;
            line-height: 1;
            margin: 0;
        }

        .ll-hero p {
            color: var(--ll-muted);
            margin: 10px 0 0;
            font-size: 0.96rem;
            font-weight: 500;
            max-width: 560px;
        }

        .ll-hero-actions,
        .ll-hero-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .ll-hero-button,
        .ll-hero-quick-link {
            min-height: 92px;
            padding: 14px;
            border-radius: var(--ll-button-radius, 18px);
            display: inline-flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            text-decoration: none;
            color: var(--ll-text);
            background: var(--ll-surface-solid);
            border: 1px solid var(--ll-border);
            box-shadow: 0 10px 24px color-mix(in srgb, var(--ll-text) 7%, transparent);
            transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }

        .ll-hero-button:hover,
        .ll-hero-button:focus,
        .ll-hero-quick-link:hover,
        .ll-hero-quick-link:focus {
            color: var(--ll-text);
            border-color: color-mix(in srgb, var(--ll-primary) 38%, var(--ll-border));
            background: color-mix(in srgb, var(--ll-primary) 7%, var(--ll-surface-solid));
            transform: translateY(-2px);
        }

        .ll-hero-button i {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            font-size: 1.05rem;
        }

        .ll-hero-button span {
            display: grid;
            gap: 3px;
        }

        .ll-hero-button small {
            color: var(--ll-muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .ll-hero-button.secondary {
            color: var(--ll-text);
            background: var(--ll-surface-solid);
        }

        .ll-hero-quick-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            font-size: 1.05rem;
        }

        .ll-hero-quick-link span:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .ll-hero-quick-link small {
            color: var(--ll-muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .ll-hero-content,
            .ll-hero-actions,
            .ll-hero-quick-grid {
                grid-template-columns: 1fr;
            }
        }

        .ll-content {
            padding: 22px 0 30px;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .ll-content-stage {
            position: relative;
            min-height: 420px;
        }

        .htmx-indicator {
            display: none;
        }

        .htmx-request.htmx-indicator,
        .htmx-request .htmx-indicator {
            display: block;
        }

        .ll-content-skeleton {
            position: absolute;
            inset: 22px 0 auto;
            z-index: 20;
            pointer-events: none;
            padding: 0 clamp(14px, 1.5vw, 22px);
        }

        .ll-content-skeleton-shell {
            width: 100%;
            padding: clamp(18px, 2vw, 26px);
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius, var(--ll-radius));
            background:
                linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--ll-background, var(--ll-bg, #ffffff)) 92%, transparent),
                    color-mix(in srgb, var(--ll-background, var(--ll-bg, #ffffff)) 82%, var(--ll-text, #111827))
                );
            box-shadow: var(--ll-shadow-soft);
            backdrop-filter: blur(18px);
        }

        .ll-skeleton {
            background: color-mix(in srgb, var(--ll-text, #ffffff) 10%, transparent);
            border-radius: var(--ll-button-radius, 12px);
            position: relative;
            overflow: hidden;
        }

        .ll-skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(
                90deg,
                transparent,
                color-mix(in srgb, var(--ll-primary, #8b5cf6) 18%, transparent),
                transparent
            );
            animation: ll-skeleton-shimmer 1.4s infinite;
        }

        @keyframes ll-skeleton-shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .ll-skeleton-page,
        .ll-skeleton-card-grid,
        .ll-skeleton-table-wrap,
        .ll-skeleton-profile {
            display: grid;
            gap: 16px;
        }

        .ll-skeleton-kicker {
            width: 110px;
            height: 16px;
        }

        .ll-skeleton-heading {
            width: min(360px, 70%);
            height: 34px;
        }

        .ll-skeleton-line {
            width: 68%;
            height: 14px;
        }

        .ll-skeleton-line-wide {
            width: 92%;
        }

        .ll-skeleton-line-mid {
            width: 48%;
        }

        .ll-skeleton-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ll-skeleton-pill {
            width: 144px;
            height: 42px;
            border-radius: 999px;
        }

        .ll-skeleton-pill-short {
            width: 96px;
        }

        .ll-skeleton-layout {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .ll-skeleton-panel,
        .ll-skeleton-card,
        .ll-skeleton-table,
        .ll-skeleton-profile-main,
        .ll-skeleton-profile-link {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius, var(--ll-radius));
            background: color-mix(in srgb, var(--ll-background, var(--ll-bg, #ffffff)) 88%, var(--ll-text, #111827));
        }

        .ll-skeleton-panel,
        .ll-skeleton-card,
        .ll-skeleton-profile-main {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .ll-skeleton-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .ll-skeleton-card-media {
            width: 100%;
            aspect-ratio: 16 / 10;
        }

        .ll-skeleton-table {
            overflow: hidden;
        }

        .ll-skeleton-table-row {
            display: grid;
            grid-template-columns: 1fr 1.6fr 1fr 110px;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-top: 1px solid var(--ll-border);
        }

        .ll-skeleton-table-row:first-child {
            border-top: 0;
        }

        .ll-skeleton-table-head {
            background: color-mix(in srgb, var(--ll-primary, #8b5cf6) 8%, transparent);
        }

        .ll-skeleton-profile {
            max-width: 430px;
            margin: 0 auto;
        }

        .ll-skeleton-profile-main {
            justify-items: center;
            text-align: center;
        }

        .ll-skeleton-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
        }

        .ll-skeleton-profile-links {
            display: grid;
            gap: 12px;
        }

        .ll-skeleton-profile-link {
            display: grid;
            grid-template-columns: 30px 1fr;
            gap: 12px;
            align-items: center;
            padding: 14px 16px;
        }

        .ll-skeleton-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        @media (max-width: 767.98px) {
            .ll-skeleton-layout,
            .ll-skeleton-grid {
                grid-template-columns: 1fr;
            }

            .ll-skeleton-table-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        .ll-content.htmx-swapping {
            opacity: 0;
            transition: opacity 0.18s ease;
        }

        .ll-content[aria-busy="true"] {
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none;
            transition: opacity 0.12s ease;
        }

        .ll-content.htmx-settling {
            opacity: 1;
            transform: translateY(0);
            animation: ll-content-enter 0.24s ease both;
        }

        @keyframes ll-content-enter {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ll-content,
            .ll-content[aria-busy="true"],
            .ll-content.htmx-settling {
                animation: none;
                transform: none;
                transition: none;
            }
        }

        .ll-content > .container-fluid,
        .ll-content .card,
        .ll-content .iq-card,
        .ll-content .card-body {
            border-radius: var(--ll-radius);
        }

        .ll-content .card,
        .ll-content .iq-card {
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow-soft);
        }

        [data-ll-theme="dark"] .ll-content,
        [data-ll-theme="dark"] .ll-content .card,
        [data-ll-theme="dark"] .ll-content .iq-card,
        [data-ll-theme="dark"] .ll-content .table,
        [data-ll-theme="dark"] .dropdown-menu,
        [data-ll-theme="dark"] .modal-content,
        [data-ll-theme="dark"] .offcanvas {
            color: var(--ll-text);
            background-color: var(--ll-surface-solid);
        }

        [data-ll-theme="dark"] .table,
        [data-ll-theme="dark"] .dropdown-item,
        [data-ll-theme="dark"] .form-control,
        [data-ll-theme="dark"] .form-select {
            color: var(--ll-text);
        }

        [data-ll-theme="dark"] .dropdown-item:hover {
            background: rgba(255,255,255,0.08);
        }

        .ll-footer {
            margin: 0 0 18px;
            border: 1px solid var(--ll-border);
            border-radius: 24px;
            background: var(--ll-surface);
            backdrop-filter: blur(18px);
            color: var(--ll-muted);
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 0.86rem;
        }

        .ll-footer a {
            color: var(--ll-text);
            font-weight: 700;
            text-decoration: none;
        }

        .ll-overlay {
            position: fixed;
            inset: 0;
            background: rgba(8, 6, 24, 0.45);
            backdrop-filter: blur(4px);
            z-index: 1030;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .ll-sidebar-open .ll-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        .dropdown-menu,
        .modal-content,
        .offcanvas {
            border: 1px solid var(--ll-border);
            border-radius: 20px;
            box-shadow: var(--ll-shadow);
        }

        .dropdown-menu { padding: 10px; }

        .ll-notification-dropdown {
            width: min(420px, calc(100vw - 24px));
            overflow: hidden;
            padding: 0;
            border-radius: 24px;
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow);
        }

        .ll-notification-header {
            padding: 16px 18px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,0.24), transparent 36%),
                linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }

        .ll-notification-header h6,
        .ll-notification-header p { color: #fff; }

        .ll-notification-header h6 {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .ll-notification-header p {
            margin: 4px 0 0;
            font-size: 0.78rem;
            opacity: 0.78;
            font-weight: 500;
        }

        .ll-notification-list {
            max-height: 440px;
            overflow-y: auto;
        }

        .ll-notification-item {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            color: var(--ll-text);
            text-decoration: none;
            border-bottom: 1px solid var(--ll-border);
            transition: background 0.18s ease, transform 0.18s ease;
        }

        .ll-notification-item:hover {
            color: var(--ll-text);
            background: color-mix(in srgb, var(--ll-primary) 8%, transparent);
            transform: translateX(2px);
        }

        .ll-notification-item.is-unread {
            background: color-mix(in srgb, var(--ll-primary) 10%, transparent);
        }

        .ll-notification-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 15px;
            font-size: 1rem;
        }

        .ll-notification-icon.info {
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 14%, transparent);
        }

        .ll-notification-icon.success {
            color: #16a34a;
            background: rgba(34, 197, 94, 0.14);
        }

        .ll-notification-icon.warning {
            color: #d97706;
            background: rgba(245, 158, 11, 0.16);
        }

        .ll-notification-icon.danger {
            color: #dc2626;
            background: rgba(239, 68, 68, 0.15);
        }

        .ll-notification-content {
            min-width: 0;
            flex: 1;
        }

        .ll-notification-title {
            color: var(--ll-text);
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.24;
            margin: 0;
        }

        .ll-notification-message {
            color: var(--ll-muted);
            font-size: 0.78rem;
            line-height: 1.35;
            margin: 3px 0 0;
        }

        .ll-notification-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 8px;
            color: var(--ll-muted);
            font-size: 0.7rem;
            font-weight: 600;
        }

        .ll-notification-source {
            padding: 2px 8px;
            border-radius: 999px;
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 10%, transparent);
        }

        .ll-notification-empty {
            padding: 28px 18px;
            text-align: center;
            color: var(--ll-muted);
            font-size: 0.86rem;
        }

        .ll-notification-empty i {
            display: block;
            margin-bottom: 8px;
            font-size: 1.8rem;
            color: var(--ll-primary);
        }

        .ll-notification-footer {
            display: block;
            padding: 13px 16px;
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 8%, transparent);
            text-align: center;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .ll-notification-footer:hover {
            color: var(--ll-primary);
            background: color-mix(in srgb, var(--ll-primary) 13%, transparent);
        }

        .dropdown-item {
            border-radius: 12px;
            font-weight: 600;
            color: var(--ll-text);
        }

        .btn-primary,
        .bg-primary {
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)) !important;
            border-color: transparent !important;
        }

        .text-primary { color: var(--ll-primary) !important; }


        /*
        |--------------------------------------------------------------------------
        | Livelatch production polish
        |--------------------------------------------------------------------------
        | These rules tame older LinkStack/Bootstrap content when it is injected
        | into the persistent dashboard shell via HTMX.
        */

        #ll-content {
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            isolation: isolate;
        }

        #ll-content > * {
            max-width: 100%;
        }

        #ll-content .container,
        #ll-content .container-fluid {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: clamp(14px, 1.5vw, 22px) !important;
            padding-right: clamp(14px, 1.5vw, 22px) !important;
        }

        #ll-content .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        #ll-content [class*="col-"] {
            min-width: 0;
        }

        #ll-content .content-inner.mt-n5 {
            margin-top: 0 !important;
        }

        #ll-content .card,
        #ll-content .iq-card,
        #ll-content .card-body,
        #ll-content .card-header,
        #ll-content .card-footer,
        #ll-content .list-group-item,
        #ll-content .accordion-item,
        #ll-content .accordion-button,
        #ll-content .accordion-body,
        #ll-content .tab-content,
        #ll-content .tab-pane {
            color: var(--ll-text) !important;
            background: var(--ll-surface-solid) !important;
            border-color: var(--ll-border) !important;
        }

        #ll-content .card,
        #ll-content .iq-card {
            width: 100% !important;
            max-width: 100% !important;
            min-height: auto !important;
            overflow: hidden;
            border-radius: var(--ll-radius) !important;
            box-shadow: var(--ll-shadow-soft) !important;
        }

        #ll-content .card-body,
        #ll-content .iq-card-body {
            padding: clamp(18px, 2vw, 28px) !important;
        }

        #ll-content h1,
        #ll-content h2,
        #ll-content h3,
        #ll-content h4,
        #ll-content h5,
        #ll-content h6,
        #ll-content p,
        #ll-content span,
        #ll-content label,
        #ll-content small,
        #ll-content strong,
        #ll-content div {
            color: inherit;
        }

        #ll-content h1,
        #ll-content h2,
        #ll-content h3,
        #ll-content h4,
        #ll-content h5,
        #ll-content h6 {
            color: var(--ll-text) !important;
            letter-spacing: -0.035em;
        }

        #ll-content p,
        #ll-content small,
        #ll-content .text-muted,
        #ll-content .form-text,
        #ll-content .help-block,
        #ll-content .caption-sub-title {
            color: var(--ll-muted) !important;
        }

        #ll-content a:not(.btn) {
            color: var(--ll-primary);
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }

        #ll-content img,
        #ll-content svg,
        #ll-content canvas,
        #ll-content iframe {
            max-width: 100%;
        }

        #ll-content table,
        #ll-content .table {
            width: 100%;
            color: var(--ll-text) !important;
            border-color: var(--ll-border) !important;
            background: transparent !important;
        }

        #ll-content .table > :not(caption) > * > * {
            color: var(--ll-text) !important;
            background: transparent !important;
            border-color: var(--ll-border) !important;
        }

        #ll-content input,
        #ll-content textarea,
        #ll-content select,
        #ll-content .form-control,
        #ll-content .form-select,
        #ll-content .input-group-text {
            color: var(--ll-text) !important;
            background: var(--ll-surface-solid) !important;
            border: 1px solid var(--ll-border) !important;
            border-radius: 14px !important;
            box-shadow: none !important;
        }

        #ll-content input:focus,
        #ll-content textarea:focus,
        #ll-content select:focus,
        #ll-content .form-control:focus,
        #ll-content .form-select:focus {
            border-color: color-mix(in srgb, var(--ll-primary) 55%, transparent) !important;
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--ll-primary) 14%, transparent) !important;
        }

        #ll-content input::placeholder,
        #ll-content textarea::placeholder {
            color: color-mix(in srgb, var(--ll-muted), transparent 20%) !important;
        }

        #ll-content .btn {
            border-radius: var(--ll-button-radius, 14px);
            font-weight: var(--ll-dev-button-weight, 700);
        }

        #ll-content .btn-primary,
        #ll-content .btn-soft-primary,
        #ll-content .badge.bg-primary {
            color: #fff !important;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)) !important;
            border-color: transparent !important;
        }

        #ll-content .badge,
        #ll-content .rounded-pill {
            font-weight: 800;
        }

        #ll-content .bg-white,
        #ll-content .bg-light,
        #ll-content .alert,
        #ll-content .dropdown-menu,
        #ll-content .pagination .page-link,
        #ll-content .page-link,
        #ll-content .list-group-item,
        #ll-content .nav-tabs .nav-link,
        #ll-content .nav-pills .nav-link:not(.active),
        #ll-content .btn-light,
        #ll-content .btn-soft-light {
            background: var(--ll-surface-solid) !important;
            color: var(--ll-text) !important;
            border-color: var(--ll-border) !important;
        }

        #ll-content .text-dark,
        #ll-content .text-black,
        #ll-content .badge.bg-light,
        #ll-content .badge.text-dark {
            color: var(--ll-text) !important;
        }

        #ll-content .border,
        #ll-content .border-top,
        #ll-content .border-end,
        #ll-content .border-bottom,
        #ll-content .border-start {
            border-color: var(--ll-border) !important;
        }

        #ll-content .sortable,
        #ll-content .list-group,
        #ll-content [id*="sortable"],
        #ll-content [class*="sortable"] {
            min-height: auto !important;
        }

        #ll-content .link-card,
        #ll-content .link-item,
        #ll-content .page-icon,
        #ll-content .page-icon-card {
            color: var(--ll-text) !important;
            background: var(--ll-surface-solid) !important;
            border-color: var(--ll-border) !important;
            box-shadow: none !important;
        }

        .dropdown-menu,
        .modal-content,
        .modal-header,
        .modal-footer,
        .offcanvas,
        .popover {
            color: var(--ll-text) !important;
            background: var(--ll-surface-solid) !important;
            border-color: var(--ll-border) !important;
        }

        .dropdown-header,
        .dropdown-item,
        .modal-title,
        .offcanvas-title {
            color: var(--ll-text) !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: var(--ll-text) !important;
            background: color-mix(in srgb, var(--ll-primary) 10%, transparent) !important;
        }

        [data-ll-theme="dark"] {
            color-scheme: dark;
        }

        [data-ll-theme="light"] {
            color-scheme: light;
        }

        [data-ll-theme="dark"] .ll-sidebar,
        [data-ll-theme="dark"] .ll-topbar,
        [data-ll-theme="dark"] .ll-footer {
            background: rgba(16, 16, 31, 0.82);
        }

        [data-ll-theme="dark"] #ll-content .card,
        [data-ll-theme="dark"] #ll-content .iq-card,
        [data-ll-theme="dark"] #ll-content .card-body,
        [data-ll-theme="dark"] #ll-content .card-header,
        [data-ll-theme="dark"] #ll-content .card-footer,
        [data-ll-theme="dark"] #ll-content .list-group-item,
        [data-ll-theme="dark"] #ll-content .accordion-item,
        [data-ll-theme="dark"] #ll-content .accordion-button,
        [data-ll-theme="dark"] #ll-content .accordion-body {
            color: var(--ll-text) !important;
            background: #10101f !important;
            border-color: rgba(255,255,255,0.10) !important;
        }

        [data-ll-theme="dark"] #ll-content input,
        [data-ll-theme="dark"] #ll-content textarea,
        [data-ll-theme="dark"] #ll-content select,
        [data-ll-theme="dark"] #ll-content .form-control,
        [data-ll-theme="dark"] #ll-content .form-select,
        [data-ll-theme="dark"] #ll-content .input-group-text {
            color: var(--ll-text) !important;
            background: #0c0c19 !important;
            border-color: rgba(255,255,255,0.12) !important;
        }

        [data-ll-theme="dark"] #ll-content .bg-white,
        [data-ll-theme="dark"] #ll-content .bg-light,
        [data-ll-theme="dark"] #ll-content .alert,
        [data-ll-theme="dark"] #ll-content .pagination .page-link,
        [data-ll-theme="dark"] #ll-content .page-link,
        [data-ll-theme="dark"] #ll-content .list-group-item,
        [data-ll-theme="dark"] #ll-content .nav-tabs .nav-link,
        [data-ll-theme="dark"] #ll-content .nav-pills .nav-link:not(.active),
        [data-ll-theme="dark"] #ll-content .btn-light,
        [data-ll-theme="dark"] #ll-content .btn-soft-light,
        [data-ll-theme="dark"] .dropdown-menu,
        [data-ll-theme="dark"] .modal-content,
        [data-ll-theme="dark"] .modal-header,
        [data-ll-theme="dark"] .modal-footer,
        [data-ll-theme="dark"] .offcanvas {
            background: #10101f !important;
            color: var(--ll-text) !important;
            border-color: rgba(255,255,255,0.10) !important;
        }

        [data-ll-theme="dark"] #ll-content .badge.bg-light,
        [data-ll-theme="dark"] #ll-content .badge.text-dark {
            color: var(--ll-text) !important;
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(255,255,255,0.10) !important;
        }

        [data-ll-theme="dark"] #ll-content .alert-success {
            color: #bbf7d0 !important;
            background: rgba(34, 197, 94, 0.12) !important;
            border-color: rgba(34, 197, 94, 0.24) !important;
        }

        [data-ll-theme="dark"] #ll-content .alert-danger {
            color: #fecaca !important;
            background: rgba(239, 68, 68, 0.12) !important;
            border-color: rgba(239, 68, 68, 0.24) !important;
        }

        [data-ll-theme="dark"] #ll-content .alert-warning {
            color: #fde68a !important;
            background: rgba(245, 158, 11, 0.12) !important;
            border-color: rgba(245, 158, 11, 0.24) !important;
        }

        [data-ll-theme="dark"] #ll-content .text-muted,
        [data-ll-theme="dark"] #ll-content .form-text,
        [data-ll-theme="dark"] #ll-content small {
            color: var(--ll-muted) !important;
        }

        [data-ll-theme="dark"] #ll-content .table > :not(caption) > * > * {
            color: var(--ll-text) !important;
            background: transparent !important;
            border-color: rgba(255,255,255,0.10) !important;
        }

        .ll-nav-link.is-loading {
            opacity: 0.72;
            pointer-events: none;
        }

        .ll-nav-link.active {
            color: #fff !important;
            background: linear-gradient(135deg, var(--ll-primary), color-mix(in srgb, var(--ll-primary-2) 86%, var(--ll-primary))) !important;
            box-shadow: inset 0 0 0 1px color-mix(in srgb, #ffffff 18%, transparent),
                        0 8px 22px color-mix(in srgb, var(--ll-primary) 34%, transparent) !important;
        }

        .ll-nav-link.active i,
        .ll-nav-link.active span {
            color: #fff !important;
        }

        @media (max-width: 1199.98px) {
            .ll-sidebar {
                transform: translateX(-112%);
                opacity: 0;
            }

            .ll-sidebar-open .ll-sidebar {
                transform: translateX(0);
                opacity: 1;
            }

            .ll-sidebar-close { display: grid; }

            .ll-main {
                margin-left: 0;
                padding: 12px;
            }

            .ll-mobile-menu { display: grid; }
        }

        @media (min-width: 1200px) {
            .ll-mobile-menu { display: none; }
        }

        @media (max-width: 767.98px) {
            .ll-sidebar {
                width: min(92vw, 326px);
                inset: 10px auto 10px 10px;
                border-radius: 24px;
            }

            .ll-main { padding: 10px; }

            .ll-topbar {
                top: 10px;
                min-height: auto;
                height: auto;
                padding: 10px;
                border-radius: 22px;
                flex-wrap: wrap;
            }

            .ll-topbar-spacer { display: none; }

            .ll-topbar-actions {
                width: 100%;
                display: grid !important;
                grid-template-columns: 1fr auto auto;
                gap: 8px;
            }

            .ll-profile-text { display: none; }

            .ll-pill-button {
                justify-content: center;
                padding: 0 12px;
            }

            .ll-hero {
                margin-top: 12px;
                padding: 18px;
                border-radius: 22px;
            }

            .ll-footer {
                justify-content: center;
                text-align: center;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>

    <div class="ll-overlay" data-ll-close-sidebar></div>

    <div class="ll-shell">
        <aside class="ll-sidebar" aria-label="Livelatch sidebar">
            <div class="ll-sidebar-brand">
                <a href="{{ route('panelIndex') }}" class="d-flex align-items-center gap-3 text-decoration-none min-w-0">
                    <div class="ll-brand-mark">
                        @if(file_exists(base_path("assets/linkstack/images/").findFile('avatar')))
                            <img src="{{ asset('assets/linkstack/images/'.findFile('avatar')) }}" alt="{{ env('APP_NAME') }}">
                        @else
                            <img src="{{ asset('assets/linkstack/images/logo.svg') }}" alt="{{ env('APP_NAME') }}">
                        @endif
                    </div>
                    <div class="ll-brand-copy">
                        <p class="ll-brand-title">Live Latch</p>
                        <span class="ll-brand-badge">Alpha 1.0</span>
                    </div>
                </a>

                <button class="ll-sidebar-close" type="button" data-ll-close-sidebar aria-label="Close menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="ll-sidebar-body data-scrollbar">
                <livewire:studio-navigation />
            </div>

        </aside>

        <main class="main-content ll-main">
            <nav class="ll-topbar" aria-label="Livelatch top navigation">
                <button class="ll-mobile-menu" type="button" data-ll-open-sidebar aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>

                @php
                    $llHandle = optional(auth()->user())->littlelink_name;
                    $llWho = $llHandle ? '@'.$llHandle : optional(auth()->user())->name;
                @endphp
                <div>
                    <h6 class="mb-0 fw-bold">Thanks {{ $llWho }} for participating in the alpha 💙</h6>
                    <small style="color: var(--ll-muted);">You're testing Livelatch Alpha 1.0</small>
                </div>

                <div class="ll-topbar-spacer"></div>

                <div class="ll-topbar-actions d-flex align-items-center gap-2">
                    <a href="{{ url('/studio/development-journey') }}" class="ll-journey-link" title="Development Journey">
                        <i class="bi bi-hourglass-split"></i>
                        <span class="ll-journey-text">Development Journey</span>
                    </a>

                    <a href="{{ $profileUrl }}" target="_blank" class="ll-icon-button" aria-label="{{ __('messages.View Page') }}" title="{{ __('messages.View Page') }}">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>

                    <div class="dropdown">
                        <button class="ll-icon-button" type="button" id="llNotificationMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="bi bi-bell-fill"></i>
                            @if($livelatchUnreadNotificationCount > 0)
                                <span class="ll-dot"></span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end ll-notification-dropdown" aria-labelledby="llNotificationMenu">
                            <div class="ll-notification-header">
                                <h6>Notifications</h6>
                                @if($livelatchUnreadNotificationCount > 0)
                                    <p>{{ $livelatchUnreadNotificationCount }} unread notification{{ $livelatchUnreadNotificationCount === 1 ? '' : 's' }}</p>
                                @else
                                    <p>You're all caught up</p>
                                @endif
                            </div>

                            <div class="ll-notification-list">
                                @forelse($livelatchNotifications as $notification)
                                    @php
                                        $severity = $notification['severity'] ?? 'info';
                                        $severityClass = match ($severity) {
                                            'success' => 'success',
                                            'warning' => 'warning',
                                            'danger' => 'danger',
                                            default => 'info',
                                        };

                                        $notificationUrl = $notification['action_url'] ?? '#';
                                        $notificationIcon = $notification['icon'] ?? 'bi-bell-fill';
                                        $notificationSource = $notification['source'] ?? null;
                                        $notificationCreatedAt = $notification['created_at'] ?? null;
                                        $isUnread = !($notification['is_read'] ?? false);
                                    @endphp

                                    <a href="{{ $notificationUrl }}"
                                       class="ll-notification-item {{ $isUnread ? 'is-unread' : '' }}">
                                        <div class="ll-notification-icon {{ $severityClass }}">
                                            <i class="bi {{ $notificationIcon }}"></i>
                                        </div>

                                        <div class="ll-notification-content">
                                            <p class="ll-notification-title">
                                                {{ $notification['title'] ?? 'Notification' }}
                                            </p>

                                            @if(!empty($notification['message']))
                                                <p class="ll-notification-message">
                                                    {{ $notification['message'] }}
                                                </p>
                                            @endif

                                            <div class="ll-notification-meta">
                                                @if($notificationSource)
                                                    <span class="ll-notification-source">{{ ucfirst($notificationSource) }}</span>
                                                @endif

                                                @if($notificationCreatedAt)
                                                    <span>{{ \Carbon\Carbon::parse($notificationCreatedAt)->diffForHumans() }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="ll-notification-empty">
                                        <i class="bi bi-bell-slash"></i>
                                        No notifications yet.
                                    </div>
                                @endforelse
                            </div>

                            <a href="#" class="ll-notification-footer" data-bs-toggle="modal" data-bs-target="#llNotificationCenterModal">
                                View notification center
                            </a>
                        </div>
                    </div>

                    <div class="ll-theme-toggle" aria-label="Theme switcher">
                        <button type="button" data-theme-choice="light" aria-label="Use light mode"><i class="bi bi-sun-fill"></i></button>
                        <button type="button" data-theme-choice="dark" aria-label="Use dark mode"><i class="bi bi-moon-stars-fill"></i></button>
                    </div>

                    <div class="dropdown">
                        <a class="ll-profile-trigger dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ profileImageUrl(auth()->id()) }}" alt="{{ optional(auth()->user())->name }}">
                            <span class="ll-profile-text">
                                <strong>{{ optional(auth()->user())->name }}</strong>
                                <span>
                                    @if($userRole == "admin")
                                        {{ __('messages.Administrator') }}
                                    @elseif($userRole == "vip")
                                        {{ __('messages.Verified user') }}
                                    @else
                                        {{ __('messages.User') }}
                                    @endif
                                </span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ url('/studio/page') }}"><i class="bi bi-person-fill"></i> {{ __('messages.Profile') }}</a></li>
                            <li><a class="dropdown-item" href="{{ url('/studio/latchid') }}"><i class="bi bi-gear-fill"></i> {{ __('messages.Settings') }}</a></li>
                            <li><a class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" role="button" aria-controls="offcanvasExample"><i class="bi bi-brush-fill"></i> {{ __('messages.Styling') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-left"></i> {{ __('messages.Logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            {{-- Dashboard hero lives in the dashboard content view. --}}
            @if(false)
            <section class="ll-hero">
                <div class="ll-hero-content">
                    <div class="ll-kicker">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Quick actions
                    </div>

                    @if(!isset($usrhandl))
                        <h1>👋 {{ __('messages.Hi') }}, {{ __('messages.stranger') }}</h1>
                    @else
                        <h1>👋 {{ __('messages.Hi') }}, {{ '@'.$usrhandl }}</h1>
                    @endif

                    <p>Jump into common Livelatch tasks without digging through the sidebar.</p>

                    <div class="ll-hero-actions">
                        <a href="{{ url('/studio/links') }}" class="ll-hero-button" hx-get="{{ url('/studio/links') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                            <i class="bi bi-link-45deg"></i>
                            <span>
                                {{ __('messages.Links') }}
                                <small>Manage blocks</small>
                            </span>
                        </a>

                        <a href="{{ url('/studio/page') }}" class="ll-hero-button secondary" hx-get="{{ url('/studio/page') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                            <i class="bi bi-person-badge"></i>
                            <span>
                                {{ __('messages.Appearance') }}
                                <small>Edit profile</small>
                            </span>
                        </a>

                        <a href="{{ url('/studio/theme') }}" class="ll-hero-button secondary" hx-get="{{ url('/studio/theme') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                            <i class="bi bi-stars"></i>
                            <span>
                                {{ __('messages.Themes') }}
                                <small>Preview style</small>
                            </span>
                        </a>

                        <a href="{{ $profileUrl }}" target="_blank" class="ll-hero-button secondary">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>
                                {{ __('messages.View Page') }}
                                <small>Open public profile</small>
                            </span>
                        </a>

                        @if(!isset($usrhandl))
                            <a href="{{ url('/studio/page') }}" class="ll-hero-button secondary" hx-get="{{ url('/studio/page') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                                <i class="bi bi-at"></i>
                                <span>
                                    {{ __('messages.Set a handle') }}
                                    <small>Required for sharing</small>
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            @endif

            <div class="ll-content-stage">
                <div id="ll-page-skeleton" class="htmx-indicator ll-content-skeleton" aria-hidden="true">
                    <div class="ll-content-skeleton-shell">
                        @include('components.skeleton.page')
                    </div>
                </div>
                <div id="ll-card-grid-skeleton" class="htmx-indicator ll-content-skeleton" aria-hidden="true">
                    <div class="ll-content-skeleton-shell">
                        @include('components.skeleton.card-grid')
                    </div>
                </div>
                <div id="ll-table-skeleton" class="htmx-indicator ll-content-skeleton" aria-hidden="true">
                    <div class="ll-content-skeleton-shell">
                        @include('components.skeleton.table')
                    </div>
                </div>
                <div id="ll-profile-skeleton" class="htmx-indicator ll-content-skeleton" aria-hidden="true">
                    <div class="ll-content-skeleton-shell">
                        @include('components.skeleton.profile')
                    </div>
                </div>

                <section id="ll-content" class="ll-content" aria-live="polite" aria-busy="false">
                    @yield('content')
                </section>
            </div>

            <footer class="ll-footer">
                <ul class="list-inline mb-0 p-0">
                    @if(env('DISPLAY_FOOTER') === true)
                        @if(env('DISPLAY_FOOTER_HOME') === true)
                            <li class="list-inline-item"><a href="@if(str_replace('"', "", EnvEditor::getKey('HOME_FOOTER_LINK')) === "" ){{ url('') }}@else{{ str_replace('"', "", EnvEditor::getKey('HOME_FOOTER_LINK')) }}@endif">{{ footer('Home') }}</a></li>
                        @endif
                        @if(env('DISPLAY_FOOTER_TERMS') === true)
                            <li class="list-inline-item"><a href="{{ url('') }}/pages/{{ strtolower(footer('Terms')) }}">{{ footer('Terms') }}</a></li>
                        @endif
                        @if(env('DISPLAY_FOOTER_PRIVACY') === true)
                            <li class="list-inline-item"><a href="{{ url('') }}/pages/{{ strtolower(footer('Privacy')) }}">{{ footer('Privacy') }}</a></li>
                        @endif
                        @if(env('DISPLAY_FOOTER_CONTACT') === true)
                            <li class="list-inline-item"><a href="{{ url('') }}/pages/{{ strtolower(footer('Contact')) }}">{{ footer('Contact') }}</a></li>
                        @endif
                    @endif
                </ul>

                <div>
                    {{ __('messages.Copyright') }} &copy; @php echo date('Y'); @endphp {{ config('app.name') }}
                    @if(env('DISPLAY_CREDIT_FOOTER') === true)
                        <span> · {{ __('messages.Made with') }} ♥ {{ __('messages.by') }} <a href="https://linkstack.org/" target="_blank">LinkStack</a></span>
                    @endif
                </div>
            </footer>
        </main>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" data-bs-scroll="true" data-bs-backdrop="true" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <div>
                <h3 class="offcanvas-title mb-1" id="offcanvasExampleLabel">{{ __('messages.Settings') }}</h3>
                <p class="mb-0" style="color: var(--ll-muted);">Quick display controls for your studio.</p>
            </div>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h5 class="mb-3">{{ __('messages.Scheme') }}</h5>
            <div class="d-grid gap-3">
                <button type="button" class="ll-pill-button justify-content-center" data-theme-choice="light">
                    <i class="bi bi-sun-fill"></i>
                    {{ __('messages.Light') }}
                </button>
                <button type="button" class="ll-pill-button justify-content-center" data-theme-choice="dark">
                    <i class="bi bi-moon-stars-fill"></i>
                    {{ __('messages.Dark') }}
                </button>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">{{ __('messages.Profile') }}</h5>
            <a href="{{ url('/studio/page') }}" class="ll-pill-button w-100 justify-content-center mb-2" hx-get="{{ url('/studio/page') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                <i class="bi bi-person-fill"></i>
                {{ __('messages.Profile') }}
            </a>
            <a href="{{ url('/studio/profile') }}" class="ll-pill-button w-100 justify-content-center" hx-get="{{ url('/studio/profile') }}" hx-target="#ll-content" hx-select="#ll-content > *" hx-push-url="true" hx-swap="innerHTML" hx-indicator="#ll-profile-skeleton">
                <i class="bi bi-gear-fill"></i>
                {{ __('messages.Settings') }}
            </a>
        </div>
    </div>

    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title" id="staticBackdropLabel">{{ __('messages.QR Code') }}</h5>
                        <p class="mb-0" style="color: var(--ll-muted); font-size: 0.9rem;">{{ $profileUrl }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') ?? 'Close' }}"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <div class="p-4 rounded-4 d-inline-block" style="background:#fff;">
                        {!! QrCode::size(250)->generate($profileUrl) !!}
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary rounded-pill share-button" data-share="{{ $profileUrl }}">
                        <i class="bi bi-share-fill"></i>
                        {{ __('messages.Share') ?? 'Share' }}
                    </button>
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('messages.Close') ?? 'Close' }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Notification center: Supabase-backed, per-user read state. --}}
    <style>
        #llNotificationCenterModal .modal-content {
            border: none;
            border-radius: 22px;
            overflow: hidden;
        }
        .ll-nc-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 0 1rem 0.75rem;
        }
        .ll-nc-tab {
            border: none;
            background: transparent;
            color: var(--ll-muted, #64748b);
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.35rem 0.1rem;
            border-bottom: 2px solid transparent;
            cursor: pointer;
        }
        .ll-nc-tab.active {
            color: #7c3aed;
            border-bottom-color: #7c3aed;
        }
        .ll-nc-tab .ll-nc-badge {
            display: inline-block;
            min-width: 1.2rem;
            margin-left: 0.25rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.14);
            color: #7c3aed;
            font-size: 0.72rem;
            line-height: 1.2rem;
            text-align: center;
        }
        .ll-nc-list {
            max-height: 60vh;
            overflow-y: auto;
        }
        .ll-nc-item {
            display: flex;
            gap: 0.85rem;
            padding: 0.85rem 0.25rem;
            border-bottom: 1px solid var(--border, rgba(15, 23, 42, 0.08));
        }
        .ll-nc-item:last-child { border-bottom: none; }
        .ll-nc-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            border-radius: 14px;
        }
        .ll-nc-icon.info    { color: #7c3aed; background: rgba(124, 58, 237, 0.14); }
        .ll-nc-icon.success { color: #16a34a; background: rgba(34, 197, 94, 0.14); }
        .ll-nc-icon.warning { color: #d97706; background: rgba(245, 158, 11, 0.16); }
        .ll-nc-icon.danger  { color: #dc2626; background: rgba(239, 68, 68, 0.15); }
        .ll-nc-body { flex: 1; min-width: 0; }
        .ll-nc-title { font-weight: 700; font-size: 0.92rem; }
        .ll-nc-message { color: var(--ll-muted, #64748b); font-size: 0.82rem; margin-top: 0.1rem; }
        .ll-nc-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-top: 0.45rem; font-size: 0.72rem; color: var(--ll-muted, #94a3b8); }
        .ll-nc-pill { padding: 0.1rem 0.45rem; border-radius: 999px; background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-weight: 700; }
        .ll-nc-action { border: none; background: transparent; color: #7c3aed; font-weight: 700; font-size: 0.74rem; cursor: pointer; padding: 0; }
        .ll-nc-empty { text-align: center; color: var(--ll-muted, #64748b); padding: 2rem 1rem; }
        .ll-nc-empty i { font-size: 1.6rem; display: block; margin-bottom: 0.4rem; }
    </style>

    <div class="modal fade" id="llNotificationCenterModal" tabindex="-1" aria-labelledby="llNotificationCenterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title" id="llNotificationCenterLabel">Notification center</h5>
                        <p class="mb-0" style="color: var(--ll-muted); font-size: 0.9rem;">Unread notifications and your inbox</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') ?? 'Close' }}"></button>
                </div>

                <div class="ll-nc-tabs">
                    <button type="button" class="ll-nc-tab active" data-ll-nc-tab="unread">
                        Unread <span class="ll-nc-badge" id="llNcUnreadBadge">0</span>
                    </button>
                    <button type="button" class="ll-nc-tab" data-ll-nc-tab="read">Inbox</button>
                    <button type="button" class="ll-nc-action ms-auto" id="llNcMarkAll">Mark all as read</button>
                </div>

                <div class="modal-body pt-0">
                    <div class="ll-nc-list" id="llNcUnread"></div>
                    <div class="ll-nc-list d-none" id="llNcRead"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const modalEl = document.getElementById('llNotificationCenterModal');
        if (!modalEl) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const unreadEl = document.getElementById('llNcUnread');
        const readEl = document.getElementById('llNcRead');
        const unreadBadge = document.getElementById('llNcUnreadBadge');
        const markAllBtn = document.getElementById('llNcMarkAll');
        const bellMenu = document.getElementById('llNotificationMenu');

        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const severityClass = (s) => ['info', 'success', 'warning', 'danger'].includes(s) ? s : 'info';

        function timeAgo(iso) {
            if (!iso) return '';
            const then = new Date(iso).getTime();
            if (isNaN(then)) return '';
            const secs = Math.max(1, Math.floor((Date.now() - then) / 1000));
            const units = [[31536000, 'y'], [2592000, 'mo'], [604800, 'w'], [86400, 'd'], [3600, 'h'], [60, 'm']];
            for (const [s, label] of units) {
                if (secs >= s) return Math.floor(secs / s) + label + ' ago';
            }
            return secs + 's ago';
        }

        function itemHtml(n, isRead) {
            const id = esc(n.id);
            const action = isRead
                ? `<button type="button" class="ll-nc-action" data-ll-nc-unread="${id}">Mark unread</button>`
                : `<button type="button" class="ll-nc-action" data-ll-nc-read="${id}">Mark read</button>`;
            const link = n.action_url ? `<a class="ll-nc-action" href="${esc(n.action_url)}">Open</a>` : '';
            const source = n.source ? `<span class="ll-nc-pill">${esc(n.source.charAt(0).toUpperCase() + n.source.slice(1))}</span>` : '';
            const message = n.message ? `<div class="ll-nc-message">${esc(n.message)}</div>` : '';
            return `
                <div class="ll-nc-item" data-ll-nc-id="${id}">
                    <div class="ll-nc-icon ${severityClass(n.severity)}"><i class="bi ${esc(n.icon || 'bi-bell-fill')}"></i></div>
                    <div class="ll-nc-body">
                        <div class="ll-nc-title">${esc(n.title || 'Notification')}</div>
                        ${message}
                        <div class="ll-nc-meta">
                            ${source}
                            <span>${timeAgo(n.created_at)}</span>
                            ${action}
                            ${link}
                        </div>
                    </div>
                </div>`;
        }

        function emptyHtml(label) {
            return `<div class="ll-nc-empty"><i class="bi bi-bell-slash"></i>${label}</div>`;
        }

        function syncBell(count) {
            if (!bellMenu) return;
            let dot = bellMenu.querySelector('.ll-dot');
            if (count > 0 && !dot) {
                dot = document.createElement('span');
                dot.className = 'll-dot';
                bellMenu.appendChild(dot);
            } else if (count === 0 && dot) {
                dot.remove();
            }
        }

        async function load() {
            unreadEl.innerHTML = emptyHtml('Loading…');
            readEl.innerHTML = '';
            try {
                const res = await fetch('{{ route('studio.notifications') }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                unreadEl.innerHTML = (data.unread && data.unread.length)
                    ? data.unread.map((n) => itemHtml(n, false)).join('')
                    : emptyHtml("You're all caught up.");
                readEl.innerHTML = (data.read && data.read.length)
                    ? data.read.map((n) => itemHtml(n, true)).join('')
                    : emptyHtml('Your inbox is empty.');
                unreadBadge.textContent = data.unread_count ?? 0;
                syncBell(data.unread_count ?? 0);
            } catch (e) {
                unreadEl.innerHTML = emptyHtml('Could not load notifications.');
            }
        }

        async function post(url) {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                return res.ok ? await res.json() : null;
            } catch (e) {
                return null;
            }
        }

        modalEl.addEventListener('show.bs.modal', load);

        modalEl.addEventListener('click', async (e) => {
            const readId = e.target.closest('[data-ll-nc-read]')?.getAttribute('data-ll-nc-read');
            const unreadId = e.target.closest('[data-ll-nc-unread]')?.getAttribute('data-ll-nc-unread');
            if (readId) {
                e.preventDefault();
                await post('{{ url('studio/notifications') }}/' + encodeURIComponent(readId) + '/read');
                await load();
            } else if (unreadId) {
                e.preventDefault();
                await post('{{ url('studio/notifications') }}/' + encodeURIComponent(unreadId) + '/unread');
                await load();
            }
        });

        markAllBtn.addEventListener('click', async () => {
            await post('{{ route('studio.notifications.readAll') }}');
            await load();
        });

        modalEl.querySelectorAll('[data-ll-nc-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                modalEl.querySelectorAll('[data-ll-nc-tab]').forEach((t) => t.classList.remove('active'));
                tab.classList.add('active');
                const which = tab.getAttribute('data-ll-nc-tab');
                unreadEl.classList.toggle('d-none', which !== 'unread');
                readEl.classList.toggle('d-none', which !== 'read');
            });
        });
    })();
    </script>

    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>
    <script src="{{ asset('assets/js/charts/vectore-chart.js') }}"></script>
    <script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/fslightbox.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/setting.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/slider-tabs.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/form-wizard.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/dist/aos.js') }}"></script>
    <script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>

    <script src="{{ asset('assets/linkstack/js/cookie.js') }}"></script>
    <script src="{{ asset('assets/linkstack/js/clipboard.min.js') }}"></script>
    <script src="{{ asset('assets/js/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-block-ui.js') }}"></script>
    <script src="{{ asset('assets/js/main-dashboard.js') }}"></script>
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>

    <script>
        (function () {
            const body = document.body;
            const root = document.documentElement;
            const storageKey = 'livelatch-theme';
            const legacyStorageKey = 'color-mode';

            function getStoredTheme() {
                try {
                    const storedTheme = localStorage.getItem(storageKey);
                    const legacyTheme = localStorage.getItem(legacyStorageKey);

                    if (storedTheme === 'light' || storedTheme === 'dark') {
                        return storedTheme;
                    }

                    if (legacyTheme === 'light' || legacyTheme === 'dark') {
                        return legacyTheme;
                    }
                } catch (error) {
                    return null;
                }

                return null;
            }

            function storeTheme(theme) {
                try {
                    localStorage.setItem(storageKey, theme);
                    localStorage.setItem(legacyStorageKey, theme);
                } catch (error) {
                    // Storage can be blocked in private or restricted browser modes.
                }
            }

            const initialTheme = root.getAttribute('data-ll-theme') || getStoredTheme() || 'dark';

            function clearInlineThemeOverrides(el) {
                if (!el) {
                    return;
                }

                const names = [];
                for (let i = 0; i < el.style.length; i++) {
                    const prop = el.style[i];
                    if (prop && prop.indexOf('--ll-') === 0) {
                        names.push(prop);
                    }
                }

                names.forEach(name => el.style.removeProperty(name));
            }

            function setTheme(theme) {
                const safeTheme = theme === 'dark' ? 'dark' : 'light';

                // Remove any inline design-token overrides left on the document
                // (for example by the admin Dev Tools live preview) so the chosen
                // theme always wins instead of a stale dark/light value leaking in.
                clearInlineThemeOverrides(root);
                clearInlineThemeOverrides(body);

                root.setAttribute('data-ll-theme', safeTheme);
                root.setAttribute('data-bs-theme', safeTheme);
                root.style.colorScheme = safeTheme;

                body.setAttribute('data-ll-theme', safeTheme);
                body.setAttribute('data-bs-theme', safeTheme);
                body.style.colorScheme = safeTheme;

                storeTheme(safeTheme);
            }

            function setActiveNavFromUrl(url) {
                const path = new URL(url, window.location.origin).pathname.replace(/\/$/, '') || '/';

                document.querySelectorAll('.ll-nav-link').forEach(link => {
                    const linkPath = new URL(link.getAttribute('href'), window.location.origin).pathname.replace(/\/$/, '') || '/';
                    link.classList.toggle('active', linkPath === path);
                    link.classList.remove('is-loading');
                });
            }

            function closeMobileSidebar() {
                if (window.innerWidth < 1200) {
                    body.classList.remove('ll-sidebar-open');
                }
            }

            function polishInjectedContent() {
                const content = document.getElementById('ll-content');

                if (!content) {
                    return;
                }

                content.querySelectorAll('img').forEach(img => {
                    img.setAttribute('loading', img.getAttribute('loading') || 'lazy');
                });

                content.querySelectorAll('.card[style*="height"], .iq-card[style*="height"]').forEach(card => {
                    card.style.minHeight = 'auto';
                });
            }

            function initStudioNavigation() {
                const nav = document.querySelector('.ll-nav-rail');

                if (!nav || nav.dataset.llNavInitialized === 'true') {
                    return;
                }

                nav.dataset.llNavInitialized = 'true';

                nav.querySelectorAll('[data-ll-nav-toggle]').forEach(button => {
                    button.addEventListener('click', () => {
                        const group = button.closest('[data-ll-nav-group]');

                        if (!group) {
                            return;
                        }

                        const shouldOpen = !group.classList.contains('is-open');

                        nav.querySelectorAll('[data-ll-nav-group]').forEach(otherGroup => {
                            otherGroup.classList.remove('is-open');
                            otherGroup.querySelector('[data-ll-nav-toggle]')?.setAttribute('aria-expanded', 'false');
                        });

                        if (shouldOpen) {
                            group.classList.add('is-open');
                            button.setAttribute('aria-expanded', 'true');
                        }
                    });
                });

                if (window.htmx) {
                    window.htmx.process(nav);
                }
            }

            setTheme(initialTheme);
            setActiveNavFromUrl(window.location.href);
            polishInjectedContent();
            initStudioNavigation();

            document.querySelectorAll('[data-theme-choice]').forEach(button => {
                button.addEventListener('click', () => setTheme(button.dataset.themeChoice));
            });

            // Re-assert the stored theme after every HTMX page swap. This keeps the
            // chosen light/dark mode authoritative and clears any inline token leaks
            // (e.g. from the Dev Tools live preview) when navigating between screens.
            document.body.addEventListener('htmx:afterSettle', () => {
                setTheme(getStoredTheme() || 'dark');
            });

            document.querySelectorAll('[data-ll-open-sidebar]').forEach(button => {
                button.addEventListener('click', () => body.classList.add('ll-sidebar-open'));
            });

            document.querySelectorAll('[data-ll-close-sidebar]').forEach(button => {
                button.addEventListener('click', () => body.classList.remove('ll-sidebar-open'));
            });

            document.querySelectorAll('.ll-nav-link[href]').forEach(link => {
                link.addEventListener('click', closeMobileSidebar);
            });

            document.body.addEventListener('htmx:beforeRequest', function (event) {
                const clickedLink = event.detail.elt;
                const content = document.getElementById('ll-content');

                if (clickedLink && clickedLink.getAttribute('hx-target') === '#ll-content' && content) {
                    content.setAttribute('aria-busy', 'true');
                }

                if (clickedLink && clickedLink.classList.contains('ll-nav-link')) {
                    document.querySelectorAll('.ll-nav-link').forEach(link => {
                        link.classList.remove('active', 'is-loading');
                    });

                    clickedLink.classList.add('active', 'is-loading');
                }
            });

            document.body.addEventListener('htmx:beforeSwap', function (event) {
                if (event.detail.target && event.detail.target.id === 'll-content') {
                    event.detail.target.classList.add('htmx-swapping');
                }
            });

            document.body.addEventListener('htmx:afterSwap', function (event) {
                if (event.detail.target && event.detail.target.id === 'll-content') {
                    event.detail.target.classList.remove('htmx-swapping');
                    event.detail.target.classList.add('htmx-settling');
                    event.detail.target.setAttribute('aria-busy', 'false');

                    setTimeout(() => {
                        event.detail.target.classList.remove('htmx-settling');
                    }, 220);

                    polishInjectedContent();
                    initStudioNavigation();
                    setActiveNavFromUrl(window.location.href);
                    closeMobileSidebar();
                }
            });

            document.body.addEventListener('htmx:responseError', function () {
                const content = document.getElementById('ll-content');

                if (content) {
                    content.setAttribute('aria-busy', 'false');
                }

                document.querySelectorAll('.ll-nav-link').forEach(link => link.classList.remove('is-loading'));
            });

            document.body.addEventListener('htmx:afterSettle', function () {
                if (window.AOS) {
                    AOS.refresh();
                }
            });

            window.addEventListener('popstate', () => {
                setActiveNavFromUrl(window.location.href);
            });
        })();

        document.querySelectorAll('.share-button').forEach(button => {
            button.addEventListener('click', () => {
                const valueToShare = button.getAttribute('data-share');

                if (navigator.share) {
                    navigator.share({
                        title: '{{ config('app.name') }}',
                        text: valueToShare,
                        url: valueToShare
                    }).catch(err => console.error('{{ __("messages.Error sharing:") }}', err));
                } else if (navigator.clipboard) {
                    navigator.clipboard.writeText(valueToShare)
                        .then(() => alert('{{ __("messages.Text copied to clipboard!") }}'))
                        .catch(err => alert('{{ __("messages.Error copying text:") }} ' + err));
                }
            });
        });
    </script>

    @stack('sidebar-scripts')
    @livewireScripts
    @include('partials.studio-tour')
</body>
</html>
