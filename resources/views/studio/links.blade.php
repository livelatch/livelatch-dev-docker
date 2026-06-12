@extends('layouts.sidebar')

@section('content')
@php
    use App\Models\Button;

    if (isset($_COOKIE['LinkCount'])) {
        setcookie('LinkCount', '', time() - 3600);
    }

    if (!function_exists('ll_link_icon_name')) {
        function ll_link_icon_name($buttonName) {
            if ($buttonName === 'default email') {
                return 'email';
            }

            if ($buttonName === 'default email_alt') {
                return 'email_alt';
            }

            return $buttonName ?: 'website';
        }
    }

    $selectedTypename = old('typename', $typename ?? 'link');
    $blockCards = ($LinkTypes ?? collect())->map(function ($lt) use ($selectedTypename) {
        if (block_text_translation_check($lt['title'])) {
            $title = bt($lt['title']);
        } else {
            $title = __('messages.block.title.' . $lt['typename']);
        }

        $description = bt($lt['description']) ?? __('messages.block.description.' . $lt['typename']);

        return [
            'typename' => $lt['typename'],
            'title' => $title,
            'description' => $description,
            'icon' => $lt['icon'],
            'selected' => $selectedTypename === $lt['typename'],
        ];
    });

    $selectedBlock = $blockCards->firstWhere('selected', true) ?? $blockCards->first();
    $profileUrl = Auth::user()->littlelink_name ? url('/@' . Auth::user()->littlelink_name) : url('/studio/page');
@endphp

