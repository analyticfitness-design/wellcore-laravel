<script setup>
import { ref } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
    open:   { type: Boolean, default: false },
    planId: { type: Number, default: null },
    planName: { type: String, default: '' },
});

const emit = defineEmits(['close', 'deleted']);

const api     = useApi();
const deleting = ref(false);

async function confirm() {
    if (!props.planId || deleting.value) return;
    deleting.value = true;
    try {
        await api.delete(`/api/v/admin/plans/${props.planId}`);
        emit('deleted');
        emit('close');
    } catch {
        // mantener modal abierto si falla
    } finally {
        deleting.value = false;
    }
}

function close() {
    if (deleting.value) return;
    emit('close');
}
</script>

<template>
  <Teleport to="body">
    <Transition name="del-fade">
      <div v-if="open" class="del-backdrop" role="alertdialog" aria-modal="true" aria-label="Confirmar eliminacion">
        <div class="del-overlay" @click="close"></div>
        <div class="del-panel">
          <!-- Icon -->
          <div class="del-icon-wrap" aria-hidden="true">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
          </div>

          <!-- Text -->
          <h3 class="del-title">ELIMINAR TEMPLATE</h3>
          <p class="del-msg">
            <span v-if="planName" class="del-plan-name">{{ planName }}</span>
            sera eliminado permanentemente. Esta accion no se puede deshacer.
          </p>

          <!-- Actions -->
          <div class="del-actions">
            <button type="button" class="btn-cancel" :disabled="deleting" @click="close">Cancelar</button>
            <button type="button" class="btn-delete" :disabled="deleting" @click="confirm">
              <svg v-if="deleting" class="btn-spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ deleting ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.del-backdrop {
    position: fixed; inset: 0; z-index: 210;
    display: flex; align-items: center; justify-content: center; padding: 16px;
}
.del-overlay {
    position: absolute; inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}
.del-panel {
    position: relative; z-index: 1;
    width: 100%; max-width: 380px;
    border-radius: 16px;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: var(--color-wc-bg-secondary, #111111);
    padding: 28px 24px;
    text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
}
.del-icon-wrap {
    width: 48px; height: 48px; border-radius: 50%;
    background: rgba(220, 38, 38, 0.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--color-wc-red-text, #F87171);
}
.del-title {
    font-family: var(--font-display);
    font-size: 20px; letter-spacing: 0.04em;
    color: var(--color-wc-text); margin: 0;
}
.del-msg {
    font-family: var(--font-sans);
    font-size: 13px; color: var(--color-wc-text-secondary);
    line-height: 1.55; margin: 0;
}
.del-plan-name {
    font-weight: 600; color: var(--color-wc-text);
    display: block; margin-bottom: 4px;
}
.del-actions {
    display: flex; gap: 10px; width: 100%; margin-top: 4px;
}
.btn-cancel {
    flex: 1; height: 40px; border-radius: 10px;
    border: 1px solid var(--color-wc-border); background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans); font-size: 13px; font-weight: 500; cursor: pointer;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.btn-cancel:hover:not(:disabled) { color: var(--color-wc-text); border-color: var(--color-wc-border-2); }
.btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-delete {
    flex: 1; height: 40px; border-radius: 10px; border: none;
    background: var(--color-wc-accent, #DC2626); color: #fff;
    font-family: var(--font-sans); font-size: 13px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background 0.15s ease;
}
.btn-delete:hover:not(:disabled) { background: #B91C1C; }
.btn-delete:disabled { opacity: 0.65; cursor: not-allowed; }

.btn-spinner { width: 14px; height: 14px; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.del-fade-enter-active, .del-fade-leave-active { transition: opacity 0.2s ease; }
.del-fade-enter-from, .del-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .del-fade-enter-active, .del-fade-leave-active { transition: none !important; }
    .btn-spinner { animation: none !important; }
}
</style>
