<script setup>
defineProps({
  variant: { type: String, default: 'amber' }, // amber | blue
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  sub: { type: String, default: '' },
});
</script>

<template>
  <div :class="['alert-chip', variant]">
    <div class="ac-icon">
      <slot name="icon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </slot>
    </div>
    <div class="ac-meta">
      <div class="lbl">{{ label }}</div>
      <div class="val tnum">{{ value }} <span v-if="sub" class="sub">{{ sub }}</span></div>
    </div>
  </div>
</template>
