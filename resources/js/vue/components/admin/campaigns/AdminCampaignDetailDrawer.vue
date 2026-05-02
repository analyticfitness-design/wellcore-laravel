<script setup>
import { ref, computed, watch } from 'vue';
import { useApi } from '../../../composables/useApi';
import { formatCOP, formatNumber } from '../../../composables/useFormat';
import AdminCampaignFunnel from './AdminCampaignFunnel.vue';

const props = defineProps({
    campaignId: { type: Number, default: null },
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'pause', 'resume', 'duplicate']);

const api = useApi();
const detail = ref(null);
const loading = ref(false);
const confirmAction = ref(null); // 'pause' | 'resume' | null

const PLATFORM_LABELS = {
    meta:   'Meta Ads',
    google: 'Google Ads',
    tiktok: 'TikTok Ads',
    email:  'Email',
};

const STATUS_CONFIG = {
    active: { label: 'Activa',    bg: 'var(--color-wc-green-soft)',  text: 'var(--color-wc-green-text)' },
    paused: { label: 'Pausada',   bg: 'var(--color-wc-amber-soft)',  text: 'var(--color-wc-amber-text)' },
    ended:  { label: 'Terminada', bg: 'rgba(255,255,255,0.06)',      text: 'var(--color-wc-text-tertiary)' },
};

function roasColor(roas) {
    if (roas >= 3) return 'var(--color-wc-green-text)';
    if (roas >= 1) return 'var(--color-wc-amber-text)';
    return 'var(--color-wc-red-text)';
}

const campaign = computed(() => detail.value?.campaign ?? null);
const funnel   = computed(() => detail.value?.funnel   ?? []);
const timeline = computed(() => detail.value?.timeline ?? []);

// SVG timeline path
const timelinePath = computed(() => {
    const pts = timeline.value;
    if (!pts.length) return '';

    const w = 280;
    const h = 60;
    const maxSpend = Math.max(...pts.map(p => p.spend ?? 0), 1);

    const coords = pts.map((p, i) => ({
        x: Math.round((i / (pts.length - 1)) * w),
        y: Math.round(h - ((p.spend ?? 0) / maxSpend) * h),
    }));

    return coords.map((c, i) => (i === 0 ? `M${c.x},${c.y}` : `L${c.x},${c.y}`)).join(' ');
});

const timelineConvPath = computed(() => {
    const pts = timeline.value;
    if (!pts.length) return '';

    const w = 280;
    const h = 60;
    const maxConv = Math.max(...pts.map(p => p.conversions ?? 0), 1);

    const coords = pts.map((p, i) => ({
        x: Math.round((i / (pts.length - 1)) * w),
        y: Math.round(h - ((p.conversions ?? 0) / maxConv) * h),
    }));

    return coords.map((c, i) => (i === 0 ? `M${c.x},${c.y}` : `L${c.x},${c.y}`)).join(' ');
});

watch(() => [props.open, props.campaignId], async ([open, id]) => {
    if (!open || !id) {
        detail.value = null;
        confirmAction.value = null;
        return;
    }
    loading.value = true;
    try {
        const { data } = await api.get(`/api/v/admin/campaigns/${id}`);
        detail.value = data;
    } catch {
        detail.value = null;
    } finally {
        loading.value = false;
    }
});

