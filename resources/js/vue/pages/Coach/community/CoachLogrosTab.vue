<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachCommunity } from '../../../composables/useCoachCommunity';
import { useToast } from '../../../composables/useToast';
import { useHaptics } from '../../../composables/useHaptics';
import EmptyState from '../../../components/coach/ios/EmptyState.vue';

const { t, locale } = useI18n();

const { fetchAchievements, loading, error } = useCoachCommunity();
const toast = useToast();
const haptics = useHaptics();

const PERIODS = computed(() => [
    { key: 'week',  label: t('coach_inbox.wins_period_week') },
    { key: 'month', label: t('coach_inbox.wins_period_month') },
    { key: 'all',   label: t('coach_inbox.wins_period_all') },
]);
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
        toast.success(t('coach_inbox.wins_congrats_sent', { name: item.client_name }));
        haptics.success();
    } catch (err) {
        toast.apiError(err, t('coach_inbox.wins_congrats_error'));
    }
}

function formatAchievedAt(iso) {
    if (!iso) return '';
    const localeTag = locale.value === 'en' ? 'en-US' : 'es-CO';
    return new Date(iso).toLocaleDateString(localeTag);
}

watch(activePeriod, () => load());
onMounted(() => load());
</script>

<template>
  <div class="anim-entry anim-entry-2 space-y-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button
        v-for="p in PERIODS" :key="p.key"
        @click="activePeriod = p.key"
        :class="activePeriod === p.key ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary'"
        class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold"
      >{{ p.label }}</button>
    </div>

    <div v-if="totals.prs >= 10" class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400 font-semibold">
      {{ totals.prs === 1 ? t('coach_inbox.wins_streak_banner_one', { achievements: totals.achievements }) : t('coach_inbox.wins_streak_banner_other', { prs: totals.prs, achievements: totals.achievements }) }}
    </div>

    <div v-if="loading && !items.length" class="space-y-3">
      <div v-for="i in 4" :key="i" class="h-24 rounded-xl border border-wc-border bg-wc-bg-secondary animate-pulse"></div>
    </div>
    <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center text-sm">{{ error }}</div>
    <EmptyState
      v-else-if="!items.length"
      kind="success"
      :title="t('coach_inbox.wins_empty_title')"
      :subtitle="t('coach_inbox.wins_empty_subtitle')"
    />
    <div v-else class="space-y-3">
      <article v-for="(item, idx) in items" :key="`${item.type}-${item.client_id}-${idx}`" class="rounded-[14px] border border-[var(--b1)] p-4 flex items-start gap-3" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
        <div class="text-3xl">{{ item.type === 'pr' ? '\u{1F3CB}' : '\u{1F3C6}' }}</div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-wc-text">{{ item.client_name }}</p>
          <p class="text-sm text-wc-text-secondary">
            <template v-if="item.type === 'pr'">
              {{ t('coach_inbox.wins_pr_label', { exercise: item.exercise, weight: item.weight_kg }) }}
            </template>
            <template v-else>{{ item.achievement_name }}</template>
          </p>
          <p class="text-xs text-wc-text-tertiary mt-1">{{ formatAchievedAt(item.achieved_at) }}</p>
        </div>
        <button @click="congratulate(item)" class="shrink-0 rounded-full bg-wc-accent/10 text-wc-accent px-3 py-1.5 text-xs font-semibold hover:bg-wc-accent/20">
          {{ t('coach_inbox.wins_congratulate') }}
        </button>
      </article>
    </div>
  </div>
</template>
