<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.supplement_stacks — combinaciones pre-armadas para Sprint 13 motor v2.
 *
 * components_essential / _recommended / _optional son arrays de objetos:
 *   [
 *     "supplement_slug" => "proteina-whey-concentrada",
 *     "dosis_recommended" => "1 scoop post-entreno",
 *     "timing" => "post_entreno_inmediato",
 *     "frequency" => "diaria_solo_dias_entreno",
 *     "rationale" => "..."
 *   ]
 */
class SupplementStack extends Model
{
    protected $connection = 'kb';
    protected $table = 'supplement_stacks';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'applicable_objectives' => 'array',
            'applicable_genders' => 'array',
            'applicable_levels' => 'array',
            'applicable_age_range' => 'array',
            'applicable_special_conditions' => 'array',
            'components_essential' => 'array',
            'components_recommended' => 'array',
            'components_optional' => 'array',
            'stack_interactions_internal' => 'array',
            'client_interactions_externas' => 'array',
            'expected_outcomes' => 'array',
            'observed_in_real_clients' => 'array',
            'alternatives_if_components_unavailable' => 'array',
            'scientific_sources' => 'array',
            'tags' => 'array',
            'raw_data' => 'array',
            'active' => 'boolean',
            'needs_daniel_validation' => 'boolean',
            'needs_medical_review' => 'boolean',
            'approximate_monthly_cost_cop' => 'integer',
        ];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }
}
