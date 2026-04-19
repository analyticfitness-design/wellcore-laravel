<?php

namespace App\Services;

use App\Models\EjercicioFitcron;
use Illuminate\Support\Facades\Cache;

class ExerciseMediaService
{
    /**
     * Enrich exercises array in-place with gif_url from ejercicios_fitcron.
     * Only sets gif_url when the exercise doesn't already have one.
     */
    public function enrichWithMedia(array &$exercises): void
    {
        if (empty($exercises)) {
            return;
        }

        $gifMap = $this->getGifMap();
        if (empty($gifMap)) {
            return;
        }

        foreach ($exercises as &$ex) {
            if (! is_array($ex) || ! empty($ex['gif_url'])) {
                continue;
            }

            $name = $ex['nombre'] ?? $ex['name'] ?? '';
            if (! $name) {
                continue;
            }

            $norm = $this->normalize($name);

            // Exact match
            if (isset($gifMap[$norm])) {
                $ex['gif_url'] = $gifMap[$norm];
                continue;
            }

            // Partial match — plan name starts with DB name or vice versa
            foreach ($gifMap as $dbNorm => $url) {
                if ($this->partialMatch($norm, $dbNorm)) {
                    $ex['gif_url'] = $url;
                    break;
                }
            }
        }
        unset($ex);
    }

    /**
     * Build and cache the full exercise-name → gif_url map from DB.
     */
    private function getGifMap(): array
    {
        return Cache::remember('exercise_gif_map_v2', 600, function () {
            $rows = EjercicioFitcron::query()
                ->where(function ($q) {
                    $q->where('descargado', true)->whereNotNull('gif_filename');
                })
                ->orWhereNotNull('gif_url')
                ->get(['nombre', 'gif_filename', 'gif_url']);

            $map = [];
            foreach ($rows as $e) {
                if (! $e->nombre) {
                    continue;
                }
                $norm = $this->normalize($e->nombre);
                // Prefer locally served GIF over CDN URL
                $url = $e->gif_filename
                    ? '/ejercicios/'.$e->gif_filename
                    : $e->gif_url;
                if ($url && ! isset($map[$norm])) {
                    $map[$norm] = $url;
                }
            }

            return $map;
        });
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');

        return strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u', 'à' => 'a', 'è' => 'e', 'ì' => 'i',
            'ò' => 'o', 'ù' => 'u',
        ]);
    }

    /**
     * True if the plan exercise name and DB exercise name overlap enough to be the same movement.
     * Uses prefix match (≥12 chars) to avoid false positives on short names.
     */
    private function partialMatch(string $planNorm, string $dbNorm): bool
    {
        $minLen = 12;

        if (strlen($dbNorm) >= $minLen && str_starts_with($planNorm, $dbNorm)) {
            return true;
        }

        if (strlen($planNorm) >= $minLen && str_starts_with($dbNorm, $planNorm)) {
            return true;
        }

        // One contains the other (both must be ≥8 chars to avoid "curl" matching "curl femoral")
        if (strlen($dbNorm) >= 8 && strlen($planNorm) >= 8) {
            if (str_contains($planNorm, $dbNorm) || str_contains($dbNorm, $planNorm)) {
                return true;
            }
        }

        return false;
    }
}
