<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'plan_type',
    'content',
    'version',
    'assigned_by',
    'valid_from',
    'expires_at',
    'active',
])]
class AssignedPlan extends Model
{
    protected $table = 'assigned_plans';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'valid_from' => 'date',
            'expires_at' => 'date',
            'active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    /**
     * ¿Plan expiró hoy o antes? Si no tiene expires_at, se considera no-expirable.
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return Carbon::parse($this->expires_at)->startOfDay()->lessThanOrEqualTo(Carbon::now()->startOfDay());
    }

    /**
     * Días restantes hasta expiración. Negativo si ya expiró. Null si no tiene fecha.
     */
    public function daysUntilExpiry(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays(
            Carbon::parse($this->expires_at)->startOfDay(),
            false
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Auto-calcula expires_at = valid_from + 30 días si no viene explícito.
     *
     * Aplica a TODOS los planes asignados (nutricion, entrenamiento, habitos,
     * suplementacion, etc.) porque el "plan_type" aquí describe el contenido,
     * no el nivel de suscripción. El PlanLockService decide si el cliente
     * está bloqueado cruzando clients.plan (esencial/metodo/elite) con el
     * expires_at del assigned_plan más reciente.
     */
    protected static function booted(): void
    {
        static::creating(function (AssignedPlan $plan) {
            if ($plan->expires_at) {
                return;
            }

            $from = $plan->valid_from ? Carbon::parse($plan->valid_from) : Carbon::now();
            $plan->expires_at = $from->copy()->addDays(30)->toDateString();
        });
    }
}
