<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'green' }, // amber | green | blue | red
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  unit: { type: String, default: '' },
  sub: { type: String, default: '' },
  delta: { type: String, default: '' },
  deltaVariant: { type: String, default: 'up' }, // up | flat | warn
  sparkPath: { type: String, default: '' }, // SVG path d=""
  sparkColor: { type: String, default: '' }, // override stroke
});

const sparkColors = { amber: '#F59E0B', green: '#10B981', blue: '#3B82F6', red: '#DC2626' };
const sparkStroke = computed(() => sparkColors[props.variant] || '#DC2626');
const sparkEndY = computed(() => {
  const m = props.sparkPath?.match(/L\s*100,\s*(\d+(?:\.\d+)?)/);
  return m ? parseFloat(m[1]) : 14;
});
</script>

<template>
  <div :class="['kpi', variant]">
    <div class="kpi-head">
      <div class="kpi-lbl">{{ label }}</div>
      <div v-if="delta" :class="['delta-pill', deltaVariant]">
        <svg v-if="deltaVariant === 'up'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <polyline points="6 15 12 9 18 15"></polyline>
        </svg>
        {{ delta }}
      </div>
    </div>
    <div class="kpi-num tnum">
      <slot name="value">{{ value }}</slot>
      <span v-if="unit" class="unit">{{ unit }}</span>
    </div>
    <div v-if="sub" class="kpi-sub" v-html="sub"></div>
    <div v-if="sparkPath" class="kpi-spark">
      <svg viewBox="0 0 100 28" preserveAspectRatio="none">
        <path :d="sparkPath" fill="none" :stroke="sparkColor || sparkStroke" stroke-width="1.5" stroke-linecap="round"></path>
        <circle cx="100" :cy="sparkEndY" r="2" :fill="sparkColor || sparkStroke" />
      </svg>
    </div>
  </div>
</template>
