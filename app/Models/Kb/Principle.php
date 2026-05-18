<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.principles — principios de coaching reutilizables.
 *
 * 15 entries seedeadas (7 entrenamiento, 4 nutrición, 1 suplementación, 3 hábitos).
 * Inyectados en notas_coach / tips[] por PrincipleInjector (Sprint 32).
 */
class Principle extends Model
{
    protected $connection = 'kb';
    protected $table = 'principles';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'evidence_level' => 'string',
        ];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeForVertical(Builder $q, string $vertical): Builder
    {
        return $q->where('vertical', $vertical);
    }

    /**
     * Filtra principles cuyo array `tags` contenga el tag dado.
     * Usa JSON_CONTAINS para evitar full-scan + strpos.
     */
    public function scopeByTag(Builder $q, string $tag): Builder
    {
        return $q->whereJsonContains('tags', $tag);
    }

    /** Filtra principles con tag 'fundamental' — usado por PrincipleInjector. */
    public function scopeFundamental(Builder $q): Builder
    {
        return $q->whereJsonContains('tags', 'fundamental');
    }

    /** Filtra por evidence_level (muy_alta|alta|moderada|limitada|anecdotica). */
    public function scopeByEvidence(Builder $q, string $level): Builder
    {
        return $q->where('evidence_level', $level);
    }
}
