<nav class="ll-nav-rail" aria-label="Studio navigation" wire:ignore hx-ext="preload">
    @foreach($sections as $section)
        @php
            $sectionIsSingle = $section['single'] ?? false;
            $sectionIsActive = $sectionIsSingle
                ? ($section['active'] ?? false)
                : collect($section['items'])->contains(fn ($item) => $item['active'] ?? false);
            $sectionIsOpen = $sectionIsActive;
        @endphp

        @if($sectionIsSingle)
            <a
                class="ll-nav-link ll-nav-single {{ $sectionIsActive ? 'active' : '' }}"
                href="{{ $section['url'] }}"
                data-tour="nav-{{ $section['key'] }}"
                hx-get="{{ $section['url'] }}"
                hx-target="#ll-content"
                hx-select="#ll-content > *"
                hx-push-url="true"
                hx-swap="innerHTML"
                hx-indicator="{{ $section['skeleton'] ?? '#ll-page-skeleton' }}"
                preload="mouseover"
            >
                <i class="{{ $section['icon'] }}"></i>
                <span>{{ $section['label'] }}</span>
            </a>
            @continue
        @endif

        <div class="ll-nav-group {{ $sectionIsOpen ? 'is-open' : '' }} {{ $sectionIsActive ? 'is-active' : '' }}" data-ll-nav-group data-tour="nav-{{ $section['key'] }}">
            <button
                type="button"
                class="ll-nav-group-button"
                data-ll-nav-toggle
                aria-expanded="{{ $sectionIsOpen ? 'true' : 'false' }}"
                aria-controls="ll-nav-panel-{{ $section['key'] }}"
                title="{{ $section['label'] }}"
            >
                <span class="ll-nav-group-icon">
                    <i class="{{ $section['icon'] }}"></i>
                </span>
                <span class="ll-nav-group-label">{{ $section['label'] }}</span>
                <i class="bi bi-chevron-right ll-nav-group-chevron"></i>
            </button>

            <ul class="ll-nav-list ll-nav-panel" id="ll-nav-panel-{{ $section['key'] }}">
                @foreach($section['items'] as $item)
                    <li>
                        @if(!empty($item['external']))
                        <a
                            class="ll-nav-link {{ ($item['active'] ?? false) ? 'active' : '' }}"
                            href="{{ $item['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                        @else
                        <a
                            class="ll-nav-link {{ ($item['active'] ?? false) ? 'active' : '' }}"
                            href="{{ $item['url'] }}"
                            hx-get="{{ $item['url'] }}"
                            hx-target="#ll-content"
                            hx-select="#ll-content > *"
                            hx-push-url="true"
                            hx-swap="innerHTML"
                            hx-indicator="{{ $item['skeleton'] ?? '#ll-page-skeleton' }}"
                            preload="mouseover"
                        >
                        @endif
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="ll-nav-badge">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