<style data-ll-links-manager-style>
    .ll-links-manager {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(320px, 390px);
        gap: 22px;
        align-items: start;
    }

    .ll-links-main {
        display: grid;
        gap: 22px;
    }

    .ll-panel {
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 18px;
        background: var(--ll-surface-solid, #fff);
        box-shadow: var(--ll-shadow-soft, 0 12px 34px rgba(30, 16, 80, 0.08));
        overflow: hidden;
    }

    .ll-panel-header {
        padding: 18px;
        border-bottom: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .ll-panel-header h3 {
        margin: 0;
        font-size: 1.05rem;
    }

    .ll-panel-header p {
        margin: 5px 0 0;
        color: var(--ll-muted, #6b6885);
        font-size: 0.9rem;
    }

    .ll-panel-body {
        padding: 18px;
    }

    .ll-link-list {
        display: grid;
        gap: 10px;
    }

    .ll-link-row {
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 14px;
        padding: 12px;
        background: var(--ll-bg-soft, #fff);
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
    }

    .ll-link-row.sortable-ghost {
        opacity: 0.55;
    }

    .sortable-handle {
        width: 34px;
        height: 34px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 10px;
        display: grid;
        place-items: center;
        cursor: grab;
        color: var(--ll-muted, #6b6885);
        background: transparent;
    }

    .ll-link-title {
        display: flex;
        gap: 9px;
        align-items: center;
        min-width: 0;
    }

    .ll-link-title strong {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--ll-text, #120f2d);
    }

    .ll-link-icon {
        width: 28px;
        height: 28px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: rgba(98, 54, 255, 0.10);
        color: var(--ll-primary, #6236ff);
        flex: 0 0 auto;
    }

    .ll-link-meta {
        margin-top: 4px;
        color: var(--ll-muted, #6b6885);
        font-size: 0.82rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ll-link-actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .ll-block-builder {
        display: grid;
        grid-template-columns: minmax(240px, 310px) minmax(0, 1fr);
        gap: 18px;
    }

    .ll-block-search {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 12px;
        padding: 0 13px;
        color: var(--ll-text, #120f2d);
        background: var(--ll-bg-soft, #fff);
        margin-bottom: 12px;
    }

    .ll-block-list {
        display: grid;
        gap: 9px;
        max-height: 430px;
        overflow: auto;
        padding-right: 4px;
    }

    .ll-block-option {
        width: 100%;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 13px;
        padding: 12px;
        background: transparent;
        color: var(--ll-text, #120f2d);
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 10px;
        text-align: left;
    }

    .ll-block-option:hover,
    .ll-block-option:focus,
    .ll-block-option.is-selected {
        border-color: rgba(98, 54, 255, 0.56);
        background: rgba(98, 54, 255, 0.08);
    }

    .ll-block-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary, #6236ff), var(--ll-primary-2, #9b5cff));
    }

    .ll-block-copy strong,
    .ll-block-copy span {
        display: block;
    }

    .ll-block-copy span {
        margin-top: 3px;
        color: var(--ll-muted, #6b6885);
        font-size: 0.8rem;
        line-height: 1.35;
    }

    .ll-phone-preview {
        position: sticky;
        top: 98px;
    }

    .ll-phone-frame {
        width: min(100%, 360px);
        margin: 0 auto;
        border: 10px solid #111827;
        border-radius: 38px;
        background: #111827;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        overflow: hidden;
    }

    .ll-phone-bar {
        height: 26px;
        background: #111827;
        display: grid;
        place-items: center;
    }

    .ll-phone-speaker {
        width: 78px;
        height: 6px;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.18);
    }

    .ll-phone-frame iframe {
        display: block;
        width: 100%;
        height: 640px;
        border: 0;
        background: #fff;
    }

    .ll-block-empty {
        display: none;
        color: var(--ll-muted, #6b6885);
        font-size: 0.88rem;
        padding: 8px 0;
    }

    @media (max-width: 1199.98px) {
        .ll-links-manager {
            grid-template-columns: 1fr;
        }

        .ll-phone-preview {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .ll-block-builder {
            grid-template-columns: 1fr;
        }

        .ll-link-row {
            grid-template-columns: 34px minmax(0, 1fr);
        }

        .ll-link-actions {
            grid-column: 2;
            justify-content: flex-start;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-link-45deg"></i> Links</h2>
            <p class="text-muted mb-0">Add blocks, arrange your profile, and preview the public page in one place.</p>
        </div>

        <a class="btn btn-light" href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-box-arrow-up-right"></i>
            View profile
        </a>
    </div>

    <div class="ll-links-manager">
        <div class="ll-links-main">
            <section class="ll-panel" id="add-block">
                <div class="ll-panel-header">
                    <div>
                        <h3>Add a block</h3>
                        <p>Select a block type and fill in its settings. Saving adds it to your list below.</p>
                    </div>
                </div>

                <div class="ll-panel-body">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('addLink') }}" method="post" id="ll-combined-link-form">
                        @method('POST')
                        @csrf
                        <input type="hidden" name="linkid" value="0">
                        <input type="hidden" name="typename" id="ll-selected-typename" value="{{ $selectedTypename }}">

                        <div class="ll-block-builder">
                            <div>
                                <input
                                    type="search"
                                    class="ll-block-search"
                                    id="ll-block-search"
                                    placeholder="Search block types"
                                    aria-label="Search block types"
                                >

                                <div class="ll-block-list" id="ll-block-list">
                                    @foreach($blockCards as $block)
                                        <button
                                            type="button"
                                            class="ll-block-option {{ $block['selected'] ? 'is-selected' : '' }}"
                                            data-block-option
                                            data-typeid="{{ $block['typename'] }}"
                                            data-title="{{ $block['title'] }}"
                                            data-description="{{ $block['description'] }}"
                                            aria-pressed="{{ $block['selected'] ? 'true' : 'false' }}"
                                        >
                                            <span class="ll-block-icon"><i class="{{ $block['icon'] }}"></i></span>
                                            <span class="ll-block-copy">
                                                <strong>{{ $block['title'] }}</strong>
                                                <span>{{ $block['description'] }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="ll-block-empty" id="ll-block-empty">No blocks match that search.</div>
                            </div>

                            <div>
                                <div class="mb-3">
                                    <h4 class="mb-1" id="ll-block-settings-title">{{ $selectedBlock['title'] ?? 'Block settings' }}</h4>
                                    <p class="text-muted mb-0" id="ll-block-settings-description">{{ $selectedBlock['description'] ?? 'Fill in the details for this block.' }}</p>
                                </div>

                                <div id="link_params">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 pt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i>
                                        Add to links
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <section class="ll-panel">
                <div class="ll-panel-header">
                    <div>
                        <h3>Current links</h3>
                        <p>Drag items to rearrange their public order.</p>
                    </div>
                    <span class="badge bg-primary">{{ $links->total() }} total</span>
                </div>

                <div class="ll-panel-body">
                    <div
                        class="ll-link-list"
                        id="links-table-body"
                        data-page="{{ request('page', 1) }}"
                        data-per-page="{{ $pagePage ? $pagePage : 0 }}"
                    >
                        @forelse($links as $link)
                            @php
                                $button = Button::find($link->button_id);
                                $buttonName = ll_link_icon_name($button->name ?? null);
                                $isIconLink = $button && $button->name === 'icon';
                            @endphp

                            @if(!$isIconLink)
                                <div class="ll-link-row" data-id="{{ $link->id }}">
                                    <button type="button" class="sortable-handle" aria-label="Drag to reorder">
                                        <i class="bi bi-grip-vertical"></i>
                                    </button>

                                    <div class="min-width-0">
                                        <div class="ll-link-title">
                                            <span class="ll-link-icon">
                                                @if(in_array($buttonName, ['space', 'heading', 'text']))
                                                    <i class="bi {{ $buttonName === 'space' ? 'bi-distribute-vertical' : ($buttonName === 'heading' ? 'bi-card-heading' : 'bi-fonts') }}"></i>
                                                @elseif($link->custom_icon && $link->type && $link->type !== 'predefined')
                                                    <i class="fa {{ $link->custom_icon }}"></i>
                                                @else
                                                    <img
                                                        alt=""
                                                        width="16"
                                                        height="16"
                                                        src="{{ asset('/assets/linkstack/icons/' . $buttonName . '.svg') }}"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';"
                                                    >
                                                    <i class="bi bi-link-45deg" style="display:none;"></i>
                                                @endif
                                            </span>

                                            <strong title="{{ strip_tags($link->title) }}">{{ Str::limit(strip_tags($link->title), 80) }}</strong>
                                        </div>

                                        @if(!empty($link->link) && $buttonName !== 'vcard')
                                            <div class="ll-link-meta" title="{{ $link->link }}">{{ Str::limit($link->link, 95) }}</div>
                                        @elseif($buttonName === 'vcard')
                                            <div class="ll-link-meta">vCard download</div>
                                        @else
                                            <div class="ll-link-meta">{{ ucfirst($link->type ?? 'block') }} block</div>
                                        @endif
                                    </div>

                                    <div class="ll-link-actions">
                                        @if(!empty($link->link))
                                            <span class="badge bg-light text-dark" title="Clicks">
                                                <i class="bi bi-bar-chart-line"></i>
                                                {{ $link->click_number }}
                                            </span>
                                        @endif

                                        <a href="{{ route('editLink', $link->id) }}" class="btn btn-sm btn-warning" aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a
                                            href="{{ route('deleteLink', $link->id) }}"
                                            onclick="return confirm('{{ __('messages.confirm_delete', ['title' => addslashes(strip_tags($link->title))]) }}')"
                                            class="btn btn-sm btn-danger"
                                            aria-label="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-link-45deg h2 d-block"></i>
                                {{ __('messages.No Link Added') }}
                            </div>
                        @endforelse
                    </div>

                    <script>
                        window.linksTableOrders = @json($links->pluck('id')->values()->all());
                    </script>
                </div>
            </section>
        </div>

        <aside class="ll-panel ll-phone-preview">
            <div class="ll-panel-header">
                <div>
                    <h3>Live preview</h3>
                    <p>Phone-sized view of your public profile.</p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="ll-refresh-preview">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <div class="ll-panel-body">
                <div class="ll-phone-frame">
                    <div class="ll-phone-bar"><span class="ll-phone-speaker"></span></div>
                    <iframe
                        allowtransparency="true"
                        id="frPreview1"
                        src="{{ $profileUrl }}"
                        title="Public profile preview"
                    >{{ __('messages.No compatible browser') }}</iframe>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
    window.LivelatchLinksManager = window.LivelatchLinksManager || {
        init: function () {
            const page = document.querySelector('.ll-links-manager');

            if (!page || page.dataset.linksManagerInitialized === 'true') {
                return;
            }

            page.dataset.linksManagerInitialized = 'true';

            const form = document.getElementById('ll-combined-link-form');
            const selectedTypeInput = document.getElementById('ll-selected-typename');
            const paramsContainer = document.getElementById('link_params');
            const searchInput = document.getElementById('ll-block-search');
            const emptyState = document.getElementById('ll-block-empty');
            const settingsTitle = document.getElementById('ll-block-settings-title');
            const settingsDescription = document.getElementById('ll-block-settings-description');
            const blockOptions = Array.from(document.querySelectorAll('[data-block-option]'));
            const preview = document.getElementById('frPreview1');
            const refreshPreview = document.getElementById('ll-refresh-preview');
            const sortableList = document.getElementById('links-table-body');
            const baseUrl = @json(url(''));

            function refreshPhonePreview() {
                if (preview) {
                    preview.src = preview.src.split('?')[0] + '?preview=' + Date.now();
                }
            }

            function loadBlockParams(typeId) {
                if (!paramsContainer) {
                    return;
                }

                paramsContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

                fetch(baseUrl + '/studio/linkparamform_part/' + encodeURIComponent(typeId) + '/0', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Unable to load block settings.');
                        }

                        return response.text();
                    })
                    .then(function (html) {
                        paramsContainer.innerHTML = html;
                        document.dispatchEvent(new Event('contentLoaded'));
                    })
                    .catch(function () {
                        paramsContainer.innerHTML = '<div class="alert alert-danger" role="alert">Block settings could not be loaded.</div>';
                    });
            }

            function selectBlock(option) {
                selectedTypeInput.value = option.dataset.typeid;
                settingsTitle.textContent = option.dataset.title || 'Block settings';
                settingsDescription.textContent = option.dataset.description || 'Fill in the details for this block.';

                blockOptions.forEach(function (blockOption) {
                    const isSelected = blockOption === option;
                    blockOption.classList.toggle('is-selected', isSelected);
                    blockOption.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                });

                loadBlockParams(option.dataset.typeid);
            }

            function filterBlocks() {
                const query = (searchInput.value || '').trim().toLowerCase();
                let visibleCount = 0;

                blockOptions.forEach(function (option) {
                    const haystack = [
                        option.dataset.typeid,
                        option.dataset.title,
                        option.dataset.description
                    ].join(' ').toLowerCase();
                    const isVisible = haystack.includes(query);

                    option.style.display = isVisible ? '' : 'none';
                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            blockOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    selectBlock(option);
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', filterBlocks);
            }

            if (form) {
                form.addEventListener('submit', function () {
                    const type = selectedTypeInput.value;
                    const linkInput = form.querySelector('input[name="link"]');

                    if (type === 'email' && linkInput && !linkInput.value.toLowerCase().startsWith('mailto:')) {
                        linkInput.value = 'mailto:' + linkInput.value;
                    }

                    if (type === 'telephone' && linkInput && !linkInput.value.toLowerCase().startsWith('tel:')) {
                        linkInput.value = 'tel:' + linkInput.value;
                    }
                });
            }

            if (refreshPreview) {
                refreshPreview.addEventListener('click', refreshPhonePreview);
            }

            if (sortableList && window.Sortable) {
                Sortable.create(sortableList, {
                    handle: '.sortable-handle',
                    animation: 150,
                    swapThreshold: 0.6,
                    ghostClass: 'sortable-ghost',
                    store: {
                        get: function () {
                            return Array.isArray(window.linksTableOrders) ? window.linksTableOrders : [];
                        },
                        set: function (sortable) {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            const formData = new FormData();

                            sortable.toArray().forEach(function (id) {
                                formData.append('linkOrders[]', id);
                            });

                            formData.append('currentPage', sortableList.dataset.page || '1');
                            formData.append('perPage', sortableList.dataset.perPage || '0');

                            fetch(baseUrl + '/studio/sort-link', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: formData
                            })
                                .then(function (response) {
                                    if (!response.ok) {
                                        throw new Error('Sort failed.');
                                    }

                                    return response.json();
                                })
                                .then(function () {
                                    refreshPhonePreview();
                                })
                                .catch(function () {
                                    alert('The link order could not be saved.');
                                });
                        }
                    }
                });
            }

            const initialOption = blockOptions.find(function (option) {
                return option.dataset.typeid === selectedTypeInput.value;
            }) || blockOptions[0];

            if (initialOption) {
                selectBlock(initialOption);
            }
        }
    };

    window.LivelatchLinksManager.init();
</script>
@endsection
