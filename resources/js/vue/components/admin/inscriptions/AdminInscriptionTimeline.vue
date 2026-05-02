<script setup>
defineProps({
    inscription: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const STATUS_LABELS = {
    pendiente:       'Pendiente de contacto',
    nuevo:           'Lead nuevo',
    pending_contact: 'Pendiente de contacto',
    contactado:      'Contactado',
    contacted:       'Contactado',
    convertido:      'Plan enviado',
    payment_sent:    'Plan enviado',
    pagado:          'Pagado',
    paid:            'Pagado',
    activo:          'Activo',
    rechazado:       'Rechazado',
    rejected:        'Rechazado',
};
</script>

<template>
    <Teleport to="body">
        <Transition name="drawer-fade">
            <div
                v-if="inscription"
                class="detail-backdrop"
                role="dialog"
                aria-modal="true"
                :aria-label="`Detalle de ${inscription.nombre}`"
                @click.self="$emit('close')"
            >
                <div class="detail-panel">
                    <!-- Header -->
                    <div class="detail-header">
                        <div class="detail-avatar">{{ inscription.initial }}</div>
                        <div class="detail-title">
                            <h3 class="detail-name">{{ inscription.nombre }}</h3>
                            <span class="detail-sub">{{ STATUS_LABELS[inscription.status] ?? inscription.status }}</span>
                        </div>
                        <button class="detail-close" @click="$emit('close')" aria-label="Cerrar detalle">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Fields -->
                    <div class="detail-grid">
                        <div class="detail-field">
                            <span class="detail-label">EMAIL</span>
                            <span class="detail-value">{{ inscription.email || '—' }}</span>
                        </div>
                        <div class="detail-field">
                            <span class="detail-label">WHATSAPP</span>
                            <a
                                v-if="inscription.whatsapp"
                                :href="`https://wa.me/${inscription.whatsapp.replace(/[^0-9]/g, '')}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="detail-link"
                            >{{ inscription.whatsapp }}</a>
                            <span v-else class="detail-value">—</span>
                        </div>
                        <div class="detail-field">
                            <span class="detail-label">CIUDAD</span>
                            <span class="detail-value">{{ inscription.ciudad || '—' }}</span>
                        </div>
                        <div class="detail-field">
                            <span class="detail-label">PLAN</span>
                            <span class="detail-value">{{ inscription.plan || '—' }}</span>
                        </div>
                        <div class="detail-field detail-field--full">
                            <span class="detail-label">OBJETIVO</span>
                            <span class="detail-value">{{ inscription.objetivo || '—' }}</span>
                        </div>
                        <div class="detail-field">
                            <span class="detail-label">EXPERIENCIA</span>
                            <span class="detail-value" style="text-transform: capitalize;">{{ inscription.experiencia || '—' }}</span>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="detail-section-label">HISTORIAL</div>
                    <div class="detail-timeline" role="list">
                        <div class="detail-tl-item" role="listitem">
                            <div class="detail-tl-dot detail-tl-dot--current"></div>
                            <div class="detail-tl-content">
                                <span class="detail-tl-event">{{ STATUS_LABELS[inscription.status] ?? inscription.status }}</span>
                                <span class="detail-tl-time">{{ inscription.time_ago }}</span>
                            </div>
                        </div>
                        <div class="detail-tl-item" role="listitem">
                            <div class="detail-tl-dot"></div>
                            <div class="detail-tl-content">
                                <span class="detail-tl-event">Inscripción recibida</span>
                                <span class="detail-tl-time">{{ inscription.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.detail-backdrop {
    position: fixed;
    inset: 0;
    z-index: 8900;
    background: rgba(0,0,0,0.55);
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}
.detail-panel {
    background: var(--c-surface);
    border-top: 1px solid rgba(255,255,255,0.12);
    border-left: 1px solid rgba(255,255,255,0.12);
    width: 100%;
    max-width: 360px;
    padding: 20px;
    overflow-y: auto;
    height: 100%;
}
@media (max-width: 639px) {
    .detail-panel {
        max-width: 100%;
        border-radius: var(--r-md, 16px) var(--r-md, 16px) 0 0;
        border: 1px solid rgba(255,255,255,0.12);
        border-bottom: none;
        max-height: 78vh;
        height: auto;
    }
}
.detail-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.detail-avatar {
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
.detail-title { flex: 1; min-width: 0; }
.detail-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.detail-sub {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    color: var(--c-text-3);
}
.detail-close {
    padding: 6px;
    border: none;
    background: transparent;
    color: var(--c-text-3);
    cursor: pointer;
    border-radius: 6px;
    min-height: var(--tap-comfort, 48px);
    transition: color 0.15s;
    flex-shrink: 0;
}
.detail-close:hover { color: var(--c-text); }
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 18px;
}
.detail-field {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.detail-field--full {
    grid-column: 1 / -1;
}
.detail-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
}
.detail-value {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
    word-break: break-word;
}
.detail-link {
    font-family: var(--font-sans);
    font-size: 12px;
    color: #34D399;
    text-decoration: none;
}
.detail-link:hover { text-decoration: underline; }
.detail-section-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    margin-bottom: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--c-border);
}
.detail-timeline { display: flex; flex-direction: column; gap: 14px; }
.detail-tl-item { display: flex; align-items: flex-start; gap: 10px; }
.detail-tl-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    flex-shrink: 0;
    margin-top: 3px;
}
.detail-tl-dot--current { background: var(--c-accent); }
.detail-tl-content { display: flex; flex-direction: column; gap: 2px; }
.detail-tl-event {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
}
.detail-tl-time {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 0.8px;
    color: var(--c-text-3);
}

.drawer-fade-enter-active { transition: opacity 0.2s; }
.drawer-fade-leave-active { transition: opacity 0.15s; }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .detail-close { transition: none !important; }
    .drawer-fade-enter-active, .drawer-fade-leave-active { transition: none !important; }
}
</style>
