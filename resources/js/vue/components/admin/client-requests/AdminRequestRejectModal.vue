<script setup>
import { ref, watch } from 'vue';
import { useAdminClientRequestsStore } from '../../../stores/adminClientRequests';

const store = useAdminClientRequestsStore();

const notas   = ref('');
const error   = ref('');
const loading = ref(false);

watch(() => store.rejectOpen, (val) => {
    if (val) {
        notas.value   = '';
        error.value   = '';
        loading.value = false;
    }
});

async function submit() {
    if (notas.value.trim().length < 5) {
        error.value = 'La razón del rechazo debe tener al menos 5 caracteres.';
        return;
    }
    error.value   = '';
    loading.value = true;
    try {
        await store.doReject(store.rejectTarget.id, notas.value.trim());
    } catch (err) {
        if (err.response?.status === 422) {
            error.value = Object.values(err.response.data.errors || {}).flat()[0] || 'Datos inválidos.';
        } else {
            error.value = err.response?.data?.error || 'No se pudo rechazar la solicitud.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="store.rejectOpen"
        class="modal-overlay"
        role="dialog"
        aria-modal="true"
        aria-label="Rechazar solicitud"
        @click.self="store.closeReject()"
      >
        <Transition name="modal-scale">
          <div v-if="store.rejectOpen" class="modal-panel">
            <header class="modal-head">
              <h3 class="modal-title">RECHAZAR SOLICITUD</h3>
              <button
                type="button"
                class="modal-close"
                aria-label="Cerrar"
                @click="store.closeReject()"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </header>

            <div class="modal-body">
              <div v-if="store.rejectTarget" class="target-info">
                <div class="target-pair">
                  <span class="t-label">COACH</span>
                  <span class="t-val">{{ store.rejectTarget.coach_name || '—' }}</span>
                </div>
                <div class="target-pair">
                  <span class="t-label">CLIENTE</span>
                  <span class="t-val">{{ store.rejectTarget.client_name || '—' }}</span>
                </div>
              </div>

              <div class="field">
                <label for="reject-reason" class="field-label">
                  RAZÓN DEL RECHAZO <span class="required" aria-hidden="true">*</span>
                </label>
                <textarea
                  id="reject-reason"
                  v-model="notas"
                  rows="4"
                  class="field-textarea"
                  placeholder="Explica al coach el motivo del rechazo..."
                  :disabled="loading"
                ></textarea>
                <p class="field-hint">Esta razón será notificada al coach.</p>
              </div>

              <p v-if="error" role="alert" class="error-msg">{{ error }}</p>
            </div>

            <footer class="modal-footer">
              <button
                type="button"
                class="btn-cancel"
                @click="store.closeReject()"
                :disabled="loading"
              >Cancelar</button>
              <button
                type="button"
                class="btn-confirm"
                @click="submit"
                :disabled="loading"
                :aria-busy="loading"
              >{{ loading ? 'Rechazando…' : 'Confirmar rechazo' }}</button>
            </footer>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(8px);
}
.modal-panel {
    width: 100%;
    max-width: 440px;
    border-radius: 16px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-secondary, #111111);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--color-wc-border);
}
.modal-title {
    font-family: var(--font-display);
    font-size: 18px;
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    margin: 0;
}
.modal-close {
    width: 28px; height: 28px;
    border-radius: 6px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s var(--ease-out);
}
.modal-close:hover { color: var(--color-wc-text); }

.modal-body {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.target-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.target-pair {
    border-radius: 8px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255,255,255,0.02);
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.t-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.2em;
    color: var(--color-wc-text-tertiary);
}
.t-val {
    font-family: var(--font-sans, 'Inter', sans-serif);
    font-size: 12px;
    font-weight: 500;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.field-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    color: var(--color-wc-text-secondary);
    display: block;
}
.required { color: var(--color-wc-accent); }
.field-textarea {
    width: 100%;
    resize: vertical;
    min-height: 96px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: rgba(255,255,255,0.025);
    color: var(--color-wc-text);
    font-family: var(--font-sans, 'Inter', sans-serif);
    font-size: 13px;
    line-height: 1.55;
    padding: 10px 12px;
    transition: border-color 0.15s var(--ease-out);
    box-sizing: border-box;
}
.field-textarea::placeholder { color: var(--color-wc-text-tertiary); }
.field-textarea:focus { outline: none; border-color: var(--color-wc-accent); }
.field-textarea:disabled { opacity: 0.5; cursor: not-allowed; }
.field-hint {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}

.error-msg {
    font-family: var(--font-sans, 'Inter', sans-serif);
    font-size: 12px;
    color: var(--color-wc-red-text);
    border-radius: 8px;
    border: 1px solid rgba(220,38,38,0.2);
    background: var(--color-wc-red-soft);
    padding: 10px 12px;
    margin: 0;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 14px 20px 18px;
    border-top: 1px solid var(--color-wc-border);
}
.btn-cancel {
    flex: 1;
    border-radius: 10px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 12px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.btn-cancel:hover:not(:disabled) {
    color: var(--color-wc-text);
    border-color: var(--color-wc-border-2);
}
.btn-cancel:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-confirm {
    flex: 2;
    border-radius: 10px;
    background: var(--color-wc-red-soft);
    border: 1px solid rgba(220,38,38,0.3);
    color: var(--color-wc-red-text);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 600;
    padding: 12px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}
.btn-confirm:hover:not(:disabled) { background: rgba(220,38,38,0.18); }
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* Approve confirm inline dentro del drawer — se reutiliza este modal
   para rechazar. El approve tiene su propio overlay en la página. */

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.18s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.modal-scale-enter-active,
.modal-scale-leave-active { transition: transform 0.22s var(--ease-out, ease), opacity 0.18s ease; }
.modal-scale-enter-from,
.modal-scale-leave-to { transform: scale(0.96) translateY(8px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-fade-enter-active, .modal-fade-leave-active,
    .modal-scale-enter-active, .modal-scale-leave-active { transition: none !important; }
    .field-textarea, .modal-close, .btn-cancel, .btn-confirm { transition: none !important; }
}
</style>
