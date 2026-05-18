<script setup>
import { useI18n } from 'vue-i18n';
import CoachLayout from '../../layouts/CoachLayout.vue';
import StrategyHistoryList from '../../components/coach/strategy/StrategyHistoryList.vue';

const { t } = useI18n();
</script>

<template>
    <CoachLayout>
        <div class="mx-auto max-w-5xl px-6 py-12">
            <header class="mb-10">
                <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-wc-text-tertiary">
                    {{ t('coach_growth.history.page_eyebrow') }}
                </span>
                <h1 class="mt-3 font-display text-4xl uppercase tracking-tight text-wc-text">
                    {{ t('coach_growth.history.page_title') }}
                </h1>
                <p class="mt-3 max-w-2xl font-editorial text-lg italic text-wc-text-secondary">
                    {{ t('coach_growth.history.page_subtitle') }}
                </p>
            </header>

            <div class="anim-entry anim-entry-2">
                <StrategyHistoryList />
            </div>
        </div>
    </CoachLayout>
</template>
