@php
    $s           = $settings ?? [];
    $buttonColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['buttonColor'] ?? '') ? $s['buttonColor'] : '#ff8fc7';
    $accentColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['accentColor'] ?? '') ? $s['accentColor'] : '#b39ddb';
    $pawColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['pawColor']    ?? '') ? $s['pawColor']    : '#ffffff';
    $textColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#7b3f63';
    $pawDensity  = max(0, min(80,  (int) ($s['pawDensity'] ?? 18)));
    $driftSpeed  = max(0, min(100, (int) ($s['driftSpeed'] ?? 45)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Mochiy Pop One';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Kosugi Maru';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/cute/style.css')) ? file_get_contents(public_path('assets/themes/cute/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/cute/cute.js'))   ? file_get_contents(public_path('assets/themes/cute/cute.js'))   : null;

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
  <style data-cu-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/cute/style.css') }}">
  @endif

  <style>
    :root {
      --cu-primary: {{ $buttonColor }};
      --cu-accent:  {{ $accentColor }};
      --cu-paw:     {{ $pawColor }};
      --cu-text:    {{ $textColor }};
      --cu-heading-font: "{{ $headingFont }}", system-ui, sans-serif;
      --cu-body-font:    "{{ $bodyFont }}", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-cu-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="cute-canvas" aria-hidden="true"></canvas>

  <main class="cu-profile">
    <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="cu-avatar" width="110" height="110">

    <h1 class="cu-name">{{ $userName }}</h1>

    @if($userBio)
      <p class="cu-bio">{{ $userBio }}</p>
    @endif

    @if(count($links) > 0)
      <nav class="cu-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'cu-link'])
      </nav>
    @endif
  </main>

  <script>
    window.__cuteOpts = {
      pawColor:   '{{ addslashes($pawColor) }}',
      pawDensity: {{ $pawDensity }},
      driftSpeed: {{ $driftSpeed }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/cute/cute.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.CuteTheme) { window.CuteTheme.init(window.__cuteOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
