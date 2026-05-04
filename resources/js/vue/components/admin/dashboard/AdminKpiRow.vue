<script setup>
import { computed } from 'vue';
import WcAdminKpi from '../../ui/wellcore-admin/WcAdminKpi.vue';

const props = defineProps({
  pendingTickets:    { type: Number, default: 0 },
  reviewTickets:     { type: Number, default: 0 },
  mrr:               { type: Object, default: () => ({ formattedCurrent: '$0', deltaPercent: 0 }) },
  clientsBreakdown:  { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
  retention:         { type: Object, default: () => ({ percent: 0, delta: '' }) },
  mrrSpark:          { type: Array,  default: () => [] },
});

// Sparkline mini paths estáticos (ilustrativos — variación por KPI). En el target
// estos son trends reales. Los reemplazamos cuando el endpoint exponga histórico.
const sparkPaths = {
  amber: 'M0,18 L20,16 L40,20 L60,15 L80,12 L100,14',
  red:   'M0,22 L20,20 L40,18 L60,12 L80,8  L100,4',
  green: 'M0,18 L20,16 L40,14 L60,10 L80,8  L100,6',
  blue:  'M0,16 L20,14 L40,16 L60,12 L80,10 L100,12',
};

const activosPercent = computed(() => {
  const t = props.clientsBreakdown.total || 1;
  return Math.round(((props.clientsBreakdown.active || 0) / t) * 100);
});
</script>

<template>
  <div class="kpi-row">
    <!-- Tickets pendientes (amber) -->
    <WcAdminKpi
      variant="amber"
      label="Tickets pend."
      :value="pendingTickets"
      :sub="`<b>${reviewTickets}</b> en revisión`"
      :delta="reviewTickets > 0 ? `+${reviewTickets}` : ''"
      delta-variant="warn"
      :spark-path="sparkPaths.amber"
    />

    <!-- MRR (red) -->
    <WcAdminKpi
      variant="red"
      label="MRR"
      :value="mrr.formattedCurrent"
      :sub="`vs mes anterior`"
      :delta="`+${mrr.deltaPercent || 0}%`"
      delta-variant="up"
      :spark-path="sparkPaths.red"
    />

    <!-- Clientes (green) -->
    <WcAdminKpi
      variant="green"
      label="Clientes"
      :value="clientsBreakdown.total || 0"
      :sub="`<b>${clientsBreakdown.active || 0}</b> activos · ${activosPercent}%`"
      :delta="`${activosPercent}%`"
      delta-variant="up"
      :spark-path="sparkPaths.green"
    />

    <!-- Retención (blue) -->
    <WcAdminKpi
      variant="blue"
      label="Retención"
      :value="retention.percent || 0"
      unit="%"
      :sub="retention.delta ? `<b>${retention.delta}</b> vs obj ${retention.target || 85}%` : `obj ${retention.target || 85}%`"
      :delta="retention.delta || ''"
      delta-variant="up"
      :spark-path="sparkPaths.blue"
    />
  </div>
</template>
