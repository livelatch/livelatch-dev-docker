<nav class="ll-nav-rail" aria-label="Studio navigation">
    @foreach($sections as $section)
        @php
            $sectionIsActive = collect($section['items'])->contains(fn ($item) => $item['active'] ?? false);
            $sectionIsOpen = $openSection === $section['key'];
        @endphp

        <div class="ll-nav-group {{ $sectionIsOpen ? 'is-open' : '' }} {{ $sectionIsActive ? 'is-active' : '' }}">
            <button
                type="button"
                class="ll-nav-group-button"
                wire:click="toggleSection('{{ $section['key'] }}')"
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

            @if($sectionIsOpen)
                <ul class="ll-nav-list ll-nav-panel" id="ll-nav-panel-{{ $section['key'] }}">
                    @foreach($section['items'] as $item)
                        <li>
                            <a class="ll-nav-link {{ ($item['active'] ?? false) ? 'active' : '' }}" {!! llHtmxAttrs($item['url']) !!}>
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                                @if(!empty($item['badge']))
                                    <span class="ll-nav-badge">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</nav>
