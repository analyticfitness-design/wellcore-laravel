<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed de nutrition_foods desde docs/audit-motor-v2/nutrition-foods-seed.json.
 *
 * Idempotente: upsert por slug.
 * Patrón: lee JSON externo (fuente de verdad), mapea a columnas, conserva entry
 * completo en raw_data por si motor v2 necesita campos no normalizados.
 */
final class NutritionFoodsSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('docs/audit-motor-v2/nutrition-foods-seed.json');

        if (! is_file($jsonPath)) {
            $this->command?->warn("nutrition-foods-seed.json no encontrado en $jsonPath — skip.");
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data) || ! isset($data['foods']) || ! is_array($data['foods'])) {
            $this->command?->warn('nutrition-foods-seed.json sin clave "foods" o malformado — skip.');
            return;
        }

        $now = now()->toDateTimeString();
        $rows = [];

        foreach ($data['foods'] as $food) {
            $macros = $food['macros_per_100g_raw'] ?? [];

            $rows[] = [
                'slug' => (string) ($food['slug'] ?? ''),
                'name' => (string) ($food['name'] ?? ''),
                'name_alternatives' => json_encode($food['name_alternatives'] ?? []),

                'category' => (string) ($food['category'] ?? 'sin_clasificar'),
                'subcategory' => $food['subcategory'] ?? null,

                'protein_g' => (float) ($macros['protein_g'] ?? 0),
                'carbs_g' => (float) ($macros['carbs_g'] ?? 0),
                'fat_g' => (float) ($macros['fat_g'] ?? 0),
                'fiber_g' => isset($macros['fiber_g']) ? (float) $macros['fiber_g'] : null,
                'kcal' => (float) ($macros['kcal'] ?? 0),

                'unit_default' => (string) ($food['unit_default'] ?? 'g'),
                'unit_options' => json_encode($food['unit_options'] ?? []),

                'portion_typical' => json_encode($food['portion_typical'] ?? new \stdClass()),
                'preparation_notes' => $food['preparation_notes'] ?? null,

                'availability_country' => json_encode($food['availability_country'] ?? []),
                'alternatives_protein_equivalent' => json_encode($food['alternatives_protein_equivalent'] ?? []),

                'is_vegetarian' => (bool) ($food['dietary_flags']['is_vegetarian'] ?? false),
                'is_vegan' => (bool) ($food['dietary_flags']['is_vegan'] ?? false),
                'is_gluten_free' => (bool) ($food['dietary_flags']['is_gluten_free'] ?? false),
                'is_lactose_free' => (bool) ($food['dietary_flags']['is_lactose_free'] ?? false),
                'is_keto_friendly' => (bool) ($food['dietary_flags']['is_keto_friendly'] ?? false),
                'is_paleo_friendly' => (bool) ($food['dietary_flags']['is_paleo_friendly'] ?? false),
                'common_allergen' => $food['dietary_flags']['common_allergen'] ?? null,

                'shopping_list_grouping' => (string) ($food['shopping_list_grouping'] ?? 'otros'),
                'shopping_category_ui_v1' => $food['shopping_category_ui_v1'] ?? null,
                'icon_emoji' => $food['icon_emoji'] ?? null,

                'cost_relative' => (string) ($food['cost_relative'] ?? 'media'),
                'glycemic_index' => isset($food['glycemic_index']) && is_numeric($food['glycemic_index'])
                    ? (int) $food['glycemic_index'] : null,

                'confidence' => (string) ($food['confidence'] ?? 'high'),
                'needs_daniel_validation' => (bool) ($food['needs_daniel_validation'] ?? false),

                'scientific_sources' => json_encode($food['scientific_sources'] ?? []),
                'tags' => json_encode($food['tags'] ?? []),

                'raw_data' => json_encode($food, JSON_UNESCAPED_UNICODE),

                'version' => (int) ($food['version'] ?? 1),
                'active' => (bool) ($food['active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            $this->command?->warn('nutrition-foods-seed.json no contenía entries — skip.');
            return;
        }

        DB::connection('kb')
            ->table('nutrition_foods')
            ->upsert($rows, ['slug'], [
                'name', 'name_alternatives', 'category', 'subcategory',
                'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'kcal',
                'unit_default', 'unit_options', 'portion_typical', 'preparation_notes',
                'availability_country', 'alternatives_protein_equivalent',
                'is_vegetarian', 'is_vegan', 'is_gluten_free', 'is_lactose_free',
                'is_keto_friendly', 'is_paleo_friendly', 'common_allergen',
                'shopping_list_grouping', 'shopping_category_ui_v1', 'icon_emoji',
                'cost_relative', 'glycemic_index',
                'confidence', 'needs_daniel_validation',
                'scientific_sources', 'tags', 'raw_data',
                'version', 'active', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' nutrition_foods.');
    }
}
