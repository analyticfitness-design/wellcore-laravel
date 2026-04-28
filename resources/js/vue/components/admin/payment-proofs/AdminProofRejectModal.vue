<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { formatCOP } from '../../../composables/useFormat';

const props = defineProps({
    open: { type: Boolean, default: false },
    proof: { type: Object, default: null },
    submitting: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'confirm']);

const note = ref('');
const error = ref('');
const textareaRef = ref(null);

const TEMPLATES = [
    'Monto del comprobante no coincide con el plan seleccionado.',
    'La foto no es legible o está incompleta. Por favor envíala de nuevo.',
    'El nombre del titular no coincide con el cliente registrado.',
    'No se identifica la fecha o el número de transacción en el comprobante.',
];

const charCount = computed(() => note.value.trim().length);
const canSubmit = computed(() => charCount.value >= 10 && !props.submitting);

function applyTemplate(t) {
    note.value = t;
    error.value = '';
    nextTick(() => textareaRef.value?.focus());
}

function handleSubmit() {
    error.value = '';
    if (charCount.value < 10) {
        error.value = 'La razón debe tener al menos 10 caracteres. El cliente la verá tal cual.';
        return;
    }
    emit('confirm', note.value.trim());
}

function close() {
    if (props.submitting) return;
    emit('close');
}

function onKeydown(e) {
    if (!props.open) return;
    if (e.key === 'Escape') close();
}

watch(() => props.open, async (open) => {
    if (open) {
        note.value = '';
        error.value = '';
        document.addEventListener('keydown', onKeydown);
        await nextTick();
        textareaRef.value?.focus();
    } else {
        document.removeEventListener('keydown', onKeydown);
    }
});
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="reject-backdrop" @click.self="close" role="dialog" aria-modal="true" aria-labelledby="reject-title">
        <Transition name="rise" appear>
          <div v-if="open" class="reject-panel">
            <header class="reject-header">
              <div>
                <p class="reject-eyebrow">RAZÓN DEL RECHAZO</p>
                <h2 id="reject-title" class="reject-title">RECHAZAR COMPROBANTE</h2>
                <p class="reject-editorial">"El cliente recibirá esta razón. Sé claro y directo — sin emoción."</p>
              </div>
              <button class="reject-close" @click="close" :disabled="submitting" aria-label="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
              </button>
            </header>

            <div v-if="proof" class="reject-summary">
              <div class="reject-summary-item">
                <span class="reject-summary-label">CLIENTE</span>
                <span class="reject-summary-value">{{ proof.clientName || proof.clientEmail }}</span>
              </div>
              <div class="reject-summary-item">
                <span class="reject-summary-label">MONTO</span>
                <span class="reject-summary-value">{{ formatCOP(proof.amount) }}</span>
              </div>
              <div class="reject-summary-item">
                <span class="reject-summary-label">PLAN</span>
                <span class="reject-summary-value">{{ proof.plan }}</span>
              </div>
            </div>

            <div class="reject-templates">
              <p class="reject-templates-label">PLANTILLAS RÁPIDAS</p>
              <div class="reject-templates-list">
                <button
                  v-for="(t, i) in TEMPLATES"
                  :key="i"
                  type="button"
                  class="reject-template"
                  @click="applyTemplate(t)"
                  :disabled="submitting"
                >{{ t }}</button>
              </div>
            </div>

            <label class="reject-label" for="reject-textarea">RAZÓN DETALLADA</label>
            <textarea
              id="reject-textarea"
              ref="textareaRef"
              v-model="note"
              rows="4"
              class="reject-textarea"
              :class="{ 'reject-textarea--error': error }"
              placeholder="Explica con precisión por qué se rechaza este comprobante. El cliente lo leerá literal."
              :disabled="submitting"
              maxlength="500"
            ></textarea>

            <div class="reject-meta-row">
              <span v-if="error" class="reject-error">{{ error }}</span>
              <span v-else class="reject-counter" :class="{ 'reject-counter--ok': charCount >= 10 }">
                {{ charCount }}/10 caracteres mínimo · máx. 500
              </span>
            </div>

            <footer class="reject-footer">
              <button type="button" class="reject-btn reject-btn--secondary" @click="close" :disabled="submitting">
                Cancelar
              </button>
              <button type="button" class="reject-btn reject-btn--primary" @click="handleSubmit" :disabled="!canSubmit">
                <svg v-if="submitting" class="reject-spinner" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity="0.25"/>
                  <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"/>
                </svg>
                {{ submitting ? 'Procesando...' : 'Confirmar rechazo' }}
              </button>
            </footer>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.reject-backdrop {
    position: fixed;
    inset: 0;
    z-index: 90;
    background: rgba(0, 0, 0, 0.78);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.reject-panel {
    width: min(92vw, 520px);
    max-height: 90vh;
    overflow-y: auto;
    background: var(--color-wc-bg-secondary, #111111);
    border: 1px solid var(--color-wc-border);
    border-radius: 16px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
}

.reject-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.reject-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0 0 4px;
}
.reject-title {
    font-family: var(--font-display);
    font-size: 22px;
    letter-spacing: 0.06em;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1;
}
.reject-editorial {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    color: var(--color-wc-gold, #C8A769);
    font-size: 12px;
    margin: 6px 0 0;
    line-height: 1.45;
}
.reject-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.reject-close:hover { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12)); color: var(--color-wc-text); }
.reject-close svg { width: 14px; height: 14px; }

