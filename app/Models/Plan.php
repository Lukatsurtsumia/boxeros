<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A championship-level weekly plan CORNER generates: each day has its own focus, sessions, and
 * targets (calories, protein, sleep, and a weight checkpoint that moves toward the goal). The
 * dashboard compares the active plan against the fighter's real logs ("plan vs reality").
 */
class Plan extends Model
{
    protected $fillable = [
        'user_id', 'title', 'is_active', 'schedule',
        'target_calories', 'target_water', 'target_sleep', 'target_weight', 'notes',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'schedule'      => 'array',
        'target_water'  => 'decimal:1',
        'target_sleep'  => 'decimal:1',
        'target_weight' => 'decimal:2',
    ];

    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public const DAY_LABELS = [
        'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu',
        'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun',
    ];

    public const SESSION_TYPES = ['boxing', 'sparring', 'gym', 'running', 'cycling', 'swimming', 'yoga', 'rest', 'other'];

    /** The only session types a plan may use — boxing-focused. Anything else is mapped in. */
    public const PLAN_TYPES = ['boxing', 'sparring', 'gym', 'running'];

    private const TYPE_MAP = ['cycling' => 'running', 'swimming' => 'running', 'yoga' => 'gym', 'other' => 'gym'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Normalised 7-day plan, keyed mon…sun, each: focus, sessions[], calories, protein, sleep, weight.
     * Handles both the rich per-day format and the older sessions-only format.
     */
    public function days(): array
    {
        $raw = is_array($this->schedule) ? $this->schedule : [];
        $out = [];

        foreach (self::DAYS as $key) {
            $found = null;

            // New format: a list of day objects each carrying its own "day".
            foreach ($raw as $d) {
                if (is_array($d) && ($d['day'] ?? null) === $key) { $found = $d; break; }
            }
            // Old format: { mon: [sessions], ... }.
            if (!$found && isset($raw[$key]) && is_array($raw[$key])) {
                $found = ['sessions' => $raw[$key]];
            }
            $found ??= [];

            $out[$key] = [
                'day'      => $key,
                'focus'    => isset($found['focus']) && is_string($found['focus']) ? $found['focus'] : null,
                'sessions' => self::normSessions($found['sessions'] ?? []),
                'calories' => isset($found['calories']) ? (int) $found['calories'] : null,
                'protein'  => isset($found['protein'])  ? (int) $found['protein']  : null,
                'sleep'    => isset($found['sleep'])    ? (float) $found['sleep']   : null,
                'weight'   => isset($found['weight'])   ? (float) $found['weight']  : null,
            ];
        }

        return $out;
    }

    public function dayPlan(string $key): array
    {
        return $this->days()[$key] ?? ['day' => $key, 'focus' => null, 'sessions' => [], 'calories' => null, 'protein' => null, 'sleep' => null, 'weight' => null];
    }

    public function restDayCount(): int
    {
        return collect($this->days())->filter(fn ($d) => empty($d['sessions']))->count();
    }

    /** Map any incoming session type into the boxing-focused allow-list (no yoga/cycling/etc.). */
    public static function mapType(string $type): string
    {
        $type = self::TYPE_MAP[$type] ?? $type;
        return in_array($type, self::PLAN_TYPES, true) ? $type : 'gym';
    }

    private static function normSessions($sessions): array
    {
        if (!is_array($sessions)) return [];
        $out = [];
        foreach ($sessions as $s) {
            if (!is_array($s)) continue;
            $type = $s['type'] ?? 'other';
            if ($type === 'rest') continue;
            $out[] = [
                'type'    => self::mapType($type),
                'minutes' => max(0, min(600, (int) ($s['minutes'] ?? 0))),
                'detail'  => isset($s['detail']) && is_string($s['detail']) ? $s['detail'] : null,
            ];
        }
        return $out;
    }
}
