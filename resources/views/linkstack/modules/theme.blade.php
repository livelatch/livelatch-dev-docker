@php
// Theme Config
if (!function_exists('theme')) {
  function theme($key){
$key = trim($key);
$file = base_path('themes/' . $GLOBALS['themeName'] . '/config.php');
  if (file_exists($file)) {
    $config = include $file;
  if (isset($config[$key])) {
    return $config[$key];
}}
return null;}
}

// Theme Custom Asset
if (!function_exists('themeAsset')) {
function themeAsset($path){
$path = url('themes/' . $GLOBALS['themeName'] . '/extra/custom-assets/' . $path);
return $path;}
}

$customBackgroundExists = false;
@endphp

@foreach($information as $info) @php $GLOBALS['themeName'] = $info->theme; @endphp @endforeach

@if(theme('allow_custom_background') != "false")
@php
$customBackgroundFile = findBackground($userinfo->id);
$customBackgroundPath = base_path('assets/img/background-img/'.$customBackgroundFile);
$customBackgroundURL = url('assets/img/background-img/'.$customBackgroundFile);
$customBackgroundExists = file_exists($customBackgroundPath)
@endphp

@if($customBackgroundExists == true)
<style>
  body {
    background-image: url('{{$customBackgroundURL}}') !important;
    background-size: cover !important;
    background-attachment: fixed !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
  }
</style>
@endif
@endif

@push('linkstack-head-end')
@if(theme('enable_custom_code') == "true" and theme('enable_custom_head') == "true" and env('ALLOW_CUSTOM_CODE_IN_THEMES') == 'true')@include($GLOBALS['themeName'] . '.extra.custom-head')@endif
@if($info->theme != '' and $info->theme != 'default')

  <!-- LinkStack Theme: "{{$info->theme}}" -->

  <!-- Theme details: -->
  <meta name="designer" href="{{ url('') . "/theme/@" . $littlelink_name}}" content="{{ url('') . "/theme/@" . $littlelink_name}}">

  <link rel="stylesheet" href="themes/{{$info->theme}}/share.button.css">
  @if(theme('use_default_buttons') == "true")
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/brands.css') }}">
  @else
  <link rel="stylesheet" href="themes/{{$info->theme}}/brands.css">
  @endif
  <link rel="stylesheet" href="themes/{{$info->theme}}/skeleton-auto.css">
@if(file_exists(base_path('themes/' . $info->theme . '/animations.css')))
  <link rel="stylesheet" href="<?php echo asset('themes/' . $info->theme . '/animations.css') ?>">
@else
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/animations.css') }}">
@endif

@else
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/share.button.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/animations.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/brands.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/linkstack/css/skeleton-auto.css') }}">
@endif
@php
  $llPreset = $publicThemePreset['preset'] ?? [];
  $llThemeSlug = $publicThemePreset['theme_slug'] ?? 'livelatch-default';
  $llPrimary = $llPreset['primary'] ?? '#2563eb';
  $llBackground = $llPreset['background'] ?? '#ffffff';
  $llText = $llPreset['text'] ?? '#111827';
  $llButtonRadius = $llPreset['buttonRadius'] ?? '8px';
  $llFontFamily = $llPreset['fontFamily'] ?? 'Inter';
  $llFontFamilySafe = preg_replace('/[^A-Za-z0-9 ]/', '', $llFontFamily) ?: 'Inter';
  $llFontFamilyQuery = str_replace(' ', '+', $llFontFamilySafe);
  $llEffectIntensity = max(0, min(100, (int) ($llPreset['effectIntensity'] ?? 55)));
  $llShapeIntensity = max(0, min(100, (int) ($llPreset['shapeIntensity'] ?? 45)));
