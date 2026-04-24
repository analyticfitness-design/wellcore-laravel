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

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Scopes\OwnedByClientScope());
    }

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
        $this->total_volume_kg = (float) $this->logs()
            ->where('completed', true)
            ->selectRaw('SUM(COALESCE(weight_kg, 0) * COALESCE(reps, 0)) as volume')
            ->value('volume');

        $completedLogs = $this->logs()->where('completed', true)->get();
        $this->total_reps = $completedLogs->sum('reps');
        $this->total_sets = $completedLogs->count();
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
        $totalSec = $this->duration_sec ?? 0;
        $minutes = (int) floor($totalSec / 60);
        $seconds = $totalSec % 60;

        if ($minutes >= 60) {
            $h = intdiv($minutes, 60);
            $m = $minutes % 60;
            return sprintf('%d:%02d:%02d', $h, $m, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
