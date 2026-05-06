<script setup>
/**
 * AnglesGrid — 3-column visual grid showing the three required angles
 * (Frente / Perfil / Espalda) as editorial silhouette cards.
 *
 * Pure presentational — no interaction. Lives inside PhotoGuide.
 */
import AngleSilhouette from './AngleSilhouette.vue';

const ANGLES = [
  { num: '01', variant: 'front', label: 'Frente',  desc: 'Mirando directo a la cámara, brazos relajados al lado del cuerpo. Pies separados al ancho de cadera.' },
  { num: '02', variant: 'side',  label: 'Perfil',  desc: 'De lado izquierdo, exactamente 90° a la cámara. Mira al horizonte, brazos sueltos.' },
  { num: '03', variant: 'back',  label: 'Espalda', desc: 'De espaldas a la cámara, brazos al lado del cuerpo. Hombros relajados, mirada al frente.' },
];
</script>

<template>
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4" role="list" aria-label="Ángulos requeridos">
    <article
      v-for="angle in ANGLES"
      :key="angle.num"
      role="listitem"
      class="relative flex aspect-[3/4] flex-col justify-end overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-b from-[#1a1a1d] to-[#0f0f12]"
    >
      <span class="absolute left-4 top-4 font-mono text-[11px] uppercase tracking-wider text-wc-text-tertiary">
        {{ angle.num }} / 03
      </span>
      <span class="absolute right-4 top-4 inline-flex items-center rounded-full border border-wc-border px-2 py-0.5 font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">
        Req
      </span>

      <div class="absolute inset-0 flex items-center justify-center">
        <AngleSilhouette :variant="angle.variant" />
      </div>

      <div class="relative bg-gradient-to-t from-black/55 to-transparent px-5 py-5">
        <h3 class="font-display text-2xl font-medium uppercase tracking-wider text-wc-text">
          {{ angle.label }}
        </h3>
        <p class="mt-1.5 text-[13px] leading-snug text-wc-text-secondary">
          {{ angle.desc }}
        </p>
      </div>
    </article>
  </div>
</template>
