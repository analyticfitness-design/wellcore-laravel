<?php

declare(strict_types=1);

use App\Exceptions\Marketing\InvalidDropSchema;
use App\Services\Marketing\DropSchemaValidator;

it('passes a valid coach_drop_v1 payload', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);

    (new DropSchemaValidator())->validate($payload);

    expect(true)->toBeTrue();
});

it('throws InvalidDropSchema on missing brief', function () {
    $payload = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    unset($payload['brief']);

    expect(fn () => (new DropSchemaValidator())->validate($payload))
        ->toThrow(InvalidDropSchema::class);
});

it('rejects unknown schema_version', function () {
    $payload                   = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $payload['schema_version'] = 'coach_drop_v9';

    expect(fn () => (new DropSchemaValidator())->validate($payload))
        ->toThrow(InvalidDropSchema::class);
});

it('rejects drop with only 1 reel instead of 2', function () {
    $payload          = json_decode(file_get_contents(base_path('tests/fixtures/coach_drop_v1_valid.json')), true);
    $payload['reels'] = [$payload['reels'][0]];

    expect(fn () => (new DropSchemaValidator())->validate($payload))
        ->toThrow(InvalidDropSchema::class);
});
