<?php

declare(strict_types=1);

use App\DataTransferObjects\Marketing\ScriptTimecodeRow;

it('roundtrips from/to array', function () {
    $a = ['time' => '00:00-00:03', 'dialogue' => 'D', 'visual' => 'V', 'edit_notes' => 'E'];

    expect(ScriptTimecodeRow::fromArray($a)->toArray())->toBe($a);
});
