<script setup>
import { formatCOP, formatNumber } from '../../../composables/useFormat';

const props = defineProps({
    campaign: { type: Object, required: true },
});

const emit = defineEmits(['open-detail', 'pause', 'resume', 'duplicate']);

const PLATFORM_LABELS = {
    meta:   'Meta',
    google: 'Google',
    tiktok: 'TikTok',
    email:  'Email',
};

const PLATFORM_COLORS = {
    meta:   { bg: 'rgba(59,130,246,0.1)', text: '#60A5FA' },
    google: { bg: 'rgba(220,38,38,0.1)', text: '#F87171' },
    tiktok: { bg: 'rgba(6,182,212,0.08)', text: '#67E8F9' },
    email:  { bg: 'rgba(245,158,11,0.1)', text: '#FCD34D' },
};

const STATUS_CONFIG = {
    active: { label: 'Activa',  bg: 'var(--color-wc-green-soft)',  text: 'var(--color-wc-green-text)' },
    paused: { label: 'Pausada', bg: 'var(--color-wc-amber-soft)',  text: 'var(--color-wc-amber-text)' },
    ended:  { label: 'Terminada', bg: 'rgba(255,255,255,0.06)', text: 'var(--color-wc-text-tertiary)' },
};

function roasColor(roas) {
    if (roas >= 3) return 'var(--color-wc-green-text)';
    if (roas >= 1) return 'var(--color-wc-amber-text)';
    return 'var(--color-wc-red-text)';
}

function platformStyle(platform) {
    return PLATFORM_COLORS[platform] ?? { bg: 'rgba(255,255,255,0.06)', text: 'var(--color-wc-text-tertiary)' };
}
</script>

<template>
    <div class="campaign-card" @click="$emit('open-detail', campaign)">
        <div class="card-top">
            <div class="card-name-row">
                <span class="card-name">{{ campaign.name }}</span>
                <svg class="card-chevron" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </div>
            <div class="card-badges">
                <span
                    class="badge"
                    :style="{ background: platformStyle(campaign.platform).bg, color: platformStyle(campaign.platform).text }"
                >{{ PLATFORM_LABELS[campaign.platform] ?? campaign.platform }}</span>
                <span
                    class="badge"
                    :style="{ background: STATUS_CONFIG[campaign.status]?.bg, color: STATUS_CONFIG[campaign.status]?.text }"
                >{{ STATUS_CONFIG[campaign.status]?.label ?? campaign.status }}</span>
            </div>
        </div>

        <div class="card-metrics">
            <div class="metric">
                <span class="metric-label">SPEND</span>
                <span class="metric-value">{{ formatCOP(campaign.spent_cop) }}</span>
            </div>
            <div class="metric">
                <span class="metric-label">CLICKS</span>
                <span class="metric-value">{{ formatNumber(campaign.clicks) }}</span>
            </div>
            <div class="metric">
                <span class="metric-label">CONV.</span>
                <span class="metric-value">{{ campaign.sales }}</span>
            </div>
            <div class="metric">
                <span class="metric-label">ROAS</span>
                <span class="metric-value" :style="{ color: roasColor(campaign.roas) }">{{ campaign.roas.toFixed(2) }}x</span>
            </div>
        </div>

        <div class="card-actions" @click.stop>
            <button
                v-if="campaign.status === 'active'"
                class="card-btn"
                @click="$emit('pause', campaign)"
                aria-label="Pausar campaña"
            >
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                </svg>
                Pausar
            </button>
            <button
                v-else-if="campaign.status === 'paused'"
                class="card-btn card-btn--green"
                @click="$emit('resume', campaign)"
                aria-label="Reactivar campaña"
            >
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                </svg>
                Reactivar
            </button>
            <button
                class="card-btn"
                @click="$emit('duplicate', campaign)"
                aria-label="Duplicar campaña"
            >
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                </svg>
                Duplicar
            </button>
        </div>
    </div>
</template>

<style scoped>
.campaign-card {
    background: rgba(17,17,17,0.7);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md, 16px);
    padding: 16px;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}

.campaign-card:hover {
    border-color: rgba(255,255,255,0.12);
    background: rgba(24,24,24,0.9);
}

.card-top { margin-bottom: 12px; }

.card-name-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}

.card-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text);
    line-height: 1.3;
}

.card-chevron {
    color: var(--c-text-3);
    flex-shrink: 0;
    margin-top: 2px;
}

.card-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.badge {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: var(--r-pill, 999px);
}

.card-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 10px 0;
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
    margin-bottom: 10px;
}

.metric {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.metric-label {
    font-family: var(--font-display);
    font-size: 7px;
    letter-spacing: 1.6px;
    color: var(--c-text-3);
    text-transform: uppercase;
}

.metric-value {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    color: var(--c-text);
    font-feature-settings: 'tnum' 1;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.card-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-2);
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--c-border);
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out);
}

.card-btn:hover {
    background: rgba(255,255,255,0.07);
    color: var(--c-text);
}

.card-btn--green {
    color: #34D399;
    background: rgba(16,185,129,0.1);
}

.card-btn--green:hover {
    background: rgba(16,185,129,0.15);
}
</style>
