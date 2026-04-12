<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sync the 464-GIF catalog into the database.
 *
 * Three actions in one command:
 *   1. Update ejercicios_fitcron.gif_filename → match exercise names to the
 *      current set of 464 Spanish-named GIFs in scripts/gif-catalog.json
 *   2. Load all pre-computed aliases from scripts/gif-aliases.json into
 *      exercise_aliases (source='canonical')
 *   3. Load manual override aliases from scripts/gif-manual-aliases.json into
 *      exercise_aliases (source='manual', score=1.0) — runs LAST to override bad fuzzy matches
 *
 * Usage:
 *   php artisan wellcore:sync-gif-catalog           # run + save
 *   php artisan wellcore:sync-gif-catalog --dry-run # preview only
 *   php artisan wellcore:sync-gif-catalog --aliases-only  # only load aliases JSON + manual
 *   php artisan wellcore:sync-gif-catalog --fitcron-only  # only update gif_filename
 */
class SyncGifCatalog extends Command
{
    protected $signature = 'wellcore:sync-gif-catalog
        {--dry-run      : Preview without writing to DB}
        {--aliases-only : Only load aliases from JSON, skip fitcron update}
        {--fitcron-only : Only update gif_filename, skip alias load}
        {--catalog=     : Path to gif-catalog.json}
        {--aliases=     : Path to gif-aliases.json}
        {--manual=      : Path to gif-manual-aliases.json}
        {--threshold=0.35 : Minimum score to accept a fuzzy match (0-1)}';

    protected $description = 'Sync 464-GIF catalog: update gif_filename in fitcron + load aliases JSON + manual overrides';

    private const STOPWORDS = ['con', 'en', 'de', 'la', 'el', 'los', 'las', 'un', 'una', 'a', 'al', 'del', 'y', 'o'];

    // Spanish plural → singular mappings for better matching
    private const EN_ES_MANUAL = [
        'russian twist'    => 'giro ruso',
        'facepull'         => 'deltoides posterior remo cuerda',
        'face pull'        => 'deltoides posterior remo cuerda',
        'rear delt'        => 'deltoides posterior',
        'good morning'     => 'buenos dias',
        'romanian deadlift'=> 'rumano peso muerto',
        'rdl'              => 'rumano peso muerto',
        'kickback gluteo'  => 'posterior patada',
        'glute kickback'   => 'posterior patada',
        'clamshell'        => 'lateral abduccion cadera',
        'hip abduction'    => 'abduccion cadera',
        'isquiotibiales'   => 'curl pierna',
        'femoral'          => 'curl pierna',
        'hacka'            => 'sentadilla hack',
    ];

    public function handle(): int
    {
        $isDry        = $this->option('dry-run');
        $aliasesOnly  = $this->option('aliases-only');
        $fitcronOnly  = $this->option('fitcron-only');
        $threshold    = (float) $this->option('threshold');

        $catalogPath  = $this->option('catalog') ?: base_path('scripts/gif-catalog.json');
        $aliasesPath  = $this->option('aliases') ?: base_path('scripts/gif-aliases.json');
        $manualPath   = $this->option('manual')  ?: base_path('scripts/gif-manual-aliases.json');

        if ($isDry) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        // ── Load catalog ──────────────────────────────────────────────────────
        if (! file_exists($catalogPath)) {
            $this->error("gif-catalog.json not found: {$catalogPath}");
            return self::FAILURE;
        }
        $catalog = json_decode(file_get_contents($catalogPath), true);
        $this->info('Catalog loaded: ' . count($catalog) . ' GIFs');

        // Pre-build a normalized lookup for the catalog
        // [normalized_display_name => gif_filename]
        $catalogByNorm = [];
        foreach ($catalog as $filename) {
            $norm = $this->normalizeFilename($filename);
            $catalogByNorm[$norm] = $filename;
        }

        // ── Step 1: Update ejercicios_fitcron.gif_filename ───────────────────
        if (! $aliasesOnly) {
            $this->syncFitcronFilenames($catalogByNorm, $catalog, $threshold, $isDry);
        }

        // ── Step 2: Load canonical aliases from gif-aliases.json ─────────────
        if (! $fitcronOnly && file_exists($aliasesPath)) {
            $this->loadAliases($aliasesPath, $isDry);
        } elseif (! $fitcronOnly) {
            $this->warn("gif-aliases.json not found: {$aliasesPath} — skipping alias load");
        }

        // ── Step 3: Load manual override aliases (always last, source=manual) ─
        if (! $fitcronOnly && file_exists($manualPath)) {
            $this->loadManualAliases($manualPath, $isDry);
        } elseif (! $fitcronOnly) {
            $this->warn("gif-manual-aliases.json not found: {$manualPath} — skipping manual overrides");
        }

        $this->info('Done!');
        return self::SUCCESS;
    }

    // ─── Fitcron sync ─────────────────────────────────────────────────────────

