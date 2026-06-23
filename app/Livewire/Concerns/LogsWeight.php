<?php

namespace App\Livewire\Concerns;

use App\Models\WeightEntry;

/**
 * Shared quick weigh-in behaviour for Livewire components (Dashboard, DailyLog).
 * Logging is always optional — a blank value is a no-op, never an error.
 */
trait LogsWeight
{
    public string $weighInValue = '';
    public string $weighInContext = 'morning';

    /** Livewire auto-hook: default the context to the current time of day. */
    public function mountLogsWeight(): void
    {
        $this->weighInContext = $this->defaultWeighInContext();
    }

    public function saveWeighIn(): void
    {
        if (trim($this->weighInValue) === '') {
            return; // nothing entered — stay quiet, no nagging
        }

        $this->validate([
            'weighInValue'   => 'numeric|min:30|max:200',
            'weighInContext' => 'in:morning,afternoon,night,pre_workout,post_workout,other',
        ]);

        $kg   = round((float) $this->weighInValue, 2);
        $prev = auth()->user()->latestWeight();

        auth()->user()->weightEntries()->create([
            'weight_kg'  => $kg,
            'context'    => $this->weighInContext,
            'weighed_at' => now(),
        ]);

        $this->weighInValue   = '';
        $this->weighInContext = $this->defaultWeighInContext();

        // Typo guard — a weigh-in far from the last one is almost always a mistake.
        if ($prev && abs($kg - (float) $prev->weight_kg) > 8) {
            $gap = round(abs($kg - (float) $prev->weight_kg), 1);
            session()->flash('message', "Logged {$kg} kg — that's {$gap} kg from your last weigh-in. If it's a typo, delete it.");
        } else {
            session()->flash('message', 'Weigh-in logged!');
        }
    }

    /** Remove a weigh-in (e.g. a typo). Scoped to the current user. */
    public function deleteWeighIn(int $id): void
    {
        auth()->user()->weightEntries()->whereKey($id)->delete();
    }

    /** Wipe today's weigh-ins to start over. Scoped to the current user. */
    public function clearTodayWeighIns(): void
    {
        auth()->user()->weightEntries()->whereDate('weighed_at', today())->delete();
    }

    private function defaultWeighInContext(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour >= 5  && $hour <= 11 => 'morning',    // 5:00–11:59
            $hour >= 12 && $hour <= 16 => 'afternoon',  // 12:00–16:59
            default                    => 'night',      // 17:00–4:59
        };
    }
}
