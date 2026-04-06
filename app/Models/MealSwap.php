<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealSwap extends Model
{
    protected $table = 'meal_swaps';

    protected $fillable = [
        'client_id',
        'recipe_id',
        'recipe_name',
        'original_meal_name',
        'swap_date',
        'calories',
        'protein_g',
        'carbs_g',
        'fat_g',
        'calories_diff',
        'protein_diff',
        'carbs_diff',
        'fat_diff',
    ];

    protected $casts = [
        'swap_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
