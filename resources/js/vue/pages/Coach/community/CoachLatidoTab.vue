<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachPulse } from '../../../composables/useCoachPulse';
import TeamHealthRing from '../../../components/community/TeamHealthRing.vue';
import TopPerformerCard from '../../../components/community/TopPerformerCard.vue';
import AtRiskClientChip from '../../../components/community/AtRiskClientChip.vue';
import PushPermissionBanner from '../../../components/community/PushPermissionBanner.vue';
import EmptyState from '../../../components/coach/ios/EmptyState.vue';

const { t, locale } = useI18n();
const { summary, loading, error, fetchSummary } = useCoachPulse();
const ringRef = ref(null);
const refreshIntervalId = ref(null);

const computedAtFormatted = computed(() => {
    if (!summary.value?.computed_at) return '';
    const d = new Date(summary.value.computed_at);
    const localeTag = locale.value === 'en' ? 'en-US' : 'es-CO';
    return d.toLocaleTimeString(localeTag, { hour: '2-digit', minute: '2-digit' });
});

const isEmpty = computed(() => {
    if (!summary.value) return false;
    return (!summary.value.top_performers?.length
        && !summary.value.at_risk_clients?.length
        && (summary.value.team_health_score ?? 0) === 0);
});

async function refresh() {
    await fetchSummary({ force: true });
}

function flashHealthScore() {
    ringRef.value?.flashHealthScore();
}
defineExpose({ flashHealthScore });

function emitQuickMessage(client) {
    window.dispatchEvent(new CustomEvent('coach-community:quick-message', { detail: client }));
}

onMounted(async () => {
    await fetchSummary();
    refreshIntervalId.value = setInterval(() => {
        if (document.visibilityState === 'visible') fetchSummary();
    }, 90_000);
});

onBeforeUnmount(() => {
    if (refreshIntervalId.value) clearInterval(refreshIntervalId.value);
});
</script>

<template>
  <div class="anim-entry anim-entry-2 space-y-6">
    <PushPermissionBanner />

    <div v-if="loading && !summary" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="h-80 rounded-[14px] border border-[var(--b1)] p-6 flex items-center justify-center" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
        <div class="h-48 w-48 rounded-full bg-wc-bg-tertiary animate-pulse"></div>
      </div>
      <div class="space-y-3">
        <div v-for="i in 3" :key="i" class="h-16 rounded-xl border border-wc-border bg-wc-bg-tertiary animate-pulse"></div>
      </div>
    </div>

    <div v-else-if="error && !summary" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">
      <p class="text-wc-text">{{ error }}</p>
      <button @click="refresh" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white">
        ↻ {{ t('coach_inbox.pulse_retry') }}
      </button>
    </div>

    <EmptyState
      v-else-if="isEmpty"
      kind="activity"
      :title="t('coach_inbox.pulse_empty_title')"
      :subtitle="t('coach_inbox.pulse_empty_subtitle')"
    />

    <template v-else-if="summary">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-[14px] border border-[var(--b1)] p-6 flex flex-col items-center justify-center" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
          <TeamHealthRing ref="ringRef" :score="summary.team_health_score" :size="220" :label="t('coach_inbox.pulse_team_ring_label')" />
          <p class="text-xs text-wc-text-tertiary mt-4">
            {{ t('coach_inbox.pulse_computed_at', { time: computedAtFormatted }) }}
          </p>
        </div>

        <div class="space-y-3">
          <h3 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ t('coach_inbox.pulse_top_performers_title') }}</h3>
          <TopPerformerCard v-for="(p, i) in summary.top_performers" :key="p.client_id" :performer="p" :rank="i + 1" />
          <p v-if="!summary.top_performers?.length" class="text-sm text-wc-text-tertiary px-3">{{ t('coach_inbox.pulse_no_top_performers') }}</p>
        </div>
      </div>

      <div v-if="summary.at_risk_clients?.length" class="rounded-[14px] border border-[var(--b1)] p-5" style="background: var(--s2); box-shadow: var(--shadow-card-ios);">
        <h3 class="text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary mb-3">
          {{ t('coach_inbox.pulse_at_risk_title') }}
        </h3>
        <div class="space-y-2">
          <AtRiskClientChip
            v-for="c in summary.at_risk_clients"
            :key="c.id || c.client_id"
            :client="c"
            @quick-message="emitQuickMessage"
          />
        </div>
      </div>

      <div class="flex items-center justify-end">
        <button @click="refresh" class="text-xs font-semibold text-wc-text-tertiary hover:text-wc-text inline-flex items-center gap-1.5">
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992h4.992m0 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          {{ t('coach_inbox.pulse_refresh_now') }}
        </button>
      </div>
    </template>
  </div>
</template>
