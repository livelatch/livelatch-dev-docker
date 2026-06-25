{{--
    Shared link + block renderer for blade themes.

    Renders EVERY block type through the blocks:: namespace (spacer, text,
    heading, …) exactly like the standard profile, and renders plain links as
    themed anchors styled by $linkClass. Each rendered item gets `--ll-i` (its
    index) for optional staggered CSS entrance animations.

    Params:
      $links     — the user's links collection (with buttons.name + decoded type_params)
      $linkClass — CSS class for themed button anchors (e.g. 'pt-link', 'ae-link')

    The host theme must also place, in its <head> (so library @push targets resolve):
      @include('linkstack.modules.block-libraries', ['links' => $links])
      @stack('linkstack-head')
    and @stack('linkstack-body-end') before </body>.
--}}
@php
    $initial = 1;

    // Universal link-icon settings (set on the Theme Studio Beta page, stored in
    // the blade theme settings). $settings is inherited from the including theme
    // view. Icons are Simple Icons stored on each link as custom_icon = "si:<slug>".
    $llIconSettings   = $settings ?? [];
    $llShowLinkIcons  = (string) ($llIconSettings['showLinkIcons'] ?? '1') !== '0';
    $llLinkIconColor  = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) ($llIconSettings['linkIconColor'] ?? '')) ? $llIconSettings['linkIconColor'] : '';

    if (!function_exists('ll_theme_link_icon')) {
        function ll_theme_link_icon($link, $show, $color) {
            if (!$show) { return ''; }
            $ci = (string) ($link->custom_icon ?? '');
            if (strpos($ci, 'si:') !== 0) { return ''; }
            $slug = preg_replace('/[^a-z0-9-]/', '', strtolower(explode(':', substr($ci, 3))[0] ?? ''));
            if ($slug === '') { return ''; }
            $src = 'https://cdn.simpleicons.org/' . $slug;
            $bg  = $color !== '' ? $color : 'currentColor';
            return '<span class="ll-theme-si" aria-hidden="true" style="background-color:' . e($bg)
                . ";-webkit-mask:url('" . e($src) . "') center/contain no-repeat;mask:url('" . e($src) . "') center/contain no-repeat;\"></span>";
        }
    }
@endphp

@once
<style>
    .ll-theme-si {
        display: inline-block; width: 1em; height: 1em; vertical-align: -0.12em; margin-right: .5em;
        background-color: currentColor;
        -webkit-mask-position: center; mask-position: center;
        -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat;
        -webkit-mask-size: contain; mask-size: contain;
    }
</style>
@endonce

@foreach($links as $i => $link)
    @if(isset($link->custom_html) && $link->custom_html)
        @php setBlockAssetContext($link->type); @endphp
        <div class="ll-theme-block" style="--ll-i: {{ $i }}">
            @includeIf('blocks::' . $link->type . '.display', ['link' => $link, 'initial' => $initial++])
        </div>
    @elseif($link->name === 'icon')
        {{-- social icon handled by the theme's own markup; skip --}}
    @elseif($link->name === 'vcard')
        <a href="{{ route('vcard') . '/' . $link->id }}" id="{{ $link->id }}" class="{{ $linkClass }} button-click" style="--ll-i: {{ $i }}" rel="noopener noreferrer nofollow">{{ $link->title }}</a>
    @elseif(!empty($link->link))
        <a href="{{ $link->link }}" id="{{ $link->id }}" class="{{ $linkClass }} button-click" style="--ll-i: {{ $i }}" rel="noopener noreferrer nofollow" target="_blank">{!! ll_theme_link_icon($link, $llShowLinkIcons, $llLinkIconColor) !!}{{ $link->title }}</a>
    @endif
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function track(event) {
            var el = event.target.closest ? event.target.closest('.button-click') : null;
            if (!el || !el.id) return;
            if (!sessionStorage.getItem('clicked-' + el.id)) {
                fetch('{{ route("clickNumber") }}/' + el.id, { method: 'GET', headers: { 'Content-Type': 'application/json' } });
                sessionStorage.setItem('clicked-' + el.id, 'true');
            }
        }
        document.addEventListener('mousedown', function (e) { if (e.button === 0 || e.button === 1) track(e); });
        document.addEventListener('touchstart', track);
    });
</script>
