<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api   = useApi();
const toast = useToast();

// ─── List state ────────────────────────────────────────────────────────────────
const loading    = ref(false);
const proofs     = ref([]);
const pagination = ref({ currentPage: 1, perPage: 20, total: 0, lastPage: 1 });

// Filters
const activeTab  = ref('all');  // all | pendiente | aprobado | rechazado | expirado
const search     = ref('');
const page       = ref(1);

let debounceTimer = null;

const pendingCount = computed(() => proofs.value.filter(p => p.status === 'pendiente').length);

// ─── Modal state ───────────────────────────────────────────────────────────────
const showModal     = ref(false);
const selectedProof = ref(null);
const fileUrl       = ref(null);
const fileLoading   = ref(false);

// Review actions
const actionLoading   = ref(false);
const showRejectForm  = ref(false);
const rejectNote      = ref('');
const rejectNoteError = ref('');
const showApproveConfirm = ref(false);

// ─── Tabs config (module-level constant) ──────────────────────────────────────
const TABS = [
  { value: 'all',       label: 'Todos'     },
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'aprobado',  label: 'Aprobado'  },
  { value: 'rechazado', label: 'Rechazado' },
  { value: 'expirado',  label: 'Expirado'  },
];

const STATUS_CLASSES = {
  pendiente: 'bg-yellow-500/15 text-yellow-300 border border-yellow-500/20',
  aprobado:  'bg-green-500/15 text-green-300 border border-green-500/20',
  rechazado: 'bg-red-500/15 text-wc-accent border border-red-500/20',
  expirado:  'bg-zinc-500/15 text-zinc-400 border border-zinc-500/20',
};

const STATUS_LABELS = {
  pendiente: 'Pendiente',
  aprobado:  'Aprobado',
  rechazado: 'Rechazado',
  expirado:  'Expirado',
};

// ─── Fetch ─────────────────────────────────────────────────────────────────────
async function fetchProofs() {
  loading.value = true;
  try {
    const params = { page: page.value };
    if (activeTab.value !== 'all') params.status = activeTab.value;
    if (search.value.trim())       params.email   = search.value.trim();

    const { data } = await api.get('/api/v/admin/payment-proofs', { params });
    proofs.value     = data.data   ?? [];
    pagination.value = data.meta   ?? { currentPage: 1, perPage: 20, total: 0, lastPage: 1 };
  } catch {
    proofs.value = [];
  } finally {
    loading.value = false;
  }
}

watch(activeTab, () => { page.value = 1; fetchProofs(); });
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => { page.value = 1; fetchProofs(); }, 300);
});

function goToPage(p) {
  if (p < 1 || p > pagination.value.lastPage) return;
  page.value = p;
  fetchProofs();
}

// ─── Modal open/close ──────────────────────────────────────────────────────────
async function openModal(proof) {
  selectedProof.value   = proof;
  fileUrl.value         = null;
  fileLoading.value     = false;
  showRejectForm.value  = false;
  rejectNote.value      = '';
  rejectNoteError.value = '';
  showApproveConfirm.value = false;
  showModal.value = true;

  // Lazy-load the signed file URL
  await loadFileUrl(proof.id);
}

function closeModal() {
  showModal.value       = false;
  selectedProof.value   = null;
  fileUrl.value         = null;
  showRejectForm.value  = false;
  rejectNote.value      = '';
  rejectNoteError.value = '';
  showApproveConfirm.value = false;
  actionLoading.value   = false;
}

