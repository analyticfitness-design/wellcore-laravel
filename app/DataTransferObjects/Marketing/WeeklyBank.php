<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class WeeklyBank
{
    public function __construct(
        public array $altHooks,
        public array $altCtas,
        public array $altCaptions,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self($a['alt_hooks'], $a['alt_ctas'], $a['alt_captions']);
    }

    public function toArray(): array
    {
        return [
            'alt_hooks'    => $this->altHooks,
            'alt_ctas'     => $this->altCtas,
            'alt_captions' => $this->altCaptions,
        ];
    }
}
