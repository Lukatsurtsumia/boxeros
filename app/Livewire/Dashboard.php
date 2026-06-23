<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Support\Corner;
use App\Livewire\Concerns\LogsWeight;

class Dashboard extends Component
{
    use LogsWeight;

    public ?string $weeklyConclusion = null;

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

    public function render()
    {
        $user    = auth()->user();
        $profile = $user->boxerProfile;

        // Today
        $todayLog       = $user->dailyLogs()->whereDate('log_date', today())->first();
        $waterToday     = (float) ($todayLog?->water_liters ?? 0);
        $todayCalories  = (int) ($user->dailyLogs()->whereDate('log_date', today())->value('calories_consumed') ?? 0);
        $activeInjuries = $user->injuries()->where('status', 'active')->count();
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

        // This week (Mon → today)
        $thisWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $thisWeekEnd   = $thisWeekStart->copy()->addDays(6);
        $thisWeekLogs  = $this->logsForRange($user, $thisWeekStart, Carbon::now());
        $thisWeekDays  = $this->buildWeekDays($thisWeekStart, $thisWeekLogs, clampToToday: true);
        $tw            = $this->summarizeWeek($thisWeekLogs->values());

        // Last week (Mon → Sun of the previous calendar week)
        $lastWeekStart = $thisWeekStart->copy()->subWeek();
        $lastWeekEnd   = $lastWeekStart->copy()->addDays(6);
        $lastWeekLogs  = $this->logsForRange($user, $lastWeekStart, $lastWeekEnd);
        $lastWeekDays  = $this->buildWeekDays($lastWeekStart, $lastWeekLogs);
        $lw            = $this->summarizeWeek($lastWeekLogs->values());

        return view('livewire.dashboard', compact(
            'profile', 'todayLog', 'nextFight',
            'activeInjuries', 'todayCalories', 'waterToday',
            'currentWeight', 'latestWeight', 'weightAgo', 'todayWeighIns', 'sweatLoss', 'weightTrend',
            'thisWeekDays', 'thisWeekStart', 'thisWeekEnd', 'tw',
            'lastWeekDays', 'lastWeekStart', 'lastWeekEnd', 'lw'
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
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
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

        $system = "You are CORNER, an elite boxing coach. Given a fighter's week of data, write a tight end-of-week conclusion in 4-6 sentences: what went well, the biggest concern, and the single most important focus for next week. Be direct and specific, reference the actual numbers, address the fighter by first name.";

        $this->weeklyConclusion = Corner::ask([['role' => 'user', 'content' => $facts]], $system, 'claude-sonnet-4-6', 500)
            ?? $this->computedVerdict($s, $sessionCount, $weightDelta, $soda + $alcohol, $fightDays);
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
