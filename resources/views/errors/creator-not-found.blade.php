<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ll-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ '@' . $handle }} isn't on Livelatch yet</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#000421; --bg2:#060a26; --text:#f8fbff; --muted:#aeb8cf; --p1:#16a6ff; --p2:#25f4ee; --border:rgba(255,255,255,.12); }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            background:
                radial-gradient(circle at 16% -10%, color-mix(in srgb, var(--p1) 22%, transparent), transparent 40%),
                radial-gradient(circle at 84% 110%, color-mix(in srgb, var(--p2) 16%, transparent), transparent 42%),
                var(--bg);
            color: var(--text); font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px 18px;
        }
        .cr-card {
            width: min(540px, 100%); text-align: center;
            background: linear-gradient(180deg, color-mix(in srgb, #fff 5%, var(--bg2)), var(--bg2));
            border: 1px solid var(--border); border-radius: 24px; padding: 34px 28px;
            box-shadow: 0 40px 120px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.08);
        }
        .cr-eyebrow { color: var(--muted); font-size: .82rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .cr-handle {
            font-family: 'Space Grotesk', system-ui, sans-serif; font-weight: 700; line-height: 1.05;
            font-size: clamp(2rem, 7vw, 3rem); margin: 8px 0 6px;
            background: linear-gradient(120deg, var(--text), var(--p1)); -webkit-background-clip: text; background-clip: text; color: transparent;
            word-break: break-word;
        }
        .cr-lead { color: var(--text); font-size: 1.05rem; font-weight: 600; margin: 0 0 6px; }
        .cr-sub { color: var(--muted); font-size: .95rem; margin: 0 auto 22px; max-width: 40ch; }
        .cr-row { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .cr-btn {
            display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer;
            border-radius: 14px; font-weight: 700; font-size: .98rem; padding: 13px 22px; font-family: inherit;
            background: linear-gradient(135deg, var(--p1), var(--p2)); color: #04122a;
            box-shadow: 0 12px 30px color-mix(in srgb, var(--p1) 32%, transparent);
            transition: transform .14s ease, box-shadow .14s ease, opacity .14s;
        }
        .cr-btn:hover { transform: translateY(-1px); }
        .cr-btn[disabled] { opacity: .6; cursor: default; transform: none; }
        .cr-ghost { background: transparent; color: var(--text); border: 1px solid var(--border); box-shadow: none; }
        .cr-email { margin-top: 16px; display: none; }
        .cr-email.is-on { display: block; }
        .cr-email input {
            width: 100%; max-width: 320px; border: 1px solid var(--border); border-radius: 12px;
            background: rgba(255,255,255,.04); color: var(--text); padding: 12px 14px; font-size: .95rem; font-family: inherit; text-align: center;
        }
        .cr-email small { display: block; color: var(--muted); font-size: .8rem; margin: 0 0 8px; }
        .cr-thanks { display: none; }
        .cr-thanks.is-on { display: block; }
        .cr-thanks .cr-check { width: 56px; height: 56px; border-radius: 50%; display: inline-grid; place-items: center; margin-bottom: 12px;
            background: color-mix(in srgb, var(--p1) 18%, transparent); color: var(--p2); font-size: 1.6rem; border: 1px solid color-mix(in srgb, var(--p1) 40%, transparent); }
        .cr-foot { margin-top: 22px; color: var(--muted); font-size: .82rem; }
        .cr-foot a { color: var(--p1); text-decoration: none; font-weight: 600; }
        .cr-ask, .cr-thanks { transition: opacity .2s ease; }
    </style>
</head>
<body>
    <main class="cr-card" data-handle="{{ $handle }}">
        <div class="cr-ask" id="cr-ask">
            <div class="cr-eyebrow">Creator not found</div>
            <div class="cr-handle">{{ '@' . $handle }}</div>
            <p class="cr-lead">This creator doesn't have a Livelatch yet.</p>
            <p class="cr-sub">Think they should? Let us know — if enough people ask for <strong>{{ '@' . $handle }}</strong>, we'll reach out and invite them.</p>

            <div class="cr-row">
                <button type="button" class="cr-btn" id="cr-yes"><i></i> Yes — they should!</button>
                <a class="cr-btn cr-ghost" href="{{ url('/') }}">Make your own Livelatch</a>
            </div>

            <div class="cr-email" id="cr-email">
                <small>Optional — we'll email you if {{ '@' . $handle }} joins.</small>
                <input type="email" id="cr-email-input" placeholder="you@example.com" autocomplete="email">
            </div>
        </div>

        <div class="cr-thanks" id="cr-thanks">
            <div class="cr-check">✓</div>
            <p class="cr-lead">Thanks — noted!</p>
            <p class="cr-sub">We're tracking interest in <strong>{{ '@' . $handle }}</strong>. The more people ask, the more likely we reach out.</p>
            <div class="cr-row"><a class="cr-btn" href="{{ url('/') }}">Make your own Livelatch</a></div>
        </div>

        <p class="cr-foot">Already a creator? <a href="{{ url('/') }}">Sign in to Livelatch</a></p>
    </main>

    <script>
        (function () {
            var handle = document.querySelector('.cr-card').dataset.handle || '';
            var key = 'll-cr-requested-' + handle.toLowerCase();
            var ask = document.getElementById('cr-ask');
            var thanks = document.getElementById('cr-thanks');
            var emailWrap = document.getElementById('cr-email');
            var emailInput = document.getElementById('cr-email-input');
            var yes = document.getElementById('cr-yes');

            function showThanks() { ask.style.display = 'none'; thanks.classList.add('is-on'); }

            // Suppress re-prompts on refresh (server-side salted-hash dedup is the
            // real counter; this just keeps the UX honest for the same browser).
            try { if (localStorage.getItem(key)) { showThanks(); } } catch (e) {}

            var revealed = false;
            yes.addEventListener('click', function () {
                if (!revealed) { emailWrap.classList.add('is-on'); yes.textContent = 'Send request'; revealed = true; emailInput.focus(); return; }
                submit();
            });
            emailInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') submit(); });

            function submit() {
                yes.disabled = true; yes.textContent = 'Sending…';
                var token = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
                fetch('{{ route('creatorRequest.store') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                    credentials: 'same-origin',
                    body: JSON.stringify({ handle: handle, email: (emailInput.value || '').trim() })
                }).then(function () {
                    try { localStorage.setItem(key, '1'); } catch (e) {}
                    showThanks();
                }).catch(function () {
                    try { localStorage.setItem(key, '1'); } catch (e) {}
                    showThanks();
                });
            }
        })();
    </script>
</body>
</html>
