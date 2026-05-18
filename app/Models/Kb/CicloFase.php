<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.ciclo_menstrual_fases — fases del ciclo + ajustes por fase.
 *
 * 5 fases naturales (folicular temprana/tardía, ovulación, lútea temprana/tardía)
 * + 2 templates (con/sin anticonceptivos hormonales).
 *
 * Usado por CycleModulationComposer (Sprint 19) para producir plan de adaptación.
 */
class CicloFase extends Model
{
    protected $connection = 'kb';
    protected $table = 'ciclo_menstrual_fases';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'alternative_names' => 'array',
            'hormonas_dominantes' => 'array',
            'sintomas_tipicos' => 'array',
            'ajustes_entrenamiento' => 'array',
            'ajustes_nutricion' => 'array',
            'ajustes_sueño_recuperacion' => 'array',
            'considerations_birth_control' => 'array',
            'scientific_sources' => 'array',
            'applicable_age_range' => 'array',
            'tags' => 'array',
            'raw_data' => 'array',
            'active' => 'boolean',
            'applicable_to_postmenopausal' => 'boolean',
            'applicable_to_pregnant' => 'boolean',
            'needs_gynecologist_validation' => 'boolean',
            'needs_daniel_validation' => 'boolean',
            'ciclo_assumes_dias_totales' => 'integer',
        ];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function scopeFasesNaturales(Builder $q): Builder
    {
        // Solo las 5 fases reales del ciclo (exclude templates)
        return $q->where('slug', 'like', 'fase-%');
    }
}
