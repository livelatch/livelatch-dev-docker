<label for='text' class='form-label'>{{__('messages.Text to display')}}</label>
<textarea class="form-control @if(env('ALLOW_USER_HTML') === true) ckeditor @endif" name="text" rows="6">{{ $title ?? '' }}</textarea>
@if(env('ALLOW_USER_HTML') === true)
<script>
// Loaded via HTMX/AJAX swap: an innerHTML-inserted <script src> does not block
// the following inline script, so ClassicEditor could be undefined. Load the
// editor dynamically (onload fires reliably for created scripts) and init once.
(function () {
    function initCkeditor() {
        var el = document.querySelector('.ckeditor');
        if (!el || el.dataset.ckReady === '1' || typeof ClassicEditor === 'undefined') return;
        el.dataset.ckReady = '1';
        ClassicEditor
            .create(el, {
            toolbar: {
                items: [
                    'exportPDF', 'exportWord', '|',
                    'findAndReplace', 'selectAll', '|',
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'blockQuote', '|',
                    'specialCharacters', 'horizontalLine', '|',
                    'textPartLanguage', '|',
                ],
                shouldNotGroupWhenFull: true
            },
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true
            },
            fontSize: {
                options: [10, 12, 14, 'default', 18, 20, 22],
                supportAllValues: true
            },
            link: {
                addTargetToExternalLinks: true, // Add this option to open external links in a new tab
                defaultProtocol: 'http://',
                decorators: {
                    addTargetToExternalLinks: {
                        mode: 'manual',
                        label: 'Open in new tab',
                        attributes: {
                            target: '_blank',
                            rel: 'noopener noreferrer'
                        }
                    }
                }
            }
            })
            .catch(function (error) { console.error(error); });
    }
    if (typeof ClassicEditor !== 'undefined') {
        initCkeditor();
    } else {
        var s = document.createElement('script');
        s.src = '{{ asset('assets/external-dependencies/ckeditor.js') }}';
        s.onload = initCkeditor;
        s.onerror = function () { console.error('CKEditor failed to load.'); };
        document.head.appendChild(s);
    }
})();
</script>

@endif
