<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';
import DeadlineBadge from '../../components/DeadlineBadge.vue';
import PlanTicketComments from '../../components/PlanTicketComments.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const ticket = ref(null);
const adminNotas = ref('');
const savingNotas = ref(false);

const rejectModalOpen = ref(false);
const completeModalOpen = ref(false);
const rejectionCode = ref('');
const rejectionDetails = ref('');
const completePlanIds = ref('');
const updatingStatus = ref(false);
const copying = ref(false);
const copyingSection = ref(null); // which section is being copied
const toast = ref(null);

const exportDropdownOpen = ref(false);
const instructions = ref(null);
const loadingInstructions = ref(false);

// Attachments + print
const attachments = ref([]);
const loadingAttachments = ref(false);
const printing = ref(false);
let printBlobUrl = null;

const CATEGORY_META = {
  plan_nuevo: { label: 'Plan nuevo', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  ajuste_plan: { label: 'Ajuste', bg: 'bg-purple-500/10', text: 'text-purple-500' },
};
function categoryMeta(c) { return CATEGORY_META[c] || CATEGORY_META.plan_nuevo; }

function formatBytes(bytes) {
  if (!bytes) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  let n = bytes;
  while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
  return `${n.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
}

function mimeIcon(mime) {
  if (!mime) return 'doc';
  if (mime.startsWith('image/')) return 'image';
  if (mime === 'application/pdf') return 'pdf';
  return 'doc';
}

function formatRelative(d) {
  if (!d) return '';
  try {
    const diffMs = Date.now() - new Date(d).getTime();
    const min = Math.floor(diffMs / 60000);
    if (min < 1) return 'hace un momento';
    if (min < 60) return `hace ${min} min`;
    const hr = Math.floor(min / 60);
    if (hr < 24) return `hace ${hr} h`;
    const days = Math.floor(hr / 24);
    if (days < 30) return `hace ${days} d`;
    return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
  } catch { return d; }
}

async function fetchAttachments() {
  loadingAttachments.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}/attachments`);
    attachments.value = data.attachments || [];
  } catch (e) {
    attachments.value = [];
  } finally {
    loadingAttachments.value = false;
  }
}

async function deleteAttachment(att) {
  if (!confirm('¿Eliminar este archivo?')) return;
  try {
    await api.delete(`/api/v/admin/plan-tickets/${route.params.id}/attachments/${att.id}`);
    showToast('success', 'Archivo eliminado');
    await fetchAttachments();
  } catch (e) {
    showToast('error', 'No se pudo eliminar.');
  }
}

async function openPrintView() {
  if (printing.value) return;
  printing.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}/print`, {
      headers: { Accept: 'text/html' },
      responseType: 'blob',
    });
    // Revoke any previous URL
    if (printBlobUrl) URL.revokeObjectURL(printBlobUrl);
    const blob = data instanceof Blob ? data : new Blob([data], { type: 'text/html' });
    printBlobUrl = URL.createObjectURL(blob);
    window.open(printBlobUrl, '_blank');
  } catch (e) {
    showToast('error', 'No se pudo abrir la vista de impresion.');
  } finally {
    printing.value = false;
  }
}

const REJECTION_REASONS = [
  { value: 'info_incompleta', label: 'Informacion incompleta' },
  { value: 'contexto_insuficiente', label: 'Contexto insuficiente' },
  { value: 'conflicto_datos', label: 'Conflicto en los datos' },
  { value: 'fuera_de_scope', label: 'Fuera de scope' },
  { value: 'necesita_validacion_medica', label: 'Necesita validacion medica' },
  { value: 'otro', label: 'Otro (explicar en detalles)' },
];
const REJECTION_LABEL = Object.fromEntries(REJECTION_REASONS.map(r => [r.value, r.label]));

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

const planType = computed(() => ticket.value?.plan_type);
const isMetodoOrElite = computed(() => ['metodo','elite'].includes(planType.value));
const isElite = computed(() => planType.value === 'elite');

const expandedSections = ref({
  datos: true, entrenamiento: true, nutricion: true, habitos: true, suplementacion: true, ciclo: true,
});

const FRECUENCIA_SUPLEMENTO_LABELS = {
  diario: 'Diario',
  dias_entrenamiento: 'Dias de entrenamiento',
  '3_veces_semana': '3 veces por semana',
  ciclico: 'Ciclico',
};

function planTypeMeta(t) { return PLAN_TYPE_META[t] || PLAN_TYPE_META.esencial; }
function statusMeta(s) { return STATUS_META[s] || STATUS_META.borrador; }

function humanLabel(s) {
  if (s === null || s === undefined || s === '') return '-';
  return String(s).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function formatDate(d) {
  if (!d) return '-';
  try {
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch { return d; }
}

async function fetchTicket() {
  loading.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}`);
    ticket.value = data.ticket;
    adminNotas.value = data.ticket.admin_notas || '';
  } catch (e) {
    showToast('error', 'No se pudo cargar el ticket.');
  } finally {
    loading.value = false;
  }
}

