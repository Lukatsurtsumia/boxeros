<?php

namespace App\Support;

use App\Models\User;

/**
 * Proactive coaching insights — CORNER watching the fighter's data and flagging what matters
 * WITHOUT being asked. Pure rule-based computation (no AI call), so it's free and instant on every
 * dashboard load. Each insight is a level + icon + title + detail, sorted by urgency.
 */
class CornerInsights
{
    /** @return array<int, array{level:string, icon:string, title:string, detail:string}> */
    public static function for(User $user): array
    {
        $weekStart = today()->copy()->subDays(6);
        $logs = $user->dailyLogs()
            ->whereDate('log_date', '>=', $weekStart)
            ->orderBy('log_date')
            ->get();

        $insights = [];

        self::weightVsFight($user, $insights);
        self::sleep($logs, $insights);
        self::training($logs, $insights);
        self::energy($logs, $insights);
        self::hydration($logs, $insights);
        self::emptyCalories($logs, $insights);

        // Always leave the fighter with something: praise when there are no red flags,
        // or a nudge to log when there's not enough data to spot patterns yet.
        if (empty($insights)) {
            $hasData = $logs->count() > 0
                || $user->weightEntries()->where('weighed_at', '>=', $weekStart)->exists();
            if ($hasData) {
                $insights[] = self::i('good', '✅', __('Everything looks on track'),
                    __('No red flags in your recent numbers — sleep, training, hydration and weight are all in a solid place. Keep the consistency going and stay sharp.'));
            } else {
                $insights[] = self::i('info', '📋', __("Start logging and I'll coach you"),
                    __("Log your sleep, training, water and a weigh-in for a few days. Once I can see your patterns I'll flag exactly what to fix and what's working."));
            }
        }

        // Most urgent first; show at most 4 so the card stays scannable.
        $order = ['alert' => 0, 'warn' => 1, 'info' => 2, 'good' => 3];
        usort($insights, fn ($a, $b) => ($order[$a['level']] ?? 9) <=> ($order[$b['level']] ?? 9));

        return array_slice($insights, 0, 4);
    }

    private static function weightVsFight(User $user, array &$out): void
    {
        $current = $user->currentWeight();
        $goal    = $user->boxerProfile?->goal_weight;
        if (!$current || !$goal) return;

        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $fightDays = $nextFight ? max(0, (int) now()->diffInDays($nextFight->fight_date, false)) : null;

        $delta = round($current - $goal, 1); // + = must cut, - = must gain
        if ($fightDays === null || abs($delta) < 0.3) return;

        $weeks = max($fightDays / 7, 0.1);
        $rate  = round(abs($delta) / $weeks, 1); // kg/week
        $pctPerWeek = $current > 0 ? ($rate / $current) * 100 : 0;

        if ($delta > 0) {
            if ($pctPerWeek > 1.5) {
                $out[] = self::i('alert', '⚠️', __('Aggressive cut ahead'),
                    __("You're :delta kg over goal with :days days to the fight — about :rate kg/week. That's faster than the safe ~1–1.5% of body weight per week. Tighten nutrition now rather than forcing it late, and talk to a nutritionist if it stays steep.", ['delta' => $delta, 'days' => $fightDays, 'rate' => $rate]));
            } else {
                $out[] = self::i('warn', '⚖️', __('On pace to make weight'),
                    __(":delta kg to cut in :days days (~:rate kg/week) — a manageable pace. Stay consistent on nutrition and water and you'll hit it clean.", ['delta' => $delta, 'days' => $fightDays, 'rate' => $rate]));
            }
        } else {
            $g = abs($delta);
            $out[] = self::i('info', '🍽️', __('You need to gain, not cut'),
                __("You're :g kg under goal with :days days out. Add clean calories and keep protein high — you're building toward the weight, not cutting.", ['g' => $g, 'days' => $fightDays]));
        }
    }

