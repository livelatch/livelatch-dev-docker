<div class="alert alert-info" role="alert" style="border-radius:12px;">
    <i class="bi bi-calendar-week-fill"></i>
    This block shows your <strong>next 7 days of streams</strong> on your public profile, with a one-tap calendar subscribe.
    Manage your streams in the <strong>Stream Schedule</strong> app. Add it to switch the section <strong>on</strong>; remove it to switch it <strong>off</strong>.
</div>

<label for='title' class='form-label'>{{ __('messages.Custom Title') ?? 'Section title' }}</label>
<input type='text' name='title' value='{{ $title ?: "Stream Schedule" }}' class='form-control' placeholder="Stream Schedule" />
<span class='small text-muted'>Set up your streams in <a href="{{ url('/studio/stream-schedule') }}">Stream Schedule</a> (Pro).</span>
