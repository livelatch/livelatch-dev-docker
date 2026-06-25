@php
    $s          = $settings ?? [];
    $runeColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['runeColor']  ?? '') ? $s['runeColor']  : '#9fd8ff';
    $stoneColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['stoneColor'] ?? '') ? $s['stoneColor'] : '#2a2e34';
    $emberColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['emberColor'] ?? '') ? $s['emberColor'] : '#ff8a3c';
    $textColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']  ?? '') ? $s['textColor']  : '#e6eef6';
    $runeDensity = max(0, min(100, (int) ($s['runeDensity'] ?? 50)));
    $emberRate   = max(0, min(100, (int) ($s['emberRate']   ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Cinzel';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Cinzel';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/skyrim/style.css')) ? file_get_contents(public_path('assets/themes/skyrim/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/skyrim/skyrim.js')) ? file_get_contents(public_path('assets/themes/skyrim/skyrim.js')) : null;

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
  <style data-sk-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/skyrim/style.css') }}">
  @endif

  <style>
    :root {
      --sk-rune:  {{ $runeColor }};
      --sk-stone: {{ $stoneColor }};
      --sk-ember: {{ $emberColor }};
      --sk-text:  {{ $textColor }};
      --sk-heading-font: "{{ $headingFont }}", 'Times New Roman', serif;
      --sk-body-font:    "{{ $bodyFont }}", 'Times New Roman', serif;
    }
  </style>
  @if($customCss !== '')
  <style data-sk-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="sk-canvas" aria-hidden="true"></canvas>

  <main class="sk-profile">
    <div class="sk-avatar-frame">
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="sk-avatar" width="110" height="110">
    </div>
    <h1 class="sk-name">{{ $userName }}</h1>
    @if($userBio)
      <p class="sk-bio">{{ $userBio }}</p>
    @endif
    @if(count($links) > 0)
      <nav class="sk-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'sk-link'])
      </nav>
    @endif
  </main>

  <script>
    window.__skOpts = {
      runeColor:   '{{ addslashes($runeColor) }}',
      emberColor:  '{{ addslashes($emberColor) }}',
      runeDensity: {{ $runeDensity }},
      emberRate:   {{ $emberRate }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/skyrim/skyrim.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.SkyrimTheme) { window.SkyrimTheme.init(window.__skOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
