<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BoxerOS')</title>
    <style>
        :root { --blood:#c0392b; --gold:#f39c12; }
        * { box-sizing: border-box; }
        body { background:#0d0d12; color:#e5e5ea; font-family: system-ui, -apple-system, sans-serif; line-height:1.7; margin:0; padding:2rem 1rem 4rem; }
        .wrap { max-width: 720px; margin:0 auto; }
        .brand { font-weight:800; letter-spacing:0.5px; }
        .brand .b { color: var(--blood); }
        h1 { font-size:1.7rem; margin:0.5rem 0 0.25rem; }
        h2 { color: var(--gold); font-size:1.05rem; margin:1.8rem 0 0.4rem; }
        p, li { color:#cfcfd6; }
        a { color: var(--gold); }
        .muted { color:#8a8a95; font-size:0.85rem; }
        .back { display:inline-block; margin-bottom:1.25rem; color:#8a8a95; text-decoration:none; font-size:0.9rem; }
        .callout { background: rgba(192,57,43,0.10); border-left:3px solid var(--blood); padding:0.85rem 1rem; border-radius:8px; margin:1rem 0; }
    </style>
</head>
<body>
    <div class="wrap">
        <a href="{{ url('/') }}" class="back">← Back to BoxerOS</a>
        <div class="brand"><span class="b">BOXER</span> OS</div>
        @yield('content')
        <p class="muted" style="margin-top:2.5rem;">Last updated {{ now()->format('F Y') }}. Questions? {{ config('mail.from.address') ? 'Contact ' . config('mail.from.address') : 'Reach out to the BoxerOS team.' }}</p>
    </div>
</body>
</html>
