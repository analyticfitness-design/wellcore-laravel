<template>
  <div class="flex flex-col gap-1.5 border-b border-wc-accent/20 bg-wc-accent/[0.06] px-4 py-2.5">
    <!-- Row 1: pill + from -->
    <div class="flex items-center gap-2 flex-wrap">
      <span class="inline-flex items-center gap-1 rounded-full bg-wc-accent/20 px-2 py-0.5 font-display text-[9px] font-semibold uppercase tracking-[0.14em] text-wc-accent">
        <span class="h-1 w-1 rounded-full bg-wc-accent"></span>
        {{ t('client_plan.swap_replaced_chip') }}
      </span>
      <span
        v-if="originalName"
        class="font-data text-[11px] line-through text-wc-text-tertiary truncate"
      >
        {{ originalName }}
      </span>
    </div>

    <!-- Row 2: arrow + to -->
    <div class="flex items-center gap-2 flex-wrap">
      <span class="font-data text-[12px] text-wc-text-tertiary">→</span>
      <span class="font-data text-[11px] font-semibold text-wc-text truncate flex-1 min-w-0">
        {{ replacementName }}
      </span>
      <button
        type="button"
        :disabled="restoring"
        :aria-label="t('client_plan.swap_restore_aria')"
        class="ml-auto inline-flex min-h-[44px] shrink-0 items-center gap-1 rounded-full border border-wc-accent/30 px-3 py-1.5 font-display text-[10px] uppercase tracking-[0.12em] text-wc-accent transition hover:bg-wc-accent/10 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
        @click="emit('restore')"
      >
        {{ t('client_plan.swap_restore_cta') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
  originalName: {
    type: String,
    default: '',
  },
  replacementName: {
    type: String,
    required: true,
  },
  restoring: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['restore']);
</script>
