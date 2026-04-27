<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class ReelScript
{
    public function __construct(
        public string $key,
        public string $type,
        public string $title,
        public array $formatMeta,
        public array $hook,
        /** @var array<int, ScriptTimecodeRow> */
        public array $timecodeTable,
        public string $caption,
        public string $musicNote,
        public string $productionNotes,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            key: $a['key'],
            type: $a['type'],
            title: $a['title'],
            formatMeta: $a['format_meta'],
            hook: $a['hook'],
            timecodeTable: array_map(fn ($r) => ScriptTimecodeRow::fromArray($r), $a['timecode_table']),
            caption: $a['caption'],
            musicNote: $a['music_note'],
            productionNotes: $a['production_notes'],
        );
    }

    public function toArray(): array
    {
        return [
            'key'              => $this->key,
            'type'             => $this->type,
            'title'            => $this->title,
            'format_meta'      => $this->formatMeta,
            'hook'             => $this->hook,
            'timecode_table'   => array_map(fn (ScriptTimecodeRow $r) => $r->toArray(), $this->timecodeTable),
            'caption'          => $this->caption,
            'music_note'       => $this->musicNote,
            'production_notes' => $this->productionNotes,
        ];
    }
}
