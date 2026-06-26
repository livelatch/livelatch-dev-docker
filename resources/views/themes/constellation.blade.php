@php
    $s = $settings ?? [];
    $aColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['aColor']    ?? '') ? $s['aColor']    : '#cfe0ff';
    $bColor   = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bColor']    ?? '') ? $s['bColor']    : '#5b8cff';
    $bgColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['bgColor']   ?? '') ? $s['bgColor']   : '#03050f';
    $textColor= preg_match('/^#[0-9a-fA-F]{3,8}$/', $s['textColor'] ?? '') ? $s['textColor'] : '#eaf2ff';
    $intensity = max(0, min(100, (int) ($s['intensity'] ?? 50)));
    $speed     = max(0, min(100, (int) ($s['speed'] ?? 50)));
    $headingFont = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['headingFont'] ?? '') ? $s['headingFont'] : 'Space Grotesk';
    $bodyFont    = preg_match('/^[A-Za-z0-9 ]{2,40}$/', $s['bodyFont']    ?? '') ? $s['bodyFont']    : 'Sora';
    $fontFamilies = array_values(array_unique([$headingFont, $bodyFont]));
    $googleFontsUrl = 'https://fonts.googleapis.com/css2?' . implode('&', array_map(fn ($f) => 'family=' . str_replace(' ', '+', $f), $fontFamilies)) . '&display=swap';
    $customCss = isset($s['customCss']) ? str_replace(['<', '>'], '', (string) $s['customCss']) : '';
    $glassCss   = is_file(public_path('assets/themes/shared/glass.css'))               ? file_get_contents(public_path('assets/themes/shared/glass.css'))               : null;
    $interactJs = is_file(public_path('assets/themes/shared/interact.js'))             ? file_get_contents(public_path('assets/themes/shared/interact.js'))             : null;
    $themeJs    = is_file(public_path('assets/themes/constellation/constellation.js')) ? file_get_contents(public_path('assets/themes/constellation/constellation.js')) : null;
    $userName = e($user->name ?? ''); $userBio = e($user->littlelink_description ?? ''); $handle = $user->littlelink_name ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $userName }}</title><meta name="description" content="{{ $userBio }}">
  <meta property="og:title" content="{{ $userName }}"><meta property="og:image" content="{{ profilePreviewImageUrl($user->id) }}">
  @if($handle)<meta property="og:url" content="{{ url('/@' . $handle) }}">@endif
  <meta name="twitter:card" content="summary"><meta name="twitter:image" content="{{ profilePreviewImageUrl($user->id) }}">
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="{{ $googleFontsUrl }}" rel="stylesheet">
  @if($glassCss !== null)<style data-llx-glass>{!! $glassCss !!}</style>@else<link rel="stylesheet" href="{{ asset('assets/themes/shared/glass.css') }}">@endif
  <style>:root{ --llx-a: {{ $aColor }}; --llx-b: {{ $bColor }}; --llx-bg: {{ $bgColor }}; --llx-text: {{ $textColor }}; --llx-heading-font: "{{ $headingFont }}", system-ui, sans-serif; --llx-body-font: "{{ $bodyFont }}", system-ui, sans-serif; }</style>
  @if($customCss !== '')<style data-llx-custom>{!! $customCss !!}</style>@endif
  @include('linkstack.modules.block-libraries', ['links' => $links])
  @stack('linkstack-head')
</head>
<body>
  <canvas id="llx-canvas" aria-hidden="true"></canvas><div class="llx-vignette" aria-hidden="true"></div>
  @include('themes.partials.glasscard', ['hint' => 'move near stars · click to add one'])
  <script>window.__llx = { aColor: '{{ addslashes($aColor) }}', bColor: '{{ addslashes($bColor) }}', bgColor: '{{ addslashes($bgColor) }}', intensity: {{ $intensity }}, speed: {{ $speed }} };</script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  @if($interactJs !== null)<script>{!! $interactJs !!}</script>@else<script src="{{ asset('assets/themes/shared/interact.js') }}"></script>@endif
  @if($themeJs !== null)<script>{!! $themeJs !!}</script>@else<script src="{{ asset('assets/themes/constellation/constellation.js') }}"></script>@endif
  <script>document.addEventListener('DOMContentLoaded', function () { if (window.LLXInteract) LLXInteract.init(); if (window.ConstellationBG) ConstellationBG.init(window.__llx); });</script>
  @stack('linkstack-body-end')
</body>
</html>
