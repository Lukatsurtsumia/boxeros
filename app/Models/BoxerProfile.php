<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxerProfile extends Model
{
    protected $fillable = [
        'user_id', 'nickname', 'weight_class', 'date_of_birth', 'current_weight', 'height_cm',
        'experience_years', 'total_fights', 'wins', 'losses', 'draws',
        'gym', 'trainer', 'stance', 'reach_cm', 'bio', 'avatar', 'goal_weight',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRecordAttribute(): string
    {
        return "{$this->wins}W - {$this->losses}L - {$this->draws}D";
    }
}
