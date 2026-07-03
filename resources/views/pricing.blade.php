@extends('layouts.legal')
@section('title', 'Pricing · BoxerOS')
@section('content')
    <h1>Pricing</h1>
    <p>One simple plan. Start with a 7-day free trial — no charge until it ends, cancel anytime.</p>

    <div style="background:#15151c; border:1px solid rgba(243,156,18,0.25); border-radius:14px; padding:1.5rem 1.5rem 1.75rem; margin:1.5rem 0; max-width:420px;">
        <div style="color:var(--gold); font-weight:800; letter-spacing:0.5px; font-size:0.8rem; text-transform:uppercase;">BoxerOS — full access</div>
        <div style="margin:0.5rem 0 0.25rem;">
            <span style="font-size:2.6rem; font-weight:800; color:#fff;">€7.99</span>
            <span style="color:#8a8a95;"> / month</span>
        </div>
        <div style="color:var(--gold); font-weight:600; font-size:0.95rem; margin-bottom:1rem;">7-day free trial included</div>

        <ul style="list-style:none; padding:0; margin:0 0 1.5rem;">
            <li style="padding:0.3rem 0;">🥊 CORNER — your AI boxing coach, unlimited</li>
            <li style="padding:0.3rem 0;">⚖️ Weight &amp; weigh-in tracking with trends</li>
            <li style="padding:0.3rem 0;">🍽️ Nutrition &amp; hydration logging</li>
            <li style="padding:0.3rem 0;">📅 Fight countdown &amp; training plans</li>
            <li style="padding:0.3rem 0;">📊 Weekly recaps &amp; progress insights</li>
        </ul>

        <a href="{{ route('register') }}"
           style="display:block; text-align:center; background:var(--blood); color:#fff; text-decoration:none;
                  font-weight:700; padding:0.85rem 1rem; border-radius:10px;">
            Start your 7-day free trial →
        </a>
        <p class="muted" style="text-align:center; margin:0.85rem 0 0;">No card needed to start · Cancel anytime · VAT included at checkout</p>
    </div>

    <h2>How billing works</h2>
    <p>Your first 7 days are free. After that, if you don't cancel, your subscription begins at €7.99/month and
    renews automatically each month. You can cancel anytime and keep access until the end of the paid period.
    See our <a href="{{ route('refunds') }}">Refund &amp; Cancellation Policy</a>.</p>

    <h2>Payments &amp; VAT</h2>
    <p>Payments are securely handled by <strong>Paddle</strong>, our Merchant of Record, who collects and remits
    any applicable VAT for you at checkout. By subscribing you agree to our
    <a href="{{ route('terms') }}">Terms of Service</a> and <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
@endsection
