<script setup>
/**
 * AnglesGrid — 3-column visual grid showing the three required angles
 * (Frente / Perfil / Espalda).
 *
 * Si recibe `genero` ('mujer' | 'hombre'), muestra las character images
 * reales (silvia para mujer, dann para hombre) — preservando el flow del
 * legacy ProgressPhotos.vue. Si NO se pasa género, fallback a las
 * silhouettes editoriales SVG.
 *
 * Pure presentational — no interaction. Lives inside PhotoGuide.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AngleSilhouette from './AngleSilhouette.vue';

const { t } = useI18n();

const props = defineProps({
  genero: { type: String, default: '' }, // 'mujer' | 'hombre' | ''
});

const ANGLES = computed(() => [
  { num: '01', variant: 'front', file: 'frontal', label: t('client_progress.photos_front'),  desc: t('client_progress.photos_front_desc') },
  { num: '02', variant: 'side',  file: 'perfil',  label: t('client_progress.photos_side'),   desc: t('client_progress.photos_side_desc') },
  { num: '03', variant: 'back',  file: 'espalda', label: t('client_progress.photos_back'),   desc: t('client_progress.photos_back_desc') },
]);

const useCharacters = computed(() => props.genero === 'mujer' || props.genero === 'hombre');
const characterBase = computed(() => props.genero === 'mujer' ? '/images/characters/silvia' : '/images/characters/dann');
</script>

<template>
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4" role="list" :aria-label="t('client_progress.photos_angles_aria')">
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
        {{ t('client_progress.photos_angle_req_short') }}
      </span>

      <div class="absolute inset-0 flex items-center justify-center">
        <img
          v-if="useCharacters"
          :src="`${characterBase}/${angle.file}.webp`"
          :alt="angle.label"
          class="h-[78%] w-auto object-contain"
          loading="lazy"
        />
        <AngleSilhouette v-else :variant="angle.variant" />
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
