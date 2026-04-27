<?php

declare(strict_types=1);

use App\DataTransferObjects\Marketing\CoachDropV1;

it('CoachDropV1 fromArray + toArray roundtrips a full payload', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $dto     = CoachDropV1::fromArray($payload);

    expect($dto->toArray())->toBe($payload);
});
