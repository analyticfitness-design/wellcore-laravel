<script setup>
/**
 * CompletionRing.vue — SVG progress ring 120×120.
 *
 * Tier coloring (NUNCA rojo):
 *   - score < 50  → low  → #F59E0B (warning ámbar)
 *   - 50-79       → mid  → #3B82F6 (info azul)
 *   - 80+         → high → #10B981 (success verde)
 *
 * El componente es puramente visual: emite nada, sólo renderiza ring + slot
 * (donde IdentityHero coloca el AvatarUploader / disco).
 *
 * Strokes: r=54, circumference ≈ 339.292 (2π·54). Animado via stroke-dashoffset.
 * prefers-reduced-motion respetado.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useReducedMotion } from '../../composables/useReducedMotion';

const { t } = useI18n();

const props = defineProps({
    score: { type: Number, default: 0 },
    size:  { type: Number, default: 120 },
    strokeWidth: { type: Number, default: 4 },
});

const reduced = useReducedMotion();

const clampedScore = computed(() => {
    const n = Number(props.score);
    if (!Number.isFinite(n)) return 0;
    return Math.max(0, Math.min(100, n));
});

const tier = computed(() => {
    const s = clampedScore.value;
    if (s >= 80) return 'high';
    if (s >= 50) return 'mid';
    return 'low';
});

const tierColor = computed(() => {
    switch (tier.value) {
        case 'high': return '#10B981';
        case 'mid':  return '#3B82F6';
        default:     return '#F59E0B';
    }
});

const radius = computed(() => (props.size - props.strokeWidth) / 2);
const center = computed(() => props.size / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const dashOffset = computed(
    () => circumference.value * (1 - clampedScore.value / 100)
);
</script>

<template>
  <div
    class="completion-ring"
    :style="{ width: `${size}px`, height: `${size}px` }"
  >
    <svg
      class="completion-ring__svg"
      :viewBox="`0 0 ${size} ${size}`"
      :width="size"
      :height="size"
      role="img"
      :aria-label="`${t('client_account.profile_completion_label')} ${clampedScore}%`"
    >
      <circle
        class="completion-ring__track"
        :cx="center" :cy="center" :r="radius"
        fill="none"
        :stroke-width="strokeWidth"
      />
      <circle
        class="completion-ring__fill"
        :class="['is-tier-' + tier, { 'is-reduced': reduced }]"
        :cx="center" :cy="center" :r="radius"
        fill="none"
        :stroke="tierColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="dashOffset"
      />
    </svg>
    <div class="completion-ring__inner">
      <slot :tier="tier" :score="clampedScore" :color="tierColor" />
    </div>
  </div>
</template>

<style scoped>
.completion-ring {
  position: relative;
  display: inline-block;
  flex-shrink: 0;
}
.completion-ring__svg {
  position: absolute;
  inset: 0;
  transform: rotate(-90deg);
  transform-origin: center;
}
.completion-ring__track {
  stroke: var(--color-wc-bg-prominent, var(--color-wc-bg-tertiary));
}
.completion-ring__fill {
  transition: stroke-dashoffset 0.8s cubic-bezier(0.2, 0.7, 0.2, 1),
              stroke 0.4s ease;
}
.completion-ring__fill.is-reduced { transition-duration: 0.01ms; }

.completion-ring__inner {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (prefers-reduced-motion: reduce) {
  .completion-ring__fill { transition-duration: 0.01ms !important; }
}
</style>
