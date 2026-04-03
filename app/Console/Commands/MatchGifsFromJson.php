<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Populates exercise_aliases using gif_mapping.json as the GIF source.
 *
 * Uses the same three-pass scoring as SmartGifMatcher but reads the
 * GIF index from storage/gif_mapping.json instead of ejercicios_fitcron.
 * This works correctly in production where fitcron has few GIFs.
 *
 * Run:
 *   php artisan wellcore:match-gifs-from-json
 *   php artisan wellcore:match-gifs-from-json --reset --threshold=0.28
 */
class MatchGifsFromJson extends Command
{
    protected $signature = 'wellcore:match-gifs-from-json
                            {--dry-run   : Preview without saving}
                            {--reset     : Clear auto-generated aliases and recompute}
                            {--threshold=0.28 : Minimum score (0-1) to accept a match}';

    protected $description = 'Populate exercise_aliases from plan names → gif_mapping.json using fuzzy scoring.';

    // Same stopwords and movement terms as SmartGifMatcher
    private const STOPWORDS = [
        'con', 'en', 'de', 'la', 'el', 'los', 'las', 'un', 'una', 'a',
        'por', 'para', 'al', 'del', 'hacia', 'sobre', 'bajo', 'sin',
        'y', 'o', 'e', 'u', 'que', 'se', 'su', 'sus',
    ];

    private const MOVEMENT_TERMS = [
        'press', 'curl', 'remo', 'sentadilla', 'extension', 'apertura',
        'cruce', 'elevacion', 'fondos', 'dominada', 'jalon', 'jalon',
        'hip', 'thrust', 'face', 'pull', 'push', 'prensa', 'aductor',
        'abductor', 'predicador', 'femoral', 'flexion', 'rotacion',
        'encogimiento', 'plancha', 'puente', 'patada', 'vuelo', 'mariposa',
        'crossover', 'row', 'squat', 'lunge', 'fly', 'raise', 'pulldown',
        'pullover', 'deadlift', 'peso', 'muerto', 'shrug', 'dips', 'chin',
        'kickback', 'lateral', 'frontal', 'inclinado', 'declinado', 'vertical',
    ];

