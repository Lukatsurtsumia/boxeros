<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['user_id', 'path', 'type', 'caption', 'taken_at'];

    protected $casts = [
        'taken_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
