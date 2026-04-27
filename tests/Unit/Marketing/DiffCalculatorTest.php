<?php

declare(strict_types=1);

use App\Services\Marketing\DropDiffCalculator;

it('detects single nested field change', function () {
    $diff = (new DropDiffCalculator)->diff(
        ['brief' => ['title' => 'A', 'objective' => 'same']],
        ['brief' => ['title' => 'B', 'objective' => 'same']],
    );

    expect($diff)->toBe([['path' => 'brief.title', 'original' => 'A', 'edited' => 'B']]);
});

it('treats list arrays as leaves (no per-index diff)', function () {
    $diff = (new DropDiffCalculator)->diff(
        ['bank' => ['alt_hooks' => ['a', 'b', 'c']]],
        ['bank' => ['alt_hooks' => ['a', 'x', 'c']]],
    );

    expect(count($diff))->toBe(1)
        ->and($diff[0]['path'])->toBe('bank.alt_hooks');
});

it('returns empty array when no changes', function () {
    $diff = (new DropDiffCalculator)->diff(
        ['brief' => ['title' => 'Same']],
        ['brief' => ['title' => 'Same']],
    );

    expect($diff)->toBeEmpty();
});
