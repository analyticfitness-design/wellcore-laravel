<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.composed_plans — Stage 5 PERSIST audit trail.
 *
 * Snapshot KB-local de cada plan generado. NO es la tabla de producción —
 * para subir a producción el JSON se exporta manualmente desde acá.
 */
class ComposedPlan extends Model
{
    protected $connection = 'kb';
    protected $table = 'composed_plans';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'profile_json' => 'array',
            'lint_result_pre_json' => 'array',
            'lint_result_post_json' => 'array',
            'fixes_applied_json' => 'array',
            'violations_before' => 'integer',
            'violations_after' => 'integer',
            'compose_duration_ms' => 'float',
            'lint_duration_ms' => 'float',
        ];
    }

    public function planJson(): array
    {
        return json_decode((string) $this->plan_json, true) ?? [];
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }

    public function scopeForMethodology(Builder $q, string $slug): Builder
    {
        return $q->where('methodology_slug', $slug);
    }
}
