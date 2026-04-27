<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ScriptTimecodeRow
{
    public function __construct(
        public string $time,
        public string $dialogue,
        public string $visual,
        public string $editNotes,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            time: $a['time'],
            dialogue: $a['dialogue'],
            visual: $a['visual'],
            editNotes: $a['edit_notes'],
        );
    }

    public function toArray(): array
    {
        return [
            'time'       => $this->time,
            'dialogue'   => $this->dialogue,
            'visual'     => $this->visual,
            'edit_notes' => $this->editNotes,
        ];
    }
}
