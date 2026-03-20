<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodAnalysis extends Model
{
    protected $table = 'food_analyses';

    protected $fillable = [
        'client_id',
        'image_path',
        'food_name',
        'calories',
        'protein',
        'carbs',
        'fat',
        'ai_response',
        'source',
    ];

    protected $casts = [
        'ai_response' => 'array',
        'protein' => 'decimal:1',
        'carbs' => 'decimal:1',
        'fat' => 'decimal:1',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
