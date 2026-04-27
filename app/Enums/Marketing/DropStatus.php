<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_content_drops.status DB enum exactly.
 * M2 will add label(), isTerminal(), and other helper methods.
 */
enum DropStatus: string
{
    case Pending     = 'pending';
    case Generating  = 'generating';
    case InReview    = 'in_review';
    case Approved    = 'approved';
    case Ready       = 'ready';
    case InProgress  = 'in_progress';
    case Completed   = 'completed';
    case Archived    = 'archived';

    public function isVisibleToCoach(): bool
    {
        return match ($this) {
            self::Ready, self::InProgress, self::Completed, self::Archived => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pendiente',
            self::Generating => 'Generando',
            self::InReview   => 'En revisión',
            self::Approved   => 'Aprobado',
            self::Ready      => 'Listo',
            self::InProgress => 'En progreso',
            self::Completed  => 'Completado',
            self::Archived   => 'Archivado',
        };
    }
}
