<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class StoryDay
{
    public function __construct(
        public string $day,
        public string $pillar,
        /** @var array<int, StorySlide> */
        public array $slides,
        public string $dmFollowupHint,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            $a['day'],
            $a['pillar'],
            array_map(fn ($s) => StorySlide::fromArray($s), $a['slides']),
            $a['dm_followup_hint'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'day'              => $this->day,
            'pillar'           => $this->pillar,
            'slides'           => array_map(fn ($s) => $s->toArray(), $this->slides),
            'dm_followup_hint' => $this->dmFollowupHint,
        ];
    }
}
