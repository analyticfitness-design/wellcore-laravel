<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const ticket = ref(null);
const adminNotas = ref('');
const savingNotas = ref(false);

const rejectModalOpen = ref(false);
const rejectReason = ref('');
const updatingStatus = ref(false);
const copying = ref(false);
const toast = ref(null);

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

async function copyJson() {
  copying.value = true;
  try {
    const { data } = await api.get(`/api/v/admin/plan-tickets/${route.params.id}/export`);
    const jsonText = JSON.stringify(data, null, 2);
    await navigator.clipboard.writeText(jsonText);
    showToast('success', 'JSON copiado. Pegalo en Claude Code de tu PC para generar el plan.');
  } catch (e) {
    showToast('error', 'No se pudo copiar el JSON.');
  } finally {
    copying.value = false;
  }
}

async function updateStatus(status, notas = null) {
  updatingStatus.value = true;
  try {
    const payload = { status };
    if (notas !== null && notas !== undefined) payload.admin_notas = notas;
    const { data } = await api.post(`/api/v/admin/plan-tickets/${route.params.id}/status`, payload);
    ticket.value = data.ticket;
    adminNotas.value = data.ticket.admin_notas || '';
    showToast('success', `Ticket marcado como ${statusMeta(status).label.toLowerCase()}.`);
    rejectModalOpen.value = false;
    rejectReason.value = '';
  } catch (e) {
    showToast('error', 'No se pudo actualizar el estado.');
  } finally {
    updatingStatus.value = false;
  }
}

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

onMounted(fetchTicket);

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
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-wc-text-tertiary">
                <div><span class="text-wc-text-secondary">Coach:</span> {{ ticket.coach_name || '-' }}</div>
                <div><span class="text-wc-text-secondary">Creado:</span> {{ formatDate(ticket.created_at) }}</div>
                <div><span class="text-wc-text-secondary">Enviado:</span> {{ formatDate(ticket.submitted_at) }}</div>
              </div>
            </div>
            <button
              @click="copyJson"
              :disabled="copying"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
              </svg>
              {{ copying ? 'Copiando...' : 'Copiar JSON para Claude Code' }}
            </button>
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
            @click="updateStatus('completado')"
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

      <!-- Reject modal -->
      <Transition name="fade">
        <div v-if="rejectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="rejectModalOpen = false">
          <div class="w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-5 space-y-4">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Rechazar ticket</h3>
            <p class="text-sm text-wc-text-secondary">Explica el motivo del rechazo para el coach.</p>
            <textarea
              v-model="rejectReason"
              rows="4"
              placeholder="Razon del rechazo..."
              class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            ></textarea>
            <div class="flex justify-end gap-2">
              <button
                @click="rejectModalOpen = false"
                class="rounded-lg border border-wc-border px-4 py-2 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg-tertiary"
              >Cancelar</button>
              <button
                @click="updateStatus('rechazado', rejectReason)"
                :disabled="!rejectReason.trim() || updatingStatus"
                class="rounded-lg bg-red-500/90 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:opacity-50"
              >{{ updatingStatus ? 'Rechazando...' : 'Confirmar rechazo' }}</button>
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
