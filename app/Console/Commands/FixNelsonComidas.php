<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class FixNelsonComidas extends Command
{
    protected $signature = 'wellcore:fix-nelson-comidas';

    protected $description = 'Convierte comidas_sugeridas de Nelson al formato opciones[] que usa ProgramView.vue';

    public function handle(): int
    {
        $rp = DB::table('rise_programs')->where('client_id', 63)->first();

        if (! $rp) {
            $this->error('No se encontró rise_program para Nelson (client_id=63)');
            return self::FAILURE;
        }

        $data   = json_decode($rp->personalized_program, true);
        $comidas = $data['plan_nutricion']['comidas_sugeridas'] ?? [];

        $nuevas = [];
        foreach ($comidas as $c) {
            // Ya tiene opciones — no tocar
            if (! empty($c['opciones'])) {
                $nuevas[] = $c;
                continue;
            }

            $opciones = [];
            if (! empty($c['descripcion'])) {
                $opciones[] = $c['descripcion'];
            }
            if (! empty($c['notas'])) {
                $opciones[] = 'Nota: ' . $c['notas'];
            }

            $nuevas[] = [
                'nombre'   => $c['nombre'],
                'opciones' => $opciones,
            ];
        }

        $data['plan_nutricion']['comidas_sugeridas'] = $nuevas;

        DB::table('rise_programs')
            ->where('client_id', 63)
            ->update([
                'personalized_program' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

        $this->info('Nelson comidas corregidas: ' . count($nuevas) . ' comidas con formato opciones[]');

        foreach ($nuevas as $i => $c) {
            $this->line("  [{$i}] {$c['nombre']} — " . count($c['opciones']) . ' opciones');
        }

        return self::SUCCESS;
    }
}
