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
        $out = '';

        // Client info
        $client = DB::table('clients')->where('id', 54)->first();
        $out .= "CLIENTE: {$client->name} | {$client->email} | plan:{$client->plan}\n\n";

        // Dump ALL active plans - raw JSON first 2000 chars
        $plans = DB::table('assigned_plans')->where('client_id', 54)->where('active', 1)->get();
        foreach ($plans as $p) {
            $out .= "=== PLAN id={$p->id} type={$p->plan_type} ===\n";
            $decoded = json_decode($p->content, true);
            // Handle double-encoded JSON
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $d = is_array($decoded) ? $decoded : [];
            $topKeys = implode(', ', array_keys($d));
            $out .= "Keys: {$topKeys}\n";

            // Raw first 1500 chars of JSON for inspection
            $raw = substr($p->content ?? '', 0, 1500);
            $out .= "RAW: {$raw}\n";
            $out .= "---\n";
        }

        // Check client_profiles
        try {
            $profile = DB::table('client_profiles')->where('client_id', 54)->first();
            if ($profile) {
                $out .= 'PERFIL: ' . json_encode((array) $profile, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                $out .= "PERFIL: no encontrado\n";
            }
        } catch (\Throwable $e) {
            $out .= 'PERFIL_ERR: ' . $e->getMessage() . "\n";
        }

        // Check measurements
        try {
            $meas = DB::table('client_measurements')->where('client_id', 54)->orderByDesc('id')->first();
            if ($meas) {
                $out .= 'MEDIDA: ' . json_encode((array) $meas, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                $out .= "MEDIDA: no encontrada\n";
            }
        } catch (\Throwable $e) {
            $out .= 'MEDIDA_ERR: ' . $e->getMessage() . "\n";
        }

        // Check intake forms / onboarding
        try {
            $intake = DB::table('client_intakes')->where('client_id', 54)->first();
            if ($intake) {
                $out .= 'INTAKE: ' . json_encode((array) $intake, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch (\Throwable) {}

        // Check rise_programs
        try {
            $rp = DB::table('rise_programs')->where('client_id', 54)->first();
            if ($rp) {
                $out .= 'RISE: found id=' . $rp->id . "\n";
            }
        } catch (\Throwable) {}

        file_put_contents(public_path('wc_check.txt'), $out);
        $this->info($out);

        return self::SUCCESS;
    }
}
