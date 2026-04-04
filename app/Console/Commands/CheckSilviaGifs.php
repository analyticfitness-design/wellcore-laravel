<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CheckSilviaGifs extends Command
{
    protected $signature = 'wellcore:check-silvia-gifs';
    protected $description = 'Verifica qué ejercicios de Silvia tienen GIF y cuáles no, y muestra el alias normalizado';

    public function handle(): int
    {
        $row = DB::table('assigned_plans')->where('id', 104)->first();
        $data = json_decode($row->content, true);
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $semana1 = $data['semanas'][0];
        $out = "=== DIAGNÓSTICO GIFs SILVIA (plan id=104) ===\n\n";

        $totalOk  = 0;
        $totalFail = 0;
        $failList  = [];

        foreach ($semana1['dias'] as $dia) {
            $out .= "## {$dia['nombre']}\n";
            foreach ($dia['ejercicios'] as $ej) {
                $nombre = $ej['nombre'];
                $alias  = $this->normalize($nombre);

                $row = DB::table('exercise_aliases')
                    ->where('alias', $alias)
                    ->whereNotNull('gif_filename')
                    ->first();

                if ($row) {
                    $out .= "  ✓ {$nombre}\n";
                    $out .= "    alias='{$alias}' → gif={$row->gif_filename}\n";
                    $totalOk++;
                } else {
                    // Try without gif_filename constraint
                    $rowAny = DB::table('exercise_aliases')
                        ->where('alias', $alias)
                        ->first();

                    if ($rowAny) {
                        $out .= "  ~ {$nombre}\n";
                        $out .= "    alias='{$alias}' → ALIAS EXISTE pero gif_filename=NULL, fitcron_slug={$rowAny->fitcron_slug}\n";
                    } else {
                        $out .= "  ✗ {$nombre}\n";
                        $out .= "    alias='{$alias}' → SIN ALIAS EN DB\n";
                    }
                    $totalFail++;
                    $failList[] = $nombre;
                }
            }
            $out .= "\n";
        }

        $out .= "RESUMEN: {$totalOk} con GIF | {$totalFail} sin GIF\n";
        if (!empty($failList)) {
            $out .= "\nSIN GIF:\n";
            foreach ($failList as $n) {
                $out .= "  - {$n}\n";
            }
        }

        file_put_contents(public_path('wc_check.txt'), $out);
        $this->info($out);
        return self::SUCCESS;
    }

    private function normalize(string $nombre): string
    {
        $s = mb_strtolower($nombre);
        $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);
        $s = preg_replace('/\([^)]*\)/', ' ', $s);
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }
}
