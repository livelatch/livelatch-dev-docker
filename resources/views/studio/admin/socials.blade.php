@extends('layouts.sidebar')

@section('content')
<style data-ll-admin-socials-style>
    .ll-admin-socials { display: grid; gap: 18px; }
    .ll-admin-socials .ll-as-header h2 { margin: 0 0 4px; color: var(--ll-text); }
    .ll-admin-socials .ll-as-header p { margin: 0; color: var(--ll-muted); max-width: 760px; }
    .ll-as-row {
        border: 1px solid var(--ll-border);
        border-radius: 16px;
        background: var(--ll-surface-solid);
        padding: 16px 18px;
        display: grid;
        gap: 12px;
    }
    .ll-as-row-top { display: flex; align-items: center; gap: 12px; }
    .ll-as-icon {
        width: 42px; height: 42px; flex: 0 0 auto;
        display: grid; place-items: center; border-radius: 12px;
        color: #fff; font-size: 1.1rem;
        background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
    }
    .ll-as-row-top h3 { margin: 0; font-size: 1rem; }
    .ll-as-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .ll-as-fields label { font-size: 0.82rem; font-weight: 600; color: var(--ll-muted); display: block; margin-bottom: 4px; }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <form method="POST" action="{{ route('admin.socials.update') }}" class="ll-admin-socials">
        @csrf

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="ll-as-header">
                <h2>Livelatch Social Links</h2>
                <p>Paste the profile link (used for the Follow button) and an optional featured post URL (embedded on the socials page) for each platform. Tick "Show" to display it on <a href="{{ route('studio.socials') }}">/studio/socials</a>.</p>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        </div>

        @if(session('status') === 'socials-saved')
            <div class="alert alert-success mb-0">Social links saved.</div>
        @elseif(session('status') === 'socials-error')
            <div class="alert alert-danger mb-0">Could not save. Check that Supabase is configured and try again.</div>
        @endif

        @foreach($platforms as $key => $meta)
            @php($row = $stored[$key] ?? [])
            <div class="ll-as-row">
                <div class="ll-as-row-top">
                    <span class="ll-as-icon"><i class="{{ $meta['icon'] }}"></i></span>
                    <h3>{{ $meta['name'] }}</h3>
                    <div class="form-check form-switch ms-auto mb-0">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="active_{{ $key }}"
                               name="socials[{{ $key }}][is_active]" value="1"
                               @checked(!empty($row['is_active']))>
                        <label class="form-check-label" for="active_{{ $key }}">Show</label>
                    </div>
                </div>
                <div class="ll-as-fields">
                    <div>
                        <label for="handle_{{ $key }}">Handle / display name</label>
                        <input type="text" class="form-control" id="handle_{{ $key }}"
                               name="socials[{{ $key }}][handle]"
                               value="{{ $row['handle'] ?? '' }}" placeholder="@livelatch">
                    </div>
                    <div>
                        <label for="profile_{{ $key }}">Profile URL (Follow button)</label>
                        <input type="url" class="form-control" id="profile_{{ $key }}"
                               name="socials[{{ $key }}][profile_url]"
                               value="{{ $row['profile_url'] ?? '' }}" placeholder="https://...">
                    </div>
                    @if($meta['embed'] !== 'none')
                        <div>
                            <label for="post_{{ $key }}">Featured post URL (embed)</label>
                            <input type="url" class="form-control" id="post_{{ $key }}"
                                   name="socials[{{ $key }}][featured_post_url]"
                                   value="{{ $row['featured_post_url'] ?? '' }}" placeholder="https://... (a single post)">
                        </div>
                    @elseif(($meta['widget'] ?? null) === 'discord')
                        <div>
                            <label for="widget_{{ $key }}">Discord Server ID (native widget)</label>
                            <input type="text" inputmode="numeric" class="form-control" id="widget_{{ $key }}"
                                   name="socials[{{ $key }}][widget_id]"
                                   value="{{ $row['widget_id'] ?? '' }}" placeholder="e.g. 123456789012345678">
                            <p class="text-muted small mt-1 mb-0">Enable <strong>Server Settings → Widget → Enable Server Widget</strong> in Discord first (no API key needed). The Profile URL above can be your invite link as a fallback.</p>
                        </div>
                    @else
                        <div class="d-flex align-items-end">
                            <p class="text-muted small mb-2">No post embed for {{ $meta['name'] }} — shows a follow card only.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
        </div>
    </form>
</div>
@endsection
