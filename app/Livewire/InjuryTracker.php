<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Injury;
use Illuminate\Support\Facades\Http;

class InjuryTracker extends Component
{
    public $body_part, $title, $description, $severity = 'minor';
    public $injury_date, $expected_recovery, $notes;
    public bool $showForm = false;
    public bool $loadingFeedback = false;

    public function mount()
    {
        $this->injury_date = today()->toDateString();
    }

    public function save()
    {
        $this->validate([
            'body_part' => 'required|string|max:100',
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'severity' => 'required|in:minor,moderate,serious',
            'injury_date' => 'required|date',
            'expected_recovery' => 'nullable|date|after:injury_date',
        ]);

        $injury = auth()->user()->injuries()->create([
            'body_part' => $this->body_part,
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'injury_date' => $this->injury_date,
            'expected_recovery' => $this->expected_recovery,
            'notes' => $this->notes,
            'status' => 'active',
        ]);

        $this->getAiFeedback($injury);
        $this->reset(['body_part', 'title', 'description', 'severity', 'expected_recovery', 'notes']);
        $this->showForm = false;
    }

    private function getAiFeedback(Injury $injury)
    {
        if (!config('services.anthropic.key')) return;

        $profile = auth()->user()->boxerProfile;
        $context = "Boxer profile: weight {$profile?->current_weight}kg, experience {$profile?->experience_years} years.";

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 400,
                'messages' => [[
                    'role' => 'user',
                    'content' => "You are a sports medicine advisor for professional boxers. {$context}\n\nThe boxer has reported this injury:\n- Body part: {$injury->body_part}\n- Injury: {$injury->title}\n- Description: {$injury->description}\n- Severity: {$injury->severity}\n\nGive concise, practical recovery advice and indicate when they can safely return to training. Be direct and professional.",
                ]],
            ]);

            if ($response->successful()) {
                $feedback = $response->json('content.0.text');
                $injury->update(['ai_feedback' => $feedback]);
            }
        } catch (\Exception $e) {
            // AI feedback is optional; silently fail
        }
    }

    public function updateStatus($id, $status)
    {
        auth()->user()->injuries()->findOrFail($id)->update(['status' => $status]);
    }

    public function render()
    {
        $injuries = auth()->user()->injuries()->orderByDesc('injury_date')->get();
        $active = $injuries->where('status', 'active');
        $recovering = $injuries->where('status', 'recovering');
        $healed = $injuries->where('status', 'healed');

        return view('livewire.injury-tracker', compact('injuries', 'active', 'recovering', 'healed'))
            ->layout('layouts.app');
    }
}
