<?php

namespace App\Enums;

enum CoachInvitationStatus: string
{
    case Sent = 'sent';
    case Opened = 'opened';
    case LinkClicked = 'link_clicked';
    case Paid = 'paid';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled]);
    }

    public function canResend(): bool
    {
        return in_array($this, [self::Expired, self::Failed]);
    }

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Enviada',
            self::Opened => 'Abierta',
            self::LinkClicked => 'Link visitado',
            self::Paid => 'Pagada',
            self::Expired => 'Expirada',
            self::Cancelled => 'Cancelada',
            self::Failed => 'Fallida',
        };
    }
}
