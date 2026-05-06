<script setup>
/**
 * VoiceCTA.vue — Botón "Dictar serie con voz" + estado de listening / confirmación / error.
 *
 * Estados:
 * - default: borde dashed rojo, copy "Dictar serie con voz · Manos sudadas · ojos en la barra"
 * - listening: solid border + bg rojo + animación de barras
 * - confirmation: card emerald con peso/reps + botones Confirmar/Editar
 * - error: card naranja con mensaje
 */
import { computed } from 'vue';

const props = defineProps({
  listening:    { type: Boolean, default: false },
  isProcessing: { type: Boolean, default: false },
  confirmation: { type: Object, default: null },   // { weight, reps, setIndex } | null
  error:        { type: String, default: '' },
  weightUnit:   { type: String, default: 'kg' },
});

const emit = defineEmits(['start', 'stop', 'confirm', 'cancel']);

const showConfirmation = computed(() => !!props.confirmation && !props.listening);

function onMainClick() {
  if (props.listening) emit('stop');
  else emit('start');
}
</script>

<template>
  <div class="voice-wrap">
    <!-- Confirmation state -->
    <div v-if="showConfirmation" class="voice-confirm">
      <div class="vc-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="vc-text">
        <span class="vc-label">Detectado</span>
        <span class="vc-value">
          <strong>{{ confirmation?.weight }}{{ weightUnit }}</strong>
          <span> × </span>
          <strong>{{ confirmation?.reps }} reps</strong>
        </span>
      </div>
      <div class="vc-actions">
        <button type="button" class="vc-btn vc-btn--ghost" @click="emit('cancel')">Editar</button>
        <button type="button" class="vc-btn vc-btn--primary" @click="emit('confirm')">Confirmar</button>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="voice-error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12" y2="16"/>
      </svg>
      <span>{{ error }}</span>
      <button type="button" class="vc-btn vc-btn--ghost vc-btn--sm" @click="emit('start')">Reintentar</button>
    </div>

    <!-- Default / listening -->
    <button
      v-else
      type="button"
      class="voice-action"
      :class="{ 'voice-action--listening': listening, 'voice-action--processing': isProcessing }"
      @click="onMainClick"
    >
      <span class="v-ico">
        <svg v-if="!listening" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v2a7 7 0 0 0 14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>
        </svg>
        <span v-else class="v-bars" aria-hidden="true">
          <span></span><span></span><span></span><span></span>
        </span>
      </span>
      <span class="v-text">
        <span>{{ listening ? 'Escuchando…' : 'Dictar serie con voz' }}</span>
        <small>{{ listening ? 'Decí: "12 reps con 50 kilos"' : 'Manos sudadas · ojos en la barra' }}</small>
      </span>
      <span class="v-arrow" v-if="!listening">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </span>
    </button>
  </div>
</template>

<style scoped>
.voice-wrap { display: contents; }

.voice-action {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  background: rgba(167,139,250,0.06);
  border: 1px solid rgba(167,139,250,0.18);
  border-radius: 16px;
  font-size: 14px;
  font-weight: 500;
  color: var(--color-wc-text);
  width: 100%;
  text-align: left;
  cursor: pointer;
  min-height: 64px;
  transition: all 0.15s var(--ease-out);
}
.voice-action:hover { background: rgba(167,139,250,0.10); }

.voice-action--listening {
  background: rgba(220,38,38,0.10);
  border-color: rgba(220,38,38,0.30);
  box-shadow: 0 0 0 4px rgba(220,38,38,0.05);
}
.voice-action--listening .v-ico { background: var(--color-wc-accent, #DC2626); color: white; }

.voice-action--processing { opacity: 0.7; pointer-events: none; }

.v-ico {
  width: 40px; height: 40px;
  border-radius: 999px;
  background: var(--color-wc-purple, #A78BFA);
  color: #1a1230;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.v-ico svg { width: 18px; height: 18px; }

.v-bars {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  height: 18px;
}
.v-bars span {
  display: inline-block;
  width: 3px;
  height: 100%;
  background: currentColor;
  border-radius: 2px;
  animation: voice-bar 0.9s ease-in-out infinite;
}
.v-bars span:nth-child(2) { animation-delay: 0.15s; }
.v-bars span:nth-child(3) { animation-delay: 0.30s; }
.v-bars span:nth-child(4) { animation-delay: 0.45s; }
@keyframes voice-bar {
  0%, 100% { transform: scaleY(0.4); }
  50%      { transform: scaleY(1); }
}

.v-text { display: flex; flex-direction: column; line-height: 1.25; flex: 1; min-width: 0; }
.v-text small {
  font-size: 11px;
  font-weight: 400;
  color: var(--color-wc-text-secondary);
  margin-top: 2px;
}

.v-arrow {
  margin-left: auto;
  color: var(--color-wc-text-tertiary);
}
.v-arrow svg { width: 16px; height: 16px; }

/* Confirmation card */
.voice-confirm {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: rgba(16,185,129,0.10);
  border: 1px solid rgba(16,185,129,0.28);
  border-radius: 16px;
}
.vc-icon {
  width: 40px; height: 40px;
  border-radius: 999px;
  background: rgba(16,185,129,0.20);
  color: #10B981;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.vc-icon svg { width: 20px; height: 20px; }
.vc-text { display: flex; flex-direction: column; line-height: 1.25; flex: 1; min-width: 0; }
.vc-label {
  font-family: var(--font-display);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.vc-value {
  font-family: var(--font-display);
  font-size: 16px;
  font-weight: 600;
  color: var(--color-wc-text);
  margin-top: 2px;
  font-variant-numeric: tabular-nums;
}
.vc-actions { display: flex; gap: 6px; }

.vc-btn {
  height: 36px;
  padding: 0 14px;
  border-radius: 10px;
  font-family: var(--font-display);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  cursor: pointer;
  border: 1px solid transparent;
}
.vc-btn--ghost {
  background: transparent;
  border-color: var(--color-wc-border);
  color: var(--color-wc-text-secondary);
}
.vc-btn--primary {
  background: #10B981;
  color: #042f24;
}
.vc-btn--sm { height: 32px; padding: 0 10px; font-size: 11px; }

/* Error */
.voice-error {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: rgba(245,158,11,0.10);
  border: 1px solid rgba(245,158,11,0.30);
  border-radius: 16px;
  color: #F59E0B;
  font-size: 13px;
}
.voice-error svg { width: 18px; height: 18px; flex-shrink: 0; }
.voice-error span { flex: 1; min-width: 0; color: var(--color-wc-text); }

@media (prefers-reduced-motion: reduce) {
  .v-bars span { animation: none; transform: scaleY(0.6); }
}
</style>
