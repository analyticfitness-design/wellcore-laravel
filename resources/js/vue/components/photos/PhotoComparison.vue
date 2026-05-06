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
import { usePhotoComparison } from '../../composables/usePhotoComparison';
import CompareCell from './CompareCell.vue';

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
const ANGLE_LABELS = { frente: 'Frente', perfil: 'Perfil', espalda: 'Espalda' };

const dateOptions = computed(() =>
  (props.sessions || []).map((s) => ({ value: s.date, label: formatLabel(s.date) }))
);

const MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
function formatLabel(date) {
  if (!date) return '';
  const d = new Date(date + 'T12:00:00');
  if (isNaN(d.getTime())) return date;
  return `${String(d.getDate()).padStart(2, '0')} ${MESES[d.getMonth()]} ${d.getFullYear()}`;
}
</script>

<template>
  <section class="space-y-4" aria-label="Comparativa de fotos">
    <!-- Controls -->
    <div class="flex flex-wrap items-end gap-2">
      <label class="flex flex-col gap-1">
        <span class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">Antes</span>
        <select
          :value="compare.fromDate.value"
          @change="compare.setFromDate($event.target.value)"
          class="min-h-[44px] rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-display text-sm uppercase tracking-wider text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
        >
          <option value="">—</option>
          <option v-for="opt in dateOptions" :key="'a-' + opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </label>

      <button
        type="button"
        class="flex h-11 w-11 items-center justify-center rounded-full border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
        aria-label="Intercambiar fechas"
        @click="compare.swap()"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
          <path d="M7 4l-3 3 3 3M4 7h16M17 14l3 3-3 3M20 17H4" />
        </svg>
      </button>

      <label class="flex flex-col gap-1">
        <span class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">Hoy</span>
        <select
          :value="compare.toDate.value"
          @change="compare.setToDate($event.target.value)"
          class="min-h-[44px] rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-display text-sm uppercase tracking-wider text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
        >
          <option value="">—</option>
          <option v-for="opt in dateOptions" :key="'b-' + opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </label>
    </div>

    <!-- Empty hint -->
    <div
      v-if="!compare.ready.value"
      class="rounded-xl border border-dashed border-wc-border bg-wc-bg-tertiary p-6 text-center text-sm text-wc-text-secondary"
    >
      Elige dos sesiones para comparar.
    </div>

    <!-- Grid: 3 rows × 2 cols -->
    <div v-else class="space-y-3">
      <div
        v-for="angle in ANGLES"
        :key="angle"
        class="space-y-1.5"
      >
        <div class="flex items-center justify-between px-1">
          <h4 class="font-display text-xs font-semibold uppercase tracking-wider text-wc-text">
            {{ ANGLE_LABELS[angle] }}
          </h4>
          <small class="font-mono text-[10px] uppercase tracking-widest text-wc-text-tertiary">
            comparativa
          </small>
        </div>
        <div class="relative grid grid-cols-2 gap-0 overflow-hidden rounded-xl border border-wc-border">
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
          <div class="pointer-events-none absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-wc-border-strong/40" aria-hidden="true"></div>
        </div>
      </div>
    </div>
  </section>
</template>