async function loadFileUrl(id) {
  fileLoading.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/payment-proofs/${id}/file`);
    fileUrl.value = data.url ?? null;
  } catch {
    fileUrl.value = null;
  } finally {
    fileLoading.value = false;
  }
}

// ─── Open file in new tab (bypasses Vue Router) ───────────────────────────────
function openFileInTab() {
  if (!fileUrl.value) return;
  window.open(fileUrl.value, '_blank', 'noopener,noreferrer');
}

// ─── Approve ──────────────────────────────────────────────────────────────────
function requestApprove() {
  showApproveConfirm.value = true;
  showRejectForm.value     = false;
}

async function confirmApprove() {
  if (!selectedProof.value) return;
  actionLoading.value = true;
  try {
    await api.post(`/api/v/admin/payment-proofs/${selectedProof.value.id}/approve`);
    toast.success('Comprobante aprobado correctamente.');
    closeModal();
    fetchProofs();
  } catch (err) {
    toast.apiError(err, 'No se pudo aprobar el comprobante.');
    showApproveConfirm.value = false;
    actionLoading.value = false;
  }
}

// ─── Reject ───────────────────────────────────────────────────────────────────
function requestReject() {
  showRejectForm.value     = true;
  showApproveConfirm.value = false;
  rejectNote.value         = '';
  rejectNoteError.value    = '';
}

async function confirmReject() {
  if (!selectedProof.value) return;

  rejectNoteError.value = '';
  if (!rejectNote.value.trim() || rejectNote.value.trim().length < 10) {
    rejectNoteError.value = 'La razon debe tener al menos 10 caracteres.';
    return;
  }

  actionLoading.value = true;
  try {
    await api.post(`/api/v/admin/payment-proofs/${selectedProof.value.id}/reject`, {
      review_note: rejectNote.value.trim(),
    });
    toast.success('Comprobante rechazado.');
    closeModal();
    fetchProofs();
  } catch (err) {
    toast.apiError(err, 'No se pudo rechazar el comprobante.');
    actionLoading.value = false;
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatAmount(amount, currency) {
  if (!amount) return '—';
  return new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: currency || 'COP', minimumFractionDigits: 0
  }).format(amount);
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('es-CO', {
    year: 'numeric', month: 'short', day: 'numeric',
  });
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleString('es-CO', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function formatFileSize(bytes) {
  if (!bytes) return '—';
  if (bytes < 1024)         return `${bytes} B`;
  if (bytes < 1024 * 1024)  return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function isPdf(mime) {
  return mime === 'application/pdf';
}

onMounted(fetchProofs);
onBeforeUnmount(() => clearTimeout(debounceTimer));
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Page header -->
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">COMPROBANTES DE PAGO</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            Revisa y gestiona los comprobantes de pago externo enviados por los coaches.
          </p>
        </div>
        <div v-if="pagination.total > 0" class="flex items-center gap-2">
          <span class="text-sm text-wc-text-secondary font-data">{{ pagination.total }} total</span>
        </div>
      </div>

      <!-- Filters row -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <!-- Status tabs -->
        <div class="flex gap-1 overflow-x-auto rounded-xl border border-wc-border bg-wc-bg-tertiary p-1">
          <button
            v-for="tab in TABS"
            :key="tab.value"
            @click="activeTab = tab.value"
            :class="[
              'relative shrink-0 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
              activeTab === tab.value
                ? 'bg-wc-bg-secondary text-wc-text shadow-sm'
                : 'text-wc-text-secondary hover:text-wc-text'
            ]"
          >
            {{ tab.label }}
            <!-- Pending count badge -->
            <span
              v-if="tab.value === 'pendiente' && pendingCount > 0"
              class="ml-1.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-wc-accent text-[9px] font-bold text-white"
            >
              {{ pendingCount > 9 ? '9+' : pendingCount }}
            </span>
          </button>
        </div>

        <!-- Email search -->
        <div class="relative flex-1 sm:max-w-xs">
          <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search"
            type="search"
            placeholder="Buscar por email..."
            class="w-full rounded-xl border border-wc-border bg-wc-bg-tertiary py-2 pl-9 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div v-for="n in 5" :key="n" class="flex items-center gap-4 border-b border-wc-border px-4 py-3.5 last:border-b-0">
            <div class="h-3 w-24 animate-pulse rounded bg-wc-bg-secondary"></div>
            <div class="h-3 w-32 animate-pulse rounded bg-wc-bg-secondary"></div>
            <div class="ml-auto h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div
        v-else-if="!proofs.length"
        class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center"
      >
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-secondary">No hay comprobantes para los filtros seleccionados.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-wc-border">
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary lg:table-cell">Coach</th>
                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">Email</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary md:table-cell">Plan</th>
                <th class="hidden px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary md:table-cell">Monto</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary xl:table-cell">Metodo</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary lg:table-cell">Fecha</th>
                <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Accion</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <tr
                v-for="proof in proofs"
                :key="proof.id"
                class="cursor-pointer transition-colors hover:bg-wc-bg-secondary/40"
                @click="openModal(proof)"
              >
                <!-- Coach -->
                <td class="hidden px-4 py-3 lg:table-cell">
                  <span class="text-sm text-wc-text-secondary">{{ proof.coach?.name ?? '—' }}</span>
                </td>
                <!-- Client name -->
                <td class="px-4 py-3">
                  <span class="text-sm font-medium text-wc-text">{{ proof.clientName ?? '—' }}</span>
                  <div class="text-xs text-wc-text-tertiary sm:hidden">{{ proof.clientEmail }}</div>
                </td>
                <!-- Email -->
                <td class="hidden px-4 py-3 sm:table-cell">
                  <span class="text-xs text-wc-text-secondary">{{ proof.clientEmail ?? '—' }}</span>
                </td>
                <!-- Plan -->
                <td class="hidden px-4 py-3 md:table-cell">
                  <span class="font-data text-sm capitalize text-wc-text-secondary">{{ proof.plan ?? '—' }}</span>
                </td>
                <!-- Amount -->
                <td class="hidden px-4 py-3 text-right md:table-cell">
                  <span class="font-data text-sm font-semibold text-wc-text">{{ formatAmount(proof.amount, proof.currency) }}</span>
                </td>
                <!-- Method -->
                <td class="hidden px-4 py-3 xl:table-cell">
                  <span class="text-xs capitalize text-wc-text-secondary">{{ proof.paymentMethod ?? '—' }}</span>
                </td>
                <!-- Date -->
                <td class="hidden px-4 py-3 lg:table-cell">
                  <span class="text-xs text-wc-text-secondary">{{ formatDate(proof.submittedAt) }}</span>
                </td>
                <!-- Status -->
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-semibold capitalize"
                    :class="STATUS_CLASSES[proof.status] ?? 'bg-zinc-500/15 text-zinc-400'"
                  >
                    {{ STATUS_LABELS[proof.status] ?? proof.status }}
                  </span>
                </td>
                <!-- Action button -->
                <td class="px-4 py-3 text-right">
                  <button
                    @click.stop="openModal(proof)"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-accent"
                  >
                    Revisar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="pagination.lastPage > 1"
        class="flex items-center justify-between"
      >
        <p class="text-xs text-wc-text-secondary font-data">
          Pagina {{ pagination.currentPage }} de {{ pagination.lastPage }} &middot; {{ pagination.total }} resultados
        </p>
        <div class="flex gap-2">
          <button
            :disabled="pagination.currentPage <= 1"
            @click="goToPage(pagination.currentPage - 1)"
            class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs text-wc-text-secondary transition-colors hover:text-wc-text disabled:cursor-not-allowed disabled:opacity-40"
          >
            Anterior
          </button>
          <button
            :disabled="pagination.currentPage >= pagination.lastPage"
            @click="goToPage(pagination.currentPage + 1)"
            class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs text-wc-text-secondary transition-colors hover:text-wc-text disabled:cursor-not-allowed disabled:opacity-40"
          >
            Siguiente
          </button>
        </div>
      </div>

    </div>

    <!-- ==================== REVIEW MODAL ==================== -->
    <Transition name="fade">
      <div
        v-if="showModal && selectedProof"
        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/70 backdrop-blur-sm"
          @click="closeModal"
        ></div>

        <!-- Panel -->
        <Transition name="slide-up">
          <div
            v-if="showModal"
            class="relative z-10 max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl"
          >

            <!-- Modal header -->
            <div class="flex items-start justify-between border-b border-wc-border px-6 py-4">
              <div>
                <h2 class="font-display text-2xl tracking-wide text-wc-text">COMPROBANTE #{{ selectedProof.id }}</h2>
                <p class="mt-0.5 text-xs text-wc-text-secondary">Enviado {{ formatDateTime(selectedProof.submittedAt) }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-semibold capitalize"
                  :class="STATUS_CLASSES[selectedProof.status] ?? 'bg-zinc-500/15 text-zinc-400'"
                >
                  {{ STATUS_LABELS[selectedProof.status] ?? selectedProof.status }}
                </span>
                <button
                  @click="closeModal"
                  class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text transition-colors"
                  aria-label="Cerrar modal"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="space-y-5 p-6">

              <!-- File thumbnail / preview -->
              <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <!-- Loading spinner -->
                <div v-if="fileLoading" class="flex h-48 items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-wc-text-tertiary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                </div>

                <!-- No URL / error -->
                <div v-else-if="!fileUrl" class="flex h-40 flex-col items-center justify-center gap-2 text-wc-text-tertiary">
                  <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                  </svg>
                  <p class="text-xs">No se pudo cargar el archivo.</p>
                </div>

                <!-- PDF indicator -->
                <div
                  v-else-if="isPdf(selectedProof.fileMime)"
                  class="flex h-40 cursor-pointer flex-col items-center justify-center gap-3 text-sky-400 transition-opacity hover:opacity-80"
                  @click.stop="openFileInTab"
                >
                  <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                  </svg>
                  <span class="text-xs font-medium">Ver PDF &rarr;</span>
                </div>

                <!-- Image preview -->
                <div v-else class="group relative cursor-pointer" @click.stop="openFileInTab">
                  <img
                    :src="fileUrl"
                    alt="Comprobante de pago"
                    class="max-h-72 w-full object-contain transition-opacity group-hover:opacity-90"
                    @error="fileUrl = null"
                  />
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                    <span class="rounded-lg bg-black/60 px-3 py-1.5 text-xs font-medium text-white">Ver completo &rarr;</span>
                  </div>
                </div>
              </div>

              <!-- Meta info grid -->
              <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedProof.coach?.name ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedProof.clientName ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Email</p>
                  <p class="mt-1 truncate text-xs text-wc-text-secondary">{{ selectedProof.clientEmail ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</p>
                  <p class="mt-1 text-sm capitalize font-medium text-wc-text">{{ selectedProof.plan ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</p>
                  <p class="font-data mt-1 text-sm font-semibold text-wc-text">{{ formatAmount(selectedProof.amount, selectedProof.currency) }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</p>
                  <p class="mt-1 text-sm capitalize text-wc-text-secondary">{{ selectedProof.paymentMethod ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Tamano</p>
                  <p class="font-data mt-1 text-sm text-wc-text-secondary">{{ formatFileSize(selectedProof.fileSize) }}</p>
                </div>
                <div v-if="selectedProof.reviewedAt" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Revisado</p>
                  <p class="mt-1 text-xs text-wc-text-secondary">{{ formatDate(selectedProof.reviewedAt) }}</p>
                </div>
                <div v-if="selectedProof.reviewer" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Revisor</p>
                  <p class="mt-1 text-sm text-wc-text-secondary">{{ selectedProof.reviewer?.name ?? '—' }}</p>
                </div>
              </div>

              <!-- Coach note -->
              <div v-if="selectedProof.coachNote" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nota del coach</p>
                <p class="text-sm text-wc-text-secondary">{{ selectedProof.coachNote }}</p>
              </div>

              <!-- Review note (if already reviewed) -->
              <div
                v-if="selectedProof.reviewNote"
                class="rounded-lg border border-red-500/20 bg-red-500/5 p-4"
              >
                <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-red-400">Razon del rechazo</p>
                <p class="text-sm text-wc-text-secondary">{{ selectedProof.reviewNote }}</p>
              </div>

              <!-- ── Actions for pendiente status only ── -->
              <template v-if="selectedProof.status === 'pendiente'">

                <!-- Approve confirmation -->
                <Transition name="fade" mode="out-in">
                  <div
                    v-if="showApproveConfirm"
                    key="approve-confirm"
                    class="rounded-xl border border-green-500/20 bg-green-500/5 p-4"
                  >
                    <p class="mb-1 text-sm font-semibold text-green-400">Confirmar aprobacion</p>
                    <p class="mb-4 text-xs text-wc-text-secondary">Esta accion es irreversible. El comprobante quedara marcado como aprobado y se notificara al coach.</p>
                    <div class="flex gap-2">
                      <button
                        @click="confirmApprove"
                        :disabled="actionLoading"
                        class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-opacity hover:bg-green-500 disabled:opacity-50"
                      >
                        <svg v-if="actionLoading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ actionLoading ? 'Procesando...' : 'Confirmar aprobacion' }}
                      </button>
                      <button
                        @click="showApproveConfirm = false"
                        :disabled="actionLoading"
                        class="rounded-lg border border-wc-border px-4 py-2 text-sm text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-50"
                      >
                        Cancelar
                      </button>
                    </div>
                  </div>

                  <!-- Reject form -->
                  <div
                    v-else-if="showRejectForm"
                    key="reject-form"
                    class="space-y-3 rounded-xl border border-red-500/20 bg-red-500/5 p-4"
                  >
                    <p class="text-sm font-semibold text-wc-accent">Razon del rechazo</p>
                    <div>
                      <textarea
                        v-model="rejectNote"
                        rows="3"
                        placeholder="Explica por que se rechaza este comprobante (minimo 10 caracteres)..."
                        class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                        :class="rejectNoteError ? 'border-wc-accent' : ''"
                      ></textarea>
                      <p v-if="rejectNoteError" class="mt-1 text-xs text-wc-accent">{{ rejectNoteError }}</p>
                      <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ rejectNote.trim().length }}/10 caracteres minimo</p>
                    </div>
                    <div class="flex gap-2">
                      <button
                        @click="confirmReject"
                        :disabled="actionLoading"
                        class="flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                      >
                        <svg v-if="actionLoading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ actionLoading ? 'Procesando...' : 'Confirmar rechazo' }}
                      </button>
                      <button
                        @click="showRejectForm = false"
                        :disabled="actionLoading"
                        class="rounded-lg border border-wc-border px-4 py-2 text-sm text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-50"
                      >
                        Cancelar
                      </button>
                    </div>
                  </div>

                  <!-- Primary action buttons -->
                  <div v-else key="action-buttons" class="flex gap-3">
                    <button
                      @click="requestApprove"
                      class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-green-500/30 bg-green-600/10 py-2.5 text-sm font-medium text-green-400 transition-colors hover:bg-green-600/20"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                      </svg>
                      Aprobar
                    </button>
                    <button
                      @click="requestReject"
                      class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-600/10 py-2.5 text-sm font-medium text-wc-accent transition-colors hover:bg-red-600/20"
                    >
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                      Rechazar
                    </button>
                  </div>
                </Transition>

              </template>

            </div>
          </div>
        </Transition>
      </div>
    </Transition>

  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(40px); opacity: 0; }
</style>
