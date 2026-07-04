<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\BoxerProfile;
use App\Livewire\DailyLog;
use App\Livewire\MealTracker;
use App\Livewire\FightCalendar;
use App\Livewire\ChatBot;
use App\Livewire\KnowledgeBase;
use App\Livewire\PlanBoard;
use App\Livewire\Onboarding;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

// Public legal pages
Route::view('/disclaimer', 'legal.disclaimer')->name('disclaimer');
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/refunds', 'legal.refunds')->name('refunds');
Route::view('/pricing', 'pricing')->name('pricing');

// Language switch (English / French) — public so it works on the landing/auth pages too.
// Stores the choice in the session (used for guests); also persists to the user when signed in.
// The SetLocale middleware applies it on every request so the whole UI + CORNER switch together.
Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $loc = in_array($request->input('locale'), ['en', 'fr'], true) ? $request->input('locale') : 'en';
    $request->session()->put('locale', $loc);
    auth()->user()?->update(['locale' => $loc]);
    app()->setLocale($loc);
    return back();
})->name('locale.set');

// Paddle webhook — server-to-server, no auth/CSRF (signature-verified in the controller).
Route::post('/webhooks/paddle', \App\Http\Controllers\PaddleWebhookController::class)->name('paddle.webhook');

// Billing / upgrade page — reachable by any signed-in user (NOT behind the paywall gate),
// so someone whose trial has ended can still get here to subscribe.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/billing', function () {
        $user = auth()->user();

        // Coming back from a completed checkout — confirm the payment straight from the
        // Paddle API (instant, no waiting on the webhook), then send them into the app.
        if (request('checkout') === 'success') {
            if (! $user->subscribedActive()) {
                \App\Support\PaddleSync::refresh($user);
                $user = $user->fresh();
            }
            if ($user->subscribedActive()) {
                return redirect()->route('dashboard')
                    ->with('message', __('Payment successful — welcome to BoxerOS! 🥊'));
            }
        }

        return view('billing');
    })->name('billing');
});

// The app itself — gated by the paywall (`subscribed`) on top of auth + verified.
Route::middleware(['auth', 'verified', 'subscribed'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/boxer/profile', BoxerProfile::class)->name('boxer.profile');
    Route::get('/log', DailyLog::class)->name('daily.log');
    Route::get('/meals', MealTracker::class)->name('meals');
    Route::get('/fights', FightCalendar::class)->name('fights');
    Route::get('/chat', ChatBot::class)->name('chat');
    Route::get('/knowledge', KnowledgeBase::class)->name('knowledge');
    Route::get('/plan', PlanBoard::class)->name('plan');
    Route::get('/welcome', Onboarding::class)->name('onboarding');
});

require __DIR__.'/auth.php';
