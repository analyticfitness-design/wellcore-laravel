<template>
  <button
    type="button"
    :disabled="disabled"
    @click="handleClick"
    :class="[
      'flex w-full items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-left transition-colors',
      disabled
        ? 'opacity-50 cursor-not-allowed'
        : 'hover:bg-wc-bg-secondary cursor-pointer',
    ]"
  >
    <span
      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10"
      aria-hidden="true"
    >
      <Camera :size="20" class="text-wc-accent" />
    </span>

    <span class="flex min-w-0 flex-1 flex-col">
      <span class="font-display text-sm uppercase tracking-[0.18em] text-wc-text">
        {{ t('client_plan.nutrition_ai_camera_title') }}
      </span>
      <span class="mt-0.5 text-xs text-wc-text-secondary">
        {{ t('client_plan.nutrition_ai_camera_subtitle') }}
      </span>
    </span>

    <ChevronRight
      :size="16"
      class="shrink-0 text-wc-text-tertiary"
      aria-hidden="true"
    />
  </button>
</template>

<script setup>
import { Camera, ChevronRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['open']);

function handleClick() {
  if (props.disabled) return;
  emit('open');
}
</script>
