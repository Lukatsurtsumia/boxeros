<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Plan;
use App\Models\User;
use App\Support\Corner;
use Carbon\Carbon;

class PlanBoard extends Component
{
    public ?int $currentPlanId = null;
    public bool $generating = false;

    public bool $editing = false;
    public array $draft = [];   // editable working copy, keyed mon…sun
    public bool $planLimitHit = false;

    public function mount(): void
    {
        // Show the MOST RECENT plan by default — so a plan you just generated is right there to
        // review and follow, instead of silently falling back to your old active plan.
        // max('id') is deterministic even if two plans share a created_at second.
        $this->currentPlanId = auth()->user()->plans()->max('id');
    }

    /** Kick off generation — shows a loading state while CORNER builds the plan. */
    public function generate(): void
    {
        $this->planLimitHit = false;
        $this->generating = true;
        $this->dispatch('runGenerate');
    }

    #[\Livewire\Attributes\On('runGenerate')]
    public function runGenerate(): void
    {
        $user = auth()->user();

        // Over today's plan-build limit → spend nothing, create nothing. Point them to free editing.
        if (Corner::enabled() && !Corner::allow('plan')) {
            $this->generating   = false;
            $this->planLimitHit = true;
            return;
        }

        $data = null;
        if (Corner::enabled()) {
            $system = "You are CORNER, a world-class boxing coach and performance nutritionist. Build a 7-day plan that develops a champion. "
                . "Respond with ONLY valid JSON (no markdown, no commentary), in EXACTLY this shape:\n"
                . '{"title": string, "summary": string, "schedule": [{"day": "mon", "focus": string, "sessions": [{"type": string, "minutes": int, "detail": string}], "calories": int, "protein": int, "sleep": number, "weight": number}]}'
                . "\nThe schedule MUST contain 7 objects, one per day in order: mon, tue, wed, thu, fri, sat, sun.\n"
                . "RULES — follow every one:\n"
                . "1. This is a BOXER. Use ONLY these session types: boxing, sparring, gym, running. NEVER use yoga, cycling, swimming or anything else. Centre the week on boxing and sparring, with gym for strength and running for conditioning.\n"
                . "2. Make EVERY day different and purposeful (technique, sparring, strength, speed, road work). Periodise the week — never repeat the same session every day.\n"
                . "2b. CRITICAL — every session MUST have a 'detail': the real coaching content a paying pro expects. Give the structure (rounds×minutes or sets×reps), the intensity, and EXACTLY what to drill. Examples: 'Technique — 8×3 on the bag, straight rights into pivots; 4×3 pads working counters off the jab', 'Strength — 4×5 back squat @80%, 4×6 weighted pull-ups, 3-round core circuit', 'Road work — 40min Zone-2 steady + 6×100m strides'. A bare 'gym 50 min' with no detail is UNACCEPTABLE — write it like a real session card.\n"
                . "3. Include EXACTLY 2 rest days (sessions: [], with a recovery 'focus'). Place them smartly (e.g. mid-week and Sunday).\n"
                . "4. 'calories' must fit each day's load — higher on hard training days, lower on rest days, never identical every day. 'protein' in grams (~1.8-2.2 g per kg bodyweight).\n"
                . "5. 'sleep' in hours (7.5-9), a little more after the hardest days.\n"
                . "6. 'weight' is a daily checkpoint that moves GRADUALLY from the fighter's current weight toward the goal at a SAFE rate (cut ~0.5-1% of bodyweight per week; gain ~0.25-0.5 kg per week). It must change day to day and finish the week on a realistic checkpoint — do NOT jump to the final goal.\n"
                . "7. 'focus' is a short 3-6 word label for the day. 'title' names the week + phase; 'summary' is 1-2 sentences on the intent.\n"
                . "Be specific and professional — this is the standard a paying pro fighter expects.";

            if ($user->isFrench()) {
                $system .= "\nLANGUAGE: Write ALL human-readable text (title, summary, each day's focus, and especially every session 'detail') in natural, fluent French. The session 'type' values MUST stay English (boxing, sparring, gym, running) — only the readable text is French.";
            }

            $raw  = Corner::ask([['role' => 'user', 'content' => $this->fighterContext($user)]], $system, 'claude-sonnet-4-6', 2500);
            $data = $this->parsePlan($raw);
        }

        $data ??= $this->defaultPlan($user);

        $plan = $user->plans()->create($data + ['is_active' => false]);
        $this->currentPlanId = $plan->id;

        // Keep the list tidy — never let draft plans pile up beyond the most recent few.
        $keep = $user->plans()->where('is_active', false)->latest()->take(5)->pluck('id');
        $user->plans()->where('is_active', false)->whereNotIn('id', $keep)->delete();

        $this->generating = false;
        $this->dispatch('scrollTop');
    }

