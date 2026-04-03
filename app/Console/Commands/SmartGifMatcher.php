<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Smart multi-layer GIF matcher.
 *
 * Reads all distinct exercise names from active assigned_plans and matches them
 * to ejercicios_fitcron using 3 strategies:
 *   1. Exact normalized name match
 *   2. Jaccard keyword overlap + similar_text() (Spanish-aware, with stemming)
 *   3. GIF filename word match (catches English-named exercises in plans)
 *
 * Results are cached in exercise_name_gif_map so ExerciseMediaService can do
 * a fast single-query lookup at runtime instead of fuzzy matching per request.
 */
class SmartGifMatcher extends Command
{
    protected $signature = 'wellcore:smart-gif-matcher
                            {--dry-run : Show matches without saving}
                            {--threshold=0.28 : Minimum combined score to accept a match (0-1)}
                            {--reset : Clear existing map and re-compute all}';

    protected $description = 'Multi-layer GIF matcher: maps plan exercise names to fitcron GIFs and caches results.';

    // Spanish fitness stopwords — excluded from keyword extraction
    private array $stopwords = [
        'con', 'en', 'de', 'la', 'el', 'los', 'las', 'un', 'una', 'a',
        'por', 'para', 'al', 'del', 'hacia', 'sobre', 'bajo', 'sin', 'e',
        'y', 'o', 'u', 'que', 'se', 'su', 'sus', 'esta', 'este',
    ];

    // Movement verbs — weighted higher in matching
    private array $movementTerms = [
        'press', 'curl', 'remo', 'sentadilla', 'extension', 'apertura',
        'cruce', 'elevacion', 'fondos', 'dominada', 'jalon', 'hip', 'thrust',
        'face', 'pull', 'push', 'prensa', 'aductor', 'abductor', 'predicador',
        'femoral', 'flexion', 'rotacion', 'encogimiento', 'plancha', 'puente',
        'patada', 'vuelo', 'mariposa', 'crossover', 'row', 'squat', 'lunge',
        'press', 'fly', 'raise', 'pulldown', 'pullover', 'deadlift', 'shrug',
        'dips', 'chin', 'kickback', 'lateral', 'frontal', 'inclinado', 'declinado',
    ];

    // Equipment terms — secondary weight
    private array $equipmentTerms = [
        'barra', 'mancuerna', 'mancuernas', 'polea', 'maquina', 'cable',
        'banco', 'ez', 'kettlebell', 'smith', 'multipower', 'cuerda',
        'banda', 'barra', 'barbell', 'dumbbell', 'machine', 'cable',
    ];

