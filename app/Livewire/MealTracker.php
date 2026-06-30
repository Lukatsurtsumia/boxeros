<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Meal;
use App\Support\Corner;
use App\Support\FoodDb;

class MealTracker extends Component
{
    public string $name = '';
    public string $meal_type = 'lunch';
    public string $description = '';
    public $eaten_at;
    public string $eaten_time = '';

    public bool $showForm = false;

    public ?int $editId = null;   // meal being corrected
    public $editKcal = null;

    public function mount(): void
    {
        $this->eaten_at   = today()->toDateString();
        $this->eaten_time = now()->format('H:i');
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'nullable|string|max:150',
            'meal_type'   => 'required|in:breakfast,lunch,dinner,snack,pre-workout,post-workout',
            'description' => 'nullable|string|max:500',
            'eaten_at'    => 'required|date',
            'eaten_time'  => 'nullable',
        ]);

        $meal = auth()->user()->meals()->create([
            'name'            => $this->name ?: 'Food',
            'meal_type'       => $this->meal_type,
            'description'     => $this->description ?: null,
            'eaten_at'        => $this->eaten_at,
            'eaten_time'      => $this->eaten_time ?: null,
            'calories_source' => 'unknown',
        ]);

        $this->autoEstimate($meal);
        $this->syncDaily();

        $this->reset(['name', 'description']);
        $this->eaten_time = now()->format('H:i');
        $this->showForm   = false;
        session()->flash('message', 'Meal logged!');
    }

    private function autoEstimate(Meal $meal): void
    {
        $desc = $meal->name !== 'Food' ? $meal->name : '';
        if ($meal->description) $desc .= ($desc ? ' ' : '') . $meal->description;

        // 1) Local food table first — real nutrition values, zero API cost.
        if ($desc !== '' && ($kcal = FoodDb::estimate($desc)) !== null) {
            $meal->calories        = $kcal;
            $meal->calories_source = 'ai_estimated'; // reuse existing enum value; shown to the user as a plain "estimate"
            $meal->save();
            return;
        }

        // 2) AI fallback — only for foods the local table doesn't know.
        if (!Corner::enabled() || !Corner::allow('meal')) return;

        $item = $desc ?: 'a typical ' . $meal->meal_type;
        $text = Corner::ask(
            [['role' => 'user', 'content' => "Estimate calories for: {$item}\nRespond with only: CALORIES: [number]"]],
            null,
            'claude-haiku-4-5-20251001',
            60
        );
        if (!$text) return;

        if (preg_match('/(\d{2,4})/', $text, $m)) {
            $calories = (int) $m[1];
            if ($calories >= 20 && $calories <= 4000) {
                $meal->calories        = $calories;
                $meal->calories_source = 'ai_estimated';
                $meal->save();
            }
        }
    }

    /** Accept CORNER's estimate for this meal as-is. */
    public function confirmExact(int $id): void
    {
        $meal = auth()->user()->meals()->find($id);
        if ($meal) {
            $meal->update(['calories_source' => 'confirmed']);
            $this->syncDaily();
        }
    }

    /** Open the inline editor to correct a meal's calories (it was more or less than estimated). */
    public function startFix(int $id): void
    {
        $meal = auth()->user()->meals()->find($id);
        if ($meal) {
            $this->editId   = $id;
            $this->editKcal = $meal->calories;
        }
    }

    public function saveFix(): void
    {
        $meal = $this->editId ? auth()->user()->meals()->find($this->editId) : null;
        if ($meal && is_numeric($this->editKcal)) {
            $meal->update([
                'calories'        => max(0, min(6000, (int) $this->editKcal)),
                'calories_source' => 'confirmed',
            ]);
            $this->syncDaily();
        }
        $this->editId = null;
        $this->editKcal = null;
    }

    public function cancelFix(): void
    {
        $this->editId = null;
        $this->editKcal = null;
    }

    /** Keep the day's total calories (used by the dashboard & plan) in sync with the meals. */
    private function syncDaily(): void
    {
        $total = (int) auth()->user()->meals()->whereDate('eaten_at', today())->sum('calories');
        auth()->user()->dailyLogs()->updateOrCreate(
            ['log_date' => today()],
            ['calories_consumed' => $total]
        );
    }

    public function delete(int $id): void
    {
        $meal = auth()->user()->meals()->findOrFail($id);
        if ($meal->photo) \Storage::disk('public')->delete($meal->photo);
        $meal->delete();
        $this->syncDaily();
    }

    public function render()
    {
        $this->syncDaily(); // keep the cached daily total (read by chat/weekly recap) in step with the meals

        $meals = auth()->user()->meals()
            ->whereDate('eaten_at', today())
            ->orderByRaw("COALESCE(eaten_time, '23:59') ASC")
            ->orderBy('created_at')
            ->get();

        $total          = (int) $meals->sum('calories');
        $confirmedCount = $meals->where('calories_source', 'confirmed')->count();

        return view('livewire.meal-tracker', compact('meals', 'total', 'confirmedCount'))
            ->layout('layouts.app');
    }
}
