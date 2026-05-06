<script setup>
/**
 * RestTimerCard.vue — Card de descanso entre sets (paleta azul/violeta).
 *
 * Modos:
 *   - default (desktop): card grande con ring 130px, preview siguiente set, controles 3 cols
 *   - compact (mobile floating): widget 72px alto, ring 48px + timer + pausar + saltar
 *
 * Visual fidelity: viewBox 100x100 r=44 (target HTML), gradient azul→violeta.
 */
import { computed } from 'vue';

const props = defineProps({
    secondsRemaining: { type: Number, default: 0 },
    totalSeconds:     { type: Number, default: 90 },
    nextExercise:     { type: String, default: '' },
    nextSetNumber:    { type: Number, default: 0 },
    nextSetTarget:    { type: String, default: '' },
    isPaused:         { type: Boolean, default: false },
    compact:          { type: Boolean, default: false },
});

defineEmits(['skip', 'pause', 'resume', 'add-15', 'subtract-15']);

const RING_RADIUS = 44;
const RING_CIRC = 2 * Math.PI * RING_RADIUS;

const minutes = computed(() => Math.floor(Math.max(0, props.secondsRemaining) / 60));
const seconds = computed(() => Math.max(0, props.secondsRemaining) % 60);

const display = computed(() => {
    const s = String(seconds.value).padStart(2, '0');
    if (minutes.value > 0) return `${minutes.value}:${s}`;
    return `0:${s}`;
});

const totalDisplay = computed(() => {
    const total = Math.max(0, props.totalSeconds);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
});

const progressPct = computed(() => {
    if (!props.totalSeconds || props.totalSeconds <= 0) return 0;
    const pct = ((props.totalSeconds - props.secondsRemaining) / props.totalSeconds) * 100;
    return Math.min(100, Math.max(0, pct));
});

const dashOffset = computed(() => RING_CIRC * (1 - progressPct.value / 100));
</script>

<template>
  <!-- ═══════════════ COMPACT MODE (mobile floating widget) ═══════════════ -->
  <section v-if="compact" class="rest-mini" role="status" aria-live="polite">
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
      <defs>
        <linearGradient id="restGradMini" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#60A5FA"/>
          <stop offset="100%" stop-color="#A78BFA"/>
        </linearGradient>
      </defs>
    </svg>

    <!-- Ring + countdown timer -->
    <div class="rest-mini-ring">
      <svg viewBox="0 0 100 100" class="rest-mini-svg">
        <circle class="rmini-bg" cx="50" cy="50" r="44" fill="none" stroke-width="8"/>
        <circle
          class="rmini-fg"
          cx="50" cy="50" r="44" fill="none" stroke-width="8"
          :stroke-dasharray="RING_CIRC"
          :stroke-dashoffset="dashOffset"
        />
      </svg>
      <span class="rest-mini-time wc-tabular">{{ display }}</span>
    </div>

    <!-- Info: descanso label + siguiente set -->
    <div class="rest-mini-info">
      <span class="rest-mini-label">
        <span class="rmini-dot" aria-hidden="true"></span>
        DESCANSO
      </span>
      <span v-if="nextExercise" class="rest-mini-next">
        Sigue: SET {{ nextSetNumber }} · {{ nextSetTarget || '—' }}
      </span>
    </div>

    <!-- Controls: -15 / pause / +15 -->
    <div class="rest-mini-controls">
      <button type="button" class="rmini-btn" @click="$emit('subtract-15')" aria-label="Restar 15 segundos">−15</button>
      <button
        type="button"
        class="rmini-btn rmini-btn--primary"
        @click="isPaused ? $emit('resume') : $emit('pause')"
        :aria-label="isPaused ? 'Reanudar' : 'Pausar'"
      >
        <svg v-if="isPaused" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
      </button>
      <button type="button" class="rmini-btn" @click="$emit('add-15')" aria-label="Sumar 15 segundos">+15</button>
    </div>

    <!-- Skip button (top-right corner) -->
    <button type="button" class="rest-mini-skip" @click="$emit('skip')" aria-label="Saltar descanso">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
      </svg>
    </button>
  </section>

  <!-- ═══════════════ DEFAULT MODE (desktop card) ═══════════════ -->
  <section v-else class="rest-card" role="status" aria-live="polite">
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
      <defs>
        <linearGradient id="restGradV2" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#60A5FA"/>
          <stop offset="100%" stop-color="#A78BFA"/>
        </linearGradient>
      </defs>
    </svg>

    <div class="rest-head">
      <div class="rest-tag">
        <span class="b-dot"></span>
        <span>Descanso · respira</span>
      </div>
      <button type="button" class="rest-skip" @click="$emit('skip')">
        <span>Saltar</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 4l10 8-10 8V4zM19 5v14"/>
        </svg>
      </button>
    </div>

    <div class="rest-body">
      <div class="rest-ring" aria-hidden="true">
        <svg viewBox="0 0 100 100">
          <circle class="rbg" cx="50" cy="50" r="44" fill="none" stroke-width="6"/>
          <circle
            class="rfg"
            cx="50" cy="50" r="44" fill="none" stroke-width="6"
            :stroke-dasharray="RING_CIRC"
            :stroke-dashoffset="dashOffset"
          />
        </svg>
        <div class="rest-ring-label">
          <div>
            <div class="big wc-tabular">{{ display }}</div>
            <div class="sm">de {{ totalDisplay }}</div>
          </div>
        </div>
      </div>

      <div class="rest-meta">
        <div class="next-label">Siguiente serie</div>
        <div class="next-name">
          {{ nextExercise || '—' }}
          <span v-if="nextSetNumber" class="next-set"> · SET {{ nextSetNumber }}</span>
        </div>
        <div v-if="nextSetTarget" class="next-target">{{ nextSetTarget }}</div>
      </div>
    </div>

    <div class="rest-controls">
      <button type="button" class="rest-btn" @click="$emit('subtract-15')" aria-label="Restar 15 segundos">−15 s</button>
      <button
        type="button"
        class="rest-btn primary"
        @click="isPaused ? $emit('resume') : $emit('pause')"
      >
        <svg v-if="isPaused" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
        {{ isPaused ? 'Reanudar' : 'Pausar' }}
      </button>
      <button type="button" class="rest-btn" @click="$emit('add-15')" aria-label="Sumar 15 segundos">+15 s</button>
    </div>
  </section>
