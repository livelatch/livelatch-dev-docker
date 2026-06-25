@php
    $s          = $settings ?? [];
    $lineColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['lineColor']   ?? '') ? $s['lineColor']   : '#bcd8ff';
    $bgColor    = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bgColor']     ?? '') ? $s['bgColor']     : '#0e3a6b';
    $accentColor= preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['accentColor'] ?? '') ? $s['accentColor'] : '#ffcf6b';
    $textColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']   ?? '') ? $s['textColor']   : '#eaf2ff';
    $gridDensity = max(0, min(100, (int) ($s['gridDensity'] ?? 50)));
    $drawSpeed   = max(0, min(100, (int) ($s['drawSpeed']   ?? 50)));
    $gridCell    = (int) round(42 - ($gridDensity / 100) * 26); // 42px → 16px

    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Chakra Petch';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Share Tech Mono';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies))
        . '&display=swap';

    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    $themeCss = is_file(public_path('assets/themes/blueprint/style.css'))     ? file_get_contents(public_path('assets/themes/blueprint/style.css'))     : null;
    $themeJs  = is_file(public_path('assets/themes/blueprint/blueprint.js'))  ? file_get_contents(public_path('assets/themes/blueprint/blueprint.js'))  : null;

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
  <style data-bp-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/blueprint/style.css') }}">
  @endif

  <style>
    :root {
      --bp-line:   {{ $lineColor }};
      --bp-bg:     {{ $bgColor }};
      --bp-accent: {{ $accentColor }};
      --bp-text:   {{ $textColor }};
      --bp-grid:   {{ $gridCell }}px;
      --bp-heading-font: "{{ $headingFont }}", monospace;
      --bp-body-font:    "{{ $bodyFont }}", monospace;
    }
  </style>
  @if($customCss !== '')
  <style data-bp-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <div class="bp-paper" aria-hidden="true"></div>
  <div class="bp-corner bp-corner-tl" aria-hidden="true"></div>
  <div class="bp-corner bp-corner-tr" aria-hidden="true"></div>
  <div class="bp-corner bp-corner-bl" aria-hidden="true"></div>
  <div class="bp-corner bp-corner-br" aria-hidden="true"></div>

  <main class="bp-profile">

    <div class="bp-avatar-frame">
      <svg class="bp-avatar-svg" viewBox="0 0 220 220" fill="none" aria-hidden="true">
        <circle class="bp-draw" cx="110" cy="110" r="86" stroke="var(--bp-line)" stroke-width="1"/>
        <circle class="bp-draw bp-thin" cx="110" cy="110" r="100" stroke="var(--bp-line)" stroke-width="0.6" stroke-dasharray="3 4"/>
        <line class="bp-draw" x1="110" y1="2" x2="110" y2="218" stroke="var(--bp-line)" stroke-width="0.6"/>
        <line class="bp-draw" x1="2" y1="110" x2="218" y2="110" stroke="var(--bp-line)" stroke-width="0.6"/>
        {{-- diameter dimension line + arrowheads --}}
        <line class="bp-draw bp-accent-stroke" x1="24" y1="196" x2="196" y2="196" stroke="var(--bp-accent)" stroke-width="0.9"/>
        <path class="bp-draw bp-accent-stroke" d="M24 196 l7 -3 v6 z" fill="var(--bp-accent)" stroke="none"/>
        <path class="bp-draw bp-accent-stroke" d="M196 196 l-7 -3 v6 z" fill="var(--bp-accent)" stroke="none"/>
      </svg>
      <span class="bp-dim-label">&#216;104</span>
      <img src="{{ profileImageUrl($user->id) }}" alt="{{ $userName }}" class="bp-avatar" width="132" height="132">
      <span class="bp-tick bp-tick-1" aria-hidden="true"></span>
      <span class="bp-tick bp-tick-2" aria-hidden="true"></span>
    </div>

    <h1 class="bp-name">{{ $userName }}</h1>
    <div class="bp-rule" aria-hidden="true"></div>

    @if($userBio)
      <p class="bp-bio"><span class="bp-note">// NOTE:</span> {{ $userBio }}</p>
    @endif

    @if(count($links) > 0)
      <nav class="bp-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'bp-link'])
      </nav>
    @endif
  </main>

  {{-- Engineering drawing title block --}}
  <aside class="bp-titleblock" aria-hidden="true">
    <div class="bp-tb-title">
      <span>TITLE</span>
      <strong>{{ $userName ?: 'PROFILE' }}</strong>
    </div>
    <div class="bp-tb-grid">
      <div><span>DRAWN</span><strong>{{ $handle ? '@'.e($handle) : 'LIVELATCH' }}</strong></div>
      <div><span>SCALE</span><strong>1:1</strong></div>
      <div><span>SHEET</span><strong>01 / 01</strong></div>
      <div><span>REV</span><strong>A</strong></div>
    </div>
  </aside>

  <script>
    window.__bpOpts = { drawSpeed: {{ $drawSpeed }} };
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/blueprint/blueprint.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.BlueprintTheme) { window.BlueprintTheme.init(window.__bpOpts); }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
