<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class FixGifMismatches extends Command
{
    protected $signature = 'wellcore:fix-gif-mismatches {--dry-run : Preview changes without updating the database}';

    protected $description = 'Fix incorrect GIF assignments in exercise_aliases for known mismatches';

    private const CORRECTIONS = [
        'vanessa_diaz' => [
            'prensa de pierna' => 'prensa de pierna',
            'jalon al pecho en polea alta' => 'jalon al pecho',
            'pajaro inclinada' => 'pajaro inclinado',
            'pull over' => 'pull over dumbbell',
            'patada de gluteo en maquina o polea' => 'patada de gluteo en maquina',
            'crunch con polea alta' => 'crunch en polea',
            'extension de cuadriceps' => 'extension de cuadriceps maquina',
            'patada de gluteo' => 'patada de gluteo',
            'elevacion de piernas en banco' => 'elevacion de piernas',
        ],
        'julie_rodriguez' => [
            'sentadilla goblet con mancuerna' => 'sentadilla goblet',
            'patada de gluteo en polea baja' => 'patada de gluteo en cable',
            'abductora en polea baja' => 'abduccion de cadera en cable',
            'puente gluteo con banda elastica' => 'puente de gluteo con banda',
            'elevacion de talones de pie' => 'elevacion de talones de pie',
            'press mancuernas sentado en banco' => 'press de mancuernas sentado',
            'sentadilla bulgara con mancuerna' => 'sentadilla bulgara',
            'extension de cuadriceps en polea' => 'extension de cuadriceps en cable',
            'elevacion de talones sentada' => 'elevacion de talones sentada',
            'kickback de gluteo con banda' => 'kickback de gluteo banda',
            'abduccion de cadera con banda' => 'abduccion de cadera con banda',
            'clamshell con banda' => 'clamshell',
        ],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be written to the database.');
        }

        $rows = [];

        foreach (self::CORRECTIONS as $group => $corrections) {
            $this->line('');
            $this->info("Processing: {$group}");

            foreach ($corrections as $exerciseName => $searchTerm) {
                $alias = $this->normalize($exerciseName);
                $gif = $this->findGifInFitcron($searchTerm);

                if ($gif === null) {
                    $gif = $this->findGifInAliases($searchTerm);
                }

                if ($gif === null) {
                    $rows[] = [$alias, '—', 'NOT FOUND'];

                    continue;
                }

                if (! $isDryRun) {
                    $this->upsertAlias($alias, $gif);
                }

                $rows[] = [$alias, $gif, $isDryRun ? 'would fix' : 'fixed'];
            }
        }

        $this->line('');
        $this->table(['alias', 'gif_filename', 'status'], $rows);

        $fixed = count(array_filter($rows, fn ($r) => str_contains($r[2], 'fix')));
        $notFound = count(array_filter($rows, fn ($r) => $r[2] === 'NOT FOUND'));

        $this->line('');
        $this->info("Done. Fixed: {$fixed} | Not found: {$notFound}");

        return self::SUCCESS;
    }

    /** @var array<string,string>|null */
    private ?array $fitcronIndex = null;

    private function fitcronIndex(): array
    {
        if ($this->fitcronIndex === null) {
            $this->fitcronIndex = [];
            DB::table('ejercicios_fitcron')
                ->whereNotNull('gif_filename')
                ->where('gif_filename', '!=', '')
                ->select('nombre', 'gif_filename')
                ->orderBy('id')
                ->each(function ($row) {
                    $this->fitcronIndex[$this->normalize($row->nombre)] = $row->gif_filename;
                });
        }

        return $this->fitcronIndex;
    }

    private function findGifInFitcron(string $searchTerm): ?string
    {
        $index = $this->fitcronIndex();
        $normalizedTerm = $this->normalize($searchTerm);

        // Exact match first
        if (isset($index[$normalizedTerm])) {
            return $index[$normalizedTerm];
        }

        // Contains full phrase
        foreach ($index as $normNombre => $gif) {
            if (str_contains($normNombre, $normalizedTerm)) {
                return $gif;
            }
        }

        // All keywords must be present in the nombre
        $keywords = array_filter(explode(' ', $normalizedTerm), fn ($k) => strlen($k) > 3);
        usort($keywords, fn ($a, $b) => strlen($b) <=> strlen($a));

        if (! empty($keywords)) {
            foreach ($index as $normNombre => $gif) {
                $allMatch = true;
                foreach ($keywords as $kw) {
                    if (! str_contains($normNombre, $kw)) {
                        $allMatch = false;
                        break;
                    }
                }
                if ($allMatch) {
                    return $gif;
                }
            }

            // Longest keyword match as last resort
            foreach ($keywords as $keyword) {
                foreach ($index as $normNombre => $gif) {
                    if (str_contains($normNombre, $keyword)) {
                        return $gif;
                    }
                }
            }
        }

        return null;
    }

    private function findGifInAliases(string $searchTerm): ?string
    {
        $normalizedTerm = $this->normalize($searchTerm);

        $gif = DB::table('exercise_aliases')
            ->whereNotNull('gif_filename')
            ->where('gif_filename', '!=', '')
            ->where('alias', 'LIKE', "%{$normalizedTerm}%")
            ->value('gif_filename');

        if ($gif !== null) {
            return $gif;
        }

        $keywords = array_filter(explode(' ', $normalizedTerm), fn ($k) => strlen($k) > 3);
        usort($keywords, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($keywords as $keyword) {
            $gif = DB::table('exercise_aliases')
                ->whereNotNull('gif_filename')
                ->where('gif_filename', '!=', '')
                ->where('alias', 'LIKE', "%{$keyword}%")
                ->value('gif_filename');

            if ($gif !== null) {
                return $gif;
            }
        }

        return null;
    }

    private function upsertAlias(string $alias, string $gifFilename): void
    {
        $exists = DB::table('exercise_aliases')->where('alias', $alias)->exists();

        if ($exists) {
            DB::table('exercise_aliases')
                ->where('alias', $alias)
                ->update([
                    'gif_filename' => $gifFilename,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('exercise_aliases')->insert([
            'alias' => $alias,
            'gif_filename' => $gifFilename,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function normalize(string $name): string
    {
        $name = preg_replace('/\([^)]*\)/', ' ', $name);
        $name = mb_strtolower(trim($name));
        $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'];
        $name = strtr($name, $map);
        $name = preg_replace('/[^a-z0-9\s]/', ' ', $name);

        return preg_replace('/\s+/', ' ', trim($name));
    }
}
