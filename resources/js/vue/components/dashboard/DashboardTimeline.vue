<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Object, required: true },
    weekMarkers: { type: Array, default: () => [] },
});

const totalWeeks = computed(() => props.data.totalWeeks || 12);
const weeksActive = computed(() => Math.min(props.data.weeksActive || 0, totalWeeks.value));
const progressPct = computed(() => props.data.progressPercent || 0);
const isContinuous = computed(() => weeksActive.value >= totalWeeks.value);
const planMetaLabel = computed(() => `Plan ${totalWeeks.value} semanas`);
</script>

<template>
  <section v-if="data.hasActivePlan" class="card section dash-card-timeline" :style="{ animationDelay: '300ms' }">
    <div class="card-head">
      <div class="card-head-left">
        <span class="card-title">Tu progreso</span>
      </div>
      <span class="card-meta">{{ planMetaLabel }}</span>
    </div>
    <div class="timeline">
      <div class="timeline-meta">
        <div class="timeline-week tnum">
          Semana <strong>{{ weeksActive }}</strong>
          <span class="of">de {{ totalWeeks }}</span>
        </div>
        <div class="timeline-pct tnum">{{ progressPct }}%</div>
      </div>
      <div class="timeline-bar">
        <div class="timeline-fill" :style="{ '--pct': progressPct + '%' }"></div>
      </div>
      <div class="timeline-axis">
        <div class="timeline-tick start">
          <span>Inicio</span>
          <small class="tnum">{{ data.startDate || '--' }}</small>
        </div>
        <div class="timeline-tick end">
          <span>{{ isContinuous ? 'Continuo' : 'Semana ' + totalWeeks }}</span>
          <small class="tnum">{{ progressPct }}%</small>
        </div>
      </div>
    </div>
  </section>
</template>
