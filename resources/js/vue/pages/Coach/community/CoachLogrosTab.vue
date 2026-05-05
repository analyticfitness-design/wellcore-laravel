<script setup>
import { ref, onMounted, watch } from 'vue';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import { useToast } from '../../../composables/useToast';
import { useHaptics } from '../../../composables/useHaptics';

const { fetchAchievements, loading, error } = useCoachCommunity();
const toast = useToast();
const haptics = useHaptics();

const PERIODS = [
    { key: 'week',  label: 'Esta semana' },
    { key: 'month', label: 'Este mes' },
    { key: 'all',   label: 'Histórico' },
];
const activePeriod = ref('week');
const items = ref([]);
const totals = ref({ prs: 0, achievements: 0 });

async function load() {
    const data = await fetchAchievements({ period: activePeriod.value, page: 1, perPage: 30 });
    if (data) {
        items.value = data.data || [];
        totals.value = data.totals || { prs: 0, achievements: 0 };
    }
}

async function congratulate(item) {
    try {
        toast.success(`Felicitación enviada a ${item.client_name}.`);
        haptics.success();
    } catch (err) {
        toast.apiError(err, 'No pudimos enviar la felicitación.');
    }
}

watch(activePeriod, () => load());
onMounted(() => load());
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="p in PERIODS" :key="p.key"
        @click="activePeriod = p.key"
        :class="activePeriod === p.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold"
      >{{ p.label }}</button>
    </div>

    <div v-if="totals.prs >= 10" class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400 font-semibold">
      Equipo en racha — {{ totals.prs }} PRs y {{ totals.achievements }} logros este período
    </div>

    <div v-if="loading && !items.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-24 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <div v-else-if="!items.length" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
      <p class="font-display text-lg text-wc-text">Aún no hay logros</p>
      <p class="text-sm text-wc-text-tertiary mt-2">Sé proactivo: motiva al cliente que esté cerca de un PR.</p>
    </div>
    <div v-else class="space-y-3">
      <article v-for="(item, idx) in items" :key="`${item.type}-${item.client_id}-${idx}`" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 flex items-start gap-3">
        <div class="text-3xl">{{ item.type === 'pr' ? '\u{1F3CB}' : '\u{1F3C6}' }}</div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-wc-text">{{ item.client_name }}</p>
          <p class="text-sm text-wc-text-secondary">
            <template v-if="item.type === 'pr'">
              PR de <strong>{{ item.exercise }}</strong>: {{ item.weight_kg }}kg
            </template>
            <template v-else>{{ item.achievement_name }}</template>
          </p>
          <p class="text-xs text-wc-text-tertiary mt-1">{{ new Date(item.achieved_at).toLocaleDateString('es-CO') }}</p>
        </div>
        <button @click="congratulate(item)" class="shrink-0 rounded-full bg-wc-accent/10 text-wc-accent px-3 py-1.5 text-xs font-semibold hover:bg-wc-accent/20">
          Felicitar
        </button>
      </article>
    </div>
  </div>
</template>
