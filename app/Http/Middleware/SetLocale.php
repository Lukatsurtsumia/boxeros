<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Drive the whole app's language off the signed-in fighter's saved locale.
     * Runs on every web request (including Livewire updates) so the UI, validation
     * messages, and __() strings all switch together. Guests fall back to 'en'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Signed-in fighters carry their saved locale; guests (login/register/landing)
        // fall back to a session choice so the language toggle works before login.
        $locale = $request->user()?->locale ?? $request->session()->get('locale');

        if (!in_array($locale, ['en', 'fr'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
