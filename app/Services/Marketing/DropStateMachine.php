<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Enums\Marketing\DropStatus;
use App\Exceptions\Marketing\InvalidDropTransition;
use App\Models\Admin;
use App\Models\CoachContentDrop;

final class DropStateMachine
{
    private const TRANSITIONS = [
        'pending'     => [DropStatus::Generating],
        'generating'  => [DropStatus::InReview, DropStatus::Pending],
        'in_review'   => [DropStatus::Approved, DropStatus::Pending],
        'approved'    => [DropStatus::Ready],
        'ready'       => [DropStatus::InProgress, DropStatus::Archived],
        'in_progress' => [DropStatus::Completed, DropStatus::Archived],
        'completed'   => [DropStatus::Archived],
        'archived'    => [],
    ];

    public function transition(CoachContentDrop $drop, DropStatus $next, Admin $actor): void
    {
        $allowed = self::TRANSITIONS[$drop->status->value] ?? [];

        if (!in_array($next, $allowed, strict: true)) {
            throw new InvalidDropTransition($drop->status, $next);
        }

        $drop->status = $next;

        match ($next) {
            DropStatus::Generating => $drop->generated_at ??= now(),
            DropStatus::InReview   => $drop->reviewed_at = now(),
            DropStatus::Approved   => ($drop->approved_at = now()) && ($drop->approved_by_id = $actor->id),
            DropStatus::Ready      => $drop->ready_at = now(),
            DropStatus::Completed  => $drop->completed_at = now(),
            default                => null,
        };

        $drop->save();
    }

    public function canTransition(CoachContentDrop $drop, DropStatus $next): bool
    {
        $allowed = self::TRANSITIONS[$drop->status->value] ?? [];

        return in_array($next, $allowed, strict: true);
    }
}
