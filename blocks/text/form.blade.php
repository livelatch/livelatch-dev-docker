<label for='text' class='form-label'>{{__('messages.Text to display')}}</label>
<textarea class="form-control @if(env('ALLOW_USER_HTML') === true) ckeditor @endif" name="text" rows="6">{{ $title ?? '' }}</textarea>
{{--
    This form is injected into the Links editor via `innerHTML`, so any inline
    <script> here would never execute. The rich-text editor is initialised by
    the shared bootstrap (assets/js/ll-ckeditor-init.js) — the Links page calls
    window.llInitCkeditors() after each form injection.
--}}
