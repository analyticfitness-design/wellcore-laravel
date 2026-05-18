<script setup>
/**
 * PhotoTimeline — horizontal scroll-snap rail of TimelineSession columns.
 * Newest sessions appear first (left). Hides scrollbar but keeps native
 * touch/wheel interaction.
 *
 * Props:
 *   sessions:   array of session objects (see useProgressPhotos)
 *   metaByDate: optional map { 'YYYY-MM-DD': { weight, waist, weekLabel,
 *               reviewStatus, notesCount } } — supplies extra info per col.
 *
 * Emits:
 *   select(photo)
 *   open-feedback(session)
 */
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import TimelineSession from './TimelineSession.vue';

const { t } = useI18n();

const props = defineProps({
  sessions: { type: Array, default: () => [] },
  metaByDate: { type: Object, default: () => ({}) },
});
defineEmits(['select', 'open-feedback']);

const railRef = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

function updateArrows() {
  const el = railRef.value;
  if (!el) return;
  canScrollLeft.value = el.scrollLeft > 8;
  canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 8;
}

function scrollBy(delta) {
  railRef.value?.scrollBy({ left: delta, behavior: 'smooth' });
}

onMounted(() => {
  requestAnimationFrame(updateArrows);
});

// HTML reference shows sessions left-to-right chronologically (oldest first)
// so the user reads "Semana 01 · Inicio → Semana 06 · Reciente". Composable
// returns newest-first, so reverse a shallow copy here.
const enhanced = computed(() => {
  const list = [...(props.sessions || [])].reverse();
  const total = list.length;
  return list.map((s, idx) => ({
    session: s,
    meta: props.metaByDate?.[s.date] || {},
    isLatest: idx === total - 1,
    isFirst: idx === 0,
    weekLabelFallback: idx === 0 ? t('client_progress.photos_week_first') : (idx === total - 1 ? t('client_progress.photos_week_latest') : ''),
  }));
});
</script>

<template>
  <div class="relative">
    <button
      v-if="canScrollLeft"
      type="button"
      class="absolute left-1 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-wc-border bg-wc-bg/80 text-wc-text-secondary backdrop-blur transition-colors hover:text-wc-text sm:flex"
      :aria-label="t('client_progress.photos_timeline_prev_aria')"
      @click="scrollBy(-300)"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
        <path d="M15 18l-6-6 6-6" />
      </svg>
    </button>

    <div
      ref="railRef"
      class="flex gap-3 overflow-x-auto pb-2 [scroll-snap-type:x_mandatory] [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
      style="scroll-padding: 0 0.75rem"
      @scroll="updateArrows"
    >
      <TimelineSession
        v-for="(item, idx) in enhanced"
        :key="item.session.date"
        :session="item.session"
        :week-label="item.meta.weekLabel || item.weekLabelFallback"
        :review-status="item.meta.reviewStatus || (item.isLatest ? 'pending' : 'reviewed')"
        :notes-count="item.meta.notesCount || 0"
        :meta="{ weight: item.meta.weight, waist: item.meta.waist }"
        @select="$emit('select', $event)"
        @open-feedback="$emit('open-feedback', $event)"
      />
    </div>

    <button
      v-if="canScrollRight"
      type="button"
      class="absolute right-1 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-wc-border bg-wc-bg/80 text-wc-text-secondary backdrop-blur transition-colors hover:text-wc-text sm:flex"
      :aria-label="t('client_progress.photos_timeline_next_aria')"
      @click="scrollBy(300)"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
        <path d="M9 6l6 6-6 6" />
      </svg>
    </button>
  </div>
</template>
