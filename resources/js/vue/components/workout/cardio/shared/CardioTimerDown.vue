<script setup>
/**
 * CardioTimerDown.vue
 *
 * Cronómetro descendente con barra de progreso para cardio continuous.
 * Resistente a background suspend usando timestamps absolutos.
 *
 * Props:
 *   - duracionMin: minutos planificados (Number, required)
 *   - autoStart: arrancar al montar (Boolean, default false)
 *
 * Emits:
 *   - @start({startedAt})         cuando el usuario arranca
 *   - @complete({durationActualSec, durationPlannedSec})  al llegar a 0
 *   - @abandon                    cuando el usuario detiene antes de 0
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    duracionMin: { type: Number, required: true },
    autoStart:   { type: Boolean, default: false },
});

const emit = defineEmits(['start', 'complete', 'abandon']);

const durationPlannedSec = computed(() => Math.max(0, Math.floor(props.duracionMin * 60)));

const elapsed = ref(0);
const running = ref(false);
const paused  = ref(false);
let startTs = 0;
let pausedAt = 0;
let pausedTotal = 0;
let raf = 0;

const remaining = computed(() => Math.max(0, durationPlannedSec.value - elapsed.value));
const progressPct = computed(() => {
    if (durationPlannedSec.value === 0) return 0;
    return Math.min(100, (elapsed.value / durationPlannedSec.value) * 100);
});

function fmt(totalSec) {
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    return (h > 0 ? `${h}:` : '') + `${mStr}:${sStr}`;
}

const displayRemaining = computed(() => fmt(remaining.value));
const displayElapsed   = computed(() => fmt(elapsed.value));

function tick() {
    if (!running.value || paused.value) return;
    const now = Date.now();
    elapsed.value = Math.floor((now - startTs - pausedTotal) / 1000);
    if (elapsed.value >= durationPlannedSec.value) {
        elapsed.value = durationPlannedSec.value;
        finish();
        return;
    }
    raf = requestAnimationFrame(tick);
}

function start() {
    if (running.value) return;
    running.value = true;
    paused.value = false;
    startTs = Date.now();
    pausedTotal = 0;
    elapsed.value = 0;
    emit('start', { startedAt: startTs });
    raf = requestAnimationFrame(tick);
}

function pause() {
    if (!running.value || paused.value) return;
    paused.value = true;
    pausedAt = Date.now();
    cancelAnimationFrame(raf);
}

function resume() {
    if (!running.value || !paused.value) return;
    pausedTotal += Date.now() - pausedAt;
    paused.value = false;
    raf = requestAnimationFrame(tick);
}

function stop() {
    if (!running.value) return;
    running.value = false;
    paused.value = false;
    cancelAnimationFrame(raf);
    emit('abandon');
}

function finish() {
    running.value = false;
    cancelAnimationFrame(raf);
    if (navigator.vibrate) navigator.vibrate([200, 50, 200, 50, 400]);
    emit('complete', {
        durationActualSec: elapsed.value,
        durationPlannedSec: durationPlannedSec.value,
    });
}

onMounted(() => {
    if (props.autoStart) start();
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
});

// Resync al volver del background
function handleVisibility() {
    if (document.visibilityState === 'visible' && running.value && !paused.value) {
        tick();
    }
}
onMounted(() => document.addEventListener('visibilitychange', handleVisibility));
onBeforeUnmount(() => document.removeEventListener('visibilitychange', handleVisibility));

defineExpose({ start, pause, resume, stop });
</script>

<template>
  <div class="cardio-timer-down">
    <div class="cardio-timer-down__display">
      <div class="cardio-timer-down__remaining">{{ displayRemaining }}</div>
      <div class="cardio-timer-down__sub">
        <span v-if="running">Restante</span>
        <span v-else>{{ duracionMin }} min</span>
      </div>
    </div>

    <div class="cardio-timer-down__bar">
      <div class="cardio-timer-down__bar-fill" :style="{ width: progressPct + '%' }"></div>
    </div>

    <div class="cardio-timer-down__elapsed" v-if="running">
      Transcurrido: {{ displayElapsed }}
    </div>

    <div class="cardio-timer-down__actions">
      <button v-if="!running" type="button" class="cardio-timer-down__btn cardio-timer-down__btn--primary" @click="start">
        Empezar
      </button>
      <template v-else>
        <button v-if="!paused" type="button" class="cardio-timer-down__btn" @click="pause">Pausa</button>
        <button v-else      type="button" class="cardio-timer-down__btn cardio-timer-down__btn--primary" @click="resume">Continuar</button>
        <button type="button" class="cardio-timer-down__btn cardio-timer-down__btn--ghost" @click="stop">Detener</button>
      </template>
    </div>
  </div>
</template>

<style scoped>
.cardio-timer-down {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 16px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.cardio-timer-down__display {
  text-align: center;
}
.cardio-timer-down__remaining {
  font-family: 'Oswald', system-ui, sans-serif;
  font-size: 48px;
  font-weight: 700;
  line-height: 1;
  color: rgb(255, 255, 255);
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}
.cardio-timer-down__sub {
  font-size: 12px;
  color: rgb(156, 163, 175);
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.cardio-timer-down__bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}
.cardio-timer-down__bar-fill {
  height: 100%;
  background: linear-gradient(90deg, rgb(34, 197, 94), rgb(220, 38, 38));
  transition: width 0.3s ease;
}
.cardio-timer-down__elapsed {
  font-size: 12px;
  color: rgb(156, 163, 175);
  text-align: center;
}
.cardio-timer-down__actions {
  display: flex;
  gap: 8px;
  justify-content: center;
}
.cardio-timer-down__btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: rgb(229, 231, 235);
  cursor: pointer;
  min-width: 100px;
  min-height: 44px;
}
.cardio-timer-down__btn:hover { background: rgba(255, 255, 255, 0.08); }
.cardio-timer-down__btn--primary {
  background: rgb(220, 38, 38);
  border-color: rgb(220, 38, 38);
  color: white;
}
.cardio-timer-down__btn--primary:hover { background: rgb(185, 28, 28); }
.cardio-timer-down__btn--ghost {
  background: transparent;
  color: rgb(156, 163, 175);
}
</style>
