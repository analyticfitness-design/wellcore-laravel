<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const tickets = ref([]);
const activeStatus = ref('all');

const STATUS_TABS = [
  { key: 'all', label: 'Todos' },
  { key: 'borrador', label: 'Borradores' },
  { key: 'pendiente', label: 'Enviados' },
  { key: 'en_revision', label: 'En revision' },
  { key: 'completado', label: 'Completados' },
  { key: 'rechazado', label: 'Rechazados' },
];

const PLAN_TYPE_META = {
  esencial: { label: 'Esencial', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  metodo: { label: 'Metodo', bg: 'bg-orange-500/10', text: 'text-orange-500' },
  elite: { label: 'Elite', bg: 'bg-wc-accent/10', text: 'text-wc-accent' },
};

const STATUS_META = {
  borrador: { label: 'Borrador', bg: 'bg-wc-bg-secondary', text: 'text-wc-text-tertiary' },
  pendiente: { label: 'Pendiente', bg: 'bg-yellow-500/10', text: 'text-yellow-500' },
  en_revision: { label: 'En revision', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  completado: { label: 'Completado', bg: 'bg-emerald-500/10', text: 'text-emerald-500' },
  rechazado: { label: 'Rechazado', bg: 'bg-red-500/10', text: 'text-red-400' },
};

const counts = computed(() => {
  const out = { all: tickets.value.length, borrador: 0, pendiente: 0, en_revision: 0, completado: 0, rechazado: 0 };
  for (const t of tickets.value) {
    if (out[t.status] !== undefined) out[t.status]++;
  }
  return out;
});

const filteredTickets = computed(() => {
  if (activeStatus.value === 'all') return tickets.value;
  return tickets.value.filter(t => t.status === activeStatus.value);
});

async function fetchTickets() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v/coach/plan-tickets');
    tickets.value = data.tickets || [];
  } catch (e) {
    tickets.value = [];
  } finally {
    loading.value = false;
  }
}

function formatDate(d) {
  if (!d) return '-';
  try {
    return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch {
    return d;
  }
}

function goToNew() {
  router.push('/coach/plan-tickets/nuevo');
}

function goToTicket(id) {
  router.push(`/coach/plan-tickets/${id}`);
}

function planTypeMeta(t) {
  return PLAN_TYPE_META[t] || PLAN_TYPE_META.esencial;
}

function statusMeta(s) {
  return STATUS_META[s] || STATUS_META.borrador;
}

onMounted(fetchTickets);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TICKETS DE PLAN</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Briefs estructurados para el equipo WellCore.</p>
        </div>
        <button
          @click="goToNew"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Crear nuevo ticket
        </button>
      </div>

      <!-- Status tabs -->
      <div class="flex flex-wrap items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
        <button
          v-for="tab in STATUS_TABS"
          :key="tab.key"
          @click="activeStatus = tab.key"
          class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors flex items-center gap-1.5"
          :class="activeStatus === tab.key ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
        >
          {{ tab.label }}
          <span
            class="rounded-full px-1.5 text-[10px] font-semibold"
            :class="activeStatus === tab.key ? 'bg-white/20 text-white' : 'bg-wc-bg-tertiary text-wc-text-tertiary'"
          >{{ counts[tab.key] ?? 0 }}</span>
        </button>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div v-for="n in 3" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-24"></div>
      </template>

      <!-- Empty -->
      <div v-else-if="filteredTickets.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-secondary">Aun no has creado tickets. Empieza uno para tu primer cliente.</p>
        <button
          @click="goToNew"
          class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition"
        >Crear nuevo ticket</button>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <button
          v-for="t in filteredTickets"
          :key="t.id"
          @click="goToTicket(t.id)"
          class="w-full text-left rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 hover:border-wc-accent/40 transition"
        >
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <p class="font-display text-lg tracking-wide text-wc-text truncate">{{ t.client_name || 'Cliente sin nombre' }}</p>
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[planTypeMeta(t.plan_type).bg, planTypeMeta(t.plan_type).text]"
                >{{ planTypeMeta(t.plan_type).label }}</span>
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[statusMeta(t.status).bg, statusMeta(t.status).text]"
                >{{ statusMeta(t.status).label }}</span>
              </div>
              <div class="mt-2 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <span>Creado {{ formatDate(t.created_at) }}</span>
                <span v-if="t.submitted_at">- Enviado {{ formatDate(t.submitted_at) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <span class="inline-flex items-center gap-1 text-xs font-semibold text-wc-accent">
                {{ t.is_editable ? 'Editar' : 'Ver' }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </span>
            </div>
          </div>
        </button>
      </div>
    </div>
  </CoachLayout>
</template>
