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
  $llPrimary = $llPreset['primary'] ?? '#2563eb';
  $llBackground = $llPreset['background'] ?? '#ffffff';
  $llText = $llPreset['text'] ?? '#111827';
  $llButtonRadius = $llPreset['buttonRadius'] ?? '8px';
  $llFontFamily = $llPreset['fontFamily'] ?? 'Inter';
@endphp
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Merriweather:wght@400;700&family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

  :root {
    --ll-primary: {{ $llPrimary }};
    --ll-background: {{ $llBackground }};
    --ll-text: {{ $llText }};
    --ll-button-radius: {{ $llButtonRadius }};
    --ll-font-family: "{{ $llFontFamily }}", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  }

  body {
    background-color: var(--ll-background) !important;
    color: var(--ll-text) !important;
    font-family: var(--ll-font-family) !important;
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
  }
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
