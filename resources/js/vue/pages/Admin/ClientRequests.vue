<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(false);
const requests = ref([]);
const counts   = ref({ pending: 0, approved: 0, rejected: 0, total: 0 });
const meta     = ref({ total: 0 });

const statusFilter = ref('pending'); // pending|approved|rejected|all
const actionFilter = ref('all');
const search       = ref('');
const coachFilter  = ref('');

let debounceTimer = null;

// ─── Toast ────────────────────────────────────────────────────────────────────
const toast = ref({ show: false, type: 'success', message: '' });
function showToast(message, type = 'success') {
  toast.value = { show: true, type, message };
  setTimeout(() => { toast.value.show = false; }, 4000);
}

// ─── Detail / approve / reject modals ─────────────────────────────────────────
const detail        = ref({ show: false, request: null, loading: false });
const approveModal  = ref({ show: false, request: null, loading: false, error: '' });
const rejectModal   = ref({ show: false, request: null, notas: '', loading: false, error: '' });

const ACTION_LABELS = {
  delete:     { label: 'Eliminar',       warning: 'Al aprobar, el cliente sera marcado para eliminacion. Sus datos historicos se conservaran segun la politica.', color: 'red' },
  deactivate: { label: 'Desactivar',     warning: 'Al aprobar, el cliente sera desactivado y perdera acceso a la plataforma inmediatamente.', color: 'amber' },
  edit:       { label: 'Editar',         warning: 'Al aprobar, confirmas que el coach puede realizar los cambios descritos en la razon.', color: 'blue' },
};

function actionClass(action) {
  return ({
    delete:     'bg-red-500/10 text-red-400 border-red-500/30',
    deactivate: 'bg-amber-500/10 text-amber-500 border-amber-500/30',
    edit:       'bg-blue-500/10 text-blue-400 border-blue-500/30',
  })[action] || 'bg-wc-bg-secondary text-wc-text-tertiary border-wc-border';
}

function statusClass(status) {
  return ({
    pending:   'bg-yellow-500/10 text-yellow-500',
    approved:  'bg-emerald-500/10 text-emerald-500',
    rejected:  'bg-red-500/10 text-red-400',
  })[status] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function formatDateTime(iso) {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
  } catch { return ''; }
}

function truncate(str, n = 140) {
  if (!str) return '';
  return str.length > n ? str.slice(0, n) + '…' : str;
}

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function fetchRequests() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all')  params.set('status', statusFilter.value);
    if (actionFilter.value !== 'all')  params.set('action', actionFilter.value);
    if (coachFilter.value)             params.set('coach_id', coachFilter.value);
    if (search.value)                  params.set('search', search.value);
    const { data } = await api.get(`/api/v/admin/client-requests?${params}`);
    requests.value = data.requests || [];
    counts.value   = data.counts   || counts.value;
    meta.value     = data.meta     || { total: requests.value.length };
  } catch {
    requests.value = [];
  } finally {
    loading.value = false;
  }
}

watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchRequests, 300);
});
watch([statusFilter, actionFilter, coachFilter], fetchRequests);

// ─── Detail ───────────────────────────────────────────────────────────────────
async function openDetail(req) {
  detail.value = { show: true, request: req, loading: true };
  try {
    const { data } = await api.get(`/api/v/admin/client-requests/${req.id}`);
    detail.value.request = data.request || data;
  } catch {
    // keep list row data
  } finally {
    detail.value.loading = false;
  }
}

// ─── Approve ──────────────────────────────────────────────────────────────────
function openApprove(req) {
  approveModal.value = { show: true, request: req, loading: false, error: '' };
}

async function doApprove() {
  const m = approveModal.value;
  if (!m.request) return;
  m.loading = true;
  m.error = '';
  try {
    await api.post(`/api/v/admin/client-requests/${m.request.id}/approve`);
    m.show = false;
    showToast('Solicitud aprobada');
    detail.value.show = false;
    fetchRequests();
  } catch (err) {
    m.error = err.response?.data?.error || 'No se pudo aprobar la solicitud';
  } finally {
    m.loading = false;
  }
}

// ─── Reject ───────────────────────────────────────────────────────────────────
function openReject(req) {
  rejectModal.value = { show: true, request: req, notas: '', loading: false, error: '' };
}

