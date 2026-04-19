<?php

namespace App\Models;

use App\Enums\PlanTicketStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTicket extends Model
{
    protected $table = 'plan_tickets';

    protected $fillable = [
        'coach_id',
        'coach_name',
        'client_id',
        'client_name',
        'plan_type',
        'status',
        'datos_generales',
        'plan_entrenamiento',
        'plan_nutricional',
        'plan_habitos',
        'plan_suplementacion',
        'plan_ciclo',
        'notas_coach',
        'admin_notas',
        'submitted_at',
        'reviewed_at',
        'completed_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
            'status' => PlanTicketStatus::class,
            'datos_generales' => 'array',
            'plan_entrenamiento' => 'array',
            'plan_nutricional' => 'array',
            'plan_habitos' => 'array',
            'plan_suplementacion' => 'array',
            'plan_ciclo' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopeForCoach(Builder $query, int $coachId): Builder
    {
        return $query->where('coach_id', $coachId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PlanTicketStatus::Pendiente->value);
    }

    public function scopeForAdminInbox(Builder $query): Builder
    {
        return $query->where('status', '!=', PlanTicketStatus::Borrador->value);
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [
            PlanTicketStatus::Borrador,
            PlanTicketStatus::Pendiente,
        ], true);
    }
}
