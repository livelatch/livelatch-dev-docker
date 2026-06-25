@php
    $s           = $settings ?? [];
    $rainColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['rainColor']   ?? '') ? $s['rainColor']   : '#00ff66';
    $promptColor = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['promptColor'] ?? '') ? $s['promptColor'] : '#7dff7d';
    $bgColor     = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bgColor']     ?? '') ? $s['bgColor']     : '#020802';
    $textColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#c8ffcf';
    $rainDensity = max(0, min(100, (int) ($s['rainDensity'] ?? 50)));
    $rainSpeed   = max(0, min(100, (int) ($s['rainSpeed']   ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'VT323';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Share Tech Mono';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/console/style.css'))  ? file_get_contents(public_path('assets/themes/console/style.css'))  : null;
    $themeJs  = is_file(public_path('assets/themes/console/console.js')) ? file_get_contents(public_path('assets/themes/console/console.js')) : null;

    $userName = e($user->name ?? '');
    $userBio  = e($user->littlelink_description ?? '');
    $handle   = $user->littlelink_name ?? '';
    $promptHost = $handle ? e($handle) : 'guest';
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
  <style data-cs-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/console/style.css') }}">
  @endif

  <style>
    :root {
      --cs-rain:   {{ $rainColor }};
      --cs-prompt: {{ $promptColor }};
      --cs-bg:     {{ $bgColor }};
      --cs-text:   {{ $textColor }};
      --cs-heading-font: "{{ $headingFont }}", monospace;
      --cs-body-font:    "{{ $bodyFont }}", monospace;
    }
  </style>
  @if($customCss !== '')
  <style data-cs-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="cs-rain" aria-hidden="true"></canvas>
  <div class="cs-scan" aria-hidden="true"></div>

  <main class="cs-terminal">
    <div class="cs-titlebar">
      <span class="cs-dots"><i></i><i></i><i></i></span>
      <span class="cs-titletext">{{ $promptHost }}@matrix: ~/profile</span>
    </div>

    <div class="cs-body">
      <p class="cs-line cs-boot">[ OK ] establishing secure uplink&hellip;</p>
      <p class="cs-line cs-boot">[ OK ] decrypting identity matrix&hellip;</p>

      <div class="cs-id">
        <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="cs-avatar" width="84" height="84">
        <div>
          <p class="cs-prompt-line"><span class="cs-prompt">{{ $promptHost }}@matrix:~$</span> whoami</p>
          <h1 class="cs-name" data-type="{{ $userName }}">{{ $userName }}<span class="cs-cursor">_</span></h1>
        </div>
      </div>

      @if($userBio)
        <p class="cs-prompt-line"><span class="cs-prompt">{{ $promptHost }}@matrix:~$</span> cat bio.txt</p>
        <p class="cs-bio">{{ $userBio }}</p>
      @endif

      @if(count($links) > 0)
        <p class="cs-prompt-line"><span class="cs-prompt">{{ $promptHost }}@matrix:~$</span> ls ./links</p>
        <nav class="cs-links" aria-label="Links">
          @include('themes.partials.links', ['links' => $links, 'linkClass' => 'cs-link'])
        </nav>
      @endif

      <p class="cs-prompt-line cs-blink-line"><span class="cs-prompt">{{ $promptHost }}@matrix:~$</span> <span class="cs-cursor">_</span></p>
    </div>
  </main>

  <script>
    window.__csOpts = {
      rainColor:   '{{ addslashes($rainColor) }}',
      rainDensity: {{ $rainDensity }},
      rainSpeed:   {{ $rainSpeed }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/console/console.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.ConsoleTheme) { window.ConsoleTheme.init(window.__csOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
