<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * Detecta planes que mencionan lesiones / dolor / contraindicaciones pero NO
 * mencionan warmup específico de la zona afectada.
 *
 * Razón fisiológica: cliente con hombro lesionado necesita warmup específico
 * para hombro (rotación rotador externo, banda elastica, escapulares) — no
 * basta con "5 min bicicleta + estiramiento general".
 *
 * Algoritmo:
 *   1. Busca palabras clave de lesión/dolor en notas_coach, tips, ejercicios:
 *      lesion, dolor, hombro, rodilla, lumbar, espalda baja, codo, muñeca,
 *      tendinitis, esguince, hernia, contraindicación.
 *   2. Si encuentra mención de lesión, verifica que en algún calentamiento
 *      o tip se mencione la zona específica + acción de movilidad/activación.
 *   3. Si menciona lesión pero no hay warmup específico → warning.
 *
 * Excepción: si el plan no menciona NINGUNA lesión, este validator NO aplica
 * (lo cubre WarmupMissingValidator más genérico).
 *
 * Usado por: heur_warmup_lesion_specific.
 */
final class WarmupLesionSpecificValidator extends BaseValidator
{
    private const ZONAS_LESION = [
        'hombro' => ['rotador', 'escapular', 'rotacion externa', 'rotación externa', 'pull-apart'],
        'rodilla' => ['terminal', 'patelar', 'cuadriceps activacion', 'cuádriceps activación', 'gluteo activacion'],
        'lumbar' => ['glúteo activacion', 'gluteo activacion', 'cat-cow', 'gato-camello', 'bird-dog', 'puente'],
        'codo' => ['flexion extension', 'flexión extensión', 'muñeca movilidad'],
        'muñeca' => ['muñeca movilidad', 'flexion muñeca', 'extension muñeca'],
        'espalda baja' => ['gato-camello', 'cat-cow', 'puente', 'bird-dog'],
        'cuello' => ['movilidad cervical', 'rotacion cervical', 'rotación cervical'],
    ];

    private const KEYWORDS_LESION = [
        'lesion', 'lesión', 'dolor', 'molestia', 'tendinitis', 'esguince',
        'hernia', 'contraindicacion', 'contraindicación', 'rehabilitacion',
        'rehabilitación', 'post-lesion', 'post-lesión',
    ];

    public function name(): string
    {
        return 'warmup_lesion_specific';
    }

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;
        $fullText = $this->collectAllText($plan);
        $lower = mb_strtolower($fullText);

        // 1. ¿Menciona lesión/dolor/contraindicación?
        $mentionsLesion = false;
        foreach (self::KEYWORDS_LESION as $kw) {
            if (str_contains($lower, $kw)) {
                $mentionsLesion = true;
                break;
            }
        }
        if (! $mentionsLesion) {
            return []; // Skip — no aplica
        }

        // 2. ¿Qué zona específica está afectada?
        $zonasAfectadas = [];
        foreach (array_keys(self::ZONAS_LESION) as $zona) {
            if (str_contains($lower, $zona)) {
                $zonasAfectadas[] = $zona;
            }
        }

        if ($zonasAfectadas === []) {
            // Menciona lesión genéricamente pero no zona específica — info nomás
            return [];
        }

        // 3. Para cada zona detectada, verificar si hay activación/movilidad específica
        $faltantes = [];
        foreach ($zonasAfectadas as $zona) {
            $tieneEspecifico = false;
            foreach (self::ZONAS_LESION[$zona] as $accion) {
                if (str_contains($lower, mb_strtolower($accion))) {
                    $tieneEspecifico = true;
                    break;
                }
            }
            if (! $tieneEspecifico) {
                $faltantes[] = $zona;
            }
        }

        if ($faltantes === []) {
            return [];
        }

        return [$this->makeViolation(
            $ctx,
            '$',
            sprintf(
                'Plan menciona lesión/dolor en zona(s) %s pero NO incluye warmup específico (movilidad/activación). Agregar warmup dirigido a esas zonas.',
                implode(', ', $faltantes),
            ),
            [
                'zonas_afectadas' => $zonasAfectadas,
                'zonas_sin_warmup_especifico' => $faltantes,
                'acciones_sugeridas_por_zona' => array_intersect_key(self::ZONAS_LESION, array_flip($faltantes)),
            ],
        )];
    }

    private function collectAllText(array $plan): string
    {
        $parts = [];
        if (! empty($plan['notas_coach'])) {
            $parts[] = (string) $plan['notas_coach'];
        }
        if (! empty($plan['objetivo'])) {
            $parts[] = (string) $plan['objetivo'];
        }
        foreach ((array) ($plan['tips'] ?? []) as $tip) {
            if (is_string($tip)) {
                $parts[] = $tip;
            }
        }
        if (! empty($plan['calentamiento'])) {
            $parts[] = (string) $plan['calentamiento'];
        }
        foreach (($plan['semanas'] ?? []) as $sem) {
            foreach (($sem['dias'] ?? []) as $dia) {
                if (! empty($dia['calentamiento'])) {
                    $parts[] = (string) $dia['calentamiento'];
                }
                if (! empty($dia['notas'])) {
                    $parts[] = (string) $dia['notas'];
                }
                foreach (($dia['ejercicios'] ?? []) as $ej) {
                    if (! empty($ej['notas'])) {
                        $parts[] = (string) $ej['notas'];
                    }
                }
            }
        }
        return implode(' ', $parts);
    }
}
