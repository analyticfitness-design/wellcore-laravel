<script setup>
/**
 * CardioIntervals.vue
 *
 * Renderiza cardio de tipo `intervals` (HIIT clásico).
 * Lee work/rest/rounds del protocol JSON o aplica inferencia desde
 * el texto del ejercicio.
 *
 * Patrón típico WellCore: "30 seg trabajo / 30 seg descanso × 8 rondas"
 *
 * Para retrocompatibilidad con planes legacy que usan el "hack circuito"
 * (Lizeth sábado: bloque:'circuito' + grupo_id + repeticiones en segundos),
 * intenta extraer work/rest desde `repeticiones` y `descanso`.
 */
import { computed, ref } from 'vue';
import CardioIntensityChip from './shared/CardioIntensityChip.vue';
import CardioIntervalEngine from './shared/CardioIntervalEngine.vue';
import CardioRPEPrompt from './shared/CardioRPEPrompt.vue';

const props = defineProps({
    exercise:      { type: Object, required: true },
    exerciseIndex: { type: Number, required: true },
    cardioType:    { type: String, default: 'intervals' },
    intensity:     { type: Object, default: () => ({}) },
    sessionId:     { type: [Number, null], default: null },
    completed:     { type: Boolean, default: false },
});

const emit = defineEmits(['complete', 'uncomplete']);

const showRpe = ref(false);
const finalStats = ref({ totalSec: 0, roundsCompleted: 0 });

function firstNumber(input, fallback = 0) {
    if (input == null) return fallback;
    const str = String(input).replace(',', '.');
    const m = str.match(/(\d+(?:\.\d+)?)/);
    return m ? parseFloat(m[1]) : fallback;
}

function parseSeconds(str, fallback = 30) {
    if (!str) return fallback;
    const s = String(str).toLowerCase();
    if (/seg|sec|\bs\b/.test(s)) return firstNumber(s, fallback);
    if (/min/.test(s)) return firstNumber(s, fallback / 60) * 60;
    return firstNumber(s, fallback);
}

// Lee protocol del JSON si existe, sino infiere desde el ejercicio.
const protocol = computed(() => {
    const p = props.exercise.protocol || {};
    if (p.work_seconds && p.rest_seconds && p.rounds) {
        return {
            workSeconds: parseInt(p.work_seconds),
            restSeconds: parseInt(p.rest_seconds),
            rounds:      parseInt(p.rounds),
            warmupMin:   parseFloat(p.warmup_min) || 0,
            cooldownMin: parseFloat(p.cooldown_min) || 0,
        };
    }

    // Inferencia desde campos legacy: repeticiones + descanso + rondas
    const workSeconds = parseSeconds(props.exercise.repeticiones || props.exercise.reps, 30);
    const restSeconds = parseSeconds(props.exercise.descanso || props.exercise.rest, 30);
    const rounds = parseInt(props.exercise.rondas || props.exercise.rounds || props.exercise.series || 8);
    return {
        workSeconds,
        restSeconds,
        rounds,
        warmupMin: 0,
        cooldownMin: 0,
    };
});

const totalMinutes = computed(() => {
    const p = protocol.value;
    const totalSec = (p.workSeconds + p.restSeconds) * p.rounds + (p.warmupMin + p.cooldownMin) * 60;
    return Math.round(totalSec / 60);
});

function onAllDone(payload) {
    finalStats.value = payload;
    showRpe.value = true;
}

function onAbandon() {
    showRpe.value = true;
}

function onRpeSubmit({ rpe, notes }) {
    showRpe.value = false;
    emit('complete', {
        exerciseIndex: props.exerciseIndex,
        cardioType: props.cardioType,
        durationActualSec: finalStats.value.totalSec,
        durationPlannedSec: totalMinutes.value * 60,
        roundsPlanned: protocol.value.rounds,
        roundsCompleted: finalStats.value.roundsCompleted,
        rpe,
        notes,
    });
}

function onRpeSkip() {
    showRpe.value = false;
    emit('complete', {
        exerciseIndex: props.exerciseIndex,
        cardioType: props.cardioType,
        durationActualSec: finalStats.value.totalSec,
        durationPlannedSec: totalMinutes.value * 60,
        roundsPlanned: protocol.value.rounds,
        roundsCompleted: finalStats.value.roundsCompleted,
        rpe: null,
        notes: null,
    });
}
</script>

<template>
  <div class="cardio-intervals">
    <CardioIntensityChip :intensity="intensity" />

    <div class="cardio-intervals__summary">
      <span class="cardio-intervals__chip">{{ protocol.workSeconds }}s trabajo</span>
      <span class="cardio-intervals__sep">·</span>
      <span class="cardio-intervals__chip">{{ protocol.restSeconds }}s descanso</span>
      <span class="cardio-intervals__sep">·</span>
      <span class="cardio-intervals__chip">{{ protocol.rounds }} rondas</span>
      <span class="cardio-intervals__sep">·</span>
      <span class="cardio-intervals__chip cardio-intervals__chip--total">~{{ totalMinutes }} min</span>
    </div>

    <CardioIntervalEngine
      v-if="!completed"
      :work-seconds="protocol.workSeconds"
      :rest-seconds="protocol.restSeconds"
      :rounds="protocol.rounds"
      :warmup-min="protocol.warmupMin"
      :cooldown-min="protocol.cooldownMin"
      @all-done="onAllDone"
      @abandon="onAbandon"
    />

    <div v-else class="cardio-intervals__done">✓ Intervalos completados</div>

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
.cardio-intervals {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.cardio-intervals__summary {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  font-size: 12px;
}
.cardio-intervals__chip {
  font-weight: 600;
  color: rgb(229, 231, 235);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.cardio-intervals__chip--total {
  color: rgb(248, 113, 113);
}
.cardio-intervals__sep {
  color: rgb(75, 85, 99);
}
.cardio-intervals__done {
  text-align: center;
  padding: 16px;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 8px;
  color: rgb(134, 239, 172);
  font-weight: 600;
}
</style>
