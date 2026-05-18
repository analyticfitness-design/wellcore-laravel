<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    size: { type: String, default: 'sm' },
    label: { type: String, default: null },
});
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1 rounded-full font-semibold uppercase tracking-wider',
      'bg-amber-500/15 text-amber-600 dark:text-amber-400',
      size === 'xs' && 'px-1.5 py-0 text-[9px]',
      size === 'sm' && 'px-2 py-0.5 text-[10px]',
      size === 'md' && 'px-2.5 py-1 text-xs',
    ]"
  >
    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.293z" />
    </svg>
    <span>{{ label ?? t('client_social.badge_coach') }}</span>
  </span>
</template>