async function loadInstructions() {
  loadingInstructions.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}/export/full`);
    instructions.value = data?.instructions || null;
  } catch (e) {
    instructions.value = null;
  } finally {
    loadingInstructions.value = false;
  }
}

const EXPORT_SECTIONS = [
  { key: 'full', label: 'Copiar TODO (brief completo)', accent: true },
  { key: 'entrenamiento', label: 'Copiar ENTRENAMIENTO' },
  { key: 'nutricion', label: 'Copiar NUTRICION' },
  { key: 'habitos', label: 'Copiar HABITOS' },
  { key: 'suplementacion', label: 'Copiar SUPLEMENTACION' },
  { key: 'ciclo', label: 'Copiar CICLO', eliteOnly: true },
];

const exportSectionsVisible = computed(() => {
  return EXPORT_SECTIONS.filter(s => !s.eliteOnly || isElite.value);
});

async function copySection(sectionKey) {
  if (copyingSection.value) return;
  copyingSection.value = sectionKey;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}/export/${sectionKey}`);
    const jsonText = JSON.stringify(data, null, 2);
    await navigator.clipboard.writeText(jsonText);
    const label = sectionKey === 'full' ? 'brief completo' : sectionKey;
    showToast('success', `JSON (${label}) copiado al portapapeles.`);
    exportDropdownOpen.value = false;
  } catch (e) {
    showToast('error', 'No se pudo copiar el JSON.');
  } finally {
    copyingSection.value = null;
  }
}

async function updateStatus(status, extras = {}) {
  updatingStatus.value = true;
  try {
    const payload = { status, ...extras };
    const { data } = await api.post(`/api/v/admin/plan-tickets/${route.params.id}/status`, payload);
    ticket.value = data.ticket;
    adminNotas.value = data.ticket.admin_notas || '';
    showToast('success', `Ticket marcado como ${statusMeta(status).label.toLowerCase()}.`);
    rejectModalOpen.value = false;
    completeModalOpen.value = false;
    rejectionCode.value = '';
    rejectionDetails.value = '';
    completePlanIds.value = '';
  } catch (e) {
    const msg = e?.response?.data?.message || 'No se pudo actualizar el estado.';
    showToast('error', msg);
  } finally {
    updatingStatus.value = false;
  }
}

function submitReject() {
  if (!rejectionCode.value) {
    showToast('error', 'Selecciona una razon de rechazo.');
    return;
  }
  if (rejectionCode.value === 'otro' && !rejectionDetails.value.trim()) {
    showToast('error', 'Los detalles son obligatorios cuando la razon es "Otro".');
    return;
  }
  const extras = { rejection_code: rejectionCode.value };
  if (rejectionDetails.value.trim()) extras.admin_notas = rejectionDetails.value.trim();
  updateStatus('rechazado', extras);
}

