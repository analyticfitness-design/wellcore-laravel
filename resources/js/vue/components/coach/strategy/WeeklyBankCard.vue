<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    bank: { type: Object, required: true },
});

// Items por columna — code prefix mapping
const COLS = [
    { type: 'h', key: 'alt_hooks', titleKey: 'bank_col_hooks', icon: 'orange' },
    { type: 'c', key: 'alt_ctas', titleKey: 'bank_col_ctas', icon: 'emerald' },
    { type: 'k', key: 'alt_captions', titleKey: 'bank_col_captions', icon: 'sky' },
];

const taken = ref({}); // key: `${type}-${idx}` -> true

// Pad a 2 dígitos
function pad2(n) {
    return String(n).padStart(2, '0');
}

function codeFor(type, idx) {
    const prefix = type === 'h' ? 'H' : type === 'c' ? 'C' : 'K';
    return `${prefix}-${pad2(idx + 1)}`;
}

// Track active timeouts so onBeforeUnmount puede limpiarlos
const timers = new Map(); // key -> timeoutId

async function copyText(text, type, idx) {
    try {
        await navigator.clipboard.writeText(text);
    } catch {}

    const key = `${type}-${idx}`;
    taken.value = { ...taken.value, [key]: true };

    // Limpia timeout previo si existía
    if (timers.has(key)) clearTimeout(timers.get(key));
    const tid = setTimeout(() => {
        const next = { ...taken.value };
        delete next[key];
        taken.value = next;
        timers.delete(key);
    }, 2500);
    timers.set(key, tid);
}

const itemsByCol = computed(() => COLS.map(col => ({
    ...col,
    title: t(`coach_growth.strategy.${col.titleKey}`),
    items: props.bank?.[col.key] ?? [],
})));

const hasAnyItems = computed(() => itemsByCol.value.some(c => c.items.length > 0));

onBeforeUnmount(() => {
    for (const tid of timers.values()) clearTimeout(tid);
    timers.clear();
});
</script>

<template>
    <div v-if="!hasAnyItems" class="card">
        <p class="banco-empty" style="text-align:center;color:var(--ink-3,#888);font-family:var(--font-mono,monospace);font-size:.85rem;padding:2rem 0;">
            {{ t('coach_growth.strategy.bank_empty_long') }}
        </p>
    </div>

    <div v-else class="banco-grid">
        <div v-for="col in itemsByCol" :key="col.type" class="banco-col">
            <div class="banco-head">
                <span :class="['ic', `ic-${col.icon}`, 'ic-sm']"></span>
                <span class="banco-head-title">{{ col.title }}</span>
            </div>
            <div class="banco-items-wrap">
                <template v-if="col.items.length">
                    <div
                        v-for="(text, idx) in col.items"
                        :key="idx"
                        class="banco-item"
                        :class="{ taken: taken[`${col.type}-${idx}`] }"
                    >
                        <span class="banco-code">{{ codeFor(col.type, idx) }}</span>
                        <p class="banco-text">{{ text }}</p>
                        <button
                            type="button"
                            class="banco-copy-btn"
                            :title="t('coach_growth.strategy.bank_copy_title', { code: codeFor(col.type, idx) })"
                            @click="copyText(text, col.type, idx)"
                        >
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185"
                                />
                            </svg>
                        </button>
                        <div class="banco-taken-overlay">{{ t('coach_growth.strategy.bank_taken') }}</div>
                    </div>
                </template>
                <p v-else class="banco-empty" style="font-size:.78rem;color:var(--ink-3,#888);font-family:var(--font-mono,monospace);padding:.5rem 0;">
                    {{ t('coach_growth.strategy.bank_empty_short') }}
                </p>
            </div>
        </div>
    </div>
</template>
