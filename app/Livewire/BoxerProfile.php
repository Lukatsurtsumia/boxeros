<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class BoxerProfile extends Component
{
    use WithFileUploads;

    public $nickname, $weight_class, $date_of_birth, $height_cm;
    public int $experience_years = 0, $wins = 0, $losses = 0, $draws = 0;
    public $gym, $trainer, $stance = 'orthodox', $reach_cm, $bio, $goal_weight;
    public $avatar;

    public bool $editing = false;

    public function mount(): void
    {
        $profile = auth()->user()->boxerProfile;
        if ($profile) {
            $this->fill($profile->only([
                'nickname', 'weight_class', 'height_cm',
                'experience_years', 'wins', 'losses', 'draws',
                'gym', 'trainer', 'stance', 'reach_cm', 'bio', 'goal_weight',
            ]));
            $this->date_of_birth = $profile->date_of_birth?->format('Y-m-d');
        }
    }

    public function save(): void
    {
        $this->validate([
            'nickname'         => 'nullable|string|max:50',
            'date_of_birth'    => 'nullable|date|before:today',
            'goal_weight'      => 'nullable|numeric|min:40|max:200',
            'height_cm'        => 'nullable|numeric|min:100|max:250',
            'reach_cm'         => 'nullable|numeric|min:100|max:250',
            'experience_years' => 'integer|min:0|max:50',
            'wins'             => 'integer|min:0',
            'losses'           => 'integer|min:0',
            'draws'            => 'integer|min:0',
            'avatar'           => 'nullable|image|max:2048',
        ]);

        $data = [
            'nickname'         => $this->nickname,
            'weight_class'     => $this->weight_class,
            'date_of_birth'    => $this->date_of_birth ?: null,
            'height_cm'        => $this->height_cm,
            'reach_cm'         => $this->reach_cm,
            'experience_years' => $this->experience_years,
            'wins'             => $this->wins,
            'losses'           => $this->losses,
            'draws'            => $this->draws,
            'total_fights'     => $this->wins + $this->losses + $this->draws,
            'gym'              => $this->gym,
            'trainer'          => $this->trainer,
            'stance'           => $this->stance,
            'bio'              => $this->bio,
            'goal_weight'      => $this->goal_weight,
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
        $user         = auth()->user();
        $profile      = $user->boxerProfile;
        $recentFights = $user->fights()->orderByDesc('fight_date')->take(5)->get();
        $currentWeight = $user->currentWeight();
        $latestWeight  = $user->latestWeight();
        $weightAgo     = $latestWeight ? $latestWeight->weighed_at->diffForHumans(['short' => true]) : null;

        return view('livewire.boxer-profile', compact('profile', 'recentFights', 'currentWeight', 'latestWeight', 'weightAgo'))
            ->layout('layouts.app');
    }
}
