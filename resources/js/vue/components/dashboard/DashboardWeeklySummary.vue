<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    data: { type: Object, required: true },
    weeklySummaryMessage: { type: Object, default: () => ({ label: '', desc: '', colorClass: '' }) },
});

const { t } = useI18n();

function getIsoWeek(date) {
    const target = new Date(date.valueOf());
    const dayNr = (date.getDay() + 6) % 7;
    target.setDate(target.getDate() - dayNr + 3);
    const firstThursday = target.valueOf();
    target.setMonth(0, 1);
    if (target.getDay() !== 4) {
        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
    }
    return 1 + Math.ceil((firstThursday - target) / 604800000);
}

const lastWeekIso = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() - 7);
    return getIsoWeek(d);
});
</script>

<template>
  <section class="card section wc-card-dashboard-summary" :style="{ animationDelay: '520ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.weekly_summary_title') }}</span>
      </div>
      <span class="card-meta">{{ t('client_home.weekly_summary_meta', { week: lastWeekIso }) }}</span>
    </div>

    <div v-if="data.hasLastWeekData" class="summary">
      <div class="summary-numbers">
        <div class="summary-num">
          <div class="k">{{ t('client_home.weekly_summary_workouts') }}</div>
          <div class="v tnum">{{ data.lastWeekWorkouts || 0 }}</div>
        </div>
        <div class="summary-num">
          <div class="k">{{ t('client_home.weekly_summary_checkins') }}</div>
          <div class="v tnum">{{ data.lastWeekCheckins || 0 }}</div>
        </div>
      </div>
      <div class="summary-status">
        <span class="badge">{{ weeklySummaryMessage.label || t('client_home.weekly_summary_default_badge') }}</span>
        <span class="msg">{{ weeklySummaryMessage.desc || t('client_home.weekly_summary_default_msg') }}</span>
      </div>
    </div>

    <div v-else class="summary">
      <div class="summary-status">
        <span class="badge">{{ t('client_home.weekly_summary_new_week') }}</span>
        <span class="msg">{{ t('client_home.weekly_summary_new_week_desc') }}</span>
      </div>
    </div>
  </section>
</template>
