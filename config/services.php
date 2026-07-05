<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

    // Google sign-in (Socialite). The "Continue with Google" button only appears when
    // client_id is set, so the feature ships dormant until credentials are added.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/auth/google/callback'),
    ],

    // Paddle (billing). `gate` is the master paywall switch — while false, every account
    // has full access (no lockout), so the code can ship and be tested before going live.
    'paddle' => [
        'env'           => env('PADDLE_ENV', 'sandbox'),   // 'sandbox' or 'production'
        'client_token'  => env('PADDLE_CLIENT_TOKEN'),     // public, used by Paddle.js
        'api_key'       => env('PADDLE_API_KEY'),          // secret, server-side API
        'webhook_secret'=> env('PADDLE_WEBHOOK_SECRET'),   // secret, verifies webhooks
        'price_id'      => env('PADDLE_PRICE_ID'),          // the €7.99/mo price
        'gate'          => env('PAYMENTS_GATE', false),     // turn the paywall ON/OFF
        'trial_days'    => (int) env('PADDLE_TRIAL_DAYS', 7),
    ],

];
