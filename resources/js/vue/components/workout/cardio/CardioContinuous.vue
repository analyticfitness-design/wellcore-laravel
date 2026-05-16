<script setup>
/**
 * CardioContinuous.vue
 *
 * Renderiza cardio de tipo `continuous_low` (LISS) o `continuous_moderate` (MISS).
 * Combina:
 *   - CardioIntensityChip (guía de zona/RPE/lenguaje cliente)
 *   - CardioTimerDown     (cronómetro descendente)
 *   - CardioRPEPrompt     (al completar pide RPE 1-10)
 *   - Inputs opcionales velocidad/inclinación (compatibles con caminadora/bici)
 *
 * Persiste la sesión vía POST /api/v/client/workout/complete-set con:
 *   { is_cardio: true, cardio_type, duration_minutes, speed_kmh, incline_percent, rpe }
 */
import { ref, computed } from 'vue';
import CardioIntensityChip from './shared/CardioIntensityChip.vue';
import CardioTimerDown from './shared/CardioTimerDown.vue';
import CardioRPEPrompt from './shared/CardioRPEPrompt.vue';

const props = defineProps({
    exercise:      { type: Object, required: true },
    exerciseIndex: { type: Number, required: true },
    cardioType:    { type: String, default: 'continuous_low' },
    intensity:     { type: Object, default: () => ({}) },
    sessionId:     { type: [Number, null], default: null },
    completed:     { type: Boolean, default: false },
});

const emit = defineEmits(['complete', 'uncomplete']);

const showRpe = ref(false);
const durationActualSec = ref(0);
const speed   = ref(parseFloat(props.exercise.velocidad_kmh) || 0);
const incline = ref(parseFloat(props.exercise.inclinacion_pct) || 0);

const duracionMin = computed(() => {
    const d = parseInt(props.exercise.duracion_min || props.exercise.duracion || props.exercise.duration || 30);
    return isNaN(d) ? 30 : d;
});

function onTimerComplete({ durationActualSec: secs }) {
    durationActualSec.value = secs;
    showRpe.value = true;
}

function onTimerAbandon() {
    // El cliente detuvo antes de tiempo — preguntar igual RPE
    showRpe.value = true;
}

function onRpeSubmit({ rpe, notes }) {
    showRpe.value = false;
    emit('complete', {
        exerciseIndex: props.exerciseIndex,
        cardioType: props.cardioType,
        durationActualSec: durationActualSec.value,
        durationPlannedSec: duracionMin.value * 60,
        speed: speed.value,
        incline: incline.value,
        rpe,
        notes,
    });
}

function onRpeSkip() {
    showRpe.value = false;
    // Guardamos sin RPE (cardioBonus reducido a 15 vs 25)
    emit('complete', {
        exerciseIndex: props.exerciseIndex,
        cardioType: props.cardioType,
        durationActualSec: durationActualSec.value,
        durationPlannedSec: duracionMin.value * 60,
        speed: speed.value,
        incline: incline.value,
        rpe: null,
        notes: null,
    });
}

const showEquipmentInputs = computed(() => {
    // Mostrar campos vel/inc solo si el ejercicio los menciona en el plan
    return props.exercise.velocidad_kmh !== undefined ||
           props.exercise.inclinacion_pct !== undefined ||
           /caminadora|cinta|bici|elíptica|eliptica/i.test(props.exercise.nombre || '');
});
</script>

<template>
  <div class="cardio-continuous">
    <CardioIntensityChip :intensity="intensity" />

    <CardioTimerDown
      v-if="!completed"
      :duracion-min="duracionMin"
      @complete="onTimerComplete"
      @abandon="onTimerAbandon"
    />

    <div v-if="showEquipmentInputs && !completed" class="cardio-continuous__equipment">
      <div class="cardio-continuous__field">
        <label>Velocidad (km/h)</label>
        <input type="number" inputmode="decimal" step="0.5" min="0" v-model.number="speed" :placeholder="exercise.velocidad_kmh || '0'" />
      </div>
      <div class="cardio-continuous__field">
        <label>Inclinación (%)</label>
        <input type="number" inputmode="decimal" step="1" min="0" v-model.number="incline" :placeholder="exercise.inclinacion_pct || '0'" />
      </div>
    </div>

    <div v-if="completed" class="cardio-continuous__done">
      ✓ Sesión completada
    </div>

    <CardioRPEPrompt
      :show="showRpe"
      :cardio-type="cardioType"
      :expected-rpe="intensity?.rpe || ''"
      @submit="onRpeSubmit"
      @skip="onRpeSkip"
    />
  </div>
</template>

<style scoped>
.cardio-continuous {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.cardio-continuous__equipment {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.cardio-continuous__field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.cardio-continuous__field label {
  font-size: 11px;
  color: rgb(156, 163, 175);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.cardio-continuous__field input {
  padding: 10px 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  color: rgb(229, 231, 235);
  font-size: 15px;
  font-variant-numeric: tabular-nums;
  min-height: 44px;
}
.cardio-continuous__done {
  text-align: center;
  padding: 16px;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 8px;
  color: rgb(134, 239, 172);
  font-weight: 600;
}
</style>
