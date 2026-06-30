<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Support\Corner;
use App\Support\CornerInsights;
use App\Support\PlanProgress;
use App\Livewire\Concerns\LogsWeight;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    use LogsWeight;

    public ?string $weeklyConclusion = null;

    /** Which week the strip shows: 0 = this week, -1 = last week, etc. Never positive (no future). */
    public int $weekOffset = 0;

    private static array $typeIcons = [
        'boxing'   => '🥊',
        'sparring' => '🥊',
        'gym'      => '🏋️',
        'running'  => '🏃',
        'cycling'  => '🚴',
        'swimming' => '🏊',
        'yoga'     => '🧘',
        'other'    => '💪',
    ];

    public function mount(): void
    {
        // First-time fighter with no profile yet → send them through onboarding.
        if (!auth()->user()->boxerProfile) {
            $this->redirectRoute('onboarding', navigate: false);
            return;
        }

        // Restore this week's recap so it survives navigation/refresh (it's not lost on re-render).
        $this->weeklyConclusion = Cache::get($this->recapKey());
    }

    private function recapKey(): string
    {
        return 'weekrecap:' . auth()->id() . ':' . Carbon::now()->startOfWeek(auth()->user()->weekStartDay())->toDateString();
    }

    /** Step the week strip back one week. */
    public function prevWeek(): void
    {
        $this->weekOffset--;
    }

    /** Step forward, but never past the current week. */
    public function nextWeek(): void
    {
        if ($this->weekOffset < 0) {
            $this->weekOffset++;
        }
    }

    public function render()
    {
        $user    = auth()->user();
        $profile = $user->boxerProfile;

        // Today
        $todayLog       = $user->dailyLogs()->whereDate('log_date', today())->first();
        $waterToday     = (float) ($todayLog?->water_liters ?? 0);
        $todayCalories  = (int) $user->meals()->whereDate('eaten_at', today())->sum('calories'); // live sum, matches Nutrition
        $nextFight      = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();

        // Weight — real-time weigh-ins
        $latestWeight = $user->latestWeight();
        $currentWeight = $user->currentWeight();
        $weightAgo = $latestWeight ? $latestWeight->weighed_at->diffForHumans(['short' => true]) : null;
        $todayWeighIns = $user->weightEntries()->whereDate('weighed_at', today())->orderByDesc('weighed_at')->get();
        $pre  = $todayWeighIns->firstWhere('context', 'pre_workout');
        $post = $todayWeighIns->firstWhere('context', 'post_workout');
        $sweatLoss = ($pre && $post) ? round($pre->weight_kg - $post->weight_kg, 1) : null;
        $weightTrend = $this->weightTrend($user);

        // Week strip — navigable. offset 0 = this week, negative = past weeks.
        // Each fighter's week starts on the weekday they registered (personal cadence).
        $isCurrentWeek = $this->weekOffset === 0;
        $weekStart   = Carbon::now()->startOfWeek($user->weekStartDay())->addWeeks($this->weekOffset);
        $weekEnd     = $weekStart->copy()->addDays(6);
        $rangeEnd    = $isCurrentWeek ? Carbon::now() : $weekEnd;
        $weekLogs    = $this->logsForRange($user, $weekStart, $rangeEnd);
        $weekDays    = $this->buildWeekDays($weekStart, $weekLogs, clampToToday: $isCurrentWeek);
        $weekSummary = $this->summarizeWeek($weekLogs->values());

        $weekTitle = match (true) {
            $this->weekOffset === 0  => __('This Week'),
            $this->weekOffset === -1 => __('Last Week'),
            default                  => __(':n weeks ago', ['n' => abs($this->weekOffset)]),
        };
        $weekRange = $weekStart->translatedFormat('M j') . ' – ' . ($isCurrentWeek ? __('today') : $weekEnd->translatedFormat('M j'));

        // Proactive coaching — CORNER flags patterns in the data without being asked (free, rule-based).
        $insights = CornerInsights::for($user);

        // Plan vs reality — today's planned sessions/targets + this week's adherence, if a plan is active.
        $activePlan   = $user->activePlan();
        $planProgress = $activePlan ? PlanProgress::for($user, $activePlan) : null;

        // Hold the very first weekly recap until the fighter has a genuine full week of data,
        // so their first conclusion reflects real usage — not a 3-day stub right after sign-up.
        $firstWeekDaysLeft = max(0, 6 - $user->daysSinceJoined());
        $recapLocked       = $firstWeekDaysLeft > 0;

        return view('livewire.dashboard', compact(
            'profile', 'todayLog', 'nextFight',
            'todayCalories', 'waterToday',
            'currentWeight', 'latestWeight', 'weightAgo', 'todayWeighIns', 'sweatLoss', 'weightTrend',
            'weekDays', 'weekSummary', 'weekTitle', 'weekRange', 'isCurrentWeek',
            'insights', 'activePlan', 'planProgress',
            'recapLocked', 'firstWeekDaysLeft'
        ))->layout('layouts.app');
    }

    /**
     * Weight trend for charting: one point per day (morning weigh-in preferred), last 14 days.
     * Falls back to legacy daily_logs.weight_kg when no weigh-ins exist yet.
     * Returns a Collection of ['date' => Carbon, 'weight' => float].
     */
    private function weightTrend($user): Collection
    {
        $since   = now()->subDays(13)->startOfDay();
        $entries = $user->weightEntries()->where('weighed_at', '>=', $since)->orderBy('weighed_at')->get();

        if ($entries->isEmpty()) {
            return $user->dailyLogs()
                ->whereNotNull('weight_kg')
                ->orderByDesc('log_date')->take(10)->get()->reverse()->values()
                ->map(fn ($l) => ['date' => $l->log_date, 'weight' => (float) $l->weight_kg]);
        }

        return $entries
            ->groupBy(fn ($e) => $e->weighed_at->format('Y-m-d'))
            ->map(function ($dayEntries) {
                $chosen = $dayEntries->firstWhere('context', 'morning')
                    ?? $dayEntries->sortByDesc('weighed_at')->first();
                return ['date' => $chosen->weighed_at->copy()->startOfDay(), 'weight' => (float) $chosen->weight_kg];
            })
            ->values();
    }

    /** Daily logs within [$start, $end] (inclusive, by date), keyed by Y-m-d. */
    private function logsForRange($user, Carbon $start, Carbon $end): Collection
    {
        return $user->dailyLogs()
            ->whereBetween('log_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($l) => $l->log_date->format('Y-m-d'));
    }

    /** Build a 7-day strip starting at $start. Days after today are flagged "future". */
    private function buildWeekDays(Carbon $start, Collection $logsKeyed, bool $clampToToday = false): Collection
    {
        $days = collect();
        for ($i = 0; $i < 7; $i++) {
            $date   = $start->copy()->addDays($i);
            $log    = $logsKeyed[$date->format('Y-m-d')] ?? null;
            $future = $clampToToday && $date->isAfter(today());

            $days->push([
                'date'    => $date->toDateString(),
                'label'   => $date->format('D'),
                'day_num' => $date->format('j'),
                'log'     => $log,
                'future'  => $future,
                'today'   => $date->isToday(),
                'trained' => $log && ($log->training_minutes ?? 0) > 0,
                'icon'    => $log && $log->training_type
                                ? (self::$typeIcons[$log->training_type] ?? '💪')
                                : null,
                'minutes' => $log?->training_minutes,
                'weight'  => $log?->weight_kg,
                'mood'    => $log?->mood,
                'energy'  => $log?->energy_level,
            ]);
        }

        return $days;
    }

    /** Aggregate a week's logs into headline metrics. */
    private function summarizeWeek(Collection $logs): array
    {
        $sleepLogs = $logs->filter(fn ($l) => $l->sleep_hours !== null);

        return [
            'daysLogged' => $logs->count(),
            'trainDays'  => $logs->where('training_minutes', '>', 0)->count(),
            'trainHrs'   => round($logs->sum('training_minutes') / 60, 1),
            'avgSleep'   => $sleepLogs->count() > 0 ? round($sleepLogs->avg('sleep_hours'), 1) : null,
            'avgWater'   => $logs->count() > 0 ? round($logs->avg('water_liters'), 1) : null,
            'totalKcal'  => (int) $logs->filter(fn ($l) => ($l->calories_consumed ?? 0) > 0)->sum('calories_consumed'),
            'avgEnergy'  => $logs->count() > 0 ? round($logs->avg('energy_level'), 1) : null,
        ];
    }

    /** End-of-week coaching conclusion — CORNER (Claude) with a computed fallback. */
    public function generateConclusion(): void
    {
        $user  = auth()->user();
        $start = Carbon::now()->startOfWeek($user->weekStartDay());
        $logs  = $this->logsForRange($user, $start, Carbon::now())->values();
        $s     = $this->summarizeWeek($logs);

        $sessionCount = (int) $logs->sum(fn ($l) => is_array($l->sessions) ? count($l->sessions) : 0);
        $trend        = $this->weightTrend($user);
        $weightDelta  = $trend->count() > 1 ? round($trend->last()['weight'] - $trend->first()['weight'], 1) : null;
        $soda         = (int) $logs->sum('soda_cans');
        $alcohol      = (int) $logs->sum('alcohol_units');

        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $fightDays = $nextFight ? max(0, (int) now()->diffInDays($nextFight->fight_date, false)) : null;

        $facts = "Week so far ({$start->format('M j')}–today) for {$user->name}:\n"
            . "- Days logged: {$s['daysLogged']}/7\n"
            . "- Training: {$s['trainHrs']}h across {$sessionCount} sessions on {$s['trainDays']} day(s)\n"
            . "- Weight change: " . ($weightDelta !== null ? (($weightDelta > 0 ? "+{$weightDelta}" : $weightDelta) . " kg (morning)") : "not enough weigh-ins") . "\n"
            . "- Avg sleep: " . ($s['avgSleep'] ?? 'n/a') . "h | Avg water: " . ($s['avgWater'] ?? 'n/a') . "L | Avg energy: " . ($s['avgEnergy'] ?? 'n/a') . "/10\n"
            . "- Soda: {$soda} | Alcohol: {$alcohol} drinks\n"
            . ($fightDays !== null ? "- Next fight in {$fightDays} days\n" : "- No fight scheduled\n");

        $system = "You are CORNER, an elite boxing coach. Given a fighter's week of data, write a SHORT, punchy end-of-week recap in markdown: one strong opening line, then exactly 3 bullet points — '✅ **Went well:**', '⚠️ **Watch:**', and '🎯 **Next week:**'. Bold the key numbers, address the fighter by first name, reference the real numbers, and keep the whole thing under ~80 words.";

        if ($user->isFrench()) {
            $system .= " Write the ENTIRE recap in natural, fluent French (translate the three bullet labels too).";
        }

        // If the AI is on but the user has spent today's recap allowance, use the computed verdict.
        if (Corner::enabled() && !Corner::allow('weekly')) {
            $this->weeklyConclusion = $this->computedVerdict($s, $sessionCount, $weightDelta, $soda + $alcohol, $fightDays);
        } else {
            $this->weeklyConclusion = Corner::ask([['role' => 'user', 'content' => $facts]], $system, 'claude-sonnet-4-6', 400)
                ?? $this->computedVerdict($s, $sessionCount, $weightDelta, $soda + $alcohol, $fightDays);
        }

        // Persist it so it doesn't vanish when the page re-renders or the fighter navigates away.
        Cache::put($this->recapKey(), $this->weeklyConclusion, now()->addDays(8));
    }

    private function computedVerdict(array $s, int $sessions, ?float $weightDelta, int $emptyDrinks, ?int $fightDays): string
    {
        $parts = ["{$s['trainHrs']}h of training across {$sessions} session(s) on {$s['trainDays']} day(s)."];
        if ($weightDelta !== null) {
            $parts[] = $weightDelta <= 0
                ? "Weight " . abs($weightDelta) . "kg down — moving the right way."
                : "Weight up {$weightDelta}kg — tighten the diet.";
        }
        if ($s['avgSleep'] !== null) {
            $parts[] = $s['avgSleep'] >= 7 ? "Sleep solid at {$s['avgSleep']}h." : "Sleep low at {$s['avgSleep']}h — prioritise recovery.";
        }
        if ($s['avgWater'] !== null) $parts[] = "Avg water {$s['avgWater']}L/day.";
        if ($emptyDrinks > 0)        $parts[] = "{$emptyDrinks} soda/alcohol servings — cut the empty calories.";
        if ($fightDays !== null)     $parts[] = "{$fightDays} days to the fight — stay disciplined.";

        return "Weekly verdict — " . implode(' ', $parts) . "\n\n(Add ANTHROPIC_API_KEY in .env for CORNER's full AI coaching take.)";
    }
}
