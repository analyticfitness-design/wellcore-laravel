<?php

namespace App\Models;

use App\Enums\PlanTicketStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanTicket extends Model
{
    protected $table = 'plan_tickets';

    protected $fillable = [
        'coach_id',
        'coach_name',
        'client_id',
        'client_name',
        'plan_type',
        'category',
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
        'deadline_at',
        'parent_ticket_id',
        'resubmitted_at',
        'generated_plan_ids',
        'rejection_code',
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
            'generated_plan_ids' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'deadline_at' => 'datetime',
            'resubmitted_at' => 'datetime',
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

    public function comments(): HasMany
    {
        return $this->hasMany(PlanTicketComment::class, 'plan_ticket_id')->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PlanTicketAttachment::class, 'plan_ticket_id')->orderByDesc('created_at');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_ticket_id');
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

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                PlanTicketStatus::Pendiente->value,
                PlanTicketStatus::EnRevision->value,
            ])
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now());
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [
            PlanTicketStatus::Borrador,
            PlanTicketStatus::Pendiente,
        ], true);
    }

    public function getTimeRemainingAttribute(): ?int
    {
        if (! $this->deadline_at) {
            return null;
        }

        return now()->diffInSeconds($this->deadline_at, false);
    }
}
