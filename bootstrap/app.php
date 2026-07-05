<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy (Coolify/Traefik) that terminates SSL in front of the app,
        // so Laravel sees requests as HTTPS — correct url() generation + secure cookies, no redirect loops.
        $middleware->trustProxies(at: '*');

        // Apply the fighter's saved language (en/fr) on every web request,
        // including Livewire updates, so the whole UI switches together.
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            // Count unique daily visitors (for the admin panel).
            \App\Http\Middleware\TrackVisit::class,
        ]);

        // Paywall gate (used on the main app route group).
        $middleware->alias([
            'subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
        ]);

        // Paddle posts webhooks server-to-server, so they carry no CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhooks/paddle',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
