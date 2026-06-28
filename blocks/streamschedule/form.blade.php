<div class="alert alert-info" role="alert" style="border-radius:12px;">
    <i class="bi bi-calendar-week-fill"></i>
    This block shows your <strong>upcoming streams</strong> on your public profile, with a one-tap calendar subscribe.
    Manage your streams in the <strong>Stream Schedule</strong> app. Add it to switch the section <strong>on</strong>; remove it to switch it <strong>off</strong>.
</div>

<label for='title' class='form-label'>{{ __('messages.Custom Title') ?? 'Section title' }}</label>
<input type='text' name='title' value='{{ $title ?: "Stream Schedule" }}' class='form-control' placeholder="Stream Schedule" />

<label for='days' class='form-label' style="margin-top:12px;">Days to show ahead</label>
@php $ssDays = (int) ($days ?? 7); @endphp
<select name='days' class='form-control'>
    <option value="3" {{ $ssDays === 3 ? 'selected' : '' }}>Next 3 days</option>
    <option value="7" {{ $ssDays === 7 ? 'selected' : '' }}>Next 7 days</option>
    <option value="14" {{ $ssDays === 14 ? 'selected' : '' }}>Next 14 days</option>
    <option value="30" {{ $ssDays === 30 ? 'selected' : '' }}>Next 30 days</option>
</select>
<span class='small text-muted'>How far ahead the schedule shows on your Livelatch page. Set up streams in <a href="{{ url('/studio/stream-schedule') }}">Stream Schedule</a> (Pro).</span>

@php $ssShowEsrb = !empty($show_esrb); @endphp
<div class="form-check form-switch" style="margin-top:16px;">
    <input class="form-check-input" type="checkbox" role="switch" id="ss-show-esrb" name="show_esrb" value="1" {{ $ssShowEsrb ? 'checked' : '' }}>
    <label class="form-check-label" for="ss-show-esrb" style="font-weight:600;">Show ESRB ratings</label>
</div>
<span class='small text-muted'>When on, a game's ESRB rating (e.g. &ldquo;Mature 17+&rdquo;) shows next to its title on the game tag. Off by default.</span>