    public function activate(int $id): void
    {
        $user = auth()->user();
        $plan = $user->plans()->find($id);
        if (!$plan) return;
        $user->plans()->update(['is_active' => false]);
        $plan->update(['is_active' => true]);
    }

    public function deactivate(int $id): void
    {
        auth()->user()->plans()->whereKey($id)->update(['is_active' => false]);
    }

    public function delete(int $id): void
    {
        auth()->user()->plans()->whereKey($id)->delete();
        if ($this->currentPlanId === $id) {
            $this->currentPlanId = auth()->user()->plans()->latest()->value('id');
        }
    }

    public function show(int $id): void
    {
        $this->currentPlanId = $id;
    }

    // ───────── Editing — full user control over the plan ─────────

    public function editPlan(): void
    {
        $plan = $this->currentPlan();
        if (!$plan) return;

        $this->draft = [];
        foreach (Plan::DAYS as $key) {
            $d = $plan->dayPlan($key);
            $this->draft[$key] = [
                'focus'    => $d['focus'] ?? '',
                'sessions' => array_map(fn ($s) => ['type' => $s['type'], 'minutes' => $s['minutes'], 'detail' => $s['detail'] ?? ''], $d['sessions']),
                'calories' => $d['calories'],
                'sleep'    => $d['sleep'],
                'weight'   => $d['weight'],
            ];
        }
        $this->editing = true;
    }

    public function addSession(string $day): void
    {
        if (!in_array($day, Plan::DAYS, true)) return;
        $this->draft[$day]['sessions'][] = ['type' => 'boxing', 'minutes' => 30, 'detail' => ''];
    }

    public function removeSession(string $day, int $i): void
    {
        if (!isset($this->draft[$day]['sessions'][$i])) return;
        unset($this->draft[$day]['sessions'][$i]);
        $this->draft[$day]['sessions'] = array_values($this->draft[$day]['sessions']);
    }

    public function saveEdit(): void
    {
        $plan = $this->currentPlan();
        if (!$plan) { $this->editing = false; return; }

        $schedule = [];
        $calSum = 0; $slSum = 0; $slN = 0; $lastW = null;

        foreach (Plan::DAYS as $key) {
            $d = $this->draft[$key] ?? [];

            $sessions = [];
            foreach (($d['sessions'] ?? []) as $s) {
                $min = max(0, min(600, (int) ($s['minutes'] ?? 0)));
                if ($min <= 0) continue;
                $sessions[] = [
                    'type'    => Plan::mapType($s['type'] ?? 'boxing'),
                    'minutes' => $min,
                    'detail'  => (isset($s['detail']) && $s['detail'] !== '') ? substr((string) $s['detail'], 0, 300) : null,
                ];
            }

            $cal = self::num($d['calories'] ?? null, 0, 8000);
            $sl  = self::num($d['sleep'] ?? null, 0, 14, true);
            $wt  = self::num($d['weight'] ?? null, 30, 200, true);

            $schedule[] = [
                'day'      => $key,
                'focus'    => ($d['focus'] ?? '') !== '' ? substr((string) $d['focus'], 0, 60) : null,
                'sessions' => $sessions,
                'calories' => $cal !== null ? (int) $cal : null,
                'protein'  => $plan->dayPlan($key)['protein'] ?? null,
                'sleep'    => $sl,
                'weight'   => $wt,
            ];

            if ($cal) $calSum += $cal;
            if ($sl) { $slSum += $sl; $slN++; }
            if ($wt) $lastW = $wt;
        }

        $plan->update([
            'schedule'        => $schedule,
            'target_calories' => $calSum ? (int) round($calSum / 7) : null,
            'target_sleep'    => $slN ? round($slSum / $slN, 1) : null,
            'target_weight'   => $lastW,
        ]);

        $this->editing = false;
        $this->draft = [];
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->draft = [];
    }

