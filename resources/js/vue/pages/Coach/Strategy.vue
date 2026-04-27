<script setup>
import { ref, onMounted } from 'vue';
import { useCoachStrategyStore } from '../../stores/coachStrategy';
import CoachLayout from '../../layouts/CoachLayout.vue';
import StrategyHero from '../../components/coach/strategy/StrategyHero.vue';
import StrategyEmptyState from '../../components/coach/strategy/StrategyEmptyState.vue';
import SectionDivider from '../../components/coach/strategy/SectionDivider.vue';
import BriefSection from '../../components/coach/strategy/BriefSection.vue';
import ReelScriptCard from '../../components/coach/strategy/ReelScriptCard.vue';
import StoriesWeekRow from '../../components/coach/strategy/StoriesWeekRow.vue';
import ProductionChecklistCard from '../../components/coach/strategy/ProductionChecklistCard.vue';
import WeeklyBankCard from '../../components/coach/strategy/WeeklyBankCard.vue';
import HashtagSetCard from '../../components/coach/strategy/HashtagSetCard.vue';
import StrategyHistoryList from '../../components/coach/strategy/StrategyHistoryList.vue';

const store = useCoachStrategyStore();
const tab = ref('this-week');

onMounted(async () => {
    await store.fetchCurrentDrop();
});
</script>

<template>
    <CoachLayout>
        <div class="strategy-page relative min-h-screen">
            <div class="mx-auto max-w-5xl px-6 py-12">

                <nav class="mb-8 flex gap-6 border-b border-wc-border">
                    <button
                        @click="tab = 'this-week'"
                        :class="['py-3 font-mono text-xs uppercase tracking-[0.15em]',
                            tab === 'this-week' ? 'text-wc-text border-b-2 border-wc-accent' : 'text-wc-text-tertiary']"
                    >
                        Esta semana
                    </button>
                    <button
                        @click="async () => { tab = 'history'; if (!store.history.length) await store.fetchHistory(); }"
                        :class="['py-3 font-mono text-xs uppercase tracking-[0.15em]',
                            tab === 'history' ? 'text-wc-text border-b-2 border-wc-accent' : 'text-wc-text-tertiary']"
                    >
                        Historial
                    </button>
                </nav>

                <template v-if="tab === 'this-week'">
                    <template v-if="store.isLoadingDrop">
                        <div class="font-mono text-sm text-wc-text-tertiary animate-pulse py-8">Cargando estrategia...</div>
                    </template>
                    <template v-else-if="!store.currentDrop">
                        <StrategyEmptyState />
                    </template>
                    <template v-else>
                        <StrategyHero :drop="store.currentDrop" />

                        <SectionDivider number="01" title="BRIEF" sub="de la semana" />
                        <BriefSection :brief="store.currentDrop.content.brief" />

                        <SectionDivider number="02" title="REELS" sub="dos guiones de producción" />
                        <ReelScriptCard
                            v-for="reel in store.currentDrop.content.reels"
                            :key="reel.key"
                            :reel="reel"
                            :drop-id="store.currentDrop.id"
                            :piece-state="store.currentDrop.pieces.find(p => p.piece_key === reel.key) ?? null"
                            :drop-assets="store.currentDrop.content?.assets ?? []"
                        />

                        <SectionDivider number="03" title="STORIES" sub="siete piezas Lun → Dom" />
                        <StoriesWeekRow
                            :stories="store.currentDrop.content.stories"
                            :drop-id="store.currentDrop.id"
                            :pieces="store.currentDrop.pieces"
                            :drop-assets="store.currentDrop.content?.assets ?? []"
                        />

                        <SectionDivider number="04" title="CHECKLIST" sub="producción de reel" />
                        <ProductionChecklistCard
                            :checklist="store.currentDrop.content.checklist"
                            :drop-id="store.currentDrop.id"
                            :pieces="store.currentDrop.pieces"
                        />

                        <SectionDivider number="05" title="BANCO SEMANAL" sub="alternativos si la idea principal no encaja" />
                        <WeeklyBankCard :bank="store.currentDrop.content.bank" />

                        <SectionDivider number="06" title="HASHTAGS" sub="sets curados por tema" />
                        <HashtagSetCard :hashtags="store.currentDrop.content.hashtags" />
                    </template>
                </template>

                <StrategyHistoryList v-else />
            </div>
        </div>
    </CoachLayout>
</template>

<style scoped>
.strategy-page { background: #09090B; }
.strategy-page::before {
    content: '';
    position: fixed;
    inset: 0;
    pointer-events: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'><filter id='n'><feTurbulence baseFrequency='0.85' numOctaves='2'/></filter><rect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/></svg>");
    mix-blend-mode: overlay;
    z-index: 1;
}
.strategy-page::after {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 400px;
    pointer-events: none;
    background: radial-gradient(ellipse at center, rgba(220,38,38,0.08), transparent 60%);
    z-index: 0;
}
</style>
