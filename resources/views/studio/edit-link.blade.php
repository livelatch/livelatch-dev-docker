@extends('layouts.sidebar')

@section('content')
@php
    $selectedTypename = old('typename', $typename ?: 'predefined');

    $blockCards = $LinkTypes->map(function ($lt) use ($selectedTypename) {
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
@endphp

<style data-ll-block-editor-style>
    .ll-block-builder {
        display: grid;
        grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .ll-block-picker,
    .ll-block-settings {
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 18px;
        background: var(--ll-surface-solid, #fff);
        box-shadow: var(--ll-shadow-soft, 0 12px 34px rgba(30, 16, 80, 0.08));
    }

    .ll-block-picker-header,
    .ll-block-settings-header {
        padding: 18px;
        border-bottom: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
    }

    .ll-block-picker-header h3,
    .ll-block-settings-header h3 {
        margin: 0;
        font-size: 1.05rem;
    }

    .ll-block-picker-header p,
    .ll-block-settings-header p {
        margin: 5px 0 0;
        color: var(--ll-muted, #6b6885);
        font-size: 0.9rem;
    }

    .ll-block-search-wrap {
        padding: 14px 18px 0;
    }

    .ll-block-search {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 12px;
        padding: 0 13px;
        color: var(--ll-text, #120f2d);
        background: var(--ll-bg-soft, #fff);
    }

    .ll-block-list {
        display: grid;
        gap: 10px;
        padding: 18px;
        max-height: 680px;
        overflow: auto;
    }

    .ll-block-option {
        width: 100%;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 14px;
        padding: 13px;
        background: transparent;
        color: var(--ll-text, #120f2d);
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        gap: 12px;
        text-align: left;
        transition: border-color 0.16s ease, background 0.16s ease, transform 0.16s ease;
    }

    .ll-block-option:hover,
    .ll-block-option:focus {
        border-color: rgba(0, 146, 236, 0.42);
        background: rgba(0, 146, 236, 0.06);
        transform: translateY(-1px);
    }

    .ll-block-option.is-selected {
        border-color: rgba(0, 146, 236, 0.72);
        background: rgba(0, 146, 236, 0.10);
    }

    .ll-block-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        color: #fff;
        background: linear-gradient(135deg, var(--ll-primary, #0092ec), var(--ll-primary-2, #0ce5de));
        font-size: 1.2rem;
    }

    .ll-block-copy strong {
        display: block;
        color: var(--ll-text, #120f2d);
        line-height: 1.2;
    }

    .ll-block-copy span {
        display: block;
        margin-top: 4px;
        color: var(--ll-muted, #6b6885);
        font-size: 0.84rem;
        line-height: 1.35;
    }

    .ll-block-settings-body {
        padding: 18px;
    }

    .ll-block-empty {
        display: none;
        padding: 0 18px 18px;
        color: var(--ll-muted, #6b6885);
        font-size: 0.9rem;
    }

    .ll-block-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        padding-top: 22px;
    }

    @media (max-width: 991.98px) {
        .ll-block-builder {
            grid-template-columns: 1fr;
        }

        .ll-block-list {
            max-height: none;
        }
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-journal-plus"></i>
                        @if($LinkID !== 0)
                            {{ __('messages.Edit') }}
                        @else
                            {{ __('messages.Add') }}
                        @endif
                        {{ __('messages.Block') }}
                    </h2>
                    <p class="text-muted mb-0">Choose the block type first, then fill in its settings.</p>
                </div>

                <a class="btn btn-light" href="{{ url('studio/links') }}">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.Back') ?? 'Back' }}
                </a>
            </div>

            <form action="{{ route('addLink') }}" method="post" id="ll-block-form">
                @method('POST')
                @csrf
                <input type="hidden" name="linkid" value="{{ $LinkID }}">
                <input type="hidden" name="typename" id="ll-selected-typename" value="{{ $selectedTypename }}">

                <div class="ll-block-builder">
                    <section class="ll-block-picker" aria-labelledby="ll-block-picker-title">
                        <div class="ll-block-picker-header">
                            <h3 id="ll-block-picker-title">Block library</h3>
                            <p>Select the type of content you want to add to your public profile.</p>
                        </div>

                        <div class="ll-block-search-wrap">
                            <input
                                type="search"
                                class="ll-block-search"
                                id="ll-block-search"
                                placeholder="Search blocks"
                                aria-label="Search blocks"
                            >
                        </div>

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
                                    <span class="ll-block-icon">
                                        <i class="{{ $block['icon'] }}"></i>
                                    </span>
                                    <span class="ll-block-copy">
                                        <strong>{{ $block['title'] }}</strong>
                                        <span>{{ $block['description'] }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <div class="ll-block-empty" id="ll-block-empty">No blocks match that search.</div>
                    </section>

                    <section class="ll-block-settings" aria-labelledby="ll-block-settings-title">
                        <div class="ll-block-settings-header">
                            <h3 id="ll-block-settings-title">{{ $selectedBlock['title'] ?? 'Block settings' }}</h3>
                            <p id="ll-block-settings-description">{{ $selectedBlock['description'] ?? 'Fill in the details for this block.' }}</p>
                        </div>

                        <div class="ll-block-settings-body">
                            @if($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <div id="link_params">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="ll-block-actions">
                                <a class="btn btn-danger" href="{{ url('studio/links') }}">{{ __('messages.Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
                                <button type="button" class="btn btn-soft-primary" data-save-add-more>
                                    {{ __('messages.Save and Add More') }}
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.LivelatchBlockEditor = window.LivelatchBlockEditor || {
        init: function () {
            const form = document.getElementById('ll-block-form');
            const selectedTypeInput = document.getElementById('ll-selected-typename');
            const paramsContainer = document.getElementById('link_params');
            const searchInput = document.getElementById('ll-block-search');
            const emptyState = document.getElementById('ll-block-empty');
            const settingsTitle = document.getElementById('ll-block-settings-title');
            const settingsDescription = document.getElementById('ll-block-settings-description');
            const saveAddMoreButton = document.querySelector('[data-save-add-more]');

            if (!form || !selectedTypeInput || !paramsContainer || form.dataset.blockEditorInitialized === 'true') {
                return;
            }

            form.dataset.blockEditorInitialized = 'true';

            const blockOptions = Array.from(document.querySelectorAll('[data-block-option]'));
            const linkId = form.querySelector('input[name="linkid"]').value || '0';
            const baseUrl = @json(url(''));

            function showLoading() {
                paramsContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            }

            function loadBlockParams(typeId) {
                showLoading();

                fetch(baseUrl + '/studio/linkparamform_part/' + encodeURIComponent(typeId) + '/' + encodeURIComponent(linkId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
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

            if (saveAddMoreButton) {
                saveAddMoreButton.addEventListener('click', function () {
                    let paramField = form.querySelector('input[name="param"]');

                    if (!paramField) {
                        paramField = document.createElement('input');
                        paramField.type = 'hidden';
                        paramField.name = 'param';
                        form.appendChild(paramField);
                    }

                    paramField.value = 'add_more';
                    form.submit();
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

    window.LivelatchBlockEditor.init();
</script>
@endsection
