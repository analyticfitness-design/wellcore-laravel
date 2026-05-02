<script setup>
import { computed } from 'vue';
import { formatNumber } from '../../../composables/useFormat';

const props = defineProps({
    funnel: {
        type: Array,
        default: () => [],
    },
});

const steps = computed(() => {
    const top = props.funnel[0]?.value || 1;
    return props.funnel.map((step, i) => {
        const pct = i === 0 ? 100 : Math.round((step.value / top) * 100);
        const convPct = i > 0 && props.funnel[i - 1]?.value > 0
            ? ((step.value / props.funnel[i - 1].value) * 100).toFixed(1)
            : null;
        return { ...step, pct, convPct };
    });
});

const STEP_COLORS = [
    'var(--color-wc-blue-text)',
    'var(--color-wc-accent)',
    'var(--color-wc-amber-text)',
    'var(--color-wc-green-text)',
];
</script>

<template>
    <div class="funnel-wrap">
        <div v-for="(step, i) in steps" :key="step.label" class="funnel-step">
            <div class="funnel-meta">
                <span class="funnel-label">{{ step.label }}</span>
                <div class="funnel-nums">
                    <span class="funnel-value" :style="{ color: STEP_COLORS[i] }">
                        {{ formatNumber(step.value) }}
                    </span>
                    <span v-if="step.convPct" class="funnel-conv">
                        {{ step.convPct }}% conv.
                    </span>
                </div>
            </div>
            <div class="funnel-track">
                <div
                    class="funnel-bar"
                    :style="{
                        width: step.pct + '%',
                        background: STEP_COLORS[i],
                    }"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.funnel-wrap {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.funnel-step {}

.funnel-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.funnel-label {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

.funnel-nums {
    display: flex;
    align-items: center;
    gap: 8px;
}

.funnel-value {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 700;
    font-feature-settings: 'tnum' 1;
}

.funnel-conv {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.0px;
    color: var(--c-text-3);
}

.funnel-track {
    height: 6px;
    background: rgba(255,255,255,0.05);
    border-radius: 3px;
    overflow: hidden;
}

.funnel-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s cubic-bezier(.22,1,.36,1);
    opacity: 0.85;
}

@media (prefers-reduced-motion: reduce) {
    .funnel-bar { transition: none !important; }
}
</style>
