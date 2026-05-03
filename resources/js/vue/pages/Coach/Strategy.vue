<script setup>
import { ref, onMounted, onActivated } from 'vue';
import { useCoachStrategyStore } from '../../stores/coachStrategy';
import CoachLayout from '../../layouts/CoachLayout.vue';
import StrategyHero from '../../components/coach/strategy/StrategyHero.vue';
import AssetGallery from '../../components/coach/strategy/AssetGallery.vue';
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

// Re-fetch si el componente vuelve activo desde keep-alive (e.g. al navegar
// con el bottom-nav y volver a la tab de estrategia)
onActivated(async () => {
    await store.fetchCurrentDrop();
});
</script>

<template>
    <CoachLayout>
        <div class="strategy-page-v2 page-wrap">
            <div class="container">

                <nav class="tabs">
                    <button
                        class="tab"
                        :class="{ active: tab === 'this-week' }"
                        @click="tab = 'this-week'"
                    >
                        Esta semana
                    </button>
                    <button
                        class="tab"
                        :class="{ active: tab === 'history' }"
                        @click="async () => { tab = 'history'; if (!store.history.length) await store.fetchHistory(); }"
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

                        <AssetGallery
                            :drop-id="store.currentDrop.id"
                            :assets="store.currentDrop.content?.assets ?? []"
                        />

                        <SectionDivider number="01" title="BRIEF" sub="de la semana" icon="amber" />
                        <BriefSection :brief="store.currentDrop.content.brief" />

                        <SectionDivider number="02" title="REELS" sub="dos guiones de producción" icon="red" />
                        <ReelScriptCard
                            v-for="reel in store.currentDrop.content.reels"
                            :key="reel.key"
                            :reel="reel"
                            :drop-id="store.currentDrop.id"
                            :piece-state="store.currentDrop.pieces.find(p => p.piece_key === reel.key) ?? null"
                            :drop-assets="store.currentDrop.content?.assets ?? []"
                        />

                        <SectionDivider number="03" title="STORIES" sub="siete piezas Lun → Dom" icon="sky" />
                        <StoriesWeekRow
                            :stories="store.currentDrop.content.stories"
                            :drop-id="store.currentDrop.id"
                            :pieces="store.currentDrop.pieces"
                            :drop-assets="store.currentDrop.content?.assets ?? []"
                        />

                        <SectionDivider number="04" title="CHECKLIST" sub="producción de reel" icon="emerald" />
                        <ProductionChecklistCard
                            :checklist="store.currentDrop.content.checklist"
                            :drop-id="store.currentDrop.id"
                            :pieces="store.currentDrop.pieces"
                        />

                        <SectionDivider number="05" title="BANCO SEMANAL" sub="alternativos si la idea principal no encaja" icon="orange" />
                        <WeeklyBankCard :bank="store.currentDrop.content.bank" />

                        <SectionDivider number="06" title="HASHTAGS" sub="sets curados por tema" icon="emerald" />
                        <HashtagSetCard :hashtags="store.currentDrop.content.hashtags" />
                    </template>
                </template>

                <StrategyHistoryList v-else />
            </div>
        </div>
    </CoachLayout>
</template>