function submitComplete() {
  const raw = completePlanIds.value.trim();
  const extras = {};
  if (raw) {
    const ids = raw
      .split(/[\s,;]+/)
      .map(s => parseInt(s, 10))
      .filter(n => Number.isInteger(n) && n > 0);
    if (ids.length > 0) extras.generated_plan_ids = ids;
  }
  updateStatus('completado', extras);
}

const generatedPlanIds = computed(() => {
  const ids = ticket.value?.generated_plan_ids;
  if (!Array.isArray(ids)) return [];
  return ids.filter(n => Number.isInteger(n) && n > 0);
});

async function saveAdminNotas() {
  savingNotas.value = true;
  try {
    // Use the status update endpoint with same status but new notas
    await api.post(`/api/v/admin/plan-tickets/${route.params.id}/status`, {
      status: ticket.value.status,
      admin_notas: adminNotas.value,
    });
    showToast('success', 'Notas guardadas.');
  } catch (e) {
    showToast('error', 'No se pudo guardar la nota.');
  } finally {
    savingNotas.value = false;
  }
}

function showToast(type, message) {
  toast.value = { type, message };
  setTimeout(() => { toast.value = null; }, 3500);
}

function toggleSection(key) {
  expandedSections.value[key] = !expandedSections.value[key];
}

function closeExportDropdown(e) {
  const el = document.getElementById('admin-export-dropdown');
  if (el && !el.contains(e.target)) {
    exportDropdownOpen.value = false;
  }
}

onMounted(async () => {
  document.addEventListener('click', closeExportDropdown);
  await fetchTicket();
  // Load instructions banner + attachments in parallel
  loadInstructions();
  fetchAttachments();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', closeExportDropdown);
  if (printBlobUrl) URL.revokeObjectURL(printBlobUrl);
});

