<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ChecklistPhase
{
    public function __construct(
        public string $key,
        public string $title,
        /** @var array<int, ChecklistItem> */
        public array $items,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            $a['key'],
            $a['title'],
            array_map(fn ($i) => ChecklistItem::fromArray($i), $a['items']),
        );
    }

    public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'title' => $this->title,
            'items' => array_map(fn ($i) => $i->toArray(), $this->items),
        ];
    }
}
