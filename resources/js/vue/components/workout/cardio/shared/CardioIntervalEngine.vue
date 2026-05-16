<script setup>
/**
 * CardioIntervalEngine.vue
 *
 * Motor de intervalos trabajo↔descanso con audio cues automáticos.
 * Cubre HIIT clásico (work/rest configurables) y Tabata (20/10×8).
 *
 * Resistente a background suspend usando requestAnimationFrame + timestamp absoluto.
 *
 * Props:
 *   - workSeconds:  duración fase trabajo
 *   - restSeconds:  duración fase descanso
 *   - rounds:       número total de rondas
 *   - warmupMin:    minutos de warmup antes del primer trabajo (default 0)
 *   - cooldownMin:  minutos de cooldown después del último descanso (default 0)
 *
 * Emits:
 *   - @phase-change({phase, round})  cada vez que cambia work↔rest
 *   - @round-complete({round})       al terminar work + rest de una ronda
 *   - @all-done({totalSec, roundsCompleted})  al completar todas las rondas
 *   - @abandon                        al detener antes
 */
import { ref, computed, onBeforeUnmount, onMounted } from 'vue';
import { useCardioAudio } from '../../../../composables/useCardioAudio';

const props = defineProps({
    workSeconds:  { type: Number, required: true },
    restSeconds:  { type: Number, required: true },
    rounds:       { type: Number, required: true },
    warmupMin:    { type: Number, default: 0 },
    cooldownMin:  { type: Number, default: 0 },
});

const emit = defineEmits(['phase-change', 'round-complete', 'all-done', 'abandon']);

const audio = useCardioAudio();

const PHASE = {
    IDLE:     'idle',
    WARMUP:   'warmup',
    WORK:     'work',
    REST:     'rest',
    COOLDOWN: 'cooldown',
    DONE:     'done',
};

const phase = ref(PHASE.IDLE);
const currentRound = ref(0);
const phaseElapsed = ref(0);
const totalElapsed = ref(0);
const running = ref(false);
const paused  = ref(false);

let phaseStartTs = 0;
let totalStartTs = 0;
let pausedTotal  = 0;
let pausedAt     = 0;
let raf = 0;
let lastBeepSec = -1;

const warmupSec   = computed(() => Math.max(0, Math.floor(props.warmupMin * 60)));
const cooldownSec = computed(() => Math.max(0, Math.floor(props.cooldownMin * 60)));

const phaseDuration = computed(() => {
    switch (phase.value) {
        case PHASE.WARMUP:   return warmupSec.value;
        case PHASE.WORK:     return props.workSeconds;
        case PHASE.REST:     return props.restSeconds;
        case PHASE.COOLDOWN: return cooldownSec.value;
        default:             return 0;
    }
});

const phaseRemaining = computed(() => Math.max(0, phaseDuration.value - phaseElapsed.value));

const phaseProgressPct = computed(() => {
    if (phaseDuration.value === 0) return 0;
    return Math.min(100, (phaseElapsed.value / phaseDuration.value) * 100);
});

const totalRoundsPlanned = computed(() => props.rounds);

const phaseLabel = computed(() => {
    switch (phase.value) {
        case PHASE.WARMUP:   return 'Calentamiento';
        case PHASE.WORK:     return 'Trabajo';
        case PHASE.REST:     return 'Descanso';
        case PHASE.COOLDOWN: return 'Enfriamiento';
        case PHASE.DONE:     return 'Listo';
        default:             return 'Preparado';
    }
});

const phaseClass = computed(() => `phase--${phase.value}`);

function fmt(sec) {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

const displayRemaining = computed(() => fmt(phaseRemaining.value));
const displayTotal     = computed(() => fmt(totalElapsed.value));

function tick() {
    if (!running.value || paused.value) return;
    const now = Date.now();
    phaseElapsed.value = Math.floor((now - phaseStartTs - pausedTotal) / 1000);
    totalElapsed.value = Math.floor((now - totalStartTs - pausedTotal) / 1000);

    // Beep últimos 3 segundos antes de cambio de fase
    if (phaseRemaining.value <= 3 && phaseRemaining.value > 0 && lastBeepSec !== phaseRemaining.value) {
        lastBeepSec = phaseRemaining.value;
        if (phase.value === PHASE.WORK) audio.workEnding();
        else if (phase.value === PHASE.REST) audio.restEnding();
    }

    if (phaseRemaining.value <= 0) {
        nextPhase();
        return;
    }
    raf = requestAnimationFrame(tick);
}

function nextPhase() {
    const prev = phase.value;
    pausedTotal = 0;
    phaseStartTs = Date.now();
    phaseElapsed.value = 0;
    lastBeepSec = -1;

    if (prev === PHASE.WARMUP) {
        phase.value = PHASE.WORK;
        currentRound.value = 1;
        audio.workStart();
    } else if (prev === PHASE.WORK) {
        if (props.restSeconds > 0) {
            phase.value = PHASE.REST;
            audio.restStart();
        } else {
            // Sin descanso → directo a siguiente ronda o end
            advanceRoundOrEnd();
        }
    } else if (prev === PHASE.REST) {
        advanceRoundOrEnd();
    } else if (prev === PHASE.COOLDOWN) {
        finish();
        return;
    } else {
        // IDLE → arrancar warmup o trabajo directo
        if (warmupSec.value > 0) {
            phase.value = PHASE.WARMUP;
        } else {
            phase.value = PHASE.WORK;
            currentRound.value = 1;
            audio.workStart();
        }
    }
    emit('phase-change', { phase: phase.value, round: currentRound.value });
    raf = requestAnimationFrame(tick);
}

function advanceRoundOrEnd() {
    emit('round-complete', { round: currentRound.value });
    if (currentRound.value >= totalRoundsPlanned.value) {
        // Última ronda completada
        if (cooldownSec.value > 0) {
            phase.value = PHASE.COOLDOWN;
            audio.restStart();  // tono de descanso para enfriamiento
        } else {
            finish();
            return;
        }
    } else {
        currentRound.value += 1;
        // Aviso especial si es la última
        if (currentRound.value === totalRoundsPlanned.value) {
            audio.lastRound();
            setTimeout(() => audio.workStart(), 250);
        } else {
            audio.workStart();
        }
        phase.value = PHASE.WORK;
    }
}

function start() {
    if (running.value) return;
    audio.prime();  // desbloquear AudioContext con interacción del usuario
    running.value = true;
    paused.value = false;
    totalStartTs = Date.now();
    pausedTotal = 0;
    phaseElapsed.value = 0;
    totalElapsed.value = 0;
    nextPhase();
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
    paused.value = false;
    cancelAnimationFrame(raf);
    phase.value = PHASE.DONE;
    audio.allDone();
    emit('all-done', {
        totalSec: totalElapsed.value,
        roundsCompleted: currentRound.value,
    });
}

function handleVisibility() {
    if (document.visibilityState === 'visible' && running.value && !paused.value) {
        tick();
    }
}

onMounted(() => document.addEventListener('visibilitychange', handleVisibility));
onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    document.removeEventListener('visibilitychange', handleVisibility);
});

