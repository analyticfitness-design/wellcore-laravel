<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed de las 4 sub-tablas hormonal_* desde docs/audit-motor-v2/hormonal-protocols-seed.json.
 *
 * Sub-tablas pobladas (todas con active=false por default — requieren validación médica antes de habilitar):
 *   - hormonal_compounds
 *   - hormonal_protocol_templates
 *   - ciclo_menstrual_fases
 *   - bloodwork_panels
 *
 * Idempotente: upsert por slug en cada sub-tabla.
 */
final class HormonalProtocolsSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('docs/audit-motor-v2/hormonal-protocols-seed.json');

        if (! is_file($jsonPath)) {
            $this->command?->warn("hormonal-protocols-seed.json no encontrado — skip.");
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data)) {
            $this->command?->warn('hormonal-protocols-seed.json malformado — skip.');
            return;
        }

        $now = now()->toDateTimeString();

        $this->seedHormonalCompounds($data['hormonal_compounds_catalog'] ?? [], $now);
        $this->seedHormonalProtocolTemplates($data['hormonal_protocols_templates'] ?? [], $now);
        $this->seedCicloMenstrualFases($data['ciclo_menstrual_fases'] ?? [], $now);
        $this->seedBloodworkPanels($data['bloodwork_panels'] ?? [], $now);
    }

    private function seedHormonalCompounds(array $entries, string $now): void
    {
        $rows = [];
        foreach ($entries as $c) {
            $rows[] = [
                'slug' => (string) ($c['slug'] ?? ''),
                'name' => (string) ($c['name'] ?? ''),
                'name_alternatives' => json_encode($c['name_alternatives'] ?? []),
                'scientific_name' => $c['scientific_name'] ?? null,
                'category' => (string) ($c['category'] ?? 'otro'),
                'primary_action' => (string) ($c['primary_action'] ?? ''),
                'use_case_primary' => (string) ($c['use_case_primary'] ?? ''),
                'use_case_secondary' => json_encode($c['use_case_secondary'] ?? []),
                'via_administracion' => $c['via_administracion'] ?? null,
                'via_options_clinicas' => json_encode($c['via_options_clinicas'] ?? []),
                'farmacocinetica' => json_encode($c['farmacocinetica'] ?? new \stdClass()),
                'dosis_rango_clinico_trt' => json_encode($c['dosis_rango_clinico_trt'] ?? new \stdClass()),
                'dosis_rango_ergogenico_off_label' => json_encode($c['dosis_rango_ergogenico_off_label'] ?? new \stdClass()),
                'objetivo_serico_testosterona_total' => json_encode($c['objetivo_serico_testosterona_total'] ?? new \stdClass()),
                'labs_monitoreo_obligatorios' => json_encode($c['labs_monitoreo_obligatorios'] ?? []),
                'lab_frecuencia' => $c['lab_frecuencia'] ?? null,
                'estradiol_management' => json_encode($c['estradiol_management'] ?? new \stdClass()),
                'hematocrit_management' => json_encode($c['hematocrit_management'] ?? new \stdClass()),
                'efectos_terapeuticos_esperados' => json_encode($c['efectos_terapeuticos_esperados'] ?? []),
                'efectos_secundarios_comunes' => json_encode($c['efectos_secundarios_comunes'] ?? []),
                'efectos_secundarios_raros' => json_encode($c['efectos_secundarios_raros'] ?? []),
                'contraindications_absolutas' => json_encode($c['contraindications_absolutas'] ?? []),
                'contraindications_relativas' => json_encode($c['contraindications_relativas'] ?? []),
                'medical_interactions' => json_encode($c['medical_interactions'] ?? []),
                'señales_alerta_emergencia_medica' => json_encode($c['señales_alerta_emergencia_medica'] ?? []),
                'applicable_gender' => json_encode($c['applicable_gender'] ?? []),
                'applicable_age_range_clinico' => json_encode($c['applicable_age_range_clinico'] ?? []),
                'applicable_age_range_ergogenico_no_recomendado_under' => isset($c['applicable_age_range_ergogenico_no_recomendado_under'])
                    ? (int) $c['applicable_age_range_ergogenico_no_recomendado_under'] : null,
                'evidence_level_terapeutico' => (string) ($c['evidence_level_terapeutico'] ?? 'moderada'),
                'evidence_level_ergogenico' => (string) ($c['evidence_level_ergogenico'] ?? 'moderada'),
                'evidence_summary' => $c['evidence_summary'] ?? null,
                'scientific_sources' => json_encode($c['scientific_sources'] ?? []),
                'legal_framing' => (string) ($c['legal_framing'] ?? ''),
                'legal_status_colombia' => $c['legal_status_colombia'] ?? null,
                'legal_status_otros_paises_latam' => $c['legal_status_otros_paises_latam'] ?? null,
                'confidence' => (string) ($c['confidence'] ?? 'moderate'),
                'needs_endocrinologist_validation' => (bool) ($c['needs_endocrinologist_validation'] ?? true),
                'needs_daniel_validation' => (bool) ($c['needs_daniel_validation'] ?? true),
                'tags' => json_encode($c['tags'] ?? []),
                'raw_data' => json_encode($c, JSON_UNESCAPED_UNICODE),
                'version' => (int) ($c['version'] ?? 1),
                'active' => (bool) ($c['active'] ?? false), // default false hasta validación médica
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            DB::connection('kb')->table('hormonal_compounds')->upsert($rows, ['slug'], array_diff(array_keys($rows[0]), ['slug', 'created_at']));
            $this->command?->info('Seeded ' . count($rows) . ' hormonal_compounds.');
        }
    }

    private function seedHormonalProtocolTemplates(array $entries, string $now): void
    {
        $rows = [];
        foreach ($entries as $p) {
            $rows[] = [
                'slug' => (string) ($p['slug'] ?? ''),
                'name' => (string) ($p['name'] ?? ''),
                'objective' => $p['objective'] ?? null,
                'applicable_use_case' => $p['applicable_use_case'] ?? null,
                'duration_weeks' => isset($p['duration_weeks']) ? (int) $p['duration_weeks'] : null,
                'extendible_to_long_term' => (bool) ($p['extendible_to_long_term'] ?? false),
                'compounds_combination' => json_encode($p['compounds_combination'] ?? []),
                'phases' => json_encode($p['phases'] ?? []),
                'pct_post_protocolo' => $p['pct_post_protocolo'] ?? null,
                'labs_required_baseline' => json_encode($p['labs_required_baseline'] ?? []),
                'labs_schedule' => json_encode($p['labs_schedule'] ?? new \stdClass()),
                'señales_emergencia' => $p['señales_emergencia'] ?? null,
                'applicable_gender' => json_encode($p['applicable_gender'] ?? []),
                'applicable_age_range' => json_encode($p['applicable_age_range'] ?? []),
                'applicable_tier_min' => (string) ($p['applicable_tier_min'] ?? 'elite'),
                'evidence_level' => (string) ($p['evidence_level'] ?? 'moderada'),
                'scientific_sources' => json_encode($p['scientific_sources'] ?? []),
                'legal_framing' => (string) ($p['legal_framing'] ?? ''),
                'confidence' => (string) ($p['confidence'] ?? 'moderate'),
                'needs_endocrinologist_validation' => (bool) ($p['needs_endocrinologist_validation'] ?? true),
                'needs_daniel_validation' => (bool) ($p['needs_daniel_validation'] ?? true),
                'needs_legal_review_before_seed' => (bool) ($p['needs_legal_review_before_seed'] ?? true),
                'raw_data' => json_encode($p, JSON_UNESCAPED_UNICODE),
                'version' => (int) ($p['version'] ?? 1),
                'active' => (bool) ($p['active'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            DB::connection('kb')->table('hormonal_protocol_templates')->upsert($rows, ['slug'], array_diff(array_keys($rows[0]), ['slug', 'created_at']));
            $this->command?->info('Seeded ' . count($rows) . ' hormonal_protocol_templates.');
        }
    }

    private function seedCicloMenstrualFases(array $entries, string $now): void
    {
        $rows = [];
        foreach ($entries as $f) {
            $rows[] = [
                'slug' => (string) ($f['slug'] ?? ''),
                'name' => (string) ($f['name'] ?? ''),
                'alternative_names' => json_encode($f['alternative_names'] ?? []),
                'ciclo_dias_tipico' => $f['ciclo_dias_tipico'] ?? null,
                'ciclo_dias_rango' => $f['ciclo_dias_rango'] ?? null,
                'ciclo_assumes_dias_totales' => (int) ($f['ciclo_assumes_dias_totales'] ?? 28),
                'hormonas_dominantes' => json_encode($f['hormonas_dominantes'] ?? new \stdClass()),
                'sintomas_tipicos' => json_encode($f['sintomas_tipicos'] ?? []),
                'ajustes_entrenamiento' => json_encode($f['ajustes_entrenamiento'] ?? new \stdClass()),
                'ajustes_nutricion' => json_encode($f['ajustes_nutricion'] ?? new \stdClass()),
                'ajustes_sueño_recuperacion' => json_encode($f['ajustes_sueño_recuperacion'] ?? new \stdClass()),
                'considerations_birth_control' => json_encode($f['considerations_birth_control'] ?? new \stdClass()),
                'scientific_sources' => json_encode($f['scientific_sources'] ?? []),
                'legal_framing' => (string) ($f['legal_framing'] ?? ''),
                'applicable_age_range' => json_encode($f['applicable_age_range'] ?? []),
                'applicable_to_postmenopausal' => (bool) ($f['applicable_to_postmenopausal'] ?? false),
                'applicable_to_pregnant' => (bool) ($f['applicable_to_pregnant'] ?? false),
                'confidence' => (string) ($f['confidence'] ?? 'moderate'),
                'needs_gynecologist_validation' => (bool) ($f['needs_gynecologist_validation'] ?? true),
                'needs_daniel_validation' => (bool) ($f['needs_daniel_validation'] ?? true),
                'tags' => json_encode($f['tags'] ?? []),
                'raw_data' => json_encode($f, JSON_UNESCAPED_UNICODE),
                'version' => (int) ($f['version'] ?? 1),
                'active' => (bool) ($f['active'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            DB::connection('kb')->table('ciclo_menstrual_fases')->upsert($rows, ['slug'], array_diff(array_keys($rows[0]), ['slug', 'created_at']));
            $this->command?->info('Seeded ' . count($rows) . ' ciclo_menstrual_fases.');
        }
    }

    private function seedBloodworkPanels(array $entries, string $now): void
    {
        $rows = [];
        foreach ($entries as $b) {
            $rows[] = [
                'slug' => (string) ($b['slug'] ?? ''),
                'name' => (string) ($b['name'] ?? ''),
                'applicable_to' => $b['applicable_to'] ?? null,
                'tests_incluidos' => json_encode($b['tests_incluidos'] ?? []),
                'frecuencia_recomendada' => $b['frecuencia_recomendada'] ?? null,
                'costo_estimado_colombia_cop' => $b['costo_estimado_colombia_cop'] ?? null,
                'costo_estimado_mexico_mxn' => $b['costo_estimado_mexico_mxn'] ?? null,
                'laboratorios_recomendados_co' => json_encode($b['laboratorios_recomendados_co'] ?? []),
                'interpretation_general' => $b['interpretation_general'] ?? null,
                'scientific_sources' => json_encode($b['scientific_sources'] ?? []),
                'legal_framing' => (string) ($b['legal_framing'] ?? ''),
                'applicable_gender' => json_encode($b['applicable_gender'] ?? []),
                'applicable_age_range' => json_encode($b['applicable_age_range'] ?? []),
                'confidence' => (string) ($b['confidence'] ?? 'high'),
                'needs_endocrinologist_validation' => (bool) ($b['needs_endocrinologist_validation'] ?? false),
                'needs_daniel_validation' => (bool) ($b['needs_daniel_validation'] ?? false),
                'raw_data' => json_encode($b, JSON_UNESCAPED_UNICODE),
                'version' => (int) ($b['version'] ?? 1),
                'active' => (bool) ($b['active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            DB::connection('kb')->table('bloodwork_panels')->upsert($rows, ['slug'], array_diff(array_keys($rows[0]), ['slug', 'created_at']));
            $this->command?->info('Seeded ' . count($rows) . ' bloodwork_panels.');
        }
    }
}
