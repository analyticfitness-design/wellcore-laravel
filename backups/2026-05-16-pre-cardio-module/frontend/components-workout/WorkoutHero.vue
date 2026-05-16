<script setup>
/**
 * WorkoutHero.vue — Hero "EN CURSO" con timer grande, ring de progreso y dots de ejercicios.
 * Solo se muestra cuando workoutStarted=true.
 *
 * Visual fidelity: viewBox 100x100 r=44 (target HTML).
 */
import { computed } from 'vue';

const props = defineProps({
    elapsedDisplay:       { type: String, default: '00:00' },
    progressPct:          { type: Number, default: 0 },
    completedExercises:   { type: Number, default: 0 },
    totalExercises:       { type: Number, default: 0 },
    phaseLabel:           { type: String, default: '' },
    exercises:            { type: Array, default: () => [] },
    currentExerciseIndex: { type: Number, default: 0 },
    exerciseStates:       { type: Array, default: () => [] },
});

// Ring: viewBox 100x100, r=44 → C = 2*pi*44 = 276.46
const RING_RADIUS = 44;
const RING_CIRC = 2 * Math.PI * RING_RADIUS;

const ringDashOffset = computed(() => {
    return RING_CIRC * (1 - Math.min(100, Math.max(0, props.progressPct)) / 100);
});

const subline = computed(() => {
    const phase = props.phaseLabel ? ` · ${props.phaseLabel}` : '';
    if (props.progressPct === 0)  return `Sesión activa${phase}`;
    if (props.progressPct < 50)   return `Progresando${phase}`;
    if (props.progressPct < 100)  return `Más del 50%${phase}`;
    return `Sesión completa${phase}`;
});

const dots = computed(() => {
    const total = Math.max(1, props.totalExercises || props.exercises.length || 0);
    const states = props.exerciseStates;
    return Array.from({ length: total }, (_, i) => states[i] || 'pending');
});

const dotsGridStyle = computed(() => {
    const n = dots.value.length;
    return `grid-template-columns: repeat(${Math.min(n, 12)}, 1fr);`;
});
</script>

<template>
  <section class="hero">
    <div class="hero-row">
      <div class="hero-left">
        <div class="live-tag">
          <span class="live-dot" aria-hidden="true"></span>
          <span>En curso</span>
        </div>
        <div class="timer wc-tabular">{{ elapsedDisplay }}</div>
        <div class="timer-sub">{{ subline }}</div>
      </div>

      <div class="ring-wrap" aria-label="Progreso de la sesión">
        <svg viewBox="0 0 100 100">
          <circle class="ring-bg" cx="50" cy="50" r="44" fill="none" stroke-width="6"/>
          <circle
            class="ring-fg"
            cx="50" cy="50" r="44" fill="none" stroke-width="6"
            :stroke-dasharray="RING_CIRC"
            :stroke-dashoffset="ringDashOffset"
          />
        </svg>
        <div class="ring-label">
          <div>
            <div class="pct">{{ progressPct }}%</div>
            <div class="frac">{{ completedExercises }}/{{ totalExercises }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="exercise-dots" :style="dotsGridStyle" role="presentation">
      <span
        v-for="(s, i) in dots"
        :key="i"
        class="dot"
        :class="{ 'dot--done': s === 'done', 'dot--active': s === 'active' }"
      ></span>
    </div>
  </section>
</template>

<style scoped>
.hero {
  margin-top: 12px;
  padding: 14px 16px;
  border-radius: 16px;
  background:
    radial-gradient(circle at 100% 0%, rgba(239,68,68,0.14), transparent 60%),
    linear-gradient(180deg, rgba(220,38,38,0.08), rgba(220,38,38,0.02));
  border: 1px solid rgba(239,68,68,0.20);
  position: relative;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 0% 100%, rgba(220,38,38,0.08), transparent 50%);
  pointer-events: none;
}
@media (min-width: 1024px) { .hero { padding: 16px 20px; } }

.hero-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  position: relative;
}

.hero-left {
  min-width: 0;
  flex: 1;
}

.live-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-accent-glow, #EF4444);
}
.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: var(--color-wc-accent-glow, #EF4444);
  box-shadow: 0 0 0 0 rgba(239,68,68,0.6);
  animation: hero-live-pulse 1.6s ease-out infinite;
}
@keyframes hero-live-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); }
  70%  { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
  100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
}

.timer {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 36px;
  line-height: 1;
  letter-spacing: 0.02em;
  margin-top: 5px;
  color: var(--color-wc-text);
  text-shadow: 0 0 20px rgba(220,38,38,0.3);
}
@media (min-width: 1024px) { .timer { font-size: 42px; } }

.timer-sub {
  font-family: var(--font-sans);
  font-size: 13px;
  color: var(--color-wc-text-secondary);
  margin-top: 4px;
}

.ring-wrap {
  width: 72px;
  height: 72px;
  position: relative;
  flex-shrink: 0;
}
@media (min-width: 1024px) { .ring-wrap { width: 84px; height: 84px; } }
.ring-wrap svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg { stroke: rgba(255,255,255,0.08); }
.ring-fg {
  stroke: var(--color-wc-accent-glow, #EF4444);
  stroke-linecap: round;
  transition: stroke-dashoffset 0.5s var(--ease-out);
}
.ring-label {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  text-align: center;
}
.ring-label .pct {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 18px;
  line-height: 1;
}
.ring-label .frac {
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-secondary);
  margin-top: 2px;
}

.exercise-dots {
  margin-top: 16px;
  display: grid;
  gap: 4px;
  position: relative;
}
.dot {
  height: 4px;
  border-radius: 999px;
  background: rgba(255,255,255,0.10);
  transition: background 0.3s, box-shadow 0.3s;
}
.dot--done   { background: #10B981; }
.dot--active { background: var(--color-wc-accent, #DC2626); box-shadow: 0 0 8px rgba(239,68,68,0.6); }

/* Mobile small */
@media (max-width: 380px) {
  .hero { padding: 14px 16px; margin-top: 10px; }
  .timer { font-size: 38px; }
  .timer-sub { font-size: 12px; }
  .ring-wrap { width: 72px; height: 72px; }
  .ring-label .pct { font-size: 18px; }
  .ring-label .frac { font-size: 9px; }
}

@media (prefers-reduced-motion: reduce) {
  .live-dot { animation: none; }
  .ring-fg  { transition: none; }
}
</style>
