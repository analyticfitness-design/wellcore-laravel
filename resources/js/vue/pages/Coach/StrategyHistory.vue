<script setup>
import CoachLayout from '../../layouts/CoachLayout.vue';
import StrategyHistoryList from '../../components/coach/strategy/StrategyHistoryList.vue';
</script>

<template>
    <CoachLayout>
        <div class="mx-auto max-w-5xl px-6 py-12">
            <header class="mb-10">
                <span class="font-mono text-[10px] uppercase tracking-[0.3em] text-wc-text-tertiary">
                    WC · ESTRATEGIA / HISTORIAL
                </span>
                <h1 class="mt-3 font-display text-4xl uppercase tracking-tight text-wc-text">
                    HISTORIAL DE DROPS
                </h1>
                <p class="mt-3 max-w-2xl font-editorial text-lg italic text-wc-text-secondary">
                    Tus semanas anteriores de contenido producido y publicado.
                </p>
            </header>

            <div class="anim-entry anim-entry-2">
                <StrategyHistoryList />
            </div>
        </div>
    </CoachLayout>
</template>
