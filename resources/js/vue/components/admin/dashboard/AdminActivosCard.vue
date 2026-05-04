<script setup>
import { computed } from 'vue';
import WcAdminProgressBar from '../../ui/wellcore-admin/WcAdminProgressBar.vue';

const props = defineProps({
  clientsBreakdown: {
    type: Object,
    default: () => ({ total: 0, active: 0, inactive: 0 }),
  },
});

const percent = computed(() => {
  const t = props.clientsBreakdown.total || 1;
  return Math.round(((props.clientsBreakdown.active || 0) / t) * 100);
});
</script>

<template>
  <!-- MOBILE: section wrapper + activos-card -->
  <section class="activos activos-mobile">
    <div class="activos-card">
      <div class="activos-head">
        <div class="left">
          <span class="n tnum">{{ clientsBreakdown.total || 0 }}</span>
          <span class="lbl">Clientes</span>
        </div>
        <div class="pct tnum">{{ percent }}%<span class="small">activos</span></div>
      </div>
      <WcAdminProgressBar :percent="percent" tall />
      <div class="activos-foot">
        <span class="item"><span class="dot g"></span>Activo <span class="num tnum">{{ clientsBreakdown.active || 0 }}</span></span>
        <span class="item"><span class="dot x"></span>Inactivo <span class="num tnum">{{ clientsBreakdown.inactive || 0 }}</span></span>
      </div>
    </div>
  </section>

  <!-- DESKTOP: single row layout -->
  <div class="activos-card activos-desktop">
    <div class="left">
      <span class="n tnum">{{ clientsBreakdown.total || 0 }}</span>
      <span class="lbl">Clientes</span>
    </div>
    <WcAdminProgressBar :percent="percent" />
    <div class="pct tnum">{{ percent }}%</div>
    <div class="activos-foot">
      <span class="item"><span class="dot g"></span>Activo <span class="num tnum">{{ clientsBreakdown.active || 0 }}</span></span>
      <span class="item"><span class="dot x"></span>Inactivo <span class="num tnum">{{ clientsBreakdown.inactive || 0 }}</span></span>
    </div>
  </div>
</template>

<style scoped>
.activos-mobile { display: block; }
.activos-desktop { display: none; }
@media (min-width: 1024px){
  .activos-mobile { display: none; }
  .activos-desktop { display: grid; }
}
</style>
