<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Smart GIF Matcher — populates the exercise_aliases table.
 *
 * Three-pass strategy per exercise name:
 *   Pass 1 – Exact normalized match against ejercicios_fitcron.nombre
 *   Pass 2 – Jaccard keyword overlap + similar_text() on Spanish names
 *   Pass 3 – Word overlap against GIF filename (catches English terms)
 *
 * Results are stored in exercise_aliases as exact normalized strings.
 * At runtime, ExerciseMediaService does pure hash lookups — zero fuzzy logic.
 *
 * Run once after deploy, then again whenever new plan names appear:
 *   php artisan wellcore:smart-gif-matcher
 *   php artisan wellcore:smart-gif-matcher --reset   (re-compute everything)
 *   php artisan wellcore:smart-gif-matcher --dry-run (preview without saving)
 */
class SmartGifMatcher extends Command
{
    protected $signature = 'wellcore:smart-gif-matcher
                            {--dry-run   : Preview matches without saving}
                            {--reset     : Clear auto-generated aliases and recompute}
                            {--threshold=0.30 : Minimum score (0-1) to accept a match}';

    protected $description = 'Populate exercise_aliases table from plan exercise names → fitcron GIFs.';

    // ─── Spanish fitness stopwords ────────────────────────────────────────────
    private const STOPWORDS = [
        'con', 'en', 'de', 'la', 'el', 'los', 'las', 'un', 'una', 'a',
        'por', 'para', 'al', 'del', 'hacia', 'sobre', 'bajo', 'sin',
        'y', 'o', 'e', 'u', 'que', 'se', 'su', 'sus',
    ];

    // ─── Key movement words — weighted higher ─────────────────────────────────
    private const MOVEMENT_TERMS = [
        'press', 'curl', 'remo', 'sentadilla', 'extension', 'apertura',
        'cruce', 'elevacion', 'fondos', 'dominada', 'jalon', 'jalón',
        'hip', 'thrust', 'face', 'pull', 'push', 'prensa', 'aductor',
        'abductor', 'predicador', 'femoral', 'flexion', 'rotacion',
        'encogimiento', 'plancha', 'puente', 'patada', 'vuelo', 'mariposa',
        'crossover', 'row', 'squat', 'lunge', 'fly', 'raise', 'pulldown',
        'pullover', 'deadlift', 'peso', 'muerto', 'shrug', 'dips', 'chin',
        'kickback', 'lateral', 'frontal', 'inclinado', 'declinado', 'vertical',
    ];

