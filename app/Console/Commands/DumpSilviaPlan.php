<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class DumpSilviaPlan extends Command
{
    protected $signature = 'wellcore:dump-silvia';
    protected $description = 'Dump Silvia plan for inspection';

    public function handle(): int
    {
        // Client info
        $client = DB::table('clients')->where('id', 54)->first();
        $out = "CLIENTE: {$client->name} | {$client->email} | plan:{$client->plan}\n\n";

        // All active plans
        $plans = DB::table('assigned_plans')->where('client_id', 54)->where('active', 1)->get();
        foreach ($plans as $p) {
            $out .= "=== PLAN id={$p->id} type={$p->plan_type} ===\n";
            $d = json_decode($p->content, true) ?? [];
            $topKeys = implode(', ', array_keys($d));
            $out .= "Keys: {$topKeys}\n";

            // Try weeks / semanas
            $weeks = $d['weeks'] ?? $d['semanas'] ?? [];
            $out .= 'Semanas/Weeks: ' . count($weeks) . "\n";

            if (!empty($weeks)) {
                $w1   = $weeks[0];
                $days = $w1['days'] ?? $w1['dias'] ?? [];
                foreach ($days as $day) {
                    $dayName = $day['name'] ?? $day['nombre'] ?? '?';
                    $exs     = $day['exercises'] ?? $day['ejercicios'] ?? [];
                    $names   = array_map(fn ($e) => $e['name'] ?? $e['nombre'] ?? '?', $exs);
                    $out .= "  [{$dayName}]: " . implode(', ', $names) . "\n";
                }
            }

            // Nutrition
            if ($p->plan_type === 'nutricion') {
                $out .= 'cal: ' . ($d['calorias_diarias'] ?? $d['objetivo_cal'] ?? 'N/A') . "\n";
                $out .= 'comidas: ' . count($d['comidas'] ?? $d['comidas_sugeridas'] ?? []) . "\n";
            }

            $out .= "\n";
        }

        // Check client_profiles for physical data
        try {
            $profile = DB::table('client_profiles')->where('client_id', 54)->first();
            if ($profile) {
                $out .= 'PERFIL: ' . json_encode((array) $profile, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (\Throwable) {}

        // Check measurements
        try {
            $meas = DB::table('client_measurements')->where('client_id', 54)->orderByDesc('id')->first();
            if ($meas) {
                $out .= 'MEDIDA: ' . json_encode((array) $meas, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (\Throwable) {}

        file_put_contents(public_path('wc_check.txt'), $out);
        $this->info($out);

        return self::SUCCESS;
    }
}
