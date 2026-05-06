<script setup>
/**
 * RestTimerCard.vue — Card de descanso entre sets (paleta azul/violeta).
 *
 * Reemplaza el overlay flotante legacy por una card inline. Display de countdown,
 * preview del próximo set y controles -15s / pausar / +15s.
 */
import { computed } from 'vue';

const props = defineProps({
  secondsRemaining: { type: Number, default: 0 },
  totalSeconds:     { type: Number, default: 90 },
  nextExercise:     { type: String, default: '' },
  nextSetNumber:    { type: Number, default: 0 },
  nextSetTarget:    { type: String, default: '' },
  isPaused:         { type: Boolean, default: false },
});

defineEmits(['skip', 'pause', 'resume', 'add-15', 'subtract-15']);

const minutes = computed(() => Math.floor(Math.max(0, props.secondsRemaining) / 60));
const seconds = computed(() => Math.max(0, props.secondsRemaining) % 60);

const display = computed(() =>
  `${String(minutes.value).padStart(2, '0')}:${String(seconds.value).padStart(2, '0')}`
);

const progressPct = computed(() => {
  if (!props.totalSeconds || props.totalSeconds <= 0) return 0;
  const pct = ((props.totalSeconds - props.secondsRemaining) / props.totalSeconds) * 100;
  return Math.min(100, Math.max(0, pct));
});

const ringCirc = 2 * Math.PI * 60;
const dashOffset = computed(() => ringCirc * (1 - progressPct.value / 100));
</script>

<template>
  <section class="rest-card" role="status" aria-live="polite">
    <div class="rest-head">
      <div class="rest-tag">
        <span class="b-dot"></span>
        <span>Descanso</span>
      </div>
      <button type="button" class="rest-skip" @click="$emit('skip')">
        <span>Saltar</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/>
        </svg>
      </button>
    </div>

    <div class="rest-body">
      <div class="rest-ring" aria-hidden="true">
        <svg viewBox="0 0 130 130">
          <defs>
            <linearGradient id="restGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#60A5FA"/>
              <stop offset="100%" stop-color="#A78BFA"/>
            </linearGradient>
          </defs>
          <circle cx="65" cy="65" r="60" fill="none" stroke-width="6" class="rbg"/>
          <circle
            cx="65" cy="65" r="60" fill="none" stroke-width="6"
            class="rfg"
            :stroke-dasharray="ringCirc"
            :stroke-dashoffset="dashOffset"
          />
        </svg>
        <div class="rest-ring-label">
          <span class="big wc-tabular">{{ display }}</span>
          <span class="sm">RESTANTE</span>
        </div>
      </div>

      <div class="rest-meta">
        <div class="next-label">Próximo</div>
        <div class="next-name">{{ nextExercise || '—' }}</div>
        <div v-if="nextSetTarget" class="next-target">
          <span v-if="nextSetNumber">SET {{ nextSetNumber }} · </span>{{ nextSetTarget }}
        </div>
      </div>
    </div>

    <div class="rest-controls">
      <button type="button" class="rest-btn" @click="$emit('subtract-15')">−15s</button>
      <button
        type="button"
        class="rest-btn primary"
        @click="isPaused ? $emit('resume') : $emit('pause')"
      >
        <svg v-if="isPaused" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 4 20 12 6 20"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        {{ isPaused ? 'Reanudar' : 'Pausar' }}
      </button>
      <button type="button" class="rest-btn" @click="$emit('add-15')">+15s</button>
    </div>
  </section>
</template>

<style scoped>
.rest-card {
  background:
    radial-gradient(circle at 100% 0%, rgba(59,130,246,0.18), transparent 55%),
    radial-gradient(circle at 0% 100%, rgba(167,139,250,0.10), transparent 55%),
    linear-gradient(180deg, #0d1421, #0a0f18);
  border: 1px solid rgba(59,130,246,0.22);
  border-radius: 20px;
  padding: 22px 20px 18px;
  position: relative;
  overflow: hidden;
}
@media (min-width: 1024px) { .rest-card { padding: 26px 28px 22px; } }

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
  padding: 8px;
  min-height: 36px;
}
.rest-skip svg { width: 14px; height: 14px; }
.rest-skip:hover { color: var(--color-wc-text); }

.rest-body {
  margin-top: 18px;
  display: grid;
  grid-template-columns: 130px 1fr;
  gap: 22px;
  align-items: center;
  position: relative;
}

.rest-ring { width: 130px; height: 130px; position: relative; }
.rest-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.rest-ring .rbg { stroke: rgba(255,255,255,0.06); }
.rest-ring .rfg {
  stroke: url(#restGrad);
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
  font-size: 42px;
  line-height: 1;
  letter-spacing: 0.01em;
  color: var(--color-wc-text);
}
.rest-ring-label .sm {
  font-family: var(--font-mono);
  font-size: 10px;
  color: var(--color-wc-text-secondary);
  margin-top: 4px;
  letter-spacing: 0.1em;
}

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
  font-size: 18px;
  line-height: 1.15;
  text-transform: uppercase;
  margin-top: 6px;
  text-wrap: balance;
  color: var(--color-wc-text);
}
.rest-meta .next-target {
  margin-top: 8px;
  font-size: 13px;
  color: var(--color-wc-text-secondary);
  font-variant-numeric: tabular-nums;
}

.rest-controls {
  margin-top: 16px;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
  position: relative;
}
.rest-btn {
  height: 44px;
  border-radius: 14px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-wc-text);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
}
.rest-btn:hover { background: rgba(255,255,255,0.08); }
.rest-btn svg { width: 14px; height: 14px; }
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
