<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BoxerOS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --blood: #c0392b;
            --blood-dark: #96281b;
            --gold: #f39c12;
            --gold-light: #f9ca24;
            --dark: #0a0a0f;
            --dark-card: #111118;
            --dark-border: #1e1e2e;
            --dark-muted: #1a1a2e;
            --text-primary: #f0f0f0;
            --text-muted: #8888a8;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-primary);
            min-height: 100vh;
            padding-bottom: 5rem;
        }
        h1, h2, h3, .font-display { font-family: 'Rajdhani', sans-serif; }

        /* Cards */
        .card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 16px;
            padding: 1.25rem;
        }
        .card-glow {
            box-shadow: 0 0 30px rgba(192, 57, 43, 0.08);
        }
        .card-gold {
            box-shadow: 0 0 30px rgba(243, 156, 18, 0.1);
            border-color: rgba(243, 156, 18, 0.2);
        }

        /* Progress bar */
        .progress-track {
            background: rgba(255,255,255,0.06);
            border-radius: 999px;
            overflow: hidden;
            height: 8px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
        }
        .progress-blood { background: linear-gradient(90deg, var(--blood-dark), var(--blood)); }
        .progress-gold  { background: linear-gradient(90deg, #e67e22, var(--gold)); }
        .progress-blue  { background: linear-gradient(90deg, #2980b9, #3498db); }
        .progress-green { background: linear-gradient(90deg, #1abc9c, #2ecc71); }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--blood), var(--blood-dark));
            color: white;
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(192,57,43,0.4); }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold), #e67e22);
            color: #0a0a0f;
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(243,156,18,0.4); }
        .btn-ghost {
            background: rgba(255,255,255,0.06);
            color: var(--text-primary);
            border: 1px solid var(--dark-border);
            padding: 0.6rem 1.4rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); }

        /* Form inputs */
        .input-dark {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--dark-border);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            width: 100%;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .input-dark:focus {
            outline: none;
            border-color: var(--blood);
            background: rgba(192,57,43,0.06);
        }
        .input-dark::placeholder { color: var(--text-muted); }
        select.input-dark option { background: var(--dark-card); }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-red   { background: rgba(192,57,43,0.25); color: #ff6b6b; }
        .badge-gold  { background: rgba(243,156,18,0.25); color: var(--gold); }
        .badge-green { background: rgba(46,204,113,0.2); color: #2ecc71; }
        .badge-blue  { background: rgba(52,152,219,0.2); color: #3498db; }
        .badge-gray  { background: rgba(255,255,255,0.08); color: var(--text-muted); }

        /* Bottom nav */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: rgba(17, 17, 24, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--dark-border);
            padding: 0.5rem 0 0.75rem;
            z-index: 100;
        }
        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.65rem;
            font-weight: 500;
            transition: color 0.2s;
            padding: 0.3rem 0;
        }
        .bottom-nav a.active, .bottom-nav a:hover { color: var(--blood); }
        .bottom-nav svg { width: 22px; height: 22px; }

        /* Stat card */
        .stat-num {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        /* Chat bubble */
        .chat-bubble-user {
            background: linear-gradient(135deg, var(--blood-dark), var(--blood));
            border-radius: 18px 18px 4px 18px;
            padding: 0.75rem 1rem;
            max-width: 78%;
            margin-left: auto;
            font-size: 0.875rem;
        }
        .chat-bubble-ai {
            background: var(--dark-muted);
            border: 1px solid var(--dark-border);
            border-radius: 18px 18px 18px 4px;
            padding: 0.75rem 1rem;
            max-width: 88%;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        /* Water drops */
        .water-btn {
            background: rgba(52,152,219,0.15);
            border: 1px solid rgba(52,152,219,0.3);
            color: #3498db;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .water-btn:hover { background: rgba(52,152,219,0.3); transform: translateY(-1px); }

        /* Top header */
        .top-header {
            background: rgba(17,17,24,0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--dark-border);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.75rem 1rem;
        }

        /* Mood icons */
        .mood-btn { cursor: pointer; font-size: 1.5rem; opacity: 0.4; transition: all 0.2s; filter: grayscale(1); }
        .mood-btn:hover, .mood-btn.selected { opacity: 1; filter: grayscale(0); transform: scale(1.2); }

        /* Injury severity */
        .sev-minor    { color: #f39c12; }
        .sev-moderate { color: #e74c3c; }
        .sev-serious  { color: #ff0055; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--dark-border); border-radius: 4px; }
    </style>
</head>
<body>

    {{-- Flash message --}}
    @if(session('message'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-4 right-4 z-50 card card-gold px-4 py-3 text-sm font-medium"
         style="border-color: rgba(243,156,18,0.5); color: var(--gold);">
        {{ session('message') }}
    </div>
    @endif

    {{-- Top header --}}
    <div class="top-header flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="font-display text-xl font-bold" style="color: var(--blood);">BOXER</span>
            <span class="font-display text-xl font-bold text-white">OS</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs" style="color: var(--text-muted);">{{ now()->format('D, M j') }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost py-1 px-3 text-xs">Logout</button>
            </form>
        </div>
    </div>

    {{-- Page content --}}
    <main class="max-w-lg mx-auto px-3 pt-4">
        {{ $slot }}
    </main>

    {{-- Bottom navigation --}}
    <nav class="bottom-nav">
        <div class="grid grid-cols-7 gap-0 max-w-lg mx-auto px-2">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>
            <a href="{{ route('boxer.profile') }}" class="{{ request()->routeIs('boxer.profile') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
            <a href="{{ route('daily.log') }}" class="{{ request()->routeIs('daily.log') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Log
            </a>
            <a href="{{ route('meals') }}" class="{{ request()->routeIs('meals') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Meals
            </a>
            <a href="{{ route('injuries') }}" class="{{ request()->routeIs('injuries') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                Injury
            </a>
            <a href="{{ route('chat') }}" class="{{ request()->routeIs('chat') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Coach
            </a>
            <a href="{{ route('fights') }}" class="{{ request()->routeIs('fights') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Fights
            </a>
        </div>
    </nav>

    @livewireScripts
</body>
</html>