async function doReject() {
  const m = rejectModal.value;
  if (!m.request) return;
  if ((m.notas || '').trim().length < 5) {
    m.error = 'La razon del rechazo es requerida.';
    return;
  }
  m.loading = true;
  m.error = '';
  try {
    await api.post(`/api/v/admin/client-requests/${m.request.id}/reject`, { admin_notas: m.notas.trim() });
    m.show = false;
    showToast('Solicitud rechazada');
    detail.value.show = false;
    fetchRequests();
  } catch (err) {
    if (err.response?.status === 422) {
      m.error = Object.values(err.response.data.errors || {}).flat()[0] || 'Datos invalidos';
    } else {
      m.error = err.response?.data?.error || 'No se pudo rechazar';
    }
  } finally {
    m.loading = false;
  }
}

const uniqueCoaches = computed(() => {
  const seen = new Map();
  for (const r of requests.value) {
    if (r.coach_id && !seen.has(r.coach_id)) {
      seen.set(r.coach_id, r.coach_name || 'Coach');
    }
  }
  return Array.from(seen.entries()).map(([id, name]) => ({ id, name }));
});

onMounted(fetchRequests);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">SOLICITUDES DE COACHES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
          Aprueba o rechaza las acciones que los coaches solicitan sobre sus clientes.
        </p>
      </div>

      <!-- Counters -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <button @click="statusFilter = 'pending'"
                class="rounded-xl border p-4 text-left transition-colors"
                :class="statusFilter === 'pending' ? 'border-yellow-500/60 bg-yellow-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-yellow-500/40'">
          <p class="font-data text-2xl font-bold text-yellow-500">{{ counts.pending ?? 0 }}</p>
          <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
        </button>
        <button @click="statusFilter = 'approved'"
                class="rounded-xl border p-4 text-left transition-colors"
                :class="statusFilter === 'approved' ? 'border-emerald-500/60 bg-emerald-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-emerald-500/40'">
          <p class="font-data text-2xl font-bold text-emerald-500">{{ counts.approved ?? 0 }}</p>
          <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Aprobadas</p>
        </button>
        <button @click="statusFilter = 'rejected'"
                class="rounded-xl border p-4 text-left transition-colors"
                :class="statusFilter === 'rejected' ? 'border-red-500/60 bg-red-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-red-500/40'">
          <p class="font-data text-2xl font-bold text-red-400">{{ counts.rejected ?? 0 }}</p>
          <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Rechazadas</p>
        </button>
        <button @click="statusFilter = 'all'"
                class="rounded-xl border p-4 text-left transition-colors"
                :class="statusFilter === 'all' ? 'border-wc-accent/60 bg-wc-accent/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40'">
          <p class="font-data text-2xl font-bold text-wc-text">{{ counts.total ?? requests.length }}</p>
          <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Todas</p>
        </button>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3">
        <div class="relative min-w-48 flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search" type="text" placeholder="Buscar por cliente, coach o razon..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
        <select v-model="actionFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="all">Todas las acciones</option>
          <option value="deactivate">Desactivar</option>
          <option value="delete">Eliminar</option>
          <option value="edit">Editar</option>
        </select>
        <select v-if="uniqueCoaches.length" v-model="coachFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="">Todos los coaches</option>
          <option v-for="c in uniqueCoaches" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-24"></div>
      </template>

      <!-- Empty -->
      <div v-else-if="!requests.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-secondary">No hay solicitudes con los filtros seleccionados.</p>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div
          v-for="req in requests"
          :key="req.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-colors hover:border-wc-border/80"
        >
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold" :class="actionClass(req.action)">
                  {{ ACTION_LABELS[req.action]?.label || req.action }}
                </span>
                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusClass(req.status)">
                  {{ req.status }}
                </span>
                <span class="text-[11px] text-wc-text-tertiary">{{ formatDateTime(req.created_at) }}</span>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <div>
                  <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Coach</span>
                  <span class="ml-1.5 font-medium text-wc-text">{{ req.coach_name || '—' }}</span>
                </div>
                <div>
                  <span class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Cliente</span>
                  <span class="ml-1.5 font-medium text-wc-text">{{ req.client_name || '—' }}</span>
                </div>
              </div>
              <p v-if="req.reason" class="mt-2 text-sm text-wc-text-secondary">{{ truncate(req.reason) }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
              <button @click="openDetail(req)"
                      class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:text-wc-text">
                Ver detalle
              </button>
              <template v-if="req.status === 'pending'">
                <button @click="openApprove(req)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                  Aprobar
                </button>
                <button @click="openReject(req)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-1.5 text-xs font-medium text-red-400 hover:bg-red-500/20">
                  Rechazar
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== DETAIL MODAL ==================== -->
    <Transition name="fade">
      <div v-if="detail.show" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="detail.show = false"></div>
        <Transition name="slide-up">
          <div v-if="detail.show" class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">DETALLE SOLICITUD</h2>
              <button @click="detail.show = false" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <div v-if="detail.loading" class="h-40 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>

            <template v-else-if="detail.request">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold" :class="actionClass(detail.request.action)">
                  {{ ACTION_LABELS[detail.request.action]?.label || detail.request.action }}
                </span>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(detail.request.status)">
                  {{ detail.request.status }}
                </span>
                <span class="text-xs text-wc-text-tertiary">{{ formatDateTime(detail.request.created_at) }}</span>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Coach</p>
                  <p class="mt-0.5 text-sm font-medium text-wc-text">{{ detail.request.coach_name || '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Cliente</p>
                  <p class="mt-0.5 text-sm font-medium text-wc-text">{{ detail.request.client_name || '—' }}</p>
                </div>
              </div>

              <div class="mt-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Razon del coach</p>
                <p class="mt-1.5 whitespace-pre-wrap text-sm text-wc-text">{{ detail.request.reason || '—' }}</p>
              </div>

              <div v-if="detail.request.admin_notas" class="mt-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Notas del admin</p>
                <p class="mt-1.5 whitespace-pre-wrap text-sm text-wc-text">{{ detail.request.admin_notas }}</p>
              </div>

              <div v-if="detail.request.status === 'pending'" class="mt-5 flex flex-wrap gap-3">
                <button @click="openReject(detail.request)"
                        class="flex-1 rounded-lg border border-red-500/40 bg-red-500/10 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/20">
                  Rechazar
                </button>
                <button @click="openApprove(detail.request)"
                        class="flex-1 rounded-lg bg-emerald-500 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                  Aprobar
                </button>
              </div>
            </template>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== APPROVE MODAL ==================== -->
    <Transition name="fade">
      <div v-if="approveModal.show" class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="approveModal.show = false"></div>
        <Transition name="slide-up">
          <div v-if="approveModal.show" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <h3 class="font-display text-xl tracking-wide text-wc-text">APROBAR SOLICITUD</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">
              Cliente: <span class="font-semibold text-wc-text">{{ approveModal.request?.client_name }}</span>
            </p>
            <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-400">
              {{ ACTION_LABELS[approveModal.request?.action]?.warning || 'Confirma que deseas aprobar esta solicitud.' }}
            </div>
            <p v-if="approveModal.error" class="mt-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-xs text-red-400">
              {{ approveModal.error }}
            </p>
            <div class="mt-5 flex gap-3">
              <button @click="approveModal.show = false"
                      class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Cancelar
              </button>
              <button @click="doApprove" :disabled="approveModal.loading"
                      class="flex-1 rounded-lg bg-emerald-500 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                {{ approveModal.loading ? 'Aprobando...' : 'Confirmar aprobacion' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== REJECT MODAL ==================== -->
    <Transition name="fade">
      <div v-if="rejectModal.show" class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="rejectModal.show = false"></div>
        <Transition name="slide-up">
          <div v-if="rejectModal.show" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <h3 class="font-display text-xl tracking-wide text-wc-text">RECHAZAR SOLICITUD</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">
              Cliente: <span class="font-semibold text-wc-text">{{ rejectModal.request?.client_name }}</span>
            </p>
            <div class="mt-4">
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Razon del rechazo <span class="text-wc-accent">*</span>
              </label>
              <textarea
                v-model="rejectModal.notas" rows="4"
                placeholder="Explica al coach por que rechazas su solicitud..."
                class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
              ></textarea>
            </div>
            <p v-if="rejectModal.error" class="mt-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-xs text-red-400">
              {{ rejectModal.error }}
            </p>
            <div class="mt-5 flex gap-3">
              <button @click="rejectModal.show = false"
                      class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Cancelar
              </button>
              <button @click="doReject" :disabled="rejectModal.loading"
                      class="flex-1 rounded-lg bg-red-500 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                {{ rejectModal.loading ? 'Rechazando...' : 'Confirmar rechazo' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== TOAST ==================== -->
    <Transition name="slide-up">
      <div v-if="toast.show"
           class="fixed bottom-6 left-1/2 z-[100] -translate-x-1/2 rounded-xl border px-5 py-3 text-sm font-medium shadow-xl"
           :class="toast.type === 'error'
              ? 'border-red-500/40 bg-red-500/10 text-red-400'
              : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400'">
        {{ toast.message }}
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(1.5rem); opacity: 0; }
</style>
