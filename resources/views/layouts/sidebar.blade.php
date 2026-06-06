@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\User;

$usrhandl = Auth::user()->littlelink_name ?? null;
$profileUrl = $usrhandl ? url('/@'.$usrhandl) : url('/studio/page');
$userRole = optional(auth()->user())->role;
@endphp
<!doctype html>
@include('layouts.lang')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }}</title>

    <script src="{{ asset('assets/js/detect-dark-mode.js') }}"></script>
    <base href="{{ url()->current() }}" />

    @include('layouts.analytics')
    @stack('sidebar-stylesheets')
    @include('layouts.notifications')

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

        :root {
            --ll-font: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --ll-bg: #f7f7fb;
            --ll-bg-soft: #ffffff;
            --ll-surface: rgba(255, 255, 255, 0.78);
            --ll-surface-solid: #ffffff;
            --ll-text: #120f2d;
            --ll-muted: #6b6885;
            --ll-border: rgba(18, 15, 45, 0.10);
            --ll-shadow: 0 24px 70px rgba(30, 16, 80, 0.10);
            --ll-shadow-soft: 0 12px 34px rgba(30, 16, 80, 0.08);
            --ll-primary: #6236ff;
            --ll-primary-2: #9b5cff;
            --ll-primary-3: #12d6df;
            --ll-danger: #ef4444;
            --ll-success: #22c55e;
            --ll-radius: 22px;
            --ll-sidebar-width: 292px;
            --ll-topbar-height: 74px;
        }

        [data-ll-theme="dark"] {
            --ll-bg: #070711;
            --ll-bg-soft: #0d0d1b;
            --ll-surface: rgba(16, 16, 31, 0.78);
            --ll-surface-solid: #10101f;
            --ll-text: #f7f5ff;
            --ll-muted: #a7a2c7;
            --ll-border: rgba(255, 255, 255, 0.10);
            --ll-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
            --ll-shadow-soft: 0 12px 34px rgba(0, 0, 0, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            font-family: var(--ll-font);
            background:
                radial-gradient(circle at 18% -12%, rgba(98, 54, 255, 0.16), transparent 28%),
                radial-gradient(circle at 90% 10%, rgba(18, 214, 223, 0.10), transparent 26%),
                var(--ll-bg);
            color: var(--ll-text);
        }

        body {
            margin: 0;
            overflow-x: hidden;
        }

        body,
        p,
        div,
        span,
        a,
        button,
        input,
        textarea,
        select,
        label {
            font-family: var(--ll-font);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--ll-font);
            font-weight: 700;
            color: var(--ll-text);
        }

        a {
            color: inherit;
        }

        #loading {
            background: var(--ll-bg);
        }

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
            box-shadow: 0 14px 28px rgba(98, 54, 255, 0.32);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .ll-brand-mark img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .ll-brand-copy {
            min-width: 0;
        }

        .ll-brand-title {
            font-size: 1.02rem;
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

        .ll-nav-section {
            margin: 20px 0 8px;
            padding: 0 12px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            color: var(--ll-muted);
        }

        .ll-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ll-nav-link {
            min-height: 46px;
            margin-bottom: 6px;
            padding: 0 12px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: var(--ll-muted);
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .ll-nav-link i,
        .ll-nav-link svg {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
        }

        .ll-nav-link:hover {
            color: var(--ll-text);
            background: rgba(98, 54, 255, 0.08);
            transform: translateX(2px);
        }

        .ll-nav-link.active,
        .ll-nav-link.bg-soft-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 14px 28px rgba(98, 54, 255, 0.28);
        }

        .ll-sidebar-footer {
            margin-top: auto;
            padding: 18px;
        }

        .ll-user-chip {
            border: 1px solid var(--ll-border);
            border-radius: 20px;
            padding: 12px;
            background: rgba(98, 54, 255, 0.06);
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

        .ll-topbar-spacer {
            flex: 1;
        }

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
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            text-decoration: none;
        }

        .ll-pill-button.primary {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            box-shadow: 0 14px 28px rgba(98, 54, 255, 0.24);
        }

        .ll-icon-button {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            position: relative;
        }

        .ll-pill-button:hover,
        .ll-icon-button:hover {
            transform: translateY(-1px);
            border-color: rgba(98, 54, 255, 0.32);
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
            margin-top: 18px;
            min-height: 246px;
            border-radius: 32px;
            padding: clamp(28px, 5vw, 56px);
            color: #fff;
            background:
                radial-gradient(circle at 82% 18%, rgba(18, 214, 223, 0.56), transparent 24%),
                radial-gradient(circle at 18% 10%, rgba(155, 92, 255, 0.78), transparent 26%),
                linear-gradient(135deg, #0b041c 0%, #241064 45%, #6d28d9 100%);
            box-shadow: var(--ll-shadow);
            isolation: isolate;
        }

        .ll-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(circle at 55% 45%, black 0%, transparent 78%);
            z-index: -2;
        }

        .ll-hero::after {
            content: "";
            position: absolute;
            width: 480px;
            height: 480px;
            right: -180px;
            top: -170px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,255,255,0.22), transparent 64%);
            z-index: -1;
        }

        .ll-hero-content {
            max-width: 780px;
            position: relative;
            z-index: 2;
        }

        .ll-kicker {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(16px);
            color: rgba(255,255,255,0.84);
            font-weight: 700;
            font-size: 0.82rem;
            margin-bottom: 18px;
        }

        .ll-hero h1 {
            color: #fff;
            font-size: clamp(2.25rem, 7vw, 5rem);
            font-weight: 800;
            letter-spacing: -0.075em;
            line-height: 0.94;
            margin: 0;
        }

        .ll-hero p {
            color: rgba(255,255,255,0.78);
            margin: 16px 0 0;
            font-size: clamp(1rem, 2vw, 1.15rem);
            font-weight: 500;
            max-width: 560px;
        }

        .ll-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .ll-hero-button {
            min-height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 800;
            color: #160c32;
            background: #fff;
            border: 1px solid rgba(255,255,255,0.26);
        }

        .ll-hero-button.secondary {
            color: #fff;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
        }

        .ll-content {
            padding: 22px 0 30px;
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

        .dropdown-menu {
            padding: 10px;
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

        .text-primary {
            color: var(--ll-primary) !important;
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

            .ll-sidebar-close {
                display: grid;
            }

            .ll-main {
                margin-left: 0;
                padding: 12px;
            }

            .ll-mobile-menu {
                display: grid;
            }
        }

        @media (min-width: 1200px) {
            .ll-mobile-menu {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .ll-sidebar {
                width: min(92vw, 326px);
                inset: 10px auto 10px 10px;
                border-radius: 24px;
            }

            .ll-main {
                padding: 10px;
            }

            .ll-topbar {
                top: 10px;
                min-height: auto;
                height: auto;
                padding: 10px;
                border-radius: 22px;
                flex-wrap: wrap;
            }

            .ll-topbar-spacer {
                display: none;
            }

            .ll-topbar-actions {
                width: 100%;
                display: grid !important;
                grid-template-columns: 1fr auto auto;
                gap: 8px;
            }

            .ll-profile-text {
                display: none;
            }

            .ll-pill-button {
                justify-content: center;
                padding: 0 12px;
            }

            .ll-hero {
                min-height: 220px;
                margin-top: 12px;
                padding: 26px 22px;
                border-radius: 26px;
            }

            .ll-hero h1 {
                letter-spacing: -0.06em;
            }

            .ll-footer {
                justify-content: center;
                text-align: center;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body data-ll-theme="light">
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
                        <p class="ll-brand-title">{{ env('APP_NAME') }}</p>
                        <p class="ll-brand-subtitle">Creator studio</p>
                    </div>
                </a>

                <button class="ll-sidebar-close" type="button" data-ll-close-sidebar aria-label="Close menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="ll-sidebar-body data-scrollbar">
                <p class="ll-nav-section">{{ __('messages.Home') }}</p>
                <ul class="ll-nav-list">
                    <li>
                        <a class="ll-nav-link {{ Request::segment(1) == 'dashboard' ? 'active' : '' }}" href="{{ route('panelIndex') }}">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>{{ __('messages.Dashboard') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="ll-nav-link {{ Request::segment(2) == 'add-link' ? 'active' : '' }}" href="{{ url('/studio/add-link') }}">
                            <i class="bi bi-plus-square-fill"></i>
                            <span>{{ __('messages.Add Link') }}</span>
                        </a>
                    </li>
                </ul>

                @if(auth()->user()->role == 'admin')
                    <p class="ll-nav-section">{{ __('messages.Administration') }}</p>
                    <ul class="ll-nav-list">
                        <li>
                            <a class="ll-nav-link" data-bs-toggle="collapse" href="#llAdminMenu" role="button" aria-expanded="false" aria-controls="llAdminMenu">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>{{ __('messages.Admin') }}</span>
                                <i class="bi bi-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse" id="llAdminMenu">
                                <a class="ll-nav-link {{ Request::segment(2) == 'config' ? 'active' : '' }}" href="{{ url('admin/config') }}">
                                    <i class="bi bi-sliders"></i>
                                    <span>{{ __('messages.Config') }}</span>
                                </a>
                                <a class="ll-nav-link {{ Request::segment(2) == 'users' ? 'active' : '' }}" href="{{ url('admin/users/all') }}">
                                    <i class="bi bi-people-fill"></i>
                                    <span>{{ __('messages.Manage Users') }}</span>
                                </a>
                                <a class="ll-nav-link {{ Request::segment(2) == 'pages' ? 'active' : '' }}" href="{{ url('admin/pages') }}">
                                    <i class="bi bi-collection-fill"></i>
                                    <span>{{ __('messages.Footer Pages') }}</span>
                                </a>
                                <a class="ll-nav-link {{ Request::segment(2) == 'site' ? 'active' : '' }}" href="{{ url('admin/site') }}">
                                    <i class="bi bi-palette-fill"></i>
                                    <span>{{ __('messages.Site Customization') }}</span>
                                </a>
                            </div>
                        </li>
                    </ul>
                @endif

                <p class="ll-nav-section">{{ __('messages.Personalization') }}</p>
                <ul class="ll-nav-list">
                    <li>
                        <a class="ll-nav-link {{ Request::segment(2) == 'links' ? 'active' : '' }}" href="{{ url('/studio/links') }}">
                            <i class="bi bi-link-45deg"></i>
                            <span>{{ __('messages.Links') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="ll-nav-link {{ Request::segment(2) == 'page' ? 'active' : '' }}" href="{{ url('/studio/page') }}">
                            <i class="bi bi-person-badge-fill"></i>
                            <span>{{ __('messages.Appearance') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="ll-nav-link {{ Request::segment(2) == 'theme' ? 'active' : '' }}" href="{{ url('/studio/theme') }}">
                            <i class="bi bi-stars"></i>
                            <span>{{ __('messages.Themes') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="ll-sidebar-footer">
                <div class="ll-user-chip">
                    <img src="{{ profileImageUrl(auth()->id()) }}" alt="{{ optional(auth()->user())->name }}" class="ll-avatar">
                    <div class="min-w-0">
                        <p class="ll-user-name">{{ optional(auth()->user())->name }}</p>
                        <p class="ll-user-role">
                            @if($userRole == "admin")
                                {{ __('messages.Administrator') }}
                            @elseif($userRole == "vip")
                                {{ __('messages.Verified user') }}
                            @else
                                {{ __('messages.User') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content ll-main">
            <nav class="ll-topbar" aria-label="Livelatch top navigation">
                <button class="ll-mobile-menu" type="button" data-ll-open-sidebar aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>

                <div>
                    <h6 class="mb-0 fw-bold">Studio</h6>
                    <small style="color: var(--ll-muted);">Manage your Livelatch presence</small>
                </div>

                <div class="ll-topbar-spacer"></div>

                <div class="ll-topbar-actions d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <a href="{{ $profileUrl }}" target="_blank" class="ll-pill-button primary">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>{{ __('messages.View Page') }}</span>
                        </a>
                    </div>

                    <div class="dropdown">
                        <button class="ll-icon-button" type="button" id="llShareMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Share profile">
                            <i class="bi bi-share-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="llShareMenu">
                            <li><h6 class="dropdown-header">{{ __('messages.Share your profile:') }}</h6></li>
                            @if(env('SUPPORTED_DOMAINS') !== '' and env('SUPPORTED_DOMAINS') !== null)
                                @php
                                    $sDomains = str_replace(' ', '', env('SUPPORTED_DOMAINS'));
                                    $sDomains = explode(',', $sDomains);
                                @endphp
                                @foreach ($sDomains as $myvar)
                                    <li>
                                        <a class="dropdown-item share-button" style="cursor:pointer!important;" data-share="{{ 'https://'.$myvar.'/@'.Auth::user()->littlelink_name }}">
                                            <i class="bi bi-files"></i> {{ $myvar }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <a class="dropdown-item share-button" style="cursor:pointer!important;" data-share="{{ url('').'/@'.Auth::user()->littlelink_name }}">
                                        <i class="bi bi-files"></i> {{ str_replace(['http://', 'https://'], '', url('')) }}
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" data-bs-toggle="modal" style="cursor:pointer!important;" data-bs-target="#staticBackdrop">
                                    <i class="bi bi-qr-code-scan"></i> {{ __('messages.QR Code') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <button class="ll-icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell-fill"></i>
                        @if($GLOBALS['activenotify'])
                            <span class="ll-dot"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0">
                        <div class="card border-0 shadow-none m-0" style="min-width: 320px;">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 text-white">{{ __('messages.All Notifications') }}</h6>
                            </div>
                            <div class="card-body p-0">
                                @stack('notifications')
                            </div>
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
                            <li><a class="dropdown-item" href="{{ url('/studio/profile') }}"><i class="bi bi-gear-fill"></i> {{ __('messages.Settings') }}</a></li>
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

            <section class="ll-hero">
                <div class="ll-hero-content">
                    <div class="ll-kicker">
                        <i class="bi bi-stars"></i>
                        Livelatch Studio
                    </div>

                    @if(!isset($usrhandl))
                        <h1>👋 {{ __('messages.Hi') }}, {{ __('messages.stranger') }}</h1>
                    @else
                        <h1>👋 {{ __('messages.Hi') }}, {{ '@'.$usrhandl }}</h1>
                    @endif

                    <p>{{ __('messages.welcome', ['appName' => config('app.name')]) }}</p>

                    <div class="ll-hero-actions">
                        <a href="{{ url('/studio/links') }}" class="ll-hero-button">
                            <i class="bi bi-link-45deg"></i>
                            {{ __('messages.Links') }}
                        </a>

                        <a href="{{ url('/studio/page') }}" class="ll-hero-button secondary">
                            <i class="bi bi-person-badge"></i>
                            {{ __('messages.Appearance') }}
                        </a>

                        @if(!isset($usrhandl))
                            <a href="{{ url('/studio/page') }}" class="ll-hero-button secondary">
                                <i class="bi bi-at"></i>
                                {{ __('messages.Set a handle') }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            <section class="ll-content">
                @yield('content')
            </section>

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
            <a href="{{ url('/studio/page') }}" class="ll-pill-button w-100 justify-content-center mb-2">
                <i class="bi bi-person-fill"></i>
                {{ __('messages.Profile') }}
            </a>
            <a href="{{ url('/studio/profile') }}" class="ll-pill-button w-100 justify-content-center">
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

    <script>
        (function () {
            const body = document.body;
            const storedTheme = localStorage.getItem('livelatch-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const initialTheme = storedTheme || (prefersDark ? 'dark' : 'light');

            function setTheme(theme) {
                body.setAttribute('data-ll-theme', theme);
                localStorage.setItem('livelatch-theme', theme);
            }

            setTheme(initialTheme);

            document.querySelectorAll('[data-theme-choice]').forEach(button => {
                button.addEventListener('click', () => setTheme(button.dataset.themeChoice));
            });

            document.querySelectorAll('[data-ll-open-sidebar]').forEach(button => {
                button.addEventListener('click', () => body.classList.add('ll-sidebar-open'));
            });

            document.querySelectorAll('[data-ll-close-sidebar]').forEach(button => {
                button.addEventListener('click', () => body.classList.remove('ll-sidebar-open'));
            });

            document.querySelectorAll('.ll-nav-link[href]').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1200) {
                        body.classList.remove('ll-sidebar-open');
                    }
                });
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
</body>
</html>
