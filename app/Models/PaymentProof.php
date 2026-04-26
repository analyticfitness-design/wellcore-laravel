<?php

namespace App\Models;

use App\Enums\PaymentProofMethod;
use App\Enums\PaymentProofStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'coach_id',
    'client_email',
    'client_name',
    'plan',
    'amount',
    'currency',
    'payment_method',
    'file_path',
    'file_disk',
    'file_mime',
    'file_size',
    'file_hash',
    'coach_note',
    'status',
    'reviewed_by',
    'review_note',
    'coach_invitation_id',
    'payment_id',
    'submitted_at',
    'reviewed_at',
    'expires_at',
])]
class PaymentProof extends Model
{
    use HasFactory;

    protected $table = 'payment_proofs';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'status' => PaymentProofStatus::class,
            'plan' => PlanType::class,
            'payment_method' => PaymentProofMethod::class,
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function coachInvitation(): BelongsTo
    {
        return $this->belongsTo(CoachInvitation::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('status', PaymentProofStatus::Pendiente);
    }

    public function scopeAprobado(Builder $query): Builder
    {
        return $query->where('status', PaymentProofStatus::Aprobado);
    }

    public function scopeExpirado(Builder $query): Builder
    {
        return $query->where('status', PaymentProofStatus::Expirado);
    }

    // -------------------------------------------------------------------------
    // Domain Methods
    // -------------------------------------------------------------------------

    public function isPendiente(): bool
    {
        return $this->status === PaymentProofStatus::Pendiente;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
