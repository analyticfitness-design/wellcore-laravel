<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DumpSilviaExercises extends Command
{
    protected $signature = 'wellcore:silvia-exercises';
    protected $description = 'Dump current exercise names from Silvia plan id=48';

    public function handle(): int
    {
        $row = DB::table('assigned_plans')->where('id', 48)->first();
        $decoded = json_decode($row->content, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        $out = "FULL STRUCTURE KEYS: " . implode(', ', array_keys($decoded)) . "\n\n";

        // The plan key could be 'plan', 'semanas', 'weeks' array, etc.
        $planData = $decoded['plan'] ?? $decoded['semanas'] ?? null;

        if ($planData === null) {
            // Maybe weeks is actually an array despite being labeled as int
            foreach ($decoded as $key => $val) {
                if (is_array($val)) {
                    $out .= "KEY [{$key}] is array, count=" . count($val) . "\n";
                    $out .= json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
                }
            }
        } else {
            $out .= json_encode($planData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }

        file_put_contents(public_path('wc_check.txt'), $out);
        return self::SUCCESS;
    }
}