    public function handle(): int
    {
        $this->ensureTableExists();

        if ($this->option('reset')) {
            DB::table('exercise_aliases')->where('source', 'auto')->delete();
            $this->info('Auto-generated aliases cleared.');
        }

        // Load fitcron index (all exercises with a GIF)
        $fitcronIndex = $this->buildFitcronIndex();
        $this->info("Fitcron index: {$fitcronIndex->count()} exercises with GIF.");

        // Load all known aliases to skip re-processing
        $knownAliases = DB::table('exercise_aliases')
            ->pluck('fitcron_slug', 'alias')
            ->all(); // [alias_norm => slug]

        // Collect all distinct exercise names from active plans
        $planNames = $this->collectPlanNames();
        $this->info("Plan exercise names to process: {$planNames->count()} distinct.");

        $threshold = (float) $this->option('threshold');
        $isDry     = $this->option('dry-run');

        $stats = ['exact' => 0, 'fuzzy' => 0, 'filename' => 0, 'none' => 0, 'skipped' => 0];
        $toInsert = [];

        $bar = $this->output->createProgressBar($planNames->count());
        $bar->start();

        foreach ($planNames as $original) {
            $normAlias = $this->normalizeAlias($original);

            // Already in alias table — skip
            if (isset($knownAliases[$normAlias])) {
                $stats['skipped']++;
                $bar->advance();
                continue;
            }

            [$slug, $gif, $score, $method] = $this->matchExercise(
                $original, $normAlias, $fitcronIndex, $threshold
            );

            $stats[$method]++;

            $toInsert[] = [
                'fitcron_slug'  => $slug,
                'gif_filename'  => $gif,
                'alias'         => $normAlias,
                'alias_display' => $original,
                'score'         => $score,
                'source'        => 'auto',
                'created_at'    => now(),
            ];

            if ($isDry) {
                $icon = $gif ? '✓' : '✗';
                $this->newLine();
                $this->line("  {$icon} [{$method} {$score}] {$original}");
                if ($slug) {
                    $this->line("    → {$slug}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Batch insert
        if (! $isDry && ! empty($toInsert)) {
            foreach (array_chunk($toInsert, 200) as $chunk) {
                DB::table('exercise_aliases')->insertOrIgnore($chunk);
            }
        }

        $matched = $stats['exact'] + $stats['fuzzy'] + $stats['filename'];
        $total   = $planNames->count() - $stats['skipped'];

        $this->info("Exact: {$stats['exact']} | Fuzzy: {$stats['fuzzy']} | Filename: {$stats['filename']} | No match: {$stats['none']} | Skipped: {$stats['skipped']}");
        $this->info('Coverage: '.($total > 0 ? round($matched / $total * 100, 1) : 0).'%');

        if ($stats['none'] > 0) {
            $this->newLine();
            $this->warn('Exercises with no GIF match (add manually or update fitcron names):');
            foreach ($toInsert as $row) {
                if (! $row['gif_filename']) {
                    $this->line("  - {$row['alias_display']}");
                }
            }
        }

        return self::SUCCESS;
    }

    // ─── Matching engine ──────────────────────────────────────────────────────

    private function matchExercise(
        string $original,
        string $normAlias,
        \Illuminate\Support\Collection $index,
        float $threshold
    ): array {
        $planKeywords   = $this->keywords($original);
        $planFileWords  = $this->filenameWords($original);

        $best = ['slug' => null, 'gif' => null, 'score' => 0.0, 'method' => 'none'];

        foreach ($index as $entry) {
            // Pass 1: exact normalized match → perfect
            if ($normAlias === $entry['norm']) {
                return [$entry['slug'], $entry['gif_filename'], 1.0, 'exact'];
            }

            // Pass 2: Jaccard keyword + similar_text on Spanish name
            $jaccard  = $this->jaccard($planKeywords, $entry['keywords']);
            $sim      = $this->simText($normAlias, $entry['norm']);
            $movement = $this->movementBonus($planKeywords, $entry['keywords']);
            $score2   = 0.50 * $jaccard + 0.30 * $sim + 0.20 * $movement;

            // Pass 3: word overlap against GIF filename (catches English terms)
            $fileScore = $this->jaccard($planFileWords, $entry['filename_words']);
            $score3    = 0.65 * $fileScore + 0.35 * $sim;

            $score  = max($score2, $score3);
            $method = ($score3 > $score2) ? 'filename' : 'fuzzy';

            if ($score > $best['score']) {
                $best = ['slug' => $entry['slug'], 'gif' => $entry['gif_filename'], 'score' => $score, 'method' => $method];
            }
        }

        if ($best['score'] >= $threshold) {
            return [$best['slug'], $best['gif'], round($best['score'], 3), $best['method']];
        }

        return [null, null, round($best['score'], 3), 'none'];
    }

    // ─── Scoring helpers ──────────────────────────────────────────────────────

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }
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

    // ─── Text helpers ─────────────────────────────────────────────────────────

    /**
     * Normalized alias stored in DB — used for exact hash lookup at runtime.
     * Removes parentheticals, accents, punctuation, lowercases.
     */
    private function normalizeAlias(string $name): string
    {
        $name = preg_replace('/\([^)]*\)/', ' ', $name); // remove (...)
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
        if (str_ends_with($word, 'iones')) return substr($word, 0, -5).'ion';
        if (str_ends_with($word, 'ales') && strlen($word) > 6) return substr($word, 0, -2);
        if (str_ends_with($word, 'es')   && strlen($word) > 5) return substr($word, 0, -2);
        if (str_ends_with($word, 's')    && strlen($word) > 4) return substr($word, 0, -1);

        return $word;
    }

    private function filenameWords(string $nameOrFilename): array
    {
        // Strip GIF metadata: "00471301-Barbell-Incline-Bench-Press_Chest_720.gif"
        $clean = preg_replace('/^\d+\-/', '', $nameOrFilename);
        $clean = preg_replace('/_(Chest|Back|Shoulder|Biceps|Triceps|Legs|Abs|Glutes|Hamstring|Quadriceps|Calves|Full.Body|Cardio)_\d+\.gif$/i', '', $clean);
        $clean = preg_replace('/[^a-zA-Z\s]/', ' ', $clean);

        return array_values(array_filter(
            preg_split('/[\s\-_]+/', mb_strtolower($clean)),
            fn ($w) => strlen($w) > 2
        ));
    }

    // ─── Fitcron index builder ────────────────────────────────────────────────

    private function buildFitcronIndex(): \Illuminate\Support\Collection
    {
        return DB::table('ejercicios_fitcron')
            ->whereNotNull('gif_filename')
            ->select('slug', 'nombre', 'gif_filename')
            ->get()
            ->map(fn ($r) => [
                'slug'           => $r->slug,
                'gif_filename'   => $r->gif_filename,
                'norm'           => $this->normalizeAlias($r->nombre),
                'keywords'       => $this->keywords($r->nombre),
                'filename_words' => $this->filenameWords($r->gif_filename),
            ]);
    }

    // ─── Plan name collector ──────────────────────────────────────────────────

    private function collectPlanNames(): \Illuminate\Support\Collection
    {
        return DB::table('assigned_plans')
            ->where('active', true)
            ->where('plan_type', 'entrenamiento')
            ->pluck('content')
            ->flatMap(function ($content) {
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
                );
            })
            ->filter(fn ($n) => is_string($n) && trim($n) !== '')
            ->unique()
            ->values();
    }

    // ─── Table setup ──────────────────────────────────────────────────────────

    private function ensureTableExists(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS exercise_aliases (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fitcron_slug    VARCHAR(255)  NULL,
                gif_filename    VARCHAR(500)  NULL,
                alias           VARCHAR(500)  NOT NULL COMMENT 'Normalized form used for lookup',
                alias_display   VARCHAR(500)  NULL     COMMENT 'Original human-readable form',
                score           FLOAT         NULL     COMMENT '0-1 match confidence',
                source          VARCHAR(20)   NOT NULL DEFAULT 'auto'
                                COMMENT 'auto | manual | canonical',
                created_at      TIMESTAMP     NULL,
                UNIQUE KEY uq_alias (alias(255)),
                INDEX idx_slug  (fitcron_slug(100))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