    private function currentPlan(): ?Plan
    {
        return $this->currentPlanId ? auth()->user()->plans()->find($this->currentPlanId) : null;
    }

    private static function num($v, float $min, float $max, bool $float = false)
    {
        if ($v === null || $v === '') return null;
        $v = max($min, min($max, (float) $v));
        return $float ? $v : (int) $v;
    }

    private function fighterContext(User $user): string
    {
        $p       = $user->boxerProfile;
        $current = $user->currentWeight();
        $goal    = $p?->goal_weight;

        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $fightDays = $nextFight ? max(0, (int) now()->diffInDays($nextFight->fight_date, false)) : null;

        $weekLogs = $user->dailyLogs()->where('log_date', '>=', now()->subDays(13))->get();
        $trainHrs = round((int) $weekLogs->sum('training_minutes') / 60, 1);
        $types    = $weekLogs->pluck('sessions')->filter()->flatMap(fn ($s) => is_array($s) ? $s : [])
            ->pluck('type')->filter()->countBy()->map(fn ($cnt, $t) => "{$t} x{$cnt}")->implode(', ');
        $avgSleep = $weekLogs->whereNotNull('sleep_hours')->avg('sleep_hours');

        $dir = ($current && $goal) ? ($goal >= $current ? 'gain ' . round($goal - $current, 1) . ' kg' : 'cut ' . round($current - $goal, 1) . ' kg') : '';

        $c  = "FIGHTER: {$user->name}\n";
        $c .= "Current weight: " . ($current ? round($current, 1) . " kg" : 'unknown') . "\n";
        $c .= "Goal weight: " . ($goal ? "{$goal} kg" . ($dir ? " ({$dir})" : '') : 'not set') . "\n";
        $c .= "Experience: " . ($p?->experience_years ?? '?') . " years\n";
        $c .= "Next fight: " . ($fightDays !== null ? "in {$fightDays} days" : 'none scheduled') . "\n";
        $c .= "Recent training (2 wks): {$trainHrs}h" . ($types ? " — sessions logged: {$types}" : ' — little logged') . "\n";
        if ($avgSleep) $c .= "Avg sleep lately: " . round($avgSleep, 1) . "h\n";
        $c .= "\nBuild this week's plan now.";

        return $c;
    }

    private function parsePlan(?string $raw): ?array
    {
        if (!$raw) return null;

        $raw  = trim(preg_replace('/^```(?:json)?|```$/m', '', $raw));
        $json = json_decode($raw, true);
        if (!is_array($json) || empty($json['schedule']) || !is_array($json['schedule'])) return null;

        $byDay = [];
        foreach ($json['schedule'] as $d) {
            if (is_array($d) && in_array($d['day'] ?? null, Plan::DAYS, true)) {
                $byDay[$d['day']] = $d;
            }
        }
        if (count($byDay) < 5) return null; // too few valid days → treat as a failed parse

        $schedule = [];
        $calSum = 0; $sleepSum = 0; $sleepN = 0; $lastWeight = null;

        foreach (Plan::DAYS as $key) {
            $d = $byDay[$key] ?? [];
            $sessions = [];
            foreach ((array) ($d['sessions'] ?? []) as $s) {
                $rawType = is_array($s) ? ($s['type'] ?? 'other') : 'other';
                if ($rawType === 'rest') continue;
                $sessions[] = [
                    'type'    => Plan::mapType($rawType),
                    'minutes' => max(0, min(600, (int) (is_array($s) ? ($s['minutes'] ?? 0) : 0))),
                    'detail'  => (is_array($s) && isset($s['detail']) && is_string($s['detail'])) ? substr($s['detail'], 0, 300) : null,
                ];
            }
            $cal = isset($d['calories']) ? max(0, min(8000, (int) $d['calories'])) : null;
            $sl  = isset($d['sleep'])    ? max(0, min(14, (float) $d['sleep']))    : null;
            $wt  = isset($d['weight'])   ? max(30, min(200, (float) $d['weight']))  : null;

            $schedule[] = [
                'day'      => $key,
                'focus'    => isset($d['focus']) && is_string($d['focus']) ? substr($d['focus'], 0, 60) : null,
                'sessions' => $sessions,
                'calories' => $cal,
                'protein'  => isset($d['protein']) ? max(0, min(500, (int) $d['protein'])) : null,
                'sleep'    => $sl,
                'weight'   => $wt,
            ];
            if ($cal) $calSum += $cal;
            if ($sl) { $sleepSum += $sl; $sleepN++; }
            if ($wt) $lastWeight = $wt;
        }

        return [
            'title'           => is_string($json['title'] ?? null) ? substr($json['title'], 0, 120) : 'Weekly Plan',
            'notes'           => is_string($json['summary'] ?? null) ? substr($json['summary'], 0, 500) : null,
            'schedule'        => $schedule,
            'target_calories' => $calSum ? (int) round($calSum / 7) : null,
            'target_sleep'    => $sleepN ? round($sleepSum / $sleepN, 1) : null,
            'target_weight'   => $lastWeight,
            'target_water'    => null,
        ];
    }

