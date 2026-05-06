<script setup>
/**
 * WorkoutBottomBar.vue — Bottom bar fija con stats (Sesión + Volumen) + abandon + finish.
 *
 * Mobile: position fixed bottom + safe-area-inset-bottom.
 * Desktop: respeta sidebar (left: var(--sidebar-w)).
 *
 * Visual fidelity: replica el target HTML — solo 2 stats (Sesión + Volumen)
 * para mantener el layout limpio. Sets count se muestra en el WorkoutHero.
 */
import { computed } from 'vue';

const props = defineProps({
    elapsedDisplay: { type: String, default: '00:00' },
    totalVolume:    { type: Number, default: 0 },
    completedSets:  { type: Number, default: 0 },
    totalSets:      { type: Number, default: 0 },
    progressPct:    { type: Number, default: 0 },
    canFinish:      { type: Boolean, default: false },
    saving:         { type: Boolean, default: false },
    weightUnit:     { type: String, default: 'kg' },
});

defineEmits(['abandon', 'finish']);

const progressVar = computed(() => `${Math.min(100, Math.max(0, props.progressPct))}%`);
const volumeText = computed(() => {
    const v = props.totalVolume || 0;
    if (v === 0) return '0';
    if (v >= 1000) return `${(v / 1000).toFixed(1).replace('.', ',')}t`;
    return Math.round(v).toString();
});
</script>

<template>
  <div class="bottom-bar">
    <div class="bb-card">
      <button
        type="button"
        class="bb-quit"
        @click="$emit('abandon')"
        aria-label="Abandonar sesión"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
        </svg>
      </button>

      <div class="bb-cta" :style="{ '--p': progressVar }">
        <div class="l">
          <span class="lbl">Sesión</span>
          <span class="v wc-tabular">{{ elapsedDisplay }}</span>
        </div>
        <span class="sep" aria-hidden="true"></span>
        <div class="l">
          <span class="lbl">Volumen</span>
          <span class="v wc-tabular">{{ volumeText }} {{ weightUnit }}</span>
        </div>
        <div class="r">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="3" fill="currentColor"/>
          </svg>
          <span>En curso</span>
        </div>
      </div>

      <button
        type="button"
        class="bb-finish"
        :class="{ ready: canFinish && !saving }"
        :disabled="!canFinish || saving"
        @click="$emit('finish')"
        :aria-label="saving ? 'Guardando' : 'Completar sesión'"
      >
        <svg v-if="!saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12l5 5L20 7"/>
        </svg>
        <svg v-else class="spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/>
        </svg>
        <span class="label-text">{{ saving ? 'Guardando…' : 'Completar sesión' }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 14px 16px max(20px, env(safe-area-inset-bottom));
  background: linear-gradient(to top, rgba(9,9,11,0.95) 60%, rgba(9,9,11,0));
  backdrop-filter: blur(20px) saturate(1.3);
  -webkit-backdrop-filter: blur(20px) saturate(1.3);
  z-index: 50;
  pointer-events: none;
}
.bottom-bar > * { pointer-events: auto; }
@media (min-width: 1024px) {
  .bottom-bar { left: var(--sidebar-w, 0px); padding: 16px 32px 24px; }
}

.bb-card {
  background: rgba(30,30,34,0.72);
  border: 1px solid var(--color-wc-border-strong);
  border-radius: 20px;
  padding: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 12px 30px -10px rgba(0,0,0,0.6);
  max-width: 1136px;
  margin: 0 auto;
}

.bb-quit {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--color-wc-border);
  display: grid;
  place-items: center;
  color: var(--color-wc-text-secondary);
  flex-shrink: 0;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.bb-quit:hover { color: var(--color-wc-text); background: rgba(255,255,255,0.08); }
.bb-quit svg { width: 18px; height: 18px; }

.bb-cta {
  flex: 1;
  height: 56px;
  border-radius: 14px;
  background: var(--color-wc-bg-prominent, #1E1E22);
  border: 1px solid var(--color-wc-border);
  display: flex;
  align-items: center;
  padding: 0 14px;
  gap: 12px;
  position: relative;
  overflow: hidden;
  min-width: 0;
}
.bb-cta::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: var(--p, 0%);
  background: linear-gradient(90deg, rgba(220,38,38,0.18), rgba(220,38,38,0.04));
  transition: width 0.4s var(--ease-out);
}

.bb-cta .l {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.1;
  min-width: 0;
}
.bb-cta .l .lbl {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.bb-cta .l .v {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 16px;
  margin-top: 3px;
  color: var(--color-wc-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.bb-cta .sep {
  width: 1px;
  height: 28px;
  background: var(--color-wc-border);
  position: relative;
  flex-shrink: 0;
}
.bb-cta .r {
  margin-left: auto;
  position: relative;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--color-wc-accent-glow, #EF4444);
  flex-shrink: 0;
}
.bb-cta .r svg { width: 12px; height: 12px; }
.bb-cta .r span { display: none; }
@media (min-width: 480px) { .bb-cta .r span { display: inline; } }

.bb-finish {
  height: 56px;
  padding: 0 18px;
  border-radius: 14px;
  background: var(--color-wc-accent, #DC2626);
  color: white;
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 14px;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  border: none;
  box-shadow: 0 6px 20px -4px rgba(220,38,38,0.45);
  opacity: 0.55;
  pointer-events: none;
  cursor: pointer;
  min-width: 56px;
  justify-content: center;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}
.bb-finish.ready { opacity: 1; pointer-events: auto; }
.bb-finish:disabled { cursor: not-allowed; }
.bb-finish svg { width: 18px; height: 18px; }
.bb-finish .label-text { display: none; }
@media (min-width: 1024px) { .bb-finish .label-text { display: inline; } }

.spinner { animation: bb-spin 1s linear infinite; }
@keyframes bb-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* Mobile small */
@media (max-width: 380px) {
  .bottom-bar { padding: 10px 10px max(16px, env(safe-area-inset-bottom)); }
  .bb-card { padding: 10px; gap: 8px; }
  .bb-quit { width: 44px; height: 44px; }
  .bb-cta { padding: 0 10px; gap: 8px; height: 52px; }
  .bb-cta .l .v { font-size: 14px; }
  .bb-cta .l .lbl { font-size: 9px; letter-spacing: 0.14em; }
  .bb-cta .r { display: none; }
  .bb-cta .sep { height: 24px; }
  .bb-finish { height: 52px; padding: 0 14px; min-width: 52px; }
}

@media (prefers-reduced-motion: reduce) {
  .spinner { animation: none; }
  .bb-cta::before { transition: none; }
}
</style>
