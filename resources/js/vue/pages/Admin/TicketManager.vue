<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const tickets = ref([]);
const filter = ref('all');

const filterOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'open', label: 'Abiertos' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'resolved', label: 'Resueltos' },
    { value: 'closed', label: 'Cerrados' },
];

async function fetchTickets() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/tickets', {
            params: { status: filter.value !== 'all' ? filter.value : undefined },
        });
        tickets.value = response.data.tickets || response.data.data || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar tickets';
    } finally {
        loading.value = false;
    }
}

function applyFilter(val) {
    filter.value = val;
    fetchTickets();
}

function getStatusColor(status) {
    const map = {
        open: 'bg-amber-500/10 text-amber-500',
        in_progress: 'bg-sky-500/10 text-sky-500',
        resolved: 'bg-emerald-500/10 text-emerald-500',
        closed: 'bg-gray-500/10 text-gray-400',
    };
    return map[status] || 'bg-gray-500/10 text-gray-400';
}

function getStatusLabel(status) {
    const map = { open: 'Abierto', in_progress: 'En progreso', resolved: 'Resuelto', closed: 'Cerrado' };
    return map[status] || status;
}

function getPriorityColor(priority) {
    const map = {
        high: 'bg-red-500/10 text-red-500',
        medium: 'bg-amber-500/10 text-amber-500',
        low: 'bg-emerald-500/10 text-emerald-500',
    };
    return map[priority] || 'bg-gray-500/10 text-gray-400';
}

onMounted(() => {
    fetchTickets();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Tickets de Soporte</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona las solicitudes de soporte</p>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="opt in filterOptions"
          :key="opt.value"
          @click="applyFilter(opt.value)"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
            filter === opt.value ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-secondary'
          ]"
        >
          {{ opt.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 6" :key="i" class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchTickets" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Tickets List -->
      <div v-else-if="tickets.length" class="space-y-2">
        <div
          v-for="ticket in tickets"
          :key="ticket.id"
          class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-colors hover:bg-wc-bg-secondary"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-bg-secondary">
            <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-wc-text truncate">{{ ticket.subject || ticket.title }}</p>
            <p class="text-xs text-wc-text-tertiary truncate">{{ ticket.clientName || ticket.email }} &middot; {{ ticket.date || ticket.created_at }}</p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <span v-if="ticket.priority" class="rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="getPriorityColor(ticket.priority)">{{ ticket.priority }}</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="getStatusColor(ticket.status)">{{ getStatusLabel(ticket.status) }}</span>
          </div>
        </div>
      </div>

      <!-- Empty / Coming Soon -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">Sin tickets de soporte</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Los tickets de soporte apareceran aqui</p>
      </div>

    </div>
  </AdminLayout>
</template>
