<?php

declare(strict_types=1);

namespace App\Services\LintEngine\Validators;

use App\Services\LintEngine\Data\LintContext;

/**
 * LEY DURA (autoritativa Daniel 2026-05-18):
 *
 * Ningún string del plan visible al cliente puede delatar que un programa/IA
 * arma los planes. El cliente debe percibir que su coach humano escribió todo
 * personalmente.
 *
 * Detecta y rechaza (severity=error) frases que delatan automatización:
 *   - "este plan está armado"
 *   - "generado automáticamente"
 *   - "según tu perfil"
 *   - "el motor / sistema / algoritmo"
 *   - "IA / inteligencia artificial"
 *   - referencia al coach en 3ra persona dentro de notas firmadas por el coach
 *     ("tu coach decidió", "el coach validó" — debe ser "yo / mi / me")
 *   - jerga técnica inglés sin explicar (compounds, isolations, stack, deload, peak, TDEE, BMR)
 *
 * Si encuentra cualquier frase prohibida, bloquea PERSIST.
 *
 * Strings analizados (cualquier campo string del plan, recursivo):
 *   - root: titulo, objetivo, notas_coach, consejos_coach
 *   - semanas[].descripcion
 *   - dias[].calentamiento, dias[].vuelta_calma
 *   - ejercicios[].notas, ejercicios[].tecnica_ejecucion
 *   - comidas[].notas, comidas[].subtitulo
 *   - habitos[].por_que_importa, habitos[].tips[]
 *   - suplementos[].notas
 *   - advertencia_legal
 *   - tips[]
 */
final class AntiAILeakValidator extends BaseValidator
{
    /** Frases CRÍTICAS (delatan automatización inmediatamente) */
    private const HARD_PROHIBITED = [
        // Meta-referencias al plan en 3ra persona
        'este plan está armado',
        'este plan esta armado',
        'tu plan está armado',
        'tu plan esta armado',
        'el plan está armado',
        'el plan esta armado',
        'el plan está calculado',
        'el plan esta calculado',
        'plan generado',
        'generado automáticamente',
        'generado automaticamente',
        'según tu perfil',
        'segun tu perfil',
        'basado en tu perfil',
        // Sistema / motor / algoritmo / IA
        'el motor v2',
        'el motor decidió',
        'el motor decidio',
        'el sistema decidió',
        'el sistema decidio',
        'el algoritmo',
        'inteligencia artificial',
        'asistente de ia',
        'modelo de ia',
        'el modelo',
        // 3ra persona del coach (dentro de notas firmadas por el coach)
        'tu coach validó',
        'tu coach valido',
        'el coach prescribió',
        'el coach prescribio',
        'el coach decidió',
        'el coach decidio',
        // Marketing visible
        'no es upselling',
    ];

    /** Jerga técnica en inglés que el cliente NO debe ver */
    private const TECH_JARGON = [
        'compounds',
        'isolations',
        'deload',
        'peak del bloque',
        'cheat meal',
        'batch cooking',
        'timing matters',
        'evidence-based',
        'stack canónico',
        'stack canonico',
        'ventana anabólica',
        'ventana anabolica',
    ];

    public function name(): string
    {
        return 'anti_ai_leak';
    }

    /** Paths internos (slugs, IDs, hashes, URLs) que NO se muestran al cliente. */
    private const SKIP_PATHS = [
        'principios_aplicados',
        'slug',
        'methodology_slug',
        'plan_type',
        'fecha_inicio',
        'fecha_fin',
        'categoria',
        'id',
        'gif_url',
        'tracking_method', // técnico interno
        'foodSlug',
    ];

    public function check(LintContext $ctx): array
    {
        $plan = $ctx->plan;
        $violations = [];
        $collected = [];
        $this->collectStrings($plan, '$', $collected);

        foreach ($collected as $path => $text) {
            // Skip paths que son IDs/slugs internos (no visibles al cliente)
            $shouldSkip = false;
            foreach (self::SKIP_PATHS as $skipKey) {
                if (str_contains($path, '.' . $skipKey)) {
                    $shouldSkip = true;
                    break;
                }
            }
            if ($shouldSkip) {
                continue;
            }

            $textLower = mb_strtolower($text, 'UTF-8');
            $textNormalized = $this->stripAccents($textLower);

            foreach (self::HARD_PROHIBITED as $pattern) {
                $needle = $this->stripAccents(mb_strtolower($pattern, 'UTF-8'));
                if (str_contains($textNormalized, $needle)) {
                    $violations[] = $this->makeViolation(
                        $ctx,
                        $path,
                        "Texto delata IA/sistema: \"{$pattern}\". El cliente nunca debe notar que un programa arma el plan.",
                        [
                            'frase_detectada' => $pattern,
                            'snippet' => mb_substr($text, 0, 200),
                            'campo' => $path,
                        ],
                    );
                    // Una violation por path es suficiente — no inundar el report.
                    continue 2;
                }
            }

            foreach (self::TECH_JARGON as $jargon) {
                $needle = $this->stripAccents(mb_strtolower($jargon, 'UTF-8'));
                if (str_contains($textNormalized, $needle)) {
                    $violations[] = $this->makeViolation(
                        $ctx,
                        $path,
                        "Jerga técnica en inglés/jerga: \"{$jargon}\". El cliente no la entiende — usar castellano.",
                        [
                            'jerga_detectada' => $jargon,
                            'snippet' => mb_substr($text, 0, 200),
                            'campo' => $path,
                        ],
                    );
                    continue 2;
                }
            }
        }

        return $violations;
    }

    /**
     * Recorre el plan recursivamente y junta TODOS los strings con su path JSON.
     *
     * @param array<string, string> $out (path => string)
     */
    private function collectStrings(mixed $value, string $path, array &$out): void
    {
        if (is_string($value) && trim($value) !== '') {
            $out[$path] = $value;
            return;
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $newPath = is_int($k) ? "{$path}[{$k}]" : "{$path}.{$k}";
                $this->collectStrings($v, $newPath, $out);
            }
        }
    }

    private function stripAccents(string $s): string
    {
        return strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);
    }
}
