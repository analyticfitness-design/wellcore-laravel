<script setup>
import { computed, ref } from 'vue';
import WcAdminSparkline from '../../ui/wellcore-admin/WcAdminSparkline.vue';

const props = defineProps({
  mrr: { type: Object, default: () => ({ current: 0, previous: 0, deltaPercent: 0, formattedCurrent: '$0', formattedDelta: '$0' }) },
  spark: { type: Array, default: () => [] },
});

const periods = [
  { id: '7d', label: '7d' },
  { id: '30d', label: '30d' },
  { id: '90d', label: '90d' },
  { id: '12m', label: '12m', active: true },
];
const activePeriod = ref('12m');
function selectPeriod(id) { activePeriod.value = id; }

const monthsLabel = computed(() => {
  const d = new Date();
  const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  const last = months[d.getMonth()];
  const prev = months[(d.getMonth() - 1 + 12) % 12];
  return `${prev} → ${last}`;
});
</script>

<template>
  <div class="chart-card wc-admin-card-mrr">
    <div class="chart-head">
      <div>
        <div class="chart-title">MRR Histórico · {{ monthsLabel }}</div>
        <div class="chart-big">
          {{ mrr.formattedCurrent }}
          <div class="delta-pill up">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 15 12 9 18 15"></polyline></svg>
            +{{ mrr.deltaPercent }}%
          </div>
        </div>
        <div class="chart-sub">vs <b>${{ mrr.previous || 0 }}</b> · mes anterior · <b>+{{ mrr.formattedDelta }}</b></div>
      </div>
      <div class="period-tabs">
        <button
          v-for="p in periods"
          :key="p.id"
          :class="{ active: activePeriod === p.id }"
          @click="selectPeriod(p.id)"
        >{{ p.label }}</button>
      </div>
    </div>

    <div class="chart-body">
      <WcAdminSparkline :data="spark" variant="mrr" color="red" />
    </div>

    <div class="chart-axis">
      <span v-for="(v, i) in spark" :key="i" :class="{ active: i === spark.length - 1 }">
        {{ Math.round(v / 1000) }}K
      </span>
    </div>
  </div>
</template>
