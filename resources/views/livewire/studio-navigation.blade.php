<nav class="ll-nav-rail" aria-label="Studio navigation" wire:ignore>
    @foreach($sections as $section)
        @php
            $sectionIsActive = collect($section['items'])->contains(fn ($item) => $item['active'] ?? false);
            $sectionIsOpen = $sectionIsActive;
        @endphp

        <div class="ll-nav-group {{ $sectionIsOpen ? 'is-open' : '' }} {{ $sectionIsActive ? 'is-active' : '' }}" data-ll-nav-group>
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
                        <a
                            class="ll-nav-link {{ ($item['active'] ?? false) ? 'active' : '' }}"
                            href="{{ $item['url'] }}"
                            hx-get="{{ $item['url'] }}"
                            hx-target="#ll-content"
                            hx-select="#ll-content > *"
                            hx-push-url="true"
                            hx-swap="innerHTML"
                            hx-indicator="{{ $item['skeleton'] ?? '#ll-page-skeleton' }}"
                        >
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
