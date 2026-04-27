<?php
declare(strict_types=1);
namespace App\Enums\Marketing;

/**
 * Cases match coach_content_piece_states.state DB enum exactly.
 * M2 will add label() and helper methods.
 */
enum PieceState: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Published  = 'published';
    case Skipped    = 'skipped';
}
