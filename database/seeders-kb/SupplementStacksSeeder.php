<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed de supplement_stacks desde docs/audit-motor-v2/supplement-stacks-seed.json.
 *
 * Idempotente: upsert por slug.
 * Cada stack referencia slugs de supplement_catalog en components_* (no se valida cross-ref acá,
 * eso queda para Stage 4 VALIDATE / lint rules cross-ref).
 */
final class SupplementStacksSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('docs/audit-motor-v2/supplement-stacks-seed.json');

        if (! is_file($jsonPath)) {
            $this->command?->warn("supplement-stacks-seed.json no encontrado — skip.");
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data) || ! isset($data['stacks']) || ! is_array($data['stacks'])) {
            $this->command?->warn('supplement-stacks-seed.json sin clave "stacks" — skip.');
            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($data['stacks'] as $stack) {
            $rows[] = [
                'slug' => (string) ($stack['slug'] ?? ''),
                'name' => (string) ($stack['name'] ?? ''),
                'name_short' => $stack['name_short'] ?? null,
                'objective' => $stack['objective'] ?? null,

                'applicable_objectives' => json_encode($stack['applicable_objectives'] ?? []),
                'applicable_genders' => json_encode($stack['applicable_genders'] ?? []),
                'applicable_tier_min' => (string) ($stack['applicable_tier_min'] ?? 'esencial'),
                'applicable_levels' => json_encode($stack['applicable_levels'] ?? []),
                'applicable_age_range' => json_encode($stack['applicable_age_range'] ?? []),
                'applicable_special_conditions' => json_encode($stack['applicable_special_conditions'] ?? []),

                'components_essential' => json_encode($stack['components_essential'] ?? []),
                'components_recommended' => json_encode($stack['components_recommended'] ?? []),
                'components_optional' => json_encode($stack['components_optional'] ?? []),

                'total_components_essential' => (int) ($stack['total_components_essential'] ?? count($stack['components_essential'] ?? [])),
                'total_components_recommended' => (int) ($stack['total_components_recommended'] ?? count($stack['components_recommended'] ?? [])),
                'total_components_optional' => (int) ($stack['total_components_optional'] ?? count($stack['components_optional'] ?? [])),
                'total_components_max_stack' => (int) ($stack['total_components_max_stack'] ?? 0),

                'stack_interactions_internal' => json_encode($stack['stack_interactions_internal'] ?? []),
                'client_interactions_externas' => json_encode($stack['client_interactions_externas'] ?? []),

                'expected_outcomes' => json_encode($stack['expected_outcomes'] ?? []),
                'expected_timeline_resultados' => $stack['expected_timeline_resultados'] ?? null,

                'approximate_monthly_cost_cop' => isset($stack['approximate_monthly_cost_cop']) && is_numeric($stack['approximate_monthly_cost_cop'])
                    ? (int) $stack['approximate_monthly_cost_cop'] : null,
                'approximate_monthly_cost_range_cop' => $stack['approximate_monthly_cost_range_cop'] ?? null,
                'cost_breakdown_note' => $stack['cost_breakdown_note'] ?? null,

                'observed_in_real_clients' => json_encode($stack['observed_in_real_clients'] ?? []),
                'alternatives_if_components_unavailable' => json_encode($stack['alternatives_if_components_unavailable'] ?? []),

                'scientific_rationale' => $stack['scientific_rationale'] ?? null,
                'scientific_sources' => json_encode($stack['scientific_sources'] ?? []),

                'legal_advertencia' => $stack['legal_advertencia'] ?? null,
                'tags' => json_encode($stack['tags'] ?? []),

                'confidence' => (string) ($stack['confidence'] ?? 'high'),
                'confidence_reason' => $stack['confidence_reason'] ?? null,
                'needs_daniel_validation' => (bool) ($stack['needs_daniel_validation'] ?? false),
                'needs_medical_review' => (bool) ($stack['needs_medical_review'] ?? false),

                'raw_data' => json_encode($stack, JSON_UNESCAPED_UNICODE),

                'version' => (int) ($stack['version'] ?? 1),
                'active' => (bool) ($stack['active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            $this->command?->warn('supplement-stacks-seed.json no contenía stacks — skip.');
            return;
        }

        DB::connection('kb')
            ->table('supplement_stacks')
            ->upsert($rows, ['slug'], [
                'name', 'name_short', 'objective',
                'applicable_objectives', 'applicable_genders', 'applicable_tier_min',
                'applicable_levels', 'applicable_age_range', 'applicable_special_conditions',
                'components_essential', 'components_recommended', 'components_optional',
                'total_components_essential', 'total_components_recommended',
                'total_components_optional', 'total_components_max_stack',
                'stack_interactions_internal', 'client_interactions_externas',
                'expected_outcomes', 'expected_timeline_resultados',
                'approximate_monthly_cost_cop', 'approximate_monthly_cost_range_cop',
                'cost_breakdown_note',
                'observed_in_real_clients', 'alternatives_if_components_unavailable',
                'scientific_rationale', 'scientific_sources',
                'legal_advertencia', 'tags',
                'confidence', 'confidence_reason',
                'needs_daniel_validation', 'needs_medical_review',
                'raw_data', 'version', 'active', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' supplement_stacks.');
    }
}
