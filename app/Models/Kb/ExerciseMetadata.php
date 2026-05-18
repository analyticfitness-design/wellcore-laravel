<?php

declare(strict_types=1);

namespace App\Models\Kb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * wellcore_kb.exercise_metadata — catálogo enriquecido de ejercicios.
 *
 * Usado por Stage 3 COMPOSE para seleccionar ejercicios por:
 *  - muscle_primary (grupo muscular del día)
 *  - level_min vs nivel del cliente
 *  - equipment_required vs equipo disponible
 *  - contraindications vs injuries del cliente
 *
 * Conexión 'kb'. NO toca producción.
 */
class ExerciseMetadata extends Model
{
    protected $connection = 'kb';
    protected $table = 'exercise_metadata';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'equipment_required' => 'array',
            'equipment_substitutes' => 'array',
            'contraindications' => 'array',
            'coaching_cues' => 'array',
            'variations' => 'array',
        ];
    }

    public function scopeForMuscle(Builder $q, string $muscle): Builder
    {
        return $q->where('muscle_primary', $muscle);
    }

    public function scopeMaxLevel(Builder $q, string $level): Builder
    {
        // Solo ejercicios con level_min <= level del cliente.
        $order = ['principiante' => 1, 'intermedio' => 2, 'avanzado' => 3];
        $rank = $order[$level] ?? 3;
        $allowed = array_keys(array_filter($order, fn ($r) => $r <= $rank));
        return $q->whereIn('level_min', $allowed);
    }

    public function scopeCompound(Builder $q): Builder
    {
        return $q->where('compound_isolation', 'compound');
    }

    public function scopeIsolation(Builder $q): Builder
    {
        return $q->where('compound_isolation', 'isolation');
    }

    /**
     * Base URL canónica del catálogo v2 (220 GIFs curados).
     *
     * **Política**: SOLO este repo está permitido como fuente de GIFs.
     * El repo viejo `wellcore-exercise-gifs` (sin v2) tiene nombres inconsistentes
     * y archivos extras no validados — está deprecado para el motor v2.
     */
    public const GIF_REPO_BASE_URL = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/';

    /**
     * Construye el `gif_url` canónico apuntando al repo v2.
     *
     * LEY DURA: requiere `gif_filename` poblado y matching contra el repo v2.
     * Si está vacío, throws — no inventamos filename del alias porque puede no existir
     * en el repo. El selector debe filtrar registros sin filename ANTES de llamar acá.
     *
     * Helper estático: resolveGifUrl($filename) para componer URLs ad-hoc desde código
     * externo (export service, scripts) sin necesitar el modelo.
     */
    public function gifUrl(): string
    {
        $filename = $this->gif_filename;
        if ($filename === null || $filename === '') {
            throw new \RuntimeException(
                "ExerciseMetadata id={$this->id} alias='{$this->alias}' no tiene gif_filename. "
                . 'No se puede construir gif_url. Agregalo manualmente o corré `php artisan kb:reconcile-gifs --force`.'
            );
        }
        return self::resolveGifUrl($filename);
    }

    public static function resolveGifUrl(string $filename): string
    {
        return self::GIF_REPO_BASE_URL . ltrim($filename, '/');
    }
}