function handleConfirm() {
    if (!campaign.value) return;
    if (confirmAction.value === 'pause') {
        emit('pause', campaign.value);
    } else if (confirmAction.value === 'resume') {
        emit('resume', campaign.value);
    }
    confirmAction.value = null;
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition name="backdrop-fade">
            <div
                v-if="open"
                class="drawer-backdrop"
                @click="$emit('close')"
                aria-hidden="true"
            ></div>
        </Transition>

        <!-- Drawer panel -->
        <Transition name="drawer-slide">
            <aside
                v-if="open"
                class="campaign-drawer"
                role="dialog"
                :aria-label="campaign?.name ?? 'Detalle de campaña'"
            >
                <!-- Header -->
                <div class="drawer-header">
                    <button class="drawer-close" @click="$emit('close')" aria-label="Cerrar panel">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <span class="drawer-eyebrow">DETALLE</span>
                </div>

                <!-- Loading state -->
                <div v-if="loading" class="drawer-loading">
                    <div v-for="i in 4" :key="i" class="drawer-skeleton"></div>
                </div>

                <!-- Content -->
                <div v-else-if="campaign" class="drawer-body">
                    <!-- Campaign identity -->
                    <div class="detail-identity">
                        <h2 class="detail-name">{{ campaign.name }}</h2>
                        <div class="detail-badges">
                            <span class="detail-badge detail-badge--platform">
                                {{ PLATFORM_LABELS[campaign.platform] ?? campaign.platform }}
                            </span>
                            <span
                                class="detail-badge"
                                :style="{
                                    background: STATUS_CONFIG[campaign.status]?.bg,
                                    color:      STATUS_CONFIG[campaign.status]?.text
                                }"
                            >{{ STATUS_CONFIG[campaign.status]?.label }}</span>
                        </div>
                        <div v-if="campaign.start_date" class="detail-dates">
                            <span class="dates-label">Periodo</span>
                            <span class="dates-value">
                                {{ campaign.start_date }}
                                <template v-if="campaign.end_date"> → {{ campaign.end_date }}</template>
                                <template v-else> → en curso</template>
                            </span>
                        </div>
                    </div>

                    <div class="drawer-divider"></div>

                    <!-- Quick stats row -->
                    <div class="drawer-stats">
                        <div class="drawer-stat">
                            <span class="ds-label">BUDGET</span>
                            <span class="ds-value">{{ formatCOP(campaign.budget_cop) }}</span>
                        </div>
                        <div class="drawer-stat">
                            <span class="ds-label">GASTADO</span>
                            <span class="ds-value">{{ formatCOP(campaign.spent_cop) }}</span>
                        </div>
                        <div class="drawer-stat">
                            <span class="ds-label">ROAS</span>
                            <span class="ds-value" :style="{ color: roasColor(campaign.roas) }">
                                {{ campaign.roas.toFixed(2) }}x
                            </span>
                        </div>
                        <div class="drawer-stat">
                            <span class="ds-label">CPL</span>
                            <span class="ds-value">{{ formatCOP(campaign.cpl) }}</span>
                        </div>
                    </div>

                    <div class="drawer-divider"></div>

                    <!-- Funnel -->
                    <div class="drawer-section">
                        <span class="section-title">FUNNEL DE CONVERSIÓN</span>
                        <AdminCampaignFunnel :funnel="funnel" />
                    </div>

                    <!-- Timeline (solo si hay datos) -->
                    <template v-if="timeline.length >= 2">
                        <div class="drawer-divider"></div>
                        <div class="drawer-section">
                            <span class="section-title">PERFORMANCE 30 DÍAS</span>
                            <div class="timeline-chart">
                                <svg viewBox="0 0 280 60" class="timeline-svg" aria-hidden="true">
                                    <path
                                        :d="timelinePath"
                                        fill="none"
                                        stroke="var(--color-wc-accent)"
                                        stroke-width="1.5"
                                        stroke-linejoin="round"
                                        opacity="0.7"
                                    />
                                    <path
                                        :d="timelineConvPath"
                                        fill="none"
                                        stroke="var(--color-wc-green-text)"
                                        stroke-width="1.5"
                                        stroke-linejoin="round"
                                        opacity="0.6"
                                    />
                                </svg>
                                <div class="timeline-legend">
                                    <span class="legend-item legend-item--spend">Spend diario</span>
                                    <span class="legend-item legend-item--conv">Conversiones</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="drawer-divider"></div>

                    <!-- Confirm modal inline -->
                    <div v-if="confirmAction" class="confirm-block">
                        <p class="confirm-msg">
                            <template v-if="confirmAction === 'pause'">
                                Pausar esta campaña solo afecta el seguimiento local. No pausa la campaña real en {{ PLATFORM_LABELS[campaign.platform] }}.
                            </template>
                            <template v-else>
                                Reactivar esta campaña en el tracker. La campaña real en {{ PLATFORM_LABELS[campaign.platform] }} no se modifica.
                            </template>
                        </p>
                        <div class="confirm-actions">
                            <button class="btn-confirm" @click="handleConfirm">Confirmar</button>
                            <button class="btn-cancel" @click="confirmAction = null">Cancelar</button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-else class="drawer-actions">
                        <button
                            v-if="campaign.status === 'active'"
                            class="action-btn"
                            @click="confirmAction = 'pause'"
                        >
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                            </svg>
                            Pausar campaña
                        </button>
                        <button
                            v-else-if="campaign.status === 'paused'"
                            class="action-btn action-btn--green"
                            @click="confirmAction = 'resume'"
                        >
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            Reactivar campaña
                        </button>
                        <button
                            class="action-btn"
                            @click="$emit('duplicate', campaign); $emit('close')"
                        >
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                            </svg>
                            Duplicar como template
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="drawer-empty">
                    <p class="empty-msg">"Sin datos para esta campaña."</p>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>

