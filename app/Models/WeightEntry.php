<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightEntry extends Model
{
    protected $fillable = [
        'user_id', 'weight_kg', 'context', 'weighed_at',
    ];

    protected $casts = [
        'weighed_at' => 'datetime',
        'weight_kg'  => 'decimal:2',
    ];

    /** Human label for the context tag. */
    public function getContextLabelAttribute(): string
    {
        return [
            'morning'      => 'Morning',
            'afternoon'    => 'Afternoon',
            'night'        => 'Night',
            'pre_workout'  => 'Pre-workout',
            'post_workout' => 'Post-workout',
            'other'        => 'Weigh-in',
        ][$this->context] ?? 'Weigh-in';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
