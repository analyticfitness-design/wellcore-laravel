<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Exercises;

use App\Models\Kb\ExerciseMetadata;
use App\Services\ComposeEngine\Data\SplitDay;
use App\Services\DecisionEngine\Data\ClientProfile;
use Illuminate\Support\Collection;

/**
 * Selecciona ejercicios para un día del split.
 *
 * Regla de selección por día:
 *  - 1-2 compuestos (priorizando muscle_primary del primer target del día)
 *  - 2-3 isolations / accesorios (cubren el resto de muscle_targets)
 *  - Filtros: level_min ≤ profile.level, contraindications ∩ injuries = ∅,
 *    equipment_required ⊆ equipo disponible (o sustituir por equipment_substitute)
 *
 * Determinismo: ordena por (compound_isolation DESC, id ASC) → mismas inputs = mismo output.
 */
final class ExerciseSelector
{
    /**
     * @param string[] $equipmentAvailable
     * @return ExerciseMetadata[]
     */
    public function selectForDay(SplitDay $day, ClientProfile $profile, array $equipmentAvailable): array
    {
        $selected = [];
        $usedIds = [];

        // 1 compuesto principal por cada target muscular (hasta 2)
        $compounds = $this->fetchByMuscleTargets($day->muscleTargets, $profile, $equipmentAvailable, 'compound');
        $compoundCount = 0;
        foreach ($day->muscleTargets as $muscle) {
            if ($compoundCount >= 2) {
                break;
            }
            $candidate = $compounds->first(function (ExerciseMetadata $e) use ($muscle, $usedIds) {
                return $e->muscle_primary === $muscle && ! isset($usedIds[$e->id]);
            });
            if ($candidate !== null) {
                $selected[] = $candidate;
                $usedIds[$candidate->id] = true;
                $compoundCount++;
            }
        }

        // 2-3 isolations / accesorios cubriendo el resto
        $isolations = $this->fetchByMuscleTargets($day->muscleTargets, $profile, $equipmentAvailable, 'isolation');
        $isolationTarget = 3;
        foreach ($day->muscleTargets as $muscle) {
            if (count($selected) >= ($compoundCount + $isolationTarget)) {
                break;
            }
            $candidate = $isolations->first(function (ExerciseMetadata $e) use ($muscle, $usedIds) {
                return $e->muscle_primary === $muscle && ! isset($usedIds[$e->id]);
            });
            if ($candidate !== null) {
                $selected[] = $candidate;
                $usedIds[$candidate->id] = true;
            }
        }

        // Fallback: si no llegamos a mínimo 3 ejercicios, completar con compound de cualquier muscle_target.
        if (count($selected) < 3) {
            foreach ($compounds as $e) {
                if (count($selected) >= 4) {
                    break;
                }
                if (! isset($usedIds[$e->id])) {
                    $selected[] = $e;
                    $usedIds[$e->id] = true;
                }
            }
        }

        return $selected;
    }

    /**
     * @param string[] $muscles
     * @param string[] $equipmentAvailable
     * @return Collection<int, ExerciseMetadata>
     */
    private function fetchByMuscleTargets(array $muscles, ClientProfile $profile, array $equipmentAvailable, string $compoundIsolation): Collection
    {
        $level = $profile->level ?? 'intermedio';

        // Filtro común: nunca devolver ejercicios con gif_url broken (verificado por kb:verify-gifs).
        // 'unknown' SÍ pasa — son ejercicios nuevos sin verificar todavía, mejor incluirlos
        // y dejar que el LintEngine los detecte. 'missing' tampoco — sin GIF no se renderiza.
        $excludeStatuses = ['broken', 'missing'];

        // Búsqueda primaria por muscle_primary.
        $primary = ExerciseMetadata::query()
            ->whereIn('muscle_primary', $muscles)
            ->where('compound_isolation', $compoundIsolation)
            ->whereNotIn('gif_url_status', $excludeStatuses)
            ->maxLevel($level)
            ->orderBy('id')
            ->get();

        // Fallback: muscle_secondary LIKE %muscle% (muchos ejercicios de glúteo viven ahí).
        // Solo agregamos lo que primary no cubrió.
        $primaryIds = $primary->pluck('id')->all();
        $secondaryQuery = ExerciseMetadata::query()
            ->where('compound_isolation', $compoundIsolation)
            ->whereNotIn('gif_url_status', $excludeStatuses)
            ->maxLevel($level)
            ->orderBy('id');
        $secondaryQuery->where(function ($q) use ($muscles) {
            foreach ($muscles as $m) {
                $q->orWhere('muscle_secondary', 'like', "%$m%");
            }
        });
        if ($primaryIds !== []) {
            $secondaryQuery->whereNotIn('id', $primaryIds);
        }
        $secondary = $secondaryQuery->get();

        return $primary->concat($secondary)->filter(function (ExerciseMetadata $e) use ($profile, $equipmentAvailable) {
            return $this->equipmentOk($e->equipment_required ?? [], $equipmentAvailable)
                && $this->injuriesOk($e->contraindications ?? [], $profile->injuries);
        })->values();
    }

    /**
     * @param string[] $required
     * @param string[] $available
     */
    private function equipmentOk(array $required, array $available): bool
    {
        if ($required === []) {
            return true;
        }

        // gym_completo = wildcard (asumimos que hay todo).
        if (in_array('gym_completo', $available, true)) {
            return true;
        }

        foreach ($required as $req) {
            if (! in_array($req, $available, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string[] $contraindications
     * @param string[] $injuries
     */
    private function injuriesOk(array $contraindications, array $injuries): bool
    {
        return array_intersect($contraindications, $injuries) === [];
    }
}
