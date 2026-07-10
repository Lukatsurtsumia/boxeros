<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Soft-launch: keep BoxerOS out of search engines. Remove this line when you want public discovery. --}}
    <meta name="robots" content="index, follow">
    <title>{{ config('app.name', 'BoxerOS') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @include('partials.pwa-head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:    #c0392b;
            --red-dk: #96281b;
            --gold:   #f39c12;
            --bg:     #08080d;
            --card:   #0f0f18;
            --border: rgba(255,255,255,0.08);
            --muted:  rgba(255,255,255,0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #f0f0f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-x: hidden;
        }

        /* Blobs */
        .blob {
            position: fixed; border-radius: 50%;
            filter: blur(140px); pointer-events: none; z-index: 0;
        }
        .blob-1 {
            width: 50vw; height: 50vw; max-width: 600px; max-height: 600px;
            background: rgba(192,57,43,.12); top: -10%; right: -10%;
        }
        .blob-2 {
            width: 35vw; height: 35vw; max-width: 400px; max-height: 400px;
            background: rgba(243,156,18,.06); bottom: 0; left: -8%;
        }

        /* Auth card */
        .auth-wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 420px;
        }

        /* Logo */
        .auth-logo {
            text-align: center; margin-bottom: 2rem;
        }
        .auth-logo a {
            text-decoration: none; display: inline-block;
        }
        .auth-logo-text {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2rem; font-weight: 700; letter-spacing: .3px;
        }
        .auth-logo-text em { color: var(--red); font-style: normal; }
        .auth-logo-sub {
            font-size: .72rem; color: var(--muted);
            letter-spacing: .5px; margin-top: .2rem;
        }

        /* Card */
        .auth-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 30px 80px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.05);
        }
        @media (min-width: 480px) { .auth-card { padding: 2.5rem; } }

        .auth-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.6rem; font-weight: 700;
            margin-bottom: .3rem;
        }
        .auth-subtitle {
            font-size: .82rem; color: var(--muted);
            margin-bottom: 1.75rem;
        }

        /* Form elements */
        .field {
            margin-bottom: 1rem;
        }
        .field label {
            display: block; font-size: .78rem; font-weight: 600;
            color: rgba(255,255,255,.65); margin-bottom: .4rem;
            letter-spacing: .2px;
        }
        .field input {
            width: 100%; padding: .72rem 1rem;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #f0f0f0; font-size: .9rem;
            font-family: 'Inter', sans-serif;
            outline: none; transition: border-color .2s, background .2s, box-shadow .2s;
            -webkit-appearance: none;
        }
        .field input:focus {
            border-color: rgba(192,57,43,.5);
            background: rgba(192,57,43,.04);
            box-shadow: 0 0 0 3px rgba(192,57,43,.08);
        }
        .field input::placeholder { color: rgba(255,255,255,.2); }
        .field input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 100px #0f0f18 inset;
            -webkit-text-fill-color: #f0f0f0;
        }

        /* Error state - applied to .field when that field has errors */
        .field.has-error input {
            border-color: rgba(231,76,60,.55);
            background: rgba(231,76,60,.04);
        }
        .field.has-error input:focus {
            border-color: rgba(231,76,60,.8);
            box-shadow: 0 0 0 3px rgba(231,76,60,.1);
        }

        /* Always reserve 1.1rem below each input - error text fills it, empty fields keep the space */
        .field-hint {
            min-height: 1.1rem;
            padding-top: .28rem;
            font-size: .72rem; line-height: 1;
            color: transparent; /* invisible when empty */
        }
        .field-hint.has-msg {
            color: #e05545;
            display: flex; align-items: center; gap: .3rem;
        }
        .field-hint.has-msg::before {
            content: '!';
            display: inline-flex; align-items: center; justify-content: center;
            width: 13px; height: 13px; border-radius: 50%;
            background: rgba(231,76,60,.2);
            color: #e05545; font-size: .65rem; font-weight: 700;
            flex-shrink: 0;
        }

        /* Password strength meter - live feedback as you type */
        .pw-strength {
            display: flex; align-items: center; gap: .6rem;
            margin-top: .5rem; min-height: 14px;
        }
        .pw-bar {
            flex: 1; height: 5px; border-radius: 999px;
            background: rgba(255,255,255,.08); overflow: hidden;
        }
        .pw-bar-fill {
            height: 100%; width: 0; border-radius: 999px;
            transition: width .25s ease, background-color .25s ease;
        }
        .pw-label {
            font-size: .7rem; font-weight: 700; letter-spacing: .3px;
            min-width: 56px; text-align: right; white-space: nowrap;
        }

        /* Remember me */
        .check-row {
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: 1.5rem;
        }
        .check-row input[type=checkbox] {
            width: 16px; height: 16px;
            accent-color: var(--red);
            cursor: pointer; flex-shrink: 0;
        }
        .check-row label {
            font-size: .8rem; color: var(--muted); cursor: pointer;
        }

        /* Session status */
        .session-status {
            background: rgba(46,204,113,.1);
            border: 1px solid rgba(46,204,113,.2);
            color: #2ecc71; border-radius: 10px;
            padding: .65rem 1rem; font-size: .82rem;
            margin-bottom: 1.25rem;
        }

        /* Submit button */
        .btn-submit {
            width: 100%; padding: .85rem;
            background: linear-gradient(135deg, var(--red), var(--red-dk));
            color: #fff; border: none; border-radius: 11px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.05rem; font-weight: 700; letter-spacing: .4px;
            cursor: pointer; transition: all .2s;
            margin-top: .25rem;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(192,57,43,.45); }
        .btn-submit:active { transform: translateY(0); }

        /* Continue with Google */
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: .65rem;
            width: 100%; padding: .8rem; margin-bottom: .4rem;
            background: #fff; color: #1f1f1f; border: 1px solid #dadce0; border-radius: 11px;
            font-family: 'Inter', sans-serif; font-size: .92rem; font-weight: 600;
            text-decoration: none; cursor: pointer; transition: all .15s;
        }
        .btn-google:hover { background: #f6f7f8; box-shadow: 0 4px 14px rgba(0,0,0,.25); }
        .btn-google svg { width: 18px; height: 18px; flex-shrink: 0; }
        .auth-divider {
            display: flex; align-items: center; gap: .8rem;
            margin: 1.1rem 0; color: var(--muted); font-size: .72rem;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        /* Form footer links */
        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: .5rem;
            margin-top: 1.5rem; padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .form-footer a, .form-footer span {
            font-size: .8rem; color: var(--muted);
            text-decoration: none; transition: color .2s;
        }
        .form-footer a:hover { color: #f0f0f0; }
        .form-footer a.link-red { color: var(--red); }
        .form-footer a.link-red:hover { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div style="position: fixed; top: 1rem; right: 1rem; z-index: 5;">
        @include('partials.lang-toggle')
    </div>

    <div class="auth-wrap">
        <div class="auth-logo">
            <a href="{{ route('home') }}">
                <div class="auth-logo-text" translate="no"><em>BOXER</em>OS</div>
                <div class="auth-logo-sub">{{ __('Your personal boxing command center') }}</div>
            </a>
        </div>

        <div class="auth-card">
            {{ $slot }}
        </div>
    </div>
    @include('partials.install-hint')
</body>
</html>
