<?php

namespace App\Livewire;

use Livewire\Component;
use App\Support\Corner;

class ChatBot extends Component
{
    public string $message = '';
    public bool $loading = false;
    public bool $awaitingAi = false;

    public function sendMessage(): void
    {
        $this->validate(['message' => 'required|string|min:1|max:2000']);

        $text = trim($this->message);
        $this->message = '';
        $this->loading = true;

        auth()->user()->chatMessages()->create([
            'role'    => 'user',
            'content' => $text,
        ]);

        $this->awaitingAi = true;
        $this->dispatch('getAiResponse');
    }

    #[\Livewire\Attributes\On('getAiResponse')]
    public function getAiResponse(): void
    {
        $lastUserMsg = auth()->user()
            ->chatMessages()
            ->where('role', 'user')
            ->latest()
            ->value('content');

        $reply = $this->fetchReply($lastUserMsg ?? '');

        auth()->user()->chatMessages()->create([
            'role'    => 'assistant',
            'content' => $reply,
        ]);

        $this->loading    = false;
        $this->awaitingAi = false;
        $this->dispatch('scrollToBottom');
    }

    private function buildSystemPrompt(): string
    {
        $user      = auth()->user();
        $profile   = $user->boxerProfile;
        $today     = $user->dailyLogs()->whereDate('log_date', today())->first();
        $injuries  = $user->injuries()->where('status', 'active')->get();
        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $todayMeals = $user->meals()->whereDate('eaten_at', today())->get();

        $currentWeight = $user->currentWeight();
        $todayWeighIns = $user->weightEntries()->whereDate('weighed_at', today())->orderBy('weighed_at')->get();
        $preW  = $todayWeighIns->firstWhere('context', 'pre_workout');
        $postW = $todayWeighIns->firstWhere('context', 'post_workout');

        $p  = "You are CORNER — an elite AI boxing coach and performance advisor. You combine the wisdom of legendary trainers with cutting-edge sports science. You are direct, motivating, and deeply knowledgeable about:\n";
        $p .= "- Boxing technique, tactics, and fight preparation\n";
        $p .= "- Sports nutrition, calorie estimation, and weight management for combat sports\n";
        $p .= "- Injury prevention, recovery, and return-to-training protocols\n";
        $p .= "- Mental performance, focus, and fight psychology\n";
        $p .= "- Conditioning, sparring strategy, and periodization\n";
        $p .= "- Weekly training plans for cutting, gaining, or maintaining weight\n\n";

        // Fighter identity
        $p .= "FIGHTER: {$user->name}\n";
        if ($profile) {
            $p .= "Nickname: " . ($profile->nickname ? "\"{$profile->nickname}\"" : 'None') . "\n";
            $p .= "Division: " . ($profile->weight_class ?? 'Unknown') . "\n";
            $p .= "Record: {$profile->wins}W – {$profile->losses}L – {$profile->draws}D ({$profile->total_fights} fights)\n";
            $p .= "Current weight: " . ($currentWeight ? round($currentWeight, 1) . " kg" : 'Not logged') . "\n";
            $p .= "Goal weight: " . ($profile->goal_weight ? "{$profile->goal_weight} kg" : 'Not set') . "\n";
            $p .= "Height: " . ($profile->height_cm ? "{$profile->height_cm} cm" : 'Unknown') . "\n";
            $p .= "Reach: " . ($profile->reach_cm ? "{$profile->reach_cm} cm" : 'Unknown') . "\n";
            $p .= "Stance: {$profile->stance}\n";
            $p .= "Experience: {$profile->experience_years} years\n";
            $p .= "Gym: " . ($profile->gym ?? 'Unknown') . "\n";
            $p .= "Trainer: " . ($profile->trainer ?? 'Unknown') . "\n";
            if ($profile->bio) $p .= "Background: {$profile->bio}\n";
        }

        // Today
        $p .= "\nTODAY (" . today()->format('D M j, Y') . "):\n";
        if ($todayWeighIns->count() > 0) {
            $p .= "Weigh-ins today: " . $todayWeighIns->map(fn ($w) => "{$w->context_label} " . round($w->weight_kg, 1) . "kg @ {$w->weighed_at->format('g:ia')}")->implode(', ') . "\n";
            if ($preW && $postW) {
                $sweat = round($preW->weight_kg - $postW->weight_kg, 1);
                $p .= "Training weight: {$preW->weight_kg} kg before → {$postW->weight_kg} kg after ({$sweat} kg sweat loss)\n";
            }
        }
        if ($today) {
            $p .= "Water: {$today->water_liters} L";
            if ($today->soda_cans ?? 0) $p .= " | Soda: {$today->soda_cans} can(s)";
            if ($today->alcohol_units ?? 0) $p .= " | Alcohol: {$today->alcohol_units} drink(s)";
            $p .= "\n";
            $p .= "Sleep: " . ($today->sleep_hours ? "{$today->sleep_hours} hrs" : 'Not logged') . "\n";
            if ($today->training_minutes) {
                $hrs = round($today->training_minutes / 60, 1);
                $type = $today->training_type ?? 'training';
                $p .= "Training: {$today->training_minutes} min of {$type} ({$hrs} hrs)\n";
            } else {
                $p .= "Training: Rest day or not logged\n";
            }
            $p .= "Mood: {$today->mood} | Energy: {$today->energy_level}/10\n";
            if ($today->notes) $p .= "Notes: {$today->notes}\n";
            if ($today->calories_consumed) $p .= "Calories confirmed: ~{$today->calories_consumed} kcal\n";
        } else {
            $p .= "No daily log yet for today.\n";
        }

        // Today's meals
        if ($todayMeals->count() > 0) {
            $p .= "\nMEALS TODAY:\n";
            foreach ($todayMeals as $meal) {
                $time = $meal->eaten_time ? " at {$meal->eaten_time}" : '';
                $kcal = $meal->calories ? " (~{$meal->calories} kcal)" : '';
                $p .= "- {$meal->meal_type}{$time}: {$meal->name}{$kcal}\n";
            }
            $totalEst = $todayMeals->whereNotNull('calories')->sum('calories');
            if ($totalEst) $p .= "Estimated total: ~{$totalEst} kcal\n";
        }

        // Active injuries
        if ($injuries->count() > 0) {
            $p .= "\nACTIVE INJURIES:\n";
            foreach ($injuries as $inj) {
                $p .= "- {$inj->body_part}: {$inj->title} ({$inj->severity}) since {$inj->injury_date->format('M j')}\n";
                if ($inj->description) $p .= "  Details: {$inj->description}\n";
            }
        }

        // Next fight
        if ($nextFight) {
            $daysOut = (int) now()->diffInDays($nextFight->fight_date, false);
            $p .= "\nNEXT FIGHT: vs {$nextFight->opponent_name}";
            if ($nextFight->event_name) $p .= " — {$nextFight->event_name}";
            $p .= "\nDate: {$nextFight->fight_date->format('D M j, Y')} ({$daysOut} days out)\n";
            if ($nextFight->location) $p .= "Location: {$nextFight->location}\n";
            if ($nextFight->weight_class) $p .= "Division: {$nextFight->weight_class}\n";
            $p .= "Rounds: {$nextFight->rounds}\n";
        }

        // 7-day weekly summary
        $weekStart = now()->subDays(6)->startOfDay();
        $weekLogs  = $user->dailyLogs()->where('log_date', '>=', $weekStart)->orderBy('log_date')->get();

        if ($weekLogs->count() > 0) {
            $p .= "\nTHIS WEEK (" . now()->subDays(6)->format('M j') . " – today):\n";

            $weights = $weekLogs->whereNotNull('weight_kg')->pluck('weight_kg');
            if ($weights->count() > 1) {
                $p .= "Weight range: {$weights->min()} – {$weights->max()} kg (avg " . round($weights->avg(), 1) . " kg)\n";
            }

            $totalTrainMins = $weekLogs->sum('training_minutes');
            $trainDays      = $weekLogs->where('training_minutes', '>', 0)->count();
            $p .= "Training: " . round($totalTrainMins / 60, 1) . " hrs total, {$trainDays} sessions\n";

            $trainingTypes = $weekLogs->whereNotNull('training_type')->pluck('training_type')->filter()->countBy();
            if ($trainingTypes->count() > 0) {
                $p .= "Sessions: " . $trainingTypes->map(fn($c, $t) => "{$c}× {$t}")->implode(', ') . "\n";
            }

            $avgSleep = $weekLogs->whereNotNull('sleep_hours')->avg('sleep_hours');
            if ($avgSleep) $p .= "Avg sleep: " . round($avgSleep, 1) . " hrs/night\n";

            $avgWater = $weekLogs->avg('water_liters');
            $p .= "Avg water: " . round($avgWater, 1) . " L/day\n";

            $totalSoda    = $weekLogs->sum('soda_cans');
            $totalAlcohol = $weekLogs->sum('alcohol_units');
            if ($totalSoda)    $p .= "Soda this week: {$totalSoda} can(s)\n";
            if ($totalAlcohol) $p .= "Alcohol this week: {$totalAlcohol} drink(s)\n";

            $avgEnergy = $weekLogs->avg('energy_level');
            $p .= "Avg energy: " . round($avgEnergy, 1) . "/10\n";

            $weekCalories = $weekLogs->whereNotNull('calories_consumed')->sum('calories_consumed');
            if ($weekCalories) $p .= "Total calories logged: ~" . number_format((int)$weekCalories) . " kcal\n";
        }

        $p .= "\nINSTRUCTIONS:\n";
        $p .= "- Address the fighter by first name when natural\n";
        $p .= "- Give specific, actionable advice — reference their real data\n";
        $p .= "- Be honest even when it's uncomfortable — real coaches are\n";
        $p .= "- When asked for a weekly plan, build it around their actual schedule, weight goal, and next fight date\n";
        $p .= "- For nutrition questions, estimate calories by food name/description if exact macros aren't logged\n";
        $p .= "- If they describe or mention a food photo, identify it and estimate its calories\n";
        $p .= "- For injuries, give practical guidance and always recommend a medical professional for serious issues\n";
        $p .= "- Keep answers focused and concise — fighters need clarity, not essays\n";

        return $p;
    }

