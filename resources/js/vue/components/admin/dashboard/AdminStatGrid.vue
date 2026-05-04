<script setup>
import { computed } from 'vue';
import WcAdminProgressBar from '../../ui/wellcore-admin/WcAdminProgressBar.vue';

const props = defineProps({
  clientsBreakdown: { type: Object, default: () => ({ total: 0, active: 0, inactive: 0 }) },
  retention: { type: Object, default: () => ({ percent: 0, target: 85, delta: '' }) },
});

const activosPercent = computed(() => {
  const t = props.clientsBreakdown.total || 1;
  return Math.round(((props.clientsBreakdown.active || 0) / t) * 100);
});
</script>

<template>
  <section class="stat-grid">
    <!-- Card 1: Clientes activos -->
    <div class="stat-card">
      <div class="lbl">Clientes activos</div>
      <div class="stat-row">
        <span class="stat-num green tnum">{{ clientsBreakdown.active || 0 }}</span>
        <span class="stat-frac tnum">/ {{ clientsBreakdown.total || 0 }}</span>
        <span class="stat-pct tnum">{{ activosPercent }}%</span>
      </div>
      <WcAdminProgressBar :percent="activosPercent" />
      <div class="stat-foot">
        <span class="item"><span class="dot g"></span>Activo {{ clientsBreakdown.active || 0 }}</span>
        <span class="item"><span class="dot r"></span>Inact. {{ clientsBreakdown.inactive || 0 }}</span>
      </div>
    </div>

    <!-- Card 2: Retención -->
    <div class="stat-card">
      <div class="lbl">Retención · mes</div>
      <div class="stat-row">
        <span class="stat-num green tnum">{{ retention.percent || 0 }}<span style="font-size:18px">%</span></span>
        <span class="stat-pct tnum" style="color:var(--wc-text-3); margin-left:auto">obj {{ retention.target || 85 }}%</span>
      </div>
      <WcAdminProgressBar :percent="retention.percent || 0" />
      <div class="stat-foot">
        <span v-if="retention.delta" class="item"><span class="dot g"></span>{{ retention.delta }}</span>
        <span v-else class="item"></span>
        <span class="item"><span class="dot r"></span></span>
      </div>
    </div>
  </section>
</template>