// Helpers for rendering JSON structures
const datosGenerales = computed(() => ticket.value?.datos_generales || {});
const planEntrenamiento = computed(() => ticket.value?.plan_entrenamiento || {});
const planNutricional = computed(() => ticket.value?.plan_nutricional || {});
const planHabitos = computed(() => ticket.value?.plan_habitos || {});
const planCiclo = computed(() => ticket.value?.plan_ciclo || {});
const planSuplementacion = computed(() => ticket.value?.plan_suplementacion || {});
const suplementos = computed(() => planSuplementacion.value?.suplementos || []);
function frecuenciaLabel(v) { return FRECUENCIA_SUPLEMENTO_LABELS[v] || humanLabel(v); }
const splitEntries = computed(() => {
  const s = planEntrenamiento.value?.split || {};
  return Object.entries(s);
});
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto space-y-6">

      <!-- Toast -->
      <Transition name="fade">
        <div
          v-if="toast"
          class="fixed top-20 right-4 z-50 rounded-lg border px-4 py-3 shadow-lg"
          :class="toast.type === 'success' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500' : 'border-red-500/30 bg-red-500/10 text-red-400'"
        >
          {{ toast.message }}
        </div>
      </Transition>

      <!-- Back -->
      <button
        @click="router.push('/admin/plan-tickets')"
        class="inline-flex items-center gap-1 text-xs font-medium text-wc-text-tertiary hover:text-wc-text"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Volver a tickets
      </button>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-24"></div>
      </div>

      <template v-else-if="ticket">
        <!-- Header -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2 flex-wrap mb-2">
                <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">{{ ticket.client_name || 'Cliente' }}</h1>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="[planTypeMeta(ticket.plan_type).bg, planTypeMeta(ticket.plan_type).text]">
                  {{ planTypeMeta(ticket.plan_type).label }}
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="[statusMeta(ticket.status).bg, statusMeta(ticket.status).text]">
                  {{ statusMeta(ticket.status).label }}
                </span>
                <span
                  v-if="ticket.category"
                  class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                  :class="[categoryMeta(ticket.category).bg, categoryMeta(ticket.category).text]"
                >{{ categoryMeta(ticket.category).label }}</span>
                <DeadlineBadge :deadline="ticket.deadline_at" :status="ticket.status" />
              </div>

              <!-- Rejection display -->
              <div v-if="ticket.status === 'rechazado' && ticket.rejection_code" class="mb-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3">
                <div class="flex items-center gap-2 mb-1">
                  <span class="rounded-full bg-red-500/20 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-red-400">
                    Rechazado: {{ REJECTION_LABEL[ticket.rejection_code] || humanLabel(ticket.rejection_code) }}
                  </span>
                </div>
                <p v-if="ticket.admin_notas" class="whitespace-pre-wrap text-xs text-red-400/90 mt-1">{{ ticket.admin_notas }}</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-wc-text-tertiary">
                <div><span class="text-wc-text-secondary">Coach:</span> {{ ticket.coach_name || '-' }}</div>
                <div><span class="text-wc-text-secondary">Creado:</span> {{ formatDate(ticket.created_at) }}</div>
                <div><span class="text-wc-text-secondary">Enviado:</span> {{ formatDate(ticket.submitted_at) }}</div>
              </div>
            </div>

            <!-- Header actions -->
            <div class="flex items-center gap-2 shrink-0">
            <button
              type="button"
              @click="openPrintView"
              :disabled="printing"
              class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text hover:border-wc-accent/40 transition disabled:opacity-50"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
              </svg>
              {{ printing ? 'Abriendo...' : 'Imprimir / PDF' }}
            </button>
            <!-- Export dropdown -->
            <div id="admin-export-dropdown" class="relative shrink-0" @click.stop>
              <button
                type="button"
                @click="exportDropdownOpen = !exportDropdownOpen"
                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                Copiar JSON
                <svg class="h-3.5 w-3.5 transition-transform" :class="exportDropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <Transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
              >
                <div
                  v-if="exportDropdownOpen"
                  class="absolute right-0 top-full mt-2 w-72 rounded-xl border border-wc-border bg-wc-bg-secondary shadow-xl z-40 py-1"
                >
                  <button
                    v-for="sec in exportSectionsVisible"
                    :key="sec.key"
                    type="button"
                    @click="copySection(sec.key)"
                    :disabled="copyingSection !== null"
                    class="w-full flex items-center justify-between px-4 py-2.5 text-left text-sm font-medium hover:bg-wc-bg-tertiary transition-colors disabled:opacity-50"
                    :class="sec.accent ? 'text-wc-accent' : 'text-wc-text-secondary hover:text-wc-text'"
                  >
                    <span>{{ sec.label }}</span>
                    <svg v-if="copyingSection === sec.key" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                  </button>
                </div>
              </Transition>
            </div>
            </div>
          </div>

          <!-- Instructions banner -->
          <div v-if="instructions" class="mt-4 rounded-lg border border-blue-500/30 bg-blue-500/5 p-3">
            <div class="flex items-start gap-2">
              <svg class="h-4 w-4 shrink-0 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
              </svg>
              <div class="text-xs text-blue-500/90 whitespace-pre-wrap leading-relaxed">{{ instructions }}</div>
            </div>
          </div>
          <div v-else-if="loadingInstructions" class="mt-4 h-8 animate-pulse rounded-lg bg-wc-bg-secondary"></div>
        </div>

        <!-- Generated plans section -->
        <div v-if="generatedPlanIds.length > 0" class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4">
          <div class="flex items-start gap-3 flex-wrap">
            <svg class="h-5 w-5 shrink-0 text-emerald-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-emerald-500">Planes generados</p>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <span v-for="id in generatedPlanIds" :key="id" class="rounded-md bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold font-data text-emerald-500">#{{ id }}</span>
              </div>
              <RouterLink
                v-if="ticket.client_id"
                :to="`/admin/clients/${ticket.client_id}/plans`"
                class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-emerald-500 hover:text-emerald-400"
              >
                Ver planes del cliente
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Status actions -->
        <div class="flex flex-wrap gap-2">
          <button
            v-if="ticket.status === 'pendiente'"
            @click="updateStatus('en_revision')"
            :disabled="updatingStatus"
            class="rounded-lg border border-blue-500/30 bg-blue-500/5 px-4 py-2 text-sm font-semibold text-blue-500 hover:bg-blue-500/10 transition disabled:opacity-50"
          >Marcar en revision</button>
          <button
            v-if="ticket.status === 'en_revision'"
            @click="completeModalOpen = true"
            :disabled="updatingStatus"
            class="rounded-lg border border-emerald-500/30 bg-emerald-500/5 px-4 py-2 text-sm font-semibold text-emerald-500 hover:bg-emerald-500/10 transition disabled:opacity-50"
          >Marcar como completado</button>
          <button
            v-if="['pendiente','en_revision'].includes(ticket.status)"
            @click="rejectModalOpen = true"
            :disabled="updatingStatus"
            class="rounded-lg border border-red-500/30 bg-red-500/5 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-500/10 transition disabled:opacity-50"
          >Rechazar</button>
        </div>

        <!-- Coach notes (readonly) -->
        <div v-if="ticket.notas_coach" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Notas del coach</p>
          <p class="whitespace-pre-wrap text-sm text-wc-text">{{ ticket.notas_coach }}</p>
        </div>

        <!-- Datos generales -->
        <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('datos')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Datos generales</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.datos ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.datos" class="border-t border-wc-border p-5 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
            <div><p class="text-xs text-wc-text-tertiary">Nombre</p><p class="font-medium text-wc-text">{{ datosGenerales.nombre || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Plan</p><p class="font-medium text-wc-text">{{ humanLabel(datosGenerales.plan) }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Edad</p><p class="font-medium text-wc-text">{{ datosGenerales.edad || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Genero</p><p class="font-medium text-wc-text">{{ humanLabel(datosGenerales.genero) }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Peso</p><p class="font-medium text-wc-text">{{ datosGenerales.peso ? datosGenerales.peso + ' kg' : '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Estatura</p><p class="font-medium text-wc-text">{{ datosGenerales.estatura ? datosGenerales.estatura + ' cm' : '-' }}</p></div>
            <div class="col-span-2 sm:col-span-3"><p class="text-xs text-wc-text-tertiary">Actividad diaria</p><p class="font-medium text-wc-text">{{ humanLabel(datosGenerales.actividad_diaria) }}</p></div>
            <div class="col-span-2 sm:col-span-3"><p class="text-xs text-wc-text-tertiary">Objetivo</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ datosGenerales.objetivo || '-' }}</p></div>
          </div>
        </section>

        <!-- Entrenamiento -->
        <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('entrenamiento')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Plan de entrenamiento</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.entrenamiento ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.entrenamiento" class="border-t border-wc-border p-5 space-y-4 text-sm">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div><p class="text-xs text-wc-text-tertiary">Lugar</p><p class="font-medium text-wc-text">{{ humanLabel(planEntrenamiento.lugar) }}</p></div>
              <div><p class="text-xs text-wc-text-tertiary">Dias/semana</p><p class="font-medium text-wc-text">{{ planEntrenamiento.dias_semana || '-' }}</p></div>
              <div><p class="text-xs text-wc-text-tertiary">Tiempo pesas</p><p class="font-medium text-wc-text">{{ planEntrenamiento.tiempo_pesas_min || '-' }} min</p></div>
              <div><p class="text-xs text-wc-text-tertiary">Tiempo cardio</p><p class="font-medium text-wc-text">{{ planEntrenamiento.tiempo_cardio_min || '-' }} min</p></div>
              <div class="col-span-2 sm:col-span-4"><p class="text-xs text-wc-text-tertiary">Nivel</p><p class="font-medium text-wc-text">{{ humanLabel(planEntrenamiento.nivel) }}</p></div>
            </div>

            <div v-if="planEntrenamiento.implementos && planEntrenamiento.implementos.length">
              <p class="text-xs text-wc-text-tertiary mb-1">Implementos</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="i in planEntrenamiento.implementos" :key="i" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-xs text-wc-text">{{ humanLabel(i) }}</span>
              </div>
            </div>

            <div v-if="planEntrenamiento.modalidad_cardio && planEntrenamiento.modalidad_cardio.length">
              <p class="text-xs text-wc-text-tertiary mb-1">Modalidad cardio</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="m in planEntrenamiento.modalidad_cardio" :key="m" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-xs text-wc-text">{{ humanLabel(m) }}</span>
              </div>
            </div>

            <div><p class="text-xs text-wc-text-tertiary">Preferencia cardio</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planEntrenamiento.preferencia_cardio || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Lesiones</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planEntrenamiento.lesiones || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Restricciones</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planEntrenamiento.restricciones || '-' }}</p></div>

            <div v-if="splitEntries.length">
              <p class="text-xs text-wc-text-tertiary mb-2">Split semanal</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div v-for="[day, info] in splitEntries" :key="day" class="rounded-lg bg-wc-bg-secondary p-3">
                  <p class="text-xs font-semibold text-wc-text mb-1">{{ humanLabel(day) }}</p>
                  <div class="flex flex-wrap gap-1 mb-1">
                    <span v-for="g in (info?.grupos || [])" :key="g" class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-medium text-wc-accent">{{ humanLabel(g) }}</span>
                    <span v-if="!info?.grupos || info.grupos.length === 0" class="text-[10px] text-wc-text-tertiary">-</span>
                  </div>
                  <p v-if="info?.prioridad" class="text-[11px] text-wc-text-tertiary italic">{{ info.prioridad }}</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Nutricion -->
        <section v-if="planNutricional" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('nutricion')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Plan nutricional</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.nutricion ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.nutricion" class="border-t border-wc-border p-5 space-y-3 text-sm">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div><p class="text-xs text-wc-text-tertiary">Comidas/dia</p><p class="font-medium text-wc-text">{{ planNutricional.num_comidas || '-' }}</p></div>
              <div><p class="text-xs text-wc-text-tertiary">Metodologia</p><p class="font-medium text-wc-text">{{ humanLabel(planNutricional.metodologia) }}</p></div>
              <div class="col-span-2 sm:col-span-1">
                <p class="text-xs text-wc-text-tertiary">Horarios</p>
                <div class="flex flex-wrap gap-1 mt-0.5">
                  <span v-for="(h,i) in (planNutricional.horarios || [])" :key="i" class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[11px] text-wc-text">{{ h }}</span>
                  <span v-if="!planNutricional.horarios || !planNutricional.horarios.length" class="text-xs text-wc-text-tertiary">-</span>
                </div>
              </div>
            </div>
            <div><p class="text-xs text-wc-text-tertiary">Objetivo</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planNutricional.objetivo || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Alimentos NO incluir</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planNutricional.alimentos_no_incluir || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Alimentos a priorizar</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planNutricional.alimentos_priorizar || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Configuracion</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planNutricional.configuracion_comidas || '-' }}</p></div>
          </div>
        </section>

        <!-- Habitos -->
        <section v-if="planHabitos" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('habitos')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Plan de habitos</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.habitos ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.habitos" class="border-t border-wc-border p-5 space-y-3 text-sm">
            <div v-if="planHabitos.areas_foco && planHabitos.areas_foco.length">
              <p class="text-xs text-wc-text-tertiary mb-1">Areas de foco</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="a in planHabitos.areas_foco" :key="a" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-xs text-wc-text">{{ humanLabel(a) }}</span>
              </div>
            </div>
            <div><p class="text-xs text-wc-text-tertiary">Rutina matutina</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planHabitos.rutina_matutina || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Rutina nocturna</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planHabitos.rutina_nocturna || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Otros</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planHabitos.otros || '-' }}</p></div>
          </div>
        </section>

        <!-- Suplementacion -->
        <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('suplementacion')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Plan de suplementacion</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.suplementacion ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.suplementacion" class="border-t border-wc-border p-5 space-y-4 text-sm">
            <div>
              <p class="text-xs text-wc-text-tertiary">Objetivo del stack</p>
              <p class="whitespace-pre-wrap font-medium text-wc-text">{{ planSuplementacion.objetivo || '-' }}</p>
            </div>

            <div>
              <p class="text-xs text-wc-text-tertiary mb-2">Suplementos ({{ suplementos.length }})</p>
              <div v-if="suplementos.length === 0" class="rounded-lg bg-wc-bg-secondary p-3 text-xs text-wc-text-tertiary">
                Sin suplementos capturados.
              </div>
              <div v-else class="overflow-x-auto">
                <table class="w-full text-xs">
                  <thead>
                    <tr class="border-b border-wc-border text-left text-wc-text-tertiary">
                      <th class="py-2 pr-3 font-semibold">Nombre</th>
                      <th class="py-2 pr-3 font-semibold">Dosis</th>
                      <th class="py-2 pr-3 font-semibold">Momento</th>
                      <th class="py-2 pr-3 font-semibold">Frecuencia</th>
                      <th class="py-2 font-semibold">Notas</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(s, i) in suplementos" :key="i" class="border-b border-wc-border/50 last:border-0">
                      <td class="py-2 pr-3 font-medium text-wc-text">{{ s.nombre || '-' }}</td>
                      <td class="py-2 pr-3 text-wc-text">{{ s.dosis || '-' }}</td>
                      <td class="py-2 pr-3 text-wc-text">{{ s.momento || '-' }}</td>
                      <td class="py-2 pr-3 text-wc-text">{{ s.frecuencia ? frecuenciaLabel(s.frecuencia) : '-' }}</td>
                      <td class="py-2 text-wc-text-secondary">{{ s.notas || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <p class="text-xs text-wc-text-tertiary">Notas del coach</p>
              <p class="whitespace-pre-wrap font-medium text-wc-text">{{ planSuplementacion.notas_coach || '-' }}</p>
            </div>
          </div>
        </section>

        <!-- Ciclo -->
        <section v-if="isElite && planCiclo" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button @click="toggleSection('ciclo')" class="w-full flex items-center justify-between p-5 hover:bg-wc-bg-secondary/30 transition">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Ciclo hormonal</h2>
            <svg class="h-4 w-4 transition-transform text-wc-text-tertiary" :class="expandedSections.ciclo ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>
          <div v-show="expandedSections.ciclo" class="border-t border-wc-border p-5 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
            <div><p class="text-xs text-wc-text-tertiary">Ultima menstruacion</p><p class="font-medium text-wc-text">{{ planCiclo.fecha_ultima_menstruacion || '-' }}</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Duracion ciclo</p><p class="font-medium text-wc-text">{{ planCiclo.duracion_ciclo_dias || '-' }} dias</p></div>
            <div><p class="text-xs text-wc-text-tertiary">Anticonceptivo</p><p class="font-medium text-wc-text">{{ planCiclo.anticonceptivo || '-' }}</p></div>
            <div class="col-span-2 sm:col-span-3"><p class="text-xs text-wc-text-tertiary">Sintomas</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planCiclo.sintomas || '-' }}</p></div>
            <div class="col-span-2 sm:col-span-3"><p class="text-xs text-wc-text-tertiary">Notas</p><p class="whitespace-pre-wrap font-medium text-wc-text">{{ planCiclo.notas || '-' }}</p></div>
          </div>
        </section>

        <!-- Attachments -->
        <section class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Adjuntos ({{ attachments.length }})</p>
          <div v-if="loadingAttachments" class="animate-pulse h-16 rounded-lg bg-wc-bg-secondary"></div>
          <div v-else-if="attachments.length === 0" class="rounded-lg bg-wc-bg-secondary p-4 text-center text-xs text-wc-text-tertiary">
            Sin archivos adjuntos.
          </div>
          <ul v-else class="space-y-2">
            <li
              v-for="att in attachments"
              :key="att.id"
              class="flex items-center gap-3 rounded-lg bg-wc-bg-secondary p-3"
            >
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-bg-tertiary">
                <svg v-if="mimeIcon(att.mime) === 'image'" class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                </svg>
                <svg v-else-if="mimeIcon(att.mime) === 'pdf'" class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <svg v-else class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text truncate">{{ att.original_name }}</p>
                <p class="text-[11px] text-wc-text-tertiary">
                  {{ formatBytes(att.size_bytes) }}
                  <span v-if="att.category"> · {{ humanLabel(att.category) }}</span>
                  · {{ att.uploaded_by_name || '-' }} · {{ formatRelative(att.created_at) }}
                </p>
              </div>
              <a
                :href="att.url"
                target="_blank"
                rel="noopener"
                class="rounded-md border border-wc-border bg-wc-bg-tertiary px-2.5 py-1 text-[11px] font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text transition"
              >Ver</a>
              <button
                type="button"
                @click="deleteAttachment(att)"
                class="rounded-md border border-red-500/30 px-2 py-1 text-[11px] font-medium text-red-400 hover:bg-red-500/10 transition"
              >Eliminar</button>
            </li>
          </ul>
        </section>

        <!-- Comments thread -->
        <PlanTicketComments
          :endpoint-base="`/api/v/admin/plan-tickets/${route.params.id}`"
          role="admin"
        />

        <!-- Admin notas -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Notas del admin</p>
          <textarea
            v-model="adminNotas"
            rows="3"
            placeholder="Notas internas del equipo WellCore..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          ></textarea>
          <div class="mt-2 flex justify-end">
            <button
              @click="saveAdminNotas"
              :disabled="savingNotas"
              class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50"
            >{{ savingNotas ? 'Guardando...' : 'Guardar notas' }}</button>
          </div>
        </div>
      </template>

      <!-- Reject modal (structured) -->
      <Transition name="fade">
        <div v-if="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="rejectModalOpen = false">
          <div class="w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Rechazar ticket</h3>
            <p class="text-sm text-wc-text-secondary">Selecciona la razon del rechazo para que el coach entienda el problema.</p>

            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Razon del rechazo</label>
              <select
                v-model="rejectionCode"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option value="">Selecciona una razon...</option>
                <option v-for="r in REJECTION_REASONS" :key="r.value" :value="r.value">{{ r.label }}</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                Detalles <span v-if="rejectionCode === 'otro'" class="text-red-400">(obligatorio)</span><span v-else class="text-wc-text-tertiary">(opcional)</span>
              </label>
              <textarea
                v-model="rejectionDetails"
                rows="4"
                placeholder="Detalles adicionales para el coach..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2">
              <button
                @click="rejectModalOpen = false"
                class="rounded-lg border border-wc-border px-4 py-2 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg-tertiary"
              >Cancelar</button>
              <button
                @click="submitReject"
                :disabled="!rejectionCode || updatingStatus || (rejectionCode === 'otro' && !rejectionDetails.trim())"
                class="rounded-lg bg-red-500/90 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:opacity-50"
              >{{ updatingStatus ? 'Rechazando...' : 'Confirmar rechazo' }}</button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Complete modal -->
      <Transition name="fade">
        <div v-if="completeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="completeModalOpen = false">
          <div class="w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Marcar como completado</h3>
            <p class="text-sm text-wc-text-secondary">Opcional: pega los IDs de los planes generados (assigned_plans) separados por comas. Se asociaran al ticket para facilitar el rastreo.</p>

            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">IDs de planes generados</label>
              <textarea
                v-model="completePlanIds"
                rows="3"
                placeholder="Ej: 142, 143, 144, 145"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-sm text-wc-text font-data focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              ></textarea>
              <p class="mt-1 text-[11px] text-wc-text-tertiary">Acepta numeros separados por comas, espacios o saltos de linea.</p>
            </div>

            <div class="flex justify-end gap-2">
              <button
                @click="completeModalOpen = false"
                class="rounded-lg border border-wc-border px-4 py-2 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg-tertiary"
              >Cancelar</button>
              <button
                @click="submitComplete"
                :disabled="updatingStatus"
                class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 disabled:opacity-50"
              >{{ updatingStatus ? 'Guardando...' : 'Confirmar completado' }}</button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
