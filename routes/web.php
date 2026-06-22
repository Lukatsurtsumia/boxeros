<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\BoxerProfile;
use App\Livewire\DailyLog;
use App\Livewire\MealTracker;
use App\Livewire\InjuryTracker;
use App\Livewire\FightCalendar;
use App\Livewire\ChatBot;
use App\Livewire\PhotoGallery;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/boxer/profile', BoxerProfile::class)->name('boxer.profile');
    Route::get('/log', DailyLog::class)->name('daily.log');
    Route::get('/meals', MealTracker::class)->name('meals');
    Route::get('/injuries', InjuryTracker::class)->name('injuries');
    Route::get('/fights', FightCalendar::class)->name('fights');
    Route::get('/chat', ChatBot::class)->name('chat');
    Route::get('/photos', PhotoGallery::class)->name('photos');
});

require __DIR__.'/auth.php';
