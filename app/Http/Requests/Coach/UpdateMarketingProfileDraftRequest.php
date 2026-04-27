<?php

declare(strict_types=1);

namespace App\Http\Requests\Coach;

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\SpecialtyPrimary;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class UpdateMarketingProfileDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Coach;
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['sometimes', 'string', 'max:120'],
            'city' => ['sometimes', 'nullable', 'string', 'max:80'],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2'],
            'specialty_primary' => ['sometimes', new Enum(SpecialtyPrimary::class)],
            'specialty_primary_other' => ['sometimes', 'nullable', 'string', 'max:80'],
            'specialty_secondary' => ['sometimes', 'nullable', new Enum(SpecialtyPrimary::class)],
            'specialty_secondary_other' => ['sometimes', 'nullable', 'string', 'max:80'],
            'differentiator' => ['sometimes', 'string', 'min:20', 'max:1000'],
            'audience_age_range' => ['sometimes', new Enum(AudienceAgeRange::class)],
            'audience_gender' => ['sometimes', new Enum(AudienceGender::class)],
            'audience_pain_main' => ['sometimes', 'string', 'max:200'],
            'audience_offer_main' => ['sometimes', new Enum(AudienceOfferMain::class)],
            'preferred_methodologies' => ['sometimes', 'array', 'min:1', 'max:10'],
            'preferred_methodologies.*' => ['string', 'max:80'],
            'preferred_methodologies_other' => ['sometimes', 'nullable', 'array', 'max:5'],
            'content_topics' => ['sometimes', 'array', 'min:1', 'max:10'],
            'content_topics.*' => ['string', 'max:80'],
            'content_topics_other' => ['sometimes', 'nullable', 'array', 'max:5'],
            'voice_adjectives' => ['sometimes', 'array', 'size:3'],
            'voice_adjectives.*' => ['string', 'max:30'],
            'voice_samples' => ['sometimes', 'nullable', 'array', 'max:3'],
            'voice_samples.*.caption' => ['required_with:voice_samples', 'string', 'max:2200'],
            'voice_samples.*.source_url' => ['nullable', 'url'],
            'voice_samples.*.note' => ['nullable', 'string', 'max:200'],
            'active_offers' => ['sometimes', 'array', 'min:1', 'max:3'],
            'active_offers.*.name' => ['required_with:active_offers', 'string', 'max:80'],
            'active_offers.*.price' => ['required_with:active_offers', 'numeric', 'min:0'],
            'active_offers.*.currency' => ['required_with:active_offers', 'string', 'size:3'],
            'active_offers.*.promo' => ['nullable', 'string', 'max:200'],
            'top_working_posts' => ['sometimes', 'nullable', 'array', 'max:3'],
            'top_working_posts.*.url' => ['required_with:top_working_posts', 'url'],
            'top_working_posts.*.why_worked' => ['required_with:top_working_posts', 'string', 'max:300'],
        ];
    }
}
