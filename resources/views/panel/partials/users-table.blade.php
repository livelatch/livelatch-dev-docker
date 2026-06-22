@php
    $state = ['search' => $search, 'perPage' => $perPage];

    // Renders a sortable column header that swaps the table via HTMX.
    $sortHeader = function (string $label, string $col) use ($state, $sort, $direction) {
        $isActive = $sort === $col;
        $nextDir = ($isActive && $direction === 'asc') ? 'desc' : 'asc';
        $url = route('showUsers', array_merge($state, ['sort' => $col, 'direction' => $nextDir]));
        $ico = !$isActive ? 'bi-arrow-down-up' : ($direction === 'asc' ? 'bi-sort-down-alt' : 'bi-sort-up-alt');
        return '<a class="' . ($isActive ? 'active' : '') . '"'
            . ' hx-get="' . e($url) . '"'
            . ' hx-target="#ll-users-table" hx-swap="outerHTML"'
            . ' hx-include="[name=\'search\'],[name=\'perPage\']"'
            . ' hx-indicator="#ll-users-table">'
            . e($label) . ' <i class="bi ' . $ico . ' ll-sort-ico"></i></a>';
    };

    $registerAuth = env('REGISTER_AUTH');
@endphp

<div id="ll-users-table" class="ll-u-card">
    <div class="ll-u-table-scroll">
        <table class="ll-u-table">
            <thead>
                <tr>
                    <th>{!! $sortHeader(__('messages.ID'), 'id') !!}</th>
                    <th>{!! $sortHeader(__('messages.Name'), 'name') !!}</th>
                    <th>{!! $sortHeader(__('messages.E-Mail'), 'email') !!}</th>
                    <th>{!! $sortHeader(__('messages.Page'), 'littlelink_name') !!}</th>
                    <th>{{ __('messages.Roles') }}</th>
                    <th>{{ __('messages.Links') }}</th>
                    <th>{{ __('messages.Clicks') }}</th>
                    <th>{!! $sortHeader(__('messages.E-Mail'), 'email_verified_at') !!}</th>
                    <th>{!! $sortHeader(__('messages.Status'), 'block') !!}</th>
                    <th>{!! $sortHeader(__('messages.Created at'), 'created_at') !!}</th>
                    <th>{!! $sortHeader(__('messages.Last seen'), 'updated_at') !!}</th>
                    <th class="text-end">{{ __('messages.Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $row)
                    @php
                        $isPrimaryAdmin = $row->role === 'admin' && (int) $row->id === 1;
                        $roles = $row->roles->sortBy('sort_order');
                    @endphp
                    <tr>
                        <td class="ll-u-id">{{ $row->id }}</td>
                        <td class="ll-u-name">{{ $row->name }}</td>
                        <td class="ll-u-email">{{ $row->email }}</td>
                        <td>
                            @if($row->littlelink_name)
                                <a class="ll-u-page-link" target="_blank" rel="noopener"
                                   href="{{ url('') }}/{{ '@' . $row->littlelink_name }}">
                                    <i class="bi bi-box-arrow-up-right"></i> {{ $row->littlelink_name }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($roles->isEmpty())
                                @if($row->role)
                                    <span class="ll-badge ll-badge-muted is-static">{{ ucfirst($row->role) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            @else
                                <div class="ll-role-chips">
                                    @foreach($roles as $role)
                                        <span class="ll-role-chip" style="background-color: {{ $role->color ?: '#6b7280' }};">{{ $role->label }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>{{ $row->links_count }}</td>
                        <td>{{ (int) $row->links_sum_click_number }}</td>
                        <td>
                            @if($registerAuth === 'auth' || ($row->role === 'admin' && $row->email_verified_at))
                                <span class="text-muted">—</span>
                            @elseif($row->email_verified_at)
                                <a class="ll-badge ll-badge-green"
                                   hx-get="{{ route('verifyUser', ['verify' => 'false', 'id' => $row->id]) }}"
                                   hx-target="#ll-users-table" hx-swap="outerHTML"
                                   hx-include="[name='search'],[name='perPage']"
                                   hx-indicator="#ll-users-table"
                                   title="{{ __('messages.Verified') }} — click to revoke">{{ __('messages.Verified') }}</a>
                            @else
                                <a class="ll-badge ll-badge-red"
                                   hx-get="{{ route('verifyUser', ['verify' => 'true', 'id' => $row->id]) }}"
                                   hx-target="#ll-users-table" hx-swap="outerHTML"
                                   hx-include="[name='search'],[name='perPage']"
                                   hx-indicator="#ll-users-table"
                                   title="{{ __('messages.Pending') }} — click to verify">{{ __('messages.Pending') }}</a>
                            @endif
                        </td>
                        <td>
                            @if($isPrimaryAdmin)
                                <span class="text-muted">—</span>
                            @elseif($row->block === 'yes')
                                <a class="ll-badge ll-badge-red"
                                   hx-get="{{ route('blockUser', ['block' => $row->block, 'id' => $row->id]) }}"
                                   hx-target="#ll-users-table" hx-swap="outerHTML"
                                   hx-include="[name='search'],[name='perPage']"
                                   hx-indicator="#ll-users-table"
                                   title="Blocked — click to approve">{{ __('messages.Pending') }}</a>
                            @else
                                <a class="ll-badge ll-badge-green"
                                   hx-get="{{ route('blockUser', ['block' => $row->block, 'id' => $row->id]) }}"
                                   hx-target="#ll-users-table" hx-swap="outerHTML"
                                   hx-include="[name='search'],[name='perPage']"
                                   hx-indicator="#ll-users-table"
                                   title="Approved — click to block">{{ __('messages.Approved') }}</a>
                            @endif
                        </td>
                        <td>{{ $row->created_at ? $row->created_at->format('d/m/y') : '' }}</td>
                        <td>{{ $row->updated_at ? $row->updated_at->diffForHumans() : '' }}</td>
                        <td class="text-end">
                            @if($isPrimaryAdmin)
                                <span class="text-muted">—</span>
                            @else
                                @php
                                    $canImpersonate = Auth::user()->id !== $row->id
                                        && $row->block !== 'yes'
                                        && ($row->email_verified_at != '' || $registerAuth === 'auth');
                                @endphp
                                <div class="ll-u-actions">
                                    <a class="ll-u-act view" href="{{ route('showLinksUser', $row->id) }}"
                                       title="{{ __('messages.tt.All links') }}"><i class="bi bi-link-45deg"></i></a>
                                    <a class="ll-u-act edit" href="{{ route('showUser', $row->id) }}"
                                       title="{{ __('messages.tt.Edit') }}"><i class="bi bi-pencil"></i></a>
                                    <a class="ll-u-act imp {{ $canImpersonate ? '' : 'is-disabled' }}"
                                       @if($canImpersonate) href="{{ route('authAsID', $row->id) }}" @endif
                                       title="{{ __('messages.tt.Impersonate') }}"><i class="bi bi-people"></i></a>
                                    <a class="ll-u-act del" style="cursor:pointer;"
                                       hx-post="{{ route('deleteTableUser', ['id' => $row->id]) }}"
                                       hx-target="#ll-users-table" hx-swap="outerHTML"
                                       hx-include="[name='search'],[name='perPage']"
                                       hx-indicator="#ll-users-table"
                                       data-ll-confirm="{{ __('messages.confirm.delete.user') }}"
                                       title="{{ __('messages.tt.Delete') }}"><i class="bi bi-trash"></i></a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">
                            <div class="ll-u-empty">
                                <i class="bi bi-search"></i>
                                @if($search !== '')
                                    No users match “{{ $search }}”.
                                @else
                                    No users yet.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ll-u-foot">
        <div class="ll-u-count">
            @if($users->total() > 0)
                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
            @else
                0 users
            @endif
        </div>
        <div class="ll-u-pager">
            <a class="pg {{ $users->onFirstPage() ? 'is-disabled' : '' }}"
               @if(!$users->onFirstPage())
                   hx-get="{{ $users->previousPageUrl() }}"
                   hx-target="#ll-users-table" hx-swap="outerHTML" hx-indicator="#ll-users-table"
               @endif>
                <i class="bi bi-chevron-left"></i> Prev
            </a>
            <span class="pg-info">Page {{ $users->currentPage() }} of {{ max($users->lastPage(), 1) }}</span>
            <a class="pg {{ $users->hasMorePages() ? '' : 'is-disabled' }}"
               @if($users->hasMorePages())
                   hx-get="{{ $users->nextPageUrl() }}"
                   hx-target="#ll-users-table" hx-swap="outerHTML" hx-indicator="#ll-users-table"
               @endif>
                Next <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</div>