.reject-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(220, 38, 38, 0.06);
    border: 1px solid rgba(220, 38, 38, 0.18);
}
.reject-summary-item { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.reject-summary-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.reject-summary-value {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-transform: capitalize;
}

.reject-templates { display: flex; flex-direction: column; gap: 6px; }
.reject-templates-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}
.reject-templates-list { display: flex; flex-direction: column; gap: 6px; }
.reject-template {
    text-align: left;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.4;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.reject-template:hover:not(:disabled) {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    background: rgba(255, 255, 255, 0.04);
}
.reject-template:disabled { opacity: 0.5; cursor: not-allowed; }

.reject-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.reject-textarea {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.5;
    resize: vertical;
    min-height: 100px;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.reject-textarea:focus {
    outline: none;
    border-color: var(--color-wc-accent, #DC2626);
    background: rgba(255, 255, 255, 0.05);
}
.reject-textarea--error { border-color: var(--color-wc-accent, #DC2626); background: rgba(220, 38, 38, 0.06); }
.reject-textarea:disabled { opacity: 0.5; cursor: not-allowed; }

.reject-meta-row {
    display: flex;
    justify-content: flex-end;
}
.reject-error {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--color-wc-red-text, #F87171);
}
.reject-counter {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
.reject-counter--ok { color: var(--color-wc-green-text, #34D399); }

.reject-footer {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}
.reject-btn {
    flex: 1;
    padding: 11px 18px;
    border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.reject-btn--secondary {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
}
.reject-btn--secondary:hover:not(:disabled) { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12)); }
.reject-btn--primary {
    background: var(--color-wc-accent, #DC2626);
    border: 1px solid var(--color-wc-accent, #DC2626);
    color: #fff;
}
.reject-btn--primary:hover:not(:disabled) { background: #B91C1C; }
.reject-btn:disabled { opacity: 0.45; cursor: not-allowed; }

.reject-spinner { width: 14px; height: 14px; animation: rspin 0.8s linear infinite; color: currentColor; }
@keyframes rspin { to { transform: rotate(360deg); } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.rise-enter-active, .rise-leave-active { transition: transform 0.22s var(--ease-out, ease), opacity 0.22s var(--ease-out, ease); }
.rise-enter-from, .rise-leave-to { transform: translateY(20px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .reject-spinner { animation: none !important; }
    .fade-enter-active, .fade-leave-active,
    .rise-enter-active, .rise-leave-active { transition: none !important; }
}
</style>
