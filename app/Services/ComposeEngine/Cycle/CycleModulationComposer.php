<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Cycle;

use App\Models\Kb\CicloFase;
use App\Services\ComposeEngine\Data\ComposeContext;
use App\Services\ComposeEngine\Data\ComposeResult;
use App\Services\ComposeEngine\Principles\PrincipleInjector;
use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Compose stage para vertical=ciclo (Sprint 19).
 *
 * Producto: plan de adaptación al ciclo menstrual. NO es un plan tradicional —
 * es información sobre las 4 fases del ciclo natural con ajustes recomendados
 * en entrenamiento, nutrición y recuperación por fase.
 *
 * Aplica solo a clientas femeninas. Si el profile es masculino o no tiene gender,
 * produce un warning y devuelve un placeholder educacional.
 *
 * Personalización:
 *   - profile.preferences['anticonceptivos_hormonales'] = true → usa template COC
 *   - profile.preferences['anticonceptivos_hormonales'] = false → usa template natural
 *   - profile.preferences['dia_uno_ultimo_ciclo'] = '2026-05-01' → marca fase actual
 *
 * Shape canónico:
 *   {
 *     plan_type: "ciclo",
 *     ciclo_dias_totales: 28,
 *     usa_anticonceptivos: false,
 *     fase_actual: "fase-folicular-tardia" (si tenemos dia_uno),
 *     fases: [
 *       {
 *         slug, name, dias_tipico, dias_rango,
 *         hormonas_dominantes, sintomas_tipicos,
 *         ajustes_entrenamiento, ajustes_nutricion, ajustes_sueño,
 *       }
 *     ]
 *   }
 */
final class CycleModulationComposer
{
    public function __construct(
        private readonly PrincipleInjector $principleInjector,
    ) {
    }

    public function compose(ComposeContext $ctx): ComposeResult
    {
        $start = microtime(true);
        $warnings = [];

        if (! $this->isFemenino($ctx->profile->gender)) {
            $warnings[] = 'Plan de ciclo aplica solo a clientas femeninas. Para masculinos: ignorar este vertical o usar `habitos`.';
        }

        $usaAnticonceptivos = $this->bool($ctx->profile->preferences['anticonceptivos_hormonales'] ?? null);
        $diaUnoUltimoCiclo = (string) ($ctx->profile->preferences['dia_uno_ultimo_ciclo'] ?? '');

        $fases = CicloFase::query()
            ->active()
            ->fasesNaturales()
            ->orderBy('id')
            ->get();

        if ($fases->isEmpty()) {
            $warnings[] = 'No se encontraron fases del ciclo en wellcore_kb.ciclo_menstrual_fases — re-correr kb:seed.';
        }

        $faseActualSlug = $this->calcularFaseActual($diaUnoUltimoCiclo, $fases);

        $fasesJson = $fases->map(fn ($f) => $this->buildFaseJson($f))->toArray();

        // Sprint 34: inyectar principles relevantes
        $injectedPrinciples = $this->principleInjector->selectTop($ctx->profile, 'ciclo', limit: 3);
        $extraTips = $this->principleInjector->asTipsArray($injectedPrinciples);

        $planJson = [
            'plan_type' => 'ciclo',
            'titulo' => $this->buildTitle($ctx),
            'objetivo' => $usaAnticonceptivos
                ? 'Adaptar entrenamiento y nutrición a la fase del ciclo con anticonceptivos hormonales (variabilidad reducida, igual hay ajustes finos).'
                : 'Adaptar entrenamiento, nutrición y recuperación a la fase del ciclo natural. La fase folicular tolera más volumen e intensidad; la lútea tardía pide soporte adicional.',
            'metodologia' => (string) $ctx->methodology->name,
            'duracion_semanas' => 4,
            'fecha_inicio' => $ctx->fechaInicio,
            'ciclo_dias_totales' => 28,
            'usa_anticonceptivos_hormonales' => $usaAnticonceptivos,
            'dia_uno_ultimo_ciclo' => $diaUnoUltimoCiclo !== '' ? $diaUnoUltimoCiclo : null,
            'fase_actual' => $faseActualSlug,
            'fases' => $fasesJson,
            'notas_coach' => $this->buildNotasCoach($ctx, $usaAnticonceptivos),
            'tips' => array_merge($this->buildTips($usaAnticonceptivos), $extraTips),
            'principios_aplicados' => $injectedPrinciples->pluck('slug')->toArray(),
            'advertencia_medica' => 'Estos ajustes son educativos. Cualquier irregularidad del ciclo (>40 días sin sangrado, dolor severo, sangrado abundante prolongado) debe revisarse con ginecólogo. NO sustituye consulta médica.',
        ];

        return new ComposeResult(
            planJson: $planJson,
            warnings: $warnings,
            durationMs: (microtime(true) - $start) * 1000,
        );
    }

