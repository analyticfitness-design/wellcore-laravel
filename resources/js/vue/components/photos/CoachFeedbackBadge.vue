<script setup>
/**
 * CoachFeedbackBadge — pill that signals coach review state on a session
 * thumbnail or list row.
 *
 * Props:
 *   status: 'reviewed' | 'pending' | 'notes'   (default 'pending')
 *   count:  number   only used when status === 'notes' to show "Notas · 3"
 *   compact: bool    smaller text for dense layouts
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  status: { type: String, default: 'pending' },
  count: { type: Number, default: 0 },
  compact: { type: Boolean, default: false },
});

const meta = computed(() => {
  switch (props.status) {
    case 'reviewed':
      return {
        label: t('client_progress.photos_badge_reviewed'),
        cls: 'border-emerald-400/30 bg-emerald-500/10 text-emerald-400',
      };
    case 'notes':
      return {
        label: props.count > 0 ? t('client_progress.photos_badge_notes_count', { n: props.count }) : t('client_progress.photos_badge_notes'),
        cls: 'border-red-400/30 bg-red-500/10 text-red-400',
      };
    default:
      return {
        label: t('client_progress.photos_badge_pending'),
        cls: 'border-amber-400/30 bg-amber-500/10 text-amber-400',
      };
  }
});
</script>

<template>
  <span
    class="inline-flex items-center rounded-full border font-display uppercase tracking-wider"
    :class="[
      meta.cls,
      compact ? 'px-2 py-0.5 text-[9px]' : 'px-2.5 py-1 text-[10px]',
    ]"
  >
    {{ meta.label }}
  </span>
</template>
