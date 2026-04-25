<?php

namespace App\Models;

use App\Enums\CoachInvitationStatus;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'coach_id', 'code', 'email', 'name', 'plan', 'amount', 'currency',
    'subject', 'intro_message', 'cta_label',
    'wompi_payment_link_id', 'wompi_payment_link_url', 'wompi_reference',
    'payment_id', 'client_id', 'status', 'resend_count',
    'sent_at', 'opened_at', 'clicked_at', 'paid_at', 'expires_at', 'cancelled_at', 'meta',
])]
class CoachInvitation extends Model
{
    use SoftDeletes;

    protected $table = 'coach_invitations';

    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'status' => CoachInvitationStatus::class,
            'amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function canResend(): bool
    {
        return $this->status->canResend() && $this->resend_count < 3;
    }

    public function invitationUrl(): string
    {
        return url('/invitacion/'.$this->code);
    }

    public function pixelUrl(): string
    {
        return url('/invitacion-pixel/'.$this->code);
    }
}
