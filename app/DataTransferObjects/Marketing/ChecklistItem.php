<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ChecklistItem
{
    public function __construct(
        public string $title,
        public string $detail,
        public array $subitems = [],
    ) {}

    public static function fromArray(array $a): self
    {
        return new self($a['title'], $a['detail'], $a['subitems'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'title'    => $this->title,
            'detail'   => $this->detail,
            'subitems' => $this->subitems,
        ];
    }
}
