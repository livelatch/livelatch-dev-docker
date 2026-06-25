@php
    $s            = $settings ?? [];
    $desktopColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['desktopColor'] ?? '') ? $s['desktopColor'] : '#008080';
    $accentColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['accentColor']  ?? '') ? $s['accentColor']  : '#000080';
    $windowColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['windowColor']  ?? '') ? $s['windowColor']  : '#c0c0c0';
    $textColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']    ?? '') ? $s['textColor']    : '#000000';
    $iconParade   = max(0, min(100, (int) ($s['iconParade'] ?? 60)));
    $starWipe     = max(0, min(100, (int) ($s['starWipe']   ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Pixelify Sans';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'VT323';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/win98/style.css')) ? file_get_contents(public_path('assets/themes/win98/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/win98/win98.js')) ? file_get_contents(public_path('assets/themes/win98/win98.js')) : null;

    $userName = e($user->name ?? '');
    $userBio  = e($user->littlelink_description ?? '');
    $handle   = $user->littlelink_name ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $userName }}</title>
  <meta name="description" content="{{ $userBio }}">

  <meta property="og:title"       content="{{ $userName }}">
  <meta property="og:description" content="{{ $userBio }}">
  <meta property="og:image"       content="{{ profilePreviewImageUrl($user->id) }}">
  @if($handle)<meta property="og:url" content="{{ url('/@' . $handle) }}">@endif
  <meta name="twitter:card"  content="summary">
  <meta name="twitter:title" content="{{ $userName }}">
  <meta name="twitter:image" content="{{ profilePreviewImageUrl($user->id) }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="{{ $googleFontsUrl }}" rel="stylesheet">
  @if($themeCss !== null)
  <style data-w9-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/win98/style.css') }}">
  @endif

  <style>
    :root {
      --w9-desktop: {{ $desktopColor }};
      --w9-accent:  {{ $accentColor }};
      --w9-window:  {{ $windowColor }};
      --w9-text:    {{ $textColor }};
      --w9-scan:    {{ $starWipe / 100 }};
      --w9-heading-font: "{{ $headingFont }}", "Tahoma", system-ui, sans-serif;
      --w9-body-font:    "{{ $bodyFont }}", "Tahoma", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-w9-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <div class="w9-desktop" id="w9-desktop">

    {{-- Desktop icons (decorative parade) --}}
    <div class="w9-icons" id="w9-icons" aria-hidden="true"></div>

    {{-- The "About Me" window --}}
    <section class="w9-window w9-profile-window" role="dialog" aria-label="{{ $userName }}">
      <div class="w9-titlebar">
        <span class="w9-title"><span class="w9-title-ico">▣</span> {{ $userName ?: 'My Profile' }}</span>
        <span class="w9-title-buttons">
          <button type="button" class="w9-tb-btn" aria-label="Minimize">_</button>
          <button type="button" class="w9-tb-btn" aria-label="Maximize">▢</button>
          <button type="button" class="w9-tb-btn w9-tb-close" aria-label="Close">✕</button>
        </span>
      </div>
      <div class="w9-window-body">
        <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="w9-avatar" width="92" height="92">
        <h1 class="w9-name">{{ $userName }}</h1>
        @if($userBio)
          <p class="w9-bio">{{ $userBio }}</p>
        @endif
        <p class="w9-hint">Click <strong>Start</strong> to open my links &#9660;</p>
      </div>
    </section>

  </div>

  {{-- Start menu (hidden until Start is clicked) --}}
  <div class="w9-startmenu" id="w9-startmenu" role="menu" aria-hidden="true">
    <div class="w9-startmenu-rail"><span>Livelatch&nbsp;98</span></div>
    <div class="w9-startmenu-items">
      @if(count($links) > 0)
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'w9-link'])
      @else
        <div class="w9-link w9-link-empty">No links yet</div>
      @endif
    </div>
  </div>

  {{-- Taskbar --}}
  <div class="w9-taskbar">
    <button type="button" class="w9-start" id="w9-start" aria-expanded="false" aria-controls="w9-startmenu">
      <span class="w9-start-logo" aria-hidden="true">
        <span></span><span></span><span></span><span></span>
      </span>
      Start
    </button>
    <div class="w9-tasks">
      <div class="w9-task w9-task-active">▣ {{ $userName ?: 'Profile' }}</div>
    </div>
    <div class="w9-tray">
      <span class="w9-tray-ico" aria-hidden="true">🔊</span>
      <span class="w9-clock" id="w9-clock">--:--</span>
    </div>
  </div>

  <script>
    window.__win98Opts = {
      iconParade: {{ $iconParade }},
      starWipe:   {{ $starWipe }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/win98/win98.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.Win98Theme) { window.Win98Theme.init(window.__win98Opts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
