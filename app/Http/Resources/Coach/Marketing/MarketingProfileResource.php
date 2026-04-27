<?php

declare(strict_types=1);

namespace App\Http\Resources\Coach\Marketing;

use App\Models\CoachMarketingProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CoachMarketingProfile */
final class MarketingProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_name' => $this->brand_name,
            'city' => $this->city,
            'country_code' => $this->country_code,
            'specialty_primary' => $this->specialty_primary?->value,
            'specialty_primary_other' => $this->specialty_primary_other,
            'specialty_secondary' => $this->specialty_secondary?->value,
            'specialty_secondary_other' => $this->specialty_secondary_other,
            'differentiator' => $this->differentiator,
            'audience_age_range' => $this->audience_age_range?->value,
            'audience_gender' => $this->audience_gender?->value,
            'audience_pain_main' => $this->audience_pain_main,
            'audience_offer_main' => $this->audience_offer_main?->value,
            'preferred_methodologies' => $this->preferred_methodologies ?? [],
            'preferred_methodologies_other' => $this->preferred_methodologies_other ?? [],
            'content_topics' => $this->content_topics ?? [],
            'content_topics_other' => $this->content_topics_other ?? [],
            'voice_adjectives' => $this->voice_adjectives ?? [],
            'voice_samples' => $this->voice_samples ?? [],
            'active_offers' => $this->active_offers ?? [],
            'top_working_posts' => $this->top_working_posts ?? [],
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_complete' => $this->isComplete(),
        ];
    }
}
