<div class="alert alert-info" role="alert" style="border-radius:12px;">
    <i class="bi bi-collection-fill"></i>
    This block shows your <strong>LatchDeck</strong> collectible cards on your public profile.
    Add it to switch the section <strong>on</strong>; remove it from your links to switch it <strong>off</strong>.
</div>

<label for='title' class='form-label'>{{ __('messages.Custom Title') ?? 'Section title' }}</label>
<input type='text' name='title' value='{{ $title ?: "LatchDeck" }}' class='form-control' placeholder="LatchDeck" />
<span class='small text-muted'>Optional heading shown above your cards.</span>
