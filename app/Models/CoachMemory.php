<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CORNER's long-term memory of a single fighter — durable facts learned across all sessions
 * (goals, preferences, recurring issues, commitments, advice already given). One row per user.
 */
class CoachMemory extends Model
{
    protected $fillable = ['user_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
