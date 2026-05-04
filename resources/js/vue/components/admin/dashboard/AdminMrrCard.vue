<script setup>
import { computed } from 'vue';
import WcAdminSparkline from '../../ui/wellcore-admin/WcAdminSparkline.vue';

const props = defineProps({
  mrr: {
    type: Object,
    default: () => ({ current: 0, previous: 0, deltaPercent: 0, formattedCurrent: '$0', formattedDelta: '$0' }),
  },
  spark: { type: Array, default: () => [] },
});

const monthLabel = computed(() => {
  const d = new Date();
  const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  return months[d.getMonth()];
});

const sparkAxis = computed(() => {
  const months = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
  const d = new Date();
  const i = d.getMonth();
  return [
    { lbl: `${months[(i - 2 + 12) % 12]} · $${formatK(props.spark[0])}` },
    { lbl: months[(i - 1 + 12) % 12] },
    { lbl: `${months[i]} · $${formatK(props.spark[props.spark.length - 1])}`, active: true },
  ];
});

function formatK(n) {
  if (!n) return '0K';
  return Math.round(n / 1000) + 'K';
}
</script>

<template>
  <section class="section">
    <div class="section-h">
      <div class="ttl">MRR · {{ monthLabel }}</div>
      <div class="lnk">Ver ledger →</div>
    </div>
    <div class="mrr-card">
      <div class="mrr-head">
        <div class="lbl">Ingreso recurrente</div>
        <div class="delta-pill">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 15 12 9 18 15"></polyline></svg>
          +{{ mrr.deltaPercent }}%
        </div>
      </div>
      <div class="mrr-num tnum">{{ mrr.formattedCurrent }}</div>
      <div class="mrr-sub">vs <b>${{ mrr.previous || 0 }}</b> · mes anterior · <b>+{{ mrr.formattedDelta }}</b></div>

      <WcAdminSparkline :data="spark" variant="mrr" color="red" />

      <div class="spark-axis">
        <span v-for="(s, i) in sparkAxis" :key="i" :class="{ active: s.active }">{{ s.lbl }}</span>
      </div>
    </div>
  </section>
</template>
