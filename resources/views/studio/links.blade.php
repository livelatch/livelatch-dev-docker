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
    $blockCards = ($LinkTypes ?? collect())
        ->filter(fn ($lt) => !($lt->hidden ?? false))
        ->values()
        ->map(function ($lt) use ($selectedTypename) {
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
        grid-template-columns: minmax(300px, 360px) minmax(0, 1fr) minmax(310px, 380px);
        gap: 18px;
        align-items: start;
    }

    .ll-add-panel {
        position: sticky;
        top: 98px;
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
        background: var(--ll-surface-solid, #fff);
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
        background: rgba(0, 146, 236, 0.10);
        color: var(--ll-primary, #0092ec);
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

    /* Two-step picker: browse the catalogue, then configure the chosen block. */
    .ll-add-config { display: none; }
    .ll-add-config.is-active { display: block; }
    .ll-add-browse.is-hidden { display: none; }

    .ll-add-back {
        display: inline-flex; align-items: center; gap: 6px; background: transparent;
        border: 0; color: var(--ll-muted, #6b6885); font-weight: 700; font-size: .82rem;
        padding: 0; margin-bottom: 14px; cursor: pointer;
    }
    .ll-add-back:hover { color: var(--ll-text, #120f2d); }

    .ll-add-chosen {
        display: flex; align-items: center; gap: 11px; padding: 11px 12px; margin-bottom: 16px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10)); border-radius: 13px;
        background: color-mix(in srgb, var(--ll-primary) 7%, transparent);
    }
    .ll-add-chosen .ll-block-icon { width: 34px; height: 34px; }
    .ll-add-chosen-copy strong { display: block; font-size: .92rem; }
    .ll-add-chosen-copy span { display: block; margin-top: 2px; color: var(--ll-muted, #6b6885); font-size: .78rem; }

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
        gap: 6px;
        max-height: 540px;
        overflow: auto;
        padding-right: 4px;
    }

    .ll-block-option {
        width: 100%;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 11px;
        padding: 8px 10px;
        background: transparent;
        color: var(--ll-text, #120f2d);
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr) 12px;
        gap: 10px;
        align-items: center;
        text-align: left;
    }

    .ll-block-chev {
        color: var(--ll-muted, #6b6885);
        opacity: .45;
        font-size: .78rem;
    }
    .ll-block-option:hover .ll-block-chev,
    .ll-block-option.is-selected .ll-block-chev { opacity: .8; }

    .ll-block-option {
        transition: border-color .14s ease, background .14s ease, transform .12s ease;
    }
    .ll-block-option:hover,
    .ll-block-option:focus {
        transform: translateY(-1px);
        border-color: color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border));
    }
    .ll-block-option.is-selected {
        border-color: color-mix(in srgb, var(--ll-primary) 60%, var(--ll-border));
        background: color-mix(in srgb, var(--ll-primary) 10%, transparent);
        box-shadow: 0 8px 24px color-mix(in srgb, var(--ll-primary) 14%, transparent);
    }

    .ll-block-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: .9rem;
        background: linear-gradient(135deg, var(--ll-primary, #0092ec), var(--ll-primary-2, #0ce5de));
    }

    .ll-block-copy {
        min-width: 0;
    }
    .ll-block-copy strong,
    .ll-block-copy span {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ll-block-copy strong { font-size: .85rem; }
    .ll-block-copy span {
        margin-top: 1px;
        color: var(--ll-muted, #6b6885);
        font-size: 0.74rem;
        line-height: 1.3;
    }

    /* ---- Merged "Links" form + Simple Icons picker (themes-panel language) ---- */
    .ll-lf { display: grid; gap: 14px; }
    .ll-lf-field { display: grid; gap: 6px; }
    .ll-lf-field > .form-label {
        margin: 0; color: var(--ll-muted); font-size: .68rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .ll-lf .form-control, .ll-lf .ll-lf-platform {
        width: 100%; min-height: 42px; border: 1px solid var(--ll-border); border-radius: 12px;
        background: var(--ll-bg-soft); color: var(--ll-text); padding: 9px 12px; font-weight: 500;
    }
    .ll-lf-favicon-wrap { display: flex; flex-direction: row; align-items: center; gap: 8px; }
    .ll-lf-favicon-wrap .form-check-input { margin: 0; flex: 0 0 auto; }
    .ll-lf-favicon-wrap .form-check-label { font-size: .85rem; color: var(--ll-muted); }
    .ll-lf-iconrow { display: grid; grid-template-columns: 46px 1fr; gap: 8px; align-items: center; }
    .ll-lf-iconpreview {
        width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center;
        border: 1px solid var(--ll-border); background: var(--ll-surface-solid); color: var(--ll-muted);
    }
    .ll-lf-iconpreview img { width: 26px; height: 26px; object-fit: contain; }
    .ll-lf-iconresults {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(46px, 1fr)); gap: 8px;
        margin-top: 2px; max-height: 210px; overflow: auto; padding: 2px;
    }
    .ll-lf-iconresults:empty { display: none; }
    .ll-lf-iconchip {
        aspect-ratio: 1; border: 1px solid var(--ll-border); border-radius: 12px;
        background: var(--ll-surface-solid); display: grid; place-items: center; cursor: pointer;
        padding: 9px; transition: border-color .14s, transform .12s, background .14s;
    }
    .ll-lf-iconchip:hover { transform: translateY(-1px); border-color: color-mix(in srgb, var(--ll-primary) 45%, var(--ll-border)); }
    .ll-lf-iconchip.is-active { border-color: color-mix(in srgb, var(--ll-primary) 65%, var(--ll-border)); background: color-mix(in srgb, var(--ll-primary) 10%, transparent); }
    .ll-lf-iconchip img { width: 100%; height: 100%; object-fit: contain; }
    .ll-lf-icon-freetype {
        grid-column: 1 / -1; text-align: left; font-size: .82rem; color: var(--ll-text);
        border: 1px dashed color-mix(in srgb, var(--ll-primary) 40%, var(--ll-border)); border-radius: 10px;
        padding: 8px 10px; cursor: pointer; background: color-mix(in srgb, var(--ll-primary) 6%, transparent);
        display: flex; align-items: center; gap: 8px;
    }
    .ll-lf-icon-freetype img { width: 20px; height: 20px; }
    .ll-lf-iconpicker-hidden { display: none !important; }

    /* Live preview — matches the Theme Studio device switcher */
    .ll-lp-preview {
        position: sticky;
        top: 98px;
    }
    .ll-lp-devices { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 14px; }
    .ll-lp-device-btn {
        border: 1px solid var(--ll-border); border-radius: 999px; background: transparent;
        color: var(--ll-muted); cursor: pointer; font-weight: 700; font-size: .74rem;
        padding: 6px 11px; display: inline-flex; gap: 6px; align-items: center;
        transition: border-color .14s, color .14s, background .14s;
    }
    .ll-lp-device-btn:hover { color: var(--ll-text); }
    .ll-lp-device-btn.is-active {
        border-color: color-mix(in srgb, var(--ll-primary) 56%, var(--ll-border));
        color: var(--ll-primary); background: color-mix(in srgb, var(--ll-primary) 10%, transparent);
    }
    .ll-lp-stage { display: flex; justify-content: center; align-items: flex-start; min-height: 200px; }
    .ll-lp-scaler { position: relative; }
    .ll-lp-frame {
        position: absolute; top: 0; left: 0; transform-origin: top left;
        background: #000; overflow: hidden; box-shadow: 0 24px 60px rgba(8, 12, 30, .32);
    }
    .ll-lp-frame.has-bezel { border: 10px solid #0b0f1c; }
    .ll-lp-frame iframe { width: 100%; height: 100%; border: 0; display: block; background: #fff; }
    .ll-lp-meta { text-align: center; color: var(--ll-muted); font-size: .74rem; margin: 12px 0 0; }

    .ll-block-empty {
        display: none;
        color: var(--ll-muted, #6b6885);
        font-size: 0.88rem;
        padding: 8px 0;
    }

    /* Edit-block modal */
    .ll-modal[hidden] { display: none; }
    .ll-modal {
        position: fixed; inset: 0; z-index: 1080;
        display: flex; align-items: flex-start; justify-content: center;
        padding: 40px 16px; overflow-y: auto;
    }
    .ll-modal-backdrop {
        position: fixed; inset: 0; background: rgba(8, 10, 24, 0.55);
        backdrop-filter: blur(2px);
    }
    .ll-modal-dialog {
        position: relative; z-index: 1; width: 100%; max-width: 560px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
        border-radius: 18px; background: var(--ll-surface-solid, #fff);
        box-shadow: 0 30px 80px rgba(8, 12, 30, 0.45);
        animation: ll-modal-in .16s ease;
    }
    @keyframes ll-modal-in {
        from { opacity: 0; transform: translateY(10px) scale(.985); }
        to { opacity: 1; transform: none; }
    }
    .ll-modal-header {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;
        padding: 18px; border-bottom: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
    }
    .ll-modal-header h3 { margin: 0; font-size: 1.05rem; }
    .ll-modal-header p {
        margin: 5px 0 0; color: var(--ll-muted, #6b6885); font-size: 0.88rem;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .ll-modal-x {
        flex: 0 0 auto; width: 34px; height: 34px; border-radius: 10px;
        border: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10)); background: transparent;
        color: var(--ll-muted, #6b6885); display: grid; place-items: center; cursor: pointer;
    }
    .ll-modal-x:hover { color: var(--ll-text, #120f2d); }
    .ll-modal-body { padding: 18px; }
    .ll-modal-footer {
        display: flex; justify-content: flex-end; gap: 10px;
        padding: 16px 18px; border-top: 1px solid var(--ll-border, rgba(18, 15, 45, 0.10));
    }

    @media (max-width: 1399.98px) {
        .ll-links-manager {
            grid-template-columns: minmax(280px, 330px) minmax(0, 1fr);
        }

        .ll-lp-preview {
            grid-column: 1 / -1;
            position: static;
        }
    }

    @media (max-width: 991.98px) {
        .ll-links-manager {
            grid-template-columns: 1fr;
        }

        .ll-add-panel {
            position: static;
        }

        .ll-lp-preview {
            grid-column: auto;
        }
    }

    @media (max-width: 767.98px) {
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
            <h2 class="mb-1"><i class="bi bi-link-45deg"></i> Blocks</h2>
            <p class="text-muted mb-0">Add blocks, arrange your profile, and preview the public page in one place.</p>
        </div>

        <a class="btn btn-light" href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-box-arrow-up-right"></i>
            View profile
        </a>
    </div>

    <div class="ll-links-manager">
        <section class="ll-panel ll-add-panel" id="add-block">
            <div class="ll-panel-header">
                <div>
                    <h3>Add a block</h3>
                    <p>Pick a block type, set it up, and it joins your list.</p>
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

                    {{-- Step 1 — browse the block catalogue --}}
                    <div class="ll-add-browse" id="ll-add-browse">
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
                                    data-icon="{{ $block['icon'] }}"
                                    aria-pressed="{{ $block['selected'] ? 'true' : 'false' }}"
                                >
                                    <span class="ll-block-icon"><i class="{{ $block['icon'] }}"></i></span>
                                    <span class="ll-block-copy">
                                        <strong>{{ $block['title'] }}</strong>
                                        <span>{{ $block['description'] }}</span>
                                    </span>
                                    <i class="bi bi-chevron-right ll-block-chev"></i>
                                </button>
                            @endforeach
                        </div>

                        <div class="ll-block-empty" id="ll-block-empty">No blocks match that search.</div>
                    </div>

                    {{-- Step 2 — configure the chosen block --}}
                    <div class="ll-add-config" id="ll-add-config">
                        <button type="button" class="ll-add-back" id="ll-add-back">
                            <i class="bi bi-arrow-left"></i> All block types
                        </button>

                        <div class="ll-add-chosen">
                            <span class="ll-block-icon" id="ll-add-chosen-icon"><i class="bi bi-grid-1x2"></i></span>
                            <span class="ll-add-chosen-copy">
                                <strong id="ll-block-settings-title">{{ $selectedBlock['title'] ?? 'Block settings' }}</strong>
                                <span id="ll-block-settings-description">{{ $selectedBlock['description'] ?? 'Fill in the details for this block.' }}</span>
                            </span>
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
                </form>
            </div>
        </section>

        <section class="ll-panel ll-links-current">
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
                                                @elseif(\Illuminate\Support\Str::startsWith($link->custom_icon ?? '', 'si:'))
                                                    @php
                                                        $rowParts = explode(':', substr($link->custom_icon, 3));
                                                        $rowSlug = preg_replace('/[^a-z0-9-]/', '', strtolower($rowParts[0] ?? ''));
                                                        $rowHex = isset($rowParts[1]) ? preg_replace('/[^0-9A-Fa-f]/', '', $rowParts[1]) : '';
                                                    @endphp
                                                    <img alt="" width="16" height="16" src="https://cdn.simpleicons.org/{{ $rowSlug }}{{ $rowHex !== '' ? '/' . $rowHex : '' }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                                    <i class="bi bi-link-45deg" style="display:none;"></i>
                                                @elseif($link->custom_icon && $link->type && $link->type !== 'predefined' && $link->custom_icon !== 'fa-external-link')
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

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning"
                                            aria-label="Edit"
                                            data-edit-link
                                            data-id="{{ $link->id }}"
                                            data-typename="{{ $link->type ?: 'predefined' }}"
                                            data-title="{{ strip_tags($link->title) }}"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

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

        <aside class="ll-panel ll-lp-preview">
            <div class="ll-panel-header">
                <div>
                    <h3>Live preview</h3>
                    <p>See your public profile across devices.</p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="ll-refresh-preview" aria-label="Refresh preview">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <div class="ll-panel-body">
                <div class="ll-lp-devices" id="ll-lp-devices"></div>
                <div class="ll-lp-stage" id="ll-lp-stage">
                    <div class="ll-lp-scaler" id="ll-lp-scaler">
                        <div class="ll-lp-frame has-bezel" id="ll-lp-frame">
                            <iframe
                                allowtransparency="true"
                                id="frPreview1"
                                src="{{ $profileUrl }}"
                                title="Public profile preview"
                                referrerpolicy="no-referrer"
                            >{{ __('messages.No compatible browser') }}</iframe>
                        </div>
                    </div>
                </div>
                <p class="ll-lp-meta" id="ll-lp-meta">iPhone 17 Pro Max · 440 × 956</p>
            </div>
        </aside>
    </div>

    {{-- Edit-block modal: loads just the chosen block's settings form (no library). --}}
    <div class="ll-modal" id="ll-edit-modal" hidden>
        <div class="ll-modal-backdrop" data-edit-close></div>
        <div class="ll-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="ll-edit-modal-title">
            <div class="ll-modal-header">
                <div class="min-width-0">
                    <h3 id="ll-edit-modal-title"><i class="bi bi-pencil-square"></i> Edit block</h3>
                    <p id="ll-edit-modal-sub">Update this block's settings.</p>
                </div>
                <button type="button" class="ll-modal-x" data-edit-close aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('addLink') }}" method="post" id="ll-edit-form">
                @method('POST')
                @csrf
                <input type="hidden" name="linkid" id="ll-edit-linkid" value="">
                <input type="hidden" name="typename" id="ll-edit-typename" value="">

                <div class="ll-modal-body" id="ll-edit-params">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="ll-modal-footer">
                    <button type="button" class="btn btn-light" data-edit-close>{{ __('messages.Cancel') ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        {{ __('messages.Save') ?? 'Save' }} changes
                    </button>
                </div>
            </form>
        </div>
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
            const addBrowse = document.getElementById('ll-add-browse');
            const addConfig = document.getElementById('ll-add-config');
            const addBack = document.getElementById('ll-add-back');
            const chosenIcon = document.getElementById('ll-add-chosen-icon');
            const baseUrl = @json(url(''));

            // Linktree-style two-step: browse the catalogue, then configure.
            function showAddView(view) {
                const config = view === 'config';
                if (addBrowse) { addBrowse.classList.toggle('is-hidden', config); }
                if (addConfig) { addConfig.classList.toggle('is-active', config); }
            }

            // ---- Edit-block modal: load just the chosen block's settings form ----
            const editModal = document.getElementById('ll-edit-modal');
            const editLinkId = document.getElementById('ll-edit-linkid');
            const editTypename = document.getElementById('ll-edit-typename');
            const editParams = document.getElementById('ll-edit-params');
            const editSub = document.getElementById('ll-edit-modal-sub');
            const editSpinner = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

            function openEditModal(id, typename, title) {
                if (!editModal || !id) {
                    return;
                }

                editLinkId.value = id;
                editTypename.value = typename || 'predefined';
                editParams.innerHTML = editSpinner;
                if (editSub) {
                    editSub.textContent = title ? ('Editing “' + title + '”') : "Update this block's settings.";
                }

                editModal.hidden = false;
                document.body.style.overflow = 'hidden';

                fetch(baseUrl + '/studio/linkparamform_part/' + encodeURIComponent(editTypename.value) + '/' + encodeURIComponent(id), {
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
                        editParams.innerHTML = html;
                        document.dispatchEvent(new Event('contentLoaded'));
                    })
                    .catch(function () {
                        editParams.innerHTML = '<div class="alert alert-danger" role="alert">Block settings could not be loaded.</div>';
                    });
            }

            function closeEditModal() {
                if (!editModal) {
                    return;
                }
                editModal.hidden = true;
                document.body.style.overflow = '';
                editParams.innerHTML = editSpinner;
            }

            function refreshPhonePreview() {
                if (preview) {
                    preview.src = preview.src.split('?')[0] + '?preview=' + Date.now();
                }
            }

            // ---- Device preview (mirrors the Theme Studio Beta panel) ----
            const LP_DEVICES = {
                phone:   { label: 'iPhone 17 Pro Max', w: 440, h: 956, bezel: true },
                tablet:  { label: 'iPad Pro',          w: 834, h: 1194, bezel: true },
                desktop: { label: 'Desktop',           w: 1280, h: 800, bezel: false },
            };
            let lpDevice = 'phone';
            const lpDevicesWrap = document.getElementById('ll-lp-devices');
            const lpStage = document.getElementById('ll-lp-stage');
            const lpScaler = document.getElementById('ll-lp-scaler');
            const lpFrame = document.getElementById('ll-lp-frame');
            const lpMeta = document.getElementById('ll-lp-meta');

            function lpFit() {
                if (!lpStage || !lpScaler || !lpFrame) {
                    return;
                }
                const d = LP_DEVICES[lpDevice];
                const availW = lpStage.clientWidth || 320;
                const maxH = 620;
                const scale = Math.min(availW / d.w, maxH / d.h, 1);
                lpFrame.classList.toggle('has-bezel', !!d.bezel);
                const radius = d.bezel ? (lpDevice === 'phone' ? 46 : 26) : 12;
                lpFrame.style.width = d.w + 'px';
                lpFrame.style.height = d.h + 'px';
                lpFrame.style.borderRadius = radius + 'px';
                lpFrame.style.transform = 'scale(' + scale + ')';
                lpScaler.style.width = (d.w * scale) + 'px';
                lpScaler.style.height = (d.h * scale) + 'px';
                if (lpMeta) {
                    lpMeta.textContent = d.label + ' · ' + d.w + ' × ' + d.h;
                }
            }
            function lpRenderDevices() {
                if (!lpDevicesWrap) {
                    return;
                }
                lpDevicesWrap.innerHTML = '';
                Object.keys(LP_DEVICES).forEach(function (key) {
                    const d = LP_DEVICES[key];
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'll-lp-device-btn' + (key === lpDevice ? ' is-active' : '');
                    const icon = key === 'phone' ? 'bi-phone' : (key === 'tablet' ? 'bi-tablet' : 'bi-display');
                    btn.innerHTML = '<i class="bi ' + icon + '"></i> ' + d.label;
                    btn.addEventListener('click', function () { lpDevice = key; lpRenderDevices(); lpFit(); });
                    lpDevicesWrap.appendChild(btn);
                });
            }
            lpRenderDevices();
            lpFit();
            window.addEventListener('resize', lpFit);

            // ---- Merged "Links" form: platform dropdown + Simple Icons picker ----
            function llIconCdn(slug, hex) {
                return 'https://cdn.simpleicons.org/' + encodeURIComponent(slug) + (hex ? '/' + encodeURIComponent(hex) : '');
            }
            // Wire every unwired link form on the page — there can be two at once
            // (the add panel and the edit modal), so a single querySelector isn't enough.
            function wireLinkForm() {
                document.querySelectorAll('[data-ll-link-form]').forEach(function (lform) {
                    if (lform.dataset.llWired !== 'true') {
                        wireOneLinkForm(lform);
                    }
                });
            }
            function wireOneLinkForm(lform) {
                lform.dataset.llWired = 'true';

                const platform = lform.querySelector('[data-ll-platform]');
                const titleInput = lform.querySelector('.ll-lf-title');
                const iconPicker = lform.querySelector('[data-ll-icon-picker]');
                const search = lform.querySelector('[data-ll-icon-search]');
                const results = lform.querySelector('[data-ll-icon-results]');
                const preview = lform.querySelector('[data-ll-icon-preview]');
                const hidden = lform.querySelector('[data-ll-icon-value]');
                const favicon = lform.querySelector('[data-ll-favicon]');
                const faviconWrap = lform.querySelector('.ll-lf-favicon-wrap');
                if (!platform || !hidden) {
                    return;
                }

                let library = [];
                try {
                    const raw = lform.querySelector('[data-ll-icon-library]');
                    library = raw ? JSON.parse(raw.textContent || '[]') : [];
                } catch (e) { library = []; }
                const bySlug = {};
                library.forEach(function (i) { bySlug[i.slug] = i; });

                function parseVal(v) {
                    if (!v || v.indexOf('si:') !== 0) { return null; }
                    const parts = v.slice(3).split(':');
                    return { slug: (parts[0] || '').toLowerCase(), hex: parts[1] || '' };
                }
                function renderPreview() {
                    const parsed = parseVal(hidden.value);
                    if (parsed && parsed.slug) {
                        preview.innerHTML = '<img alt="" src="' + llIconCdn(parsed.slug, parsed.hex) + '">';
                    } else {
                        preview.innerHTML = '<i class="bi bi-image"></i>';
                    }
                }
                function setIcon(slug) {
                    // Store only the icon identity; colour + on/off live in theme settings.
                    slug = (slug || '').toLowerCase().replace(/[^a-z0-9-]/g, '');
                    hidden.value = slug ? ('si:' + slug) : '';
                    renderPreview();
                }
                function markActive(chip) {
                    results.querySelectorAll('.ll-lf-iconchip').forEach(function (c) { c.classList.toggle('is-active', c === chip); });
                }
                function renderResults(q) {
                    q = (q || '').trim().toLowerCase();
                    results.innerHTML = '';
                    let matches = library;
                    if (q) {
                        matches = library.filter(function (i) { return i.slug.indexOf(q) !== -1 || i.label.toLowerCase().indexOf(q) !== -1; });
                    }
                    matches.slice(0, 60).forEach(function (i) {
                        const chip = document.createElement('button');
                        chip.type = 'button';
                        chip.className = 'll-lf-iconchip';
                        chip.title = i.label;
                        chip.innerHTML = '<img alt="' + i.label + '" src="' + llIconCdn(i.slug, i.hex) + '">';
                        chip.addEventListener('click', function () { setIcon(i.slug); markActive(chip); });
                        results.appendChild(chip);
                    });
                    const slugified = q.replace(/[^a-z0-9-]/g, '');
                    if (slugified && !bySlug[slugified]) {
                        const ft = document.createElement('button');
                        ft.type = 'button';
                        ft.className = 'll-lf-icon-freetype';
                        ft.innerHTML = '<img alt="" src="' + llIconCdn(slugified, '') + '" onerror="this.style.display=\'none\'"> Use &ldquo;' + slugified + '&rdquo; from Simple Icons';
                        ft.addEventListener('click', function () { setIcon(slugified); });
                        results.appendChild(ft);
                    }
                }
                function syncMode() {
                    const isCustom = platform.value === 'custom';
                    const useFavicon = !!(favicon && favicon.checked);
                    iconPicker.classList.toggle('ll-lf-iconpicker-hidden', !isCustom || useFavicon);
                    if (faviconWrap) { faviconWrap.classList.toggle('ll-lf-iconpicker-hidden', !isCustom); }
                    if (!isCustom) {
                        if (favicon) { favicon.checked = false; }
                        const opt = platform.options[platform.selectedIndex];
                        setIcon(platform.value);
                        if (titleInput && !titleInput.value && opt) { titleInput.value = opt.getAttribute('data-label') || ''; }
                    }
                }

                platform.addEventListener('change', syncMode);
                if (favicon) { favicon.addEventListener('change', syncMode); }
                if (search) { search.addEventListener('input', function () { renderResults(search.value); }); }

                const existing = parseVal(hidden.value);
                if (existing && existing.slug && bySlug[existing.slug]) { platform.value = existing.slug; }
                renderResults('');
                renderPreview();
                syncMode();
            }

            document.addEventListener('contentLoaded', wireLinkForm);
            wireLinkForm();

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

                if (chosenIcon && option.dataset.icon) {
                    chosenIcon.innerHTML = '<i class="' + option.dataset.icon + '"></i>';
                }

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
                    showAddView('config');
                });
            });

            if (addBack) {
                addBack.addEventListener('click', function () {
                    showAddView('browse');
                });
            }

            document.querySelectorAll('[data-edit-link]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openEditModal(btn.dataset.id, btn.dataset.typename, btn.dataset.title);
                });
            });

            document.querySelectorAll('[data-edit-close]').forEach(function (el) {
                el.addEventListener('click', closeEditModal);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && editModal && !editModal.hidden) {
                    closeEditModal();
                }
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

            function initLinkSortable() {
                if (!sortableList) return;
                // Sortable.min.js is loaded at the end of the layout body, i.e.
                // AFTER this content script on a full page load. When it isn't
                // ready yet, wait for the window 'load' event and retry. On an
                // HTMX swap Sortable is already present, so this runs inline.
                if (!window.Sortable) {
                    window.addEventListener('load', initLinkSortable, { once: true });
                    return;
                }
                if (Sortable.get(sortableList)) return; // guard against double-init on HTMX re-runs
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
            initLinkSortable();

            const initialOption = blockOptions.find(function (option) {
                return option.dataset.typeid === selectedTypeInput.value;
            }) || blockOptions[0];

            if (initialOption) {
                selectBlock(initialOption);
            }

            // If the add attempt bounced back with validation errors, reopen the
            // config step so the user sees them in context instead of the catalogue.
            @if($errors->any())
                showAddView('config');
            @endif
        }
    };

    window.LivelatchLinksManager.init();
</script>
@endsection
