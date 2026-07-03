@extends('layouts.legal')
@section('title', 'Refund & Cancellation Policy · BoxerOS')
@section('content')
    <h1>Refund &amp; Cancellation Policy</h1>
    <p>BoxerOS is a monthly subscription with a free trial. This page explains your trial, billing,
    cancellation, and refund rights.</p>

    <div class="callout">
        <strong>7-day free trial.</strong> Every new account starts with a 7-day free trial of the full app.
        You are not charged during the trial. If you cancel before it ends, you pay nothing.
    </div>

    <h2>Billing</h2>
    <p>After the free trial, BoxerOS costs <strong>€7.99 per month</strong>. The subscription renews
    automatically each month until you cancel. Prices include any applicable VAT, shown at checkout.</p>

    <h2>Cancel anytime</h2>
    <p>You can cancel your subscription at any time from your account's billing page. When you cancel, your
    subscription stays active until the end of the period you've already paid for, and you are not billed
    again. There are no cancellation fees.</p>

    <h2>Refunds</h2>
    <p>If you were charged by mistake, experienced a technical problem that prevented you from using the
    service, or are otherwise unhappy, contact us and we'll make it right. As an EU consumer you may also
    have a statutory 14-day right of withdrawal for digital services; note that if you ask to start using
    paid features immediately, that right may not apply once the service has been fully provided.</p>

    <h2>Who processes your payment</h2>
    <p>Our order process and payments are handled by our online reseller <strong>Paddle.com</strong>, which is
    the Merchant of Record for all orders. Paddle handles billing, tax, and refunds on our behalf. Refund
    requests are processed back to your original payment method.</p>

    <h2>How to request a refund or get help</h2>
    <p>Email us at {{ config('mail.from.address', 'support@boxeros.app') }} with your account email and a short
    description, and we'll respond as quickly as we can.</p>
@endsection
