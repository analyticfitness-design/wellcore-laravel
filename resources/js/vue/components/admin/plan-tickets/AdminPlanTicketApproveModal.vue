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
const planIdsRaw = ref('');
const forceWithoutPlans = ref(false);
const notesInputRef = ref(null);
const planIdsInputRef = ref(null);

const clientName = computed(() => props.ticket?.client_name ?? 'Cliente');
const planLabel = computed(() => {
    const t = props.ticket?.plan_type ?? '';
    return t.charAt(0).toUpperCase() + t.slice(1);
});

// Parse "12, 34, 56" → [12, 34, 56]. Ignora basura.
const parsedPlanIds = computed(() => {
    return planIdsRaw.value
        .split(/[,\s]+/)
        .map((s) => s.trim())
        .filter(Boolean)
        .map((s) => Number(s))
        .filter((n) => Number.isInteger(n) && n > 0);
});

const hasParsedIds = computed(() => parsedPlanIds.value.length > 0);
const ticketAlreadyHasIds = computed(() => Array.isArray(props.ticket?.generated_plan_ids) && props.ticket.generated_plan_ids.length > 0);
// Si el ticket ya tiene IDs cargados desde antes (e.g., motor v2 los insertó vía API),
// el admin puede aprobar sin volver a pegarlos.
const canApprove = computed(() => hasParsedIds.value || ticketAlreadyHasIds.value || forceWithoutPlans.value);
const showOverrideOption = computed(() => !hasParsedIds.value && !ticketAlreadyHasIds.value);

watch(() => props.open, async (isOpen) => {
    if (isOpen) {
        adminNotes.value = '';
        planIdsRaw.value = '';
        forceWithoutPlans.value = false;
        await nextTick();
        planIdsInputRef.value?.focus();
    }
});

function onBackdropClick() {
    if (!props.submitting) emit('close');
}

function onConfirm() {
    if (props.submitting || !canApprove.value) return;
    emit('confirm', {
        adminNotes: adminNotes.value.trim() || null,
        generatedPlanIds: parsedPlanIds.value,
        forceCompleteWithoutPlans: forceWithoutPlans.value && !hasParsedIds.value && !ticketAlreadyHasIds.value,
    });
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
                        <span class="modal-label">
                            IDs de planes generados
                            <span v-if="ticketAlreadyHasIds" class="modal-label-aside">(ya tiene {{ ticket.generated_plan_ids.length }} cargados)</span>
                            <span v-else-if="!hasParsedIds" class="modal-label-aside modal-label-aside--required">requerido</span>
                        </span>
                        <input
                            ref="planIdsInputRef"
                            v-model="planIdsRaw"
                            type="text"
                            class="modal-input"
                            :placeholder="ticketAlreadyHasIds ? `${ticket.generated_plan_ids.join(', ')} (se conservan si dejas vacio)` : 'ej: 1234, 1235, 1236'"
                            :disabled="submitting"
                            autocomplete="off"
                            inputmode="numeric"
                        />
                        <span class="modal-hint">
                            IDs de assigned_plans creados por el motor v2 o por Claude Code para este ticket. Separados por coma o espacio.
                        </span>
                    </label>

                    <label v-if="showOverrideOption" class="modal-field modal-field--inline">
                        <input
                            v-model="forceWithoutPlans"
                            type="checkbox"
                            class="modal-checkbox"
                            :disabled="submitting"
                        />
                        <span class="modal-checkbox-label">
                            <strong>Override</strong> — aprobar sin planes generados.
                            <span class="modal-checkbox-hint">
                                Solo si los planes ya existen por otra via (ej. script PHP) y el cliente ya los puede ver. No recomendado.
                            </span>
                        </span>
                    </label>

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
                            :class="{ 'modal-btn--warning': forceWithoutPlans && !hasParsedIds && !ticketAlreadyHasIds }"
                            @click="onConfirm"
                            :disabled="submitting || !canApprove"
                            :title="!canApprove ? 'Ingresa al menos un ID de plan generado o activa el override' : ''"
                        >
                            {{ submitting
                                ? 'Aprobando...'
                                : (forceWithoutPlans && !hasParsedIds && !ticketAlreadyHasIds
                                    ? 'Aprobar SIN planes (override)'
                                    : 'Aprobar y activar plan') }}
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
    color: #FCD34D;
    font-family: var(--font-display);
    font-size: 16px;
    line-height: 1;
}
.modal-warning-text {
    font-family: var(--font-sans);
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--c-text-2);
    margin: 0;
}
.modal-warning-text strong { color: var(--c-text); font-weight: 600; }

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
    min-height: 80px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.modal-textarea::placeholder {
    color: var(--c-text-3);
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
}
.modal-textarea:focus { outline: none; border-color: rgba(255,255,255,0.12); }
.modal-textarea:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-input {
    border-radius: var(--r-sm, 12px);
    background: rgba(0, 0, 0, 0.30);
    border: 1px solid var(--c-border);
    color: var(--c-text);
    font-family: var(--font-mono, monospace);
    font-feature-settings: 'tnum' 1;
    font-size: 13px;
    line-height: 1.4;
    padding: 10px 12px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.modal-input::placeholder {
    color: var(--c-text-3);
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
}
.modal-input:focus { outline: none; border-color: rgba(255,255,255,0.18); }
.modal-input:disabled { opacity: 0.6; cursor: not-allowed; }

.modal-hint {
    font-family: var(--font-sans);
    font-size: 11px;
    line-height: 1.45;
    color: var(--c-text-3);
    margin-top: -2px;
}

.modal-label-aside {
    font-family: var(--font-sans);
    font-size: 9px;
    letter-spacing: normal;
    text-transform: none;
    color: var(--c-text-3);
    margin-left: 6px;
    font-weight: 400;
}
.modal-label-aside--required { color: #F87171; }

.modal-field--inline {
    flex-direction: row;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--r-sm, 12px);
    border: 1px dashed var(--c-border);
    background: rgba(245, 158, 11, 0.04);
}
.modal-checkbox {
    width: 16px; height: 16px;
    margin-top: 2px;
    accent-color: #FCD34D;
    cursor: pointer;
}
.modal-checkbox:disabled { cursor: not-allowed; }
.modal-checkbox-label {
    font-family: var(--font-sans);
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--c-text-2);
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.modal-checkbox-label strong { color: #FCD34D; font-weight: 600; }
.modal-checkbox-hint {
    font-size: 11px;
    color: var(--c-text-3);
}

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
.modal-btn--ghost:hover:not(:disabled) {
    border-color: rgba(255,255,255,0.12);
    color: var(--c-text);
}
.modal-btn--primary {
    background: #34D399;
    color: #04221A;
}
.modal-btn--primary:hover:not(:disabled) { filter: brightness(1.08); }
.modal-btn--warning {
    background: #FCD34D;
    color: #2A1B00;
}
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
