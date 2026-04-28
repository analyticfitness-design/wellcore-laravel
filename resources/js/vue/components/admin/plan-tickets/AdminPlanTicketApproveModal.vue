<script setup>
import { ref, watch, nextTick, computed } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    ticket: { type: Object, default: null },
    submitting: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'confirm']);

const adminNotes = ref('');
const notesInputRef = ref(null);

const clientName = computed(() => props.ticket?.client_name ?? 'Cliente');
const planLabel = computed(() => {
    const t = props.ticket?.plan_type ?? '';
    return t.charAt(0).toUpperCase() + t.slice(1);
});

watch(() => props.open, async (isOpen) => {
    if (isOpen) {
        adminNotes.value = '';
        await nextTick();
        notesInputRef.value?.focus();
    }
});

function onBackdropClick() {
    if (!props.submitting) emit('close');
}

function onConfirm() {
    if (props.submitting) return;
    emit('confirm', { adminNotes: adminNotes.value.trim() || null });
}

function onKey(e) {
    if (e.key === 'Escape' && !props.submitting) emit('close');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="open"
                class="modal-root"
                role="dialog"
                aria-modal="true"
                aria-labelledby="approve-title"
                @keydown="onKey"
            >
                <div class="modal-backdrop" @click="onBackdropClick" aria-hidden="true"></div>

                <div class="modal-card" role="document">
                    <header class="modal-head">
                        <span class="modal-eyebrow">DECISION CRITICA</span>
                        <h2 id="approve-title" class="modal-title">Aprobar plan</h2>
                        <p v-if="ticket" class="modal-sub">
                            {{ clientName }} · {{ planLabel }} · {{ ticket.coach_name || `coach #${ticket.coach_id}` }}
                        </p>
                    </header>

                    <p class="modal-tagline">
                        "Aprobar activa el plan al cliente. La decision se vuelve compromiso en el momento."
                    </p>

                    <div class="modal-warning" role="note">
                        <span class="modal-warning-icon" aria-hidden="true">!</span>
                        <p class="modal-warning-text">
                            Esto activa el plan de
                            <strong>{{ clientName }}</strong>
                            inmediatamente. Recibe notificacion en la app y se le envia push.
                        </p>
                    </div>

                    <label class="modal-field">
                        <span class="modal-label">Notas internas (opcional)</span>
                        <textarea
                            ref="notesInputRef"
                            v-model="adminNotes"
                            class="modal-textarea"
                            rows="3"
                            placeholder="ej: Validado contra historial de checkin. Coach ajusto carga con buen criterio."
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
                            {{ submitting ? 'Aprobando...' : 'Aprobar y activar plan' }}
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
    color: var(--color-wc-text);
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

.modal-warning {
    display: grid;
    grid-template-columns: 28px 1fr;
    gap: 10px;
    align-items: start;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid rgba(245, 158, 11, 0.20);
    background: rgba(245, 158, 11, 0.06);
}
.modal-warning-icon {
    width: 26px; height: 26px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(245, 158, 11, 0.20);
    color: var(--color-wc-amber-text, #FCD34D);
    font-family: var(--font-display);
    font-size: 16px;
    line-height: 1;
}
.modal-warning-text {
    font-family: var(--font-sans);
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--color-wc-text-secondary);
    margin: 0;
}
.modal-warning-text strong { color: var(--color-wc-text); font-weight: 600; }

.modal-field { display: flex; flex-direction: column; gap: 6px; }
.modal-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
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
    min-height: 80px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.modal-textarea::placeholder {
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
}
.modal-textarea:focus { outline: none; border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16)); }
.modal-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

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
.modal-btn--ghost:hover:not(:disabled) {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.16));
    color: var(--color-wc-text);
}
.modal-btn--primary {
    background: var(--color-wc-green-text, #34D399);
    color: #04221A;
}
.modal-btn--primary:hover:not(:disabled) { filter: brightness(1.08); }
.modal-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.18s var(--ease-out, ease); }
.modal-enter-active .modal-card, .modal-leave-active .modal-card {
    transition: transform 0.22s var(--ease-out, ease), opacity 0.18s var(--ease-out, ease);
}
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-card, .modal-leave-to .modal-card { transform: translateY(8px); opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-enter-active, .modal-leave-active,
    .modal-enter-active .modal-card, .modal-leave-active .modal-card { transition: none !important; }
}
</style>
