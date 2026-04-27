<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class CoachDropV1
{
    public function __construct(
        public string $schemaVersion,
        public BriefSection $brief,
        /** @var array<int, ReelScript> */
        public array $reels,
        /** @var array<int, StoryDay> */
        public array $stories,
        public ProductionChecklist $checklist,
        public WeeklyBank $bank,
        public HashtagSets $hashtags,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            schemaVersion: $a['schema_version'],
            brief: BriefSection::fromArray($a['brief']),
            reels: array_map(fn ($r) => ReelScript::fromArray($r), $a['reels']),
            stories: array_map(fn ($s) => StoryDay::fromArray($s), $a['stories']),
            checklist: ProductionChecklist::fromArray($a['checklist']),
            bank: WeeklyBank::fromArray($a['bank']),
            hashtags: HashtagSets::fromArray($a['hashtags']),
        );
    }

    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'brief'          => $this->brief->toArray(),
            'reels'          => array_map(fn (ReelScript $r) => $r->toArray(), $this->reels),
            'stories'        => array_map(fn (StoryDay $s) => $s->toArray(), $this->stories),
            'checklist'      => $this->checklist->toArray(),
            'bank'           => $this->bank->toArray(),
            'hashtags'       => $this->hashtags->toArray(),
        ];
    }
}
