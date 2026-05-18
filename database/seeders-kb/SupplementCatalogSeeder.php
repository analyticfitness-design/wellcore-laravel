<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed de supplement_catalog desde docs/audit-motor-v2/supplement-catalog-seed.json.
 *
 * Idempotente: upsert por slug.
 * NO incluye compounds hormonales prescripcionales (esos viven en hormonal_compounds).
 */
final class SupplementCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('docs/audit-motor-v2/supplement-catalog-seed.json');

        if (! is_file($jsonPath)) {
            $this->command?->warn("supplement-catalog-seed.json no encontrado — skip.");
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data) || ! isset($data['supplements']) || ! is_array($data['supplements'])) {
            $this->command?->warn('supplement-catalog-seed.json sin clave "supplements" — skip.');
            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($data['supplements'] as $sup) {
            $rows[] = [
                'slug' => (string) ($sup['slug'] ?? ''),
                'name' => (string) ($sup['name'] ?? ''),
                'name_alternatives' => json_encode($sup['name_alternatives'] ?? []),
                'scientific_name' => $sup['scientific_name'] ?? null,

                'category' => (string) ($sup['category'] ?? 'otro'),
                'primary_action' => (string) ($sup['primary_action'] ?? 'salud_general'),
                'type' => (string) ($sup['type'] ?? 'compound_aislado'),

                'blend_components' => json_encode($sup['blend_components'] ?? []),

                'dosis_recommended' => json_encode($sup['dosis_recommended'] ?? new \stdClass()),
                'timing_recommended' => json_encode($sup['timing_recommended'] ?? []),
                'frequency' => $sup['frequency'] ?? null,

                'macros_per_serving' => json_encode($sup['macros_per_serving'] ?? new \stdClass()),
                'serves_as_food' => (bool) ($sup['serves_as_food'] ?? false),

                'applicable_gender' => json_encode($sup['applicable_gender'] ?? []),
                'applicable_tier_min' => (string) ($sup['applicable_tier_min'] ?? 'esencial'),
                'applicable_objectives' => json_encode($sup['applicable_objectives'] ?? []),
                'applicable_levels' => json_encode($sup['applicable_levels'] ?? []),
                'applicable_age_range' => json_encode($sup['applicable_age_range'] ?? []),

                'evidence_level' => (string) ($sup['evidence_level'] ?? 'moderada'),
                'evidence_summary' => $sup['evidence_summary'] ?? null,

                'contraindications' => json_encode($sup['contraindications'] ?? []),
                'medical_interactions' => json_encode($sup['medical_interactions'] ?? []),
                'side_effects_common' => json_encode($sup['side_effects_common'] ?? []),
                'side_effects_rare' => json_encode($sup['side_effects_rare'] ?? []),

                'synergies' => json_encode($sup['synergies'] ?? []),
                'stacks_with_common' => json_encode($sup['stacks_with_common'] ?? []),

                'cost_relative' => (string) ($sup['cost_relative'] ?? 'media'),
                'shopping_list_grouping' => $sup['shopping_list_grouping'] ?? null,

                'advertencia_legal' => $sup['advertencia_legal'] ?? null,
                'tags' => json_encode($sup['tags'] ?? []),

                'confidence' => (string) ($sup['confidence'] ?? 'high'),
                'needs_daniel_validation' => (bool) ($sup['needs_daniel_validation'] ?? false),
                'needs_medical_professional_review' => (bool) ($sup['needs_medical_professional_review'] ?? false),

                'scientific_sources' => json_encode($sup['scientific_sources'] ?? []),

                'raw_data' => json_encode($sup, JSON_UNESCAPED_UNICODE),

                'version' => (int) ($sup['version'] ?? 1),
                'active' => (bool) ($sup['active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            $this->command?->warn('supplement-catalog-seed.json no contenía entries — skip.');
            return;
        }

        DB::connection('kb')
            ->table('supplement_catalog')
            ->upsert($rows, ['slug'], [
                'name', 'name_alternatives', 'scientific_name',
                'category', 'primary_action', 'type', 'blend_components',
                'dosis_recommended', 'timing_recommended', 'frequency',
                'macros_per_serving', 'serves_as_food',
                'applicable_gender', 'applicable_tier_min', 'applicable_objectives',
                'applicable_levels', 'applicable_age_range',
                'evidence_level', 'evidence_summary',
                'contraindications', 'medical_interactions',
                'side_effects_common', 'side_effects_rare',
                'synergies', 'stacks_with_common',
                'cost_relative', 'shopping_list_grouping',
                'advertencia_legal', 'tags',
                'confidence', 'needs_daniel_validation', 'needs_medical_professional_review',
                'scientific_sources', 'raw_data',
                'version', 'active', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' supplement_catalog.');
    }
}
