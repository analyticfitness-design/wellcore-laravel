<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalRecord extends Model
{
    protected $table = 'personal_records';

    protected $fillable = [
        'client_id',
        'exercise',
        'category',
        'weight',
        'reps',
        'duration_sec',
        'distance_km',
        'notes',
        'achieved_at',
        'is_current',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'achieved_at' => 'date',
        'is_current' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
