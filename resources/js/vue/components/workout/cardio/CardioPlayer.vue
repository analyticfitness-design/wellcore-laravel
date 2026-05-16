<script setup>
/**
 * CardioPlayer.vue — Dispatcher polimorfo del módulo cardio.
 *
 * Recibe un ejercicio, infiere su `cardio_type` y rutea al sub-componente
 * correspondiente. NO modifica el JSON del plan: la inferencia vive aquí,
 * en el frontend.
 *
 * Si `cardio_type` es 'free' o si el plan no tiene infraestructura cardio v2,
 * el padre debe renderizar el SetRow cardio actual como fallback.
 */
import { computed, defineAsyncComponent } from 'vue';
import { inferCardioType, defaultIntensityFor } from '../../../composables/useCardioInference';

const props = defineProps({
    exercise:      { type: Object, required: true },
    exerciseIndex: { type: Number, required: true },
    sessionId:     { type: [Number, null], default: null },
    completed:     { type: Boolean, default: false },
});

const emit = defineEmits(['complete', 'uncomplete']);

const CardioContinuous = defineAsyncComponent(() => import('./CardioContinuous.vue'));
const CardioIntervals  = defineAsyncComponent(() => import('./CardioIntervals.vue'));
const CardioTabata     = defineAsyncComponent(() => import('./CardioTabata.vue'));
// F2b agregará: CardioCircuit (AMRAP/EMOM)

const cardioType = computed(() => inferCardioType(props.exercise));
const intensity  = computed(() => props.exercise.intensidad || defaultIntensityFor(cardioType.value));

const componentMap = {
    continuous_low:      CardioContinuous,
    continuous_moderate: CardioContinuous,
    intervals:           CardioIntervals,
    tabata:              CardioTabata,
    // circuit:   CardioCircuit,     // F2b
};

const activeComponent = computed(() => componentMap[cardioType.value] || null);

// Si no hay componente especializado, el padre maneja fallback
const supportsCardioV2 = computed(() => activeComponent.value !== null);

defineExpose({ cardioType, supportsCardioV2 });
</script>

<template>
  <component
    v-if="activeComponent"
    :is="activeComponent"
    :exercise="exercise"
    :exercise-index="exerciseIndex"
    :cardio-type="cardioType"
    :intensity="intensity"
    :session-id="sessionId"
    :completed="completed"
    @complete="$emit('complete', $event)"
    @uncomplete="$emit('uncomplete', $event)"
  />
  <!-- Si no hay match, parent renderiza SetRow cardio legacy como fallback -->
</template>
