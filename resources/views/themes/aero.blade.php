@php
    $s            = $settings ?? [];
    $buttonColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['buttonColor'] ?? '') ? $s['buttonColor'] : '#2b9fe6';
    $skyTop       = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['skyTop']      ?? '') ? $s['skyTop']      : '#6cc6ff';
    $skyBottom    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['skyBottom']   ?? '') ? $s['skyBottom']   : '#8ef0c0';
    $textColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#0b3a5b';
    $bubbleDensity = max(0, min(120, (int) ($s['bubbleDensity'] ?? 28)));
    $bubbleSpeed   = max(0, min(100, (int) ($s['bubbleSpeed']   ?? 50)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Nunito';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Nunito';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/aero/style.css')) ? file_get_contents(public_path('assets/themes/aero/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/aero/aero.js'))   ? file_get_contents(public_path('assets/themes/aero/aero.js'))   : null;

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
  <style data-ae-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/aero/style.css') }}">
  @endif

  <style>
    :root {
      --ae-button:     {{ $buttonColor }};
      --ae-sky-top:    {{ $skyTop }};
      --ae-sky-bottom: {{ $skyBottom }};
      --ae-text:       {{ $textColor }};
      --ae-heading-font: "{{ $headingFont }}", system-ui, sans-serif;
      --ae-body-font:    "{{ $bodyFont }}", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-ae-custom-css>
{!! $customCss !!}
  </style>
  @endif
</head>
<body>

  <canvas id="aero-canvas" aria-hidden="true"></canvas>

  <main class="ae-profile">
    <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="ae-avatar" width="104" height="104">

    <h1 class="ae-name">{{ $userName }}</h1>

    @if($userBio)
      <p class="ae-bio">{{ $userBio }}</p>
    @endif

    @if(count($links) > 0)
      <nav class="ae-links" aria-label="Links">
        @foreach($links as $i => $link)
          @if(!empty($link->link) && empty($link->custom_html))
            <a href="{{ $link->link }}" class="ae-link" style="--ae-i: {{ $i }}" rel="noopener noreferrer nofollow" target="_blank">{{ $link->title }}</a>
          @endif
        @endforeach
      </nav>
    @endif
  </main>

  <script>
    window.__aeroOpts = {
      bubbleDensity: {{ $bubbleDensity }},
      bubbleSpeed:   {{ $bubbleSpeed }},
    };
  </script>

  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/aero/aero.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.AeroTheme) { window.AeroTheme.init(window.__aeroOpts); }
    });
  </script>

</body>
</html>
