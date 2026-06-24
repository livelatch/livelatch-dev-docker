@php
    $s             = $settings ?? [];
    $portalColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['portalColor']    ?? '') ? $s['portalColor']    : '#8b5cf6';
    $secondaryCol  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['secondaryColor'] ?? '') ? $s['secondaryColor'] : '#2a1a52';
    $accentColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['accentColor']    ?? '') ? $s['accentColor']    : '#0ce5de';
    $textColor     = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor']      ?? '') ? $s['textColor']      : '#ffffff';
    $particles     = max(100, min(3000, (int) ($s['particleCount']  ?? 800)));
    $speed         = max(0,   min(100,  (int) ($s['animationSpeed'] ?? 60)));

    // Fonts: allow letters/numbers/spaces only, fall back to Poppins.
    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Poppins';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Poppins';

    // Build a single Google Fonts request covering both chosen families.
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?'
        . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@400;500;600;700;800', $fontFamilies))
        . '&display=swap';

    // Pro custom CSS: strip angle brackets so it cannot break out of the <style> block.
    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';

    // Inline the theme's own CSS/JS so the page never depends on a separate
    // static fetch. Assets live at the project-root web dir (LinkStack rebinds
    // path.public to the project root), under assets/themes/<slug>/.
    // Falls back to <link>/<script> tags if the files can't be read from disk.
    $themeCss = is_file(public_path('assets/themes/portal/style.css')) ? file_get_contents(public_path('assets/themes/portal/style.css')) : null;
    $themeJs  = is_file(public_path('assets/themes/portal/portal.js'))  ? file_get_contents(public_path('assets/themes/portal/portal.js'))  : null;

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
  <style data-pt-base>{!! $themeCss !!}</style>
  @else
  <link rel="stylesheet" href="{{ asset('assets/themes/portal/style.css') }}">
  @endif

  <style>
    :root {
      --pt-portal:    {{ $portalColor }};
      --pt-secondary: {{ $secondaryCol }};
      --pt-accent:    {{ $accentColor }};
      --pt-text:      {{ $textColor }};
      --pt-heading-font: "{{ $headingFont }}", system-ui, sans-serif;
      --pt-body-font:    "{{ $bodyFont }}", system-ui, sans-serif;
    }
  </style>
  @if($customCss !== '')
  <style data-pt-custom-css>
{!! $customCss !!}
  </style>
  @endif

  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>

  <canvas id="portal-canvas" aria-hidden="true"></canvas>

  <main class="pt-profile" id="pt-profile">

    <img
      src="{{ profileImageUrl($user->id) }}"
      alt="{{ $userName }}"
      class="pt-avatar"
      id="pt-avatar"
      width="90"
      height="90"
    >

    <h1 class="pt-name" id="pt-name">{{ $userName }}</h1>

    @if($userBio)
      <p class="pt-bio" id="pt-bio">{{ $userBio }}</p>
    @endif

    @if(count($links) > 0)
      <nav class="pt-links" id="pt-links" aria-label="Links">
        @include('themes.partials.links', ['links' => $links, 'linkClass' => 'pt-link'])
      </nav>
    @endif

  </main>

  <script>
    window.__portalOpts = {
      portalColor:    '{{ addslashes($portalColor) }}',
      accentColor:    '{{ addslashes($accentColor) }}',
      particleCount:  {{ $particles }},
      animationSpeed: {{ $speed }},
    };
  </script>

  <script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  @if($themeJs !== null)
  <script>{!! $themeJs !!}</script>
  @else
  <script src="{{ asset('assets/themes/portal/portal.js') }}"></script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.PortalTheme && window.THREE) {
        window.PortalTheme.init(window.__portalOpts);
      }

      if (window.gsap) {
        var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        tl.fromTo('#pt-avatar',
              { opacity: 0, y: 24, scale: 0.9 },
              { opacity: 1, y: 0,  scale: 1,  duration: 0.9, delay: 0.5 })
          .fromTo('#pt-name',
              { opacity: 0, y: 16 },
              { opacity: 1, y: 0, duration: 0.6 }, '-=0.4')
          .fromTo('#pt-bio',
              { opacity: 0, y: 12 },
              { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
          .fromTo('.pt-link',
              { opacity: 0, y: 14 },
              { opacity: 1, y: 0, duration: 0.4, stagger: 0.07 }, '-=0.2');
      } else {
        document.querySelectorAll('#pt-avatar,#pt-name,#pt-bio,.pt-link')
          .forEach(function (el) { el.style.opacity = '1'; });
      }
    });
  </script>

  @stack('linkstack-body-end')
</body>
</html>
