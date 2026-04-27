<?php

declare(strict_types=1);

namespace App\Exceptions\Marketing;

final class InvalidDropSchema extends \DomainException
{
    /** @param array<int, array{path: string, message: string}> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Drop JSON failed schema validation: ' . count($errors) . ' error(s)');
    }
}
