<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Diagnose and fix GIFs for Julie Rodriguez's training plan.
 * Run: php artisan wellcore:fix-julie-gifs
 * Run (dry): php artisan wellcore:fix-julie-gifs --dry-run
 */
class FixJulieGifs extends Command
{
    protected $signature   = 'wellcore:fix-julie-gifs {--dry-run : Show changes without saving}';
    protected $description = 'Map GIFs to all exercises in Julie Rodriguez\'s active training plan.';

    public function handle(): int
    {
        // ── 1. Find client ────────────────────────────────────────────────────
        $client = DB::selectOne(
            "SELECT id, name, email FROM clients
             WHERE name LIKE '%julie%' OR name LIKE '%juliana%' OR name LIKE '%julia%'
             ORDER BY id LIMIT 1"
        );

        if (! $client) {
            $this->error('Client Julie/Juliana/Julia not found.');
            return 1;
        }
        $this->info("Client: [{$client->id}] {$client->name} — {$client->email}");

        // ── 2. Load active training plan ──────────────────────────────────────
        $plan = DB::selectOne(
            "SELECT id, content FROM assigned_plans
             WHERE client_id = ? AND plan_type = 'entrenamiento' AND active = 1
             ORDER BY id DESC LIMIT 1",
            [$client->id]
        );

        if (! $plan) {
            $this->error('No active training plan found.');
            return 1;
        }
        $this->info("Plan ID: {$plan->id}");

        $content = is_array($plan->content)
            ? $plan->content
            : json_decode($plan->content, true);

        if (! $content) {
            $this->error('Plan content is empty or invalid JSON.');
            return 1;
        }

        // ── 3. Load GIF alias table ───────────────────────────────────────────
        $aliases = DB::select(
            "SELECT alias, gif_filename FROM exercise_aliases WHERE gif_filename IS NOT NULL"
        );
        $gifMap = [];
        foreach ($aliases as $a) {
            $gifMap[$this->normalize($a->alias)] = $a->gif_filename;
        }
        $this->info('GIF aliases loaded: ' . count($gifMap));

        // ── 4. Walk exercises and map GIFs ────────────────────────────────────
        $updated = 0;
        $missing = [];

        $dias = $content['dias'] ?? [];

        foreach ($dias as $dIdx => &$dia) {
            $ejercicios = $dia['ejercicios'] ?? [];
            foreach ($ejercicios as $eIdx => &$ej) {
                $name    = $ej['nombre'] ?? $ej['name'] ?? '';
                $normKey = $this->normalize($name);

                // Already has a valid GIF?
                $current = $ej['gif_url'] ?? null;

                // Try exact match first
                $gif = $gifMap[$normKey] ?? null;

                // Fuzzy fallback — best token overlap
                if (! $gif) {
                    $gif = $this->fuzzyMatch($normKey, $gifMap);
                }

                if ($gif) {
                    $gifUrl = "https://www.wellcorefitness.com/storage/exercise-gifs/{$gif}";
                    if ($current !== $gifUrl) {
                        $this->line("  ✓ [{$dIdx}][{$eIdx}] {$name}");
                        $this->line("      old: " . ($current ?: '(none)'));
                        $this->line("      new: {$gifUrl}");
                        $ej['gif_url'] = $gifUrl;
                        $updated++;
                    } else {
                        $this->line("  = [{$dIdx}][{$eIdx}] {$name} (already correct)");
                    }
                } else {
                    $missing[] = $name;
                    $this->warn("  ✗ [{$dIdx}][{$eIdx}] {$name} — NO GIF FOUND");
                }
            }
            unset($ej);
            $dia['ejercicios'] = $ejercicios;
        }
        unset($dia);
        $content['dias'] = $dias;

        // ── 5. Summary ────────────────────────────────────────────────────────
        $this->newLine();
        $this->info("Updated: {$updated} exercises");
        $this->warn('Missing GIFs (' . count($missing) . '):');
        foreach ($missing as $m) {
            $this->line("  - {$m}");
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes saved.');
            return 0;
        }

        // ── 6. Save ───────────────────────────────────────────────────────────
        DB::update(
            "UPDATE assigned_plans SET content = ? WHERE id = ?",
            [json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $plan->id]
        );
        $this->info('Plan saved successfully.');

        return 0;
    }

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = strtr($name, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
        return preg_replace('/\s+/', ' ', $name);
    }

    private function fuzzyMatch(string $query, array $gifMap): ?string
    {
        $queryWords = array_filter(explode(' ', $query), fn ($w) => strlen($w) > 2);
        if (empty($queryWords)) {
            return null;
        }

        $bestScore = 0;
        $bestGif   = null;

        foreach ($gifMap as $alias => $gif) {
            $aliasWords = array_filter(explode(' ', $alias), fn ($w) => strlen($w) > 2);
            if (empty($aliasWords)) {
                continue;
            }

            $common = count(array_intersect($queryWords, $aliasWords));
            $score  = $common / max(count($queryWords), count($aliasWords));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestGif   = $gif;
            }
        }

        return $bestScore >= 0.40 ? $bestGif : null;
    }
}
