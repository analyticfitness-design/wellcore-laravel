<script setup>
import { ref, computed, watch } from 'vue';
import { useAdminInscriptionsStore } from '@/stores/adminInscriptions';

const store = useAdminInscriptionsStore();

const inscription = computed(() => store.contactTarget);
const sending = ref(false);
const sent = ref(false);
const contactError = ref('');
const waTemplate = ref('');
const emailTemplate = ref('');

watch(inscription, (val) => {
    if (!val) { sent.value = false; contactError.value = ''; return; }
    const nombre = val.nombre?.split(' ')[0] ?? 'hola';
    const plan = val.plan_raw ?? 'tu plan';
    waTemplate.value = `Hola ${nombre}, vi que te interesó el plan ${plan.toUpperCase()} en WellCore. Cuéntame, ¿qué buscas lograr con el entrenamiento?`;
    emailTemplate.value = `${nombre}, gracias por tu interés en WellCore.\n\nEl plan ${plan.toUpperCase()} incluye entrenamiento personalizado, seguimiento semanal y ajustes según tus resultados.\n\n¿Cuándo podemos agendar una llamada de 10 minutos?`;
    sent.value = false;
}, { immediate: true });

function openWhatsApp() {
    if (!inscription.value?.whatsapp) return;
    const num = inscription.value.whatsapp.replace(/[^0-9]/g, '');
    const msg = encodeURIComponent(waTemplate.value);
    window.open(`https://wa.me/${num}?text=${msg}`, '_blank', 'noopener,noreferrer');
}

function openEmail() {
    if (!inscription.value?.email) return;
    const plan = inscription.value.plan_raw?.toUpperCase() ?? '';
    const sub = encodeURIComponent(`WellCore — Plan ${plan}`);
    const body = encodeURIComponent(emailTemplate.value);
    window.open(`mailto:${inscription.value.email}?subject=${sub}&body=${body}`, '_self');
}

