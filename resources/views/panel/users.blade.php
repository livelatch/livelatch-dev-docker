@extends('layouts.sidebar')

@section('content')
<style data-ll-admin-users-style>
    .ll-users { display: grid; gap: 18px; }
    .ll-users .ll-u-header h2 { margin: 0 0 4px; color: var(--ll-text); display: flex; align-items: center; gap: 8px; }
    .ll-users .ll-u-header p { margin: 0; color: var(--ll-muted); max-width: 760px; }

    /* Toolbar */
    .ll-u-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
    }
    .ll-u-search { position: relative; flex: 1 1 280px; min-width: 220px; }
    .ll-u-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--ll-muted); pointer-events: none; }
    .ll-u-search input {
        width: 100%; height: 44px; padding: 0 14px 0 40px;
        border: 1px solid var(--ll-border); border-radius: 12px;
        background: var(--ll-surface-solid); color: var(--ll-text); font-size: .92rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ll-u-search input:focus { outline: none; border-color: var(--ll-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ll-primary) 25%, transparent); }
    .ll-u-perpage {
        height: 44px; padding: 0 12px; border-radius: 12px;
        border: 1px solid var(--ll-border); background: var(--ll-surface-solid);
        color: var(--ll-text); font-size: .9rem; cursor: pointer;
    }
    .ll-u-add {
        height: 44px; padding: 0 18px; border-radius: 12px; border: none;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        color: #04122b; font-weight: 700; font-size: .9rem; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px; text-decoration: none; white-space: nowrap;
    }
    .ll-u-add:hover { filter: brightness(1.05); color: #04122b; }

    /* Alpha gate toggle */
    .ll-alpha-gate {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;
        padding: 14px 16px; border: 1px solid var(--ll-border); border-radius: 14px;
        background: var(--ll-surface-solid);
    }
    .ll-alpha-gate .ll-ag-text strong { color: var(--ll-text); display: block; }
    .ll-alpha-gate .ll-ag-text span { color: var(--ll-muted); font-size: .86rem; }
    .ll-ag-switch { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .ll-ag-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
    .ll-ag-track {
        width: 46px; height: 26px; border-radius: 999px; position: relative;
        background: color-mix(in srgb, var(--ll-text) 22%, transparent); transition: background .16s ease;
    }
    .ll-ag-track::after {
        content: ""; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px;
        border-radius: 999px; background: #fff; transition: transform .16s ease;
    }
    .ll-ag-switch input:checked + .ll-ag-track { background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2)); }
    .ll-ag-switch input:checked + .ll-ag-track::after { transform: translateX(20px); }
    .ll-ag-state { font-weight: 700; color: var(--ll-text); min-width: 26px; }

    /* Card + table */
    .ll-u-card {
        border: 1px solid var(--ll-border); border-radius: 16px;
        background: var(--ll-surface-solid); overflow: hidden;
    }
    .ll-u-table-scroll { overflow-x: auto; }
    .ll-u-table { width: 100%; border-collapse: collapse; font-size: .88rem; min-width: 900px; }
    .ll-u-table thead th {
        text-align: left; padding: 12px 14px; white-space: nowrap;
        color: var(--ll-muted); font-weight: 600; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid var(--ll-border);
        background: color-mix(in srgb, var(--ll-text) 4%, transparent);
        position: sticky; top: 0; z-index: 1;
    }
    .ll-u-table thead th a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }
    .ll-u-table thead th a:hover { color: var(--ll-text); }
    .ll-u-table thead th .ll-sort-ico { opacity: .35; font-size: .8rem; }
    .ll-u-table thead th a.active .ll-sort-ico { opacity: 1; color: var(--ll-primary); }
    .ll-u-table tbody td { padding: 11px 14px; border-bottom: 1px solid color-mix(in srgb, var(--ll-border) 60%, transparent); color: var(--ll-text); vertical-align: middle; white-space: nowrap; }
    .ll-u-table tbody tr:last-child td { border-bottom: none; }
    .ll-u-table tbody tr:hover { background: color-mix(in srgb, var(--ll-primary) 6%, transparent); }
    .ll-u-id { color: var(--ll-muted); font-variant-numeric: tabular-nums; }
    .ll-u-name { font-weight: 600; }
    .ll-u-email { color: var(--ll-muted); }
    .ll-u-page-link { color: var(--ll-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .ll-u-page-link:hover { text-decoration: underline; }

    /* Badges */
    .ll-badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-size: .72rem; font-weight: 600; line-height: 1.4; cursor: pointer; }
    .ll-badge.is-static { cursor: default; }
    .ll-badge-green { background: color-mix(in srgb, #23a55a 18%, transparent); color: #23a55a; }
    .ll-badge-red { background: color-mix(in srgb, #ef4444 18%, transparent); color: #ef4444; }
    .ll-badge-muted { background: color-mix(in srgb, var(--ll-text) 10%, transparent); color: var(--ll-muted); }
    .ll-role-chips { display: flex; flex-wrap: wrap; gap: 4px; max-width: 220px; white-space: normal; }
    .ll-role-chip { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: .7rem; font-weight: 600; color: #fff; }

    /* Row action buttons */
    .ll-u-actions { display: inline-flex; gap: 6px; }
    .ll-u-act {
        width: 32px; height: 32px; border-radius: 9px; border: 1px solid var(--ll-border);
        background: var(--ll-surface-solid); color: var(--ll-text);
        display: inline-grid; place-items: center; cursor: pointer; text-decoration: none;
        transition: background .12s ease, color .12s ease, border-color .12s ease;
    }
    .ll-u-act:hover { border-color: transparent; }
    .ll-u-act.view:hover { background: #23a55a; color: #fff; }
    .ll-u-act.edit:hover { background: var(--ll-primary); color: #fff; }
    .ll-u-act.imp:hover { background: #6366f1; color: #fff; }
    .ll-u-act.del:hover { background: #ef4444; color: #fff; }
    .ll-u-act.is-disabled { opacity: .4; pointer-events: none; }

    /* Footer / pagination */
    .ll-u-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--ll-border); }
    .ll-u-count { color: var(--ll-muted); font-size: .82rem; }
    .ll-u-pager { display: inline-flex; align-items: center; gap: 8px; }
    .ll-u-pager .pg {
        height: 34px; min-width: 34px; padding: 0 12px; border-radius: 9px;
        border: 1px solid var(--ll-border); background: var(--ll-surface-solid);
        color: var(--ll-text); font-size: .85rem; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    }
    .ll-u-pager .pg:hover:not(.is-disabled) { border-color: var(--ll-primary); color: var(--ll-primary); }
    .ll-u-pager .pg.is-disabled { opacity: .4; pointer-events: none; }
    .ll-u-pager .pg-info { color: var(--ll-muted); font-size: .82rem; }

    .ll-u-empty { padding: 48px 20px; text-align: center; color: var(--ll-muted); }
    .ll-u-empty i { font-size: 1.8rem; display: block; margin-bottom: 8px; opacity: .6; }

    /* Keep the table interactive but dim during HTMX swaps */
    #ll-users-table.htmx-request { opacity: .55; transition: opacity .15s ease; }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="ll-users"
         hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>

        <div class="ll-u-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2><i class="bi bi-people"></i> {{ __('messages.Manage Users') }}</h2>
                <p>Search, sort and manage every Livelatch account. Click a status badge to toggle it, or use the row actions to view links, edit, impersonate or delete.</p>
            </div>
            <a href="{{ url('') }}/admin/new-user" class="ll-u-add">
                <i class="bi bi-plus-lg"></i> {{ __('messages.Add new user') }}
            </a>
        </div>

        @php $alphaGateOn = \App\Models\AppSetting::getBool('alpha_gate_enabled', true); @endphp
        <form method="POST" action="{{ route('admin.alphaGate') }}" class="ll-alpha-gate">
            @csrf
            <div class="ll-ag-text">
                <strong>Alpha gating</strong>
                <span>When on, new sign-ups are held on a waiting screen until you approve them (remove their “Not approved” role below).</span>
            </div>
            <label class="ll-ag-switch">
                <input type="checkbox" name="enabled" value="1" {{ $alphaGateOn ? 'checked' : '' }}
                       onchange="this.form.submit()">
                <span class="ll-ag-track"></span>
                <span class="ll-ag-state">{{ $alphaGateOn ? 'On' : 'Off' }}</span>
            </label>
        </form>

        <div class="ll-u-toolbar">
            <label class="ll-u-search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="Search by name, email, page or role…"
                       autocomplete="off"
                       hx-get="{{ route('showUsers') }}"
                       hx-trigger="input changed delay:350ms, search"
                       hx-target="#ll-users-table"
                       hx-swap="outerHTML"
                       hx-include="[name='perPage']"
                       hx-indicator="#ll-users-table">
            </label>

            <select name="perPage" class="ll-u-perpage"
                    hx-get="{{ route('showUsers') }}"
                    hx-trigger="change"
                    hx-target="#ll-users-table"
                    hx-swap="outerHTML"
                    hx-include="[name='search']"
                    hx-indicator="#ll-users-table">
                @foreach([25, 50, 100, 250, 500] as $opt)
                    <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }} / page</option>
                @endforeach
            </select>
        </div>

        @include('panel.partials.users-table')
    </div>
</div>

@push('sidebar-scripts')
<script>
    // Confirm before HTMX-deleting a user (htmx:confirm gives us an async hook).
    document.body.addEventListener('htmx:confirm', function (e) {
        if (e.target.matches('[data-ll-confirm]')) {
            e.preventDefault();
            if (window.confirm(e.target.getAttribute('data-ll-confirm'))) {
                e.detail.issueRequest(true);
            }
        }
    });
</script>
@endpush

@endsection