    public function handle(): int
    {
        $mapFile = base_path('storage/gif_mapping.json');
        if (! file_exists($mapFile)) {
            $this->error('gif_mapping.json not found: ' . $mapFile);
            return self::FAILURE;
        }

        $mapping = json_decode(file_get_contents($mapFile), true);
        if (! $mapping) {
            $this->error('Failed to parse gif_mapping.json');
            return self::FAILURE;
        }

        // Ensure table exists
        DB::statement("
            CREATE TABLE IF NOT EXISTS exercise_aliases (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fitcron_slug    VARCHAR(255)  NULL,
                gif_filename    VARCHAR(500)  NULL,
                alias           VARCHAR(500)  NOT NULL,
                alias_display   VARCHAR(500)  NULL,
                score           FLOAT         NULL,
                source          VARCHAR(20)   NOT NULL DEFAULT 'auto',
                created_at      TIMESTAMP     NULL,
                UNIQUE KEY uq_alias (alias(255)),
                INDEX idx_slug  (fitcron_slug(100))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        if ($this->option('reset')) {
            DB::table('exercise_aliases')->where('source', 'auto')->delete();
            $this->info('Auto-generated aliases cleared.');
        }

        // Build GIF index from json
        $gifIndex = collect($mapping)
            ->filter(fn ($e) => ! empty($e['gif_filename']) && ! empty($e['nombre']))
            ->map(fn ($e) => [
                'slug'           => $e['slug'] ?? null,
                'gif_filename'   => $e['gif_filename'],
                'norm'           => $this->normalizeAlias($e['nombre']),
                'keywords'       => $this->keywords($e['nombre']),
                'filename_words' => $this->filenameWords($e['gif_filename']),
            ]);

        $this->info("GIF index: {$gifIndex->count()} entries from gif_mapping.json");

        $knownAliases = DB::table('exercise_aliases')->pluck('fitcron_slug', 'alias')->all();

        $planNames = $this->collectPlanNames();
        $this->info("Plan exercise names: {$planNames->count()} distinct.");

        $threshold = (float) $this->option('threshold');
        $isDry     = $this->option('dry-run');
        $stats     = ['exact' => 0, 'fuzzy' => 0, 'filename' => 0, 'none' => 0, 'skipped' => 0];
        $toInsert  = [];

        $bar = $this->output->createProgressBar($planNames->count());
        $bar->start();

        foreach ($planNames as $original) {
            $normAlias     = $this->normalizeAlias($original);
            $planKeywords  = $this->keywords($original);
            $planFileWords = $this->filenameWords($original);

            if (isset($knownAliases[$normAlias])) {
                $stats['skipped']++;
                $bar->advance();
                continue;
            }

            $best = ['entry' => null, 'score' => 0.0, 'method' => 'none'];

            foreach ($gifIndex as $entry) {
                // Pass 1: exact
                if ($normAlias === $entry['norm']) {
                    $best = ['entry' => $entry, 'score' => 1.0, 'method' => 'exact'];
                    break;
                }

                // Pass 2: Jaccard keywords + similar_text + movement bonus
                $jaccard  = $this->jaccard($planKeywords, $entry['keywords']);
                $sim      = $this->simText($normAlias, $entry['norm']);
                $movement = $this->movementBonus($planKeywords, $entry['keywords']);
                $score2   = 0.50 * $jaccard + 0.30 * $sim + 0.20 * $movement;

                // Pass 3: filename word overlap + similar_text
                $fileScore = $this->jaccard($planFileWords, $entry['filename_words']);
                $score3    = 0.65 * $fileScore + 0.35 * $sim;

                $score  = max($score2, $score3);
                $method = ($score3 > $score2) ? 'filename' : 'fuzzy';

                if ($score > $best['score']) {
                    $best = ['entry' => $entry, 'score' => $score, 'method' => $method];
                }
            }

            if ($best['method'] === 'exact') {
                $stats['exact']++;
                $toInsert[] = $this->makeRow($normAlias, $original, $best['entry']['gif_filename'], $best['entry']['slug'], 1.0);
                if ($isDry) $this->line("\n  ✓ [exact] {$original}");
            } elseif ($best['score'] >= $threshold) {
                $stats[$best['method']]++;
                $toInsert[] = $this->makeRow($normAlias, $original, $best['entry']['gif_filename'], $best['entry']['slug'], $best['score']);
                if ($isDry) $this->line(sprintf("\n  ~ [%.3f %s] %s → %s", $best['score'], $best['method'], $original, $best['entry']['gif_filename']));
            } else {
                $stats['none']++;
                if ($isDry) $this->line(sprintf("\n  ✗ [%.3f] %s", $best['score'], $original));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! $isDry && ! empty($toInsert)) {
            foreach (array_chunk($toInsert, 200) as $chunk) {
                DB::table('exercise_aliases')->insertOrIgnore($chunk);
            }
        }

        $matched = $stats['exact'] + $stats['fuzzy'] + $stats['filename'];
        $total   = $planNames->count() - $stats['skipped'];
        $this->info("Exact: {$stats['exact']} | Fuzzy: {$stats['fuzzy']} | Filename: {$stats['filename']} | No match: {$stats['none']} | Skipped: {$stats['skipped']}");
        $this->info('Coverage: ' . ($total > 0 ? round($matched / $total * 100, 1) : 0) . '%');

        return self::SUCCESS;
    }

    // ─── Scoring helpers (same as SmartGifMatcher) ───────────────────────────

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;
        $inter = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        return $union > 0 ? $inter / $union : 0.0;
    }

    private function simText(string $a, string $b): float
    {
        similar_text($a, $b, $pct);
        return $pct / 100;
    }

    private function movementBonus(array $a, array $b): float
    {
        $shared = array_intersect(
            array_intersect($a, self::MOVEMENT_TERMS),
            array_intersect($b, self::MOVEMENT_TERMS)
        );
        return count($shared) > 0 ? min(1.0, count($shared) * 0.5) : 0.0;
    }

    // ─── Text helpers (same as SmartGifMatcher) ──────────────────────────────

    private function normalizeAlias(string $name): string
    {
        $name = preg_replace('/\([^)]*\)/', ' ', $name);
        $name = mb_strtolower(trim($name));
        $map  = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function keywords(string $name): array
    {
        $norm  = $this->normalizeAlias($name);
        $words = explode(' ', $norm);
        return array_unique(array_values(array_filter(
            array_map([$this, 'stem'], $words),
            fn ($w) => strlen($w) > 2 && ! in_array($w, self::STOPWORDS, true)
        )));
    }

    private function stem(string $word): string
    {
        if (str_ends_with($word, 'iones')) return substr($word, 0, -5) . 'ion';
        if (str_ends_with($word, 'ales') && strlen($word) > 6) return substr($word, 0, -2);
        if (str_ends_with($word, 'es')   && strlen($word) > 5) return substr($word, 0, -2);
        if (str_ends_with($word, 's')    && strlen($word) > 4) return substr($word, 0, -1);
        return $word;
    }

    private function filenameWords(string $nameOrFilename): array
    {
        $clean = preg_replace('/^\d+\-/', '', $nameOrFilename);
        $clean = preg_replace('/_(Chest|Back|Shoulder|Biceps|Triceps|Legs|Abs|Glutes|Hamstring|Quadriceps|Calves|Full.Body|Cardio|Thighs|Waist|Shoulders|Hips|Hip|Upper.Arms)_\d+\.gif$/i', '', $clean);
        $clean = preg_replace('/[^a-zA-Z\s]/', ' ', $clean);
        return array_values(array_filter(
            preg_split('/[\s\-_]+/', mb_strtolower($clean)),
            fn ($w) => strlen($w) > 2
        ));
    }

    private function makeRow(string $alias, string $display, string $gif, ?string $slug, float $score): array
    {
        return [
            'fitcron_slug'  => $slug,
            'gif_filename'  => $gif,
            'alias'         => $alias,
            'alias_display' => $display,
            'score'         => round($score, 3),
            'source'        => 'auto',
            'created_at'    => now(),
        ];
    }

    private function collectPlanNames(): \Illuminate\Support\Collection
    {
        // ── Source 1: assigned_plans (Client / Elite / Método / Esencial) ──────
        $fromAssigned = DB::table('assigned_plans')
            ->where('active', true)
            ->where('plan_type', 'entrenamiento')
            ->pluck('content')
            ->flatMap(function ($content) {
                return $this->extractNamesFromContent($content);
            });

        // ── Source 2: rise_programs (RISE clients) ────────────────────────────
        $fromRise = DB::table('rise_programs')
            ->whereNotNull('personalized_program')
            ->pluck('personalized_program')
            ->flatMap(function ($content) {
                $data = is_string($content) ? json_decode($content, true) : $content;
                if (! is_array($data)) return [];
                $trainingPlan = $data['plan_entrenamiento'] ?? $data['training_plan'] ?? $data;
                return $this->extractNamesFromContent(is_array($trainingPlan) ? json_encode($trainingPlan) : $trainingPlan);
            });

        return $fromAssigned->merge($fromRise)
            ->filter(fn ($n) => is_string($n) && trim($n) !== '')
            ->unique()
            ->values();
    }

    private function extractNamesFromContent(mixed $content): array
    {
        $data = is_string($content) ? json_decode($content, true) : $content;
        if (! is_array($data)) return [];

        $dias = is_array($data['dias'] ?? null) ? $data['dias'] : [];
        $semanas = is_array($data['semanas'] ?? null) ? $data['semanas'] : [];
        foreach ($semanas as $s) {
            $semDias = is_array($s['dias'] ?? null) ? $s['dias'] : [];
            $dias = array_merge($dias, $semDias);
        }

        return collect($dias)->flatMap(fn ($d) =>
            is_array($d)
                ? collect($d['ejercicios'] ?? [])->map(fn ($e) =>
                    is_array($e) ? ($e['nombre'] ?? $e['name'] ?? null) : $e
                  )
                : collect()
        )->all();
    }
}
