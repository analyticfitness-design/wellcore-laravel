<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Schema;

it('coach_content_drops table exists with required columns', function () {
    expect(Schema::hasTable('coach_content_drops'))->toBeTrue();
    $cols = ['coach_id','iso_year','iso_week','week_starts_on','status',
             'content','intake_snapshot','schema_version','generated_at',
             'reviewed_at','approved_at','ready_at','completed_at'];
    foreach ($cols as $col) {
        expect(Schema::hasColumn('coach_content_drops', $col))->toBeTrue("column {$col} missing");
    }
});

it('coach_content_drops has unique coach_week constraint', function () {
    $indexes = collect(Schema::getIndexes('coach_content_drops'));
    expect($indexes->contains(fn($i) => $i['name'] === 'uniq_coach_week' && $i['unique']))->toBeTrue();
});

it('coach_content_drops has status_week index', function () {
    $indexes = collect(Schema::getIndexes('coach_content_drops'));
    expect($indexes->contains(fn($i) => $i['name'] === 'idx_status_week'))->toBeTrue();
});
