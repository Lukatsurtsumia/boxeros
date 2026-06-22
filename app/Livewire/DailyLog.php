<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DailyLog as DailyLogModel;

class DailyLog extends Component
{
    public $log_date;
    public $weight_kg, $water_liters = 0, $calories_consumed;
    public $sleep_hours, $training_minutes, $mood = 'good', $energy_level = 5, $notes;

    public bool $showForm = false;

    public function mount()
    {
        $this->log_date = today()->toDateString();
        $this->loadToday();
    }

    public function loadToday()
    {
        $log = auth()->user()->dailyLogs()->whereDate('log_date', $this->log_date)->first();
        if ($log) {
            $this->weight_kg = $log->weight_kg;
            $this->water_liters = $log->water_liters;
            $this->calories_consumed = $log->calories_consumed;
            $this->sleep_hours = $log->sleep_hours;
            $this->training_minutes = $log->training_minutes;
            $this->mood = $log->mood;
            $this->energy_level = $log->energy_level;
            $this->notes = $log->notes;
        }
    }

    public function addWater($amount)
    {
        $this->water_liters = round($this->water_liters + $amount, 2);
        $this->saveLog();
    }

    public function saveLog()
    {
        $this->validate([
            'weight_kg' => 'nullable|numeric|min:30|max:200',
            'water_liters' => 'numeric|min:0|max:20',
            'calories_consumed' => 'nullable|integer|min:0',
            'sleep_hours' => 'nullable|integer|min:0|max:24',
            'training_minutes' => 'nullable|integer|min:0',
            'energy_level' => 'integer|min:1|max:10',
        ]);

        auth()->user()->dailyLogs()->updateOrCreate(
            ['log_date' => $this->log_date],
            [
                'weight_kg' => $this->weight_kg,
                'water_liters' => $this->water_liters,
                'calories_consumed' => $this->calories_consumed,
                'sleep_hours' => $this->sleep_hours,
                'training_minutes' => $this->training_minutes,
                'mood' => $this->mood,
                'energy_level' => $this->energy_level,
                'notes' => $this->notes,
            ]
        );

        $this->showForm = false;
        session()->flash('message', 'Daily log saved!');
    }

    public function render()
    {
        $logs = auth()->user()->dailyLogs()->orderByDesc('log_date')->take(14)->get();
        $profile = auth()->user()->boxerProfile;
        $waterGoal = $profile?->daily_water_goal_liters ?? 3.0;
        $waterPct = $waterGoal > 0 ? min(100, round(($this->water_liters / $waterGoal) * 100)) : 0;

        return view('livewire.daily-log', compact('logs', 'waterGoal', 'waterPct'))
            ->layout('layouts.app');
    }
}
