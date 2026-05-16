<?php

namespace App\Models;

use App\Scopes\OwnedByClientScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    use HasFactory;

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
        static::addGlobalScope(new OwnedByClientScope);
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

    /**
     * Calcula XP otorgado al cerrar la sesión.
     *
     * - Base 40 XP por sesión completada.
     * - Bonus 25 XP máximo, elegido como max entre:
     *   - strengthBonus: 25 si registró peso en TODOS sus sets de fuerza
     *   - cardioBonus:   25 si reportó RPE o tiene cardio_type estructurado,
     *                    15 si solo completó cardio sin RPE/structured
     *
     * Diseño: NO penaliza sesiones cardio-puro (Lizeth sábado HIIT) y
     * NO duplica el bonus en sesiones mixtas. Toma el bonus más alto.
     */
    public function awardXp(): int
    {
        $base = 40;

        $strengthLogs = $this->logs()->where('completed', true)->where('is_cardio', false);
        $cardioLogs   = $this->logs()->where('completed', true)->where('is_cardio', true);

        $strengthBonus = 0;
        if ($strengthLogs->exists()) {
            $allWeightsLogged = (clone $strengthLogs)->whereNull('weight_kg')->doesntExist();
            $strengthBonus = $allWeightsLogged ? 25 : 0;
        }

        $cardioBonus = 0;
        if ($cardioLogs->exists()) {
            $hasRpe = (clone $cardioLogs)->whereNotNull('rpe')->exists();
            $hasStructured = (clone $cardioLogs)
                ->whereNotNull('cardio_type')
                ->where('cardio_type', '!=', 'free')
                ->exists();
            $cardioBonus = ($hasRpe || $hasStructured) ? 25 : 15;
        }

        return $base + max($strengthBonus, $cardioBonus);
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
