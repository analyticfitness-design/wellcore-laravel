<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ExerciseMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * kb:verify-gifs — Stage VERIFY assistant del motor v2.
 *
 * Recorre wellcore_kb.exercise_metadata, hace HEAD a cada gif_url, actualiza
 * `gif_url_status` (ok/broken/missing) + `gif_url_verified_at`.
 *
 * Útil porque el catálogo seed inicial tiene aliases que no existen en el repo
 * GitHub público — ExerciseSelector luego filtra gif_url_status='ok' para
 * componer planes sin links rotos.
 *
 * Opciones:
 *   --force      Re-verifica todos, incluyendo los que ya tienen status reciente
 *   --max=N      Limita a N ejercicios (útil para dry-run)
 *   --stale-days=N  Solo re-verifica los que tienen gif_url_verified_at > N días atrás (default 7)
 *
 * Idempotente: corre varias veces sin efectos secundarios.
 * NO toca producción (solo wellcore_kb).
 */
final class KbVerifyGifsCommand extends Command
{
    protected $signature = 'kb:verify-gifs
                            {--force : re-verifica todos los ejercicios}
                            {--max= : limita a N ejercicios}
                            {--stale-days=7 : re-verifica solo si verified_at > N días}
                            {--timeout=8 : timeout HTTP por request (segundos)}';

    protected $description = 'Verifica con HEAD que los gif_url de exercise_metadata existen y actualiza gif_url_status.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $max = $this->option('max') !== null ? (int) $this->option('max') : null;
        $staleDays = (int) $this->option('stale-days');
        $timeout = (int) $this->option('timeout');

        $query = ExerciseMetadata::query()->orderBy('id');
        if (! $force) {
            $threshold = now()->subDays($staleDays);
            $query->where(function ($q) use ($threshold) {
                $q->whereNull('gif_url_verified_at')
                  ->orWhere('gif_url_verified_at', '<', $threshold)
                  ->orWhere('gif_url_status', 'unknown');
            });
        }
        if ($max !== null) {
            $query->limit($max);
        }

        $exercises = $query->get();
        $total = $exercises->count();

        if ($total === 0) {
            $this->info('Sin ejercicios para verificar — todos están al día o no hay datos.');
            return 0;
        }

        $this->info("Verificando $total gif_url (timeout {$timeout}s cada uno)...");

        $stats = ['ok' => 0, 'broken' => 0, 'missing' => 0, 'errors' => 0];
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($exercises as $ex) {
            $url = $ex->gifUrl();
            $status = $this->checkUrl($url, $timeout);

            try {
                $ex->update([
                    'gif_url_status' => $status,
                    'gif_url_verified_at' => now(),
                ]);
            } catch (Throwable $e) {
                $stats['errors']++;
                $this->newLine();
                $this->warn("Error actualizando '{$ex->alias}': " . $e->getMessage());
            }

            $stats[$status] = ($stats[$status] ?? 0) + 1;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Resumen:');
        $this->line("  ✓ ok:      {$stats['ok']}");
        $this->line("  ✗ broken:  {$stats['broken']}");
        $this->line("  ? missing: " . ($stats['missing'] ?? 0));
        if (($stats['errors'] ?? 0) > 0) {
            $this->warn("  ! errors:  {$stats['errors']}");
        }

        return $stats['broken'] === 0 && ($stats['errors'] ?? 0) === 0 ? 0 : 1;
    }

    /**
     * Retorna 'ok' | 'broken' | 'missing'.
     */
    private function checkUrl(string $url, int $timeoutSeconds): string
    {
        if ($url === '' || ! str_starts_with($url, 'https://')) {
            return 'missing';
        }

        try {
            $response = Http::timeout($timeoutSeconds)
                ->withOptions(['allow_redirects' => true])
                ->head($url);

            return $response->successful() ? 'ok' : 'broken';
        } catch (Throwable) {
            return 'broken';
        }
    }
}
