<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time command: run storage/update_gifs_prod.sql statement-by-statement.
 * Needed because DB::unprepared() may not execute multi-statement SQL reliably.
 */
class SyncGifFilenames extends Command
{
    protected $signature = 'wellcore:sync-gif-filenames
                            {--dry-run : Show count only, do not update}';

    protected $description = 'Apply storage/update_gifs_prod.sql to ejercicios_fitcron (statement by statement).';

    public function handle(): int
    {
        $file = storage_path('../storage/update_gifs_prod.sql');

        // Try relative path if absolute fails
        if (! file_exists($file)) {
            $file = base_path('storage/update_gifs_prod.sql');
        }

        if (! file_exists($file)) {
            $this->error('SQL file not found: ' . $file);
            return self::FAILURE;
        }

        $before = DB::table('ejercicios_fitcron')->whereNotNull('gif_filename')->count();
        $this->info("Before: {$before} exercises with gif_filename.");

        if ($this->option('dry-run')) {
            $sql = file_get_contents($file);
            $count = count(array_filter(array_map('trim', explode(';', $sql))));
            $this->info("Would execute {$count} statements.");
            return self::SUCCESS;
        }

        $sql = file_get_contents($file);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $bar = $this->output->createProgressBar(count($statements));
        $bar->start();

        $updated = 0;
        foreach ($statements as $stmt) {
            if (! empty($stmt)) {
                DB::statement($stmt);
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $after = DB::table('ejercicios_fitcron')->whereNotNull('gif_filename')->count();
        $this->info("Done. Executed {$updated} statements.");
        $this->info("After: {$after} exercises with gif_filename.");

        return self::SUCCESS;
    }
}
