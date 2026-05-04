<script setup>
defineProps({
  variant: { type: String, default: 'red' }, // red | green | purple | amber
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  unit: { type: String, default: '' },
  sub: { type: String, default: '' },
  xpPercent: { type: [Number, null], default: null },
});
</script>

<template>
  <div :class="['stat-card', variant]">
    <slot name="ghost" />
    <div class="stat-label">{{ label }}</div>
    <div class="stat-value tight tnum">
      {{ value }}
      <span v-if="unit" class="unit">{{ unit }}</span>
    </div>
    <div v-if="sub" class="stat-sub">{{ sub }}</div>
    <div v-if="xpPercent !== null" class="xp-bar">
      <div class="xp-fill" :style="{ '--pct': xpPercent + '%' }"></div>
    </div>
  </div>
</template>
