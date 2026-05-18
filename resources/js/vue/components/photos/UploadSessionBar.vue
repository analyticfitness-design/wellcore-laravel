<script setup>
/**
 * UploadSessionBar — sticky bar (mobile bottom, desktop top of upload grid)
 * showing progress ring (X/3 angles selected) + DateField + primary CTA.
 *
 * Props:
 *   modelValue: 'YYYY-MM-DD'  date
 *   selected:   number  0..3
 *   total:      number  default 3
 *   uploading:  bool
 *   disabled:   bool
 *
 * Emits:
 *   update:modelValue (date)
 *   submit
 */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DateField from './DateField.vue';

const { t } = useI18n();

const props = defineProps({
  modelValue: { type: String, default: '' },
  selected: { type: Number, default: 0 },
  total: { type: Number, default: 3 },
  uploading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});
defineEmits(['update:modelValue', 'submit']);

const RADIUS = 14;
const CIRC = 2 * Math.PI * RADIUS; // ≈ 87.96

const dashOffset = computed(() => {
  const pct = Math.min(1, Math.max(0, props.selected / props.total));
  return CIRC * (1 - pct);
});

const ctaLabel = computed(() => {
  if (props.uploading) return t('client_progress.photos_upload_uploading');
  if (props.selected === 0) return t('client_progress.photos_upload_pick_one');
  if (props.selected < props.total) return t('client_progress.photos_upload_partial', { selected: props.selected, total: props.total });
  return t('client_progress.photos_upload_session_cta');
});

const ctaDisabled = computed(() => props.disabled || props.uploading || props.selected === 0);
</script>

<template>
  <div class="flex flex-col items-stretch gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-3 sm:flex-row sm:items-end sm:p-4">
    <!-- Ring progress -->
    <div class="flex shrink-0 items-center gap-3">
      <div class="relative h-12 w-12">
        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" aria-hidden="true">
          <circle cx="18" cy="18" :r="RADIUS" fill="none" stroke="currentColor" stroke-width="3" class="text-wc-border" />
          <circle
            cx="18"
            cy="18"
            :r="RADIUS"
            fill="none"
            stroke="#DC2626"
            stroke-width="3"
            stroke-linecap="round"
            :stroke-dasharray="CIRC"
            :stroke-dashoffset="dashOffset"
            class="transition-all duration-500"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="font-display text-sm font-semibold leading-none text-wc-text">{{ selected }}</span>
          <span class="font-mono text-[8px] uppercase tracking-widest text-wc-text-tertiary">/{{ total }}</span>
        </div>
      </div>
      <div class="hidden flex-col sm:flex">
        <span class="font-display text-[11px] uppercase tracking-widest text-wc-text-tertiary">{{ t('client_progress.photos_upload_session_label') }}</span>
        <span class="text-xs text-wc-text-secondary">{{ t('client_progress.photos_upload_session_angles') }}</span>
      </div>
    </div>

    <!-- Date -->
    <div class="flex-1">
      <DateField
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        :label="t('client_progress.photos_upload_date_label')"
        id="upload-session-date"
      />
    </div>

    <!-- CTA -->
    <button
      type="button"
      :disabled="ctaDisabled"
      class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-wc-accent px-5 font-display text-sm uppercase tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:cursor-not-allowed disabled:opacity-50"
      @click="$emit('submit')"
    >
      <svg v-if="uploading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5" aria-hidden="true">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" />
      </svg>
      <span>{{ ctaLabel }}</span>
    </button>
  </div>
</template>
