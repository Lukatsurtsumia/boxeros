<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Soft-launch: keep BoxerOS out of search engines. Remove this line when you want public discovery. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name', 'BoxerOS') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

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
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text-primary);
            min-height: 100vh;
            padding-bottom: calc(5rem + env(safe-area-inset-bottom));
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
            padding: 0.5rem 0 calc(0.5rem + env(safe-area-inset-bottom));
            z-index: 100;
        }
        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.58rem;
            font-weight: 500;
            transition: color 0.15s;
            padding: 0.25rem 0;
        }
        .bottom-nav a.active { color: var(--blood); font-weight: 700; }
        .bottom-nav a:hover { color: var(--blood); }
        .bottom-nav svg { width: 20px; height: 20px; }

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
            padding: calc(0.75rem + env(safe-area-inset-top)) 1rem 0.75rem;
        }

        /* Mood icons */
        .mood-btn { cursor: pointer; font-size: 1.5rem; opacity: 0.4; transition: all 0.2s; filter: grayscale(1); }
        .mood-btn:hover, .mood-btn.selected { opacity: 1; filter: grayscale(0); transform: scale(1.2); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--dark-border); border-radius: 4px; }

        /* ── Reusable dashboard primitives ── */
        .section-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
        }
        .stat-tile {
            border-radius: 12px;
            padding: 0.85rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .stat-tile-blood { background: rgba(192,57,43,0.08);  border-color: rgba(192,57,43,0.2); }
        .stat-tile-gold  { background: rgba(243,156,18,0.08);  border-color: rgba(243,156,18,0.2); }
        .stat-tile-blue  { background: rgba(52,152,219,0.08);  border-color: rgba(52,152,219,0.2); }

        /* ── Desktop sidebar (≥1024px) ── */
        .sidebar { display: none; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 0.9rem;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.18s;
        }
        .sidebar-link svg { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar-link:hover { background: rgba(255,255,255,0.05); color: var(--text-primary); }
        .sidebar-link.active {
            background: rgba(192,57,43,0.14);
            color: #ff6b6b;
            border: 1px solid rgba(192,57,43,0.3);
        }

        @media (min-width: 1024px) {
            body { padding-bottom: 2rem; }
            .sidebar {
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0; left: 0; bottom: 0;
                width: 240px;
                background: rgba(17,17,24,0.95);
                border-right: 1px solid var(--dark-border);
                padding: 1.25rem 0.9rem;
                z-index: 60;
            }
        }
    </style>
</head>
<body>

    @php
        // Single source of truth for navigation — looped in both the desktop sidebar and the
        // mobile bottom bar. `d` is the SVG path; all icons share the same <svg> wrapper.
        $navItems = [
            ['route' => 'dashboard',     'label' => __('Home'),    'd' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'plan',          'label' => __('Plan'),    'd' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['route' => 'boxer.profile', 'label' => __('Profile'), 'd' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['route' => 'daily.log',     'label' => __('Log'),     'd' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'meals',         'label' => __('Meals'),   'd' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
            ['route' => 'chat',          'label' => __('Coach'),   'd' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['route' => 'fights',        'label' => __('Fights'),  'd' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ];
    @endphp

    {{-- Flash message --}}
    @if(session('message'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
         class="fixed top-4 right-4 z-50 card card-gold px-4 py-3 text-sm font-medium"
         style="border-color: rgba(243,156,18,0.5); color: var(--gold);">
        {{ session('message') }}
    </div>
    @endif

    {{-- Desktop sidebar (≥1024px) --}}
    <aside class="sidebar">
        <div class="flex items-center gap-2 px-2 mb-6" translate="no">
            <span class="font-display text-2xl font-bold" style="color: var(--blood);">BOXER</span>
            <span class="font-display text-2xl font-bold text-white">OS</span>
        </div>
        <nav class="flex flex-col gap-1 flex-1">
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['d'] }}"/></svg>
                {{ $item['label'] }}
            </a>
            @endforeach
        </nav>
        <div class="mt-4 flex items-center justify-between gap-2">
            @include('partials.lang-toggle')
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="btn-ghost w-full text-sm">{{ __('Logout') }}</button>
            </form>
        </div>
    </aside>

    {{-- Mobile top header (<1024px) --}}
    <div class="top-header flex items-center justify-between lg:hidden">
        <div class="flex items-center gap-2" translate="no">
            <span class="font-display text-xl font-bold" style="color: var(--blood);">BOXER</span>
            <span class="font-display text-xl font-bold text-white">OS</span>
        </div>
        <div class="flex items-center gap-2">
            @include('partials.lang-toggle')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost py-1 px-3 text-xs">{{ __('Logout') }}</button>
            </form>
        </div>
    </div>

    {{-- Page content --}}
    <main class="px-3 pt-4 lg:pt-8 lg:pr-8 lg:pl-[264px]">
        <div class="max-w-lg lg:max-w-6xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    {{-- Mobile bottom navigation (<1024px) --}}
    <nav class="bottom-nav lg:hidden">
        <div class="grid grid-cols-7 gap-0 max-w-lg mx-auto px-1">
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['d'] }}"/></svg>
                {{ $item['label'] }}
            </a>
            @endforeach
        </div>
    </nav>

    @livewireScripts
</body>
</html>
