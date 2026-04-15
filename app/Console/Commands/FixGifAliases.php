<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixGifAliases extends Command
{
    protected $signature = 'wellcore:fix-gif-aliases';
    protected $description = 'Fix specific exercise→GIF mappings in existing plans';

    private const CDN = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

    private const FIXES = [
        'patada de gluteo en polea' => 'patada-trasera-en-polea.gif',
        'patada de glúteo en polea' => 'patada-trasera-en-polea.gif',
        'abductor en polea' => 'patada-lateral-en-polea.gif',
        'abduccion en polea' => 'patada-lateral-en-polea.gif',
        'remo con mancuerna un brazo en banco' => 'remo-con-mancuerna-a-una mano.gif',
        'remo con mancuerna a un brazo' => 'remo-con-mancuerna-a-una mano.gif',
        'face pull en polea alta con cuerda' => 'facepull-en-polea.gif',
        'face pull con cuerda' => 'facepull-en-polea.gif',
        'face pull' => 'facepull-en-polea.gif',
        'facepull' => 'facepull-en-polea.gif',
        'romanian deadlift con mancuernas' => 'peso-muerto-rumano-con-mancuerna.gif',
        'romanian deadlift con mancuerna' => 'peso-muerto-rumano-con-mancuerna.gif',
        'rdl con mancuernas' => 'peso-muerto-rumano-con-mancuerna.gif',
        'zancada con mancuerna' => 'zancada-frontal-con-mancuerna.gif',
        'zancada con mancuernas' => 'zancada-frontal-con-mancuerna.gif',
        'press en banco inclinado con mancuerna' => 'press-de-banca-con-mancuernas.gif',
        'press en banco inclinado con mancuernas' => 'press-de-banca-con-mancuernas.gif',
        'press inclinado con mancuerna' => 'press-de-banca-con-mancuernas.gif',
        'press inclinado con mancuernas' => 'press-de-banca-con-mancuernas.gif',
        'extension de triceps en polea alta' => 'extension-de-triceps-en-polea-con-cuerda.gif',
        'extensión de tríceps en polea alta' => 'extension-de-triceps-en-polea-con-cuerda.gif',
        'sentadilla búlgara con mancuerna' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla bulgara con mancuerna' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla búlgara con mancuernas' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla bulgara con mancuernas' => 'sentadilla-bulgara-mancuerna.gif',
        'extension de cuádriceps en maquina' => 'extension-de-piernas-en-maquina.gif',
        'extension de cuadriceps en maquina' => 'extension-de-piernas-en-maquina.gif',
        'extensión de cuádriceps en máquina' => 'extension-de-piernas-en-maquina.gif',
        'zancada reversa con mancuerna' => 'zancada-inversa-con-mancuernas.gif',
        'zancada reversa con mancuernas' => 'zancada-inversa-con-mancuernas.gif',
    ];

    public function handle(): int
    {
        $normFixes = [];
        foreach (self::FIXES as $k => $v) {
            $normFixes[$this->norm($k)] = $v;
        }

        $total = 0;

        // assigned_plans
        $this->info('── ASSIGNED PLANS ──');
        $plans = DB::table('assigned_plans')
            ->where('plan_type', 'entrenamiento')
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get();

        foreach ($plans as $p) {
            $content = json_decode($p->content, true);
            if (!$content) continue;
            $before = $total;
            $this->fixPlan($content, $normFixes, $total);
            if ($total > $before) {
                $count = $total - $before;
                $this->line("Plan #{$p->id} (client {$p->client_id}): {$count} fixed");
                DB::table('assigned_plans')->where('id', $p->id)->update([
                    'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }

        // rise_programs
        $this->info('── RISE PROGRAMS ──');
        $rises = DB::table('rise_programs')
            ->whereNotNull('personalized_program')
            ->where('personalized_program', '!=', '')
            ->get();

        foreach ($rises as $r) {
            $prog = json_decode($r->personalized_program, true);
            if (!$prog || empty($prog['plan_entrenamiento'])) continue;
            $before = $total;
            $this->fixPlan($prog['plan_entrenamiento'], $normFixes, $total);
            if ($total > $before) {
                $count = $total - $before;
                $this->line("RISE #{$r->id} (client {$r->client_id}): {$count} fixed");
                DB::table('rise_programs')->where('id', $r->id)->update([
                    'personalized_program' => json_encode($prog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }

        $this->info("TOTAL: {$total} exercises fixed");
        return 0;
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    }

    private function fixPlan(array &$plan, array $normFixes, int &$total): void
    {
        if (!empty($plan['semanas'])) {
            foreach ($plan['semanas'] as &$sem) {
                if (!is_array($sem)) continue;
                foreach ($sem['dias'] ?? [] as &$dia) {
                    if (!is_array($dia)) continue;
                    if (!empty($dia['ejercicios'])) {
                        $this->fixExercises($dia['ejercicios'], $normFixes, $total);
                    }
                }
                unset($dia);
            }
            unset($sem);
        }
        if (!empty($plan['dias'])) {
            foreach ($plan['dias'] as &$dia) {
                if (!is_array($dia)) continue;
                if (!empty($dia['ejercicios'])) {
                    $this->fixExercises($dia['ejercicios'], $normFixes, $total);
                }
            }
            unset($dia);
        }
    }

    private function fixExercises(array &$exercises, array $normFixes, int &$total): void
    {
        foreach ($exercises as &$ej) {
            if (!is_array($ej) || empty($ej['nombre'])) continue;
            $key = $this->norm($ej['nombre']);
            if (isset($normFixes[$key])) {
                $ej['gif_url'] = self::CDN . $normFixes[$key];
                unset($ej['gif_filename']);
                $this->line("  ✅ {$ej['nombre']} → {$normFixes[$key]}");
                $total++;
            }
        }
        unset($ej);
    }
}
