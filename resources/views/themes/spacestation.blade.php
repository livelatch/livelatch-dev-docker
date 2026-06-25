@php
    $s           = $settings ?? [];
    $planetColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['planetColor'] ?? '') ? $s['planetColor'] : '#3f7fd8';
    $atmoColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['atmoColor']   ?? '') ? $s['atmoColor']   : '#7fd0ff';
    $hullColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['hullColor']   ?? '') ? $s['hullColor']   : '#2a3038';
    $textColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#dfe9f5';
    $starDensity = max(0, min(100, (int) ($s['starDensity'] ?? 50)));
    $orbitSpeed  = max(0, min(100, (int) ($s['orbitSpeed']  ?? 50)));
    $spinSeconds = max(14, round(130 - $orbitSpeed * 1.1));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Orbitron';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Exo 2';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/spacestation/style.css'))      ? file_get_contents(public_path('assets/themes/spacestation/style.css'))      : null;
    $themeJs  = is_file(public_path('assets/themes/spacestation/spacestation.js')) ? file_get_contents(public_path('assets/themes/spacestation/spacestation.js')) : null;

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
  <style data-ss-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/spacestation/style.css') }}">
  @endif

  <style>
    :root {
      --ss-planet: {{ $planetColor }};
      --ss-atmo:   {{ $atmoColor }};
      --ss-hull:   {{ $hullColor }};
      --ss-text:   {{ $textColor }};
      --ss-spin:   {{ $spinSeconds }}s;
      --ss-heading-font: "{{ $headingFont }}", system-ui, sans-serif;
      --ss-body-font:    "{{ $bodyFont }}", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-ss-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="ss-stars" aria-hidden="true"></canvas>

  <main class="ss-shell">

    {{-- Viewport window looking out at the planet --}}
    <div class="ss-viewport" aria-hidden="true">
      <div class="ss-space">
        <div class="ss-planet">
          <div class="ss-planet-surface"></div>
          <div class="ss-planet-clouds"></div>
          <div class="ss-planet-shadow"></div>
        </div>
      </div>
      <div class="ss-glass"></div>
      <div class="ss-frame"></div>
      <div class="ss-readout ss-readout-tl">◎ ORBIT&nbsp;STABLE</div>
      <div class="ss-readout ss-readout-tr">O₂ 99.7%</div>
      <div class="ss-readout ss-readout-bl">ALT 412KM</div>
      <div class="ss-readout ss-readout-br">SYS&nbsp;NOMINAL ●</div>
    </div>

    {{-- Hull console with profile --}}
    <section class="ss-console">
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="ss-avatar" width="92" height="92">
      <h1 class="ss-name">{{ $userName }}</h1>
      @if($userBio)
        <p class="ss-bio">{{ $userBio }}</p>
      @endif
      @if(count($links) > 0)
        <nav class="ss-links" aria-label="Links">
          @include('themes.partials.links', ['links' => $links, 'linkClass' => 'ss-link'])
        </nav>
      @endif
    </section>

  </main>

  <script>
    window.__ssOpts = {
      starDensity: {{ $starDensity }},
      orbitSpeed:  {{ $orbitSpeed }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/spacestation/spacestation.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.SpaceStationTheme) { window.SpaceStationTheme.init(window.__ssOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
