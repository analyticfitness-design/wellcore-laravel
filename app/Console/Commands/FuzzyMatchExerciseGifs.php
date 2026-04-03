<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class FuzzyMatchExerciseGifs extends Command
{
    protected $signature = 'wellcore:fuzzy-match-exercise-gifs
        {--dry-run : Solo muestra matches sin actualizar}
        {--threshold=60 : Porcentaje mínimo de similitud (0-100)}';

    protected $description = 'Fuzzy-match ejercicios sin gif_filename contra los que sí tienen GIF y los actualiza.';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $dryRun    = (bool) $this->option('dry-run');

        $withGif    = $this->loadExercisesWithGif();
        $withoutGif = $this->loadExercisesWithoutGif();

        $this->line("Ejercicios sin GIF: " . count($withoutGif));
        $this->line("Analizando con threshold: {$threshold}%...");
        $this->newLine();

        $matches   = $this->findMatches($withoutGif, $withGif, $threshold);
        $noMatches = count($withoutGif) - count($matches);

        $this->printMatches($matches);

        if (!$dryRun && count($matches) > 0) {
            $this->applyMatches($matches);
        }

        $this->newLine();
        $this->line("Matches encontrados: " . count($matches));
        $this->line("Sin match (score < {$threshold}%): {$noMatches}");

        if ($dryRun) {
            $this->comment('[dry-run] No se realizaron cambios en la BD.');
        }

        return self::SUCCESS;
    }

    private function loadExercisesWithGif(): array
    {
        return DB::table('ejercicios_fitcron')
            ->whereNotNull('gif_filename')
            ->select('slug', 'nombre', 'gif_filename', 'sin_fondo_listo')
            ->get()
            ->toArray();
    }

    private function loadExercisesWithoutGif(): array
    {
        return DB::table('ejercicios_fitcron')
            ->whereNull('gif_filename')
            ->select('slug', 'nombre')
            ->get()
            ->toArray();
    }

    private function findMatches(array $withoutGif, array $withGif, int $threshold): array
    {
        $matches = [];

        foreach ($withoutGif as $target) {
            $normalizedTarget = $this->normalize($target->nombre);
            $bestScore        = 0;
            $bestSource       = null;

            foreach ($withGif as $source) {
                $score = $this->score($normalizedTarget, $this->normalize($source->nombre));

                if ($score > $bestScore) {
                    $bestScore  = $score;
                    $bestSource = $source;
                }
            }

            if ($bestScore >= $threshold && $bestSource !== null) {
                $matches[] = [
                    'target_slug'    => $target->slug,
                    'target_nombre'  => $target->nombre,
                    'source_nombre'  => $bestSource->nombre,
                    'gif_filename'   => $bestSource->gif_filename,
                    'sin_fondo_listo' => $bestSource->sin_fondo_listo,
                    'score'          => $bestScore,
                ];
            }
        }

        return $matches;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower($name);
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = preg_replace('/[^a-z0-9 ]/', '', $name);

        return trim($name);
    }

    private function score(string $a, string $b): float
    {
        similar_text($a, $b, $similarPercent);

        $wordsA      = array_filter(explode(' ', $a));
        $wordsB      = array_filter(explode(' ', $b));
        $commonWords = count(array_intersect($wordsA, $wordsB));
        $maxWords    = max(count($wordsA), count($wordsB), 1);
        $wordOverlap = ($commonWords / $maxWords) * 100;

        return max($similarPercent, $wordOverlap);
    }

    private function printMatches(array $matches): void
    {
        foreach ($matches as $match) {
            $score  = number_format($match['score'], 0);
            $target = str_pad('"' . $match['target_nombre'] . '"', 50);
            $source = '"' . $match['source_nombre'] . '"';

            $this->line("MATCH  {$target}  ->  {$source}  ({$score}%)");
        }
    }

    private function applyMatches(array $matches): void
    {
        foreach ($matches as $match) {
            DB::table('ejercicios_fitcron')
                ->where('slug', $match['target_slug'])
                ->update([
                    'gif_filename'    => $match['gif_filename'],
                    'sin_fondo_listo' => $match['sin_fondo_listo'],
                ]);
        }

        $this->info("Matches aplicados: " . count($matches));
    }
}
