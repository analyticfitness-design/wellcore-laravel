<script setup>
/**
 * AnglesGrid — 3-column visual grid showing the three required angles
 * (Frente / Perfil / Espalda) as editorial silhouette cards.
 *
 * Pure presentational — no interaction. Lives inside PhotoGuide.
 */
import AngleSilhouette from './AngleSilhouette.vue';

const ANGLES = [
  { num: '01', variant: 'front', label: 'Frente' },
  { num: '02', variant: 'side',  label: 'Perfil' },
  { num: '03', variant: 'back',  label: 'Espalda' },
];
</script>

<template>
  <div class="grid grid-cols-3 gap-2 sm:gap-3" role="list" aria-label="Ángulos requeridos">
    <article
      v-for="angle in ANGLES"
      :key="angle.num"
      role="listitem"
      class="relative flex aspect-[1/1.2] flex-col justify-end overflow-hidden rounded-xl border border-wc-border bg-gradient-to-b from-wc-bg-secondary to-wc-bg"
    >
      <span class="absolute left-2 top-2 font-mono text-[9px] uppercase tracking-wider text-wc-text-tertiary">
        {{ angle.num }}
      </span>

      <div class="absolute inset-0 flex items-center justify-center">
        <AngleSilhouette :variant="angle.variant" />
      </div>

      <div class="relative bg-gradient-to-t from-black/60 to-transparent px-2.5 py-2.5">
        <h3 class="font-display text-[13px] font-semibold uppercase tracking-wider text-wc-text">
          {{ angle.label }}
        </h3>
      </div>
    </article>
  </div>
</template>
