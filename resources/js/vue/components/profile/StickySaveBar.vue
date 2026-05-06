<script setup>
/**
 * StickySaveBar.vue — barra fija inferior visible cuando el form tiene cambios
 * sin guardar (isDirty=true).
 *
 * Visual: blur backdrop, dot pulsante ámbar a la izquierda, mensaje "Tienes N
 * cambios sin guardar", botones Descartar (ghost) y Guardar (primary).
 *
 * Animación: transform translateY(100%) → 0 con cubic-bezier suave (0.28s).
 *
 * Padding-left = sidebar-w en desktop ≥1024px (heredando var --sidebar-w del shell)
 * Touch targets ≥ 44px en ambos botones.
 *
 * Emits:
 *   - discard
 *   - save
 *
 * Props:
 *   - visible: boolean (típicamente isDirty del composable)
 *   - dirtyCount: number (cuántos campos cambiaron)
 *   - saving: boolean (disable botones + spinner en Guardar)
 */
import { computed } from 'vue';
import { useReducedMotion } from '../../composables/useReducedMotion';

const props = defineProps({
    visible:    { type: Boolean, default: false },
    dirtyCount: { type: Number, default: 0 },
    saving:     { type: Boolean, default: false },
    saveLabel:    { type: String, default: 'Guardar cambios' },
    discardLabel: { type: String, default: 'Descartar' },
});

const emit = defineEmits(['save', 'discard']);

const reduced = useReducedMotion();

const message = computed(() => {
    const n = props.dirtyCount;
    if (n <= 0) return 'Hay cambios sin guardar';
    if (n === 1) return '1 cambio sin guardar';
    return `${n} cambios sin guardar`;
});

function onSave() {
    if (props.saving) return;
    emit('save');
}

function onDiscard() {
    if (props.saving) return;
    emit('discard');
}
</script>

<template>
  <div
    class="savebar"
    :class="{ 'is-visible': visible, 'is-reduced': reduced }"
    role="region"
    aria-label="Cambios sin guardar"
    :aria-hidden="!visible"
  >
    <div class="savebar__msg">
      <span class="savebar__dot" aria-hidden="true"></span>
      <strong>{{ message }}</strong>
    </div>

    <div class="savebar__actions">
      <button
        type="button"
        class="btn btn--ghost"
        :disabled="saving"
        @click="onDiscard"
      >{{ discardLabel }}</button>

      <button
        type="button"
        class="btn btn--primary"
        :disabled="saving"
        @click="onSave"
      >
        <svg
          v-if="saving"
          class="btn__spin"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32 32"/>
        </svg>
        <span>{{ saving ? 'Guardando…' : saveLabel }}</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.savebar {
  position: fixed;
  left: var(--sidebar-w, 0);
  right: 0;
  bottom: 0;
  z-index: 40;
  padding: 12px 24px;
  border-top: 1px solid var(--color-wc-border);
  background: rgba(9, 9, 11, 0.92);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  transform: translateY(100%);
  transition: transform 0.28s cubic-bezier(0.2, 0.7, 0.2, 1);
  pointer-events: none;
}
.savebar.is-visible {
  transform: translateY(0);
  pointer-events: auto;
}
.savebar.is-reduced { transition-duration: 0.01ms; }

/* Light mode: usa surface clara con blur */
:global(html:not(.dark)) .savebar {
  background: rgba(255, 255, 255, 0.92);
  border-top-color: var(--color-wc-border);
}

@media (max-width: 1023px) {
  .savebar {
    left: 0;
    flex-direction: column;
    align-items: stretch;
    padding: 12px 16px calc(env(safe-area-inset-bottom, 0px) + 12px);
  }
  .savebar__actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .savebar__msg { justify-content: flex-start; }
}

.savebar__msg {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: var(--color-wc-text-secondary);
  min-width: 0;
}
.savebar__msg strong {
  color: var(--color-wc-text);
  font-weight: 600;
}

.savebar__dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #F59E0B;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.16);
  flex-shrink: 0;
  animation: dot-pulse 1.6s ease-in-out infinite;
}
@keyframes dot-pulse {
  0%, 100% { box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.16); }
  50%      { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.28); }
}
@media (prefers-reduced-motion: reduce) {
  .savebar__dot { animation: none; }
  .savebar { transition-duration: 0.01ms; }
}

.savebar__actions {
  display: flex;
  gap: 8px;
}

.btn {
  height: 44px;
  min-height: 44px;
  padding: 0 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  font-family: inherit;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  letter-spacing: 0.01em;
  cursor: pointer;
  border: 1px solid transparent;
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn:focus-visible {
  outline: 2px solid var(--color-wc-accent-glow, #EF4444);
  outline-offset: 2px;
}

.btn--ghost {
  border-color: var(--color-wc-border);
  background: transparent;
  color: var(--color-wc-text-secondary);
}
.btn--ghost:hover:not(:disabled) {
  background: var(--color-wc-bg-tertiary);
  color: var(--color-wc-text);
  border-color: var(--color-wc-border-strong, var(--color-wc-border));
}

.btn--primary {
  background: var(--color-wc-accent, #DC2626);
  color: #fff;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.10) inset,
              0 8px 20px -8px rgba(220, 38, 38, 0.55);
}
.btn--primary:hover:not(:disabled) {
  background: var(--color-wc-accent-hover, #B91C1C);
}
.btn--primary:active:not(:disabled) { transform: translateY(1px); }

.btn__spin {
  width: 16px;
  height: 16px;
  animation: btn-spin 0.9s linear infinite;
}
@keyframes btn-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) {
  .btn__spin { animation-duration: 0.01ms; }
  .btn { transition-duration: 0.01ms; }
}
</style>
