<script setup>
const props = defineProps({
  dirty: { type: Boolean, required: true },
  dirtyCount: { type: Number, default: 0 },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['save', 'discard']);
</script>

<template>
  <Teleport to="body">
    <Transition name="save-bar">
      <div v-if="dirty" class="save-bar" role="status" aria-live="polite">
        <p class="save-bar-msg">
          <span class="save-bar-dot" aria-hidden="true"></span>
          Tienes {{ dirtyCount }} {{ dirtyCount === 1 ? 'cambio' : 'cambios' }} sin guardar
        </p>
        <div class="save-bar-actions">
          <button
            type="button"
            class="save-bar-discard"
            :disabled="saving"
            @click="emit('discard')"
          >
            DESCARTAR
          </button>
          <button
            type="button"
            class="save-bar-save"
            :disabled="saving"
            @click="emit('save')"
          >
            <span v-if="saving" class="save-bar-spinner" aria-hidden="true"></span>
            {{ saving ? 'GUARDANDO' : 'GUARDAR CAMBIOS' }} &nbsp;{{ saving ? '' : '→' }}
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.save-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 120;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 20px;
  background: var(--color-wc-bg-secondary);
  border-top: 1px solid var(--color-wc-border-2);
  backdrop-filter: blur(12px);
  flex-wrap: wrap;
}

@media (min-width: 1024px) {
  .save-bar {
    left: var(--admin-sidebar-w, 240px);
  }
}

.save-bar-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-wc-text-secondary);
  margin: 0;
}
.save-bar-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--color-wc-amber-text);
  flex-shrink: 0;
  animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.save-bar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.save-bar-discard {
  height: 34px;
  padding: 0 14px;
  border-radius: 8px;
  border: 1px solid var(--color-wc-border-2);
  background: transparent;
  color: var(--color-wc-text-secondary);
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  cursor: pointer;
  transition: color 0.12s, border-color 0.12s;
}
.save-bar-discard:hover:not(:disabled) {
  color: var(--color-wc-text);
  border-color: var(--color-wc-border-2);
}
.save-bar-discard:disabled { opacity: 0.4; cursor: not-allowed; }

.save-bar-save {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 34px;
  padding: 0 18px;
  border-radius: 8px;
  border: none;
  background: var(--color-wc-accent);
  color: #fff;
  font-family: var(--font-mono);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  cursor: pointer;
  transition: opacity 0.12s;
}
.save-bar-save:hover:not(:disabled) { opacity: 0.88; }
.save-bar-save:disabled { opacity: 0.5; cursor: not-allowed; }

.save-bar-spinner {
  width: 12px;
  height: 12px;
  border: 1.5px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Transicion entrada/salida */
.save-bar-enter-active,
.save-bar-leave-active { transition: transform 0.22s var(--ease-out), opacity 0.18s; }
.save-bar-enter-from,
.save-bar-leave-to { transform: translateY(100%); opacity: 0; }
</style>
