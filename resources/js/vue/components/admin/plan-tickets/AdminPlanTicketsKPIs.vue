<script setup>
import { computed } from 'vue';

const props = defineProps({
    kpis: { type: Array, required: true },
});

const RING_RADIUS = 22;
const RING_STROKE = 4;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;

const cards = computed(() => props.kpis ?? []);

function strokeOffset(pct) {
    const v = Math.max(0, Math.min(100, Number(pct ?? 0)));
    return RING_CIRCUMFERENCE - (RING_CIRCUMFERENCE * v / 100);
}

function ringClass(variant) {
    return {
        'ring-fill--red': variant === 'urgent',
        'ring-fill--amber': variant === 'warn',
        'ring-fill--green': variant === 'healthy',
        'ring-fill--blue': variant === 'info',
    };
}
</script>

<template>
    <div class="ptickets-kpis" role="list">
        <div
            v-for="m in cards"
            :key="m.id"
            class="kpi-card"
            :class="`kpi-card--${m.variant}`"
            role="listitem"
        >
            <div class="kpi-ring-wrap" aria-hidden="true">
                <svg width="56" height="56" class="kpi-ring">
                    <circle class="ring-track" cx="28" cy="28" :r="RING_RADIUS" :stroke-width="RING_STROKE" />
                    <circle
                        class="ring-fill"
                        :class="ringClass(m.variant)"
                        cx="28" cy="28"
                        :r="RING_RADIUS"
                        :stroke-width="RING_STROKE"
                        :stroke-dasharray="RING_CIRCUMFERENCE"
                        :stroke-dashoffset="strokeOffset(m.ringPct)"
                    />
                </svg>
            </div>
            <span class="kpi-label">{{ m.label }}</span>
            <span class="kpi-value" :class="`kpi-value--${m.variant}`">{{ m.value }}</span>
            <span v-if="m.sub" class="kpi-sub">{{ m.sub }}</span>
        </div>
    </div>
</template>

<style scoped>
.ptickets-kpis {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    overflow-y: visible;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
}
.ptickets-kpis::-webkit-scrollbar { display: none; }

@media (min-width: 1024px) {
    .ptickets-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        overflow: visible;
        gap: 12px;
        padding-bottom: 0;
    }
}

.kpi-card {
    flex: 0 0 220px;
    border-radius: 14px;
    padding: 16px 14px 14px;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    min-height: 124px;
    min-width: 0;
    scroll-snap-align: start;
    transition: transform 0.2s var(--ease-out, ease), box-shadow 0.2s var(--ease-out, ease);
}
@media (min-width: 1024px) { .kpi-card { flex: none; } }

.kpi-card--urgent  { background: rgba(220, 38, 38, 0.07); border: 1px solid rgba(220, 38, 38, 0.22); }
.kpi-card--warn    { background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.20); }
.kpi-card--healthy { background: rgba(16, 185, 129, 0.07); border: 1px solid rgba(16, 185, 129, 0.20); }
.kpi-card--info    { background: rgba(59, 130, 246, 0.07); border: 1px solid rgba(59, 130, 246, 0.20); }

.kpi-ring-wrap { position: absolute; top: 12px; right: 12px; }
.kpi-ring { transform: rotate(-90deg); }
.ring-track { fill: none; stroke: rgba(255, 255, 255, 0.06); }
.ring-fill {
    fill: none;
    stroke-linecap: round;
    transition: stroke-dashoffset 1.2s var(--ease-out, ease);
}
.ring-fill--red    { stroke: var(--color-wc-red-text, #F87171); }
.ring-fill--amber  { stroke: var(--color-wc-amber-text, #FCD34D); }
.ring-fill--green  { stroke: var(--color-wc-green-text, #34D399); }
.ring-fill--blue   { stroke: var(--color-wc-blue-text, #60A5FA); }

.kpi-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 8px;
    padding-right: 60px;
    line-height: 1.4;
    min-height: 12px;
}
.kpi-value {
    font-family: var(--font-display);
    font-size: 32px;
    letter-spacing: 0.03em;
    line-height: 1;
    margin-bottom: 4px;
}
.kpi-value--urgent  { color: var(--color-wc-red-text, #F87171); }
.kpi-value--warn    { color: var(--color-wc-amber-text, #FCD34D); }
.kpi-value--healthy { color: var(--color-wc-green-text, #34D399); }
.kpi-value--info    { color: var(--color-wc-text); }
.kpi-sub {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (min-width: 1024px) {
    .kpi-card { min-height: 120px; padding: 18px 16px 14px; }
    .kpi-value { font-size: 36px; }
}

@media (prefers-reduced-motion: reduce) {
    .ring-fill { transition: none !important; }
    .kpi-card { transition: none !important; }
}
</style>
