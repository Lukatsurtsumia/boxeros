<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;

/**
 * Compares a fighter's active plan against what they've actually logged — the "plan vs reality"
 * engine. Each day in the plan carries its own sessions and targets (calories, protein, sleep,
 * weight checkpoint), so the comparison is day-specific, and training gets a motivational verdict.
 */
class PlanProgress
{
    /** @return array{today:array, week:array} */
    public static function for(User $user, Plan $plan): array
    {
        return [
            'today' => self::today($user, $plan),
            'week'  => self::week($user, $plan),
        ];
    }

    private static function today(User $user, Plan $plan): array
    {
        $dayKey = Plan::DAYS[Carbon::today()->dayOfWeekIso - 1];
        $dp     = $plan->dayPlan($dayKey);

        $log    = $user->dailyLogs()->whereDate('log_date', today())->first();
        $logged = self::aggregate(is_array($log?->sessions) ? $log->sessions : []);
        $planned = self::aggregate($dp['sessions']);

        $sessions = [];
        foreach ($planned as $type => $target) {
            $done = (int) ($logged[$type] ?? 0);
            $sessions[] = ['type' => $type, 'target' => $target, 'done' => $done, 'complete' => $done >= $target];
        }

        $plannedMin = array_sum($planned);
        $doneMin    = array_sum($logged);
        $feedback   = self::trainingVerdict($plannedMin, $doneMin);

        $targets = [];
        if ($dp['calories']) {
            $actual = (float) $user->meals()->whereDate('eaten_at', today())->sum('calories');
            $targets['calories'] = ['target' => (float) $dp['calories'], 'actual' => $actual, 'unit' => 'kcal',
                'protein' => $dp['protein'], 'complete' => $actual > 0 && $actual >= $dp['calories'] * 0.9];
        }
        if ($dp['sleep']) {
            $actual = (float) ($log?->sleep_hours ?? 0);
            $targets['sleep'] = ['target' => (float) $dp['sleep'], 'actual' => $actual, 'unit' => 'h', 'complete' => $actual >= $dp['sleep']];
        }
        if ($dp['weight']) {
            $current = $user->currentWeight();
            $targets['weight'] = ['target' => (float) $dp['weight'], 'actual' => $current, 'unit' => 'kg',
                'complete' => $current !== null && abs($current - $dp['weight']) <= 0.4];
        }

        return [
            'day'      => $dayKey,
            'focus'    => $dp['focus'],
            'isRest'   => empty($dp['sessions']),
            'sessions' => $sessions,
            'feedback' => $feedback,
            'targets'  => $targets,
        ];
    }

    private static function week(User $user, Plan $plan): array
    {
        $start = Carbon::today()->startOfWeek($user->weekStartDay());
        $logsByDate = $user->dailyLogs()
            ->whereDate('log_date', '>=', $start)
            ->get()->keyBy(fn ($l) => Carbon::parse($l->log_date)->toDateString());

        $plannedUnits = 0;
        $completed    = 0;

        for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {
            $dayKey  = Plan::DAYS[$d->dayOfWeekIso - 1];
            $planned = self::aggregate($plan->dayPlan($dayKey)['sessions']);
            $log     = $logsByDate->get($d->toDateString());
            $logged  = self::aggregate(is_array($log?->sessions) ? $log->sessions : []);

            foreach ($planned as $type => $target) {
                $plannedUnits++;
                if ((int) ($logged[$type] ?? 0) >= $target) $completed++;
            }
        }

        return [
            'planned'   => $plannedUnits,
            'completed' => $completed,
            'percent'   => $plannedUnits > 0 ? (int) round($completed / $plannedUnits * 100) : null,
        ];
    }

    /** Motivational verdict on today's training minutes vs the plan. */
    private static function trainingVerdict(int $plannedMin, int $doneMin): ?array
    {
        if ($plannedMin <= 0) {
            return ['verdict' => 'rest', 'message' => __('Rest day — recover and refuel.'), 'color' => 'var(--text-muted)'];
        }
        if ($doneMin <= 0) {
            return ['verdict' => 'start', 'message' => __('Get to work — 0 / :planned min today.', ['planned' => $plannedMin]), 'color' => 'var(--gold)'];
        }
        if ($doneMin >= $plannedMin) {
            $extra = $doneMin - $plannedMin;
            $msg = $extra > 0
                ? __("💪 You're a machine — :done / :planned min (+:extra).", ['done' => $doneMin, 'planned' => $plannedMin, 'extra' => $extra])
                : __('💪 Nailed it — :done / :planned min.', ['done' => $doneMin, 'planned' => $plannedMin]);
            return ['verdict' => 'machine', 'message' => $msg, 'color' => '#2ecc71'];
        }
        $left = $plannedMin - $doneMin;
        return ['verdict' => 'improve', 'message' => __('Keep pushing — :done / :planned min, :left to go.', ['done' => $doneMin, 'planned' => $plannedMin, 'left' => $left]), 'color' => 'var(--gold)'];
    }

    private static function aggregate(array $sessions): array
    {
        $out = [];
        foreach ($sessions as $s) {
            $type = $s['type'] ?? 'other';
            if ($type === 'rest') continue;
            $out[$type] = ($out[$type] ?? 0) + (int) ($s['minutes'] ?? 0);
        }
        return $out;
    }
}