    /** Offline / parse-failure fallback — still boxing-focused, 2 rest days, per-day targets. */
    private function defaultPlan(User $user): array
    {
        $cur  = $user->currentWeight() ?? 70.0;
        $goal = (float) ($user->boxerProfile?->goal_weight ?? $cur);
        $dir  = $goal >= $cur ? 1 : -1;
        $step = min(0.25, abs($goal - $cur) / 7) * $dir;
        $prot = (int) round($cur * 2);
        $base = $dir > 0 ? 3000 : 2200;

        $w   = fn ($i) => round($cur + $step * $i, 1);
        $box = fn ($m, $d) => ['type' => 'boxing', 'minutes' => $m, 'detail' => $d];
        $gym = fn ($m, $d) => ['type' => 'gym', 'minutes' => $m, 'detail' => $d];
        $run = fn ($m, $d) => ['type' => 'running', 'minutes' => $m, 'detail' => $d];
        $spr = fn ($m, $d) => ['type' => 'sparring', 'minutes' => $m, 'detail' => $d];
        $day = fn ($i, $focus, $sessions, $cal, $sleep) => [
            'day' => Plan::DAYS[$i], 'focus' => $focus, 'sessions' => $sessions,
            'calories' => $cal, 'protein' => $prot, 'sleep' => $sleep, 'weight' => $w($i),
        ];

        return [
            'title'    => 'Starter Week — Base Phase',
            'notes'    => 'A balanced base week. Regenerate once CORNER has your real training logged for a sharper, fully personalised plan.',
            'schedule' => [
                $day(0, 'Technique + strength', [$box(60, '8×3 bag — straight shots into pivots; 4×3 pads on counters'), $gym(40, '4×5 back squat, 4×6 pull-ups, 3-round core circuit')], $base + 400, 8.0),
                $day(1, 'Road work + conditioning', [$run(45, '40min Zone-2 steady + 6×100m strides')], $base, 8.0),
                $day(2, 'Sparring + core', [$box(45, '3×3 technical warm-up on pads'), $spr(30, '5×3 controlled sparring — work behind the jab, no head-hunting')], $base + 400, 8.5),
                $day(3, 'Active recovery', [], $base - 300, 8.5),
                $day(4, 'Speed + bag work', [$box(60, '10×3 speed bag + double-end bag; sharp 3-4-2 combos, hands back fast')], $base + 200, 8.0),
                $day(5, 'Conditioning + strength', [$run(50, '8×400m intervals @ hard, 90s rest'), $gym(30, '3×8 deadlift, 3×10 press, plank circuit')], $base + 300, 8.0),
                $day(6, 'Full rest', [], $base - 300, 9.0),
            ],
            'target_calories' => $base + 150,
            'target_sleep'    => 8.2,
            'target_weight'   => $w(6),
            'target_water'    => null,
        ];
    }

    public function render()
    {
        $user  = auth()->user();
        $plans = $user->plans()->latest()->get();
        $plan  = $this->currentPlanId ? $plans->firstWhere('id', $this->currentPlanId) : null;

        $plansLeft = Corner::enabled() ? Corner::remaining('plan') : null;

        return view('livewire.plan-board', compact('plans', 'plan', 'plansLeft'))->layout('layouts.app');
    }
}