async function confirmContact() {
    if (!inscription.value || sent.value) return;
    sending.value = true;
    contactError.value = '';
    try {
        await store.moveCard(inscription.value.id, 'contactado');
        sent.value = true;
        setTimeout(() => store.closeContact(), 900);
    } catch {
        contactError.value = 'No se pudo registrar el contacto. Intenta de nuevo.';
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="inscription"
                class="modal-backdrop"
                role="dialog"
                aria-modal="true"
                :aria-label="`Contactar a ${inscription.nombre}`"
                @click.self="store.closeContact"
            >
                <div class="modal-panel">
                    <!-- Header -->
                    <div class="modal-header">
                        <div class="modal-avatar">{{ inscription.initial }}</div>
                        <div class="modal-title">
                            <h3 class="modal-name">{{ inscription.nombre }}</h3>
                            <span class="modal-sub">{{ inscription.plan }} · {{ inscription.ciudad || 'Ciudad no indicada' }}</span>
                        </div>
                        <button class="modal-close" @click="store.closeContact" aria-label="Cerrar modal">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <!-- WhatsApp section -->
                    <div v-if="inscription.whatsapp" class="modal-section">
                        <div class="modal-section-label">PLANTILLA WHATSAPP</div>
                        <textarea v-model="waTemplate" class="modal-textarea" rows="3" aria-label="Mensaje WhatsApp"></textarea>
                        <button class="modal-btn modal-btn--wa" @click="openWhatsApp">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.75.75 0 00.917.918l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.347 0-4.518-.802-6.237-2.148a.75.75 0 00-.593-.131l-3.22 1.079 1.079-3.22a.75.75 0 00-.131-.593A9.958 9.958 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            ABRIR WHATSAPP
                        </button>
                    </div>

                    <!-- Email section -->
                    <div v-if="inscription.email" class="modal-section">
                        <div class="modal-section-label">PLANTILLA EMAIL</div>
                        <textarea v-model="emailTemplate" class="modal-textarea" rows="4" aria-label="Mensaje email"></textarea>
                        <button class="modal-btn modal-btn--email" @click="openEmail">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            ABRIR EMAIL
                        </button>
                    </div>

                    <!-- Contact error -->
                    <Transition name="modal-fade">
                        <div v-if="contactError" class="modal-contact-error">{{ contactError }}</div>
                    </Transition>

                    <!-- Confirm -->
                    <button
                        class="modal-confirm"
                        :class="{ 'modal-confirm--sent': sent }"
                        @click="confirmContact"
                        :disabled="sending || sent"
                        aria-live="polite"
                    >
                        <template v-if="sent">REGISTRADO — MOVIDO A CONTACTADO</template>
                        <template v-else-if="sending">REGISTRANDO...</template>
                        <template v-else>REGISTRAR CONTACTO → MOVER A CONTACTADO</template>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9000;
    background: rgba(0,0,0,0.72);
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
@media (min-width: 640px) {
    .modal-backdrop { align-items: center; }
}
.modal-panel {
    background: var(--c-surface);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: var(--r-md, 16px) var(--r-md, 16px) 0 0;
    padding: 20px;
    width: 100%;
    max-width: 460px;
    max-height: 90vh;
    overflow-y: auto;
}
@media (min-width: 640px) {
    .modal-panel { border-radius: var(--r-md, 16px); }
}
.modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.modal-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(59,130,246,0.1);
    color: #60A5FA;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 600;
    flex-shrink: 0;
}
.modal-title { flex: 1; min-width: 0; }
.modal-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0 0 2px;
}
.modal-sub {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.0px;
    color: var(--c-text-3);
}
.modal-close {
    margin-left: auto;
    padding: 6px;
    border: none;
    background: transparent;
    color: var(--c-text-3);
    cursor: pointer;
    border-radius: 6px;
    min-height: var(--tap-comfort, 48px);
    flex-shrink: 0;
    transition: color 0.15s;
}
.modal-close:hover { color: var(--c-text); }
.modal-section { margin-bottom: 14px; }
.modal-section-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    margin-bottom: 7px;
}
.modal-textarea {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    padding: 9px 10px;
    font-family: var(--font-sans);
    font-size: 12px;
    line-height: 1.55;
    color: var(--c-text);
    resize: vertical;
    outline: none;
    margin-bottom: 8px;
    display: block;
    transition: border-color 0.15s;
}
.modal-textarea:focus { border-color: rgba(255,255,255,0.12); }
.modal-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: transparent;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    cursor: pointer;
    transition: all 0.15s var(--ease-out);
}
.modal-btn--wa {
    color: #34D399;
    border-color: rgba(52,211,153,0.2);
}
.modal-btn--wa:hover {
    background: rgba(16,185,129,0.1);
    border-color: rgba(52,211,153,0.4);
}
.modal-btn--email {
    color: #60A5FA;
    border-color: rgba(96,165,250,0.2);
}
.modal-btn--email:hover {
    background: rgba(59,130,246,0.1);
    border-color: rgba(96,165,250,0.4);
}
.modal-confirm {
    width: 100%;
    margin-top: 6px;
    padding: 11px;
    border-radius: var(--r-sm, 12px);
    border: none;
    background: var(--c-accent);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: white;
    cursor: pointer;
    min-height: var(--tap-comfort, 48px);
    transition: background 0.15s var(--ease-out);
}
.modal-confirm:hover:not(:disabled) { background: #b91c1c; }
.modal-confirm--sent {
    background: rgba(16,185,129,0.1);
    color: #34D399;
    border: 1px solid rgba(52,211,153,0.3);
}
.modal-confirm:disabled { cursor: not-allowed; opacity: 0.7; }
.modal-contact-error {
    border-radius: var(--r-sm, 12px);
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: rgba(220, 38, 38, 0.06);
    padding: 8px 10px;
    font-family: var(--font-sans);
    font-size: 12px;
    color: #F87171;
    margin-bottom: 4px;
}

.modal-fade-enter-active { transition: opacity 0.2s; }
.modal-fade-leave-active { transition: opacity 0.15s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .modal-btn, .modal-confirm, .modal-close { transition: none !important; }
    .modal-fade-enter-active, .modal-fade-leave-active { transition: none !important; }
}
</style>
