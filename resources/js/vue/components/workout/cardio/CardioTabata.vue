<script setup>
/**
 * CardioTabata.vue
 *
 * Preset rígido: 20 segundos trabajo / 10 segundos descanso × 8 rondas = 4 minutos.
 * No editable por el cliente (es lo que es). El coach NO puede cambiar work/rest/rounds
 * sin elegir otro cardio_type — por eso es preset.
 *
 * Wrapper minimal sobre CardioIntervalEngine con valores hardcoded.
 */
import { ref, computed } from 'vue';
import CardioIntensityChip from './shared/CardioIntensityChip.vue';
import CardioIntervalEngine from './shared/CardioIntervalEngine.vue';
import CardioRPEPrompt from './shared/CardioRPEPrompt.vue';

const props = defineProps({
    exercise:      { type: Object, required: true },
    exerciseIndex: { type: Number, required: true },
    cardioType:    { type: String, default: 'tabata' },
    intensity:     { type: Object, default: () => ({}) },
    sessionId:     { type: [Number, null], default: null },
    completed:     { type: Boolean, default: false },
});

const emit = defineEmits(['complete', 'uncomplete']);

const TABATA = { workSeconds: 20, restSeconds: 10, rounds: 8 };

const showRpe = ref(false);
const finalStats = ref({ totalSec: 0, roundsCompleted: 0 });

const warmupMin = computed(() => parseFloat(props.exercise.protocol?.warmup_min) || 0);
const cooldownMin = computed(() => parseFloat(props.exercise.protocol?.cooldown_min) || 0);

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
        cardioType: 'tabata',
        durationActualSec: finalStats.value.totalSec,
        durationPlannedSec: 240,  // 4 min exactos
        roundsPlanned: TABATA.rounds,
        roundsCompleted: finalStats.value.roundsCompleted,
        rpe,
        notes,
    });
}

function onRpeSkip() {
    showRpe.value = false;
    emit('complete', {
        exerciseIndex: props.exerciseIndex,
        cardioType: 'tabata',
        durationActualSec: finalStats.value.totalSec,
        durationPlannedSec: 240,
        roundsPlanned: TABATA.rounds,
        roundsCompleted: finalStats.value.roundsCompleted,
        rpe: null,
        notes: null,
    });
}
</script>

<template>
  <div class="cardio-tabata">
    <CardioIntensityChip :intensity="intensity" />

    <div class="cardio-tabata__banner">
      <div class="cardio-tabata__banner-title">TABATA</div>
      <div class="cardio-tabata__banner-spec">8 × (20 seg máximo / 10 seg descanso) — 4 minutos exactos</div>
      <div class="cardio-tabata__banner-warning">
        Cada bloque de 20 segundos va con TODO. Sin reservar.
      </div>
    </div>

    <CardioIntervalEngine
      v-if="!completed"
      :work-seconds="TABATA.workSeconds"
      :rest-seconds="TABATA.restSeconds"
      :rounds="TABATA.rounds"
      :warmup-min="warmupMin"
      :cooldown-min="cooldownMin"
      @all-done="onAllDone"
      @abandon="onAbandon"
    />

    <div v-else class="cardio-tabata__done">✓ Tabata completado</div>

    <CardioRPEPrompt
      :show="showRpe"
      cardio-type="tabata"
      :expected-rpe="intensity?.rpe || '9-10'"
      @submit="onRpeSubmit"
      @skip="onRpeSkip"
    />
  </div>
</template>

<style scoped>
.cardio-tabata {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.cardio-tabata__banner {
  padding: 12px 14px;
  background: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.3);
  border-radius: 8px;
  text-align: center;
}
.cardio-tabata__banner-title {
  font-family: 'Oswald', system-ui, sans-serif;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: rgb(248, 113, 113);
}
.cardio-tabata__banner-spec {
  font-size: 13px;
  color: rgb(229, 231, 235);
  margin-top: 4px;
}
.cardio-tabata__banner-warning {
  font-size: 11px;
  color: rgb(252, 165, 165);
  margin-top: 6px;
  font-style: italic;
}
.cardio-tabata__done {
  text-align: center;
  padding: 16px;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 8px;
  color: rgb(134, 239, 172);
  font-weight: 600;
}
</style>
