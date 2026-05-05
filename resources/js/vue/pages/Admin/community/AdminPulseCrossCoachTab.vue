<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAdminCommunity } from '../../../composables/useAdminCommunity';
import CoachAnalyticsKPIBar from '../../../components/admin/community/CoachAnalyticsKPIBar.vue';
import CoachAnalyticsTable from '../../../components/admin/community/CoachAnalyticsTable.vue';
import TimeSeriesChart from '../../../components/admin/community/TimeSeriesChart.vue';

const { fetchPulseCrossCoach, loading, error } = useAdminCommunity();
const emit = defineEmits(['drill-down']);
const data = ref(null);
const period = ref('week');
const PERIODS = [
    { key: 'day',   label: 'Día' },
    { key: 'week',  label: 'Semana' },
    { key: 'month', label: 'Mes' },
];

const totals = computed(() => {
    if (!data.value) return {};
    const coaches = data.value.coaches || [];
    return {
        active_communities: coaches.length,
        posts_30d: coaches.reduce((sum, c) => sum + (c.total_posts_count ?? c.posts_count ?? 0), 0),
        engagements_30d: coaches.reduce((sum, c) => sum + (c.reactions_count ?? 0), 0),
    };
});
const coaches = computed(() => data.value?.coaches || []);
const moderationQueueCount = computed(() => data.value?.moderation_queue_count || 0);
const timeSeriesData = computed(() => data.value?.time_series || []);

async function load(force = false) {
    data.value = await fetchPulseCrossCoach({ period: period.value, force });
}

function setPeriod(key) {
    period.value = key;
    load();
}

function onDrillDown(coachId) {
    emit('drill-down', coachId);
}

onMounted(() => load());
</script>

<template>
  <div class="space-y-5">
    <div class="flex items-center gap-2">
      <button v-for="p in PERIODS" :key="p.key" @click="setPeriod(p.key)"
        :class="period === p.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="rounded-full px-4 py-1.5 text-xs font-semibold">{{ p.label }}</button>
      <div class="flex-1"></div>
      <button @click="load(true)" class="text-xs text-wc-text-tertiary hover:text-wc-text">↻ Actualizar</button>
    </div>

    <div v-if="loading && !data" class="space-y-4">
      <div class="h-24 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      <div class="h-48 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
      <div class="h-64 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
    </div>

    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">{{ error }}</div>

    <template v-else-if="data">
      <CoachAnalyticsKPIBar :totals="totals" :pending="moderationQueueCount" />
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
        <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Posts/día último mes</h3>
        <TimeSeriesChart v-if="timeSeriesData.length" :data="timeSeriesData" :height="240" />
      </div>
      <CoachAnalyticsTable :coaches="coaches" @drill-down="onDrillDown" />
    </template>
  </div>
</template>