    private function buildFaseJson(CicloFase $f): array
    {
        return [
            'slug' => $f->slug,
            'nombre' => $f->name,
            'dias_tipico' => $f->ciclo_dias_tipico,
            'dias_rango' => $f->ciclo_dias_rango,
            'hormonas_dominantes' => $f->hormonas_dominantes ?? [],
            'sintomas_tipicos' => $f->sintomas_tipicos ?? [],
            'ajustes_entrenamiento' => $f->ajustes_entrenamiento ?? [],
            'ajustes_nutricion' => $f->ajustes_nutricion ?? [],
            'ajustes_recuperacion' => $f->ajustes_sueño_recuperacion ?? [],
        ];
    }

    /**
     * Si tenemos dia_uno_ultimo_ciclo, calcular en qué fase está hoy.
     */
    private function calcularFaseActual(string $diaUno, $fases): ?string
    {
        if ($diaUno === '') {
            return null;
        }

        try {
            $fechaInicio = \Carbon\Carbon::parse($diaUno);
        } catch (\Throwable) {
            return null;
        }

        $diasTranscurridos = $fechaInicio->diffInDays(now()) + 1; // día 1 = primer día del ciclo

        // Ciclo se repite cada ~28 días
        $diaCiclo = (($diasTranscurridos - 1) % 28) + 1;

        foreach ($fases as $f) {
            $rango = $f->ciclo_dias_tipico ?? '';
            if (! preg_match('/^(\d+)-(\d+)$/', $rango, $m)) {
                continue;
            }
            $min = (int) $m[1];
            $max = (int) $m[2];
            if ($diaCiclo >= $min && $diaCiclo <= $max) {
                return $f->slug;
            }
        }
        return null;
    }

    private function buildTitle(ComposeContext $ctx): string
    {
        $base = 'Plan de adaptación al ciclo menstrual';
        return $ctx->clientName !== null ? "{$base} — {$ctx->clientName}" : $base;
    }

    private function buildNotasCoach(ComposeContext $ctx, bool $usaAnticonceptivos): string
    {
        $coach = $ctx->coachName ?? 'tu coach';

        if ($usaAnticonceptivos) {
            return "Con anticonceptivos hormonales, las fases naturales son menos pronunciadas — pero igual hay variabilidad útil. El plan principal de entreno y nutrición sigue como está; estos ajustes son finos. — $coach";
        }

        return "Tu cuerpo no es una máquina constante — el ciclo modula recuperación, fuerza y respuesta a déficit calórico. La fase folicular (semanas 1-2 del ciclo) tolera más volumen e intensidad; la lútea tardía (semana 4) pide bajar carga y subir comfort foods. Esto NO es excusa para días flojos — es info para ajustar la carga. — $coach";
    }

    /**
     * @return string[]
     */
    private function buildTips(bool $usaAnticonceptivos): array
    {
        $tips = [
            'Registrá día 1 del ciclo y duración promedio en la app WellCore para que el coach ajuste el plan',
            'Si el ciclo se interrumpe (>40 días sin sangrado) o cambia drásticamente, avisá al coach',
        ];

        if ($usaAnticonceptivos) {
            $tips[] = 'Con anticonceptivos: la variabilidad hormonal está suprimida, pero efectos secundarios (cefalea, retención líquidos) pueden marcar ciclos';
        } else {
            $tips[] = 'Lútea tardía (PMS): sumá 100-200 kcal/día y reducí 10-15% el volumen — NO es debilidad, es fisiología';
            $tips[] = 'Folicular tardía + ovulación: ventana óptima para PRs, alta intensidad, déficit más agresivo si aplica';
        }

        $tips[] = 'NO uses el ciclo como excusa: 80% de adherencia sostenida vale más que ajustes perfectos en papel';

        return $tips;
    }

    private function isFemenino(?string $g): bool
    {
        if ($g === null) {
            return false;
        }
        return in_array(strtolower($g), ['f', 'femenino', 'female', 'mujer'], true);
    }

    private function bool(mixed $val): bool
    {
        if (is_bool($val)) {
            return $val;
        }
        if (is_string($val)) {
            return in_array(strtolower($val), ['true', '1', 'yes', 'si', 'sí'], true);
        }
        return (bool) $val;
    }
}
