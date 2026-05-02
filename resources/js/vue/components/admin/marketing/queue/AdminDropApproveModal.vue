<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    drop: { type: Object, default: null },
    submitting: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'confirm']);

const notes = ref('');
const notesInputRef = ref(null);

watch(() => props.open, async (isOpen) => {
    if (isOpen) {
        notes.value = '';
        await nextTick();
        notesInputRef.value?.focus();
    }
});

function onBackdropClick() {
    if (!props.submitting) emit('close');
}

function onConfirm() {
    emit('confirm', notes.value.trim() || null);
}

function onKey(e) {
    if (e.key === 'Escape' && !props.submitting) emit('close');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="open" class="modal-root" role="dialog" aria-modal="true" aria-labelledby="approve-title" @keydown="onKey">
                <div class="modal-backdrop" @click="onBackdropClick" aria-hidden="true"></div>

                <div class="modal-card" role="document">
                    <header class="modal-head">
                        <span class="modal-eyebrow">DECISION</span>
                        <h2 id="approve-title" class="modal-title">Aprobar drop</h2>
                        <p class="modal-sub" v-if="drop">
                            {{ drop.coach?.name ?? 'Coach #' + drop.coach?.id }} · {{ drop.iso_year }}-W{{ String(drop.iso_week).padStart(2, '0') }}
                        </p>
                    </header>

                    <p class="modal-tagline">
                        "Aprobar es un compromiso con la calidad del mensaje, no con el calendario."
                    </p>

                    <label class="modal-field">
                        <span class="modal-label">Notas para el coach (opcional)</span>
                        <textarea
                            ref="notesInputRef"
                            v-model="notes"
                            class="modal-textarea"
                            rows="4"
                            placeholder="Que valor agrega esta pieza al cliente? Que hubo que ajustar?"
                            :disabled="submitting"
                        ></textarea>
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
                            class="modal-btn modal-btn--primary"
                            @click="onConfirm"
                            :disabled="submitting"
                        >
                            {{ submitting ? 'Aprobando...' : 'Aprobar drop' }}
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
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    padding: 22px 22px 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
}

.modal-head { display: flex; flex-direction: column; gap: 4px; }
.modal-eyebrow {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase;
    color: var(--c-text-3);
}
.modal-title {
    font-family: var(--font-display);
    font-size: 26px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    margin: 0;
    line-height: 1;
}
.modal-sub {
    font-family: var(--font-display);
    font-size: 10px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 0;
}
.modal-tagline {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12.5px;
    line-height: 1.55;
    color: #C8A769;
    margin: 0;
    text-wrap: balance;
}

.modal-field { display: flex; flex-direction: column; gap: 6px; }
.modal-label {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.modal-textarea {
    border-radius: var(--r-sm, 12px);
    background: rgba(0, 0, 0, 0.30);
    border: 1px solid var(--c-border);
    color: var(--c-text);
    font-family: var(--font-sans);
    font-size: 13px;
    line-height: 1.55;
    padding: 10px 12px;
    resize: vertical;
    min-height: 96px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.modal-textarea::placeholder { color: var(--c-text-3); }
.modal-textarea:focus { outline: none; border-color: rgba(255,255,255,0.12); }
.modal-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-error {
    font-family: var(--font-sans);
    font-size: 12px;
    color: #F87171;
    background: rgba(220, 38, 38, 0.07);
    border: 1px solid rgba(220, 38, 38, 0.20);
    border-radius: var(--r-sm, 12px);
    padding: 8px 10px;
    margin: 0;
}

.modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px; }
.modal-btn {
    height: 38px;
    min-height: var(--tap-comfort, 48px);
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
    color: var(--c-text-2);
    border-color: var(--c-border);
}
.modal-btn--ghost:hover:not(:disabled) { border-color: rgba(255,255,255,0.12); color: var(--c-text); }
.modal-btn--primary {
    background: #34D399;
    color: #04221A;
}
.modal-btn--primary:hover:not(:disabled) { filter: brightness(1.08); }
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
