<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DecisionEngine\Data\ClientProfile;
use App\Services\DecisionEngine\Data\Recommendation;
use App\Services\DecisionEngine\DecisionEngine;
use Illuminate\Console\Command;
use JsonException;

/**
 * plan:decide — Stage 2 SELECT del motor v2.
 *
 * Recibe un ClientProfile (intake) y retorna las metodologías recomendadas
 * por vertical (entrenamiento, nutrición, suplementación, hábitos, ciclo).
 *
 * Modos:
 *   php artisan plan:decide --file=intake.json
 *       Lee profile desde JSON file.
 *
 *   php artisan plan:decide --inline='{"vertical":"entrenamiento","goal":"hipertrofia","level":"intermedio","days":5}'
 *       Profile inline.
 *
 *   php artisan plan:decide --vertical=entrenamiento --goal=hipertrofia --level=intermedio --days=5
 *       Profile vía flags individuales (más cómodo desde CLI).
 *
 * Opciones extras:
 *   --json     output en JSON parseable
 *   --top      muestra solo top-1 por vertical (default: todas las matches)
 *   --vertical-only=X  filtra resultados a solo vertical X
 *
 * Exit codes:
 *   0 = al menos una recomendación
 *   1 = profile válido pero 0 matches
 *   2 = profile inválido o error
 */
final class PlanDecideCommand extends Command
{
    protected $signature = 'plan:decide
                            {--file= : Path a un archivo JSON con el ClientProfile}
                            {--inline= : JSON inline del profile}
                            {--vertical= : profile.vertical}
                            {--goal= : profile.goal}
                            {--level= : profile.level}
                            {--days= : profile.days (int)}
                            {--gender= : profile.gender}
                            {--equipment= : profile.equipment}
                            {--tier= : profile.tier}
                            {--top : muestra solo top-1 por vertical}
                            {--vertical-only= : filtra a una vertical específica}
                            {--json : output en JSON estructurado}';

    protected $description = 'Stage 2 SELECT del motor v2 — recomienda metodologías para un ClientProfile.';

    public function handle(DecisionEngine $engine): int
    {
        $profile = $this->buildProfile();
        if ($profile === null) {
            return 2;
        }

        $result = $engine->decide($profile);
        $isJson = (bool) $this->option('json');
        $top = (bool) $this->option('top');
        $verticalOnly = $this->option('vertical-only');

        if ($isJson) {
            $this->renderJson($profile, $result, $top, $verticalOnly);
        } else {
            $this->renderText($profile, $result, $top, $verticalOnly);
        }

        return $result->rulesMatched > 0 ? 0 : 1;
    }

    private function buildProfile(): ?ClientProfile
    {
        // Prioridad: --file > --inline > --flags
        if ($file = $this->option('file')) {
            if (! is_file($file)) {
                $this->error("Archivo no encontrado: $file");
                return null;
            }
            try {
                $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error("JSON inválido en $file: " . $e->getMessage());
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

        // Flags individuales
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
            equipment: $this->option('equipment'),
            tier: $this->option('tier'),
        );
    }

    private function renderText(ClientProfile $profile, \App\Services\DecisionEngine\Data\DecisionResult $result, bool $top, ?string $verticalFilter): void
    {
        $this->info('═══ DecisionEngine ═══');
        $this->line('Profile: ' . json_encode($profile->toArray(), JSON_UNESCAPED_UNICODE));
        $sum = $result->summary();
        $this->line(sprintf(
            'rules evaluadas: %d · matched: %d · verticals: %s · %.1f ms',
            $sum['rules_evaluated'],
            $sum['rules_matched'],
            implode(', ', $sum['verticals_covered']) ?: '—',
            $sum['duration_ms'],
        ));

        if ($result->rulesMatched === 0) {
            $this->warn('Sin recomendaciones. Verifica que el profile tenga vertical+goal+level+days alineados con alguna decision_rule activa.');
            return;
        }

        $byVertical = $result->byVertical;
        if ($verticalFilter !== null) {
            $byVertical = array_filter($byVertical, fn ($_, $v) => $v === $verticalFilter, ARRAY_FILTER_USE_BOTH);
        }

        foreach ($byVertical as $vertical => $recs) {
            $this->newLine();
            $this->info("┌─ vertical: $vertical");
            $toShow = $top ? array_slice($recs, 0, 1) : $recs;
            foreach ($toShow as $i => $rec) {
                $prefix = $i === 0 ? '✦' : ' ';
                $this->line(sprintf(
                    '  %s [%.2f] %s → %s',
                    $prefix,
                    $rec->confidence,
                    $rec->ruleName,
                    $rec->methodologySlug,
                ));
                $this->line('       rationale: ' . $rec->rationale);
                $this->line('       matched:   ' . json_encode($rec->matchedConditions, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    private function renderJson(ClientProfile $profile, \App\Services\DecisionEngine\Data\DecisionResult $result, bool $top, ?string $verticalFilter): void
    {
        $byVertical = $result->byVertical;
        if ($verticalFilter !== null) {
            $byVertical = array_filter($byVertical, fn ($_, $v) => $v === $verticalFilter, ARRAY_FILTER_USE_BOTH);
        }

        $out = [
            'profile' => $profile->toArray(),
            'summary' => $result->summary(),
            'recommendations' => [],
        ];
        foreach ($byVertical as $vertical => $recs) {
            $toShow = $top ? array_slice($recs, 0, 1) : $recs;
            $out['recommendations'][$vertical] = array_map(fn (Recommendation $r) => $r->toArray(), $toShow);
        }

        $this->line(json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
