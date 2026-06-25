@php
    $s          = $settings ?? [];
    $coreColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['coreColor'] ?? '') ? $s['coreColor'] : '#ffd9a0';
    $edgeColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['edgeColor'] ?? '') ? $s['edgeColor'] : '#5b6cff';
    $bgColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bgColor']   ?? '') ? $s['bgColor']   : '#05030f';
    $textColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor'] ?? '') ? $s['textColor'] : '#eaf0ff';
    $particleCount = max(1000, min(20000, (int) ($s['particleCount'] ?? 8000)));
    $spinSpeed     = max(0, min(100, (int) ($s['spinSpeed'] ?? 45)));
    $branches      = max(2, min(8, (int) ($s['branches'] ?? 4)));
    $glow          = max(0, min(100, (int) ($s['glow'] ?? 55)));

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Space Grotesk';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Sora';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/singularity/style.css'))        ? file_get_contents(public_path('assets/themes/singularity/style.css'))        : null;
    $themeJs  = is_file(public_path('assets/themes/singularity/singularity.js'))   ? file_get_contents(public_path('assets/themes/singularity/singularity.js'))   : null;

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
  <style data-sg-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/singularity/style.css') }}">
  @endif

  <style>
    :root {
      --sg-core: {{ $coreColor }};
      --sg-edge: {{ $edgeColor }};
      --sg-bg:   {{ $bgColor }};
      --sg-text: {{ $textColor }};
      --sg-heading-font: "{{ $headingFont }}", system-ui, sans-serif;
      --sg-body-font:    "{{ $bodyFont }}", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-sg-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="sg-canvas" aria-hidden="true"></canvas>
  <div class="sg-grain" aria-hidden="true"></div>
  <div class="sg-vignette" aria-hidden="true"></div>

  <div class="sg-cursor" id="sg-cursor" aria-hidden="true"></div>
  <div class="sg-cursor-ring" id="sg-cursor-ring" aria-hidden="true"></div>

  <main class="sg-stage" id="sg-stage">
    <section class="sg-card" id="sg-card">
      <div class="sg-avatar-wrap">
        <span class="sg-avatar-halo" aria-hidden="true"></span>
        <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="sg-avatar" width="120" height="120">
      </div>

      <h1 class="sg-name" data-text="{{ $userName }}">{{ $userName }}</h1>

      @if($userBio)
        <p class="sg-bio">{{ $userBio }}</p>
      @endif

      @if(count($links) > 0)
        <nav class="sg-links" id="sg-links" aria-label="Links">
          @include('themes.partials.links', ['links' => $links, 'linkClass' => 'sg-link'])
        </nav>
      @endif

      <p class="sg-hint" id="sg-hint"><span>◐</span> move to steer · click to warp</p>
    </section>
  </main>

  <script>
    window.__sgOpts = {
      coreColor:     '{{ addslashes($coreColor) }}',
      edgeColor:     '{{ addslashes($edgeColor) }}',
      bgColor:       '{{ addslashes($bgColor) }}',
      particleCount: {{ $particleCount }},
      spinSpeed:     {{ $spinSpeed }},
      branches:      {{ $branches }},
      glow:          {{ $glow }}
    };
  </script>

  <script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/singularity/singularity.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.SingularityTheme) { window.SingularityTheme.init(window.__sgOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
