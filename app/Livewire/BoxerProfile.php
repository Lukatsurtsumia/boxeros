<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\BoxerProfile as BoxerProfileModel;

class BoxerProfile extends Component
{
    use WithFileUploads;

    public $nickname, $weight_class, $current_weight, $height_cm;
    public $experience_years = 0, $total_fights = 0, $wins = 0, $losses = 0, $draws = 0;
    public $gym, $trainer, $stance = 'orthodox', $reach_cm, $bio;
    public $goal_weight, $daily_water_goal_liters = 3.0, $daily_calorie_goal = 2500;
    public $avatar;

    public bool $editing = false;

    public function mount()
    {
        $profile = auth()->user()->boxerProfile;
        if ($profile) {
            $this->fill($profile->only([
                'nickname', 'weight_class', 'current_weight', 'height_cm',
                'experience_years', 'total_fights', 'wins', 'losses', 'draws',
                'gym', 'trainer', 'stance', 'reach_cm', 'bio',
                'goal_weight', 'daily_water_goal_liters', 'daily_calorie_goal',
            ]));
        }
    }

    public function save()
    {
        $this->validate([
            'nickname' => 'nullable|string|max:50',
            'current_weight' => 'nullable|numeric|min:40|max:200',
            'height_cm' => 'nullable|numeric|min:100|max:250',
            'experience_years' => 'integer|min:0|max:50',
            'goal_weight' => 'nullable|numeric|min:40|max:200',
            'daily_water_goal_liters' => 'numeric|min:1|max:10',
            'daily_calorie_goal' => 'integer|min:1000|max:10000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nickname' => $this->nickname,
            'weight_class' => $this->weight_class,
            'current_weight' => $this->current_weight,
            'height_cm' => $this->height_cm,
            'experience_years' => $this->experience_years,
            'total_fights' => $this->total_fights,
            'wins' => $this->wins,
            'losses' => $this->losses,
            'draws' => $this->draws,
            'gym' => $this->gym,
            'trainer' => $this->trainer,
            'stance' => $this->stance,
            'reach_cm' => $this->reach_cm,
            'bio' => $this->bio,
            'goal_weight' => $this->goal_weight,
            'daily_water_goal_liters' => $this->daily_water_goal_liters,
            'daily_calorie_goal' => $this->daily_calorie_goal,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        auth()->user()->boxerProfile()->updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        $this->editing = false;
        session()->flash('message', 'Profile updated!');
    }

    public function render()
    {
        $profile = auth()->user()->boxerProfile;
        $recentFights = auth()->user()->fights()->orderByDesc('fight_date')->take(5)->get();
        return view('livewire.boxer-profile', compact('profile', 'recentFights'))
            ->layout('layouts.app');
    }
}