    private function fetchReply(string $userMessage): string
    {
        if (!Corner::enabled()) return $this->offlineReply($userMessage);

        $history = auth()->user()
            ->chatMessages()
            ->orderByDesc('created_at')
            ->take(30)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        $reply = Corner::ask($history, $this->buildSystemPrompt(), 'claude-sonnet-4-6', 1000);

        return $reply ?? "Couldn't reach CORNER right now — check your connection and try again.";
    }

    private function offlineReply(string $message): string
    {
        $msg = mb_strtolower($message);

        if (str_contains($msg, 'week') || str_contains($msg, 'plan')) {
            return "CORNER is offline (no API key). Once you add ANTHROPIC_API_KEY to .env, I'll build you a full weekly plan based on your real logs, fight date, and weight goal.";
        }
        if (str_contains($msg, 'calor') || str_contains($msg, 'eat') || str_contains($msg, 'food') || str_contains($msg, 'meal')) {
            return "CORNER is offline. Set ANTHROPIC_API_KEY in .env and I'll estimate calories, identify foods from photos, and give you a proper nutrition plan.";
        }
        if (str_contains($msg, 'water') || str_contains($msg, 'hydrat')) {
            return "CORNER is offline. Once live, I'll coach hydration based on your actual daily logs and training intensity.";
        }
        if (str_contains($msg, 'weight') || str_contains($msg, 'cut')) {
            return "Weight questions are important — I'll answer with your real numbers once ANTHROPIC_API_KEY is set in .env.";
        }
        if (str_contains($msg, 'injur') || str_contains($msg, 'pain') || str_contains($msg, 'hurt')) {
            return "Add ANTHROPIC_API_KEY to .env and I'll give you a full recovery assessment based on your logged injuries.";
        }
        if (str_contains($msg, 'fight') || str_contains($msg, 'camp')) {
            return "Fight prep needs to be built around your timeline and record. Set the API key in .env and I'll coach the camp properly.";
        }

        return "CORNER needs an API key to talk. Add ANTHROPIC_API_KEY=sk-ant-... to your .env, run `php artisan config:clear`, and I'll be live with full knowledge of your profile, weekly logs, meals, injuries, and fight date.";
    }

    public function clearChat(): void
    {
        auth()->user()->chatMessages()->delete();
        $this->loading    = false;
        $this->awaitingAi = false;
    }

    public function render()
    {
        $messages  = auth()->user()->chatMessages()->orderBy('created_at')->take(60)->get();
        $hasApiKey = (bool) config('services.anthropic.key');

        return view('livewire.chat-bot', compact('messages', 'hasApiKey'))
            ->layout('layouts.app');
    }
}