</template>

<style scoped>
/* ═══════════════ COMPACT MODE — mobile floating widget ═══════════════ */
.rest-mini {
  display: grid;
  grid-template-columns: 56px 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 10px 12px 10px 10px;
  background:
    linear-gradient(135deg, rgba(59,130,246,0.18), rgba(167,139,250,0.10)),
    linear-gradient(180deg, #0d1421, #0a0f18);
  border: 1px solid rgba(59,130,246,0.30);
  border-radius: 16px;
  position: relative;
  box-shadow: 0 12px 32px -8px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04) inset;
  min-height: 72px;
}

.rest-mini-ring {
  position: relative;
  width: 56px;
  height: 56px;
  flex-shrink: 0;
}
.rest-mini-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}
.rmini-bg { stroke: rgba(255,255,255,0.08); }
.rmini-fg {
  stroke: url(#restGradMini);
  stroke-linecap: round;
  filter: drop-shadow(0 0 4px rgba(96,165,250,0.5));
  transition: stroke-dashoffset 1s linear;
}
.rest-mini-time {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 16px;
  color: var(--color-wc-text);
  line-height: 1;
}

.rest-mini-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.rest-mini-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  color: #60A5FA;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.rmini-dot {
  width: 6px; height: 6px;
  border-radius: 999px;
  background: #60A5FA;
  animation: rmini-pulse 2s ease-out infinite;
}
@keyframes rmini-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(96,165,250,0.6); }
  70%  { box-shadow: 0 0 0 6px rgba(96,165,250,0); }
  100% { box-shadow: 0 0 0 0 rgba(96,165,250,0); }
}
.rest-mini-next {
  font-size: 11px;
  color: var(--color-wc-text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-variant-numeric: tabular-nums;
}

.rest-mini-controls {
  display: flex;
  gap: 4px;
  align-items: center;
  flex-shrink: 0;
}
.rmini-btn {
  height: 36px;
  min-width: 36px;
  padding: 0 8px;
  border-radius: 10px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  color: var(--color-wc-text);
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  cursor: pointer;
  display: grid;
  place-items: center;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.rmini-btn:hover { background: rgba(255,255,255,0.08); }
.rmini-btn--primary {
  background: rgba(59,130,246,0.20);
  border-color: rgba(59,130,246,0.35);
  color: #93C5FD;
  width: 40px;
  padding: 0;
}
.rmini-btn--primary:hover { background: rgba(59,130,246,0.28); }
.rmini-btn svg { width: 14px; height: 14px; }

.rest-mini-skip {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border-radius: 999px;
  background: rgba(255,255,255,0.06);
  border: 0;
  color: var(--color-wc-text-tertiary);
  display: grid;
  place-items: center;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  z-index: 2;
  display: none;
}
@media (max-width: 380px) {
  /* En pantallas extra-pequeñas, ocultar info de "siguiente" para que quepa */
  .rest-mini { grid-template-columns: 48px 1fr auto; padding: 8px 10px; gap: 8px; }
  .rest-mini-ring { width: 48px; height: 48px; }
  .rest-mini-time { font-size: 14px; }
  .rest-mini-next { display: none; }
  .rmini-btn { height: 34px; min-width: 34px; font-size: 10px; padding: 0 6px; }
  .rmini-btn--primary { width: 36px; }
}

@media (prefers-reduced-motion: reduce) {
  .rmini-dot { animation: none; }
  .rmini-fg  { transition: none; }
}

/* ═══════════════ DEFAULT MODE — desktop card ═══════════════ */
.rest-card {
  background:
    radial-gradient(circle at 100% 0%, rgba(59,130,246,0.18), transparent 55%),
    radial-gradient(circle at 0% 100%, rgba(167,139,250,0.10), transparent 55%),
    linear-gradient(180deg, #0d1421, #0a0f18);
  border: 1px solid rgba(59,130,246,0.22);
  border-radius: 18px;
  padding: 18px 18px 14px;
  position: relative;
  overflow: hidden;
}
@media (min-width: 1024px) { .rest-card { padding: 20px 22px 16px; } }

.rest-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.rest-tag {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #60A5FA;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.rest-tag .b-dot {
  width: 8px; height: 8px;
  background: #60A5FA;
  border-radius: 999px;
  animation: rest-pulse 2s ease-out infinite;
}
@keyframes rest-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(96,165,250,0.6); }
  70%  { box-shadow: 0 0 0 8px rgba(96,165,250,0); }
  100% { box-shadow: 0 0 0 0 rgba(96,165,250,0); }
}

.rest-skip {
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  display: inline-flex;
  gap: 6px;
  align-items: center;
  background: transparent;
  border: 0;
  cursor: pointer;
  padding: 6px 8px;
  min-height: 32px;
  -webkit-tap-highlight-color: transparent;
}
.rest-skip svg { width: 14px; height: 14px; }
.rest-skip:hover { color: var(--color-wc-text); }

.rest-body {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 18px;
  align-items: center;
  position: relative;
}

.rest-ring {
  width: 100px;
  height: 100px;
  position: relative;
}
.rest-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.rest-ring .rbg { stroke: rgba(255,255,255,0.06); }
.rest-ring .rfg {
  stroke: url(#restGradV2);
  stroke-linecap: round;
  filter: drop-shadow(0 0 8px rgba(96,165,250,0.5));
  transition: stroke-dashoffset 1s linear;
}

.rest-ring-label {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  text-align: center;
}
.rest-ring-label .big {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 28px;
  line-height: 1;
  letter-spacing: 0.01em;
  color: var(--color-wc-text);
}
.rest-ring-label .sm {
  font-family: var(--font-mono);
  font-size: 9px;
  color: var(--color-wc-text-secondary);
  margin-top: 4px;
  letter-spacing: 0.1em;
}

.rest-meta { min-width: 0; }
.rest-meta .next-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.rest-meta .next-name {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 15px;
  line-height: 1.15;
  text-transform: uppercase;
  margin-top: 6px;
  text-wrap: balance;
  color: var(--color-wc-text);
  word-break: break-word;
}
.rest-meta .next-set {
  color: var(--color-wc-accent-glow, #EF4444);
}
.rest-meta .next-target {
  margin-top: 6px;
  font-size: 12px;
  color: var(--color-wc-text-secondary);
  font-variant-numeric: tabular-nums;
}

.rest-controls {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
  position: relative;
}
.rest-btn {
  height: 40px;
  border-radius: 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.rest-btn:hover { background: rgba(255,255,255,0.08); }
.rest-btn svg { width: 12px; height: 12px; }
.rest-btn.primary {
  background: rgba(59,130,246,0.14);
  border-color: rgba(59,130,246,0.28);
  color: #93C5FD;
}
.rest-btn.primary:hover { background: rgba(59,130,246,0.22); }

@media (prefers-reduced-motion: reduce) {
  .rest-tag .b-dot { animation: none; }
  .rest-ring .rfg  { transition: none; }
}
</style>
