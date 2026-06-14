<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($type ?? 'privacy') === 'privacy' ? 'Privacy Notice' : 'Terms of Service' }} | Livelatch</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
            background: #f8fafc;
            line-height: 1.6;
        }
        main {
            width: min(760px, calc(100% - 32px));
            margin: 0 auto;
            padding: 56px 0;
        }
        a { color: #2563eb; }
        .ll-card {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .08);
        }
    </style>
</head>
<body>
<main>
    <p><a href="{{ url('/') }}">Back to Livelatch</a></p>
    <section class="ll-card">
        @include('public.legal-partial', ['type' => $type ?? 'privacy'])
    </section>
</main>
</body>
</html>
