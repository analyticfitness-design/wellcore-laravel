<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class StorySlide
{
    public function __construct(
        public string $kind,
        public string $text,
        public string $visualHint,
        public string $sticker,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self($a['kind'], $a['text'], $a['visual_hint'], $a['sticker']);
    }

    public function toArray(): array
    {
        return [
            'kind'        => $this->kind,
            'text'        => $this->text,
            'visual_hint' => $this->visualHint,
            'sticker'     => $this->sticker,
        ];
    }
}
