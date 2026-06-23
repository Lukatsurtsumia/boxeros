<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Livewire\Concerns\LogsWeight;

class DailyLog extends Component
{
    use LogsWeight;

    public $log_date;
    public bool $showForm = false;

    public float $water_liters = 0;
    public int $soda_cans = 0;
    public array $alcohol_drinks = ['beer' => 0, 'wine' => 0, 'whiskey' => 0, 'vodka' => 0, 'champagne' => 0];
    public array $sessions = [];                  // [['type'=>'boxing','minutes'=>60,'when'=>'morning'], ...]
    public $sleep_hours;
    public $training_minutes, $training_type = ''; // derived from sessions (display + back-compat)
    public string $mood = 'good';
    public int $energy_level = 5;
    public $notes;

    public const DRINKS = [
        'beer'      => ['🍺', 'Beer', 50, 215],
        'wine'      => ['🍷', 'Wine', 15, 125],
        'whiskey'   => ['🥃', 'Whiskey', 4, 95],
        'vodka'     => ['🍸', 'Vodka', 4, 90],
        'champagne' => ['🥂', 'Champagne', 12, 90],
    ];

    public const SESSION_TYPES = [
        'boxing'   => 'Boxing',
        'sparring' => 'Sparring',
        'gym'      => 'Gym / Weights',
        'running'  => 'Running',
        'cycling'  => 'Cycling',
        'swimming' => 'Swimming',
        'yoga'     => 'Yoga / Stretch',
        'other'    => 'Other',
    ];

    private const AUTOSAVE_FIELDS = [
        'water_liters', 'soda_cans', 'sleep_hours', 'mood', 'energy_level', 'notes',
    ];

    public function mount(): void
    {
        $this->log_date = today()->toDateString();
        $this->loadDay();
    }

    private function resetFields(): void
    {
        $this->water_liters     = 0;
        $this->soda_cans        = 0;
        $this->alcohol_drinks   = ['beer' => 0, 'wine' => 0, 'whiskey' => 0, 'vodka' => 0, 'champagne' => 0];
        $this->sessions         = [];
        $this->sleep_hours      = null;
        $this->training_minutes = null;
        $this->training_type    = '';
        $this->mood             = 'good';
        $this->energy_level     = 5;
        $this->notes            = null;
    }

    private function loadDay(): void
    {
        $log = auth()->user()->dailyLogs()->whereDate('log_date', $this->log_date)->first();
        if (!$log) return;

        $this->water_liters     = (float) $log->water_liters;
        $this->soda_cans        = (int) ($log->soda_cans ?? 0);
        $this->alcohol_drinks   = array_merge($this->alcohol_drinks, $log->alcohol_drinks ?? []);
        $this->sleep_hours      = $log->sleep_hours;
        $this->training_minutes = $log->training_minutes;
        $this->training_type    = $log->training_type ?? '';
        $this->mood             = $log->mood;
        $this->energy_level     = $log->energy_level;
        $this->notes            = $log->notes;

        $this->sessions = $log->sessions ?? [];
        // Back-fill legacy single-session days so they're editable as sessions.
        if (empty($this->sessions) && $log->training_minutes) {
            $this->sessions = [[
                'type'    => $log->training_type ?: 'boxing',
                'minutes' => (int) $log->training_minutes,
                'when'    => 'morning',
            ]];
        }
    }

