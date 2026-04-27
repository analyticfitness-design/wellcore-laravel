<?php

declare(strict_types=1);

use App\Enums\Marketing\DropStatus;

it('returns true on isVisibleToCoach for ready/in_progress/completed/archived', function () {
    expect(DropStatus::Ready->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::InProgress->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::Completed->isVisibleToCoach())->toBeTrue()
        ->and(DropStatus::Archived->isVisibleToCoach())->toBeTrue();
});

it('returns false on isVisibleToCoach for pre-approval states', function () {
    expect(DropStatus::Pending->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::Generating->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::InReview->isVisibleToCoach())->toBeFalse()
        ->and(DropStatus::Approved->isVisibleToCoach())->toBeFalse();
});
