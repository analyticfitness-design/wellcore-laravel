<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ExerciseMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * kb:reconcile-gifs — fuzzy match de aliases broken contra los paths reales del repo.
 *
 * Flow:
 *   1. GET https://api.github.com/repos/.../git/trees/master?recursive=1 → 265 .gif paths
 *   2. Por cada ExerciseMetadata con gif_url_status='broken', calcula best fuzzy match
 *      contra los paths reales
 *   3. Si la similaridad supera threshold, guarda en `gif_filename`
 *   4. Recomienda correr `kb:verify-gifs --force` después
 *
 * Conservador: solo reconcilia si similaridad >= --min-score (default 70).
 * Idempotente: si ya tiene gif_filename, lo skip salvo --force.
 *
 * Solo wellcore_kb local. NO toca producción.
 */
final class KbReconcileGifsCommand extends Command
{
    protected $signature = 'kb:reconcile-gifs
                            {--min-score=70 : score mínimo de similaridad (0-100) para aceptar match}
                            {--force : re-reconcilia incluso si gif_filename ya está seteado}
                            {--dry-run : muestra matches sin escribir DB}';

    protected $description = 'Reconcilia aliases broken con paths reales del repo GitHub (fuzzy match).';

    public function handle(): int
    {
        $minScore = (int) $this->option('min-score');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Descargando lista de gifs del repo...');
        $repoGifs = $this->fetchRepoGifs();
        if ($repoGifs === null) {
            $this->error('No pude obtener la lista de gifs del repo. Verificá conexión.');
            return 2;
        }
        $this->info('Repo contiene ' . count($repoGifs) . ' .gif files.');

        $query = ExerciseMetadata::query()
            ->where('gif_url_status', 'broken');
        if (! $force) {
            $query->whereNull('gif_filename');
        }
        $broken = $query->orderBy('id')->get();

        if ($broken->isEmpty()) {
            $this->info('Nada para reconciliar.');
            return 0;
        }

        $this->info('Reconciliando ' . $broken->count() . ' aliases broken...');
        $stats = ['matched' => 0, 'skipped_low_score' => 0];

        foreach ($broken as $ex) {
            [$bestPath, $bestScore] = $this->bestMatch($ex->alias, $repoGifs);
            if ($bestScore >= $minScore) {
                if ($dryRun) {
                    $this->line(sprintf('  [%2d%%] %s → %s', $bestScore, $ex->alias, $bestPath));
                } else {
                    $ex->update(['gif_filename' => $bestPath]);
                    $this->line(sprintf('  ✓ [%2d%%] %s → %s', $bestScore, $ex->alias, $bestPath));
                }
                $stats['matched']++;
            } else {
                $this->warn(sprintf('  ✗ [%2d%%] %s → %s (score bajo, no aplicado)', $bestScore, $ex->alias, $bestPath));
                $stats['skipped_low_score']++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Resumen:");
        $this->line("  ✓ matched: {$stats['matched']}");
        $this->line("  ✗ score bajo: {$stats['skipped_low_score']}");

        if (! $dryRun && $stats['matched'] > 0) {
            $this->newLine();
            $this->line('Recomendado: php artisan kb:verify-gifs --force  (re-verifica los reconciliados)');
        }

        return 0;
    }

    /**
     * @return string[]|null
     */
    private function fetchRepoGifs(): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                ->get('https://api.github.com/repos/analyticfitness-design/wellcore-exercise-gifs/git/trees/master?recursive=1');

            if (! $response->successful()) {
                return null;
            }

            $tree = $response->json('tree') ?? [];
            $gifs = array_filter($tree, fn ($n) => str_ends_with($n['path'] ?? '', '.gif'));
            return array_values(array_map(fn ($n) => $n['path'], $gifs));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param string[] $candidates
     * @return array{0: string, 1: int}  [bestPath, bestScore]
     */
    private function bestMatch(string $alias, array $candidates): array
    {
        $best = '';
        $bestScore = 0;

        foreach ($candidates as $cand) {
            // Normaliza ambos para comparar: lowercase, sin .gif, con espacios en vez de guiones.
            $candName = preg_replace('/\.gif$/', '', $cand);
            $score = $this->similarity($alias, $candName);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $cand;
            }
        }

        return [$best, $bestScore];
    }

    /**
     * Similaridad basada en cobertura de tokens significativos.
     *
     * Algoritmo:
     *   1. Tokeniza ambos por '-', filtra stop words (de, con, en, el, la, a, una, un).
     *   2. Score = % de tokens significativos del alias original presentes en el candidato.
     *      También se penaliza si el candidato introduce un token "diferenciador"
     *      que el alias original NO tenía (ej. "maquina" en match cuando alias dice "mancuerna").
     *   3. Empate desempata por similar_text raw (texto literal más cercano).
     *
     * Resultado 0-100.
     */
    private function similarity(string $alias, string $candidate): int
    {
        $alias = strtolower($alias);
        $candidate = strtolower(preg_replace('/\.gif$/', '', $candidate));

        if ($alias === $candidate) {
            return 100;
        }

        $stopWords = ['de', 'con', 'en', 'el', 'la', 'a', 'una', 'un', 'al', 'del'];

        $tokenize = function (string $s) use ($stopWords): array {
            $tokens = preg_split('/[-_\s]+/', $s) ?: [];
            return array_values(array_filter($tokens, fn ($t) => $t !== '' && ! in_array($t, $stopWords, true)));
        };

        $tokensA = $tokenize($alias);
        $tokensB = $tokenize($candidate);

        if ($tokensA === [] || $tokensB === []) {
            return 0;
        }

        // Coverage: cuántos tokens significativos del alias están en el candidato.
        $coverage = count(array_intersect($tokensA, $tokensB)) / count($tokensA);

        // Penalty: tokens NUEVOS en candidato que no están en alias y son "diferenciadores"
        // (palabras que cambian el significado: maquina vs mancuerna, barra vs cuerda, etc).
        $differentiators = ['mancuerna', 'mancuernas', 'maquina', 'barra', 'polea', 'cuerda', 'banda', 'cable', 'kettlebell', 'banco', 'cuerpo', 'piso'];
        $extraTokens = array_diff($tokensB, $tokensA);
        $extraDifferentiators = array_intersect($extraTokens, $differentiators);

        // Si el candidato introduce un differentiator que el alias NO menciona, penaliza.
        // Pero solo si el alias TENÍA su propio differentiator (mancuerna en alias, maquina en candidato).
        $aliasDifferentiators = array_intersect($tokensA, $differentiators);
        $penalty = 0.0;
        if ($aliasDifferentiators !== [] && $extraDifferentiators !== []) {
            // Si los differentiators no se solapan, fuerte penalización.
            if (array_intersect($aliasDifferentiators, $tokensB) === []) {
                $penalty = 0.5; // bajamos score 50%
            }
        }

        $score = $coverage * (1 - $penalty);

        // Tiebreak: similar_text raw para casos muy parecidos.
        similar_text($alias, $candidate, $rawSim);
        $score = $score * 0.85 + ($rawSim / 100) * 0.15;

        return (int) round($score * 100);
    }
}
