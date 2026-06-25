@php
    $s          = $settings ?? [];
    $pinkColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['pinkColor'] ?? '') ? $s['pinkColor'] : '#ff2e8b';
    $cyanColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['cyanColor'] ?? '') ? $s['cyanColor'] : '#23e0e0';
    $sunColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['sunColor']  ?? '') ? $s['sunColor']  : '#ffd23c';
    $textColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor'] ?? '') ? $s['textColor'] : '#ffffff';
    $sunGlow    = max(0, min(100, (int) ($s['sunGlow']  ?? 50)));
    $palmSway   = max(0, min(100, (int) ($s['palmSway'] ?? 50)));
    $swaySeconds = round(9 - ($palmSway / 100) * 6, 1); // 0 -> 9s, 100 -> 3s

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Pricedown';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Oswald';
    // Pricedown is not a Google font (loaded via @font-face in the theme CSS),
    // so keep it out of the Google request or the whole CSS2 call 400s. Always
    // include Anton as the live fallback.
    $googleFamilies = array_values(array_unique(array_filter([$headingFont, $bodyFont, 'Anton'], fn ($f) => $f !== 'Pricedown')));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $googleFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/vice/style.css')) ? file_get_contents(public_path('assets/themes/vice/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/vice/vice.js'))   ? file_get_contents(public_path('assets/themes/vice/vice.js'))   : null;

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
  <style data-vc-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/vice/style.css') }}">
  @endif

  <style>
    :root {
      --vc-pink: {{ $pinkColor }};
      --vc-cyan: {{ $cyanColor }};
      --vc-sun:  {{ $sunColor }};
      --vc-text: {{ $textColor }};
      --vc-sway: {{ $swaySeconds }}s;
      --vc-heading-font: "{{ $headingFont }}", "Pricedown", "Anton", sans-serif;
      --vc-body-font:    "{{ $bodyFont }}", "Anton", "Oswald", sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-vc-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="vc-canvas" aria-hidden="true"></canvas>

  {{-- Palm silhouettes --}}
  <div class="vc-palm vc-palm-left" aria-hidden="true">
    <svg viewBox="0 0 120 260" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax meet">
      <path d="M58 260 C56 180 54 120 60 86 C62 96 64 110 70 120 C66 104 64 92 66 82 L60 80 L54 84 C56 94 54 106 50 120 C56 108 56 96 58 86 C50 118 50 180 50 260 Z" fill="#0a0a14"/>
      <g fill="#0a0a14">
        <path d="M60 80 C40 64 22 60 4 66 C24 60 44 66 60 80 Z"/>
        <path d="M60 80 C44 56 24 46 4 44 C28 48 48 60 60 80 Z"/>
        <path d="M60 80 C80 64 98 60 116 66 C96 60 76 66 60 80 Z"/>
        <path d="M60 80 C76 56 96 46 116 44 C92 48 72 60 60 80 Z"/>
        <path d="M60 80 C58 56 52 36 40 22 C54 38 60 58 60 80 Z"/>
        <path d="M60 80 C62 56 70 36 84 24 C68 40 62 58 60 80 Z"/>
      </g>
    </svg>
  </div>
  <div class="vc-palm vc-palm-right" aria-hidden="true">
    <svg viewBox="0 0 120 260" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax meet">
      <path d="M58 260 C56 180 54 120 60 86 C62 96 64 110 70 120 C66 104 64 92 66 82 L60 80 L54 84 C56 94 54 106 50 120 C56 108 56 96 58 86 C50 118 50 180 50 260 Z" fill="#0a0a14"/>
      <g fill="#0a0a14">
        <path d="M60 80 C40 64 22 60 4 66 C24 60 44 66 60 80 Z"/>
        <path d="M60 80 C44 56 24 46 4 44 C28 48 48 60 60 80 Z"/>
        <path d="M60 80 C80 64 98 60 116 66 C96 60 76 66 60 80 Z"/>
        <path d="M60 80 C76 56 96 46 116 44 C92 48 72 60 60 80 Z"/>
        <path d="M60 80 C58 56 52 36 40 22 C54 38 60 58 60 80 Z"/>
        <path d="M60 80 C62 56 70 36 84 24 C68 40 62 58 60 80 Z"/>
      </g>
    </svg>
  </div>

  <main class="vc-profile">
    <div class="vc-avatar-wrap">
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="vc-avatar" width="110" height="110">
    </div>
    <h1 class="vc-name">{{ $userName }}</h1>
    @if($userBio)
      <p class="vc-bio">{{ $userBio }}</p>
    @endif
    @if(count($links) > 0)
      <nav class="vc-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'vc-link'])
      </nav>
    @endif
  </main>

  <script>
    window.__vcOpts = {
      pinkColor: '{{ addslashes($pinkColor) }}',
      cyanColor: '{{ addslashes($cyanColor) }}',
      sunColor:  '{{ addslashes($sunColor) }}',
      sunGlow:   {{ $sunGlow }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/vice/vice.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.ViceTheme) { window.ViceTheme.init(window.__vcOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
