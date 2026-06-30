@extends('layouts.legal')
@section('title', 'Privacy Policy · BoxerOS')
@section('content')
    <h1>Privacy Policy</h1>
    <p>This policy explains what BoxerOS collects, why, and your rights over your data.</p>

    <h2>What we collect</h2>
    <ul>
        <li><strong>Account data</strong> — your name, email, and password (stored hashed).</li>
        <li><strong>Health & training data you enter</strong> — weight, training sessions, meals, sleep,
        mood, fights, and goals.</li>
        <li><strong>Coaching content</strong> — your messages to CORNER and the plans/recaps it generates.</li>
    </ul>

    <h2>How we use it</h2>
    <p>Your data is used only to run the app for you: to show your dashboard, build your plans, power the
    CORNER coach, and track your progress. We do not sell your data.</p>

    <h2>AI processing</h2>
    <p>To provide AI coaching, relevant data (e.g. your profile and recent logs) is sent to our AI provider
    (Anthropic) to generate responses. It is processed to answer your request and is subject to that
    provider's terms. Do not share information you are not comfortable processing this way.</p>

    <h2>Storage & security</h2>
    <p>Your data is stored in our application database. We take reasonable measures to protect it, but no
    system is perfectly secure.</p>

    <h2>Your rights</h2>
    <p>You can view and edit your data in the app at any time. To export or permanently delete your account
    and data, contact us and we will action your request.</p>

    <h2>Cookies</h2>
    <p>We use only essential cookies required to keep you logged in and secure (session and CSRF). We do not
    use third-party advertising or tracking cookies.</p>
@endsection
