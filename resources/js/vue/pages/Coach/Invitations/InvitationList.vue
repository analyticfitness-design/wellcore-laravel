<script setup>
import { computed } from 'vue';
import { useInvitationsStore } from '../../../stores/invitationsStore';
import InvitationStatusBadge from './InvitationStatusBadge.vue';

const emit = defineEmits(['new-invitation']);

const store = useInvitationsStore();

const PLAN_LABELS = {
    esencial: 'Esencial',
    metodo:   'Metodo',
    elite:    'Elite',
};

const STATUS_OPTIONS = [
    { value: '',             label: 'Todos los estados' },
    { value: 'sent',         label: 'Enviadas' },
    { value: 'opened',       label: 'Abiertas' },
    { value: 'link_clicked', label: 'Link visitado' },
    { value: 'paid',         label: 'Pagadas' },
    { value: 'expired',      label: 'Expiradas' },
    { value: 'cancelled',    label: 'Canceladas' },
    { value: 'failed',       label: 'Fallidas' },
];

const PLAN_OPTIONS = [
    { value: '',         label: 'Todos los planes' },
    { value: 'esencial', label: 'Esencial' },
    { value: 'metodo',   label: 'Metodo' },
    { value: 'elite',    label: 'Elite' },
];

const TERMINAL_STATUSES = ['paid', 'expired', 'cancelled', 'failed'];

function isTerminal(status) {
    return TERMINAL_STATUSES.includes(status);
}

