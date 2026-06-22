<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fight extends Model
{
    protected $fillable = [
        'user_id', 'opponent_name', 'event_name', 'venue', 'location',
        'fight_date', 'weight_class', 'rounds', 'result', 'result_method', 'notes',
    ];

    protected $casts = [
        'fight_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDaysUntilAttribute()
    {
        if ($this->result !== 'upcoming') return null;
        return now()->diffInDays($this->fight_date, false);
    }
}
