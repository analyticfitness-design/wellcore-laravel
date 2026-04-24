<?php

declare(strict_types=1);

namespace App\Console\Commands\Archive;

use Illuminate\Support\Facades\DB;

// ARCHIVED — contains hardcoded client_id=37 (Daniel Esparza). Run manually via tinker if needed.
final class FixDanielCiclo
{
    protected string $signature = 'wellcore:fix-daniel-ciclo';

    protected string $description = 'Corrige fases y labs del ciclo hormonal de Daniel Esparza (client_id=37)';

    public function handle(): int
    {
        $row = DB::table('assigned_plans')
            ->where('client_id', 37)
            ->where('plan_type', 'ciclo_hormonal')
            ->where('active', 1)
            ->first();

        if (! $row) {
            $this->error('No se encontró ciclo_hormonal para Daniel (client_id=37)');
            return self::FAILURE;
        }

        $data = json_decode($row->content, true);

        // ── FASES ──────────────────────────────────────────────────────────────
        // calendario_semanas tiene keys como: semanas_1_6, semanas_7_12, semanas_13_15
        // Cada valor tiene: compuesto => dosis, descripcion => "Fase de..."
        $fases = [];
        foreach ($data['calendario_semanas'] ?? [] as $key => $val) {
            // semanas_1_6  → "Semanas 1 - 6"
            // semanas_7_12 → "Semanas 7 - 12"
            $nombre = preg_replace_callback(
                '/^semanas_(\d+)_(\d+)$/',
                fn($m) => "Semanas {$m[1]} - {$m[2]}",
                $key
            );
            if ($nombre === $key) {
                // fallback si no matchea el patrón
                $nombre = ucwords(str_replace('_', ' ', $key));
            }

            $fases[] = [
                'nombre'      => $nombre,
                'duracion'    => $nombre,
                'descripcion' => $val['descripcion'] ?? '',
                'notas'       => $val['descripcion'] ?? '',
            ];
        }

        $data['fases'] = $fases;
        $this->info('Fases generadas: ' . count($fases));
        foreach ($fases as $f) {
            $this->line("  • {$f['nombre']}");
        }

        // ── LABS ───────────────────────────────────────────────────────────────
        // analisis_recomendados tiene keys: antes_del_ciclo, semana_4_durante, etc.
        // Cada valor es array de strings (nombres de análisis)
        $cuandoMap = [
            'antes_del_ciclo'   => 'Antes del ciclo',
            'semana_4_durante'  => 'Semana 4 (durante ciclo)',
            'semana_8_durante'  => 'Semana 8 (durante ciclo)',
            'post_pct_semana_4' => 'Post-PCT Semana 4',
        ];

        $labs = [];
        foreach ($data['analisis_recomendados'] ?? [] as $key => $items) {
            $cuando = $cuandoMap[$key] ?? ucwords(str_replace('_', ' ', $key));
            if (! is_array($items)) {
                continue;
            }
            foreach ($items as $item) {
                if (is_string($item) && $item !== '') {
                    $labs[] = [
                        'nombre' => $item,
                        'cuando' => $cuando,
                    ];
                }
            }
        }

        $data['labs'] = $labs;
        $this->info('Labs generados: ' . count($labs));
        foreach ($labs as $l) {
            $this->line("  • [{$l['cuando']}] {$l['nombre']}");
        }

        DB::table('assigned_plans')
            ->where('client_id', 37)
            ->where('plan_type', 'ciclo_hormonal')
            ->where('active', 1)
            ->update([
                'content' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

        $this->info('✓ Ciclo de Daniel actualizado correctamente.');

        return self::SUCCESS;
    }
}
