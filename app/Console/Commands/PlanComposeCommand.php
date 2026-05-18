<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ComposeEngine\ComposeEngine;
use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\DecisionEngine;
use Illuminate\Console\Command;
use JsonException;
use Throwable;

/**
 * plan:compose — Stage 3 COMPOSE del motor v2 (entrenamiento, Sprint 4).
 *
 * Orquesta DecisionEngine (Stage 2 SELECT) + ComposeEngine (Stage 3 COMPOSE).
 *
 * Modos:
 *   php artisan plan:compose --methodology=body_part_split_5d \
 *       --vertical=entrenamiento --goal=hipertrofia --level=intermedio --days=5
 *       Compone con metodología explícita.
 *
 *   php artisan plan:compose --auto \
 *       --vertical=entrenamiento --goal=hipertrofia --level=intermedio --days=5
 *       Usa DecisionEngine para escoger metodología top-1 automáticamente.
 *
 *   php artisan plan:compose --file=intake.json --out=plan.json
 *       Lee profile desde archivo, escribe plan JSON al --out.
 *
 * Salidas:
 *   stdout pretty por default · --json para JSON parseable · --out=path para grabar a archivo
 */
final class PlanComposeCommand extends Command
{
    protected $signature = 'plan:compose
                            {--file= : Path a JSON con el ClientProfile}
                            {--inline= : ClientProfile inline JSON}
                            {--vertical= : profile.vertical (sprint 4 solo entrenamiento)}
                            {--goal= : profile.goal}
                            {--level= : profile.level}
                            {--days= : profile.days (int)}
                            {--gender= : profile.gender}
                            {--equipment=gym_completo : equipo disponible (comma-separated)}
                            {--methodology= : slug de methodology explícita (saltea DecisionEngine)}
                            {--auto : usa DecisionEngine para escoger top-1}
                            {--client-name= : nombre cliente (string en titulo y notas_coach)}
                            {--coach-name= : nombre coach (string en notas_coach)}
                            {--fecha-inicio= : YYYY-MM-DD, default mañana}
                            {--out= : path donde guardar el JSON output}
                            {--json : output completo en JSON}';

    protected $description = 'Stage 3 COMPOSE del motor v2 — compone un plan de entrenamiento mensual.';

    public function handle(DecisionEngine $decision, ComposeEngine $compose): int
    {
        $profile = $this->buildProfile();
        if ($profile === null) {
            return 2;
        }

        if (! in_array($profile->vertical, ['entrenamiento', 'nutricion', 'suplementacion', 'habitos', 'ciclo'], true)) {
            $this->error("Verticales soportadas: entrenamiento, nutricion, suplementacion, habitos, ciclo. Recibido: '{$profile->vertical}'");
            return 2;
        }

        $methodologySlug = (string) ($this->option('methodology') ?? '');
        if ($methodologySlug === '' && $this->option('auto')) {
            $result = $decision->decide($profile);
            $trainRecs = $result->byVertical['entrenamiento'] ?? [];
            if ($trainRecs === []) {
                $this->error('DecisionEngine no encontró metodologías para el profile. Pasa --methodology explícita.');
                return 1;
            }
            $methodologySlug = $trainRecs[0]->methodologySlug;
            $this->info("DecisionEngine seleccionó: $methodologySlug (confidence " . $trainRecs[0]->confidence . ')');
        }

        if ($methodologySlug === '') {
            $this->error('Falta --methodology=<slug> o --auto');
            return 2;
        }

        $fechaInicio = (string) ($this->option('fecha-inicio') ?: now()->addDay()->toDateString());
        $clientName = $this->option('client-name');
        $coachName = $this->option('coach-name');
        $equipment = array_map('trim', explode(',', (string) ($this->option('equipment') ?: 'gym_completo')));

        try {
            $composeResult = $compose->composeForMethodology(
                $profile,
                $methodologySlug,
                $fechaInicio,
                $clientName,
                $coachName,
                $equipment,
            );
        } catch (Throwable $e) {
            $this->error('ComposeEngine falló: ' . $e->getMessage());
            return 2;
        }

        $outPath = $this->option('out');
        if ($outPath !== null) {
            file_put_contents($outPath, json_encode($composeResult->planJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->info("Plan guardado en: $outPath");
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'plan' => $composeResult->planJson,
                'warnings' => $composeResult->warnings,
                'duration_ms' => $composeResult->durationMs,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->renderText($composeResult);
        }

        return 0;
    }

    private function buildProfile(): ?ClientProfile
    {
        if ($file = $this->option('file')) {
            if (! is_file($file)) {
                $this->error("Archivo no encontrado: $file");
                return null;
            }
            try {
                $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error("JSON inválido: " . $e->getMessage());
                return null;
            }
            return is_array($data) ? ClientProfile::fromArray($data) : null;
        }

        if ($inline = $this->option('inline')) {
            try {
                $data = json_decode((string) $inline, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error("JSON inline inválido: " . $e->getMessage());
                return null;
            }
            return is_array($data) ? ClientProfile::fromArray($data) : null;
        }

        $vertical = $this->option('vertical');
        if (! $vertical) {
            $this->error('Debes especificar profile vía --file, --inline o --vertical (+ otros flags).');
            return null;
        }

        return new ClientProfile(
            vertical: (string) $vertical,
            goal: $this->option('goal'),
            level: $this->option('level'),
            days: $this->option('days') !== null ? (int) $this->option('days') : null,
            gender: $this->option('gender'),
        );
    }

    private function renderText(\App\Services\ComposeEngine\Data\ComposeResult $r): void
    {
        $this->info('═══ ComposeEngine ═══');
        $this->line(sprintf('duración: %.2f ms · warnings: %d', $r->durationMs, count($r->warnings)));
        foreach ($r->warnings as $w) {
            $this->warn(' ! ' . $w);
        }

        $p = $r->planJson;
        $this->newLine();
        $this->line("Plan: {$p['titulo']}");
        $this->line("Tipo: {$p['plan_type']} · Frec: {$p['frecuencia']} · Semanas: {$p['duracion_semanas']} · Inicia: {$p['fecha_inicio']}");
        $this->line("Metodología: {$p['metodologia']}");
        $this->line("Objetivo: {$p['objetivo']}");
        $this->newLine();
        $this->info('Split semanal:');
        foreach ($p['split'] as $dia => $grupo) {
            $this->line("  $dia → $grupo");
        }
        $this->newLine();
        $this->info('Periodización por semana:');
        foreach ($p['semanas'] as $s) {
            $totalEj = array_sum(array_map(fn ($d) => count($d['ejercicios']), $s['dias']));
            $this->line("  Sem {$s['numero']} · {$s['fase']} · {$totalEj} ejercicios totales");
        }
    }
}