@endphp
<style>
  @import url('https://fonts.googleapis.com/css2?family={{ $llFontFamilyQuery }}:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Merriweather:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Press+Start+2P:wght@400&family=Rajdhani:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Roboto+Mono:wght@400;500;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

  :root {
    --ll-primary: {{ $llPrimary }};
    --ll-background: {{ $llBackground }};
    --ll-text: {{ $llText }};
    --ll-button-radius: {{ $llButtonRadius }};
    --ll-font-family: "{{ $llFontFamilySafe }}", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    --ll-effect-intensity: {{ $llEffectIntensity / 100 }};
    --ll-shape-intensity: {{ $llShapeIntensity / 100 }};
  }

  html,
  body {
    min-height: 100%;
  }

  body {
    background-color: var(--ll-background) !important;
    color: var(--ll-text) !important;
    font-family: var(--ll-font-family) !important;
    overflow-x: hidden;
    position: relative;
  }

  body::before,
  body::after {
    content: "";
    position: fixed;
    inset: 0;
    pointer-events: none;
  }

  body::before {
    z-index: -2;
  }

  body::after {
    z-index: -1;
    opacity: calc(0.12 + (var(--ll-effect-intensity) * 0.24));
  }

  .container {
    word-break: break-word;
    font-family: var(--ll-font-family) !important;
  }

  .container,
  .container h1,
  .container h2,
  .container h3,
  .container p,
  .container .dynamic-contrast {
    color: var(--ll-text) !important;
  }

  .container .button {
    background-color: var(--ll-primary) !important;
    border-color: var(--ll-primary) !important;
    border-radius: var(--ll-button-radius) !important;
    color: #ffffff !important;
    font-family: var(--ll-font-family) !important;
    box-shadow: 0 14px 34px color-mix(in srgb, var(--ll-primary) 24%, transparent) !important;
    transform: translateZ(0);
  }

  @keyframes ll-theme-drift {
    0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
    50% { transform: translate3d(18px, -14px, 0) rotate(2deg); }
  }

  @keyframes ll-theme-scan {
    0% { transform: translateY(-12%); }
    100% { transform: translateY(12%); }
  }

  @switch($llThemeSlug)
    @case('australia')
      body::before {
        background:
          radial-gradient(circle at 18% 18%, color-mix(in srgb, #f97316 36%, transparent), transparent 28%),
          radial-gradient(circle at 86% 18%, color-mix(in srgb, #22d3ee 28%, transparent), transparent 30%),
          linear-gradient(160deg, var(--ll-background), color-mix(in srgb, var(--ll-primary) 18%, var(--ll-background)));
      }
      body::after {
        background-image: repeating-linear-gradient(120deg, transparent 0 18px, color-mix(in srgb, var(--ll-primary) 12%, transparent) 18px 20px);
        animation: ll-theme-drift calc(18s - (var(--ll-effect-intensity) * 8s)) ease-in-out infinite;
      }
      @break
    @case('minecraft')
      body::before {
        background:
          linear-gradient(90deg, color-mix(in srgb, var(--ll-primary) 18%, transparent) 1px, transparent 1px),
          linear-gradient(color-mix(in srgb, var(--ll-primary) 18%, transparent) 1px, transparent 1px),
          var(--ll-background);
        background-size: calc(28px - (var(--ll-shape-intensity) * 10px)) calc(28px - (var(--ll-shape-intensity) * 10px));
      }
      .container .button {
        box-shadow: 5px 5px 0 color-mix(in srgb, var(--ll-text) 38%, transparent) !important;
      }
      @break
    @case('anime')
      body::before {
        background:
          radial-gradient(circle at 16% 22%, color-mix(in srgb, var(--ll-primary) 26%, transparent), transparent 24%),
          radial-gradient(circle at 80% 12%, color-mix(in srgb, #60a5fa 24%, transparent), transparent 22%),
          linear-gradient(135deg, var(--ll-background), color-mix(in srgb, var(--ll-primary) 10%, var(--ll-background)));
      }
      body::after {
        background-image: radial-gradient(circle, color-mix(in srgb, var(--ll-primary) 24%, transparent) 1px, transparent 2px);
        background-size: 26px 26px;
        animation: ll-theme-drift 14s ease-in-out infinite;
      }
      @break
    @case('cars')
      body::before {
        background:
          repeating-linear-gradient(115deg, transparent 0 28px, color-mix(in srgb, var(--ll-primary) 16%, transparent) 28px 34px),
          radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--ll-primary) 24%, transparent), transparent 42%),
          var(--ll-background);
      }
      body::after {
        background: linear-gradient(90deg, transparent, color-mix(in srgb, #ffffff 16%, transparent), transparent);
        animation: ll-theme-scan calc(7s - (var(--ll-effect-intensity) * 3s)) linear infinite alternate;
      }
      @break
    @case('horses')
      body::before {
        background:
          radial-gradient(ellipse at 50% 110%, color-mix(in srgb, #16a34a 28%, transparent), transparent 46%),
          repeating-linear-gradient(25deg, color-mix(in srgb, var(--ll-primary) 9%, transparent) 0 2px, transparent 2px 18px),
          var(--ll-background);
      }
      @break
    @case('bliss')
      body::before {
        background:
          radial-gradient(ellipse at 50% 105%, color-mix(in srgb, #22c55e 46%, transparent), transparent 38%),
          radial-gradient(circle at 72% 18%, rgba(255,255,255,.72), transparent 12%),
          linear-gradient(180deg, color-mix(in srgb, #38bdf8 28%, var(--ll-background)), var(--ll-background));
      }
      body::after {
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.55), transparent 8%), radial-gradient(circle at 70% 30%, rgba(255,255,255,.42), transparent 10%);
        animation: ll-theme-drift 22s ease-in-out infinite;
      }
      @break
    @case('heavy-metal')
      body::before {
        background:
          radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--ll-primary) 34%, transparent), transparent 34%),
          repeating-linear-gradient(100deg, transparent 0 18px, rgba(255,255,255,.035) 18px 20px),
          var(--ll-background);
      }
      .container .button {
        text-transform: uppercase;
        letter-spacing: .04em;
        border: 1px solid color-mix(in srgb, var(--ll-primary) 70%, #fff) !important;
      }
      @break
    @case('cyberpunk')
      body::before {
        background:
          linear-gradient(90deg, color-mix(in srgb, var(--ll-primary) 18%, transparent) 1px, transparent 1px),
          linear-gradient(color-mix(in srgb, #f0abfc 14%, transparent) 1px, transparent 1px),
          radial-gradient(circle at 82% 20%, color-mix(in srgb, #f0abfc 28%, transparent), transparent 26%),
          var(--ll-background);
        background-size: 42px 42px, 42px 42px, auto, auto;
      }
      body::after {
        background-image: repeating-linear-gradient(0deg, transparent 0 8px, rgba(255,255,255,.07) 8px 9px);
        animation: ll-theme-scan 5s linear infinite alternate;
      }
      @break
    @case('windows-95')
      body::before {
        background:
          linear-gradient(45deg, rgba(255,255,255,.18) 25%, transparent 25% 50%, rgba(0,0,0,.08) 50% 75%, transparent 75%),
          var(--ll-background);
        background-size: 18px 18px;
      }
      .container .button {
        border-radius: 0 !important;
        border: 2px solid !important;
        border-color: #ffffff #404040 #404040 #ffffff !important;
        box-shadow: none !important;
      }
      @break
    @case('macos')
      body::before {
        background:
          radial-gradient(circle at 24% 18%, color-mix(in srgb, #60a5fa 34%, transparent), transparent 28%),
          radial-gradient(circle at 78% 18%, color-mix(in srgb, #f472b6 30%, transparent), transparent 28%),
          linear-gradient(135deg, var(--ll-background), color-mix(in srgb, var(--ll-primary) 10%, var(--ll-background)));
      }
      .container .button {
        backdrop-filter: blur(18px);
        background-color: color-mix(in srgb, var(--ll-primary) 86%, rgba(255,255,255,.36)) !important;
      }
      @break
    @default
      body::before {
        background:
          radial-gradient(circle at 16% 12%, color-mix(in srgb, var(--ll-primary) 18%, transparent), transparent 28%),
          linear-gradient(135deg, var(--ll-background), color-mix(in srgb, var(--ll-primary) 8%, var(--ll-background)));
      }
  @endswitch
</style>
@endpush

@push('linkstack-body-start')
@if(theme('enable_custom_code') == "true" and theme('enable_custom_body') == "true" and env('ALLOW_CUSTOM_CODE_IN_THEMES') == 'true')@include($GLOBALS['themeName'] . '.extra.custom-body')@endif

@if($info->theme != '' and $info->theme != 'default')
    <!-- Enables parallax background animations -->
    <div class="background-container">
    <section class="parallax-background">
      <div id="object1" class="object1"></div>
      <div id="object2" class="object2"></div>
      <div id="object3" class="object3"></div>
      <div id="object4" class="object4"></div>
      <div id="object5" class="object5"></div>
      <div id="object6" class="object6"></div>
      <div id="object7" class="object7"></div>
      <div id="object8" class="object8"></div>
      <div id="object9" class="object9"></div>
      <div id="object10" class="object10"></div>
      <div id="object11" class="object11"></div>
      <div id="object12" class="object12"></div>
    </section>
    </div>
    <!-- End of parallax background animations -->
@endif
@endpush

@push('linkstack-body-end')
@if(theme('enable_custom_code') == "true" and theme('enable_custom_body_end') == "true" and env('ALLOW_CUSTOM_CODE_IN_THEMES') == 'true')@include($GLOBALS['themeName'] . '.extra.custom-body-end')@endif
@endpush
@include('linkstack.modules.dynamic-contrast')
