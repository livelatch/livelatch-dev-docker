<?php use App\Models\UserData; ?>
@extends('layouts.sidebar')

@section('content')

<div class="container-fluid content-inner mt-n5 py-0 ll-appearance">
    <style data-ll-appearance-style>
        .ll-appearance { display: grid; gap: 18px; }

        .ll-appearance-head h1 {
            margin: 0 0 6px;
            color: var(--ll-text);
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            line-height: 1.1;
        }
        .ll-appearance-head p { color: var(--ll-muted); margin: 0; }

        .ll-appearance-card {
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-radius);
            background: var(--ll-surface-solid);
            box-shadow: var(--ll-shadow-soft);
            padding: clamp(18px, 3vw, 28px);
        }

        .ll-appearance-grid { display: grid; gap: 20px; max-width: 680px; }

        .ll-field { display: grid; gap: 8px; }
        .ll-field > label,
        .ll-field .form-label {
            color: var(--ll-text);
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
        }

        .ll-appearance .ll-input {
            width: 100%;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius);
            background: var(--ll-surface-solid);
            color: var(--ll-text);
            padding: 11px 14px;
            font: inherit;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        .ll-appearance .ll-input:focus {
            outline: none;
            border-color: color-mix(in srgb, var(--ll-primary) 55%, var(--ll-border));
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ll-primary) 16%, transparent);
        }

        .ll-input-group {
            display: flex;
            align-items: stretch;
            border: 1px solid var(--ll-border);
            border-radius: var(--ll-button-radius);
            overflow: hidden;
            background: var(--ll-surface-solid);
        }
        .ll-input-group .ll-input-prefix {
            display: inline-flex;
            align-items: center;
            padding: 0 12px;
            background: color-mix(in srgb, var(--ll-bg-soft) 70%, transparent);
            color: var(--ll-muted);
            font-weight: 600;
            white-space: nowrap;
            border-right: 1px solid var(--ll-border);
        }
        .ll-input-group input {
            border: 0;
            border-radius: 0;
            flex: 1;
            min-width: 0;
            background: transparent;
            color: var(--ll-text);
            padding: 11px 14px;
            font: inherit;
        }
        .ll-input-group input:focus { outline: none; }
        .ll-input-group:focus-within {
            border-color: color-mix(in srgb, var(--ll-primary) 55%, var(--ll-border));
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ll-primary) 16%, transparent);
        }

        .ll-avatar-row { display: flex; align-items: center; gap: 16px; }
        .ll-avatar {
            width: 84px;
            height: 84px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid var(--ll-border);
            background: var(--ll-surface-solid);
        }
        .ll-avatar-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--ll-danger);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .ll-toggle-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0;
            border-top: 1px solid var(--ll-border);
        }
        .ll-toggle-row:first-of-type { border-top: 0; padding-top: 0; }
        .ll-toggle-text strong { display: block; color: var(--ll-text); font-size: 0.98rem; }
        .ll-toggle-text span { color: var(--ll-muted); font-size: 0.86rem; }

        .ll-switch { position: relative; flex: 0 0 auto; width: 46px; height: 26px; }
        .ll-switch input {
            position: absolute;
            inset: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
        }
        .ll-switch-track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: color-mix(in srgb, var(--ll-text) 18%, transparent);
            transition: background 160ms ease;
        }
        .ll-switch-track::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            transition: transform 160ms ease;
        }
        .ll-switch input:checked + .ll-switch-track {
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
        }
        .ll-switch input:checked + .ll-switch-track::after { transform: translateX(20px); }
        .ll-switch input:focus-visible + .ll-switch-track {
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ll-primary) 24%, transparent);
        }

        .ll-appearance-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: var(--ll-button-radius);
            background: linear-gradient(135deg, var(--ll-primary), var(--ll-primary-2));
            color: #fff;
            font-weight: 600;
            padding: 12px 22px;
            cursor: pointer;
            box-shadow: 0 14px 30px color-mix(in srgb, var(--ll-primary) 26%, transparent);
            transition: transform 150ms ease;
        }
        .ll-appearance-save:hover { transform: translateY(-1px); }

        .ll-appearance-alert {
            border: 1px solid color-mix(in srgb, var(--ll-danger) 40%, var(--ll-border));
            background: color-mix(in srgb, var(--ll-danger) 10%, transparent);
            color: var(--ll-danger);
            border-radius: var(--ll-button-radius);
            padding: 12px 14px;
            display: grid;
            gap: 4px;
        }

        .ck-editor__editable[role="textbox"] { min-height: 200px; }
        .ck-content .image { max-width: 80%; margin: 20px auto; }
    </style>

    <div class="ll-appearance-head">
        <h1>{{ __('messages.My Profile') }}</h1>
        <p>Manage how your public Livelatch profile looks and behaves.</p>
    </div>

    @if($errors->any())
        <div class="ll-appearance-alert" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @foreach($pages as $page)
    <form action="{{ route('editPage') }}" enctype="multipart/form-data" method="post" class="ll-appearance-card">
        @csrf
        <div class="ll-appearance-grid">
            <div class="ll-avatar-row">
                <img src="{{ profileImageUrl(auth()->id()) }}" class="ll-avatar" width="84" height="84" draggable="false" alt="Current profile picture">
                @if(profileImageExists(auth()->id()))
                    <a href="{{ route('delProfilePicture') }}" class="ll-avatar-delete" data-bs-toggle="tooltip" data-bs-placement="right" title="Delete profile picture">
                        <i class="bi bi-trash-fill"></i> Remove
                    </a>
                @endif
            </div>

            @if($page->littlelink_name != '')
            <div class="ll-field">
                <label for="customFile">{{ __('messages.Profile Picture') }}</label>
                <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" name="image" class="ll-input" id="customFile">
            </div>
            @endif

            @php
                $candidates = $candidates ?? [];
                $hasPendingRequest = $hasPendingRequest ?? false;
                $currentHandle = $page->littlelink_name ?? '';
                $currentName = $page->name ?? '';
                $haveCurrentHandle = collect($candidates)->contains(fn ($c) => strtolower($c['handle']) === strtolower($currentHandle));
                $haveCurrentName = collect($candidates)->contains(fn ($c) => mb_strtolower($c['name']) === mb_strtolower($currentName));
            @endphp

            <div class="ll-field">
                <?php
                    $url = $_SERVER['REQUEST_URI'];
                    if (strpos($url, "no_page_name") == true) echo '<span style="color:var(--ll-danger); font-weight:600;">You do not have a Page URL</span>';
                ?>
                <label for="littlelink_name" class="form-label">{{ __('messages.Page URL') }}</label>
                <div class="ll-input-group">
                    <span class="ll-input-prefix" id="basic-addon3">{{ str_replace(['http://', 'https://'], '', url('')) }}/@</span>
                    <select id="littlelink_name" name="littlelink_name" required style="flex:1; border:0; background:transparent; color:var(--ll-text); padding:0 6px; min-height:38px; font-size:inherit;">
                        @unless($haveCurrentHandle)
                            <option value="{{ $currentHandle }}" selected>{{ $currentHandle }}</option>
                        @endunless
                        @foreach($candidates as $c)
                            @php $isCurrent = strtolower($c['handle']) === strtolower($currentHandle); $taken = !$c['available'] && !$isCurrent; @endphp
                            <option value="{{ $c['handle'] }}" @selected($isCurrent) @disabled($taken)>{{ $c['handle'] }}{{ $taken ? ' (taken)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ll-field">
                <label>{{ __('messages.Display name') }}</label>
                <select class="ll-input" name="name" required>
                    @unless($haveCurrentName)
                        <option value="{{ $currentName }}" selected>{{ $currentName }}</option>
                    @endunless
                    @foreach($candidates as $c)
                        <option value="{{ $c['name'] }}" @selected(mb_strtolower($c['name']) === mb_strtolower($currentName))>{{ $c['name'] }}@if(($c['source'] ?? '') !== 'Current name') · {{ $c['source'] }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div class="ll-field" id="ll-req-wrap" data-req-url="{{ route('requestNameChange') }}" data-csrf="{{ csrf_token() }}">
                @if($hasPendingRequest)
                    <div class="ll-appearance-alert" role="alert" style="border:1px solid var(--ll-border); border-radius:12px; padding:10px 12px; color:var(--ll-muted); font-size:.85rem;">
                        <i class="bi bi-hourglass-split"></i> You have a pending name request awaiting review.
                    </div>
                @else
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between;">
                        <small style="color:var(--ll-muted);">These come from your linked accounts. Is your name missing? <a href="{{ url('/studio/latchid') }}" style="color:var(--ll-primary); font-weight:600; text-decoration:none;">Link it in LatchID</a>.</small>
                        <button type="button" id="ll-req-toggle" style="border:1px solid var(--ll-border); background:transparent; color:var(--ll-text); border-radius:10px; padding:7px 13px; font-weight:600; font-size:.84rem; cursor:pointer;">Request a custom name</button>
                    </div>
                    <div id="ll-req-panel" style="display:none; margin-top:10px; gap:8px; grid-template-columns:1fr; display:none;">
                        <small style="color:var(--ll-muted);">Want a name or URL that isn't linked to your account? Request it — an admin reviews custom names. Your current URL keeps working after a change.</small>
                        <input type="text" id="ll-req-name" maxlength="120" placeholder="Custom display name (optional)" class="ll-input">
                        <input type="text" id="ll-req-handle" maxlength="60" placeholder="custom-url (optional)" class="ll-input">
                        <div style="display:flex; gap:10px; align-items:center;">
                            <button type="button" id="ll-req-submit" style="border:0; background:linear-gradient(135deg,var(--ll-primary),var(--ll-primary-2)); color:#fff; border-radius:10px; padding:9px 16px; font-weight:700; font-size:.85rem; cursor:pointer;">Send request</button>
                            <small id="ll-req-status" style="color:var(--ll-muted);"></small>
                        </div>
                    </div>
                @endif
            </div>
            <script>
                (function () {
                    var wrap = document.getElementById('ll-req-wrap');
                    if (!wrap) return;
                    var toggle = document.getElementById('ll-req-toggle');
                    var panel = document.getElementById('ll-req-panel');
                    var submit = document.getElementById('ll-req-submit');
                    if (toggle && panel) toggle.addEventListener('click', function () { panel.style.display = panel.style.display === 'grid' ? 'none' : 'grid'; });
                    if (submit) submit.addEventListener('click', function () {
                        var status = document.getElementById('ll-req-status');
                        var name = (document.getElementById('ll-req-name').value || '').trim();
                        var handle = (document.getElementById('ll-req-handle').value || '').trim();
                        if (!name && !handle) { status.textContent = 'Enter a name or URL.'; return; }
                        submit.disabled = true; status.textContent = 'Sending…';
                        fetch(wrap.dataset.reqUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': wrap.dataset.csrf },
                            credentials: 'same-origin',
                            body: JSON.stringify({ requested_name: name, requested_handle: handle })
                        }).then(function () {
                            panel.innerHTML = '<div style="color:var(--ll-primary); font-weight:600; font-size:.88rem;">Request sent — we\'ll review it shortly.</div>';
                        }).catch(function () {
                            submit.disabled = false; status.textContent = 'Could not send — try again.';
                        });
                    });
                })();
            </script>

            <div class="ll-field">
                <label>{{ __('messages.Page Description') }}</label>
                <textarea class="ll-input @if(env('ALLOW_USER_HTML') === true) ckeditor @endif" name="pageDescription" rows="3">{{ $page->littlelink_description ?? '' }}</textarea>
            </div>

            <div>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'vip')
                <div class="ll-toggle-row">
                    <div class="ll-toggle-text">
                        <strong>{{ __('messages.Show checkmark') }}</strong>
                        <span>{{ __('messages.disableverified') }}</span>
                    </div>
                    <label class="ll-switch">
                        <input name="checkmark" type="checkbox" id="checkmark" <?php if(UserData::getData(Auth::user()->id, 'checkmark') == true){echo 'checked';} ?> />
                        <span class="ll-switch-track"></span>
                    </label>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @endif

                <div class="ll-toggle-row">
                    <div class="ll-toggle-text">
                        <strong>{{ __('messages.Show share button') }}</strong>
                        <span>{{ __('messages.disablesharebutton') }}</span>
                    </div>
                    <label class="ll-switch">
                        <input name="sharebtn" type="checkbox" id="sharebtn" <?php if(UserData::getData(Auth::user()->id, 'disable-sharebtn') != "true"){echo 'checked';} ?> />
                        <span class="ll-switch-track"></span>
                    </label>
                </div>

                <div class="ll-toggle-row">
                    <div class="ll-toggle-text">
                        <strong>{{ __('messages.Open links in new tab') }}</strong>
                        <span>{{ __('messages.openlinksnewtab') }}</span>
                    </div>
                    <label class="ll-switch">
                        <input name="tablinks" type="checkbox" id="tablinks" <?php if(UserData::getData(Auth::user()->id, 'links-new-tab') != false){echo 'checked';} ?> />
                        <span class="ll-switch-track"></span>
                    </label>
                </div>
            </div>

            <div>
                <button id="submit-btn" type="submit" class="ll-appearance-save">
                    <i class="bi bi-check2-circle"></i> {{ __('messages.Save') }}
                </button>
            </div>
        </div>
    </form>
    @endforeach

    @if(env('ALLOW_USER_HTML') === true)
    <script>window.llCkeditorSrc = @json(asset('assets/external-dependencies/ckeditor.js'));</script>
    <script src="{{ asset('assets/js/ll-ckeditor-init.js') }}"></script>
    <script>window.llInitCkeditors();</script>
    <script>
    // Swapped in via HTMX: an innerHTML-inserted <script src> does not block the
    // following inline script, so ClassicEditor could be undefined. Load the
    // editor dynamically (onload fires reliably for created scripts), init once.
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
                  addTargetToExternalLinks: true,
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
</div>

@endsection