    private static function sleep($logs, array &$out): void
    {
        $sleeps = $logs->whereNotNull('sleep_hours')->pluck('sleep_hours');
        if ($sleeps->count() < 2) return;

        $avg = round($sleeps->avg(), 1);
        $n   = $sleeps->count();
        if ($avg < 6.5) {
            $out[] = self::i('alert', '😴', __('Sleep is too low'),
                __('Averaging :avg h over your last :n logged nights. Under 7h drags down recovery, reaction time, and your weight cut. Protect sleep like you protect training.', ['avg' => $avg, 'n' => $n]));
        } elseif ($avg < 7) {
            $out[] = self::i('warn', '😴', __('Sleep could be better'),
                __('Averaging :avg h. Nudging toward 7–9h will sharpen recovery and focus in camp.', ['avg' => $avg]));
        } elseif ($avg >= 7.5) {
            $out[] = self::i('good', '😴', __('Sleep is dialed in'),
                __('Averaging :avg h — right where a fighter in camp wants to be. Keep it up.', ['avg' => $avg]));
        }
    }

    private static function training($logs, array &$out): void
    {
        $loggedDays = $logs->count();
        if ($loggedDays < 3) return;

        $trainDays = $logs->where('training_minutes', '>', 0)->count();
        $totalMin  = (int) $logs->sum('training_minutes');

        if ($trainDays === 0) {
            $out[] = self::i('warn', '🥊', __('No training logged this week'),
                __("Nothing logged in the last :days days. A rest week is fine — but if you're training and not logging it, CORNER can't coach what it can't see.", ['days' => $loggedDays]));
            return;
        }
        if ($trainDays >= 5) {
            $hrs = round($totalMin / 60, 1);
            $out[] = self::i('warn', '🔥', __('Heavy training week'),
                __(":hrs h across :days days — strong work. Just make sure sleep, food, and at least one rest day keep pace, or you'll tip into overtraining.", ['hrs' => $hrs, 'days' => $trainDays]));
        }
    }

    private static function energy($logs, array &$out): void
    {
        $energy = $logs->whereNotNull('energy_level')->pluck('energy_level');
        if ($energy->count() < 3) return;

        $avg = round($energy->avg(), 1);
        if ($avg <= 4) {
            $out[] = self::i('warn', '🔋', __('Energy is running low'),
                __('Your energy is averaging :avg/10. Paired with training load, that often means under-eating or under-recovering. Check your sleep and calories before pushing harder.', ['avg' => $avg]));
        }
    }

    private static function hydration($logs, array &$out): void
    {
        $waters = $logs->pluck('water_liters')->filter(fn ($w) => $w !== null && $w > 0);
        if ($waters->count() < 3) return;

        $avg = round($waters->avg(), 1);
        if ($avg < 2) {
            $out[] = self::i('warn', '💧', __('Hydration is low'),
                __('Averaging :avg L/day. Aim for 2.5–3L+ — especially mid-cut, where dehydration quietly tanks performance and recovery.', ['avg' => $avg]));
        }
    }

    private static function emptyCalories($logs, array &$out): void
    {
        $soda    = (int) $logs->sum('soda_cans');
        $alcohol = (int) $logs->sum('alcohol_units');
        if ($alcohol < 3 && $soda < 5) return;

        $parts = [];
        if ($soda)    $parts[] = trans_choice('{1}:count soda|[2,*]:count sodas', $soda, ['count' => $soda]);
        if ($alcohol) $parts[] = trans_choice('{1}:count drink|[2,*]:count drinks', $alcohol, ['count' => $alcohol]);
        $what = implode(' + ', $parts);

        $out[] = self::i('warn', '🥤', __('Empty calories adding up'),
            __(":what this week — calories with no training benefit. This is the first thing to trim when you're making weight.", ['what' => $what]));
    }

    private static function i(string $level, string $icon, string $title, string $detail): array
    {
        return compact('level', 'icon', 'title', 'detail');
    }
}
