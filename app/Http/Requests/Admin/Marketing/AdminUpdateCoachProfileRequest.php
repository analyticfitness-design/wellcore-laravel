<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Marketing;

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\SpecialtyPrimary;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class AdminUpdateCoachProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::Admin, UserRole::Superadmin], strict: true);
    }

    public function rules(): array
    {
        return [
            'brand_name'                    => ['required', 'string', 'max:120'],
            'city'                          => ['nullable', 'string', 'max:80'],
            'country_code'                  => ['nullable', 'string', 'size:2'],
            'specialty_primary'             => ['required', new Enum(SpecialtyPrimary::class)],
            'specialty_primary_other'       => ['nullable', 'string', 'max:80'],
            'specialty_secondary'           => ['nullable', new Enum(SpecialtyPrimary::class)],
            'specialty_secondary_other'     => ['nullable', 'string', 'max:80'],
            'differentiator'               => ['required', 'string', 'min:20', 'max:1000'],
            'audience_age_range'            => ['required', new Enum(AudienceAgeRange::class)],
            'audience_gender'               => ['required', new Enum(AudienceGender::class)],
            'audience_pain_main'            => ['required', 'string', 'max:200'],
            'audience_offer_main'           => ['required', new Enum(AudienceOfferMain::class)],
            'preferred_methodologies'       => ['required', 'array', 'min:1', 'max:10'],
            'preferred_methodologies.*'     => ['string', 'max:80'],
            'preferred_methodologies_other' => ['nullable', 'array', 'max:5'],
            'content_topics'                => ['required', 'array', 'min:1', 'max:10'],
            'content_topics.*'              => ['string', 'max:80'],
            'content_topics_other'          => ['nullable', 'array', 'max:5'],
            'voice_adjectives'              => ['required', 'array', 'size:3'],
            'voice_adjectives.*'            => ['string', 'max:30'],
            'voice_samples'                 => ['nullable', 'array', 'max:3'],
            'voice_samples.*.caption'       => ['required', 'string', 'max:2200'],
            'voice_samples.*.source_url'    => ['nullable', 'url'],
            'voice_samples.*.note'          => ['nullable', 'string', 'max:200'],
            'active_offers'                 => ['required', 'array', 'min:1', 'max:3'],
            'active_offers.*.name'          => ['required', 'string', 'max:80'],
            'active_offers.*.price'         => ['required', 'numeric', 'min:0'],
            'active_offers.*.currency'      => ['required', 'string', 'size:3'],
            'active_offers.*.promo'         => ['nullable', 'string', 'max:200'],
            'top_working_posts'             => ['nullable', 'array', 'max:3'],
            'top_working_posts.*.url'       => ['required', 'url'],
            'top_working_posts.*.why_worked' => ['required', 'string', 'max:300'],
        ];
    }
}
