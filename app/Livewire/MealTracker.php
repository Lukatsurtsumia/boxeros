<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Meal;

class MealTracker extends Component
{
    use WithFileUploads;

    public $name, $meal_type = 'breakfast', $calories, $protein_g, $carbs_g, $fat_g;
    public $description, $photo, $eaten_at;
    public bool $showForm = false;

    public function mount()
    {
        $this->eaten_at = today()->toDateString();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack,pre-workout,post-workout',
            'calories' => 'nullable|integer|min:0',
            'protein_g' => 'nullable|numeric|min:0',
            'carbs_g' => 'nullable|numeric|min:0',
            'fat_g' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:4096',
            'eaten_at' => 'required|date',
        ]);

        $data = [
            'name' => $this->name,
            'meal_type' => $this->meal_type,
            'calories' => $this->calories,
            'protein_g' => $this->protein_g,
            'carbs_g' => $this->carbs_g,
            'fat_g' => $this->fat_g,
            'description' => $this->description,
            'eaten_at' => $this->eaten_at,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('meals', 'public');
        }

        auth()->user()->meals()->create($data);

        $this->reset(['name', 'calories', 'protein_g', 'carbs_g', 'fat_g', 'description', 'photo']);
        $this->showForm = false;
        session()->flash('message', 'Meal logged!');
    }

    public function delete($id)
    {
        auth()->user()->meals()->findOrFail($id)->delete();
    }

    public function render()
    {
        $meals = auth()->user()->meals()
            ->whereDate('eaten_at', today())
            ->orderByRaw("CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")
            ->get();

        $totalCalories = $meals->sum('calories');
        $totalProtein = $meals->sum('protein_g');
        $totalCarbs = $meals->sum('carbs_g');
        $totalFat = $meals->sum('fat_g');

        return view('livewire.meal-tracker', compact('meals', 'totalCalories', 'totalProtein', 'totalCarbs', 'totalFat'))
            ->layout('layouts.app');
    }
}
