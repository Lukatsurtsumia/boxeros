<?php

namespace App\Livewire;

use Livewire\Component;

class Onboarding extends Component
{
    public string $nickname = '';
    public string $weight_class = '';
    public $current_weight = null;
    public $goal_weight = null;
    public $experience_years = null;

    public function mount(): void
    {
        // Already set up? Skip straight to the app.
        if (auth()->user()->boxerProfile) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function save(): void
    {
        $this->validate([
            'nickname'         => 'nullable|string|max:50',
            'weight_class'     => 'nullable|string|max:50',
            'current_weight'   => 'nullable|numeric|min:30|max:200',
            'goal_weight'      => 'nullable|numeric|min:30|max:200',
            'experience_years' => 'nullable|integer|min:0|max:60',
        ]);

        $user = auth()->user();
        $user->boxerProfile()->create([
            'nickname'         => $this->nickname ?: null,
            'weight_class'     => $this->weight_class ?: null,
            'goal_weight'      => $this->goal_weight ?: null,
            'experience_years' => (int) ($this->experience_years ?: 0),
            'wins' => 0, 'losses' => 0, 'draws' => 0, 'total_fights' => 0,
            'stance' => 'orthodox',
        ]);

        if ($this->current_weight) {
            $user->weightEntries()->create([
                'weight_kg'  => $this->current_weight,
                'context'    => 'morning',
                'weighed_at' => now(),
            ]);
        }

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function skip(): void
    {
        auth()->user()->boxerProfile()->create([
            'wins' => 0, 'losses' => 0, 'draws' => 0, 'total_fights' => 0, 'stance' => 'orthodox',
        ]);
        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.onboarding')->layout('layouts.app');
    }
}
