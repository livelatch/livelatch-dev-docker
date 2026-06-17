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

            <div class="ll-field">
                <?php
                    $url = $_SERVER['REQUEST_URI'];
                    if (strpos($url, "no_page_name") == true) echo '<span style="color:var(--ll-danger); font-weight:600;">You do not have a Page URL</span>';
                ?>
                <label for="littlelink_name" class="form-label">{{ __('messages.Page URL') }}</label>
                <div class="ll-input-group">
                    <span class="ll-input-prefix" id="basic-addon3">{{ str_replace(['http://', 'https://'], '', url('')) }}/@</span>
                    <input type="littlelink_name" id="littlelink_name" name="littlelink_name" aria-describedby="littlelink_name" value="{{ $page->littlelink_name ?? '' }}" :value="old('littlelink_name')" required autofocus>
                </div>
                <script>var exceptionvar = " value="{{ $page->littlelink_name }}";</script>
                @include('auth.url-validation')
            </div>

            <div class="ll-field">
                <label>{{ __('messages.Display name') }}</label>
                <input type="text" class="ll-input" name="name" value="{{ $page->name }}" required>
            </div>

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
    <script src="{{ asset('assets/external-dependencies/ckeditor.js') }}"></script>
    <script>
      ClassicEditor
          .create(document.querySelector('.ckeditor'), {
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
          .catch(error => {
              console.error(error);
          });
    </script>
    @endif
</div>

@endsection
