<script setup>
/**
 * EmptyState — first-run empty hero shown when the user has 0 sessions.
 * 3 ghost slots evoke the upload grid + editorial copy + 2 CTAs.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineEmits(['start', 'guide']);

const angleLabels = computed(() => [
  t('client_progress.photos_front'),
  t('client_progress.photos_side'),
  t('client_progress.photos_back'),
]);
</script>

<template>
  <section
    class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-b from-white/[0.015] to-transparent bg-wc-bg-tertiary px-6 py-12 text-center sm:px-8 sm:py-20"
    :aria-label="t('client_progress.photos_empty_aria')"
  >
    <!-- Ghost slots — HTML ref: 120px wide, 3/4 aspect, gap 14px -->
    <div class="mx-auto mb-7 flex justify-center gap-2.5 sm:mb-8 sm:gap-3.5">
      <div
        v-for="(label, i) in angleLabels"
        :key="label"
        class="flex aspect-[3/4] w-20 flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-wc-border bg-[repeating-linear-gradient(45deg,_rgba(255,255,255,0.02)_0_8px,_rgba(255,255,255,0.04)_8px_16px)] sm:w-[120px] sm:rounded-xl"
        :style="{ animationDelay: `${i * 0.08}s` }"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4 text-wc-text-tertiary opacity-40 sm:h-[18px] sm:w-[18px]" aria-hidden="true">
          <rect x="3" y="6" width="18" height="14" rx="2" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        <small class="font-mono text-[8px] uppercase tracking-widest text-wc-text-tertiary sm:text-[9px]">
          {{ label }}
        </small>
      </div>
    </div>

    <h3 class="mx-auto max-w-[18ch] font-display text-3xl font-medium uppercase leading-[1.05] tracking-wide text-wc-text sm:text-4xl">
      {{ t('client_progress.photos_empty_title_line1') }}
      <em class="not-italic font-normal text-wc-text-tertiary">{{ t('client_progress.photos_empty_title_line2') }}</em>
    </h3>
    <p class="mx-auto mt-3.5 max-w-[48ch] text-sm text-wc-text-secondary sm:text-[15px]">
      {{ t('client_progress.photos_empty_body') }}
    </p>

    <div class="mx-auto mt-7 flex flex-col items-stretch justify-center gap-2.5 sm:flex-row sm:items-center">
      <button
        type="button"
        class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-wc-accent px-5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
        @click="$emit('start')"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5" aria-hidden="true">
          <rect x="3" y="6" width="18" height="14" rx="2" />
          <circle cx="12" cy="13" r="3.5" />
          <path d="M8 6l1.5-2h5L16 6" />
        </svg>
        {{ t('client_progress.photos_empty_cta_start') }}
      </button>
      <button
        type="button"
        class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-secondary px-5 text-sm font-semibold text-wc-text-secondary transition-colors hover:border-wc-accent/50 hover:text-wc-text"
        @click="$emit('guide')"
      >
        {{ t('client_progress.photos_empty_cta_guide') }}
      </button>
    </div>
  </section>
</template>
