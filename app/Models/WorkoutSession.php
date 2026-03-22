<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSession extends Model
{
    protected $table = 'workout_sessions';

    protected $fillable = [
        'client_id',
        'plan_id',
        'day_name',
        'day_index',
        'session_date',
        'duration_minutes',
        'feeling',
        'notes',
        'completed',
        'total_volume',
    ];

    protected $casts = [
        'session_date' => 'date',
        'completed' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class, 'session_id');
    }

    public function calculateTotals(): void
    {
        $logs = $this->logs()->where('completed', true)->get();
        $this->total_volume = (int) $logs->sum(fn ($l) => ($l->weight_kg ?? 0) * ($l->reps ?? 0));
        $this->save();
    }

    public function awardXp(): int
    {
        $base = 40;
        $allWeightsLogged = $this->logs()->where('completed', true)->whereNull('weight_kg')->doesntExist();
        $bonus = $allWeightsLogged ? 25 : 0;

        return $base + $bonus;
    }

    public function formattedDuration(): string
    {
        $totalSec = ($this->duration_minutes ?? 0) * 60;
        $m = intdiv($totalSec, 60);
        $s = $totalSec % 60;

        return sprintf('%d:%02d', $m, $s);
    }
}
