<script setup>
/**
 * CompareCell — single image cell used in the side-by-side comparison.
 * Shows the photo (or empty state), a date stamp overlay, and an optional
 * angle label below.
 *
 * Props:
 *   photo:  { url, tipo, photo_date } | null
 *   date:   'YYYY-MM-DD'
 *   angle:  'frente' | 'perfil' | 'espalda'
 *   side:   'a' | 'b'  (used to position the divider)
 */
import { computed } from 'vue';

const props = defineProps({
  photo: { type: Object, default: null },
  date: { type: String, default: '' },
  angle: { type: String, default: '' },
  side: { type: String, default: 'a' },
});

const ANGLE_LABELS = { frente: 'Frente', perfil: 'Perfil', espalda: 'Espalda' };

const stamp = computed(() => {
  if (!props.date) return '';
  const [y, m, d] = props.date.split('-').map(Number);
  if (!y || !m || !d) return props.date;
  const MESES = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
  return `${String(d).padStart(2, '0')} ${MESES[m - 1]} ${y}`;
});
</script>

<template>
  <div class="relative aspect-[3/4] overflow-hidden bg-wc-bg-tertiary">
    <template v-if="photo?.url">
      <img
        :src="photo.url"
        :alt="ANGLE_LABELS[angle] || angle"
        class="absolute inset-0 h-full w-full object-cover"
        loading="lazy"
      />
    </template>
    <template v-else>
      <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-wc-bg-tertiary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7 text-wc-text-tertiary" aria-hidden="true">
          <rect x="3" y="6" width="18" height="14" rx="2" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        <p class="text-xs text-wc-text-tertiary">Sin foto</p>
      </div>
    </template>

    <!-- Stamp -->
    <div
      class="absolute top-2 rounded bg-black/65 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-white backdrop-blur"
      :class="side === 'a' ? 'left-2' : 'right-2'"
    >
      {{ stamp }}
      <small v-if="ANGLE_LABELS[angle]" class="mt-0.5 block text-[8px] text-white/60">
        {{ ANGLE_LABELS[angle] }}
      </small>
    </div>
  </div>
</template>
