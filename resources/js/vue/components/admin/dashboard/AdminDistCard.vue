<script setup>
import { computed } from 'vue';
import WcAdminDonut from '../../ui/wellcore-admin/WcAdminDonut.vue';

const props = defineProps({
  distribution: {
    type: Array,
    default: () => [
      { name: 'Entrenamiento',  value: 0, color: '#DC2626', glow: 'rgba(220,38,38,.5)' },
      { name: 'Nutrición',      value: 0, color: '#10B981', glow: 'rgba(16,185,129,.5)' },
      { name: 'Suplementación', value: 0, color: '#3B82F6', glow: 'rgba(59,130,246,.5)' },
      { name: 'Hábitos',        value: 0, color: '#F59E0B', glow: 'rgba(245,158,11,.5)' },
      { name: 'Sin clasificar', value: 0, color: '#71717A' },
      { name: 'Ciclo',          value: 0, color: '#A78BFA' },
    ],
  },
});

const total = computed(() => props.distribution.reduce((s, d) => s + (d.value || 0), 0) || 1);
const max = computed(() => Math.max(...props.distribution.map(d => d.value || 0), 1));
const segments = computed(() => props.distribution.map(d => ({ color: d.color, value: d.value || 0 })));
const unclassified = computed(() => props.distribution.find(d => /sin clasificar/i.test(d.name))?.value || 0);
const topPair = computed(() => {
  const sorted = [...props.distribution].sort((a, b) => (b.value || 0) - (a.value || 0));
  const top2 = sorted.slice(0, 2);
  const sum = top2.reduce((s, d) => s + (d.value || 0), 0);
  const pct = total.value ? Math.round((sum / total.value) * 100) : 0;
  return { names: top2.map(d => d.name).join(' + '), pct };
});
</script>

<template>
  <section class="dist-card wc-admin-card-dist">
    <div class="dist-head">
      <div class="ttl">Distribución de planes</div>
      <div class="lnk">Detalle →</div>
    </div>
    <div class="dist-top">
      <WcAdminDonut :segments="segments" :total="total" center-label="Asignaciones" />
      <div class="dist-summary">
        <div class="ttl">Mix por categoría</div>
        <div class="sub">El {{ topPair.pct }}% del mix es {{ topPair.names.toLowerCase() }}.</div>
        <div class="top-row">
          <span class="n tnum">{{ unclassified }}</span>
          <span class="lbl">Sin clasificar pendientes</span>
        </div>
      </div>
    </div>
    <div class="dist-rows">
      <div v-for="row in distribution" :key="row.name" class="dist-row">
        <span class="led" :style="{ background: row.color, boxShadow: row.glow ? `0 0 6px ${row.glow}` : 'none' }"></span>
        <span class="name">{{ row.name }}</span>
        <span class="meter"><i :style="{ width: ((row.value / max) * 100) + '%', background: row.color }"></i></span>
        <span class="num tnum">{{ row.value || 0 }}</span>
      </div>
    </div>
  </section>
</template>
