<script setup>
/**
 * TipsList — 4 numbered editorial rules (01-04) for taking precise photos.
 * Replaces the legacy "💡 Iluminación / 👕 Vestimenta..." emoji rows with
 * a Bebas/Oswald + monospaced number hierarchy.
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const TIPS = computed(() => [
  { n: '01', title: t('client_progress.photos_lighting'),    body: t('client_progress.photos_lighting_body') },
  { n: '02', title: t('client_progress.photos_clothing'),    body: t('client_progress.photos_clothing_body') },
  { n: '03', title: t('client_progress.photos_distance'),    body: t('client_progress.photos_distance_body') },
  { n: '04', title: t('client_progress.photos_consistency'), body: t('client_progress.photos_consistency_body') },
]);
</script>

<template>
  <div class="grid gap-6 border-t border-wc-border pt-8 sm:grid-cols-[auto_1fr] sm:gap-12">
    <div class="max-w-[14ch]">
      <small class="mb-2 block font-mono text-[10px] font-normal uppercase tracking-[0.16em] text-wc-text-tertiary">
        {{ t('client_progress.photos_tips_kicker') }}
      </small>
      <h3 class="font-display text-xl font-medium uppercase tracking-wider text-wc-text">
        {{ t('client_progress.photos_tips_title') }}
      </h3>
    </div>
    <ul class="grid gap-6 sm:grid-cols-2 sm:gap-x-10 sm:gap-y-6" :aria-label="t('client_progress.photos_tips_aria')">
      <li
        v-for="tip in TIPS"
        :key="tip.n"
        class="grid grid-cols-[auto_1fr] items-start gap-3.5"
      >
        <span class="pt-0.5 font-mono text-[11px] uppercase tracking-widest text-wc-text-tertiary">
          {{ tip.n }}
        </span>
        <div class="min-w-0">
          <h4 class="mb-1 font-display text-sm font-semibold uppercase tracking-wider text-wc-text">
            {{ tip.title }}
          </h4>
          <p class="text-sm leading-relaxed text-wc-text-secondary">
            {{ tip.body }}
          </p>
        </div>
      </li>
    </ul>
  </div>
</template>
