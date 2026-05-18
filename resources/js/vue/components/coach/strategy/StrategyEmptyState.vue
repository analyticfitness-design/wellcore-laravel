<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';

const { t } = useI18n();
const store = useCoachStrategyStore();
const refreshing = ref(false);

async function refresh() {
    if (refreshing.value) return;
    refreshing.value = true;
    try {
        await store.fetchCurrentDrop();
    } finally {
        refreshing.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-32 text-center">
        <span class="font-mono text-[11px] uppercase tracking-[0.3em] text-wc-text-tertiary">
            {{ t('coach_growth.strategy.empty_eyebrow') }}
        </span>
        <h2 class="mt-6 max-w-2xl font-display text-4xl uppercase tracking-tight text-wc-text">
            {{ t('coach_growth.strategy.empty_title') }}
        </h2>
        <p class="mt-4 max-w-xl font-editorial text-lg italic text-wc-text-secondary">
            {{ t('coach_growth.strategy.empty_subtitle') }}
        </p>
        <div class="mt-10 flex flex-wrap items-center justify-center gap-6">
            <button
                @click="refresh"
                :disabled="refreshing"
                class="inline-flex items-center gap-2 border-b border-wc-accent pb-1 font-mono text-xs uppercase tracking-[0.2em] text-wc-accent transition-opacity hover:opacity-70 disabled:opacity-40"
            >
                <svg class="h-3 w-3" :class="{ 'animate-spin': refreshing }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ refreshing ? t('coach_growth.strategy.empty_refreshing') : t('coach_growth.strategy.empty_refresh') }}
            </button>
            <RouterLink
                :to="{ name: 'coach-strategy-history' }"
                class="inline-flex items-center gap-2 border-b border-wc-border pb-1 font-mono text-xs uppercase tracking-[0.2em] text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-text"
            >
                {{ t('coach_growth.strategy.empty_view_history') }}
                <span aria-hidden="true">→</span>
            </RouterLink>
        </div>
    </div>
</template>
