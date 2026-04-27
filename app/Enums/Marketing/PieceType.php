<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_content_piece_states.piece_type DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum PieceType: string
{
    case Reel            = 'reel';
    case Story           = 'story';
    case ChecklistPhase  = 'checklist_phase';
}
