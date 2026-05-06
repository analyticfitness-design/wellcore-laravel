<script setup>
/**
 * ProgressPhotos.vue — Dispatcher entre PhotosV2 (redesign Fase 2) y legacy.
 *
 * Activación:
 *   1. localStorage `wc_force_photos_v2` = '1' → V2 (force, QA bypass)
 *   2. localStorage `wc_force_photos_v2` = '0' → legacy (force rollback)
 *   3. featureFlags store con `photos_v2` activo (URL ?ff=photos_v2 o
 *      wc_flags_v1) → V2
 *   4. Default → legacy (flag OFF en config)
 *
 * Patrón espejo de WorkoutPlayer.vue / WorkoutPlayerV2.vue.
 *
 * Fase 2 ships con flag OFF: usuarios reciben legacy hasta que Daniel active.
 * QA bypass rápido en consola del navegador:
 *   localStorage.setItem('wc_force_photos_v2','1'); location.reload();
 */
import { defineAsyncComponent } from 'vue';
import { useFeatureFlag } from '../../composables/useFeatureFlag';

const photosV2Enabled = useFeatureFlag('photos_v2');

const PhotosV2 = defineAsyncComponent(() => import('./PhotosV2.vue'));
const ProgressPhotosLegacy = defineAsyncComponent(() => import('./ProgressPhotos.legacy.vue'));
</script>

<template>
  <component :is="photosV2Enabled ? PhotosV2 : ProgressPhotosLegacy" />
</template>
