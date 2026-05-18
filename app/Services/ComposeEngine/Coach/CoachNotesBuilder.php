<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Coach;

use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Genera el bloque `notas_coach` y `objetivo` largos, personalizados al cliente.
 *
 * Reemplaza los strings genéricos del PlanComposer (35 palabras boilerplate) por
 * 3-4 párrafos con:
 *   1. Conexión personal (nombre, edad, peso, objetivo del cliente)
 *   2. Estrategia (qué metodología, por qué, parámetros calculados)
 *   3. Qué esperar (sensaciones por semana, semanas 1→4)
 *   4. Cierre con acción concreta
 *
 * Voz: voseo colombiano neutro amable. Sin "vale", "che", "parcero".
 * No menciona IA ni Claude (regla autoritativa).
 */
final class CoachNotesBuilder
{
    /**
     * @param ClientProfile $profile El perfil del cliente
     * @param string $clientName Nombre del cliente para personalización ("Karen", "Daniel")
     * @param string $coachName Coach que firma ("Héctor", "Anderson")
     * @param string $methodologyName Nombre legible ("Upper / Lower 4 días")
     * @param int $duracionSemanas Default 4
     */
    public function buildForEntrenamiento(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        string $methodologyName,
        int $duracionSemanas = 4,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $coachName ?? 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $diasLabel = $profile->days ? "{$profile->days} días/semana" : 'tus días disponibles';
        $nivelLabel = $profile->level ?? 'tu nivel';

        // 4 párrafos
        $p1 = "{$nombre}, este plan está armado para tu objetivo de {$objetivoLabel} con {$diasLabel}. La metodología elegida es {$methodologyName} porque encaja con tu nivel {$nivelLabel} y el split que tu coach validó.";

        $p2 = "La estructura sube intensidad cada semana: empezás con RIR 3 (3 reps en reserva) y vas bajando hasta RIR 0 en la semana 4. Las series y rangos de repeticiones cambian entre fases — compounds llevan más series y menos reps, isolations lo opuesto. No es 3×12 lineal — cada ejercicio tiene su prescripción.";

        $p3 = $this->buildExpectationsParagraph($profile->goal);

        $p4 = "Empezás mañana. Anotá peso y RIR de cada serie apenas terminás — sin registro no hay progresión real. Si una semana no llegás al RIR objetivo, te quedás en el peso y ajustás técnica primero. Cualquier dolor articular (no fatiga muscular), parás y me avisás. — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    /**
     * Notas coach para nutrición con TDEE/BMR/proteína g/kg explícitos.
     */
    public function buildForNutricion(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        array $macroPlan,
        int $numComidas,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $coachName ?? 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $tdee = (int) ($macroPlan['tdee'] ?? 0);
        $bmr = (int) ($macroPlan['bmr'] ?? 0);
        $objetivoCal = (int) ($macroPlan['objetivo_cal'] ?? 0);
        $proteinaG = (int) ($macroPlan['macros']['proteina_g'] ?? 0);
        $weight = $profile->weightKg ?? 0;
        $proteinaPorKg = $weight > 0 ? round($proteinaG / $weight, 1) : 0;
        $deltaKcal = $tdee - $objetivoCal;
        $deltaDirection = $deltaKcal > 0 ? 'déficit' : ($deltaKcal < 0 ? 'superávit' : 'mantenimiento');
        $deltaAbs = abs($deltaKcal);

        $p1 = "{$nombre}, tu plan nutricional tiene {$objetivoCal} kcal/día con {$proteinaG}g de proteína ({$proteinaPorKg} g/kg). Esto es un {$deltaDirection} de {$deltaAbs} kcal sobre tu TDEE calculado ({$tdee} kcal) — pensado para {$objetivoLabel}.";

        $p2 = "El reparto en {$numComidas} comidas mantiene proteína constante todo el día. Cada comida tiene 3 opciones equivalentes en macros (±5%) para que no te aburras y puedas cambiar según lo que tengas en casa. Pesá la primera semana en crudo — después calculás a ojo.";

        $p3 = $this->buildNutritionExpectationsParagraph($profile->goal);

        $p4 = "Empezás mañana. Si llegás tarde a una comida, no te la saltes — sumá su proteína a la siguiente. Hidratate bien (mínimo 35 ml × peso en kg/día). Cualquier antojo nocturno se cubre con té + canela o agua caliente con miel. — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    /**
     * Notas coach para suplementación: stack info + por qué este orden + costos.
     */
    public function buildForSuplementacion(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        ?array $stackInfo,
        int $totalItems,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $coachName ?? 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $stackName = $stackInfo['stack_nombre'] ?? 'stack básico';
        $costo = $stackInfo['costo_mensual_estimado_cop'] ?? null;

        $p1 = "{$nombre}, este stack ({$stackName}) está pensado para {$objetivoLabel} en tu nivel actual. Son {$totalItems} suplementos con evidencia respaldada — no es upselling, es lo que mueve la aguja.";

        $p2 = "Tomá cada uno en el momento indicado (timing matters: pre-entreno vs post-entreno vs con comida). La constancia importa más que la dosis exacta — mejor 80% sostenido durante 4 semanas que 100% sólo la primera semana.";

        $p3 = "Si el coach prescribió suplementos específicos, esos van primero — yo agregué los evidence-based que faltaban. Si tenés contraindicación renal/hepática o estás embarazada, parate y avisame antes de empezar.";

        $p4Cost = $costo ? " El costo mensual aproximado es de COP \${$this->formatCop((int) $costo)} (referencial, varía 2-3× por marca y país)." : '';
        $p4 = "Si no podés comprar todos, priorizá los marcados como esenciales primero.{$p4Cost} — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    /**
     * Objetivo largo para entrenamiento (reemplaza el match() corto).
     */
    public function buildObjetivoEntrenamiento(ClientProfile $profile, string $methodologyName): string
    {
        return match ($profile->goal) {
            'hipertrofia' => "Ganar masa muscular con foco en hipertrofia progresiva. {$methodologyName} con periodización RIR 3→0 en 4 semanas para crear estímulo sin sobreentreno.",
            'fuerza' => "Aumentar fuerza máxima en compuestos principales. {$methodologyName} con cargas pesadas, descansos largos y técnica perfecta.",
            'perdida_grasa' => "Pérdida de grasa con preservación de masa muscular. {$methodologyName} con énfasis en mantener intensidad alta mientras el déficit calórico hace el trabajo.",
            'recomposicion' => "Recomposición corporal: bajar grasa y ganar/preservar músculo simultáneamente. {$methodologyName} con periodización progresiva.",
            'mantenimiento' => "Mantener masa muscular, fuerza y rendimiento. {$methodologyName} con volumen moderado y técnica como prioridad.",
            'performance' => "Mejorar rendimiento deportivo y capacidad de trabajo. {$methodologyName} con foco en fuerza-resistencia y potencia.",
            default => "Mejorar composición corporal y rendimiento. {$methodologyName} con periodización progresiva en 4 semanas.",
        };
    }

    private function buildExpectationsParagraph(?string $goal): string
    {
        return match ($goal) {
            'perdida_grasa' => 'Las primeras 2 semanas pueden sentirse pesadas — el cuerpo está en déficit calórico. Si tenés baja energía, mové la carga +5kg cada semana solo si llegás al RIR objetivo. A partir de la semana 3 empezás a ver cambios reales en espejo y medidas.',
            'hipertrofia' => 'La semana 1 es de adaptación — vas a sentir agujetas fuertes, normal. Semana 2-3 es donde se acumula volumen real y empezás a sentirte más fuerte. La semana 4 es peak — tu mejor sesión del mes va acá. Después un deload o cambio de plan.',
            'recomposicion' => 'Recomposición es más lenta que solo perder o solo ganar — esperá ~1kg/mes en cualquier dirección. Lo importante es que la balanza no cambie mucho pero el espejo sí. Fotos cada 2 semanas en las mismas condiciones.',
            'fuerza' => 'En fuerza el progreso se mide en kg en la barra, no en la balanza. Esperá +2.5-5kg/semana en compuestos principales si la técnica está sólida. Si te estancás, hablamos de cambiar variante o descanso.',
            default => 'Las primeras 2 semanas son de adaptación — tu cuerpo se acostumbra al nuevo volumen. Semana 3-4 es donde rendís al máximo. Anotá todo, así medimos progreso real.',
        };
    }

    private function buildNutritionExpectationsParagraph(?string $goal): string
    {
        return match ($goal) {
            'perdida_grasa' => 'Las primeras 2 semanas vas a sentir hambre — es normal, tu cuerpo se ajusta al nuevo aporte calórico. Tomá agua + masticá despacio (20 masticadas por bocado) ayuda. Esperá perder 0.5-1 kg/semana después de la semana 2. Si bajás más rápido, es agua o músculo — avisame y ajustamos.',
            'hipertrofia' => 'En superávit calórico esperá ganar ~0.3-0.5 kg/semana después de la semana 2. Si subís más rápido, es probablemente grasa — avisame. Si no subís nada, sumamos 100-150 kcal extra.',
            'recomposicion' => 'En recomposición los cambios son sutiles — la balanza puede no moverse pero el espejo sí. Fotos cada 2 semanas en mismas condiciones (mismo día, hora, ropa, luz) para detectar cambios que el peso no muestra.',
            default => 'Las primeras 2 semanas son de ajuste — tu cuerpo se acostumbra al nuevo aporte y horarios. Si tenés hambre extrema o energía muy baja, avisame para ajustar.',
        };
    }

    private function goalLabel(?string $goal): string
    {
        return match ($goal) {
            'hipertrofia' => 'ganancia de masa muscular',
            'fuerza' => 'aumento de fuerza',
            'perdida_grasa' => 'pérdida de grasa',
            'recomposicion' => 'recomposición corporal',
            'mantenimiento' => 'mantenimiento',
            'performance' => 'mejorar rendimiento',
            default => 'mejorar composición corporal',
        };
    }

    private function resolveFirstName(?string $fullName): string
    {
        if ($fullName === null || $fullName === '') {
            return 'Hola';
        }
        $parts = explode(' ', trim($fullName));
        return $parts[0] !== '' ? $parts[0] : 'Hola';
    }

    /**
     * @param string[] $paragraphs
     */
    private function joinParagraphs(array $paragraphs): string
    {
        return implode("\n\n", array_filter(array_map('trim', $paragraphs)));
    }

    private function formatCop(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
