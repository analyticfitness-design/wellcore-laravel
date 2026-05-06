<script setup>
/**
 * TimelineSession — single column in PhotoTimeline. Renders the date header,
 * 3 thumbnails (frente/perfil/espalda), CoachFeedbackBadge and optional
 * meta line (peso/cintura).
 *
 * Props:
 *   session:   { date, photos: { frente, perfil, espalda }, hasAll }
 *   weekLabel: string   ("Semana 03 · Inicio") — optional
 *   reviewStatus: 'reviewed' | 'pending' | 'notes'   default 'pending'
 *   notesCount:  number  for the badge
 *   meta:      { weight?: number, waist?: number }   optional
 *
 * Emits:
 *   select(photo)   click on thumbnail
 *   open-feedback(session)  click on badge / meta hint
 */
import { computed } from 'vue';
import CoachFeedbackBadge from './CoachFeedbackBadge.vue';

const props = defineProps({
  session: { type: Object, required: true },
  weekLabel: { type: String, default: '' },
  reviewStatus: { type: String, default: 'pending' },
  notesCount: { type: Number, default: 0 },
  meta: { type: Object, default: () => ({}) },
});
defineEmits(['select', 'open-feedback']);

const ANGLES = ['frente', 'perfil', 'espalda'];
const ANGLE_LABELS = { frente: 'Frente', perfil: 'Perfil', espalda: 'Espalda' };

const MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

const formatted = computed(() => {
  if (!props.session?.date) return { day: '—', month: '', dow: '' };
  const d = new Date(props.session.date + 'T12:00:00');
  if (isNaN(d.getTime())) return { day: '—', month: '', dow: '' };
  const DOW = ['dom','lun','mar','mié','jue','vie','sáb'];
  return {
    day: String(d.getDate()).padStart(2, '0'),
    month: MESES[d.getMonth()] || '',
    dow: DOW[d.getDay()],
  };
});
</script>

<template>
  <article
    class="flex w-[calc(100%-1.5rem)] shrink-0 snap-start flex-col gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-3.5 sm:w-[280px]"
  >
    <header class="flex items-start justify-between">
      <div class="min-w-0">
        <p
          v-if="weekLabel"
          class="font-display text-[10px] font-medium uppercase tracking-[0.16em] text-wc-text-tertiary"
        >
          {{ weekLabel }}
        </p>
        <p class="font-display text-base uppercase tracking-wider text-wc-text">
          {{ formatted.day }} {{ formatted.month.toUpperCase() }}
          <small class="ml-1 font-normal text-wc-text-tertiary">· {{ formatted.dow }}</small>
        </p>
      </div>
      <CoachFeedbackBadge
        :status="reviewStatus"
        :count="notesCount"
        compact
        class="cursor-pointer"
        @click="$emit('open-feedback', session)"
      />
    </header>

    <div class="grid grid-cols-3 gap-2">
      <button
        v-for="angle in ANGLES"
        :key="angle"
        type="button"
        class="group relative aspect-[3/4] overflow-hidden rounded-[10px] bg-wc-bg-secondary transition-transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-wc-accent/40"
        :aria-label="`Ver ${ANGLE_LABELS[angle]} de la sesión`"
        @click="session.photos[angle] && $emit('select', session.photos[angle])"
      >
        <template v-if="session.photos[angle]">
          <img
            :src="session.photos[angle].url"
            :alt="ANGLE_LABELS[angle]"
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
          />
        </template>
        <template v-else>
          <div class="absolute inset-0 flex items-center justify-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5 text-wc-text-tertiary opacity-40" aria-hidden="true">
              <rect x="3" y="6" width="18" height="14" rx="2" />
              <circle cx="12" cy="13" r="3.5" />
            </svg>
          </div>
        </template>
        <!-- Tag top-left (HTML ref uses top:8px) -->
        <span class="absolute left-1.5 top-1.5 rounded border border-white/15 bg-black/55 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-white backdrop-blur">
          {{ ANGLE_LABELS[angle] }}
        </span>
        <!-- Has-note pin: shows when this specific photo has coach notes -->
        <span
          v-if="session.photos[angle]?.has_notes"
          class="absolute bottom-1.5 right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-white text-black"
          aria-label="Tiene notas del coach"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-2.5 w-2.5" aria-hidden="true">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </span>
      </button>
    </div>

    <footer
      v-if="meta?.weight || meta?.waist"
      class="flex items-center gap-3 font-mono text-[11px] text-wc-text-tertiary"
    >
      <span v-if="meta.weight"><strong class="font-semibold text-wc-text">{{ meta.weight }}</strong> kg</span>
      <span v-if="meta.waist"><strong class="font-semibold text-wc-text">{{ meta.waist }}</strong> cm cintura</span>
    </footer>
  </article>
</template>
