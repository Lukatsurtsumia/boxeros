<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DailyLog;
use App\Models\Fight;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $profile = $user->boxerProfile;
        $todayLog = $user->dailyLogs()->whereDate('log_date', today())->first();
        $recentLogs = $user->dailyLogs()->orderByDesc('log_date')->take(7)->get();
        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $activeInjuries = $user->injuries()->where('status', 'active')->count();
        $todayMeals = $user->meals()->whereDate('eaten_at', today())->get();
        $todayCalories = $todayMeals->sum('calories');
        $waterGoal = $profile?->daily_water_goal_liters ?? 3.0;
        $waterToday = $todayLog?->water_liters ?? 0;
        $waterPct = $waterGoal > 0 ? min(100, round(($waterToday / $waterGoal) * 100)) : 0;
        $calorieGoal = $profile?->daily_calorie_goal ?? 2500;
        $caloriePct = $calorieGoal > 0 ? min(100, round(($todayCalories / $calorieGoal) * 100)) : 0;

        return view('livewire.dashboard', compact(
            'profile', 'todayLog', 'recentLogs', 'nextFight',
            'activeInjuries', 'todayCalories', 'waterToday', 'waterPct',
            'caloriePct', 'calorieGoal', 'waterGoal'
        ))->layout('layouts.app');
    }
}
