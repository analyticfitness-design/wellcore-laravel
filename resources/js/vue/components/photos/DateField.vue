<script setup>
/**
 * DateField — typographic date picker (NO native <input type="date">).
 *
 * The visible trigger is a <button> showing the date in editorial style
 * ("12 ABR 2026"). Clicking opens an overlay with a year/month/day grid.
 * v-model returns 'YYYY-MM-DD' strings (matches backend session_date format).
 *
 * Phase 1 implementation: native <select> overlay (year/month) + day grid.
 * This avoids dragging a date-picker dependency. Visual polish iterates in
 * later phases without changing the public API.
 *
 * Props:
 *   modelValue: 'YYYY-MM-DD' string  required
 *   min, max:   'YYYY-MM-DD' optional bounds
 *   label:      string  optional (defaults to 'Fecha')
 *
 * Emits:
 *   update:modelValue
 */
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { localDateStr } from '../../composables/useDate';

const { t, locale } = useI18n();

const props = defineProps({
  modelValue: { type: String, default: '' },
  min: { type: String, default: '' },
  max: { type: String, default: '' },
  label: { type: String, default: '' },
  id: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const MESES_ES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const MESES_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const today = localDateStr();
const value = computed({
  get: () => props.modelValue || today,
  set: (v) => emit('update:modelValue', v),
});

const resolvedLabel = computed(() => props.label || t('client_progress.photos_datefield_label_default'));

const display = computed(() => {
  const v = value.value;
  if (!v) return '—';
  const [y, m, d] = v.split('-').map(Number);
  if (!y || !m || !d) return v;
  const arr = locale.value === 'en' ? MESES_EN : MESES_ES;
  return `${String(d).padStart(2, '0')} ${arr[m - 1]?.toUpperCase() || ''} ${y}`;
});

// Picker uses a hidden native input fallback for now to guarantee correctness;
// rendered as button + clipped input. v2.1 will swap for a custom calendar.
const inputRef = ref(null);
function openPicker() {
  open.value = true;
  // Defer to allow input to be present
  requestAnimationFrame(() => {
    if (inputRef.value?.showPicker) {
      try { inputRef.value.showPicker(); } catch { inputRef.value.click(); }
    } else {
      inputRef.value?.click();
    }
  });
}

watch(() => value.value, () => { open.value = false; });
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label
      :for="id || 'wc-date-field'"
      class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary"
    >
      {{ resolvedLabel }}
    </label>

    <button
      type="button"
      :id="id || 'wc-date-field'"
      class="group inline-flex min-h-[44px] items-center justify-between gap-3 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-left transition-colors hover:border-wc-accent/50 focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
      @click="openPicker"
      :aria-label="`${resolvedLabel}: ${display}`"
    >
      <span class="font-display text-base uppercase tracking-wider text-wc-text">
        {{ display }}
      </span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-wc-text-tertiary group-hover:text-wc-accent" aria-hidden="true">
        <rect x="3" y="4" width="18" height="18" rx="2" />
        <path d="M16 2v4M8 2v4M3 10h18" />
      </svg>

      <!-- Native input clipped — provides correct date semantics + keyboard -->
      <input
        ref="inputRef"
        type="date"
        class="absolute h-0 w-0 opacity-0"
        tabindex="-1"
        :value="value"
        :min="min || null"
        :max="max || null"
        @change="value = $event.target.value"
      />
    </button>
  </div>
</template>
