<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Schema;

it('coach_marketing_profiles table exists with required columns', function () {
    expect(Schema::hasTable('coach_marketing_profiles'))->toBeTrue();
    $cols = ['coach_id','brand_name','specialty_primary','differentiator',
             'audience_age_range','audience_offer_main','preferred_methodologies',
             'voice_adjectives','active_offers','completed_at','last_updated_by'];
    foreach ($cols as $col) {
        expect(Schema::hasColumn('coach_marketing_profiles', $col))->toBeTrue("column {$col} missing");
    }
});

it('coach_id has unique constraint', function () {
    $indexes = collect(Schema::getIndexes('coach_marketing_profiles'));
    expect($indexes->contains(fn($i) => in_array('coach_id', $i['columns']) && $i['unique']))->toBeTrue();
});
