<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class MigrateGifNames extends Command
{
    protected $signature = 'wellcore:migrate-gif-names
        {--dry-run : Preview changes without updating the database}
        {--mapping= : Path to gif-mapping.json}
        {--aliases= : Path to gif-aliases.json}';

    protected $description = 'Migrate GIF filenames from English to Spanish and load aliases';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $mappingPath = $this->option('mapping') ?: base_path('scripts/gif-mapping.json');
        $aliasesPath = $this->option('aliases') ?: base_path('scripts/gif-aliases.json');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        // ── Step 1: Load mapping (old_filename → new_filename) ──
        if (! file_exists($mappingPath)) {
            $this->error("Mapping file not found: {$mappingPath}");
            return self::FAILURE;
        }
        $mapping = json_decode(file_get_contents($mappingPath), true);
        $this->info('Loaded mapping: ' . count($mapping) . ' entries');

        // ── Step 2: Update ejercicios_fitcron.gif_filename ──
        $this->line('');
        $this->info('Updating ejercicios_fitcron...');
        $fitcronUpdated = 0;

        foreach ($mapping as $oldName => $newName) {
            if ($oldName === $newName) {
                continue;
            }

            $affected = 0;
            if (! $isDryRun) {
                $affected = DB::table('ejercicios_fitcron')
                    ->where('gif_filename', $oldName)
                    ->update(['gif_filename' => $newName]);
            } else {
                $affected = DB::table('ejercicios_fitcron')
                    ->where('gif_filename', $oldName)
                    ->count();
            }

            if ($affected > 0) {
                $fitcronUpdated += $affected;
                if ($isDryRun) {
                    $this->line("  would update: {$oldName} → {$newName} ({$affected} rows)");
                }
            }
        }
        $this->info("ejercicios_fitcron: {$fitcronUpdated} rows " . ($isDryRun ? 'would be updated' : 'updated'));

        // ── Step 3: Update exercise_aliases.gif_filename ──
        $this->line('');
        $this->info('Updating exercise_aliases gif_filename...');
        $aliasesUpdated = 0;

        foreach ($mapping as $oldName => $newName) {
            if ($oldName === $newName) {
                continue;
            }

            $affected = 0;
            if (! $isDryRun) {
                $affected = DB::table('exercise_aliases')
                    ->where('gif_filename', $oldName)
                    ->update(['gif_filename' => $newName]);
            } else {
                $affected = DB::table('exercise_aliases')
                    ->where('gif_filename', $oldName)
                    ->count();
            }

            if ($affected > 0) {
                $aliasesUpdated += $affected;
            }
        }
        $this->info("exercise_aliases: {$aliasesUpdated} rows " . ($isDryRun ? 'would be updated' : 'updated'));

        // ── Step 4: Load new aliases from gif-aliases.json ──
        if (file_exists($aliasesPath)) {
            $this->line('');
            $this->info('Loading new aliases...');
            $aliasData = json_decode(file_get_contents($aliasesPath), true);
            $inserted = 0;
            $skipped = 0;

            foreach ($aliasData as $gifFilename => $data) {
                $aliases = $data['aliases'] ?? [];
                $oldFilename = $data['old_filename'] ?? $gifFilename;

                // Find fitcron_slug for this GIF (from the new filename)
                $fitcronSlug = DB::table('ejercicios_fitcron')
                    ->where('gif_filename', $gifFilename)
                    ->value('slug');

                foreach ($aliases as $alias) {
                    $alias = trim($alias);
                    if (strlen($alias) < 4) {
                        continue;
                    }

                    // Check if alias already exists
                    $exists = DB::table('exercise_aliases')
                        ->where('alias', $alias)
                        ->exists();

                    if ($exists) {
                        // Update to point to this GIF if it was pointing to a different one
                        if (! $isDryRun) {
                            DB::table('exercise_aliases')
                                ->where('alias', $alias)
                                ->update([
                                    'gif_filename' => $gifFilename,
                                    'fitcron_slug' => $fitcronSlug,
                                ]);
                        }
                        $skipped++;
                        continue;
                    }

                    if (! $isDryRun) {
                        DB::table('exercise_aliases')->insert([
                            'alias' => $alias,
                            'gif_filename' => $gifFilename,
                            'fitcron_slug' => $fitcronSlug,
                        ]);
                    }
                    $inserted++;
                }
            }

            $this->info("New aliases inserted: {$inserted}");
            $this->info("Existing aliases updated: {$skipped}");
        } else {
            $this->warn("Aliases file not found: {$aliasesPath} — skipping alias load");
        }

        $this->line('');
        $this->info('Done!');

        return self::SUCCESS;
    }
}
