<?php
declare(strict_types=1);
use App\Models\CoachContentDrop;
use App\Models\CoachMarketingProfile;
use App\Policies\Coach\CoachContentDropPolicy;
use App\Policies\Coach\CoachMarketingProfilePolicy;
use Illuminate\Support\Facades\Gate;

it('CoachContentDrop policy resolves to CoachContentDropPolicy', function () {
    expect(Gate::getPolicyFor(CoachContentDrop::class))
        ->toBeInstanceOf(CoachContentDropPolicy::class);
});

it('CoachMarketingProfile policy resolves to CoachMarketingProfilePolicy', function () {
    expect(Gate::getPolicyFor(CoachMarketingProfile::class))
        ->toBeInstanceOf(CoachMarketingProfilePolicy::class);
});

it('admin gate abilities are registered', function () {
    expect(Gate::has('admin.marketing.approveDrop'))->toBeTrue();
    expect(Gate::has('admin.marketing.viewDrop'))->toBeTrue();
});
