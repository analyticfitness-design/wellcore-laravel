<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPr extends Model
{
    protected $table = 'workout_prs';

    protected $fillable = [
        'client_id',
        'exercise_name',
        'weight_kg',
        'reps',
        'volume',
        'achieved_at',
        'is_current',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'volume' => 'decimal:2',
        'achieved_at' => 'date',
        'is_current' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public static function checkAndAward(int $clientId, string $exercise, float $weight, int $reps): ?self
    {
        $volume = $weight * $reps;

        $currentPr = static::where('client_id', $clientId)
            ->where('exercise_name', $exercise)
            ->where('is_current', true)
            ->first();

        if (!$currentPr || $volume > ($currentPr->volume ?? 0)) {
            if ($currentPr) {
                $currentPr->update(['is_current' => false]);
            }

            return static::create([
                'client_id' => $clientId,
                'exercise_name' => $exercise,
                'weight_kg' => $weight,
                'reps' => $reps,
                'volume' => $volume,
                'achieved_at' => now()->toDateString(),
                'is_current' => true,
            ]);
        }

        return null;
    }
}
