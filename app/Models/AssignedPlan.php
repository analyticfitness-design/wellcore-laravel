<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

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
    use HasFactory;

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

        return Carbon::parse($this->expires_at)->startOfDay()
            ->lessThanOrEqualTo(Carbon::now('America/Bogota')->startOfDay());
    }

    /**
     * Días restantes hasta expiración. Negativo si ya expiró. Null si no tiene fecha.
     */
    public function daysUntilExpiry(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return (int) Carbon::now('America/Bogota')->startOfDay()->diffInDays(
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

    protected static function booted(): void
    {
        static::creating(function (AssignedPlan $plan) {
            // SECURITY: hard cap — valid_from no más de 7 días en el futuro
            if ($plan->valid_from) {
                $from = Carbon::parse($plan->valid_from);
                if ($from->greaterThan(Carbon::now()->addDays(7))) {
                    throw new \InvalidArgumentException(
                        'valid_from no puede ser más de 7 días en el futuro'
                    );
                }
            }

            if ($plan->expires_at) {
                return; // respetar valor explícito
            }

            $isTrial = str_contains((string) $plan->plan_type, 'trial');

            // P1.4: heredar expires_at del ciclo de facturación activo del cliente.
            // Solo para planes no-trial y cuando el cliente ya tiene planes activos con fecha futura.
            if (! $isTrial && $plan->client_id) {
                $inheritedExpiry = self::query()
                    ->forClient($plan->client_id)
                    ->active()
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '>', Carbon::now()->toDateString())
                    ->max('expires_at');

                if ($inheritedExpiry) {
                    $plan->expires_at = $inheritedExpiry;

                    return;
                }
            }

            $from = $plan->valid_from ? Carbon::parse($plan->valid_from) : Carbon::now();
            $duration = $isTrial ? 3 : 30;
            $plan->expires_at = $from->copy()->addDays($duration)->toDateString();
        });

        // Flush cache de lock cuando el plan cambia o se elimina
        $flushCache = function (AssignedPlan $plan) {
            if ($plan->client_id) {
                Cache::forget("plan_lock_status:{$plan->client_id}");
            }
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }
}
