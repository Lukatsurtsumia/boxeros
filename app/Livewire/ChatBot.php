<?php

namespace App\Livewire;

use Livewire\Component;
use App\Support\Corner;

class ChatBot extends Component
{
    public string $message = '';
    public bool $loading = false;
    public bool $awaitingAi = false;

    /** Long-term memory loaded once per session so the prompt stays cache-stable mid-conversation. */
    public string $memorySnapshot = '';

    public function mount(): void
    {
        $this->memorySnapshot = auth()->user()->coachMemory?->content ?? '';
    }

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
        $this->dispatch('corner-input-clear');
        $this->dispatch('scroll-to-bottom');
    }

    /** Open a session with a FREE, data-driven greeting (no AI call) — costs nothing. */
    public function startSession(): void
    {
        $user    = auth()->user();
        $current = $user->currentWeight();
        $goal    = $user->boxerProfile?->goal_weight;

        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $fightDays = $nextFight ? max(0, (int) now()->diffInDays($nextFight->fight_date, false)) : null;

        $fr = $user->isFrench();

        $bits = [];
        if ($current) {
            if ($fr) {
                $w = "tu es à **" . round($current, 1) . " kg**";
                if ($goal) {
                    $d = round($current - $goal, 1);
                    $w .= $d > 0 ? " ({$d} à perdre)" : ($d < 0 ? " (" . abs($d) . " à prendre)" : " — au poids");
                }
            } else {
                $w = "you're at **" . round($current, 1) . " kg**";
                if ($goal) {
                    $d = round($current - $goal, 1);
                    $w .= $d > 0 ? " ({$d} to cut)" : ($d < 0 ? " (" . abs($d) . " to gain)" : " — on weight");
                }
            }
            $bits[] = $w;
        }
        if ($fightDays !== null) {
            $bits[] = $fr ? "**{$fightDays} jours** avant le combat" : "**{$fightDays} days** to the fight";
        }

        if ($fr) {
            $opener  = "Allez {$user->name} — au travail. ";
            if ($bits) $opener .= ucfirst(implode(', ', $bits)) . ". ";
            $opener .= "On bosse quoi aujourd'hui — **technique, nutrition, poids, ou récupération** ?";
        } else {
            $flag = collect(\App\Support\CornerInsights::for($user))->whereIn('level', ['alert', 'warn'])->first();
            $opener  = "Yo {$user->name} — back to work. ";
            if ($bits) $opener .= ucfirst(implode(', ', $bits)) . ". ";
            if ($flag) $opener .= "{$flag['icon']} Heads up: " . lcfirst($flag['title']) . ". ";
            $opener .= "What's the focus today — **training, nutrition, making weight, or recovery**?";
        }

        $user->chatMessages()->create(['role' => 'assistant', 'content' => $opener]);
        $this->dispatch('scroll-to-bottom');
    }

    /** One-tap suggested question — fills it in and sends it straight to CORNER. */
    public function ask(string $text): void
    {
        $this->message = $text;
        $this->sendMessage();
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
        $this->dispatch('scroll-to-bottom');

        // Refresh long-term memory only every 4th message — keeps it current while cutting
        // background (Haiku) calls by ~75%.
        if (auth()->user()->chatMessages()->where('role', 'user')->count() % 4 === 0) {
            $this->dispatch('updateCoachMemory');
        }
    }

    /**
     * Background pass: merge new durable facts from the latest exchange into the fighter's
     * long-term memory file. Runs on cheap Haiku, capped per day, and never blocks the reply.
     */
    #[\Livewire\Attributes\On('updateCoachMemory')]
    public function updateCoachMemory(): void
    {
        if (!Corner::enabled() || !Corner::allow('memory')) return;

        $user = auth()->user();

        $recent = $user->chatMessages()
            ->orderByDesc('created_at')->take(6)->get()->reverse()
            ->map(fn ($m) => strtoupper($m->role) . ': ' . $m->content)
            ->implode("\n");

        if ($recent === '') return;

        $existing = $user->coachMemory?->content ?: '(no memory yet)';

        $system = "You maintain a concise long-term memory file about a boxer, for their AI coach to read before every session. "
            . "Merge any NEW durable facts from the latest conversation into the existing memory: goals, target/walk-around weight, "
            . "preferences, recurring problems, injuries mentioned, commitments the fighter made, what motivates them, and key advice "
            . "already given (so the coach doesn't repeat it). Keep what's still relevant, drop nothing important, and remove anything "
            . "clearly outdated. Organise as short bullet points under a few headings. Hard limit ~250 words. "
            . "Output ONLY the updated memory file — no preamble, no commentary.";

        $payload = "EXISTING MEMORY:\n{$existing}\n\nLATEST CONVERSATION:\n{$recent}";

        $updated = Corner::ask([['role' => 'user', 'content' => $payload]], $system, 'claude-haiku-4-5-20251001', 600);

        if ($updated) {
            $user->coachMemory()->updateOrCreate(
                ['user_id' => $user->id],
                ['content' => trim($updated)]
            );
        }
    }

    private function buildSystemPrompt(): string
    {
        $user      = auth()->user();
        $profile   = $user->boxerProfile;
        $today     = $user->dailyLogs()->whereDate('log_date', today())->first();
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

        // Coach's own knowledge base — authoritative reference material (if any has been added).
        if ($kb = \App\Models\KnowledgeEntry::promptBlock()) {
            $p .= "\n" . $kb;
        }

        // Fighter identity
        $p .= "\nFIGHTER: {$user->name}\n";
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

        // Long-term memory — what CORNER has learned about this fighter across past sessions.
        if (trim($this->memorySnapshot) !== '') {
            $p .= "\nLONG-TERM MEMORY (durable facts you've learned about this fighter across past sessions — rely on these, build on them, and don't re-ask what you already know):\n";
            $p .= $this->memorySnapshot . "\n";
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
        $p .= "- This reply is for {$user->name} ALONE. Ground every answer in THEIR real numbers above — their actual weight, goal, fight date, energy, sleep, training and logs. Never give a generic answer that could apply to any boxer: if a sentence would fit anyone, rewrite it so it only fits this fighter. Two different fighters must never receive the same answer.\n";
        $p .= "- Be honest even when it's uncomfortable — real coaches are\n";
        $p .= "- When asked for a weekly plan, build it around their actual schedule, weight goal, and next fight date\n";
        $p .= "- For nutrition questions, estimate calories by food name/description if exact macros aren't logged\n";
        $p .= "- If they describe or mention a food photo, identify it and estimate its calories\n";
        $p .= "- For injuries, give practical guidance and always recommend a medical professional for serious issues\n";
        $p .= "- Keep answers focused and concise — fighters need clarity, not essays\n";
        $p .= "- Format for easy scanning, matched to the content. A quick question gets a short paragraph with **bold** key numbers. For anything structured — meal plans, training plans, nutrition breakdowns, day-by-day options, comparisons — lay it out like a polished coaching document: clear `##` section headers, a markdown table for daily totals/macros, **bold** numbers, a `> blockquote` line for each section's key total so it stands out, and a `---` divider between major sections. Be consistent: every plan/nutrition answer should look this clean, not flat text.\n";
        $p .= "- Match the length to the question: a quick question gets a few sentences; only build long structured plans when explicitly asked.\n";
        $p .= "- Answer with confidence and completeness the FIRST time, so the fighter never has to ask the same thing twice. Don't hedge with 'it depends' and stop there — if something is unclear, state a sensible assumption out loud and then give your concrete best recommendation. Always end with a clear, actionable next step.\n";

        $p .= "\nSTYLE & PERSONALITY:\n";
        $p .= "- Talk like a real corner-man: professional and razor-sharp, but with a bit of humour and edge. A well-placed quip lands well — just keep it to a line, never a comedy routine.\n";
        $p .= "- Bring it to life with the greats. Reference legendary boxers to make a point vivid (Ali's footwork, Tyson's peek-a-boo pressure, Mayweather's shoulder roll, Lomachenko's angles, Canelo's body work, Pacquiao's volume) — ONE sharp reference, not a history lecture.\n";
        $p .= "- If you don't yet know who the fighter idolises, ask early: 'Which pro's style do you love?' Then tailor your advice to that style and point out where their own build/data fits it or differs.\n";
        $p .= "- Push film study: when relevant, name a specific fight or fighter to watch on YouTube for the exact skill they're working on (e.g. 'study Lomachenko vs Rigondeaux for angles', 'watch Mayweather vs Canelo for defence').\n";
        $p .= "- PLANS ARE ONE WEEK AT A TIME — never build month-long or longer programs. If asked for a month, give THIS week and say you'll evolve it next week based on how it goes.\n";
        $p .= "- Be sharp and well-organised — cut filler, not substance. Never sacrifice useful detail, a number, or a complete answer just to be brief: accuracy and completeness come first, then keep the tone tight.\n";

        $p .= "\nWHEN BUILDING A MEAL / RECIPE / NUTRITION PLAN:\n";
        $p .= "- Give concrete recipes: list each ingredient with a real quantity (g, ml, or units).\n";
        $p .= "- Show calories and macros (protein / carbs / fat) per item and a clear total.\n";
        $p .= "- Anchor portions to the fighter's weight goal and daily calorie target: if cutting, prioritise high protein and satiety; if gaining, add clean calories.\n";
        $p .= "- Keep meals realistic and easy to shop for. State clearly that calorie/macro numbers are estimates.\n";

        $p .= "\nWHEN BUILDING A TRAINING PLAN:\n";
        $p .= "- Lay it out day by day. For each session give: type, duration, rounds/sets, intensity (easy / moderate / hard), and a one-line purpose.\n";
        $p .= "- Periodise around the next fight date: build early, sharpen mid-camp, taper and make weight in the final week. With no fight set, build a balanced maintenance week.\n";
        $p .= "- Respect their real schedule and logged sessions (e.g. if they already train twice a day or add cycling). Always include rest/recovery.\n";

        $p .= "\nSAFETY (never skip):\n";
        $p .= "- Never prescribe dangerous weight cuts. Flag rapid cuts (more than ~1-1.5% of body weight per week, or last-minute dehydration) as risky and point them to a qualified nutritionist.\n";
        $p .= "- For pain, injuries, or medical symptoms: give general guidance only, do not diagnose, and clearly recommend a doctor or physiotherapist for anything serious or worsening.\n";
        $p .= "- Do not advise on banned substances or PEDs except to discourage their use.\n";
        $p .= "- Add a brief one-line disclaimer whenever giving medical or aggressive weight-cut advice.\n";

        if (auth()->user()->isFrench()) {
            $p .= "\nLANGUAGE: The fighter's app language is FRENCH. Write your ENTIRE reply in natural, fluent French (français) — all coaching, plans, nutrition and explanations, using proper French boxing terminology. Keep the same markdown structure and formatting.\n";
        }

        return $p;
    }

    private function fetchReply(string $userMessage): string
    {
        if (!Corner::enabled()) return $this->offlineReply($userMessage);

        if (!Corner::allow('chat')) {
            return "That's today's coaching done — you've used all " . Corner::DAILY_LIMITS['chat']
                . " messages. I'll be fresh in the morning. Rest up, review what we covered, and come back tomorrow. 🥊";
        }

        // Last 12 messages only — long-term memory carries older context, so we don't resend 30.
        $history = auth()->user()
            ->chatMessages()
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        // Sonnet 4.6 — the top-quality coaching model, for the most complete and accurate answers.
        // Prompt caching bills the big system prompt at ~0.1x on follow-ups, so quality stays high
        // while cost holds around ~1-1.5c per message. 1500 tokens gives room for a full answer.
        $reply = Corner::ask($history, $this->buildSystemPrompt(), 'claude-sonnet-4-6', 1500);

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
            return "Add ANTHROPIC_API_KEY to .env and I'll give you practical recovery guidance — and I'll always tell you when something needs a doctor or physio.";
        }
        if (str_contains($msg, 'fight') || str_contains($msg, 'camp')) {
            return "Fight prep needs to be built around your timeline and record. Set the API key in .env and I'll coach the camp properly.";
        }

        return "CORNER needs an API key to talk. Add ANTHROPIC_API_KEY=sk-ant-... to your .env, run `php artisan config:clear`, and I'll be live with full knowledge of your profile, weekly logs, meals, and fight date.";
    }

    public function clearChat(): void
    {
        auth()->user()->chatMessages()->delete();
        $this->loading    = false;
        $this->awaitingAi = false;
    }

    public function render()
    {
        $messages      = auth()->user()->chatMessages()->orderBy('created_at')->take(60)->get();
        $hasApiKey     = (bool) config('services.anthropic.key');
        $chatRemaining = $hasApiKey ? Corner::remaining('chat') : null;

        return view('livewire.chat-bot', compact('messages', 'hasApiKey', 'chatRemaining'))
            ->layout('layouts.app');
    }
}
