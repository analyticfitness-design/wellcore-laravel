<template>
  <ClientLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">SOPORTE</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Envia solicitudes a tu coach. Respuesta garantizada en 48 horas.</p>
        </div>
        <button
          @click="openForm"
          class="rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition-opacity"
        >
          + Nueva Solicitud
        </button>
      </div>

      <!-- Success banner -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showSuccess" class="flex items-center justify-between rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3">
          <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="text-sm font-medium text-green-400">Solicitud enviada correctamente. Tu coach responderá en 48 horas.</p>
          </div>
          <button @click="showSuccess = false" class="text-green-400 hover:text-green-300 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 animate-pulse">
          <div v-for="i in 4" :key="i" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 h-20"></div>
        </div>
        <div class="space-y-3">
          <div v-for="i in 3" :key="i" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 h-20 animate-pulse"></div>
        </div>
      </template>

      <template v-else>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ stats.total }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
          </div>
          <div class="rounded-xl border border-yellow-500/30 bg-yellow-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-yellow-400">{{ stats.open }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Abiertos</p>
          </div>
          <div class="rounded-xl border border-blue-500/30 bg-blue-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-blue-400">{{ stats.in_progress }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">En Progreso</p>
          </div>
          <div class="rounded-xl border border-green-500/30 bg-green-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-green-400">{{ stats.closed }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Resueltos</p>
          </div>
        </div>

        <!-- Status filter tabs -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="[key, label] in statusTabs"
            :key="key"
            @click="setFilter(key)"
            class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors"
            :class="statusFilter === key
              ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
              : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          >
            {{ label }}
          </button>
        </div>

        <!-- Ticket list -->
        <div v-if="filteredTickets.length" class="space-y-3">
          <div
            v-for="ticket in filteredTickets"
            :key="ticket.id"
            class="rounded-xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-accent/30"
          >
            <!-- Card header -->
            <button
              @click="toggleExpand(ticket.id)"
              class="flex w-full items-start gap-4 p-4 text-left"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-sm font-semibold text-wc-text">{{ typeLabels[ticket.ticket_type] ?? ticket.ticket_type }}</span>
                  <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider" :class="statusColors[ticket.status]">
                    {{ statusLabels[ticket.status] ?? ticket.status }}
                  </span>
                  <span v-if="['alta','high','urgent'].includes(ticket.priority)"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                    :class="priorityColors[ticket.priority]">
                    {{ priorityLabels[ticket.priority] }}
                  </span>
                  <span v-if="ticket.response" class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent">
                    Respondido
                  </span>
                </div>
                <p class="mt-1 text-sm text-wc-text-secondary line-clamp-2">{{ ticket.description }}</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">
                  {{ ticket.created_at }} &middot; Límite: {{ ticket.deadline }}
                </p>
              </div>

              <svg
                class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform"
                :class="expandedId === ticket.id ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <!-- Expanded detail -->
            <Transition
              enter-active-class="transition duration-200 ease-out"
              enter-from-class="opacity-0"
              enter-to-class="opacity-100"
              leave-active-class="transition duration-150 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div v-if="expandedId === ticket.id" class="border-t border-wc-border px-4 pb-4 pt-4 space-y-4">
                <div>
                  <h4 class="mb-1.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Descripcion completa</h4>
                  <p class="text-sm text-wc-text leading-relaxed">{{ ticket.description }}</p>
                </div>

                <div v-if="ticket.response" class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-4">
                  <h4 class="mb-1.5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-accent">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                    Respuesta del coach
                  </h4>
                  <p class="text-sm text-wc-text leading-relaxed">{{ ticket.response }}</p>
                  <p v-if="ticket.resolved_at" class="mt-2 text-xs text-wc-text-tertiary">Respondido {{ ticket.resolved_at }}</p>
                </div>
                <div v-else class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                  <p class="text-sm text-wc-text-tertiary">Pendiente de respuesta. Tu coach responderá pronto.</p>
                </div>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
            <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
            </svg>
          </div>
          <h3 class="mb-1 text-base font-semibold text-wc-text">
            {{ statusFilter !== 'all' ? 'Sin tickets con ese estado' : 'Sin solicitudes aun' }}
          </h3>
          <p class="text-sm text-wc-text-secondary">
            {{ statusFilter !== 'all' ? 'Prueba con otro filtro.' : 'Crea tu primera solicitud de soporte con el botón de arriba.' }}
          </p>
        </div>

      </template>

      <!-- New Ticket Form Modal -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeForm"></div>

          <!-- Modal -->
          <div class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-center justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVA SOLICITUD</h2>
              <button @click="closeForm" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="space-y-4">

              <!-- Ticket type -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Tipo de solicitud <span class="text-wc-accent">*</span>
                </label>
                <select
                  v-model="form.ticketType"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none transition-colors"
                  :class="{ 'border-wc-accent': formErrors.ticketType }"
                >
                  <option value="">-- Selecciona --</option>
                  <option value="rutina_nueva">Rutina nueva</option>
                  <option value="cambio_rutina">Cambio de rutina</option>
                  <option value="nutricion">Nutricion</option>
                  <option value="habitos">Habitos</option>
                  <option value="invitacion_cliente">Invitacion de cliente</option>
                  <option value="otro">Otro</option>
                </select>
                <p v-if="formErrors.ticketType" class="mt-1 text-xs text-wc-accent">{{ formErrors.ticketType }}</p>
              </div>

              <!-- Priority -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Prioridad</label>
                <div class="flex gap-3">
                  <label
                    class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors"
                    :class="form.priority === 'normal' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40'"
                  >
                    <input type="radio" v-model="form.priority" value="normal" class="accent-red-600" />
                    <span class="text-sm text-wc-text">Normal</span>
                  </label>
                  <label
                    class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors"
                    :class="form.priority === 'alta' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40'"
                  >
                    <input type="radio" v-model="form.priority" value="alta" class="accent-red-600" />
                    <span class="text-sm text-wc-text">Alta</span>
                  </label>
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Descripcion <span class="text-wc-accent">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  rows="5"
                  placeholder="Describe tu solicitud con el mayor detalle posible..."
                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none transition-colors"
                  :class="{ 'border-wc-accent': formErrors.description }"
                ></textarea>
                <p v-if="formErrors.description" class="mt-1 text-xs text-wc-accent">{{ formErrors.description }}</p>
              </div>

              <!-- Actions -->
              <div class="flex gap-3 pt-1">
                <button
                  type="button"
                  @click="closeForm"
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                >
                  Cancelar
                </button>
                <button
                  @click="submitTicket"
                  :disabled="submitting"
                  class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:opacity-90 transition-opacity disabled:opacity-50"
                >
                  <span v-if="!submitting">Enviar Solicitud</span>
                  <span v-else class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Enviando...
                  </span>
                </button>
              </div>

            </div>
          </div>
        </div>
      </Transition>

    </div>
  </ClientLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { useApi } from '../../composables/useApi';

const api = useApi();

// State
const loading      = ref(true);
const tickets      = ref([]);
const stats        = reactive({ total: 0, open: 0, in_progress: 0, closed: 0 });
const statusFilter = ref('all');
const expandedId   = ref(null);
const showSuccess  = ref(false);

// Form
const showForm  = ref(false);
const submitting = ref(false);
const form       = reactive({ ticketType: '', description: '', priority: 'normal' });
const formErrors = reactive({ ticketType: null, description: null });

const statusTabs = [
  ['all', 'Todos'],
  ['open', 'Abiertos'],
  ['in_progress', 'En Progreso'],
  ['closed', 'Cerrados'],
];

const typeLabels = {
  rutina_nueva:       'Rutina nueva',
  cambio_rutina:      'Cambio de rutina',
  nutricion:          'Nutricion',
  habitos:            'Habitos',
  invitacion_cliente: 'Invitacion cliente',
  otro:               'Otro',
};

const statusLabels = { open: 'Abierto', in_progress: 'En progreso', closed: 'Cerrado' };
const statusColors = {
  open:        'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
  in_progress: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
  closed:      'bg-green-500/10 text-green-400 border-green-500/30',
};
const priorityLabels = { normal: 'Normal', alta: 'Alta', low: 'Baja', high: 'Alta', urgent: 'Urgente' };
const priorityColors = {
  normal: 'text-wc-text-secondary bg-wc-bg-secondary',
  alta:   'text-wc-accent bg-wc-accent/10',
  high:   'text-wc-accent bg-wc-accent/10',
  urgent: 'text-red-400 bg-red-500/10',
};

const filteredTickets = computed(() => {
  if (statusFilter.value === 'all') return tickets.value;
  return tickets.value.filter(t => t.status === statusFilter.value);
});

async function fetchTickets() {
  loading.value = true;
  try {
    const response = await api.get('/api/v/client/tickets');
    tickets.value = response.data.tickets ?? [];
    Object.assign(stats, response.data.stats ?? {});
  } finally {
    loading.value = false;
  }
}

function setFilter(key) {
  statusFilter.value = key;
  expandedId.value   = null;
}

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

function openForm() {
  resetForm();
  showForm.value = true;
}

function closeForm() {
  showForm.value = false;
  resetForm();
}

function resetForm() {
  form.ticketType   = '';
  form.description  = '';
  form.priority     = 'normal';
  formErrors.ticketType   = null;
  formErrors.description  = null;
}

async function submitTicket() {
  formErrors.ticketType  = null;
  formErrors.description = null;

  if (!form.ticketType) {
    formErrors.ticketType = 'Selecciona el tipo de solicitud.';
    return;
  }
  if (form.description.length < 10) {
    formErrors.description = 'La descripción debe tener al menos 10 caracteres.';
    return;
  }

  submitting.value = true;
  try {
    const response = await api.post('/api/v/client/tickets', {
      ticket_type: form.ticketType,
      description: form.description,
      priority:    form.priority,
    });
    if (response.data.ticket) tickets.value.unshift(response.data.ticket);
    stats.total++;
    stats.open++;
    closeForm();
    showSuccess.value = true;
    setTimeout(() => { showSuccess.value = false; }, 5000);
  } catch (err) {
    const errors = err?.response?.data?.errors;
    if (errors?.ticket_type?.[0]) formErrors.ticketType  = errors.ticket_type[0];
    if (errors?.description?.[0]) formErrors.description = errors.description[0];
  } finally {
    submitting.value = false;
  }
}

onMounted(fetchTickets);
</script>
