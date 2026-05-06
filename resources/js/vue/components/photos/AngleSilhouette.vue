<script setup>
/**
 * AngleSilhouette — inline editorial SVG of a body silhouette in three
 * variants (front / side / back). Decorative — `aria-hidden` by default,
 * unless an explicit `label` is passed.
 *
 * Props:
 *   variant: 'front' | 'side' | 'back'  (default 'front')
 *   label:   string  (optional accessible label; if provided the SVG is
 *            no longer aria-hidden and gets <title>)
 *
 * Visuals match HTML source v2.1 (paths preserved verbatim).
 */
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'front' },
  label: { type: String, default: '' },
});

const ariaHidden = computed(() => (props.label ? null : 'true'));
</script>

<template>
  <svg
    viewBox="0 0 200 280"
    fill="none"
    stroke="rgba(250,250,250,0.85)"
    stroke-width="1.6"
    class="h-auto w-[55%] opacity-90"
    role="img"
    :aria-hidden="ariaHidden"
    :aria-label="label || null"
  >
    <title v-if="label">{{ label }}</title>

    <!-- FRONT (default) -->
    <template v-if="variant === 'front'">
      <circle cx="100" cy="42" r="20" fill="rgba(250,250,250,0.06)" />
      <path
        d="M100 62 L100 80 M76 88 Q100 72 124 88 L132 150 L120 156 L116 138 L116 220 L108 250 L92 250 L84 220 L84 138 L80 156 L68 150 Z"
        fill="rgba(250,250,250,0.06)"
      />
    </template>

    <!-- SIDE / PERFIL -->
    <template v-else-if="variant === 'side'">
      <ellipse cx="108" cy="42" rx="16" ry="20" fill="rgba(250,250,250,0.06)" />
      <path
        d="M104 62 Q102 70 106 80 Q120 90 122 110 Q124 140 116 160 Q112 175 110 200 Q108 225 104 250 L96 250 Q94 230 96 200 Q94 175 88 160 Q82 140 84 110 Q86 90 100 80 Q98 70 100 62 Z"
        fill="rgba(250,250,250,0.06)"
      />
    </template>

    <!-- BACK / ESPALDA -->
    <template v-else>
      <circle cx="100" cy="42" r="20" fill="rgba(250,250,250,0.06)" />
      <path
        d="M100 62 L100 80 M76 88 Q100 72 124 88 L132 150 L120 156 L116 138 L116 220 L108 250 L92 250 L84 220 L84 138 L80 156 L68 150 Z"
        fill="rgba(250,250,250,0.06)"
      />
      <line x1="100" y1="80" x2="100" y2="220" opacity="0.5" />
    </template>
  </svg>
</template>
