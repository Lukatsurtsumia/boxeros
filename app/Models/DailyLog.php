<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    protected $fillable = [
        'user_id', 'log_date',
        'weight_kg', 'weight_before_kg', 'weight_after_kg',
        'water_liters', 'soda_cans', 'alcohol_units', 'alcohol_drinks',
        'calories_consumed',
        'sleep_hours', 'training_minutes', 'training_type', 'sessions',
        'mood', 'energy_level', 'notes',
    ];

    protected $casts = [
        'log_date'       => 'date',
        'alcohol_drinks' => 'array',
        'sessions'       => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