    private function syncFitcronFilenames(array $catalogByNorm, array $catalog, float $threshold, bool $isDry): void
    {
        $this->line('');
        $this->info('Updating ejercicios_fitcron.gif_filename...');

        $exercises = DB::table('ejercicios_fitcron')
            ->select('id', 'slug', 'nombre', 'gif_filename')
            ->get();

        $updated   = 0;
        $alreadyOk = 0;
        $noMatch   = 0;
        $noMatchList = [];

        foreach ($exercises as $ex) {
            $currentFile  = $ex->gif_filename;
            $normName     = $this->normalizeName($ex->nombre);

            // 1. Current filename already in catalog — nothing to do
            if ($currentFile && in_array($currentFile, $catalog, true)) {
                $alreadyOk++;
                continue;
            }

            // 2. Exact match by normalized exercise name = normalized filename
            if (isset($catalogByNorm[$normName])) {
                $newFile = $catalogByNorm[$normName];
                if (! $isDry) {
                    DB::table('ejercicios_fitcron')
                        ->where('id', $ex->id)
                        ->update(['gif_filename' => $newFile]);
                }
                $updated++;
                if ($isDry) {
                    $this->line("  [EXACT] {$ex->nombre} → {$newFile}");
                }
                continue;
            }

            // 3. Fuzzy match against catalog
            $best = $this->fuzzyMatch($normName, $catalogByNorm, $threshold);
            if ($best) {
                if (! $isDry) {
                    DB::table('ejercicios_fitcron')
                        ->where('id', $ex->id)
                        ->update(['gif_filename' => $best['file']]);
                }
                $updated++;
                if ($isDry) {
                    $this->line("  [FUZZY {$best['score']}] {$ex->nombre} → {$best['file']}");
                }
            } else {
                $noMatch++;
                $noMatchList[] = $ex->nombre . ($currentFile ? " (tenía: {$currentFile})" : '');
            }
        }

        $this->info("  Already OK (file in catalog): {$alreadyOk}");
        $this->info("  Updated: {$updated}" . ($isDry ? ' (would be)' : ''));
        $this->info("  No match found: {$noMatch}");

        if ($noMatch > 0 && count($noMatchList) <= 30) {
            $this->newLine();
            $this->warn('Exercises without a GIF match in the 464 catalog:');
            foreach ($noMatchList as $name) {
                $this->line("  - {$name}");
            }
        } elseif ($noMatch > 30) {
            $this->warn("  ({$noMatch} exercises without match — run with --dry-run to see full list)");
        }
    }

    // ─── Aliases loader ───────────────────────────────────────────────────────

    private function loadAliases(string $aliasesPath, bool $isDry): void
    {
        $this->line('');
        $this->info('Loading aliases from gif-aliases.json...');

        $aliasData = json_decode(file_get_contents($aliasesPath), true);

        $inserted = 0;
        $updated  = 0;

        foreach ($aliasData as $gifFilename => $data) {
            $aliases = $data['aliases'] ?? [];
            if (empty($aliases)) {
                continue;
            }

            // Find the fitcron_slug for this gif_filename
            $fitcronSlug = DB::table('ejercicios_fitcron')
                ->where('gif_filename', $gifFilename)
                ->value('slug');

            foreach ($aliases as $alias) {
                $alias = trim($alias);
                if (strlen($alias) < 4) {
                    continue;
                }

                if ($isDry) {
                    $inserted++;
                    continue;
                }

                $exists = DB::table('exercise_aliases')->where('alias', $alias)->first();

                if ($exists) {
                    DB::table('exercise_aliases')
                        ->where('alias', $alias)
                        ->update([
                            'gif_filename'  => $gifFilename,
                            'fitcron_slug'  => $fitcronSlug,
                            'score'         => 1.0,
                            'source'        => 'canonical',
                        ]);
                    $updated++;
                } else {
                    DB::table('exercise_aliases')->insert([
                        'alias'         => $alias,
                        'alias_display' => $alias,
                        'gif_filename'  => $gifFilename,
                        'fitcron_slug'  => $fitcronSlug,
                        'score'         => 1.0,
                        'source'        => 'canonical',
                        'created_at'    => now(),
                    ]);
                    $inserted++;
                }
            }
        }

        $this->info("  Aliases inserted: {$inserted}" . ($isDry ? ' (dry run)' : ''));
        $this->info("  Aliases updated:  {$updated}");
    }

    // ─── Manual aliases loader ────────────────────────────────────────────────

