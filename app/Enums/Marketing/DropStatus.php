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
}
