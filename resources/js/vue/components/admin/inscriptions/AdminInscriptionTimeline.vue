<script setup>
defineProps({
    inscription: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const STATUS_LABELS = {
    pendiente:  'Pendiente de contacto',
    nuevo:      'Lead nuevo',
    contactado: 'Contactado',
    convertido: 'Plan enviado',
    pagado:     'Pagado',
    activo:     'Activo',
    rechazado:  'Rechazado',
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
    background: var(--color-wc-bg-secondary);
    border-top: 1px solid var(--color-wc-border-2);
    border-left: 1px solid var(--color-wc-border-2);
    width: 100%;
    max-width: 360px;
    padding: 20px;
    overflow-y: auto;
    height: 100%;
}
@media (max-width: 639px) {
    .detail-panel {
        max-width: 100%;
        border-radius: 14px 14px 0 0;
        border: 1px solid var(--color-wc-border-2);
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
    background: var(--color-wc-blue-soft);
    color: var(--color-wc-blue-text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-data);
    font-size: 15px;
    font-weight: 600;
    flex-shrink: 0;
}
.detail-title { flex: 1; min-width: 0; }
.detail-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-wc-text);
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.detail-sub {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
}
.detail-close {
    padding: 6px;
    border: none;
    background: transparent;
    color: var(--color-wc-text-tertiary);
    cursor: pointer;
    border-radius: 6px;
    transition: color 0.15s;
    flex-shrink: 0;
}
.detail-close:hover { color: var(--color-wc-text); }
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
    font-family: var(--font-mono);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
}
.detail-value {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-text-secondary);
    word-break: break-word;
}
.detail-link {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-green-text);
    text-decoration: none;
}
.detail-link:hover { text-decoration: underline; }
.detail-section-label {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--color-wc-border);
}
.detail-timeline { display: flex; flex-direction: column; gap: 14px; }
.detail-tl-item { display: flex; align-items: flex-start; gap: 10px; }
.detail-tl-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-wc-border-2);
    flex-shrink: 0;
    margin-top: 3px;
}
.detail-tl-dot--current { background: var(--color-wc-accent); }
.detail-tl-content { display: flex; flex-direction: column; gap: 2px; }
.detail-tl-event {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-text-secondary);
}
.detail-tl-time {
    font-family: var(--font-mono);
    font-size: 9px;
    letter-spacing: 0.1em;
    color: var(--color-wc-text-tertiary);
}

.drawer-fade-enter-active { transition: opacity 0.2s; }
.drawer-fade-leave-active { transition: opacity 0.15s; }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .detail-close { transition: none !important; }
    .drawer-fade-enter-active, .drawer-fade-leave-active { transition: none !important; }
}
</style>
