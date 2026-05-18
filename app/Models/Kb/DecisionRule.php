<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * wellcore_kb.decision_rules — Stage 2 SELECT del motor v2.
 *
 * Cada rule: input pattern (when_json) → metodología recomendada con confidence.
 * Conexión 'kb'. NO toca producción.
 */
class DecisionRule extends Model
{
    protected $connection = 'kb';
    protected $table = 'decision_rules';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'when_json' => 'array',
            'confidence' => 'float',
            'times_fired' => 'integer',
        ];
    }

    public function methodology(): BelongsTo
    {
        return $this->belongsTo(Methodology::class, 'then_methodology_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeForVertical(Builder $q, string $vertical): Builder
    {
        return $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(when_json, '$.vertical')) = ?", [$vertical]);
    }
}