    private function safeDate(string $date): ?string
    {
        try {
            $d = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
        return $d->isAfter(today()) ? null : $d->toDateString();
    }

    private function currentWhen(): string
    {
        $hour = (int) now()->format('G');
        return match (true) {
            $hour < 12 => 'morning',
            $hour < 17 => 'afternoon',
            default    => 'evening',
        };
    }

    public function openForm(): void
    {
        $this->log_date = today()->toDateString();
        $this->resetFields();
        $this->loadDay();
        $this->showForm = true;
    }

    public function editLog(string $date): void
    {
        $safe = $this->safeDate($date);
        if (!$safe) return;
        $this->resetFields();
        $this->log_date = $safe;
        $this->loadDay();
        $this->showForm = true;
    }

    public function deleteLog(string $date): void
    {
        $safe = $this->safeDate($date);
        if (!$safe) return;
        auth()->user()->dailyLogs()->whereDate('log_date', $safe)->delete();
        if ($safe === $this->log_date) {
            $this->closeForm();
        }
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->log_date = today()->toDateString();
        $this->resetFields();
        $this->loadDay();
    }

    public function addSession(): void
    {
        $this->sessions[] = ['type' => 'boxing', 'minutes' => null, 'when' => $this->currentWhen()];
        $this->persist();
    }

    public function removeSession(int $index): void
    {
        unset($this->sessions[$index]);
        $this->sessions = array_values($this->sessions);
        $this->persist();
    }

    public function updated($property): void
    {
        if (in_array($property, self::AUTOSAVE_FIELDS, true) || str_starts_with($property, 'sessions')) {
            $this->persist();
        }
    }

    public function addWater(float $amount): void
    {
        $this->water_liters = max(0, round($this->water_liters + $amount, 2));
        $this->persist();
    }

    public function adjustDrink(string $key, int $delta): void
    {
        if (!array_key_exists($key, self::DRINKS)) return;
        $this->alcohol_drinks[$key] = max(0, min(20, ($this->alcohol_drinks[$key] ?? 0) + $delta));
        $this->persist();
    }

    private function persist(): void
    {
        $drinks = array_intersect_key($this->alcohol_drinks, self::DRINKS);

        // Normalise sessions; derive legacy training columns.
        $sessions = [];
        foreach ($this->sessions as $s) {
            $type = $s['type'] ?? 'other';
            if (!array_key_exists($type, self::SESSION_TYPES)) $type = 'other';
            $sessions[] = [
                'type'    => $type,
                'minutes' => ($s['minutes'] === null || $s['minutes'] === '') ? null : max(0, min(600, (int) $s['minutes'])),
                'when'    => in_array($s['when'] ?? '', ['morning', 'afternoon', 'evening'], true) ? $s['when'] : 'morning',
            ];
        }
        $totalMin  = array_sum(array_map(fn ($s) => (int) ($s['minutes'] ?? 0), $sessions));
        $firstType = $sessions[0]['type'] ?? null;

        $this->training_minutes = $totalMin ?: null;
        $this->training_type    = $firstType ?? '';

        auth()->user()->dailyLogs()->updateOrCreate(
            ['log_date' => $this->log_date],
            [
                'water_liters'     => max(0, min(20, (float) $this->water_liters)),
                'soda_cans'        => max(0, min(30, (int) $this->soda_cans)),
                'alcohol_drinks'   => $drinks,
                'alcohol_units'    => array_sum($drinks),
                'sessions'         => $sessions,
                'training_minutes' => $totalMin ?: null,
                'training_type'    => $firstType,
                'sleep_hours'      => ($this->sleep_hours === null || $this->sleep_hours === '') ? null : max(0, min(24, (int) $this->sleep_hours)),
                'mood'             => $this->mood,
                'energy_level'     => max(1, min(10, (int) $this->energy_level)),
                'notes'            => $this->notes,
            ]
        );
    }

    public function render()
    {
        $user = auth()->user();

        $logs = $user->dailyLogs()->orderByDesc('log_date')->take(14)->get();

        $confirmedCalories = $user->dailyLogs()
            ->whereDate('log_date', today())
            ->value('calories_consumed');

        $todayWeighIns = $user->weightEntries()->whereDate('weighed_at', today())->orderByDesc('weighed_at')->get();
        $pre  = $todayWeighIns->firstWhere('context', 'pre_workout');
        $post = $todayWeighIns->firstWhere('context', 'post_workout');
        $sweatLoss = ($pre && $post) ? round($pre->weight_kg - $post->weight_kg, 1) : null;

        $weightByDate = $user->weightEntries()
            ->where('weighed_at', '>=', now()->subDays(14)->startOfDay())
            ->orderBy('weighed_at')->get()
            ->groupBy(fn ($e) => $e->weighed_at->format('Y-m-d'))
            ->map(fn ($day) => (float) (($day->firstWhere('context', 'morning') ?? $day->sortByDesc('weighed_at')->first())->weight_kg));

        $nextFight = $user->fights()->where('result', 'upcoming')->orderBy('fight_date')->first();
        $fightDays = $nextFight ? max(0, (int) now()->diffInDays($nextFight->fight_date, false)) : null;

        $drinkTypes  = self::DRINKS;
        $sessionTypes = self::SESSION_TYPES;
        $totalDrinks = array_sum(array_intersect_key($this->alcohol_drinks, $drinkTypes));
        $alcoholKcal = 0;
        foreach ($drinkTypes as $k => $d) {
            $alcoholKcal += ($this->alcohol_drinks[$k] ?? 0) * $d[3];
        }

        return view('livewire.daily-log', compact(
            'logs', 'confirmedCalories', 'todayWeighIns', 'sweatLoss', 'weightByDate', 'fightDays',
            'drinkTypes', 'sessionTypes', 'totalDrinks', 'alcoholKcal'
        ))->layout('layouts.app');
    }
}
