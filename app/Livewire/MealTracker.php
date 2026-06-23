<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Meal;
use App\Support\Corner;

class MealTracker extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $meal_type = 'lunch';
    public string $description = '';
    public $photo;
    public $eaten_at;
    public string $eaten_time = '';

    public bool $showForm = false;

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
            'photo'       => 'nullable|image|max:6144',
            'eaten_at'    => 'required|date',
            'eaten_time'  => 'nullable',
        ]);

        $photoPath = $this->photo ? $this->photo->store('meals', 'public') : null;

        $meal = auth()->user()->meals()->create([
            'name'            => $this->name ?: 'Food',
            'meal_type'       => $this->meal_type,
            'description'     => $this->description ?: null,
            'eaten_at'        => $this->eaten_at,
            'eaten_time'      => $this->eaten_time ?: null,
            'photo'           => $photoPath,
            'calories_source' => 'unknown',
        ]);

        $this->autoEstimate($meal, $photoPath);

        $this->reset(['name', 'description', 'photo']);
        $this->eaten_time = now()->format('H:i');
        $this->showForm   = false;
        session()->flash('message', 'Meal logged!');
    }

    private function autoEstimate(Meal $meal, ?string $photoPath): void
    {
        if (!Corner::enabled()) return;

        $content = $this->buildEstimationContent($meal, $photoPath);
        $text    = Corner::ask([['role' => 'user', 'content' => $content]], null, 'claude-haiku-4-5-20251001', 60);
        if (!$text) return;

        $text = trim($text);

        // If photo was used and meal name was generic, try to extract identified food name
        if ($photoPath && $meal->name === 'Food') {
            if (preg_match('/FOOD:\s*([^\n|]{2,60})/i', $text, $m)) {
                $meal->name = trim($m[1]);
            }
        }

        // Extract calorie estimate
        if (preg_match('/(\d{2,4})/i', $text, $m)) {
            $calories = (int) $m[1];
            if ($calories >= 20 && $calories <= 4000) {
                $meal->calories        = $calories;
                $meal->calories_source = 'ai_estimated';
            }
        }

        $meal->save();
    }

    private function buildEstimationContent(Meal $meal, ?string $photoPath): array|string
    {
        $desc = $meal->name !== 'Food' ? $meal->name : '';
        if ($meal->description) $desc .= ($desc ? ' — ' : '') . $meal->description;

        if ($photoPath) {
            $fullPath = storage_path('app/public/' . $photoPath);
            if (file_exists($fullPath)) {
                $imageData = base64_encode(file_get_contents($fullPath));
                $mimeType  = mime_content_type($fullPath);
                $prompt    = $desc
                    ? "This is a photo of: {$desc}. Identify the food precisely and estimate calories. Reply:\nFOOD: [name]\nCALORIES: [number]"
                    : "Identify this food and estimate calories. Reply:\nFOOD: [name]\nCALORIES: [number]";
                return [
                    ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mimeType, 'data' => $imageData]],
                    ['type' => 'text', 'text' => $prompt],
                ];
            }
        }

        $item = $desc ?: 'a typical meal';
        return "Estimate calories for: {$item}\nRespond with only: CALORIES: [number]";
    }

    public function confirmCalories(string $direction): void
    {
        $estimated = (int) auth()->user()->meals()
            ->whereDate('eaten_at', today())
            ->whereNotNull('calories')
            ->sum('calories');

        $confirmed = match ($direction) {
            'less'  => (int) round($estimated * 0.8),
            'more'  => (int) round($estimated * 1.25),
            default => $estimated,
        };

        auth()->user()->dailyLogs()->updateOrCreate(
            ['log_date' => today()],
            ['calories_consumed' => $confirmed]
        );

        session()->flash('message', "Calories saved: ~{$confirmed} kcal");
    }

    public function delete(int $id): void
    {
        $meal = auth()->user()->meals()->findOrFail($id);
        if ($meal->photo) \Storage::disk('public')->delete($meal->photo);
        $meal->delete();
    }

    public function render()
    {
        $meals = auth()->user()->meals()
            ->whereDate('eaten_at', today())
            ->orderByRaw("COALESCE(eaten_time, '23:59') ASC")
            ->orderBy('created_at')
            ->get();

        $estimatedTotal = (int) $meals->whereNotNull('calories')->sum('calories');

        $confirmedToday = auth()->user()->dailyLogs()
            ->whereDate('log_date', today())
            ->value('calories_consumed');

        return view('livewire.meal-tracker', compact('meals', 'estimatedTotal', 'confirmedToday'))
            ->layout('layouts.app');
    }
}
