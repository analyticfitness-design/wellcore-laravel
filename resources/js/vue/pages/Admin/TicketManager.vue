<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const tickets = ref([]);
const filter = ref('all');

// Drawer state
const showDrawer = ref(false);
const selectedTicket = ref(null);
const replyText = ref('');
const savingReply = ref(false);
const savingStatus = ref(false);
const replyError = ref(null);
const replySuccess = ref(false);

const filterOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'open', label: 'Abiertos' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'resolved', label: 'Resueltos' },
    { value: 'closed', label: 'Cerrados' },
];

const statusOptions = [
    { value: 'open', label: 'Abierto' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'resolved', label: 'Resuelto' },
    { value: 'closed', label: 'Cerrado' },
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

function openDrawer(ticket) {
    selectedTicket.value = { ...ticket };
    replyText.value = '';
    replyError.value = null;
    replySuccess.value = false;
    showDrawer.value = true;
}

function closeDrawer() {
    showDrawer.value = false;
    // Delay clearing so the slide-out animation completes
    setTimeout(() => {
        selectedTicket.value = null;
        replyText.value = '';
        replyError.value = null;
        replySuccess.value = false;
    }, 300);
}

async function submitReply() {
    if (!selectedTicket.value || !replyText.value.trim()) return;
    savingReply.value = true;
    replyError.value = null;
    replySuccess.value = false;
    try {
        const response = await api.post(`/api/v/admin/tickets/${selectedTicket.value.id}/reply`, {
            response: replyText.value.trim(),
        });
        const updated = response.data.ticket;
        // Update in list reactively
        const idx = tickets.value.findIndex(t => t.id === updated.id);
        if (idx !== -1) {
            tickets.value[idx] = { ...tickets.value[idx], ...updated };
        }
        // Update selected ticket in drawer
        selectedTicket.value = { ...selectedTicket.value, ...updated };
        replyText.value = '';
        replySuccess.value = true;
        setTimeout(() => { replySuccess.value = false; }, 3000);
    } catch (err) {
        if (err.response?.status === 422) {
            const errs = err.response.data.errors;
            replyError.value = errs?.response?.[0] || 'Error de validacion.';
        } else {
            replyError.value = err.response?.data?.message || 'Error al enviar respuesta.';
        }
    } finally {
        savingReply.value = false;
    }
}

async function changeStatus(newStatus) {
    if (!selectedTicket.value || newStatus === selectedTicket.value.status) return;
    savingStatus.value = true;
    try {
        const response = await api.patch(`/api/v/admin/tickets/${selectedTicket.value.id}/status`, {
            status: newStatus,
        });
        const updated = response.data.ticket;
        // Update in list reactively
        const idx = tickets.value.findIndex(t => t.id === updated.id);
        if (idx !== -1) {
            tickets.value[idx] = { ...tickets.value[idx], ...updated };
        }
        // Update drawer
        selectedTicket.value = { ...selectedTicket.value, ...updated };
    } catch (err) {
        // Silently fail — status stays unchanged visually
    } finally {
        savingStatus.value = false;
    }
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

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        return new Date(dateStr).toLocaleDateString('es-CO', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
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
          @click="openDrawer(ticket)"
          class="flex cursor-pointer items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/30 hover:bg-wc-bg-secondary hover:shadow-sm"
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
            <!-- Arrow hint -->
            <svg class="h-4 w-4 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">Sin tickets de soporte</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Los tickets de soporte apareceran aqui</p>
      </div>

    </div>

    <!-- ============================================================
         DRAWER — rendered inside AdminLayout teleport-safe container
         ============================================================ -->

    <!-- Backdrop -->
    <Transition name="fade">
      <div
        v-if="showDrawer"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
        @click="closeDrawer"
      />
    </Transition>

    <!-- Drawer panel -->
    <Transition name="slide-right">
      <div
        v-if="showDrawer && selectedTicket"
        class="fixed right-0 top-0 z-50 flex h-full w-full max-w-[480px] flex-col bg-wc-bg-secondary shadow-2xl border-l border-wc-border"
      >
        <!-- Drawer header -->
        <div class="flex items-start justify-between gap-4 border-b border-wc-border px-6 py-5">
          <div class="min-w-0 flex-1">
            <h2 class="font-display text-xl tracking-wide text-wc-text leading-tight">
              {{ selectedTicket.subject || selectedTicket.title || 'Ticket #' + selectedTicket.id }}
            </h2>
            <p class="mt-1 text-xs text-wc-text-tertiary truncate">
              {{ selectedTicket.clientName || selectedTicket.email }}
              <span v-if="selectedTicket.date || selectedTicket.created_at">
                &middot; {{ formatDate(selectedTicket.date || selectedTicket.created_at) }}
              </span>
            </p>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="getStatusColor(selectedTicket.status)">
                {{ getStatusLabel(selectedTicket.status) }}
              </span>
              <span v-if="selectedTicket.priority" class="rounded-full px-2 py-0.5 text-xs font-medium capitalize" :class="getPriorityColor(selectedTicket.priority)">
                {{ selectedTicket.priority }}
              </span>
            </div>
          </div>
          <button
            @click="closeDrawer"
            class="mt-0.5 shrink-0 rounded-lg p-1.5 text-wc-text-tertiary transition-colors hover:bg-wc-bg-tertiary hover:text-wc-text"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Drawer body — scrollable -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

          <!-- Original message -->
          <div>
            <p class="mb-2 text-[10px] font-semibold tracking-widest text-wc-text-tertiary uppercase">Mensaje</p>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-sm text-wc-text-secondary leading-relaxed whitespace-pre-wrap">
                {{ selectedTicket.message || selectedTicket.body || selectedTicket.description || 'Sin contenido.' }}
              </p>
            </div>
          </div>

          <!-- Existing response (if any) -->
          <div v-if="selectedTicket.response">
            <p class="mb-2 text-[10px] font-semibold tracking-widest text-emerald-500 uppercase">Respuesta actual</p>
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4">
              <p class="text-sm text-wc-text-secondary leading-relaxed whitespace-pre-wrap">{{ selectedTicket.response }}</p>
              <p v-if="selectedTicket.resolved_at" class="mt-2 text-[11px] text-wc-text-tertiary">
                Respondido el {{ formatDate(selectedTicket.resolved_at) }}
              </p>
            </div>
          </div>

          <!-- Change status -->
          <div>
            <p class="mb-2 text-[10px] font-semibold tracking-widest text-wc-text-tertiary uppercase">Cambiar estado</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in statusOptions"
                :key="opt.value"
                @click="changeStatus(opt.value)"
                :disabled="savingStatus"
                :class="[
                  'rounded-full px-3 py-1 text-xs font-medium transition-all',
                  selectedTicket.status === opt.value
                    ? getStatusColor(opt.value) + ' ring-1 ring-current'
                    : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-secondary',
                  savingStatus ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                ]"
              >
                <span v-if="savingStatus && selectedTicket.status !== opt.value" class="inline-block h-2.5 w-2.5 mr-1 animate-spin rounded-full border border-current border-t-transparent" />
                {{ opt.label }}
              </button>
            </div>
          </div>

          <!-- Reply section -->
          <div>
            <p class="mb-2 text-[10px] font-semibold tracking-widest text-wc-text-tertiary uppercase">Responder</p>

            <!-- Success toast -->
            <Transition name="fade">
              <div v-if="replySuccess" class="mb-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-medium text-emerald-500">
                Respuesta enviada correctamente.
              </div>
            </Transition>

            <!-- Error -->
            <p v-if="replyError" class="mb-2 text-xs text-red-400">{{ replyError }}</p>

            <textarea
              v-model="replyText"
              rows="5"
              placeholder="Escribe tu respuesta (minimo 5 caracteres)..."
              class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary outline-none transition-colors focus:border-wc-accent/50 focus:ring-0"
              :class="replyError ? 'border-red-400/50' : ''"
            />
            <div class="mt-3 flex items-center justify-between">
              <p class="text-xs text-wc-text-tertiary">
                {{ replyText.length }} / 5000 caracteres
              </p>
              <button
                @click="submitReply"
                :disabled="savingReply || replyText.trim().length < 5"
                class="flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white transition-opacity disabled:opacity-50"
                :class="savingReply || replyText.trim().length < 5 ? 'cursor-not-allowed' : 'hover:opacity-90'"
              >
                <svg v-if="savingReply" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ savingReply ? 'Enviando...' : 'Enviar respuesta' }}
              </button>
            </div>
          </div>

        </div><!-- /scrollable body -->
      </div>
    </Transition>

  </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-right-enter-from,
.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