    /**
     * Load gif-manual-aliases.json: { "alias display name" => "gif_filename.gif" }
     * These are explicit corrections that override any auto/canonical alias.
     * Loaded LAST so source='manual' wins in ExerciseMediaService hash lookup.
     */
    private function loadManualAliases(string $manualPath, bool $isDry): void
    {
        $this->line('');
        $this->info('Loading manual override aliases from gif-manual-aliases.json...');

        $raw = json_decode(file_get_contents($manualPath), true);

        // Strip the _comment key if present
        unset($raw['_comment']);

        $inserted = 0;
        $updated  = 0;

        foreach ($raw as $displayName => $gifFilename) {
            $alias       = $this->normalizeName($displayName);
            $gifFilename = trim($gifFilename);

            if (strlen($alias) < 3) {
                continue;
            }

            // Find the fitcron_slug for this gif_filename
            $fitcronSlug = DB::table('ejercicios_fitcron')
                ->where('gif_filename', $gifFilename)
                ->value('slug');

            if ($isDry) {
                $this->line("  [MANUAL] {$alias} → {$gifFilename}" . ($fitcronSlug ? " ({$fitcronSlug})" : ' (no fitcron slug)'));
                $inserted++;
                continue;
            }

            $exists = DB::table('exercise_aliases')->where('alias', $alias)->first();

            if ($exists) {
                DB::table('exercise_aliases')
                    ->where('alias', $alias)
                    ->update([
                        'alias_display' => $displayName,
                        'gif_filename'  => $gifFilename,
                        'fitcron_slug'  => $fitcronSlug,
                        'score'         => 1.0,
                        'source'        => 'manual',
                    ]);
                $updated++;
            } else {
                DB::table('exercise_aliases')->insert([
                    'alias'         => $alias,
                    'alias_display' => $displayName,
                    'gif_filename'  => $gifFilename,
                    'fitcron_slug'  => $fitcronSlug,
                    'score'         => 1.0,
                    'source'        => 'manual',
                    'created_at'    => now(),
                ]);
                $inserted++;
            }
        }

        $this->info("  Manual aliases inserted: {$inserted}" . ($isDry ? ' (dry run)' : ''));
        $this->info("  Manual aliases updated:  {$updated}");
    }

    // ─── Fuzzy matching ───────────────────────────────────────────────────────

    private function fuzzyMatch(string $normName, array $catalogByNorm, float $threshold): ?array
    {
        $planWords = $this->keywords($normName);
        $best      = null;

        foreach ($catalogByNorm as $normFile => $filename) {
            $fileWords = $this->keywords($normFile);

            // Jaccard similarity on keywords
            $jaccard  = $this->jaccard($planWords, $fileWords);

            // Character-level similarity on full normalized strings
            similar_text($normName, $normFile, $pct);
            $sim = $pct / 100;

            $score = 0.55 * $jaccard + 0.45 * $sim;

            if ($score >= $threshold && ($best === null || $score > $best['score'])) {
                $best = ['file' => $filename, 'score' => round($score, 3)];
            }
        }

        return $best;
    }

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;
        $inter = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        return $union > 0 ? $inter / $union : 0.0;
    }

    // ─── Normalization helpers ────────────────────────────────────────────────

    /**
     * Normalize a Spanish GIF filename into a comparable string.
     * "barra-press-de-banca.gif" → "barra press banca"
     */
    private function normalizeFilename(string $filename): string
    {
        $name = str_replace(['.gif', '-'], [' ', ' '], $filename);
        return $this->normalizeName($name);
    }

    /**
     * Normalize a human-readable Spanish exercise name.
     * "Press de Banca con Barra (Plano)" → "press banca barra"
     * "Elevaciones Laterales con Mancuernas" → "elevacion lateral mancuerna"
     */
    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\([^)]*\)/', ' ', $name); // strip (...)
        $name = mb_strtolower(trim($name));
        $map  = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Apply manual EN→ES / alias translations first (longest match first)
        $translated = $name;
        foreach (self::EN_ES_MANUAL as $from => $to) {
            $translated = str_replace($from, $to, $translated);
        }
        if ($translated !== $name) {
            $name = preg_replace('/\s+/', ' ', trim($translated));
        }

        // Remove stopwords to improve matching
        $words = array_filter(
            explode(' ', $name),
            fn ($w) => strlen($w) > 1 && ! in_array($w, self::STOPWORDS, true)
        );

        // Basic Spanish stemming: reduce common plural forms to singular
        // elevaciones→elevacion, laterales→lateral, frontales→frontal, etc.
        $words = array_map(function (string $w): string {
            if (strlen($w) > 5) {
                if (str_ends_with($w, 'ciones')) return substr($w, 0, -2); // elevaciones→elevacion
                if (str_ends_with($w, 'iones'))  return substr($w, 0, -2); // extensiones→extension
                if (str_ends_with($w, 'ales'))   return substr($w, 0, -2); // laterales→lateral
                if (str_ends_with($w, 'ares'))   return substr($w, 0, -2); // musculares→muscular
                if (str_ends_with($w, 'eres'))   return substr($w, 0, -2); // estireres...
                if (str_ends_with($w, 'uras'))   return substr($w, 0, -1); // aperturas→apertura
                if (str_ends_with($w, 'ernas'))  return substr($w, 0, -1); // piernas→pierna
                if (str_ends_with($w, 'etas'))   return substr($w, 0, -1); // bicicletas→bicicleta
                if (str_ends_with($w, 'os') && strlen($w) > 5) return substr($w, 0, -1); // brazos→brazo
                if (str_ends_with($w, 'as') && strlen($w) > 5) return substr($w, 0, -1); // mancuernas→mancuerna
            }
            return $w;
        }, array_values($words));

        return implode(' ', $words);
    }

    private function keywords(string $norm): array
    {
        return array_unique(array_values(array_filter(
            explode(' ', $norm),
            fn ($w) => strlen($w) > 2 && ! in_array($w, self::STOPWORDS, true)
        )));
    }
}
