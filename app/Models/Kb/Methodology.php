<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.methodologies — catálogo de metodologías del motor v2.
 *
 * Schema legacy: slug, name, vertical, description, target_*, periodization_pattern.
 * Conexión 'kb'.
 */
class Methodology extends Model
{
    protected $connection = 'kb';
    protected $table = 'methodologies';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'periodization_pattern' => 'array',
            'target_days_min' => 'integer',
            'target_days_max' => 'integer',
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

    /** Methodologies cuyo rango target_days_min..max incluye $days. */
    public function scopeForDays(Builder $q, int $days): Builder
    {
        return $q->where('target_days_min', '<=', $days)
            ->where('target_days_max', '>=', $days);
    }
}
