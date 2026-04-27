<?php

declare(strict_types=1);

use App\Models\CoachContentDrop;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('archives drops completed >= 30 days ago', function () {
    $old = CoachContentDrop::factory()->completed()->create(['completed_at' => now()->subDays(31)]);
    $recent = CoachContentDrop::factory()->completed()->create(['completed_at' => now()->subDays(10)]);

    $this->artisan('wellcore:archive-old-drops')->assertSuccessful();

    expect($old->fresh()->status->value)->toBe('archived')
        ->and($recent->fresh()->status->value)->toBe('completed');
});

it('respects custom days option', function () {
    $drop = CoachContentDrop::factory()->completed()->create(['completed_at' => now()->subDays(8)]);

    $this->artisan('wellcore:archive-old-drops --days=7')->assertSuccessful();

    expect($drop->fresh()->status->value)->toBe('archived');
});

it('does not archive non-completed drops', function () {
    $drop = CoachContentDrop::factory()->ready()->create();

    $this->artisan('wellcore:archive-old-drops')->assertSuccessful();

    expect($drop->fresh()->status->value)->toBe('ready');
});
