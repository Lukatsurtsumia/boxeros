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
            'morning'      => __('Morning'),
            'afternoon'    => __('Afternoon'),
            'night'        => __('Night'),
            'pre_workout'  => __('Pre-workout'),
            'post_workout' => __('Post-workout'),
            'other'        => __('Weigh-in'),
        ][$this->context] ?? __('Weigh-in');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
