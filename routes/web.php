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

Route::middleware(['auth', 'verified'])->group(function () {
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
