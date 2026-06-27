<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ll-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Username not allowed · Livelatch</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#000421; --bg2:#060a26; --text:#f8fbff; --muted:#aeb8cf; --p1:#16a6ff; --p2:#25f4ee; --border:rgba(255,255,255,.12); --danger:#ef4444; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            background:
                radial-gradient(circle at 16% -10%, color-mix(in srgb, var(--danger) 16%, transparent), transparent 40%),
                radial-gradient(circle at 84% 110%, color-mix(in srgb, var(--p2) 12%, transparent), transparent 42%),
                var(--bg);
            color: var(--text); font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px 18px;
        }
        .ub-card {
            width: min(500px, 100%); text-align: center;
            background: linear-gradient(180deg, color-mix(in srgb, #fff 5%, var(--bg2)), var(--bg2));
            border: 1px solid var(--border); border-radius: 24px; padding: 36px 28px;
            box-shadow: 0 40px 120px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.08);
        }
        .ub-icon { width: 60px; height: 60px; border-radius: 50%; display: inline-grid; place-items: center; margin-bottom: 16px;
            background: color-mix(in srgb, var(--danger) 16%, transparent); color: var(--danger); font-size: 1.8rem; border: 1px solid color-mix(in srgb, var(--danger) 40%, transparent); }
        .ub-title { font-family: 'Space Grotesk', system-ui, sans-serif; font-weight: 700; font-size: clamp(1.5rem, 5vw, 2rem); margin: 0 0 8px; color: var(--text); }
        .ub-sub { color: var(--muted); font-size: 1rem; margin: 0 auto 24px; max-width: 38ch; }
        .ub-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; border-radius: 14px; font-weight: 700; font-size: .98rem; padding: 13px 22px; text-decoration: none;
            background: linear-gradient(135deg, var(--p1), var(--p2)); color: #04122a; box-shadow: 0 12px 30px color-mix(in srgb, var(--p1) 30%, transparent); }
        .ub-foot { margin-top: 22px; color: var(--muted); font-size: .82rem; }
    </style>
</head>
<body>
    <main class="ub-card">
        <div class="ub-icon"><i style="font-style:normal;">⛔</i></div>
        <h1 class="ub-title">This username is banned</h1>
        <p class="ub-sub">The username generated for your account isn't allowed on Livelatch. Please use a different name or email and try again.</p>
        <a class="ub-btn" href="{{ url('/') }}">Back to Livelatch</a>
        <p class="ub-foot">If you think this is a mistake, contact support.</p>
    </main>
</body>
</html>
