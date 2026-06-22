<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxerProfile extends Model
{
    protected $fillable = [
        'user_id', 'nickname', 'weight_class', 'current_weight', 'height_cm',
        'experience_years', 'total_fights', 'wins', 'losses', 'draws',
        'gym', 'trainer', 'stance', 'reach_cm', 'bio', 'avatar',
        'goal_weight', 'daily_water_goal_liters', 'daily_calorie_goal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRecordAttribute()
    {
        return "{$this->wins}W - {$this->losses}L - {$this->draws}D";
    }

    public function getWeightProgressAttribute()
    {
        if (!$this->goal_weight || !$this->current_weight) return 0;
        $start = 100; // assume start weight is always higher
        $progress = (($start - $this->current_weight) / ($start - $this->goal_weight)) * 100;
        return min(100, max(0, round($progress)));
    }
}
