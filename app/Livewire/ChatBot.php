<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Http;

class ChatBot extends Component
{
    public string $message = '';
    public bool $loading = false;

    public function sendMessage()
    {
        $this->validate(['message' => 'required|string|min:1|max:2000']);

        $userMessage = $this->message;
        $this->message = '';

        auth()->user()->chatMessages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        $this->loading = true;
        $this->dispatch('scrollToBottom');

        $this->getAiResponse($userMessage);
    }

    private function getAiResponse(string $userMessage)
    {
        $user = auth()->user();
        $profile = $user->boxerProfile;
        $todayLog = $user->dailyLogs()->whereDate('log_date', today())->first();
        $activeInjuries = $user->injuries()->where('status', 'active')->get();
        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $recentMeals = $user->meals()->whereDate('eaten_at', today())->get();

        $systemPrompt = "You are CORNER — the ultimate boxing AI coach and mentor. You are wise, experienced, and deeply knowledgeable about boxing, sports nutrition, injury recovery, mental strength, and peak performance. You speak like a seasoned trainer who truly cares about the fighter's success and wellbeing.\n\n";

        $systemPrompt .= "FIGHTER PROFILE:\n";
        $systemPrompt .= "Name: {$user->name}\n";
        if ($profile) {
            $systemPrompt .= "Nickname: " . ($profile->nickname ?? 'N/A') . "\n";
            $systemPrompt .= "Weight: " . ($profile->current_weight ?? 'N/A') . " kg | Goal: " . ($profile->goal_weight ?? 'N/A') . " kg\n";
            $systemPrompt .= "Height: " . ($profile->height_cm ?? 'N/A') . " cm\n";
            $systemPrompt .= "Experience: {$profile->experience_years} years | Record: {$profile->wins}W-{$profile->losses}L-{$profile->draws}D\n";
            $systemPrompt .= "Gym: " . ($profile->gym ?? 'N/A') . " | Trainer: " . ($profile->trainer ?? 'N/A') . "\n";
            $systemPrompt .= "Stance: {$profile->stance}\n";
        }

        if ($todayLog) {
            $systemPrompt .= "\nTODAY'S STATUS:\n";
            $systemPrompt .= "Weight: " . ($todayLog->weight_kg ?? 'N/A') . " kg\n";
            $systemPrompt .= "Water: {$todayLog->water_liters} L\n";
            $systemPrompt .= "Calories: " . ($todayLog->calories_consumed ?? 'N/A') . "\n";
            $systemPrompt .= "Sleep: " . ($todayLog->sleep_hours ?? 'N/A') . " hrs\n";
            $systemPrompt .= "Training: " . ($todayLog->training_minutes ?? 'N/A') . " min\n";
            $systemPrompt .= "Mood: {$todayLog->mood} | Energy: {$todayLog->energy_level}/10\n";
        }

        if ($recentMeals->count() > 0) {
            $systemPrompt .= "\nTODAY'S MEALS: " . $recentMeals->pluck('name')->join(', ') . "\n";
        }

        if ($activeInjuries->count() > 0) {
            $systemPrompt .= "\nACTIVE INJURIES: " . $activeInjuries->map(fn($i) => "{$i->title} ({$i->body_part}, {$i->severity})")->join('; ') . "\n";
        }

        if ($nextFight) {
            $daysUntil = now()->diffInDays($nextFight->fight_date, false);
            $systemPrompt .= "\nNEXT FIGHT: vs {$nextFight->opponent_name} in {$daysUntil} days ({$nextFight->fight_date->format('M d, Y')})\n";
        }

        $systemPrompt .= "\nAlways give practical, specific advice. Be motivating but honest. Keep responses concise and actionable. Use boxing terminology naturally.";

        $history = $user->chatMessages()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 600,
                'system' => $systemPrompt,
                'messages' => $history,
            ]);

            if ($response->successful()) {
                $aiContent = $response->json('content.0.text');
            } else {
                $aiContent = "I'm having trouble connecting right now. Check your API key in the settings and try again, champ.";
            }
        } catch (\Exception $e) {
            $aiContent = "Connection issue. Make sure your ANTHROPIC_API_KEY is set in the .env file.";
        }

        $user->chatMessages()->create([
            'role' => 'assistant',
            'content' => $aiContent,
        ]);

        $this->loading = false;
        $this->dispatch('scrollToBottom');
    }

    public function clearChat()
    {
        auth()->user()->chatMessages()->delete();
    }

    public function render()
    {
        $messages = auth()->user()->chatMessages()->orderBy('created_at')->take(50)->get();
        return view('livewire.chat-bot', compact('messages'))->layout('layouts.app');
    }
}