defineExpose({ start, pause, resume, stop });
</script>

<template>
  <div class="interval-engine" :class="phaseClass">
    <header class="interval-engine__header">
      <div class="interval-engine__phase-label">{{ phaseLabel }}</div>
      <div v-if="phase !== 'idle' && phase !== 'done'" class="interval-engine__round">
        Ronda {{ currentRound }} / {{ totalRoundsPlanned }}
      </div>
    </header>

    <div class="interval-engine__display">
      <div class="interval-engine__remaining">{{ displayRemaining }}</div>
    </div>

    <div class="interval-engine__bar">
      <div class="interval-engine__bar-fill" :style="{ width: phaseProgressPct + '%' }"></div>
    </div>

    <div class="interval-engine__total">Total: {{ displayTotal }}</div>

    <div class="interval-engine__actions">
      <button v-if="phase === 'idle'" type="button" class="interval-engine__btn interval-engine__btn--primary" @click="start">
        Empezar
      </button>
      <template v-else-if="phase !== 'done'">
        <button v-if="!paused" type="button" class="interval-engine__btn" @click="pause">Pausa</button>
        <button v-else type="button" class="interval-engine__btn interval-engine__btn--primary" @click="resume">Continuar</button>
        <button type="button" class="interval-engine__btn interval-engine__btn--ghost" @click="stop">Detener</button>
      </template>
    </div>

    <div class="interval-engine__audio">
      <button type="button" class="interval-engine__audio-btn" @click="audio.toggle">
        <span v-if="audio.enabled.value">🔊 Audio</span>
        <span v-else>🔇 Sin audio</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.interval-engine {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 16px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-left: 4px solid rgb(156, 163, 175);
  transition: border-color 0.2s;
}
.interval-engine.phase--warmup    { border-left-color: rgb(59, 130, 246); }
.interval-engine.phase--work      { border-left-color: rgb(220, 38, 38); }
.interval-engine.phase--rest      { border-left-color: rgb(34, 197, 94); }
.interval-engine.phase--cooldown  { border-left-color: rgb(59, 130, 246); }
.interval-engine.phase--done      { border-left-color: rgb(245, 158, 11); }

.interval-engine__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.interval-engine__phase-label {
  font-family: 'Oswald', system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgb(229, 231, 235);
}
.phase--work .interval-engine__phase-label { color: rgb(248, 113, 113); }
.phase--rest .interval-engine__phase-label { color: rgb(134, 239, 172); }
.interval-engine__round {
  font-size: 13px;
  color: rgb(156, 163, 175);
  font-variant-numeric: tabular-nums;
}
.interval-engine__display { text-align: center; padding: 8px 0; }
.interval-engine__remaining {
  font-family: 'Oswald', system-ui, sans-serif;
  font-size: 64px;
  font-weight: 700;
  line-height: 1;
  color: rgb(255, 255, 255);
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}
.interval-engine__bar {
  height: 8px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 999px;
  overflow: hidden;
}
.interval-engine__bar-fill {
  height: 100%;
  transition: width 0.15s linear;
  background: rgb(229, 231, 235);
}
.phase--work .interval-engine__bar-fill { background: rgb(220, 38, 38); }
.phase--rest .interval-engine__bar-fill { background: rgb(34, 197, 94); }

.interval-engine__total {
  font-size: 12px;
  color: rgb(156, 163, 175);
  text-align: center;
}
.interval-engine__actions {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-top: 4px;
}
.interval-engine__btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: rgb(229, 231, 235);
  cursor: pointer;
  min-width: 100px;
  min-height: 44px;
}
.interval-engine__btn--primary {
  background: rgb(220, 38, 38);
  border-color: rgb(220, 38, 38);
  color: white;
}
.interval-engine__btn--ghost {
  background: transparent;
  color: rgb(156, 163, 175);
}
.interval-engine__audio {
  display: flex;
  justify-content: center;
}
.interval-engine__audio-btn {
  font-size: 11px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgb(156, 163, 175);
  padding: 4px 10px;
  border-radius: 999px;
  cursor: pointer;
}
</style>
