<?php
declare(strict_types=1);
use App\Models\CoachContentDrop;
use App\Models\CoachMarketingProfile;

it('CoachMarketingProfile factory creates record', function () {
    $profile = CoachMarketingProfile::factory()->create();
    expect($profile->exists)->toBeTrue();
    expect($profile->preferred_methodologies)->toBeArray();
});

it('CoachContentDrop factory states work', function () {
    $drop = CoachContentDrop::factory()->ready()->create();
    expect($drop->status->value)->toBe('ready');
    expect($drop->content)->toBeArray();
});
