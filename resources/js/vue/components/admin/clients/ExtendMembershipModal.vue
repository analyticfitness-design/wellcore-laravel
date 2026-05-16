<script setup>
import { computed, ref, watch } from 'vue';
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'extended']);

const store = useAdminClientDetailStore();

const newDate = ref('');
const notes = ref('');
const error = ref(null);

const todayPlus1 = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
});

const maxDate = computed(() => {
    const d = new Date();
    d.setFullYear(d.getFullYear() + 2);
    return d.toISOString().split('T')[0];
});

const currentExpiry = computed(() => store.membership?.expires_at_formatted || 'sin fecha');
const isLocked = computed(() => store.membership?.is_locked === true);
const daysUntil = computed(() => store.membership?.days_until_expiry);

const newDateFormatted = computed(() => {
    if (!newDate.value) return null;
    try {
        const d = new Date(newDate.value + 'T00:00:00');
        return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
    } catch {
        return null;
    }
});

const canSubmit = computed(() => {
    return newDate.value && newDate.value >= todayPlus1.value && newDate.value <= maxDate.value;
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        newDate.value = '';
        notes.value = '';
        error.value = null;
    }
});

async function submit() {
    if (!canSubmit.value || store.savingExtension) return;
    error.value = null;
    const ok = await store.extendMembership({
        newExpiresAt: newDate.value,
        notes: notes.value.trim() || null,
    });
    if (ok) {
        emit('extended');
        emit('close');
    } else {
        error.value = store.actionMessage || 'No se pudo extender la membresía';
    }
}

function close() {
    if (store.savingExtension) return;
    emit('close');
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="open" class="modal-overlay" @click.self="close">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="ext-title">
          <header class="modal-head">
            <div>
              <span class="eyebrow">ACCIÓN OPERATIVA</span>
              <h2 id="ext-title" class="title">Extender membresía</h2>
            </div>
            <button type="button" class="close-btn" @click="close" :disabled="store.savingExtension" aria-label="Cerrar">
              ×
            </button>
          </header>

          <div class="status-grid">
            <div class="status-row">
              <span class="label">Fecha de corte actual</span>
              <span class="value">{{ currentExpiry }}</span>
            </div>
            <div class="status-row">
              <span class="label">Estado</span>
              <span class="value">
                <span v-if="isLocked" class="badge badge--danger">VENCIDO</span>
                <span v-else-if="daysUntil !== null && daysUntil >= 0" class="badge badge--ok">
                  {{ daysUntil }} día{{ daysUntil === 1 ? '' : 's' }} restantes
                </span>
                <span v-else class="badge badge--neutral">SIN PLAN MENSUAL</span>
              </span>
            </div>
          </div>

          <form @submit.prevent="submit" class="form">
            <div class="field">
              <label class="field-label" for="ext-date">Nueva fecha de corte</label>
              <input
                id="ext-date"
                v-model="newDate"
                type="date"
                :min="todayPlus1"
                :max="maxDate"
                required
                class="input"
                :disabled="store.savingExtension"
              />
              <p v-if="newDateFormatted" class="preview">
                → Membresía vigente hasta <strong>{{ newDateFormatted }}</strong>
              </p>
            </div>

            <div class="field">
              <label class="field-label" for="ext-notes">Notas (opcional)</label>
              <textarea
                id="ext-notes"
                v-model="notes"
                class="textarea"
                rows="3"
                maxlength="500"
                placeholder="Ej: Pago confirmado por transferencia Bancolombia, comprobante adjunto en chat"
                :disabled="store.savingExtension"
              ></textarea>
              <span class="char-count">{{ notes.length }}/500</span>
            </div>

            <p v-if="error" class="error-msg">{{ error }}</p>

            <footer class="actions">
              <button
                type="button"
                class="btn btn--secondary"
                @click="close"
                :disabled="store.savingExtension"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn btn--primary"
                :disabled="!canSubmit || store.savingExtension"
              >
                <span v-if="store.savingExtension">Extendiendo…</span>
                <span v-else>Confirmar extensión</span>
              </button>
            </footer>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.72);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
    backdrop-filter: blur(4px);
}

.modal-card {
    width: 100%;
    max-width: 480px;
    background: #0E0E0E;
    border: 1px solid rgba(220, 38, 38, 0.25);
    border-radius: var(--r-md, 16px);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
}

.modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: #DC2626;
    display: block;
    margin-bottom: 4px;
}
.title {
    font-family: var(--font-display);
    font-size: 22px;
    color: var(--c-text);
    margin: 0;
    line-height: 1.1;
}

.close-btn {
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-3);
    width: 28px;
    height: 28px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}
.close-btn:hover:not(:disabled) { color: var(--c-text); border-color: var(--c-text-3); }
.close-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.status-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: var(--r-sm, 8px);
    border: 1px solid var(--c-border);
}
.status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.value {
    font-family: var(--font-display);
    font-size: 12px;
    color: var(--c-text);
}

.badge {
    display: inline-block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
}
.badge--danger { background: rgba(220, 38, 38, 0.15); color: #F87171; }
.badge--ok { background: rgba(16, 185, 129, 0.12); color: #34D399; }
.badge--neutral { background: rgba(255, 255, 255, 0.04); color: var(--c-text-3); }

.form { display: flex; flex-direction: column; gap: 14px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.input, .textarea {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 8px);
    padding: 10px 12px;
    color: var(--c-text);
    font-family: inherit;
    font-size: 14px;
    width: 100%;
}
.input:focus, .textarea:focus {
    outline: none;
    border-color: #DC2626;
    background: rgba(255, 255, 255, 0.04);
}
.textarea { resize: vertical; min-height: 70px; }
.char-count {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
    align-self: flex-end;
}

.preview {
    margin: 0;
    font-size: 13px;
    color: var(--c-text);
    background: rgba(220, 38, 38, 0.08);
    border-left: 2px solid #DC2626;
    padding: 8px 12px;
    border-radius: var(--r-sm, 8px);
}
.preview strong { color: #F87171; font-weight: 600; }

.error-msg {
    margin: 0;
    color: #F87171;
    font-size: 13px;
    padding: 8px 12px;
    background: rgba(220, 38, 38, 0.08);
    border-radius: var(--r-sm, 8px);
}

.actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    padding-top: 4px;
}

.btn {
    padding: 10px 16px;
    border-radius: var(--r-sm, 8px);
    font-family: var(--font-display);
    font-size: 11px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    border: 1px solid transparent;
    cursor: pointer;
    transition: opacity 0.15s;
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn--primary { background: #DC2626; color: white; }
.btn--primary:hover:not(:disabled) { background: #B91C1C; }
.btn--secondary {
    background: transparent;
    color: var(--c-text-3);
    border-color: var(--c-border);
}
.btn--secondary:hover:not(:disabled) { color: var(--c-text); border-color: var(--c-text-3); }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.18s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
