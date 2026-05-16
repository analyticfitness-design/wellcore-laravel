import { computed } from 'vue';

/**
 * useCardioInference.js
 *
 * Función pura `inferCardioType(exercise)` que clasifica un ejercicio cardio
 * en uno de 6 arquetipos. Diseñada para retrocompatibilidad: planes existentes
 * sin `cardio_type` explícito se clasifican automáticamente leyendo nombre,
 * notas, bloque y campos numéricos.
 *
 * Arquetipos:
 *   - continuous_low      LISS continuo (default zona 2)
 *   - continuous_moderate MISS continuo (zona 3)
 *   - intervals           HIIT clásico con work/rest
 *   - tabata              Tabata estricto 20s/10s × 8
 *   - circuit             AMRAP/EMOM bodyweight con múltiples estaciones
 *   - free                fallback — render con SetRow cardio actual
 *
 * NO modifica el JSON del plan en DB. Solo decide qué sub-componente renderiza
 * en el frontend.
 *
 * Si el plan tiene `exercise.cardio_type` explícito, gana sobre la inferencia.
 */

const TABATA_REGEX = /\btabata\b/i;
const INTERVAL_REGEX = /\bhiit\b|\bintervalo[s]?\b|\bsprints?\b|\b\d+\s*\/\s*\d+\s*(seg|sec|s)\b|\b\d+x\d+\b|all-?out/i;
const CIRCUIT_REGEX = /\bamrap\b|\bemom\b|\brounds for time\b|\brft\b|\bcircuito metab[oó]lico\b/i;

/**
 * Parsea un número desde un string (acepta "30", "30 min", "30-45", "1,5").
 * Devuelve el primer número o 0.
 */
function firstNumber(input) {
    if (input == null) return 0;
    const str = String(input).replace(',', '.');
    const m = str.match(/(\d+(?:\.\d+)?)/);
    return m ? parseFloat(m[1]) : 0;
}

/**
 * Clasifica un ejercicio cardio según su contenido.
 *
 * @param {object} exercise - ejercicio del plan
 * @returns {string} cardio_type
 */
export function inferCardioType(exercise) {
    if (!exercise) return 'free';

    // 1. Explícito gana siempre
    if (exercise.cardio_type) return exercise.cardio_type;

    // No es cardio → no aplica
    if (!exercise.is_cardio) return 'free';

    const name = String(exercise.nombre || exercise.name || '').toLowerCase();
    const notas = String(exercise.notas || exercise.notes || '').toLowerCase();
    const haystack = `${name} ${notas}`;

    // 2. Tabata explícito
    if (TABATA_REGEX.test(haystack)) return 'tabata';

    // 3. Patrón "circuito" del JSON (Lizeth sábado): bloque + grupo_id + reps cortas
    const bloque = String(exercise.bloque || exercise.block_type || '').toLowerCase();
    if (bloque === 'circuito' || bloque === 'circuit') {
        // Si hay rondas y descansos cortos → intervals
        const repsText = String(exercise.repeticiones || exercise.reps || '').toLowerCase();
        const isShortReps = /seg|sec|\bs\b/.test(repsText) && firstNumber(repsText) <= 60;
        if (isShortReps) return 'intervals';
        return 'circuit';
    }

    // 4. HIIT/Intervalos/Sprints en texto
    if (INTERVAL_REGEX.test(haystack)) return 'intervals';

    // 5. AMRAP/EMOM/RFT en texto
    if (CIRCUIT_REGEX.test(haystack)) return 'circuit';

    // 6. LISS keywords explícitos
    if (/\bliss\b|zona\s*2|z2\b|ritmo c[oó]modo|paso constante|hablar pero no cantar/i.test(haystack)) {
        return 'continuous_low';
    }

    // 7. MISS keywords
    if (/zona\s*3|z3\b|ritmo medio|ritmo moderado|hablar entrecortado/i.test(haystack)) {
        return 'continuous_moderate';
    }

    // 8. Fallback por duración: si duración >= 20 min → LISS, si < 20 → moderado
    const duration = firstNumber(exercise.duracion_min || exercise.duracion || exercise.duration);
    if (duration >= 20) return 'continuous_low';
    if (duration > 0)   return 'continuous_moderate';

    // 9. Sin pistas → free (fallback al SetRow cardio actual)
    return 'free';
}

/**
 * Devuelve true si el cardio_type usa cronómetro de intervalos automático.
 */
export function isIntervalBasedType(cardioType) {
    return cardioType === 'intervals' || cardioType === 'tabata';
}

/**
 * Devuelve true si el cardio_type usa cronómetro descendente continuo.
 */
export function isContinuousType(cardioType) {
    return cardioType === 'continuous_low' || cardioType === 'continuous_moderate';
}

/**
 * Devuelve true si el cardio_type usa cronómetro ascendente (AMRAP-style).
 */
export function isAmrapType(cardioType) {
    return cardioType === 'circuit';
}

/**
 * Devuelve la guía de intensidad por defecto según cardio_type.
 * Útil cuando el coach NO especifica `intensidad` en el JSON.
 */
export function defaultIntensityFor(cardioType) {
    const map = {
        continuous_low:      { zona_fc: 2, porcentaje_fcmax: '60-70', rpe: '4-5', descripcion_cliente: 'Ritmo donde puedas hablar pero no cantar.' },
        continuous_moderate: { zona_fc: 3, porcentaje_fcmax: '70-80', rpe: '6-7', descripcion_cliente: 'Ritmo donde solo puedes decir frases cortas.' },
        intervals:           { zona_fc: 4, porcentaje_fcmax: '85-95', rpe: '8-9', descripcion_cliente: 'En las fases de trabajo, vas muy fuerte. En descanso, baja a caminar.' },
        tabata:              { zona_fc: 5, porcentaje_fcmax: '90-100', rpe: '9-10', descripcion_cliente: 'Cada 20 segundos vas con todo. Sin reservar.' },
        circuit:             { zona_fc: 3, porcentaje_fcmax: '70-85', rpe: '7-8', descripcion_cliente: 'Ritmo sostenido difícil. Mínimo descanso entre estaciones.' },
        free:                { zona_fc: null, porcentaje_fcmax: null, rpe: null, descripcion_cliente: null },
    };
    return map[cardioType] || map.free;
}

/**
 * Composable wrapper (Vue 3). Recibe un ref del ejercicio, devuelve computed.
 *
 * import { computed } from 'vue';
 * import { useCardioInference } from '@/composables/useCardioInference';
 *
 * const { cardioType, intensity, isInterval, isContinuous } = useCardioInference(exerciseRef);
 */
export function useCardioInference(exerciseRef) {
    const cardioType = computed(() => inferCardioType(exerciseRef.value));
    const intensity = computed(() => {
        const ex = exerciseRef.value || {};
        return ex.intensidad || defaultIntensityFor(cardioType.value);
    });

    return {
        cardioType,
        intensity,
        isInterval:   computed(() => isIntervalBasedType(cardioType.value)),
        isContinuous: computed(() => isContinuousType(cardioType.value)),
        isAmrap:      computed(() => isAmrapType(cardioType.value)),
    };
}
