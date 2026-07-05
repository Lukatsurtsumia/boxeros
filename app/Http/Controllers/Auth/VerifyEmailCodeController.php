<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Verifies the 6-digit code the user typed against the one stashed in the cache. On success
 * the account is marked verified and the (already logged-in) user is sent into the app.
 */
class VerifyEmailCodeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->validate(['code' => ['required', 'string']]);

        $entered = preg_replace('/\D/', '', (string) $request->input('code'));
        $expected = Cache::get($user->emailVerificationCodeKey());

        if (! $expected || ! hash_equals((string) $expected, (string) $entered)) {
            throw ValidationException::withMessages([
                'code' => __('That code is incorrect or has expired — request a new one below.'),
            ]);
        }

        $user->markEmailAsVerified();
        Cache::forget($user->emailVerificationCodeKey());
        event(new Verified($user));

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('message', __('Email verified — welcome to BoxerOS! 🥊'));
    }
}
