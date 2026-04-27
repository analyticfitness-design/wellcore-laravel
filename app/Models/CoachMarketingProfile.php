<?php
declare(strict_types=1);
namespace App\Models;

use App\Enums\Marketing\AudienceAgeRange;
use App\Enums\Marketing\AudienceGender;
use App\Enums\Marketing\AudienceOfferMain;
use App\Enums\Marketing\LastUpdatedBy;
use App\Enums\Marketing\SpecialtyPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CoachMarketingProfile extends Model
{
    use HasFactory;

    protected $table = 'coach_marketing_profiles';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'specialty_primary'            => SpecialtyPrimary::class,
            'specialty_secondary'          => SpecialtyPrimary::class,
            'audience_age_range'           => AudienceAgeRange::class,
            'audience_gender'              => AudienceGender::class,
            'audience_offer_main'          => AudienceOfferMain::class,
            'preferred_methodologies'      => 'array',
            'preferred_methodologies_other'=> 'array',
            'content_topics'               => 'array',
            'content_topics_other'         => 'array',
            'voice_adjectives'             => 'array',
            'voice_samples'                => 'array',
            'active_offers'                => 'array',
            'top_working_posts'            => 'array',
            'completed_at'                 => 'datetime',
            'last_updated_by'              => LastUpdatedBy::class,
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'coach_id');
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
