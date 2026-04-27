<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ProductionChecklist
{
    public function __construct(
        /** @var array<int, ChecklistPhase> */
        public array $phases,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(array_map(fn ($p) => ChecklistPhase::fromArray($p), $a['phases']));
    }

    public function toArray(): array
    {
        return ['phases' => array_map(fn ($p) => $p->toArray(), $this->phases)];
    }
}
