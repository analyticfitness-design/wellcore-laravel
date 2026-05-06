<script setup>
/**
 * PhotosHero — editorial hero for /client/photos.
 * Title + 3 stats (sesiones / semanas activas / última) + "próxima sesión" hint.
 *
 * Props:
 *   sessionCount: number     total sessions
 *   weekCount:    number     sessions in last 7d
 *   latestDate:   string     'YYYY-MM-DD' or empty
 *   nextSuggested: string    pre-formatted text ("en 5 días — domingo 17 may")
 *
 * Stat values are rendered with font-data (Barlow) for tabular cleanliness.
 */
import { computed } from 'vue';

const props = defineProps({
  sessionCount: { type: Number, default: 0 },
  weekCount: { type: Number, default: 0 },
  latestDate: { type: String, default: '' },
  nextSuggested: { type: String, default: '' },
});

const MESES_SHORT = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

const latestParts = computed(() => {
  if (!props.latestDate) return { day: '—', month: '' };
  const [y, m, d] = props.latestDate.split('-').map(Number);
  if (!y || !m || !d) return { day: '—', month: '' };
  return {
    day: String(d).padStart(2, '0'),
    month: MESES_SHORT[m - 1] || '',
  };
});

const padNum = (n) => String(n ?? 0).padStart(2, '0');
</script>

<template>
  <section class="space-y-5">
    <div>
      <div class="mb-2 inline-flex items-center gap-2 text-[10px] uppercase tracking-widest text-wc-text-tertiary">
        <span class="h-1.5 w-1.5 rounded-full bg-wc-accent" aria-hidden="true"></span>
        Fotos de progreso
      </div>
      <h1 class="font-display text-4xl font-medium uppercase leading-[0.95] tracking-tight text-wc-text sm:text-6xl">
        Tu cuerpo,<br />
        <em class="font-normal not-italic text-wc-text-tertiary">semana a semana.</em>
      </h1>
      <p class="mt-4 max-w-[46ch] text-[15px] leading-relaxed text-wc-text-secondary sm:text-[17px]">
        Estas fotos cuentan una historia que el peso no puede contar. Suben aquí solo para tu coach Marina — privadas, encriptadas, tuyas.
      </p>
    </div>

    <div class="grid grid-cols-3 gap-2 rounded-2xl border border-wc-border bg-wc-bg-tertiary p-3.5 sm:gap-4 sm:p-4">
      <div class="flex min-w-0 flex-col gap-0.5">
        <span class="font-display text-[9px] font-medium uppercase tracking-widest text-wc-text-tertiary">Sesiones</span>
        <span class="font-display text-2xl font-medium leading-none text-wc-text tabular-nums sm:text-[38px]">
          {{ padNum(sessionCount) }}
        </span>
      </div>
      <div class="flex min-w-0 flex-col gap-0.5">
        <span class="font-display text-[9px] font-medium uppercase tracking-widest text-wc-text-tertiary">Semanas</span>
        <span class="font-display text-2xl font-medium leading-none text-wc-text tabular-nums sm:text-[38px]">
          {{ padNum(weekCount) }}<small class="ml-1 text-[10px] font-normal text-wc-text-tertiary">sem</small>
        </span>
      </div>
      <div class="flex min-w-0 flex-col gap-0.5">
        <span class="font-display text-[9px] font-medium uppercase tracking-widest text-wc-text-tertiary">Última</span>
        <span class="font-display text-2xl font-medium leading-none text-wc-text tabular-nums sm:text-[38px]">
          {{ latestParts.day }}<small class="ml-1 text-[10px] font-normal text-wc-text-tertiary">{{ latestParts.month }}</small>
        </span>
      </div>
    </div>

    <div
      v-if="nextSuggested"
      class="flex items-center gap-2 rounded-xl border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-xs text-wc-text-secondary"
    >
      <svg viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5 shrink-0 text-wc-accent" aria-hidden="true">
        <path d="M12 2c1 4 4 5 4 9a4 4 0 1 1-8 0c0-2 1-3 1-5 2 1 3 0 3-4z" />
      </svg>
      <span><strong class="font-semibold text-wc-text">Próxima sesión</strong> {{ nextSuggested }}</span>
    </div>
  </section>
</template>
