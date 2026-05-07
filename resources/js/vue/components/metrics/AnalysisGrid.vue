<script setup>
import CheckinsStreak from './CheckinsStreak.vue';
import CompositionPanel from './CompositionPanel.vue';

defineProps({
  weeklyCheckins: { type: Array, default: () => [] },
  composition: { type: Object, default: null },
  trainingVolume: { type: Array, default: () => [] },
});
</script>

<template>
  <div class="analysis-grid">
    <!-- Checkins streak -->
    <div class="analysis-card">
      <CheckinsStreak :weeklyCheckins="weeklyCheckins" />
    </div>

    <!-- Composition -->
    <div class="analysis-card">
      <CompositionPanel :composition="composition" />
    </div>
  </div>
</template>

<style scoped>
.analysis-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}
@media (min-width: 1024px) {
  .analysis-grid { grid-template-columns: 1.4fr 1fr; }
}
.analysis-card {
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  overflow: hidden;
}
</style>
