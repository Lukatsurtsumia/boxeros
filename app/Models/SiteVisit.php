<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per unique visitor per day (deduped by the unique hash+day index). Used only to
 * show visitor counts in the admin panel. The hash is anonymous and non-reversible.
 */
class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = ['visitor_hash', 'visited_on', 'created_at'];

    protected $casts = [
        'visited_on' => 'date',
        'created_at' => 'datetime',
    ];
}