<style scoped>
.drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 49;
}

.campaign-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(420px, 100vw);
    background: var(--c-surface);
    border-left: 1px solid var(--c-border);
    z-index: 50;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.drawer-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--c-border);
    flex-shrink: 0;
}

.drawer-close {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    color: var(--c-text-2);
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
}

.drawer-close:hover { background: rgba(255,255,255,0.08); }

.drawer-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    text-transform: uppercase;
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.drawer-loading {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
}

.drawer-skeleton {
    height: 48px;
    background: var(--c-surface-2);
    border-radius: 10px;
    animation: page-pulse 1.5s ease-in-out infinite;
}

.detail-identity {
    padding-bottom: 16px;
}

.detail-name {
    font-family: var(--font-display);
    font-size: 26px;
    letter-spacing: 0.04em;
    color: var(--c-text);
    text-transform: uppercase;
    line-height: 1.1;
    margin: 0 0 10px;
}

.detail-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.detail-badge {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: var(--r-pill, 999px);
}

.detail-badge--platform {
    background: rgba(255,255,255,0.06);
    color: var(--c-text-2);
}

.detail-dates {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dates-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.dates-value {
    font-family: var(--font-display);
    font-size: 12px;
    color: var(--c-text-2);
}

.drawer-divider {
    height: 1px;
    background: var(--c-border);
    margin: 14px 0;
}

.drawer-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.drawer-stat {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ds-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.ds-value {
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 700;
    color: var(--c-text);
    font-feature-settings: 'tnum' 1;
}

.drawer-section {
    padding: 4px 0;
}

.section-title {
    display: block;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-bottom: 14px;
}

/* Timeline SVG chart */
.timeline-chart {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--c-border);
    border-radius: 10px;
    padding: 12px;
}

.timeline-svg {
    width: 100%;
    height: auto;
    overflow: visible;
    display: block;
}

.timeline-legend {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}

.legend-item {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-item::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 2px;
    border-radius: 1px;
}

.legend-item--spend { color: var(--c-text-3); }
.legend-item--spend::before { background: var(--c-accent); }

.legend-item--conv { color: var(--c-text-3); }
.legend-item--conv::before { background: #34D399; }

/* Confirm block */
.confirm-block {
    background: rgba(220,38,38,0.06);
    border: 1px solid rgba(220,38,38,0.15);
    border-radius: 10px;
    padding: 14px;
    margin-top: 4px;
}

.confirm-msg {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--c-text-2);
    line-height: 1.5;
    margin: 0 0 12px;
}

.confirm-actions {
    display: flex;
    gap: 8px;
}

.btn-confirm {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    background: var(--c-accent);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    cursor: pointer;
    transition: opacity 0.15s var(--ease-out);
    min-height: var(--tap-comfort, 48px);
}

.btn-confirm:hover { opacity: 0.85; }

.btn-cancel {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    background: rgba(255,255,255,0.04);
    color: var(--c-text-2);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    padding: 7px 14px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out);
    min-height: var(--tap-comfort, 48px);
}

.btn-cancel:hover { background: rgba(255,255,255,0.08); }

/* Actions */
.drawer-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 4px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: var(--c-text-2);
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: var(--r-sm, 12px);
    padding: 10px 16px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
    min-height: var(--tap-comfort, 48px);
}

.action-btn:hover {
    background: rgba(255,255,255,0.07);
    color: var(--c-text);
}

.action-btn--green {
    color: #34D399;
    background: rgba(16,185,129,0.1);
    border-color: rgba(16,185,129,0.2);
}

.action-btn--green:hover { background: rgba(16,185,129,0.15); }

/* Empty state */
.drawer-empty {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
}

.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 13px;
    color: var(--c-text-3);
    text-align: center;
}

/* Transitions */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.2s var(--ease-out);
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}

.drawer-slide-enter-active,
.drawer-slide-leave-active {
    transition: transform 0.25s var(--ease-out);
}

.drawer-slide-enter-from,
.drawer-slide-leave-to {
    transform: translateX(100%);
}

/* Skeleton */
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    .drawer-skeleton { animation: none !important; }
    .drawer-slide-enter-active,
    .drawer-slide-leave-active { transition: none !important; }
    .backdrop-fade-enter-active,
    .backdrop-fade-leave-active { transition: none !important; }
}
</style>
