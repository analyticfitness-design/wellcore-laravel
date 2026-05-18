<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Models\Kb\ExerciseMetadata;
use App\Services\LintEngine\Data\LintContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Hace HEAD request a cada URL del path y verifica respuesta 2xx.
 *
 * Usado por: external_gif_url_inaccessible.
 *
 * Estrategia de cache (en orden):
 *   1. exercise_metadata.gif_url_status (DB cache, persistente, refrescable con kb:verify-gifs)
 *   2. Laravel Cache (cache_ttl_hours)
 *   3. HEAD HTTP real (fallback)
 *
 * Para URLs del repo wellcore-exercise-gifs (raw.githubusercontent.com/.../master/*.gif),
 * primero busca en exercise_metadata. Si el alias matchea y status='ok', skip HTTP.
 * Esto baja un compose+lint de ~10s a ~50ms cuando el catálogo está verificado.
 *
 * Si check_definition incluye `expected_pattern` sin `method`, delegate a UrlMatchesPatternValidator.
 */
final class ExternalHeadValidator extends BaseValidator
{
    /** Prefijo del repo de GIFs v2 — única fuente autorizada para el motor v2. */
    private const REPO_PREFIX = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/';

    /** Cache en memoria por proceso del lookup gif_filename → status (evita N queries). */
    private array $filenameStatusCache = [];

    /** Force-disable del DB cache (override CLI --no-cache). */
    private bool $forceNoDbCache = false;

    public function name(): string
    {
        return 'external_head';
    }

    /**
     * Permite al CLI (plan:lint --no-cache) forzar HEAD HTTP saltando el DB cache.
     * Útil cuando el catálogo gif_url_status puede estar stale y queremos verificación real.
     */
    public function forceNoDbCache(bool $force): void
    {
        $this->forceNoDbCache = $force;
        if ($force) {
            $this->filenameStatusCache = [];
        }
    }

    public function check(LintContext $ctx): array
    {
        // Si la rule tiene expected_pattern, delegate a url_matches_pattern
        // (porque external_head con expected_pattern es pattern check, no HTTP)
        if (isset($ctx->checkDefinition['expected_pattern']) && ! isset($ctx->checkDefinition['method'])) {
            return [];
        }

        $path = $ctx->checkDefinition['json_path'] ?? null;
        if (! is_string($path)) {
            return [];
        }

        $timeoutMs = (int) ($ctx->checkDefinition['timeout_ms'] ?? 8000);
        $cacheTtlHours = (int) ($ctx->checkDefinition['cache_ttl_hours'] ?? 24);
        $followRedirects = (bool) ($ctx->checkDefinition['follow_redirects'] ?? true);
        $useDbCache = ! $this->forceNoDbCache && (bool) ($ctx->checkDefinition['use_db_cache'] ?? true);

        $this->preloadDbCacheForPlan($ctx->plan, $useDbCache);

        $matches = $this->resolvePath($ctx->plan, $path);
        $violations = [];

        foreach ($matches as $m) {
            $url = $m->value;
            if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue; // URL inválida la cubre otra rule
            }

            $status = $this->resolveStatus($url, $timeoutMs, $followRedirects, $cacheTtlHours, $useDbCache);

            if ($status < 200 || $status >= 300) {
                $violations[] = $this->makeViolation(
                    $ctx,
                    $m->path,
                    "HEAD a `$url` retornó status $status (no 2xx). El recurso puede estar roto.",
                    ['url' => $url, 'status' => $status],
                );
            }
        }
        return $violations;
    }

    /**
     * Resuelve status HTTP con cascade: DB cache → Laravel cache → HEAD real.
     */
    private function resolveStatus(string $url, int $timeoutMs, bool $followRedirects, int $cacheTtlHours, bool $useDbCache): int
    {
        // 1. DB cache (exercise_metadata.gif_url_status)
        if ($useDbCache && str_starts_with($url, self::REPO_PREFIX)) {
            $filename = substr($url, strlen(self::REPO_PREFIX));
            if (isset($this->filenameStatusCache[$filename])) {
                return $this->mapDbStatusToHttp($this->filenameStatusCache[$filename]);
            }
        }

        // 2. Laravel cache
        $cacheKey = 'lint_head:' . md5($url);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (int) $cached;
        }

        // 3. HEAD HTTP real
        $status = $this->fetchHeadStatus($url, $timeoutMs, $followRedirects);
        Cache::put($cacheKey, $status, now()->addHours($cacheTtlHours));
        return $status;
    }

    /**
     * Pre-carga el cache en memoria con TODOS los filenames del exercise_metadata
     * en una sola query — evita N+1 cuando el plan tiene 50+ ejercicios.
     */
    private function preloadDbCacheForPlan(array $plan, bool $useDbCache): void
    {
        if (! $useDbCache || $this->filenameStatusCache !== []) {
            return;
        }

        try {
            // Solo cachear los que tienen status conocido (ok/broken/missing).
            // 'unknown' → fallback a HTTP en resolveStatus.
            $rows = ExerciseMetadata::query()
                ->whereIn('gif_url_status', ['ok', 'broken', 'missing'])
                ->whereNotNull('gif_url_verified_at')
                ->get(['alias', 'gif_filename', 'gif_url_status']);

            foreach ($rows as $row) {
                $filename = $row->gif_filename ?? ($row->alias . '.gif');
                $this->filenameStatusCache[$filename] = (string) $row->gif_url_status;
            }
        } catch (Throwable) {
            // Si no podemos leer la tabla (ej. tests sin schema), seguimos sin cache.
        }
    }

    private function mapDbStatusToHttp(string $dbStatus): int
    {
        return match ($dbStatus) {
            'ok' => 200,
            'broken' => 404,
            'missing' => 404,
            default => 0,
        };
    }

    private function fetchHeadStatus(string $url, int $timeoutMs, bool $followRedirects): int
    {
        try {
            $req = Http::timeout(max(1, (int) ceil($timeoutMs / 1000)));
            if (! $followRedirects) {
                $req = $req->withOptions(['allow_redirects' => false]);
            }
            $response = $req->head($url);
            return $response->status();
        } catch (Throwable) {
            return 0; // 0 = error de red / timeout
        }
    }
}
