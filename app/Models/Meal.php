<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'user_id', 'name', 'meal_type', 'calories', 'protein_g',
        'carbs_g', 'fat_g', 'description', 'photo', 'eaten_at',
    ];

    protected $casts = [
        'eaten_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
