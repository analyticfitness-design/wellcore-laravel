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

        // Target de volumen por nivel (MD 20 §881-885):
        //   principiante: 5-7 ejercicios (2 compounds + 3-5 isolations)
        //   intermedio: 6-9 ejercicios (2-3 compounds + 4-6 isolations)
        //   avanzado: 7-10 ejercicios (2-3 compounds + 5-7 isolations)
        $level = $profile->level ?? 'intermedio';
        [$compoundsTarget, $isolationsTarget] = match ($level) {
            'principiante' => [2, 4],   // 6 total
            'avanzado'     => [3, 6],   // 9 total
            default        => [2, 5],   // 7 total (intermedio)
        };

        // 1 compuesto principal por cada target muscular hasta `compoundsTarget`.
        $compounds = $this->fetchByMuscleTargets($day->muscleTargets, $profile, $equipmentAvailable, 'compound');
        $compoundCount = 0;

        // Primera ronda: 1 compound por muscle target.
        foreach ($day->muscleTargets as $muscle) {
            if ($compoundCount >= $compoundsTarget) {
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

        // Segunda ronda compound: si quedan slots, agregar más compounds de los targets.
        if ($compoundCount < $compoundsTarget) {
            foreach ($compounds as $e) {
                if ($compoundCount >= $compoundsTarget) {
                    break;
                }
                if (! isset($usedIds[$e->id])) {
                    $selected[] = $e;
                    $usedIds[$e->id] = true;
                    $compoundCount++;
                }
            }
        }

        // Isolations / accesorios cubriendo el resto.
        $isolations = $this->fetchByMuscleTargets($day->muscleTargets, $profile, $equipmentAvailable, 'isolation');

        // Primera ronda: 1-2 isolations por muscle target.
        $perMuscleTarget = max(1, (int) ceil($isolationsTarget / max(count($day->muscleTargets), 1)));
        $perMuscleCount = [];
        foreach ($day->muscleTargets as $muscle) {
            $perMuscleCount[$muscle] = 0;
        }
        foreach ($isolations as $candidate) {
            if (count($selected) >= ($compoundsTarget + $isolationsTarget)) {
                break;
            }
            if (isset($usedIds[$candidate->id])) {
                continue;
            }
            $muscle = $candidate->muscle_primary;
            if (in_array($muscle, $day->muscleTargets, true) && ($perMuscleCount[$muscle] ?? 0) < $perMuscleTarget) {
                $selected[] = $candidate;
                $usedIds[$candidate->id] = true;
                $perMuscleCount[$muscle]++;
            }
        }

        // Fallback: si todavía hay slots, agregar cualquier isolation restante.
        if (count($selected) < ($compoundsTarget + $isolationsTarget)) {
            foreach ($isolations as $e) {
                if (count($selected) >= ($compoundsTarget + $isolationsTarget)) {
                    break;
                }
                if (! isset($usedIds[$e->id])) {
                    $selected[] = $e;
                    $usedIds[$e->id] = true;
                }
            }
        }

        // Fallback final: si no llegamos a mínimo 4, agregar más compounds.
        if (count($selected) < 4) {
            foreach ($compounds as $e) {
                if (count($selected) >= 5) {
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

        // LEY DURA: solo ejercicios con gif_filename real (matching contra repo v2).
        // Si gif_filename está vacío/null, ExerciseMetadata::gifUrl() cae al fallback
        // "{alias}.gif" que probablemente NO existe en el repo → ExerciseGifFromV2RepoValidator
        // bloquea el plan entero con severity=error.
        // Filtramos en el origen: nunca devolver ejercicios sin filename canónico.
        $excludeStatuses = ['broken', 'missing'];

        // Búsqueda primaria por muscle_primary.
        $primary = ExerciseMetadata::query()
            ->whereIn('muscle_primary', $muscles)
            ->where('compound_isolation', $compoundIsolation)
            ->whereNotNull('gif_filename')
            ->where('gif_filename', '!=', '')
            ->whereNotIn('gif_url_status', $excludeStatuses)
            ->maxLevel($level)
            ->orderBy('id')
            ->get();

        // Fallback: muscle_secondary LIKE %muscle% (muchos ejercicios de glúteo viven ahí).
        // Solo agregamos lo que primary no cubrió.
        $primaryIds = $primary->pluck('id')->all();
        $secondaryQuery = ExerciseMetadata::query()
            ->where('compound_isolation', $compoundIsolation)
            ->whereNotNull('gif_filename')
            ->where('gif_filename', '!=', '')
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
