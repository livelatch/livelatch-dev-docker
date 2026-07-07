/**
 * Shared CKEditor bootstrap for Livelatch Studio.
 *
 * Both the Page editor (studio/page.blade.php, top-level markup) and the Links
 * editor (studio/links.blade.php, block config forms injected via innerHTML)
 * need the same ClassicEditor configuration. Scripts injected through
 * `element.innerHTML = ...` never execute, so the block form can't init the
 * editor itself — instead the loader calls `window.llInitCkeditors()` after
 * each injection. Keeping the ~55-line config in one place avoids drift.
 *
 * Usage:
 *   window.llCkeditorSrc = '.../assets/external-dependencies/ckeditor.js';
 *   window.llInitCkeditors();            // scan the whole document
 *   window.llInitCkeditors(someElement); // scan a subtree
 *
 * The CKEditor bundle is loaded lazily on first use, and each `.ckeditor`
 * textarea is only initialised once (guarded by `data-ck-ready`), so repeat
 * calls (e.g. every time a block is re-selected) are safe.
 */
(function () {
    'use strict';

    var ckLoading = null;

    function loadCkeditor() {
        if (window.ClassicEditor) {
            return Promise.resolve();
        }
        if (ckLoading) {
            return ckLoading;
        }
        if (!window.llCkeditorSrc) {
            return Promise.reject(new Error('CKEditor source not configured (window.llCkeditorSrc).'));
        }

        ckLoading = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = window.llCkeditorSrc;
            script.onload = function () { resolve(); };
            script.onerror = function () {
                ckLoading = null;
                reject(new Error('Failed to load CKEditor bundle.'));
            };
            document.head.appendChild(script);
        });

        return ckLoading;
    }

    var CONFIG = {
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
            addTargetToExternalLinks: true, // Open external links in a new tab
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
    };

    /**
     * Initialise every un-initialised `.ckeditor` textarea within `root`
     * (defaults to the whole document).
     */
    window.llInitCkeditors = function (root) {
        var scope = root && root.querySelectorAll ? root : document;
        var fields = Array.prototype.slice.call(scope.querySelectorAll('textarea.ckeditor'));

        // Mark synchronously so overlapping calls don't double-init the same field.
        var pending = fields.filter(function (field) {
            if (field.dataset.ckReady === 'true') {
                return false;
            }
            field.dataset.ckReady = 'true';
            return true;
        });

        if (!pending.length) {
            return;
        }

        loadCkeditor().then(function () {
            pending.forEach(function (field) {
                window.ClassicEditor.create(field, CONFIG).catch(function (error) {
                    // Let a failed field be retried on a later call.
                    field.dataset.ckReady = '';
                    console.error(error);
                });
            });
        }).catch(function (error) {
            pending.forEach(function (field) { field.dataset.ckReady = ''; });
            console.error(error);
        });
    };
})();
