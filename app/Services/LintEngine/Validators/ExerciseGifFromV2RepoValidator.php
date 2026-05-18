<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Models\Kb\ExerciseMetadata;
use App\Services\LintEngine\Data\LintContext;
use Illuminate\Support\Facades\Cache;

/**
 * LEY DURA del motor v2 (autoritativa Daniel · 2026-05-18):
 *
 * Todos los ejercicios usados en planes de entrenamiento DEBEN tener `gif_url`
 * apuntando al repo oficial `analyticfitness-design/wellcore-exercise-gifs-v2`
 * Y su `gif_filename` debe estar registrado en wellcore_kb.exercise_metadata.
 *
 * Si un plan contiene un ejercicio con:
 *   - gif_url fuera del repo v2 (otro repo / dominio / null)
 *   - gif_filename que no existe en exercise_metadata
 *   - gif_url cuyo basename no matchea ningún exercise_metadata.gif_filename
 *
 * → severity=error, BLOQUEA PERSIST.
 *
 * No hay autofix — el coach/motor debe corregir el ejercicio o agregarlo al repo
 * (pull request + kb:import-exercise-catalog).
 *
 * Cache: lista de filenames válidos cacheada 5 minutos para no golpear DB en cada
 * ejercicio del plan (un plan típico tiene ~17 ejercicios).
 */
final class ExerciseGifFromV2RepoValidator extends BaseValidator
{
    /** URL base obligatoria. Cualquier gif_url debe empezar por esto. */
    private const REQUIRED_URL_PREFIX = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/';

    /** Cache TTL para la lista de filenames canónicos. */
    private const CACHE_TTL_SECONDS = 300;

    /** Cache key. */
    private const CACHE_KEY = 'lint.exercise_v2_repo.valid_filenames';

    public function name(): string
    {
        return 'exercise_gif_from_v2_repo';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;

        // Solo aplica a plan_type=entrenamiento
        if (($plan['plan_type'] ?? null) !== 'entrenamiento') {
            return [];
        }

        $validFilenames = $this->loadValidFilenames();
        if ($validFilenames === []) {
            // Catálogo vacío → no podemos validar. Emitir warning meta y skipear.
            return [$this->makeViolation(
                $ctx,
                '$',
                'exercise_metadata está vacío. Imposible validar ejercicios contra repo v2. Correr `php artisan kb:import-exercise-catalog`.',
                ['catalogo_count' => 0],
            )];
        }

        $violations = [];
        $semanas = $plan['semanas'] ?? [];
        if (! is_array($semanas)) {
            return [];
        }

        foreach ($semanas as $iSem => $sem) {
            $dias = $sem['dias'] ?? [];
            if (! is_array($dias)) continue;

            foreach ($dias as $iDia => $dia) {
                $ejercicios = $dia['ejercicios'] ?? [];
                if (! is_array($ejercicios)) continue;

                foreach ($ejercicios as $iEj => $ej) {
                    $check = $this->checkExercise($ej, $validFilenames);
                    if ($check === null) continue;

                    $path = "\$.semanas[{$iSem}].dias[{$iDia}].ejercicios[{$iEj}]";
                    $violations[] = $this->makeViolation(
                        $ctx,
                        $path,
                        $check['message'],
                        $check['evidence'],
                    );
                }
            }
        }

        return $violations;
    }

    /**
     * Carga la lista canónica de gif_filename desde wellcore_kb.exercise_metadata.
     * Cacheada para no golpear DB en cada ejercicio.
     *
     * @return array<string,true> map gif_filename → true (lookup O(1))
     */
    private function loadValidFilenames(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return ExerciseMetadata::query()
                ->whereNotNull('gif_filename')
                ->where('gif_filename', '!=', '')
                ->where(function ($q) {
                    $q->whereNull('gif_url_status')->orWhere('gif_url_status', '!=', 'broken');
                })
                ->pluck('gif_filename')
                ->mapWithKeys(fn ($fn) => [(string) $fn => true])
                ->toArray();
        });
    }

    /**
     * Devuelve null si el ejercicio es válido. Si no, devuelve [message, evidence].
     *
     * @param array<string,mixed> $ej
     * @param array<string,true> $validFilenames
     * @return array{message: string, evidence: array<string,mixed>}|null
     */
    private function checkExercise(array $ej, array $validFilenames): ?array
    {
        $nombre = (string) ($ej['nombre'] ?? '(sin nombre)');
        $gifUrl = (string) ($ej['gif_url'] ?? '');

        if ($gifUrl === '') {
            return [
                'message' => "Ejercicio '{$nombre}' sin gif_url. Ley dura: todo ejercicio debe tener gif del repo wellcore-exercise-gifs-v2.",
                'evidence' => ['ejercicio' => $nombre, 'gif_url' => null],
            ];
        }

        if (! str_starts_with($gifUrl, self::REQUIRED_URL_PREFIX)) {
            return [
                'message' => "Ejercicio '{$nombre}' tiene gif_url fuera del repo oficial. Ley: solo URLs del repo wellcore-exercise-gifs-v2 main branch.",
                'evidence' => [
                    'ejercicio' => $nombre,
                    'gif_url_encontrado' => $gifUrl,
                    'prefijo_requerido' => self::REQUIRED_URL_PREFIX,
                ],
            ];
        }

        $filename = substr($gifUrl, strlen(self::REQUIRED_URL_PREFIX));
        if ($filename === '' || ! str_ends_with($filename, '.gif')) {
            return [
                'message' => "Ejercicio '{$nombre}' tiene path inválido tras el repo base. Debe terminar en .gif.",
                'evidence' => ['ejercicio' => $nombre, 'path' => $filename],
            ];
        }

        if (! isset($validFilenames[$filename])) {
            return [
                'message' => "Ejercicio '{$nombre}' apunta a '{$filename}' que NO está en wellcore_kb.exercise_metadata. El ejercicio no es canónico. Agregalo al repo + correr kb:import-exercise-catalog, o reemplazalo por un alias existente.",
                'evidence' => [
                    'ejercicio' => $nombre,
                    'filename' => $filename,
                    'catalogo_size' => count($validFilenames),
                ],
            ];
        }

        return null;
    }

    /**
     * Invalida el cache. Útil después de kb:import-exercise-catalog o
     * kb:clean-exercise-catalog para que el siguiente lint vea el catálogo nuevo.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