function canResend(inv) {
    return !isTerminal(inv.status);
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function formatAmount(amount) {
    if (!amount) return '—';
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(amount);
}

async function handleResend(inv) {
    try {
        await store.resendInvitation(inv.id);
    } catch {
        // silent — UI stays unchanged
    }
}

async function handleCancel(inv) {
    if (!confirm(`Cancelar la invitacion de ${inv.email}? Esta accion no se puede deshacer.`)) return;
    try {
        await store.cancelInvitation(inv.id);
    } catch {
        // silent
    }
}

function applyFilters() {
    store.filters.page = 1;
    store.fetchInvitations();
}

function prevPage() {
    if (store.filters.page > 1) {
        store.filters.page--;
        store.fetchInvitations();
    }
}

function nextPage() {
    const total = store.pagination?.last_page ?? store.pagination?.total_pages ?? 1;
    if (store.filters.page < total) {
        store.filters.page++;
        store.fetchInvitations();
    }
}

const hasNextPage = computed(() => {
    if (!store.pagination) return false;
    const total = store.pagination.last_page ?? store.pagination.total_pages ?? 1;
    return store.filters.page < total;
});

const hasPrevPage = computed(() => store.filters.page > 1);
</script>

<template>
  <div class="space-y-5">

    <!-- Stats row -->
    <div class="flex flex-wrap items-center gap-2">
      <span class="rounded-full bg-zinc-700 px-3 py-1 text-xs font-semibold text-zinc-300">
        Enviadas: <span class="font-data ml-1">{{ store.stats.sent }}</span>
      </span>
      <span class="rounded-full bg-blue-900/50 px-3 py-1 text-xs font-semibold text-blue-300">
        Abiertas: <span class="font-data ml-1">{{ store.stats.opened }}</span>
      </span>
      <span class="rounded-full bg-amber-900/50 px-3 py-1 text-xs font-semibold text-amber-300">
        Link visitado: <span class="font-data ml-1">{{ store.stats.linkClicked }}</span>
      </span>
      <span class="rounded-full bg-green-900/50 px-3 py-1 text-xs font-semibold text-green-300">
        Pagadas: <span class="font-data ml-1">{{ store.stats.paid }}</span>
      </span>
      <span class="rounded-full bg-red-900/50 px-3 py-1 text-xs font-semibold text-red-300">
        Expiradas: <span class="font-data ml-1">{{ store.stats.expired }}</span>
      </span>
      <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold text-zinc-500">
        Canceladas: <span class="font-data ml-1">{{ store.stats.cancelled }}</span>
      </span>
    </div>

    <!-- Filters + new button -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-2">
        <select
          v-model="store.filters.status"
          @change="applyFilters"
          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
        >
          <option v-for="opt in STATUS_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
        <select
          v-model="store.filters.plan"
          @change="applyFilters"
          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
        >
          <option v-for="opt in PLAN_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
      <button
        @click="emit('new-invitation')"
        class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nueva invitacion
      </button>
    </div>

    <!-- Loading skeleton -->
    <template v-if="store.loading">
      <div
        v-for="n in 5"
        :key="n"
        class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-14"
      ></div>
    </template>

    <!-- Empty state -->
    <div
      v-else-if="!store.loading && store.invitations.length === 0"
      class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center"
    >
      <svg class="mx-auto mb-4 h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
      </svg>
      <p class="text-sm font-medium text-wc-text">Aun no has enviado invitaciones</p>
      <p class="mt-1 text-xs text-wc-text-tertiary">Envia tu primera invitacion haciendo clic en "Nueva invitacion".</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-hidden rounded-xl border border-wc-border">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-wc-border bg-wc-bg-tertiary">
            <tr>
              <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Correo / Nombre</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
              <th class="hidden px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary md:table-cell">Expira</th>
              <th class="hidden px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary lg:table-cell">Enviada</th>
              <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-wc-border bg-wc-bg-secondary">
            <tr
              v-for="inv in store.invitations"
              :key="inv.id"
              class="hover:bg-wc-bg-tertiary transition-colors"
            >
              <!-- Email / Name -->
              <td class="px-4 py-3">
                <p class="font-medium text-wc-text">{{ inv.email }}</p>
                <p v-if="inv.name" class="text-xs text-wc-text-tertiary">{{ inv.name }}</p>
              </td>

              <!-- Plan + Amount -->
              <td class="px-4 py-3">
                <p class="text-wc-text">{{ PLAN_LABELS[inv.plan] ?? inv.plan }}</p>
                <p class="text-xs text-wc-text-tertiary font-data">{{ formatAmount(inv.amount) }}</p>
              </td>

              <!-- Status -->
              <td class="px-4 py-3">
                <InvitationStatusBadge :status="inv.status" />
                <p v-if="inv.resend_count > 0" class="mt-1 text-[10px] text-wc-text-tertiary">
                  Reenviada {{ inv.resend_count }}x
                </p>
              </td>

              <!-- Expires at (md+) -->
              <td class="hidden px-4 py-3 text-xs text-wc-text-secondary md:table-cell">
                {{ formatDate(inv.expires_at) }}
              </td>

              <!-- Sent at (lg+) -->
              <td class="hidden px-4 py-3 text-xs text-wc-text-secondary lg:table-cell">
                {{ formatDate(inv.sent_at) }}
              </td>

              <!-- Actions -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <!-- Resend -->
                  <button
                    v-if="canResend(inv)"
                    @click="handleResend(inv)"
                    title="Reenviar invitacion"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                  </button>

                  <!-- Cancel -->
                  <button
                    v-if="!isTerminal(inv.status)"
                    @click="handleCancel(inv)"
                    title="Cancelar invitacion"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-400 transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!store.loading && store.pagination && store.invitations.length > 0" class="flex items-center justify-between">
      <p class="text-xs text-wc-text-tertiary">
        Pagina <span class="font-data font-semibold text-wc-text">{{ store.filters.page }}</span>
        <template v-if="store.pagination.last_page ?? store.pagination.total_pages">
          de <span class="font-data font-semibold text-wc-text">{{ store.pagination.last_page ?? store.pagination.total_pages }}</span>
        </template>
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="prevPage"
          :disabled="!hasPrevPage"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-zinc-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        <button
          @click="nextPage"
          :disabled="!hasNextPage"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-zinc-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>
    </div>

  </div>
</template>
