<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.lint_rules — catálogo del linter del motor v2.
 *
 * Cada fila es una regla con tipo (schema | heuristic | external_head | sql | llm_review)
 * y check_definition_json con parámetros. El LintEngine las carga dinámicamente
 * y las pasa al validator correspondiente.
 *
 * Conexión: 'kb' (DB local wellcore_kb, NO la compartida wellcore_fitness).
 */
class LintRule extends Model
{
    protected $connection = 'kb';
    protected $table = 'lint_rules';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'check_definition_json' => 'array',
            'enabled' => 'boolean',
            'auto_fix_available' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $q): Builder
    {
        return $q->where('enabled', true);
    }

    /**
     * Filtrar por vertical (o rules globales sin vertical).
     */
    public function scopeForVertical(Builder $q, ?string $vertical): Builder
    {
        if ($vertical === null) {
            return $q;
        }
        return $q->where(function (Builder $sub) use ($vertical) {
            $sub->where('vertical', $vertical)->orWhereNull('vertical');
        });
    }

    public function scopeOfCheckType(Builder $q, string $checkType): Builder
    {
        return $q->where('check_type', $checkType);
    }
}
