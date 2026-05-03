<script setup>
/**
 * WcProgressBar — Barra de progreso con shimmer + tabular nums opcional.
 *
 * Mapea a .wc-progress + .wc-progress-fill de app.css. El fill tiene shimmer
 * animation (--grad-accent-glow + animation wc-shimmer). El track usa
 * --wc-translucent-bg-medium (theme-aware). Width transitions con
 * --dur-fill var(--ease-glide).
 *
 * Props:
 *   value   number  0-100 (porcentaje)
 *   animate boolean — si false, deshabilita el shimmer animation
 *   label   string  — texto opcional debajo (con tabular nums)
 *   ariaLabel string — texto a11y para screen readers (opcional, se infiere de label si no se pasa)
 *
 * Uso:
 *   <WcProgressBar :value="92.5" label="185/200 XP"/>
 *   <WcProgressBar :value="100" :animate="false" label="CONTINUO 100%"/>
 */
import { computed } from 'vue';

const props = defineProps({
    value:     { type: Number, required: true, validator: (v) => v >= 0 && v <= 100 },
    animate:   { type: Boolean, default: true },
    label:     { type: String, default: '' },
    ariaLabel: { type: String, default: '' },
});

const fillStyle = computed(() => {
    const style = { width: `${props.value}%` };
    if (!props.animate) style.animation = 'none';
    return style;
});

const a11yLabel = computed(() => props.ariaLabel || props.label || `${props.value}% completado`);
</script>

<template>
  <div>
    <div
      class="wc-progress"
      role="progressbar"
      :aria-valuenow="value"
      aria-valuemin="0"
      aria-valuemax="100"
      :aria-label="a11yLabel"
    >
      <div class="wc-progress-fill" :style="fillStyle" />
    </div>
    <p v-if="label" class="mt-2 text-xs wc-tnum" style="color: var(--color-wc-text-secondary);">
      {{ label }}
    </p>
  </div>
</template>
