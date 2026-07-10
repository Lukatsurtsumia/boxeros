@php $u = auth()->user(); $success = request('checkout') === 'success'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Your subscription') }} · BoxerOS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <style>
        :root { --blood:#c0392b; --blood-dark:#8f0e18; --gold:#f39c12; }
        * { box-sizing: border-box; }
        body { background:#0b0c0e; color:#e8e8ee; font-family: system-ui, -apple-system, sans-serif; line-height:1.6; margin:0; padding:2.5rem 1rem 4rem; }
        .wrap { max-width: 440px; margin:0 auto; text-align:center; }
        .brand { font-weight:800; letter-spacing:0.5px; font-size:1.4rem; margin-bottom:2rem; }
        .brand .r { color: var(--blood); }
        h1 { font-size:1.6rem; margin:0 0 0.5rem; }
        p { color:#b9b9c4; }
        .pill { display:inline-block; padding:0.3rem 0.8rem; border-radius:999px; font-size:0.8rem; font-weight:700; }
        .pill.gold { background:rgba(243,156,18,0.15); color:var(--gold); }
        .pill.green { background:rgba(46,204,113,0.15); color:#2ecc71; }
        .card { background:#15151c; border:1px solid rgba(243,156,18,0.25); border-radius:16px; padding:1.75rem; margin:1.75rem 0; text-align:left; }
        .price { font-size:2.6rem; font-weight:800; color:#fff; }
        .price small { font-size:1rem; color:#8a8a95; font-weight:400; }
        ul { list-style:none; padding:0; margin:1rem 0 1.5rem; }
        li { padding:0.3rem 0; color:#d4d4dc; }
        .btn { display:block; width:100%; text-align:center; border:0; cursor:pointer; background:var(--blood); color:#fff; font-weight:700; font-size:1rem; padding:0.95rem 1rem; border-radius:12px; text-decoration:none; }
        .btn:hover { background:#a93226; }
        .btn.ghost { background:transparent; border:1px solid #333; color:#b9b9c4; margin-top:0.75rem; }
        .muted { color:#7a7a85; font-size:0.82rem; margin-top:1.25rem; }
        a.link { color:var(--gold); }
        form { margin:0; }
        .logout { background:none; border:0; color:#7a7a85; cursor:pointer; font-size:0.85rem; text-decoration:underline; margin-top:1.5rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand" translate="no"><span class="r">BOXER</span>OS</div>

        @if($u->is_admin)
            {{-- Owner / admin - always full access, never billed --}}
            <span class="pill green">👑 {{ __('Owner') }}</span>
            <h1 style="margin-top:1rem;">{{ __('You have full access') }}</h1>
            <p>{{ __('As the owner of BoxerOS you have unlimited access - no subscription needed.') }}</p>
            <a href="{{ route('dashboard') }}" class="btn" style="margin-top:1.5rem;">{{ __('Go to your dashboard') }} →</a>

        @elseif($u->subscribedActive())
            {{-- Already subscribed --}}
            <span class="pill green">✓ {{ __('Subscribed') }}</span>
            <h1 style="margin-top:1rem;">{{ __("You're all set") }}</h1>
            <p>{{ __('Your BoxerOS subscription is active. Thanks for being in the corner.') }}</p>
            <a href="{{ route('dashboard') }}" class="btn" style="margin-top:1.5rem;">{{ __('Go to your dashboard') }} →</a>
            <a href="{{ route('billing.manage') }}" class="btn ghost" style="margin-top:0.75rem;">{{ __('Manage or cancel subscription') }}</a>

        @elseif($success)
            {{-- Just paid - the route confirms via the Paddle API; this is the rare fallback. --}}
            <span class="pill green">✓ {{ __('Payment received') }}</span>
            <h1 style="margin-top:1rem;">{{ __('Thank you!') }}</h1>
            <p>{{ __('Your payment went through. Tap continue to enter BoxerOS.') }}</p>
            <a href="{{ route('billing') }}?checkout=success" class="btn" style="margin-top:1.5rem;">{{ __('Continue') }} →</a>

        @else
            {{-- Trial active (can subscribe early) or trial ended (must subscribe) --}}
            @if($u->onTrial())
                <span class="pill gold">{{ __(':days days left in your free trial', ['days' => $u->trialDaysLeft()]) }}</span>
                <h1 style="margin-top:1rem;">{{ __('Keep your corner') }}</h1>
                <p>{{ __('Your trial is still active. Subscribe any time to keep full access when it ends.') }}</p>
            @else
                <span class="pill gold">{{ __('Free trial ended') }}</span>
                <h1 style="margin-top:1rem;">{{ __('Subscribe to keep training') }}</h1>
                <p>{{ __('Your 7-day free trial is over. Subscribe to unlock BoxerOS again.') }}</p>
            @endif

            <div class="card">
                <div class="price">€7.99<small> / {{ __('month') }}</small></div>
                <ul>
                    <li>🥊 {{ __('CORNER - your AI coach, unlimited') }}</li>
                    <li>⚖️ {{ __('Weight & weigh-in tracking') }}</li>
                    <li>🍽️ {{ __('Nutrition & hydration logging') }}</li>
                    <li>📅 {{ __('Fight countdown & training plans') }}</li>
                    <li>📊 {{ __('Weekly recaps & insights') }}</li>
                </ul>
                <button class="btn" onclick="boxerosCheckout()">{{ __('Subscribe - €7.99/month') }}</button>
                @if($u->onTrial())
                    <a href="{{ route('dashboard') }}" class="btn ghost">{{ __('Keep using my trial') }}</a>
                @endif
            </div>

            <p class="muted">{{ __('Cancel anytime · VAT included · Secure checkout by Paddle.') }}
                <br><a href="{{ route('refunds') }}" class="link">{{ __('Refund policy') }}</a></p>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">{{ __('Log out') }}</button>
        </form>
    </div>

    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
    <script>
        (function () {
            if (typeof Paddle === 'undefined') return;
            Paddle.Environment.set(@json(config('services.paddle.env') ?: 'sandbox'));
            @if(config('services.paddle.client_token'))
                Paddle.Initialize({ token: @json(config('services.paddle.client_token')) });
            @endif
        })();

        function boxerosCheckout() {
            if (typeof Paddle === 'undefined') { alert('Checkout is still loading - please try again in a moment.'); return; }
            Paddle.Checkout.open({
                items: [{ priceId: @json(config('services.paddle.price_id')), quantity: 1 }],
                customer: { email: @json($u->email) },
                customData: { user_id: @json((string) $u->id) },
                settings: {
                    successUrl: @json(route('billing') . '?checkout=success'),
                    theme: 'dark',
                    locale: @json(app()->getLocale() === 'fr' ? 'fr' : 'en'),
                    showAddDiscounts: false
                }
            });
        }
    </script>
</body>
</html>
