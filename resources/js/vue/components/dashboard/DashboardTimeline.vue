<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    data: { type: Object, required: true },
    weekMarkers: { type: Array, default: () => [] },
});

const { t } = useI18n();

const totalWeeks = computed(() => props.data.totalWeeks || 12);
const weeksActive = computed(() => Math.min(props.data.weeksActive || 0, totalWeeks.value));
const progressPct = computed(() => props.data.progressPercent || 0);
const isContinuous = computed(() => weeksActive.value >= totalWeeks.value);
const planMetaLabel = computed(() => t('client_home.timeline_plan_meta', { weeks: totalWeeks.value }));
</script>

<template>
  <section v-if="data.hasActivePlan" class="card section wc-card-dashboard-timeline" :style="{ animationDelay: '300ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">{{ t('client_home.timeline_title') }}</span>
      </div>
      <span class="card-meta">{{ planMetaLabel }}</span>
    </div>
    <div class="timeline">
      <div class="timeline-meta">
        <div class="timeline-week tnum">
          {{ t('client_home.timeline_week') }} <strong>{{ weeksActive }}</strong>
          <span class="of">{{ t('client_home.timeline_of', { total: totalWeeks }) }}</span>
        </div>
        <div class="timeline-pct tnum">{{ progressPct }}%</div>
      </div>
      <div class="timeline-bar">
        <div class="timeline-fill" :style="{ '--pct': progressPct + '%' }"></div>
      </div>
      <div class="timeline-axis">
        <div class="timeline-tick start">
          <span>{{ t('client_home.timeline_start') }}</span>
          <small class="tnum">{{ data.startDate || '--' }}</small>
        </div>
        <div class="timeline-tick end">
          <span>{{ isContinuous ? t('client_home.timeline_continuous') : t('client_home.timeline_week') + ' ' + totalWeeks }}</span>
          <small class="tnum">{{ progressPct }}%</small>
        </div>
      </div>
    </div>
  </section>
</template>
