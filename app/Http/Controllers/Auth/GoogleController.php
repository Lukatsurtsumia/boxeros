<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * "Continue with Google" sign-in via Laravel Socialite. Lets a fighter log in with their
 * Gmail account — no password to create or remember. New accounts get the same free trial
 * as a normal sign-up and are treated as email-verified (Google already verified them).
 */
class GoogleController extends Controller
{
    /** Send the user off to Google to choose an account. */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /** Google sends them back here — find or create the account, then log them in. */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => __('Google sign-in failed — please try again.'),
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Existing account (maybe registered with a password first) — link the Google id.
            if (! $user->google_id) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Boxer',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null, // Google-only account — no password
                'email_verified_at' => now(), // Google has already verified the address
                'locale' => in_array(session('locale'), ['en', 'fr'], true) ? session('locale') : 'en',
                'trial_ends_at' => now()->addDays((int) config('services.paddle.trial_days', 7)),
            ]);

            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
