<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';

const { t } = useI18n();
const api = useApi();
const loading = ref(true);
const dateRange = ref('month');

const coachScore = ref(0);
const coachScoreLabel = ref('');
const avgResponseHours = ref(0);
const checkinReplyRate = ref(0);
const retentionRate = ref(0);
const avgBienestar = ref(0);
const totalCheckins = ref(0);
const totalMessages = ref(0);

const clientOverview = ref([]);
const slaBreakdown = ref({ within24h: 0, within48h: 0, over48h: 0 });
const bienestarTrend = ref([]);
const revenueStats = ref({ total: 0, monthly: 0, clients_paying: 0 });

async function loadAnalytics() {
    loading.value = true;
    try {
        const { data } = await api.get(`/api/v/coach/analytics?range=${dateRange.value}`);
        checkinReplyRate.value = data.checkinReplyRate || 0;
        retentionRate.value = data.retentionRate || 0;
        avgBienestar.value = data.avgBienestar || 0;
        totalCheckins.value = data.totalCheckins || 0;
        totalMessages.value = (data.messagesSent || 0) + (data.messagesReceived || 0);
        clientOverview.value = data.clientOverview || [];
        slaBreakdown.value = data.slaBreakdown || slaBreakdown.value;
        bienestarTrend.value = data.bienestarTrend || [];

        revenueStats.value = data.revenueStats || {
            total: data.totalRevenue || 0,
            monthly: 0,
            clients_paying: data.activeClients || 0,
        };

        if (!data.coachScore && (checkinReplyRate.value > 0 || retentionRate.value > 0)) {
            const scoreCalc = Math.round(
                checkinReplyRate.value * 0.5 + retentionRate.value * 0.5
            );
            coachScore.value = scoreCalc;
            coachScoreLabel.value = scoreCalc >= 75 ? t('coach_ops.analytics_score_label_excellent') : scoreCalc >= 50 ? t('coach_ops.analytics_score_label_regular') : t('coach_ops.analytics_score_label_needs_improvement');
            avgResponseHours.value = data.avgResponseHours || 0;
        } else {
            coachScore.value = data.coachScore || 0;
            coachScoreLabel.value = data.coachScoreLabel || '';
            avgResponseHours.value = data.avgResponseHours || 0;
        }
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

function switchRange(range) {
    dateRange.value = range;
    loadAnalytics();
}

onMounted(loadAnalytics);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader :contextLabel="t('coach_ops.analytics_context_label')" :title="t('coach_ops.analytics_title')" :subtitle="t('coach_ops.analytics_subtitle')">
        <template #actions>
          <div class="flex items-center gap-1 rounded-button border border-wc-border bg-wc-bg-secondary p-1">
            <button
              v-for="r in [{ key: 'month', label: t('coach_ops.analytics_range_month') }, { key: 'quarter', label: t('coach_ops.analytics_range_quarter') }, { key: 'year', label: t('coach_ops.analytics_range_year') }, { key: 'all', label: t('coach_ops.analytics_range_all') }]"
              :key="r.key"
              @click="switchRange(r.key)"
              class="rounded-button px-3 py-1.5 text-xs font-medium transition-colors"
              :class="dateRange === r.key ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
            >{{ r.label }}</button>
          </div>
        </template>
      </WcPageHeader>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center gap-2 text-sm text-wc-text-tertiary">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ t('coach_ops.analytics_loading') }}
      </div>

      <template v-else>

        <!-- Empty state para coach sin datos -->
        <div v-if="!checkinReplyRate && !retentionRate && !totalCheckins" class="rounded-card border border-wc-border bg-wc-bg-tertiary p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
          </svg>
          <p class="mt-3 font-display text-sm uppercase tracking-wide text-wc-text">{{ t('coach_ops.analytics_empty_title') }}</p>
          <p class="mt-1 text-xs text-wc-text-tertiary">{{ t('coach_ops.analytics_empty_subtitle') }}</p>
        </div>

        <!-- Coach Score Hero -->
        <div v-if="coachScore > 0" class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
              <div class="relative flex h-20 w-20 shrink-0 items-center justify-center">
                <svg class="h-20 w-20 -rotate-90" viewBox="0 0 80 80">
                  <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="6" class="text-wc-border" />
                  <circle cx="40" cy="40" r="34" fill="none" stroke-width="6"
                    :stroke-dasharray="`${coachScore * 2.136} 213.6`"
                    stroke-linecap="round"
                    :class="coachScore >= 75 ? 'text-wc-text/40' : coachScore >= 50 ? 'text-wc-accent/60' : 'text-wc-accent'" />
                </svg>
                <span class="absolute font-data text-xl font-bold text-wc-text">{{ coachScore }}</span>
              </div>
              <div>
                <h2 class="font-display text-lg uppercase tracking-wide text-wc-text">{{ t('coach_ops.analytics_coach_score') }}</h2>
                <p class="text-sm font-semibold" :class="coachScore >= 75 ? 'text-wc-text' : coachScore >= 50 ? 'text-wc-accent/60' : 'text-wc-accent'">{{ coachScoreLabel }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ t('coach_ops.analytics_coach_score_subtitle') }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs sm:grid-cols-3">
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_response') }}</span>
                <span class="font-data font-semibold" :class="avgResponseHours <= 24 ? 'text-wc-text' : avgResponseHours <= 48 ? 'text-wc-accent/60' : 'text-wc-accent'">{{ t('coach_ops.analytics_metric_response_value', { hours: avgResponseHours }) }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_reply_rate') }}</span>
                <span class="font-data font-semibold text-wc-text">{{ checkinReplyRate }}%</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_retention') }}</span>
                <span class="font-data font-semibold text-wc-text">{{ retentionRate }}%</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_wellbeing') }}</span>
                <span class="font-data font-semibold text-wc-text">{{ t('coach_ops.analytics_metric_wellbeing_value', { value: avgBienestar }) }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_checkins') }}</span>
                <span class="font-data font-semibold text-wc-text">{{ totalCheckins }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-wc-text-tertiary">{{ t('coach_ops.analytics_metric_messages') }}</span>
                <span class="font-data font-semibold text-wc-text">{{ totalMessages }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- SLA + Revenue -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <!-- SLA Breakdown -->
          <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary mb-4">{{ t('coach_ops.analytics_sla_title') }}</p>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">{{ t('coach_ops.analytics_sla_within_24h') }}</span>
                <span class="font-data text-sm font-semibold text-wc-text">{{ slaBreakdown.within24h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-wc-text/40" :style="{ width: slaBreakdown.within24h + '%' }"></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">{{ t('coach_ops.analytics_sla_24_48h') }}</span>
                <span class="font-data text-sm font-semibold text-wc-accent/60">{{ slaBreakdown.within48h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-wc-accent/60" :style="{ width: slaBreakdown.within48h + '%' }"></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">{{ t('coach_ops.analytics_sla_over_48h') }}</span>
                <span class="font-data text-sm font-semibold text-wc-accent">{{ slaBreakdown.over48h }}%</span>
              </div>
              <div class="h-2 w-full rounded-full bg-wc-bg-secondary">
                <div class="h-2 rounded-full bg-wc-accent" :style="{ width: slaBreakdown.over48h + '%' }"></div>
              </div>
            </div>
          </div>

          <!-- Revenue -->
          <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary mb-4">{{ t('coach_ops.analytics_revenue_title') }}</p>
            <div class="grid grid-cols-3 gap-4">
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-wc-text">${{ revenueStats.total.toLocaleString() }}</p>
                <p class="text-[10px] text-wc-text-tertiary">{{ t('coach_ops.analytics_revenue_total') }}</p>
              </div>
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-wc-text">${{ revenueStats.monthly.toLocaleString() }}</p>
                <p class="text-[10px] text-wc-text-tertiary">{{ t('coach_ops.analytics_revenue_monthly') }}</p>
              </div>
              <div class="text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ revenueStats.clients_paying }}</p>
                <p class="text-[10px] text-wc-text-tertiary">{{ t('coach_ops.analytics_revenue_active_clients') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Client Overview Table -->
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="px-5 py-3 border-b border-wc-border">
            <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">{{ t('coach_ops.analytics_overview_title') }}</p>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                  <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_ops.analytics_overview_col_client') }}</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_ops.analytics_overview_col_wellbeing') }}</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_ops.analytics_overview_col_checkins') }}</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ t('coach_ops.analytics_overview_col_adherence') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr v-for="c in clientOverview" :key="c.id" class="hover:bg-wc-bg-secondary/30">
                  <td class="px-4 py-2.5 font-medium text-wc-text">{{ c.name }}</td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="font-data font-semibold" :class="(c.bienestar || 0) >= 7 ? 'text-wc-text' : (c.bienestar || 0) >= 4 ? 'text-wc-accent/60' : 'text-wc-accent'">{{ c.bienestar || t('coach_ops.analytics_overview_no_value') }}</span>
                  </td>
                  <td class="px-4 py-2.5 text-center font-data text-wc-text">{{ c.checkins || 0 }}</td>
                  <td class="px-4 py-2.5 text-center font-data text-wc-text">{{ c.adherence || 0 }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="clientOverview.length === 0" class="py-8 text-center text-sm text-wc-text-tertiary">{{ t('coach_ops.analytics_overview_empty') }}</div>
        </div>

      </template>
    </div>
  </CoachLayout>
</template>
