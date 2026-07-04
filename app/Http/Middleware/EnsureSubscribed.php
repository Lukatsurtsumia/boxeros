<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paywall gate. Sends a signed-in user to the billing page when their free trial has
 * ended and they don't have an active subscription. While services.paddle.gate is off,
 * hasAppAccess() always returns true, so this is a no-op until we switch payments on.
 */
class EnsureSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasAppAccess()) {
            return redirect()->route('billing');
        }

        return $next($request);
    }
}
