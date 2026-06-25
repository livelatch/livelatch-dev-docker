@php
    $s           = $settings ?? [];
    $grassColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['grassColor'] ?? '') ? $s['grassColor'] : '#5bbf3a';
    $skyColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['skyColor']   ?? '') ? $s['skyColor']   : '#86c5ff';
    $stoneColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['stoneColor'] ?? '') ? $s['stoneColor'] : '#8a8d92';
    $textColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']  ?? '') ? $s['textColor']  : '#ffffff';
    $viewDistance = max(30, min(90,  (int) ($s['viewDistance'] ?? 60)));
    $glideSpeed   = max(0,  min(100, (int) ($s['glideSpeed']   ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Press Start 2P';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'VT323';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/minecraft/style.css'))   ? file_get_contents(public_path('assets/themes/minecraft/style.css'))   : null;
    $themeJs  = is_file(public_path('assets/themes/minecraft/minecraft.js')) ? file_get_contents(public_path('assets/themes/minecraft/minecraft.js')) : null;

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
  <style data-mc-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/minecraft/style.css') }}">
  @endif

  <style>
    :root {
      --mc-text: {{ $textColor }};
      --mc-sky:  {{ $skyColor }};
      --mc-stone: {{ $stoneColor }};
      --mc-heading-font: "{{ $headingFont }}", monospace;
      --mc-body-font:    "{{ $bodyFont }}", monospace;
    }
  </style>
  @if($customCss !== '')
  <style data-mc-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="mc-canvas" aria-hidden="true"></canvas>
  <div class="mc-vignette" aria-hidden="true"></div>

  <main class="mc-profile">
    <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="mc-avatar" width="96" height="96">
    <h1 class="mc-name">{{ $userName }}</h1>
    @if($userBio)
      <p class="mc-bio">{{ $userBio }}</p>
    @endif
    @if(count($links) > 0)
      <nav class="mc-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'mc-link'])
      </nav>
    @endif
  </main>

  <script>
    window.__mcOpts = {
      grassColor:   '{{ addslashes($grassColor) }}',
      skyColor:     '{{ addslashes($skyColor) }}',
      stoneColor:   '{{ addslashes($stoneColor) }}',
      viewDistance: {{ $viewDistance }},
      glideSpeed:   {{ $glideSpeed }},
    };
  </script>

  <script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/minecraft/minecraft.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.MinecraftTheme && window.THREE) { window.MinecraftTheme.init(window.__mcOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
