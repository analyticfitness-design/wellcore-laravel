<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useApi } from '../composables/useApi';

const props = defineProps({
    // Days since coach account creation — if >= 7, widget auto-hides
    daysOld: {
        type: Number,
        default: null,
    },
    // Force show (for preview / QA)
    force: {
        type: Boolean,
        default: false,
    },
});

const DISMISS_KEY = 'coach_onboarding_dismissed';
const ITEMS_KEY = 'coach_onboarding_items';
const CELEBRATE_KEY = 'coach_onboarding_celebrated';

const api = useApi();

// Checklist items — id matches persisted key
const checklist = [
    {
        id: 'brand',
        label: 'Visita Mi Marca y personaliza tu branding',
        to: '/coach/brand',
        cta: 'Configurar marca',
    },
    {
        id: 'clients',
        label: 'Revisa tus clientes asignados',
        to: '/coach/clients',
        cta: 'Ver clientes',
    },
    {
        id: 'ticket',
        label: 'Crea tu primer ticket de plan',
        to: '/coach/plan-tickets/nuevo',
        cta: 'Crear ticket',
    },
    {
        id: 'checkin',
        label: 'Responde tu primer check-in',
        to: '/coach/checkins',
        cta: 'Ir a check-ins',
    },
];

const dismissed = ref(localStorage.getItem(DISMISS_KEY) === '1');
const celebrated = ref(localStorage.getItem(CELEBRATE_KEY) === '1');
const completed = ref(loadCompleted());
const celebrationVisible = ref(false);

function loadCompleted() {
    try {
        const raw = localStorage.getItem(ITEMS_KEY);
        if (!raw) return {};
        const parsed = JSON.parse(raw);
        return typeof parsed === 'object' && parsed !== null ? parsed : {};
    } catch {
        return {};
    }
}

function saveCompleted() {
    try {
        localStorage.setItem(ITEMS_KEY, JSON.stringify(completed.value));
    } catch {
        // ignore quota errors
    }
}

const completedCount = computed(() => checklist.filter(i => completed.value[i.id]).length);
const progressPct = computed(() => Math.round((completedCount.value / checklist.length) * 100));
const allDone = computed(() => completedCount.value === checklist.length);

// Visibility rules:
// - force=true → always visible (for QA)
// - dismissed → hidden
// - daysOld >= 7 AND not all done AND not celebrating → hidden (user past onboarding window)
// - daysOld = null (no backend data yet) → show by default (assume onboarding)
const visible = computed(() => {
    if (props.force) return true;
    if (dismissed.value) return false;
    if (celebrationVisible.value) return true;
    if (props.daysOld !== null && props.daysOld >= 7 && !allDone.value) return false;
    return true;
});

function markItem(id) {
    if (completed.value[id]) return;
    completed.value = { ...completed.value, [id]: Date.now() };
    saveCompleted();
}

function dismiss() {
    dismissed.value = true;
    try { localStorage.setItem(DISMISS_KEY, '1'); } catch {}
}

// Auto-detect completions from API state (runs once on mount)
async function autoDetectCompletions() {
    try {
        // If coach has created a plan ticket → mark 'ticket'
        const tr = await api.get('/api/v/coach/plan-tickets');
        if ((tr.data?.tickets || []).length > 0) markItem('ticket');
    } catch {}

    try {
        // If coach has answered at least one checkin → mark 'checkin'
        const cr = await api.get('/api/v/coach/checkins/answered-count');
        if ((cr.data?.count || 0) > 0) markItem('checkin');
    } catch {
        // endpoint may not exist — silent
    }

    try {
        // If coach has a brand configured → mark 'brand'
        const br = await api.get('/api/v/coach/brand');
        const b = br.data || {};
        if (b.nombre_comercial || b.logo_url || b.primary_color) markItem('brand');
    } catch {}
}

// Watch for all-done transition → trigger celebration and auto-dismiss after 5s
watch(allDone, (done) => {
    if (done && !celebrated.value) {
        celebrated.value = true;
        celebrationVisible.value = true;
        try { localStorage.setItem(CELEBRATE_KEY, '1'); } catch {}
        setTimeout(() => {
            celebrationVisible.value = false;
            dismiss();
        }, 5000);
    }
});

onMounted(() => {
    autoDetectCompletions();
});
</script>

<template>
  <Transition name="onboard-fade">
    <section
      v-if="visible"
      role="region"
      aria-labelledby="coach-onboarding-title"
      class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-5"
    >
      <!-- Subtle accent glow -->
      <div aria-hidden="true" class="pointer-events-none absolute -top-16 -right-16 h-40 w-40 rounded-full bg-wc-accent/10 blur-3xl"></div>

      <!-- Celebration state -->
      <div v-if="celebrationVisible" class="relative flex items-center gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="font-display text-lg tracking-wide text-wc-text">Onboarding completo</p>
          <p class="text-sm text-wc-text-secondary">Sigue creciendo. Este aviso se cerrara en unos segundos.</p>
        </div>
      </div>

      <!-- Checklist state -->
      <div v-else class="relative">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 id="coach-onboarding-title" class="font-display text-lg tracking-wide text-wc-text">
              Tu primer dia en WellCore
            </h2>
            <p class="mt-0.5 text-sm text-wc-text-secondary">Completa estos pasos para arrancar fuerte.</p>
          </div>
          <button
            type="button"
            @click="dismiss"
            aria-label="Cerrar checklist de onboarding"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Items -->
        <ul class="mt-4 space-y-2">
          <li v-for="item in checklist" :key="item.id">
            <RouterLink
              :to="item.to"
              @click="markItem(item.id)"
              :class="[
                'group flex items-center gap-3 rounded-lg border px-3 py-2.5 transition-colors',
                completed[item.id]
                  ? 'border-emerald-500/30 bg-emerald-500/5'
                  : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40 hover:bg-wc-accent/5'
              ]"
            >
              <!-- Checkbox / check icon -->
              <span
                :class="[
                  'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border transition-colors',
                  completed[item.id]
                    ? 'border-emerald-500 bg-emerald-500 text-white'
                    : 'border-wc-border bg-wc-bg group-hover:border-wc-accent'
                ]"
                :aria-hidden="true"
              >
                <svg v-if="completed[item.id]" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </span>
              <span
                :class="[
                  'flex-1 text-sm font-medium',
                  completed[item.id] ? 'text-wc-text-secondary line-through' : 'text-wc-text'
                ]"
              >{{ item.label }}</span>
              <span class="hidden shrink-0 text-xs font-semibold text-wc-accent group-hover:underline sm:inline">{{ item.cta }}</span>
              <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
              </svg>
            </RouterLink>
          </li>
        </ul>

        <!-- Progress bar -->
        <div class="mt-4 flex items-center gap-3">
          <div class="relative h-1.5 flex-1 overflow-hidden rounded-full bg-wc-bg-tertiary" role="progressbar" :aria-valuenow="progressPct" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-wc-accent transition-all duration-500 ease-out" :style="{ width: progressPct + '%' }"></div>
          </div>
          <span class="shrink-0 font-data text-xs font-semibold text-wc-text-secondary">{{ completedCount }}/{{ checklist.length }}</span>
        </div>
      </div>
    </section>
  </Transition>
</template>

<style scoped>
.onboard-fade-enter-active,
.onboard-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.onboard-fade-enter-from,
.onboard-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
