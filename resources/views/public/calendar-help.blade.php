<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ll-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Remove a Livelatch calendar</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#000421; --bg2:#060a26; --text:#f8fbff; --muted:#aeb8cf; --p1:#16a6ff; --border:rgba(255,255,255,.12); }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; display: flex; justify-content: center; padding: 40px 18px; }
        .ch { width: min(640px, 100%); }
        h1 { font-family: 'Space Grotesk', system-ui, sans-serif; font-size: clamp(1.6rem, 4vw, 2.1rem); margin: 0 0 6px; }
        .lead { color: var(--muted); margin: 0 0 26px; }
        .card { background: linear-gradient(180deg, color-mix(in srgb, #fff 5%, var(--bg2)), var(--bg2)); border: 1px solid var(--border); border-radius: 18px; padding: 20px 22px; margin-bottom: 14px; }
        .card h2 { margin: 0 0 10px; font-size: 1.05rem; display: flex; align-items: center; gap: 9px; }
        .card ol { margin: 0; padding-left: 20px; color: var(--muted); line-height: 1.7; }
        .card ol strong { color: var(--text); }
        a { color: var(--p1); }
        .foot { color: var(--muted); font-size: .85rem; margin-top: 20px; }
    </style>
</head>
<body>
    <main class="ch">
        <h1>Remove a stream calendar</h1>
        <p class="lead">You subscribed to a creator's Livelatch stream schedule. Here's how to unsubscribe on each device — it only removes the calendar, nothing else.</p>

        <div class="card">
            <h2><i style="font-style:normal;"></i> iPhone &amp; iPad</h2>
            <ol>
                <li>Open <strong>Settings</strong> → <strong>Calendar</strong> → <strong>Accounts</strong>.</li>
                <li>Tap <strong>Subscribed Calendars</strong>.</li>
                <li>Tap the Livelatch calendar, then <strong>Delete Account</strong>.</li>
            </ol>
        </div>

        <div class="card">
            <h2>Google Calendar</h2>
            <ol>
                <li>Open <a href="https://calendar.google.com" target="_blank" rel="noopener">calendar.google.com</a> on a computer.</li>
                <li>In the left sidebar under <strong>Other calendars</strong>, hover the Livelatch calendar.</li>
                <li>Click the <strong>×</strong> (Unsubscribe).</li>
            </ol>
        </div>

        <div class="card">
            <h2>Outlook</h2>
            <ol>
                <li>Go to <strong>Calendar</strong> in Outlook on the web.</li>
                <li>Right-click the Livelatch calendar under your calendar list.</li>
                <li>Choose <strong>Remove</strong>.</li>
            </ol>
        </div>

        <p class="foot">Subscribed calendars refresh on your device's own schedule, so a removed one may take a little while to disappear. <a href="{{ url('/') }}">Back to Livelatch</a></p>
    </main>
</body>
</html>
