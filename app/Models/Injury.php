<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Injury extends Model
{
    protected $fillable = [
        'user_id', 'body_part', 'title', 'description', 'severity',
        'status', 'injury_date', 'expected_recovery', 'ai_feedback', 'notes',
    ];

    protected $casts = [
        'injury_date' => 'date',
        'expected_recovery' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
