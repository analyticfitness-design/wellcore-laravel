<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

use App\Models\CoachMarketingProfile;

final readonly class MarketingProfile
{
    public function __construct(
        public string $brandName,
        public ?string $city,
        public ?string $countryCode,
        public string $specialtyPrimary,
        public ?string $specialtySecondary,
        public string $differentiator,
        public string $audienceAgeRange,
        public string $audienceGender,
        public string $audiencePainMain,
        public string $audienceOfferMain,
        public array $preferredMethodologies,
        public array $contentTopics,
        public array $voiceAdjectives,
        public array $voiceSamples,
        public array $activeOffers,
        public array $topWorkingPosts = [],
    ) {}

    public static function fromModel(CoachMarketingProfile $m): self
    {
        return new self(
            brandName: $m->brand_name,
            city: $m->city,
            countryCode: $m->country_code,
            specialtyPrimary: $m->specialty_primary?->value ?? 'otro',
            specialtySecondary: $m->specialty_secondary?->value,
            differentiator: $m->differentiator,
            audienceAgeRange: $m->audience_age_range->value,
            audienceGender: $m->audience_gender->value,
            audiencePainMain: $m->audience_pain_main,
            audienceOfferMain: $m->audience_offer_main->value,
            preferredMethodologies: $m->preferred_methodologies ?? [],
            contentTopics: $m->content_topics ?? [],
            voiceAdjectives: $m->voice_adjectives ?? [],
            voiceSamples: $m->voice_samples ?? [],
            activeOffers: $m->active_offers ?? [],
            topWorkingPosts: $m->top_working_posts ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'brand_name'              => $this->brandName,
            'city'                    => $this->city,
            'country_code'            => $this->countryCode,
            'specialty_primary'       => $this->specialtyPrimary,
            'specialty_secondary'     => $this->specialtySecondary,
            'differentiator'          => $this->differentiator,
            'audience_age_range'      => $this->audienceAgeRange,
            'audience_gender'         => $this->audienceGender,
            'audience_pain_main'      => $this->audiencePainMain,
            'audience_offer_main'     => $this->audienceOfferMain,
            'preferred_methodologies' => $this->preferredMethodologies,
            'content_topics'          => $this->contentTopics,
            'voice_adjectives'        => $this->voiceAdjectives,
            'voice_samples'           => $this->voiceSamples,
            'active_offers'           => $this->activeOffers,
            'top_working_posts'       => $this->topWorkingPosts,
        ];
    }
}
