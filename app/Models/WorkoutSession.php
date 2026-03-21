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
        'session_date',
        'duration_sec',
        'feeling',
        'notes',
        'completed',
        'total_volume_kg',
        'total_reps',
        'total_sets',
        'xp_earned',
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
        $this->total_sets = $logs->count();
        $this->total_reps = $logs->sum('reps');
        $this->total_volume_kg = (int) $logs->sum(fn ($l) => ($l->weight_kg ?? 0) * ($l->reps ?? 0));
        $this->save();
    }

    public function awardXp(): int
    {
        $base = 40;
        $allWeightsLogged = $this->logs()->where('completed', true)->whereNull('weight_kg')->doesntExist();
        $bonus = $allWeightsLogged ? 25 : 0;
        $this->xp_earned = $base + $bonus;
        $this->save();

        return $this->xp_earned;
    }

    public function formattedDuration(): string
    {
        $m = intdiv($this->duration_sec, 60);
        $s = $this->duration_sec % 60;

        return sprintf('%d:%02d', $m, $s);
    }
}
