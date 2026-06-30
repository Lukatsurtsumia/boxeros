<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    /** Whether this user's app language is French. */
    public function isFrench(): bool
    {
        return ($this->locale ?? 'en') === 'fr';
    }

    /**
     * The weekday a fighter's personal "week" starts on — the day they registered.
     * Every boxer trains on their own rhythm, so weeks (and the weekly recap) run
     * 7 days from sign-up rather than a fixed calendar Monday. Returned as a Carbon
     * day-of-week constant (0=Sun … 6=Sat) for use with startOfWeek().
     */
    public function weekStartDay(): int
    {
        return $this->created_at?->dayOfWeek ?? \Carbon\Carbon::MONDAY;
    }

    /** Days since the fighter registered (0 on sign-up day). */
    public function daysSinceJoined(): int
    {
        return $this->created_at ? (int) $this->created_at->copy()->startOfDay()->diffInDays(today()) : 0;
    }

    public function boxerProfile()
    {
        return $this->hasOne(BoxerProfile::class);
    }

    public function dailyLogs()
    {
        return $this->hasMany(DailyLog::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function fights()
    {
        return $this->hasMany(Fight::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function weightEntries()
    {
        return $this->hasMany(WeightEntry::class);
    }

    /** CORNER's long-term memory of this fighter (one row, built up across sessions). */
    public function coachMemory()
    {
        return $this->hasOne(CoachMemory::class);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /** The plan the fighter is currently following, if any. */
    public function activePlan(): ?Plan
    {
        return $this->plans()->where('is_active', true)->latest()->first();
    }

    public function todayLog()
    {
        return $this->hasOne(DailyLog::class)->whereDate('log_date', today());
    }

    /** Most recent weigh-in, or null if none logged. */
    public function latestWeight(): ?WeightEntry
    {
        return $this->weightEntries()->orderByDesc('weighed_at')->first();
    }

    /** Current weight in kg — latest weigh-in, falling back to the legacy profile field. */
    public function currentWeight(): ?float
    {
        $entry = $this->latestWeight();
        if ($entry) {
            return (float) $entry->weight_kg;
        }

        return $this->boxerProfile?->current_weight !== null
            ? (float) $this->boxerProfile->current_weight
            : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
