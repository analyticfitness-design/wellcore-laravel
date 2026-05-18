<?php

declare(strict_types=1);

namespace App\Services\ComposeEngine\Coach;

use App\Services\DecisionEngine\Data\ClientProfile;

/**
 * Genera notas del coach personalizadas.
 *
 * Reglas de voz (autoritativa Daniel 2026-05-18):
 * - 2da persona voseo colombiano neutro amable
 * - Firma del coach al final, NUNCA "tu coach" en 3ra persona
 * - Cliente jamás puede notar que un programa/sistema arma el plan
 * - PROHIBIDO: "este plan está armado", "generado", "según tu perfil",
 *   "el motor", "el sistema", "compounds/isolations" (inglés técnico),
 *   "TDEE/BMR" sin explicar, "macros ±5%", "déficit calórico" sin contexto,
 *   "stack", "evidence-based", "deload", "peak", "batch cooking", "cheat meal"
 */
final class CoachNotesBuilder
{
    public function buildForEntrenamiento(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        string $methodologyName,
        int $duracionSemanas = 4,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $this->resolveFirstName($coachName) ?: 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $diasLabel = $profile->days ? "{$profile->days} días por semana" : 'tus días disponibles';
        $nivelLabel = $profile->level ?? 'tu nivel actual';

        $p1 = "{$nombre}, te armé este plan pensando en lo que estamos buscando: {$objetivoLabel} entrenando {$diasLabel}. Vamos con {$methodologyName} porque para tu nivel {$nivelLabel} es lo que mejor te va a funcionar.";

        $p2 = "Vamos a subir intensidad semana a semana: arrancás con RIR 3 (te tienen que quedar 3 reps en el tanque al terminar la serie) y vamos bajando hasta RIR 0 en la semana 4. En los ejercicios grandes (sentadilla, peso muerto, press) hacés más series con menos reps; en los de aislación es al revés. Cada ejercicio tiene su propia cuenta — no es el típico 3×12 para todo.";

        $p3 = $this->buildExpectationsParagraph($profile->goal);

        $p4 = "Arrancás mañana. Anotá peso y RIR de cada serie apenas la terminás — si no anotás, no sabemos qué subir la próxima semana. Si una semana no llegás al RIR que te pongo, te quedás con el mismo peso y limpiá técnica primero. Cualquier dolor articular (no fatiga normal, dolor que pincha), parás y me escribís de una. — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    public function buildForNutricion(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        array $macroPlan,
        int $numComidas,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $this->resolveFirstName($coachName) ?: 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $tdee = (int) ($macroPlan['tdee'] ?? 0);
        $objetivoCal = (int) ($macroPlan['objetivo_cal'] ?? 0);
        $proteinaG = (int) ($macroPlan['macros']['proteina_g'] ?? 0);
        $weight = $profile->weightKg ?? 0;
        $proteinaPorKg = $weight > 0 ? round($proteinaG / $weight, 1) : 0;
        $delta = $tdee - $objetivoCal;
        $deltaText = match (true) {
            $delta > 50 => "comiendo {$delta} kcal menos de lo que tu cuerpo gasta normalmente",
            $delta < -50 => "comiendo " . abs($delta) . " kcal de más para subir músculo",
            default => 'manteniendo lo que tu cuerpo gasta',
        };

        $p1 = "{$nombre}, te dejo {$objetivoCal} kcal por día con {$proteinaG}g de proteína ({$proteinaPorKg} g por cada kilo tuyo). Vamos a estar {$deltaText} — esto es lo que necesitás para {$objetivoLabel}.";

        $p2 = "Te lo partí en {$numComidas} comidas para que tengas proteína repartida todo el día. Cada comida te dejé 3 opciones que cumplen lo mismo — elegís según lo que tengas en la cocina o lo que se te antoje. La primera semana pesá los alimentos en crudo; ya después le agarrás el ojo.";

        $p3 = $this->buildNutritionExpectationsParagraph($profile->goal);

        $p4 = "Arrancás mañana. Si llegás tarde a una comida, no te la saltés — sumale la proteína a la próxima. Tomá agua (mínimo 35 ml por cada kilo tuyo al día). Si te ataca el antojo de noche, agua caliente con miel o un té con canela lo apagan. — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    public function buildForSuplementacion(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
        ?array $stackInfo,
        int $totalItems,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $this->resolveFirstName($coachName) ?: 'tu coach';
        $objetivoLabel = $this->goalLabel($profile->goal);
        $costo = $stackInfo['costo_mensual_estimado_cop'] ?? null;

        $p1 = "{$nombre}, te seleccioné estos {$totalItems} suplementos para {$objetivoLabel} en tu momento actual. Son los que tienen ciencia detrás — nada de polvitos de moda. Lo que de verdad mueve la aguja.";

        $p2 = "Tomá cada uno en el momento que te marco — el cuándo importa tanto como el qué (pre-entreno, post-entreno, con comida). Y constancia: mejor que los tomes el 80% del mes y no que los tomes a tope la primera semana y los abandones.";

        $p3 = "Si tenés algo de riñones, hígado, presión, o estás embarazada, parame ahí y hablamos antes de que compres nada.";

        $p4Cost = $costo ? ' El costo mensual aproximado es de COP $' . $this->formatCop((int) $costo) . ' (referencial, varía 2-3× por marca y país).' : '';
        $p4 = "Si no te alcanza para todos este mes, arrancá por los que te marqué como esenciales — los demás los sumás cuando puedas.{$p4Cost} — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    /**
     * Notas coach para hábitos — voz personalizada.
     */
    public function buildForHabitos(
        ClientProfile $profile,
        ?string $clientName,
        ?string $coachName,
    ): string {
        $nombre = $this->resolveFirstName($clientName);
        $coach = $this->resolveFirstName($coachName) ?: 'tu coach';

        $p1 = "{$nombre}, estos hábitos son la base de todo. Con esto firme, el resto del plan rinde el triple. Sin esto, el mejor entreno y la mejor nutrición no sirven.";

        $p2 = 'No te pongas la meta del 100% — apuntá al 80% todas las semanas y vas a ver el cambio. Si fallás un día, retomá al siguiente. No compensés con esfuerzo extra (eso desgasta).';

        $p3 = 'Arrancá por el hábito que más te cuesta — ese es el que más te va a mover la aguja. En 4 semanas el hábito se te vuelve automático y ya no necesitás estar motivada todos los días.';

        $p4 = "Si algo no te encaja con tus tiempos o tu situación, escribime y lo ajustamos. — {$coach}";

        return $this->joinParagraphs([$p1, $p2, $p3, $p4]);
    }

    public function buildObjetivoEntrenamiento(ClientProfile $profile, string $methodologyName): string
    {
        return match ($profile->goal) {
            'hipertrofia' => "Te voy a ayudar a ganar masa muscular real, subiendo intensidad mes a mes. {$methodologyName}, 4 semanas, RIR bajando de 3 a 0 (cada vez te quedan menos reps en el tanque). El plan está calibrado para que crezcas sin pasarte.",
            'fuerza' => "Vamos a subir fuerza máxima en los ejercicios grandes. {$methodologyName} con cargas pesadas, descansos largos y técnica como prioridad.",
            'perdida_grasa' => "Vas a bajar grasa preservando músculo. {$methodologyName} con intensidad sostenida — el déficit calórico hace el trabajo de bajar grasa; el gym hace el trabajo de mantener el músculo.",
            'recomposicion' => "Vamos a bajar grasa y subir/preservar músculo al mismo tiempo. {$methodologyName} con periodización progresiva.",
            'mantenimiento' => "Vamos a mantener masa muscular y rendimiento. {$methodologyName} con volumen moderado y técnica como prioridad.",
            'performance' => "Vamos a mejorar tu rendimiento y capacidad de trabajo. {$methodologyName} con foco en fuerza-resistencia y potencia.",
            default => "Vamos a mejorar tu composición corporal y rendimiento. {$methodologyName} con periodización progresiva en 4 semanas.",
        };
    }

    private function buildExpectationsParagraph(?string $goal): string
    {
        return match ($goal) {
            'perdida_grasa' => 'Las primeras dos semanas las vas a sentir pesadas — estás comiendo menos de lo que gastás, es normal. Si te baja la energía, no fuerces — quedate con el mismo peso. Recién subís +5kg cuando llegás al RIR que te puse. A partir de la tercera semana ya empezás a notar cambios en el espejo y en las medidas.',
            'hipertrofia' => 'La primera semana van a aparecer agujetas fuertes, no te asustés. En la segunda y tercera ya te empezás a sentir más fuerte. La semana 4 es la dura — tu mejor entreno del mes lo querés ahí. Después armamos el siguiente bloque.',
            'recomposicion' => 'Bajar grasa y subir músculo al mismo tiempo es más lento que solo hacer una cosa — esperá moverte como mucho 1 kilo al mes en cualquier dirección. Lo importante: la balanza casi no se mueve pero el espejo cambia. Sacate foto cada 2 semanas, misma luz, mismo ángulo.',
            'fuerza' => 'El progreso se mide en kg en la barra, no en la balanza. Esperá +2.5-5kg por semana en los ejercicios grandes si la técnica está sólida. Si te estancás, hablamos de cambiar variante o descanso.',
            default => 'Las primeras dos semanas son de adaptación — tu cuerpo se acostumbra al volumen. En la tercera y cuarta semana rendís al máximo. Anotá todo, así medimos progreso real.',
        };
    }

    private function buildNutritionExpectationsParagraph(?string $goal): string
    {
        return match ($goal) {
            'perdida_grasa' => 'Las primeras dos semanas vas a tener hambre — es normal, tu cuerpo se está acomodando. Tomá más agua, masticá despacio (intentá 20 masticadas por bocado), y se te va apagando. De la semana 2 en adelante esperá bajar entre medio y un kilo por semana. Si bajás más rápido, no es grasa — escribime y ajustamos.',
            'hipertrofia' => 'Vas a subir entre 300 y 500 gramos por semana después de la semana 2. Si subís más rápido, probablemente sea grasa — escribime. Si no subís nada, sumamos 100-150 kcal extra.',
            'recomposicion' => 'En recomposición los cambios son sutiles — la balanza puede no moverse pero el espejo sí. Sacate foto cada 2 semanas en las mismas condiciones (mismo día, hora, ropa, luz) — eso te muestra cambios que el peso esconde.',
            default => 'Las primeras dos semanas son de ajuste — tu cuerpo se acostumbra al nuevo aporte y horarios. Si tenés hambre extrema o energía muy baja, escribime para ajustar.',
        };
    }

    private function goalLabel(?string $goal): string
    {
        return match ($goal) {
            'hipertrofia' => 'ganar masa muscular',
            'fuerza' => 'aumentar fuerza',
            'perdida_grasa' => 'bajar grasa',
            'recomposicion' => 'bajar grasa y mantener músculo',
            'mantenimiento' => 'mantener forma física',
            'performance' => 'mejorar tu rendimiento',
            default => 'mejorar tu composición corporal',
        };
    }

    private function resolveFirstName(?string $fullName): string
    {
        if ($fullName === null || trim($fullName) === '') {
            return '';
        }
        $parts = explode(' ', trim($fullName));
        return $parts[0];
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
