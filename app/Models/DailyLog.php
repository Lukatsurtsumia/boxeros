<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    protected $fillable = [
        'user_id', 'log_date', 'weight_kg', 'water_liters',
        'calories_consumed', 'sleep_hours', 'training_minutes',
        'mood', 'energy_level', 'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
