@php
    $s           = $settings ?? [];
    $hudColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['hudColor']    ?? '') ? $s['hudColor']    : '#37d6ff';
    $accentColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['accentColor'] ?? '') ? $s['accentColor'] : '#ffcc44';
    $bgColor     = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bgColor']     ?? '') ? $s['bgColor']     : '#03080f';
    $textColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#dffaff';
    $ringSpeed   = max(0, min(100, (int) ($s['ringSpeed'] ?? 50)));
    $scanRate    = max(0, min(100, (int) ($s['scanRate']  ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Orbitron';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Share Tech Mono';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/jarvis/style.css'))  ? file_get_contents(public_path('assets/themes/jarvis/style.css'))  : null;
    $themeJs  = is_file(public_path('assets/themes/jarvis/jarvis.js'))  ? file_get_contents(public_path('assets/themes/jarvis/jarvis.js'))  : null;

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
  <style data-jv-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/jarvis/style.css') }}">
  @endif

  <style>
    :root {
      --jv-hud:    {{ $hudColor }};
      --jv-accent: {{ $accentColor }};
      --jv-bg:     {{ $bgColor }};
      --jv-text:   {{ $textColor }};
      --jv-heading-font: "{{ $headingFont }}", monospace;
      --jv-body-font:    "{{ $bodyFont }}", monospace;
    }
  </style>
  @if($customCss !== '')
  <style data-jv-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="jv-canvas" aria-hidden="true"></canvas>
  <div class="jv-grid" aria-hidden="true"></div>

  <main class="jv-profile">
    <div class="jv-reactor">
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="jv-avatar" width="120" height="120">
    </div>
    <h1 class="jv-name">{{ $userName }}</h1>
    <p class="jv-status"><span class="jv-dot"></span> J.A.R.V.I.S. ONLINE</p>
    @if($userBio)
      <p class="jv-bio">{{ $userBio }}</p>
    @endif
    @if(count($links) > 0)
      <nav class="jv-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'jv-link'])
      </nav>
    @endif
  </main>

  <script>
    window.__jvOpts = {
      hudColor:    '{{ addslashes($hudColor) }}',
      accentColor: '{{ addslashes($accentColor) }}',
      ringSpeed:   {{ $ringSpeed }},
      scanRate:    {{ $scanRate }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/jarvis/jarvis.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.JarvisTheme) { window.JarvisTheme.init(window.__jvOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
