<script setup>
/**
 * PhotoComparison — side-by-side pre/post viewer. 3 rows (frente/perfil/
 * espalda), 2 columns (sesión A vs sesión B), date pickers + swap button.
 *
 * State is driven by usePhotoComparison composable so selection persists.
 *
 * Props:
 *   sessions:   array of session objects (for the date selectors)
 *
 * No emits — works against the singleton composable.
 */
import { computed, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePhotoComparison } from '../../composables/usePhotoComparison';
import CompareCell from './CompareCell.vue';

const { t, locale } = useI18n();

const props = defineProps({
  sessions: { type: Array, default: () => [] },
});

const compare = usePhotoComparison();

// Auto-pick most recent + oldest on first mount if not set
watchEffect(() => {
  if (!compare.fromDate.value && !compare.toDate.value && props.sessions.length >= 2) {
    compare.setFromDate(props.sessions[props.sessions.length - 1].date); // oldest
    compare.setToDate(props.sessions[0].date); // newest
  }
});

const ANGLES = ['frente', 'perfil', 'espalda'];
const ANGLE_LABELS = computed(() => ({
  frente:  t('client_progress.photos_front'),
  perfil:  t('client_progress.photos_side'),
  espalda: t('client_progress.photos_back'),
}));

const dateOptions = computed(() =>
  (props.sessions || []).map((s) => ({ value: s.date, label: formatLabel(s.date) }))
);

const MESES_ES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
const MESES_EN = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function months() { return locale.value === 'en' ? MESES_EN : MESES_ES; }
function formatLabel(date) {
  if (!date) return '';
  const d = new Date(date + 'T12:00:00');
  if (isNaN(d.getTime())) return date;
  return `${String(d.getDate()).padStart(2, '0')} ${months()[d.getMonth()]} ${d.getFullYear()}`;
}
function formatPill(date) {
  if (!date) return '—';
  const d = new Date(date + 'T12:00:00');
  if (isNaN(d.getTime())) return date;
  return `${String(d.getDate()).padStart(2, '0')} ${months()[d.getMonth()].toUpperCase()}`;
}
</script>

<template>
  <section class="space-y-4" :aria-label="t('client_progress.photos_compare_aria')">
    <!-- Controls — editorial pill style matching HTML ref -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Antes pill -->
      <label class="relative inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-3.5 py-2 transition-colors hover:border-wc-accent/40 focus-within:border-wc-accent focus-within:ring-2 focus-within:ring-wc-accent/20">
        <span class="flex flex-col">
          <small class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">{{ t('client_progress.photos_compare_before') }}</small>
          <strong class="font-display text-[13px] font-medium uppercase tracking-wider text-wc-text">
            {{ formatPill(compare.fromDate.value) }}
          </strong>
        </span>
        <select
          :value="compare.fromDate.value"
          @change="compare.setFromDate($event.target.value)"
          class="absolute inset-0 cursor-pointer opacity-0"
          :aria-label="t('client_progress.photos_compare_select_a_aria')"
        >
          <option value="">—</option>
          <option v-for="opt in dateOptions" :key="'a-' + opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </label>

      <span class="text-wc-text-tertiary" aria-hidden="true">→</span>

      <!-- Después pill -->
      <label class="relative inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-3.5 py-2 transition-colors hover:border-wc-accent/40 focus-within:border-wc-accent focus-within:ring-2 focus-within:ring-wc-accent/20">
        <span class="flex flex-col">
          <small class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">{{ t('client_progress.photos_compare_after') }}</small>
          <strong class="font-display text-[13px] font-medium uppercase tracking-wider text-wc-text">
            {{ formatPill(compare.toDate.value) }}
          </strong>
        </span>
        <select
          :value="compare.toDate.value"
          @change="compare.setToDate($event.target.value)"
          class="absolute inset-0 cursor-pointer opacity-0"
          :aria-label="t('client_progress.photos_compare_select_b_aria')"
        >
          <option value="">—</option>
          <option v-for="opt in dateOptions" :key="'b-' + opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </label>

      <button
        type="button"
        class="ml-auto inline-flex min-h-[44px] items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-3.5 text-xs text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        :aria-label="t('client_progress.photos_compare_swap_aria')"
        @click="compare.swap()"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true">
          <path d="M7 4l-3 3 3 3M4 7h16M17 14l3 3-3 3M20 17H4" />
        </svg>
        {{ t('client_progress.photos_compare_swap') }}
      </button>
    </div>

    <!-- Empty hint -->
    <div
      v-if="!compare.ready.value"
      class="rounded-xl border border-dashed border-wc-border bg-wc-bg-tertiary p-6 text-center text-sm text-wc-text-secondary"
    >
      {{ t('client_progress.photos_compare_empty') }}
    </div>

    <!-- Grid: 3 rows × 2 cols on mobile, 3 cols on desktop -->
    <div v-else class="grid gap-4 lg:grid-cols-3 lg:gap-6">
      <div
        v-for="angle in ANGLES"
        :key="angle"
        class="space-y-3"
      >
        <div class="relative grid grid-cols-2 gap-0 overflow-hidden rounded-2xl border border-wc-border">
          <CompareCell
            :photo="compare.sessionA.value?.photos?.[angle]"
            :date="compare.fromDate.value"
            :angle="angle"
            side="a"
          />
          <CompareCell
            :photo="compare.sessionB.value?.photos?.[angle]"
            :date="compare.toDate.value"
            :angle="angle"
            side="b"
          />
          <!-- Vertical divider w/ swap dot — HTML ref -->
          <div class="pointer-events-none absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-white/20" aria-hidden="true"></div>
          <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex h-9 w-9 items-center justify-center rounded-full border-[3px] border-wc-bg bg-white text-black">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5" aria-hidden="true">
                <path d="M7 4l-3 3 3 3M4 7h16M17 14l3 3-3 3M20 17H4" />
              </svg>
            </div>
          </div>
        </div>
        <!-- meta row: title + delta hint -->
        <div class="flex items-center justify-between gap-2 px-1">
          <h4 class="font-display text-[13px] font-semibold uppercase tracking-[0.10em] text-wc-text">
            {{ ANGLE_LABELS[angle] }}
          </h4>
          <small class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">
            {{ t('client_progress.photos_compare_label') }}
          </small>
        </div>
      </div>
    </div>
  </section>
</template>