    public function handle(): int
    {
        // 1. Ensure cache table exists
        $this->ensureTable();

        if ($this->option('reset')) {
            DB::table('exercise_name_gif_map')->truncate();
            $this->info('Map cleared.');
        }

        // 2. Load fitcron exercises with GIFs
        $fitcronRows = DB::table('ejercicios_fitcron')
            ->whereNotNull('gif_filename')
            ->select('slug', 'nombre', 'gif_filename')
            ->get();

        if ($fitcronRows->isEmpty()) {
            $this->error('No fitcron exercises with gif_filename found.');
            return self::FAILURE;
        }

        $this->info("Loaded {$fitcronRows->count()} fitcron exercises with GIFs.");

        // Build normalized index
        $fitcronIndex = $fitcronRows->map(fn ($r) => [
            'slug'         => $r->slug,
            'nombre'       => $r->nombre,
            'gif_filename' => $r->gif_filename,
            'norm'         => $this->normalize($r->nombre),
            'keywords'     => $this->extractKeywords($r->nombre),
            'filename_words' => $this->extractFilenameWords($r->gif_filename),
        ])->all();

        // 3. Collect all distinct plan exercise names
        $planNames = $this->collectPlanExerciseNames();
        $this->info("Found {$planNames->count()} distinct exercise names in active plans.");

        // 4. Load already-mapped names to skip
        $alreadyMapped = DB::table('exercise_name_gif_map')
            ->pluck('nombre_plan')
            ->map(fn ($n) => $this->normalize($n))
            ->flip()
            ->all();

        $newMatches   = 0;
        $noMatch      = 0;
        $skipped      = 0;
        $threshold    = (float) $this->option('threshold');
        $isDry        = $this->option('dry-run');

        $bar = $this->output->createProgressBar($planNames->count());
        $bar->start();

        foreach ($planNames as $planName) {
            $normPlan = $this->normalize($planName);

            if (isset($alreadyMapped[$normPlan])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            [$bestSlug, $bestGif, $bestScore, $bestMethod] = $this->findBestMatch(
                $planName, $normPlan, $fitcronIndex, $threshold
            );

            if (! $isDry) {
                DB::table('exercise_name_gif_map')->insertOrIgnore([
                    'nombre_plan'  => $planName,
                    'gif_filename' => $bestGif,
                    'fitcron_slug' => $bestSlug,
                    'score'        => $bestScore,
                    'method'       => $bestMethod,
                    'created_at'   => now(),
                ]);
            }

            if ($bestGif) {
                $newMatches++;
                if ($isDry) {
                    $this->line("\n  ✓ [{$bestMethod} {$bestScore}] {$planName}");
                    $this->line("    → {$bestSlug}");
                }
            } else {
                $noMatch++;
                if ($isDry) {
                    $this->line("\n  ✗ No match: {$planName}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $total = $planNames->count();
        $this->info("Results: {$newMatches} matched, {$noMatch} unmatched, {$skipped} skipped (already mapped).");
        $this->info('Coverage: '.round(($newMatches / max(1, $total - $skipped)) * 100, 1).'%');

        if ($noMatch > 0 && ! $isDry) {
            $unmatched = DB::table('exercise_name_gif_map')
                ->whereNull('gif_filename')
                ->pluck('nombre_plan');
            $this->warn('Exercises without a GIF match:');
            foreach ($unmatched as $u) {
                $this->line("  - {$u}");
            }
        }

        return self::SUCCESS;
    }

    // ─── Matching ────────────────────────────────────────────────────────────

    private function findBestMatch(string $planName, string $normPlan, array $fitcronIndex, float $threshold): array
    {
        $planKeywords    = $this->extractKeywords($planName);
        $planFileWords   = $this->extractFilenameWords($planName); // in case plan has English words
        $bestScore       = 0.0;
        $bestEntry       = null;
        $bestMethod      = 'none';

        foreach ($fitcronIndex as $entry) {
            // Layer 1: exact normalized match → perfect score
            if ($normPlan === $entry['norm']) {
                return [$entry['slug'], $entry['gif_filename'], 1.0, 'exact'];
            }

            // Layer 2: Jaccard keyword overlap + similar_text
            $jaccard  = $this->jaccardScore($planKeywords, $entry['keywords']);
            $simText  = $this->similarTextScore($normPlan, $entry['norm']);
            $movement = $this->movementBonus($planKeywords, $entry['keywords']);
            $score2   = 0.50 * $jaccard + 0.30 * $simText + 0.20 * $movement;

            // Layer 3: filename word overlap (catches English terms in plan names)
            $filenameScore = $this->jaccardScore($planFileWords, $entry['filename_words']);
            $score3        = 0.60 * $filenameScore + 0.40 * $simText;

            $combinedScore = max($score2, $score3);
            $method        = ($score3 > $score2) ? 'filename' : 'jaccard';

            if ($combinedScore > $bestScore) {
                $bestScore  = $combinedScore;
                $bestEntry  = $entry;
                $bestMethod = $method;
            }
        }

        if ($bestScore >= $threshold && $bestEntry) {
            return [
                $bestEntry['slug'],
                $bestEntry['gif_filename'],
                round($bestScore, 3),
                $bestMethod,
            ];
        }

        return [null, null, round($bestScore, 3), 'none'];
    }

    private function jaccardScore(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union        = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function similarTextScore(string $a, string $b): float
    {
        similar_text($a, $b, $pct);

        return $pct / 100;
    }

    private function movementBonus(array $planKw, array $fitcronKw): float
    {
        // Bonus if they share any movement term
        $planMov    = array_intersect($planKw, $this->movementTerms);
        $fitcronMov = array_intersect($fitcronKw, $this->movementTerms);
        $shared     = array_intersect($planMov, $fitcronMov);

        return count($shared) > 0 ? min(1.0, count($shared) * 0.5) : 0.0;
    }

    // ─── Text normalization ───────────────────────────────────────────────────

    private function normalize(string $name): string
    {
        // Remove parenthetical content: "(cable crossover hacia arriba)" → ""
        $name = preg_replace('/\([^)]*\)/', '', $name);
        $name = mb_strtolower(trim($name));

        // Transliterate accents
        $map  = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                 'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i',
                 'ò' => 'o', 'ù' => 'u'];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }

    private function extractKeywords(string $name): array
    {
        $norm  = $this->normalize($name);
        $words = explode(' ', $norm);

        // Remove stopwords
        $keywords = array_filter($words, fn ($w) => strlen($w) > 2 && ! in_array($w, $this->stopwords));

        // Apply simple Spanish stemming (singulars)
        $keywords = array_map([$this, 'stem'], array_values($keywords));

        return array_unique($keywords);
    }

    private function stem(string $word): string
    {
        // Simple suffix removal for Spanish plurals and verb forms
        if (str_ends_with($word, 'iones')) {
            return substr($word, 0, -5).'ion';
        }
        if (str_ends_with($word, 'cion')) {
            return substr($word, 0, -4).'cion';
        }
        if (str_ends_with($word, 'iones')) {
            return substr($word, 0, -5).'ion';
        }
        if (str_ends_with($word, 'ales')) {
            return substr($word, 0, -2); // laterales → lateral
        }
        if (str_ends_with($word, 'es') && strlen($word) > 5) {
            return substr($word, 0, -2); // cruces → cruce, elevaciones → elevacion
        }
        if (str_ends_with($word, 's') && strlen($word) > 4) {
            return substr($word, 0, -1); // mancuernas → mancuerna
        }

        return $word;
    }

    private function extractFilenameWords(string $nameOrFilename): array
    {
        // For GIF filenames: "00471301-Barbell-Incline-Bench-Press_Chest_720.gif"
        // Remove ID prefix, extension, resolution, muscle group tag
        $clean = preg_replace('/^\d+\-/', '', $nameOrFilename);
        $clean = preg_replace('/_(Chest|Back|Shoulder|Biceps|Triceps|Legs|Abs|Glutes|Hamstring|Quadriceps|Calves|Forearms|Full Body|Cardio)_\d+\.gif$/i', '', $clean);
        $clean = preg_replace('/[^a-zA-Z\s]/', ' ', $clean);

        // Also handle Spanish plan names that may have English words
        $words = array_filter(
            preg_split('/[\s\-_]+/', mb_strtolower($clean)),
            fn ($w) => strlen($w) > 2
        );

        return array_values(array_unique($words));
    }

    // ─── Data collection ─────────────────────────────────────────────────────

    private function collectPlanExerciseNames(): \Illuminate\Support\Collection
    {
        $plans = DB::table('assigned_plans')
            ->where('active', true)
            ->where('plan_type', 'entrenamiento')
            ->pluck('content');

        return $plans
            ->flatMap(function ($content) {
                $data = is_string($content) ? json_decode($content, true) : $content;
                if (! is_array($data)) {
                    return [];
                }

                $dias = $data['dias'] ?? [];

                foreach ($data['semanas'] ?? [] as $semana) {
                    $dias = array_merge($dias, $semana['dias'] ?? []);
                }

                return collect($dias)->flatMap(function ($dia) {
                    $ejercicios = $dia['ejercicios'] ?? [];

                    return collect($ejercicios)->map(function ($ej) {
                        if (is_string($ej)) {
                            return $ej;
                        }

                        return $ej['nombre'] ?? $ej['name'] ?? $ej['ejercicio'] ?? null;
                    });
                });
            })
            ->filter(fn ($n) => ! empty(trim($n ?? '')))
            ->unique()
            ->values();
    }

    // ─── Table setup ─────────────────────────────────────────────────────────

    private function ensureTable(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS exercise_name_gif_map (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre_plan   VARCHAR(500) NOT NULL,
            fitcron_slug  VARCHAR(500) NULL,
            gif_filename  VARCHAR(500) NULL,
            score         FLOAT        NULL,
            method        VARCHAR(50)  NULL DEFAULT \'none\',
            created_at    TIMESTAMP    NULL,
            UNIQUE KEY uq_nombre_plan (nombre_plan(255))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }
}
