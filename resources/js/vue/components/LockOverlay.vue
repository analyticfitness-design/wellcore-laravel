<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { usePlanLock } from '../composables/usePlanLock';

const props = defineProps({
  planType: { type: String, default: null },
  // Optional override for the formatted expiry date. If not passed,
  // the overlay reads it from usePlanLock().expiresAt.
  expiresAt: { type: String, default: null },
});

const router = useRouter();
const { expiresAt: lockExpiresAt, planType: lockPlanType } = usePlanLock();

const effectiveExpiresAt = computed(() => props.expiresAt || lockExpiresAt.value);
const effectivePlanType = computed(() => props.planType || lockPlanType.value);

const MONTHS_ES = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

const formattedExpiry = computed(() => {
  const raw = effectiveExpiresAt.value;
  if (!raw) return null;
  // Accept 'YYYY-MM-DD' or ISO datetime — avoid Date timezone surprises for the date-only case.
  const dateMatch = /^(\d{4})-(\d{2})-(\d{2})/.exec(raw);
  if (!dateMatch) return null;
  const [, y, m, d] = dateMatch;
  const month = MONTHS_ES[Math.max(0, Math.min(11, Number(m) - 1))];
  return `Expiro el ${Number(d)} de ${month} de ${y}`;
});

const subtitle = computed(() => {
  const pt = (effectivePlanType.value || '').toLowerCase();
  if (pt === 'rise') return 'Renueva para continuar tu programa RISE con WellCore';
  if (pt === 'elite') return 'Renueva para continuar con tu plan Elite y tu coach';
  return 'Renueva para continuar tu transformacion con WellCore';
});

function goToRenew() {
  router.push('/renovar');
}

function goToDashboard() {
  router.push('/client');
}
</script>

<template>
  <Transition name="fade" appear>
    <div
      class="absolute inset-0 z-40 flex items-center justify-center overflow-hidden rounded-2xl"
      role="dialog"
      aria-modal="true"
      aria-labelledby="lock-overlay-title"
    >
      <!-- Backdrop blurs the underlying page -->
      <div class="absolute inset-0 bg-wc-bg/85 backdrop-blur-md"></div>

      <!-- Card -->
      <div class="relative mx-4 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 text-center shadow-[0_20px_60px_-20px_rgba(0,0,0,0.6)]">
        <!-- Lock icon -->
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-wc-accent/30 bg-wc-accent/10">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
               class="h-10 w-10 text-wc-accent" aria-hidden="true">
            <rect x="4" y="11" width="16" height="10" rx="2" />
            <path d="M8 11V7a4 4 0 1 1 8 0v4" />
            <circle cx="12" cy="16" r="1.2" fill="currentColor" stroke="none" />
          </svg>
        </div>

        <!-- Title -->
        <h2 id="lock-overlay-title" class="mb-2 font-display text-3xl uppercase tracking-wide text-wc-text">
          Tu plan expiro
        </h2>

        <!-- Subtitle -->
        <p class="mb-5 text-sm text-wc-text-secondary">
          {{ subtitle }}
        </p>

        <!-- Expiry date -->
        <div v-if="formattedExpiry" class="mb-8 inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-xs font-medium text-red-400">
          <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
          {{ formattedExpiry }}
        </div>

        <!-- Primary CTA -->
        <button
          type="button"
          @click="goToRenew"
          class="mb-3 flex w-full items-center justify-center gap-2 rounded-xl bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 focus:outline-none focus:ring-2 focus:ring-wc-accent/40"
        >
          Renovar ahora
          <span aria-hidden="true">&rarr;</span>
        </button>

        <!-- Secondary CTA -->
        <button
          type="button"
          @click="goToDashboard"
          class="block w-full rounded-xl border border-wc-border bg-transparent py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text"
        >
          Volver al dashboard
        </button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
