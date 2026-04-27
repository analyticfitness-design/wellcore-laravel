<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class HashtagSets
{
    public function __construct(public array $sets) {}

    public static function fromArray(array $a): self
    {
        return new self($a['sets']);
    }

    public function toArray(): array
    {
        return ['sets' => $this->sets];
    }
}
