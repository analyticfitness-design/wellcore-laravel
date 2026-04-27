<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Schema;

it('coach_content_piece_states table exists with required columns', function () {
    expect(Schema::hasTable('coach_content_piece_states'))->toBeTrue();
    $cols = ['drop_id','coach_id','piece_type','piece_key','state',
             'published_url','notes','state_changed_at'];
    foreach ($cols as $col) {
        expect(Schema::hasColumn('coach_content_piece_states', $col))->toBeTrue("column {$col} missing");
    }
});

it('coach_content_piece_states has unique piece constraint', function () {
    $indexes = collect(Schema::getIndexes('coach_content_piece_states'));
    expect($indexes->contains(fn($i) => $i['name'] === 'uniq_piece' && $i['unique']))->toBeTrue();
});
