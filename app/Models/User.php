<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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

    public function injuries()
    {
        return $this->hasMany(Injury::class);
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
        ];
    }
}
