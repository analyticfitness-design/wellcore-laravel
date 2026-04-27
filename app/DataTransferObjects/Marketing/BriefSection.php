<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class BriefSection
{
    public function __construct(
        public string $title,
        public string $objective,
        public string $priorityOffer,
        public string $keyMessage,
        public string $targetMetric,
        public string $weeklyTheme,
        public string $framingCopy,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            title: $a['title'],
            objective: $a['objective'],
            priorityOffer: $a['priority_offer'],
            keyMessage: $a['key_message'],
            targetMetric: $a['target_metric'],
            weeklyTheme: $a['weekly_theme'],
            framingCopy: $a['framing_copy'],
        );
    }

    public function toArray(): array
    {
        return [
            'title'         => $this->title,
            'objective'     => $this->objective,
            'priority_offer' => $this->priorityOffer,
            'key_message'   => $this->keyMessage,
            'target_metric' => $this->targetMetric,
            'weekly_theme'  => $this->weeklyTheme,
            'framing_copy'  => $this->framingCopy,
        ];
    }
}
