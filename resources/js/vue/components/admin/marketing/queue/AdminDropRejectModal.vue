<script setup>
import { ref, computed, watch, nextTick } from 'vue';

const MIN_REASON_LENGTH = 10;

const props = defineProps({
    open: { type: Boolean, default: false },
    drop: { type: Object, default: null },
    submitting: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'confirm']);

const reason = ref('');
const reasonInputRef = ref(null);

watch(() => props.open, async (isOpen) => {
    if (isOpen) {
        reason.value = '';
        await nextTick();
        reasonInputRef.value?.focus();
    }
});

const trimmed = computed(() => reason.value.trim());
const isValid = computed(() => trimmed.value.length >= MIN_REASON_LENGTH);

function onBackdropClick() {
    if (!props.submitting) emit('close');
}

function onConfirm() {
    if (!isValid.value || props.submitting) return;
    emit('confirm', trimmed.value);
}

function onKey(e) {
    if (e.key === 'Escape' && !props.submitting) emit('close');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="open" class="modal-root" role="dialog" aria-modal="true" aria-labelledby="reject-title" @keydown="onKey">
                <div class="modal-backdrop" @click="onBackdropClick" aria-hidden="true"></div>

                <div class="modal-card" role="document">
                    <header class="modal-head">
                        <span class="modal-eyebrow">DECISION</span>
                        <h2 id="reject-title" class="modal-title">Solicitar regenerar</h2>
                        <p class="modal-sub" v-if="drop">
                            {{ drop.coach?.name ?? 'Coach #' + drop.coach?.id }} · {{ drop.iso_year }}-W{{ String(drop.iso_week).padStart(2, '0') }}
                        </p>
                    </header>

                    <p class="modal-tagline">
                        "Rechazar sin razon es ego. Rechazar con evidencia es coaching."
                    </p>

                    <label class="modal-field">
                        <span class="modal-label">
                            Razon detallada
                            <span class="modal-label-req" aria-hidden="true">*</span>
                        </span>
                        <textarea
                            ref="reasonInputRef"
                            v-model="reason"
                            class="modal-textarea"
                            rows="5"
                            placeholder="Que falta? Que pieza concreta hay que rehacer y por que? El coach lo va a leer."
                            :disabled="submitting"
                            required
                            :aria-invalid="!isValid && trimmed.length > 0"
                        ></textarea>
                        <span class="modal-counter" :class="{ 'modal-counter--warn': !isValid }">
                            {{ trimmed.length }} / minimo {{ MIN_REASON_LENGTH }}
                        </span>
                    </label>

                    <p v-if="error" class="modal-error" role="alert">{{ error }}</p>

                    <footer class="modal-foot">
                        <button
                            type="button"
                            class="modal-btn modal-btn--ghost"
                            @click="emit('close')"
                            :disabled="submitting"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="modal-btn modal-btn--reject"
                            @click="onConfirm"
                            :disabled="!isValid || submitting"
                        >
                            {{ submitting ? 'Enviando...' : 'Devolver al coach' }}
                        </button>
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-root {
    position: fixed;
    inset: 0;
    z-index: 90;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.66);
    backdrop-filter: blur(2px);
}
.modal-card {
    position: relative;
    width: 100%;
    max-width: 480px;
    border-radius: 16px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    padding: 22px 22px 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
}

.modal-head { display: flex; flex-direction: column; gap: 4px; }
.modal-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.modal-title {
    font-family: var(--font-display);
    font-size: 26px;
    letter-spacing: 0.04em;
    color: var(--color-wc-red-text, #F87171);
    margin: 0;
    line-height: 1;
}
.modal-sub {
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0;
}
.modal-tagline {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--color-wc-gold, #C8A769);
    margin: 0;
    text-wrap: balance;
}

.modal-field { display: flex; flex-direction: column; gap: 6px; }
.modal-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    display: inline-flex; gap: 4px;
}
.modal-label-req { color: var(--color-wc-red-text, #F87171); }
.modal-textarea {
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.30);
    border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text);
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    padding: 10px 12px;
    resize: vertical;
    min-height: 110px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.modal-textarea::placeholder { color: var(--color-wc-text-tertiary); }
.modal-textarea:focus { outline: none; border-color: var(--color-wc-accent, #DC2626); }
.modal-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-counter {
    align-self: flex-end;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
.modal-counter--warn { color: var(--color-wc-amber-text, #FCD34D); }

.modal-error {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-red-text, #F87171);
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: 8px;
    padding: 8px 10px;
    margin: 0;
}

.modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px; }
.modal-btn {
    height: 38px;
    padding: 0 16px;
    border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    border: 1px solid transparent;
}
.modal-btn--ghost {
    background: transparent;
    color: var(--color-wc-text-secondary);
    border-color: var(--color-wc-border);
}
.modal-btn--ghost:hover:not(:disabled) { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16)); color: var(--color-wc-text); }
.modal-btn--reject {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
}
.modal-btn--reject:hover:not(:disabled) { background: #B91C1C; }
.modal-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.modal-enter-active .modal-card, .modal-leave-active .modal-card { transition: transform 0.22s var(--ease-out, ease), opacity 0.18s var(--ease-out, ease); }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-card, .modal-leave-to .modal-card { transform: translateY(8px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-enter-active, .modal-leave-active,
    .modal-enter-active .modal-card, .modal-leave-active .modal-card { transition: none !important; }
}
</style>
