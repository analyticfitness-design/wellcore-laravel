<script setup>
import DeltaBadge from './DeltaBadge.vue';

defineProps({
  composition: { type: Object, default: null },
  // { grasa, musculo, agua, date }
});
</script>

<template>
  <div class="comp-panel">
    <div class="comp-hd">
      <p class="comp-label">Composición Corporal</p>
      <p class="comp-sub">Última medición<template v-if="composition?.date"> · {{ composition.date }}</template></p>
    </div>

    <template v-if="composition">
      <!-- Stacked bar rows -->
      <div class="comp-rows">
        <!-- Músculo -->
        <div class="comp-row">
          <span class="comp-name">Músculo</span>
          <div class="comp-bar">
            <div class="comp-fill comp-fill--musc" :style="{ width: composition.musculo + '%' }"></div>
          </div>
          <span class="comp-num tnum">{{ composition.musculo }}<small>%</small></span>
        </div>
        <!-- Grasa -->
        <div class="comp-row">
          <span class="comp-name">Grasa</span>
          <div class="comp-bar">
            <div class="comp-fill comp-fill--grasa" :style="{ width: composition.grasa + '%' }"></div>
          </div>
          <span class="comp-num tnum">{{ composition.grasa }}<small>%</small></span>
        </div>
        <!-- Agua -->
        <div class="comp-row">
          <span class="comp-name">Agua</span>
          <div class="comp-bar">
            <div class="comp-fill comp-fill--agua" :style="{ width: composition.agua + '%' }"></div>
          </div>
          <span class="comp-num tnum">{{ composition.agua }}<small>%</small></span>
        </div>
      </div>
    </template>

    <p v-else class="comp-empty">Sin datos de composición</p>
  </div>
</template>

<style scoped>
.comp-panel { padding: 16px 20px; }
.comp-hd { margin-bottom: 18px; }
.comp-label {
  font: 600 12px/1 var(--font-display);
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--color-wc-text);
  margin: 0;
}
.comp-sub {
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  margin-top: 2px;
}
.comp-rows { display: flex; flex-direction: column; gap: 18px; }
.comp-row {
  display: grid;
  grid-template-columns: 72px 1fr 56px;
  align-items: center;
  gap: 14px;
}
.comp-name {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.comp-bar {
  height: 10px;
  border-radius: 999px;
  background: var(--color-wc-bg);
  overflow: hidden;
  border: 1px solid var(--color-wc-border);
}
.comp-fill {
  height: 100%;
  border-radius: 999px;
  transition: width .4s ease;
}
.comp-fill--musc { background: linear-gradient(90deg, #10B981, #34D399); }
.comp-fill--grasa { background: linear-gradient(90deg, #F59E0B, #FBBF24); }
.comp-fill--agua { background: linear-gradient(90deg, #3B82F6, #60A5FA); }
.comp-num {
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 600;
  color: var(--color-wc-text);
  text-align: right;
  font-variant-numeric: tabular-nums;
}
.comp-num small {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  font-weight: 400;
  display: inline;
  margin-left: 1px;
}
.comp-empty {
  font-size: 13px;
  color: var(--color-wc-text-tertiary);
  text-align: center;
  padding: 24px 0;
  margin: 0;
}
.tnum { font-variant-numeric: tabular-nums; }
</style>
