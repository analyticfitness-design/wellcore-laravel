<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import DeadlineBadge from '../../components/DeadlineBadge.vue';
import PlanTicketComments from '../../components/PlanTicketComments.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const savingIndicator = ref(''); // '' | 'saving' | 'saved'
const submitting = ref(false);
const deleting = ref(false);
const toast = ref(null); // { type, message }

const ticket = ref(null); // current ticket from API
const ticketId = computed(() => ticket.value?.id || null);
const isNew = computed(() => !route.params.id);

// Clients for dropdown (only needed in new mode)
const clients = ref([]);
const loadingClients = ref(false);

// Form state — local copies. Flush to backend via auto-save.
const newForm = ref({ client_id: '', plan_type: '', category: 'plan_nuevo' });

// Attachments
const attachments = ref([]);
const loadingAttachments = ref(false);
const uploadingAttachment = ref(false);
const attachmentCategory = ref('');
const attachmentDragging = ref(false);
const ATTACHMENT_CATEGORY_OPTIONS = [
  { value: '', label: 'Sin categoria' },
  { value: 'foto_progreso', label: 'Foto de progreso' },
  { value: 'laboratorio', label: 'Laboratorio' },
  { value: 'documento_medico', label: 'Documento medico' },
  { value: 'otro', label: 'Otro' },
];
const MAX_ATTACHMENT_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_MIMES = [
  'image/jpeg', 'image/png', 'image/webp', 'image/heic',
  'application/pdf',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

const CATEGORY_META = {
  plan_nuevo: { label: 'Plan nuevo', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  ajuste_plan: { label: 'Ajuste', bg: 'bg-purple-500/10', text: 'text-purple-500' },
};
function categoryMeta(c) { return CATEGORY_META[c] || CATEGORY_META.plan_nuevo; }

const ticketCategory = computed(() => ticket.value?.category || newForm.value.category || 'plan_nuevo');
const isAjuste = computed(() => ticketCategory.value === 'ajuste_plan');

const datosGenerales = ref({
  nombre: '', plan: '', edad: null, genero: '',
  peso: null, estatura: null, actividad_diaria: '', objetivo: '',
});
const planEntrenamiento = ref({
  lugar: '', implementos: [], dias_semana: null,
  tiempo_pesas_min: null, tiempo_cardio_min: null,
  preferencia_cardio: '', modalidad_cardio: [], nivel: '',
  lesiones: '', restricciones: '',
  split: {
    lunes: { grupos: [], prioridad: '' },
    martes: { grupos: [], prioridad: '' },
    miercoles: { grupos: [], prioridad: '' },
    jueves: { grupos: [], prioridad: '' },
    viernes: { grupos: [], prioridad: '' },
    sabado: { grupos: [], prioridad: '' },
    domingo: { grupos: [], prioridad: '' },
  },
});
const planNutricional = ref({
  objetivo: '', num_comidas: null, horarios: [],
  metodologia: '', alimentos_no_incluir: '',
  alimentos_priorizar: '', configuracion_comidas: '',
});
const planHabitos = ref({
  areas_foco: [], rutina_matutina: '', rutina_nocturna: '', otros: '',
});
const planCiclo = ref({
  fecha_ultima_menstruacion: '', duracion_ciclo_dias: null,
  sintomas: '', anticonceptivo: '', notas: '',
});
const planSuplementacion = ref({
  objetivo: '',
  suplementos: [],
  notas_coach: '',
});

const FRECUENCIA_SUPLEMENTO_OPTIONS = [
  { value: 'diario', label: 'Diario' },
  { value: 'dias_entrenamiento', label: 'Dias de entrenamiento' },
  { value: '3_veces_semana', label: '3 veces por semana' },
  { value: 'ciclico', label: 'Ciclico' },
];

function emptySuplemento() {
  return { nombre: '', dosis: '', momento: '', frecuencia: '', notas: '' };
}
function addSuplemento() {
  if (!planSuplementacion.value.suplementos) planSuplementacion.value.suplementos = [];
  planSuplementacion.value.suplementos.push(emptySuplemento());
}
function removeSuplemento(idx) {
  planSuplementacion.value.suplementos.splice(idx, 1);
}

const missingFields = ref([]);
const step = ref(0);

// Static constants
const IMPLEMENTOS_OPTIONS = [
  'bandas_elasticas', 'mancuernas', 'polea', 'trx', 'peso_corporal',
  'barra', 'kettlebell', 'step', 'pelota_pilates', 'liga_circular',
];
const MODALIDAD_CARDIO_OPTIONS = [
  'caminadora', 'escaladora', 'caminata_exterior', 'rumbaterapia',
  'eliptica', 'bicicleta', 'hiit', 'spinning',
];
const GRUPOS_MUSCULARES = [
  'pecho', 'espalda', 'hombros', 'biceps', 'triceps', 'piernas',
  'gluteos', 'core', 'cardio', 'descanso',
];
const DIAS_SEMANA = [
  { key: 'lunes', label: 'Lunes' },
  { key: 'martes', label: 'Martes' },
  { key: 'miercoles', label: 'Miercoles' },
  { key: 'jueves', label: 'Jueves' },
  { key: 'viernes', label: 'Viernes' },
  { key: 'sabado', label: 'Sabado' },
  { key: 'domingo', label: 'Domingo' },
];
const NIVEL_ACTIVIDAD = [
  { value: 'sedentario', label: 'Sedentario (trabajo de oficina, poco movimiento)' },
  { value: 'ligero', label: 'Ligero (ejercicio 1-2x por semana)' },
  { value: 'moderado', label: 'Moderado (ejercicio 3-4x por semana)' },
  { value: 'activo', label: 'Activo (ejercicio 5-6x por semana)' },
  { value: 'muy_activo', label: 'Muy activo (trabajo fisico + ejercicio)' },
];
const METODOLOGIAS = [
  { value: 'deficit_calorico', label: 'Deficit calorico', desc: 'Perdida de grasa con deficit sostenible.' },
  { value: 'flexible', label: 'Flexible / IIFYM', desc: 'Macros flexibles, preferencias libres.' },
  { value: 'carb_cycling', label: 'Carb cycling', desc: 'Ciclado de carbohidratos alto/bajo.' },
  { value: 'ayuno_intermitente', label: 'Ayuno intermitente', desc: 'Ventana de alimentacion (16:8 / 18:6).' },
  { value: 'mantenimiento', label: 'Mantenimiento', desc: 'Calorias de mantenimiento para recomposicion.' },
  { value: 'volumen_limpio', label: 'Volumen limpio', desc: 'Superavit calorico controlado.' },
];
const AREAS_FOCO_HABITOS = [
  'sueño', 'estres', 'hidratacion', 'mindfulness',
  'recuperacion_activa', 'rutina_matutina', 'rutina_nocturna',
];

// Derived: plan type
const planType = computed(() => ticket.value?.plan_type || newForm.value.plan_type);
const isElite = computed(() => planType.value === 'elite');
const isMetodoOrElite = computed(() => ['metodo', 'elite'].includes(planType.value));

// Readonly when status not editable — strict: only borrador or pendiente are editable
const isEditable = computed(() => {
  if (!ticket.value) return true;
  const status = ticket.value.status;
  const editableStatuses = ['borrador', 'pendiente'];
  // Prefer backend flag when present, but still enforce status guard
  if (typeof ticket.value.is_editable === 'boolean') {
    return ticket.value.is_editable && editableStatuses.includes(status);
  }
  return editableStatuses.includes(status);
});

// Resubmit indicator
const wasResubmitted = computed(() => {
  if (!ticket.value?.resubmitted_at) return false;
  const resub = new Date(ticket.value.resubmitted_at).getTime();
  const submitted = ticket.value.submitted_at ? new Date(ticket.value.submitted_at).getTime() : 0;
  return resub > submitted;
});

function formatDateTimeShort(d) {
  if (!d) return '';
  try {
    return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
  } catch { return d; }
}

const statusBannerMessage = computed(() => {
  if (!ticket.value) return null;
  const s = ticket.value.status;
  if (s === 'en_revision') return 'Este ticket esta siendo revisado por el equipo WellCore. No se puede editar.';
  if (s === 'completado') return 'Este ticket ya fue completado. El plan esta asignado al cliente.';
  if (s === 'rechazado') return 'Este ticket fue rechazado por el equipo WellCore. Revisa los comentarios y crea un ticket nuevo si es necesario.';
  return null;
});

// ============ Auto-fill + field highlight ============
const autofillLoading = ref(false);
const highlightedFields = ref(new Set());

function flashFields(keys) {
  for (const k of keys) highlightedFields.value.add(k);
  // Clear after 3s
  setTimeout(() => {
    for (const k of keys) highlightedFields.value.delete(k);
    // Trigger reactivity
    highlightedFields.value = new Set(highlightedFields.value);
  }, 3000);
  highlightedFields.value = new Set(highlightedFields.value);
}

function isHighlighted(key) {
  return highlightedFields.value.has(key);
}

async function autofillFromProfile() {
  const clientId = ticket.value?.client_id || newForm.value.client_id;
  if (!clientId) {
    showToast('error', 'No hay cliente asociado al ticket.');
    return;
  }
  autofillLoading.value = true;
  try {
    const { data } = await api.get(`/api/v/coach/plan-tickets/autofill`, { params: { client_id: clientId } });
    const dg = data?.datos_generales || {};
    const pe = data?.plan_entrenamiento || {};
    let filledCount = 0;
    const flashed = [];
    for (const [key, val] of Object.entries(dg)) {
      if (val === null || val === undefined || val === '') continue;
      const prev = datosGenerales.value[key];
      const changed = prev !== val;
      datosGenerales.value[key] = val;
      filledCount++;
      if (changed) {
        flashed.push(`dg.${key}`);
      }
    }
    for (const [key, val] of Object.entries(pe)) {
      if (val === null || val === undefined || val === '') continue;
      if (Array.isArray(val) && val.length === 0) continue;
      const prev = planEntrenamiento.value[key];
      const changed = JSON.stringify(prev) !== JSON.stringify(val);
      planEntrenamiento.value[key] = val;
      filledCount++;
      if (changed) flashed.push(`pe.${key}`);
    }
    if (filledCount === 0) {
      showToast('info', 'No hay datos previos para pre-llenar.');
    } else {
      flashFields(flashed);
      showToast('success', `${filledCount} campo${filledCount === 1 ? '' : 's'} rellenado${filledCount === 1 ? '' : 's'} desde el perfil.`);
    }
  } catch (e) {
    showToast('error', 'No se pudieron cargar los datos del perfil.');
  } finally {
    autofillLoading.value = false;
  }
}

// ============ Duplicate from previous (Paso 0) ============
const previousTickets = ref([]);
const loadingPrev = ref(false);
const selectedPrevTicketId = ref('');
const duplicatingPrev = ref(false);

async function fetchPreviousTickets(clientId) {
  if (!clientId) {
    previousTickets.value = [];
    return;
  }
  loadingPrev.value = true;
  try {
    const { data } = await api.get('/api/v/coach/plan-tickets', { params: { client_id: clientId, status: 'completado' } });
    previousTickets.value = data.tickets || [];
  } catch (e) {
    previousTickets.value = [];
  } finally {
    loadingPrev.value = false;
  }
}

async function duplicateFromPrevious() {
  if (!selectedPrevTicketId.value) return;
  if (!confirm('Crear un nuevo borrador duplicando este ticket previo?')) return;
  duplicatingPrev.value = true;
  try {
    const { data } = await api.post(`/api/v/coach/plan-tickets/${selectedPrevTicketId.value}/duplicate`);
    if (data?.ticket?.id) {
      showToast('success', 'Ticket duplicado. Abriendo borrador...');
      setTimeout(() => router.push(`/coach/plan-tickets/${data.ticket.id}`), 400);
    }
  } catch (e) {
    showToast('error', 'No se pudo duplicar el ticket previo.');
  } finally {
    duplicatingPrev.value = false;
  }
}

// Watch client selection for new tickets -> fetch previous
watch(() => newForm.value.client_id, (v) => {
  selectedPrevTicketId.value = '';
  fetchPreviousTickets(v);
});

// Steps array built dynamically
const steps = computed(() => {
  const list = [];
  list.push({ key: 'cliente', label: 'Cliente y Plan' });
  list.push({ key: 'datos', label: 'Datos Generales' });
  list.push({ key: 'entrenamiento', label: 'Entrenamiento' });
  list.push({ key: 'nutricion', label: 'Nutricion' });
  list.push({ key: 'habitos', label: 'Habitos' });
  list.push({ key: 'suplementacion', label: 'Suplementacion' });
  if (isElite.value) list.push({ key: 'ciclo', label: 'Ciclo Hormonal' });
  list.push({ key: 'adjuntos', label: 'Adjuntos' });
  list.push({ key: 'revision', label: 'Revision y Envio' });
  return list;
});

const currentStepKey = computed(() => steps.value[step.value]?.key);
const progressPct = computed(() => Math.round(((step.value + 1) / steps.value.length) * 100));

// ============ Load ============

async function fetchClients() {
  loadingClients.value = true;
  try {
    const { data } = await api.get('/api/v/coach/clients');
    clients.value = data.clients || [];
  } catch (e) {
    clients.value = [];
  } finally {
    loadingClients.value = false;
  }
}

async function fetchTicket(id) {
  loading.value = true;
  try {
    const { data } = await api.get(`/api/v/coach/plan-tickets/${id}`);
    ticket.value = data.ticket;
    hydrate(data.ticket);
    fetchAttachments();
  } catch (e) {
    showToast('error', 'No se pudo cargar el ticket.');
  } finally {
    loading.value = false;
  }
}

async function fetchAttachments() {
  if (!ticketId.value) return;
  loadingAttachments.value = true;
  try {
    const { data } = await api.get(`/api/v/coach/plan-tickets/${ticketId.value}/attachments`);
    attachments.value = data.attachments || [];
  } catch (e) {
    attachments.value = [];
  } finally {
    loadingAttachments.value = false;
  }
}

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

async function uploadAttachment(file) {
  if (!ticketId.value || !file) return;
  if (file.size > MAX_ATTACHMENT_SIZE) {
    showToast('error', 'El archivo excede 10MB.');
    return;
  }
  if (file.type && !ALLOWED_MIMES.includes(file.type)) {
    showToast('error', 'Tipo de archivo no permitido.');
    return;
  }
  uploadingAttachment.value = true;
  try {
    const formData = new FormData();
    formData.append('file', file);
    if (attachmentCategory.value) formData.append('category', attachmentCategory.value);
    await api.post(`/api/v/coach/plan-tickets/${ticketId.value}/attachments`, formData);
    showToast('success', 'Archivo subido');
    await fetchAttachments();
  } catch (e) {
    const msg = e?.response?.data?.message || 'No se pudo subir el archivo.';
    showToast('error', msg);
  } finally {
    uploadingAttachment.value = false;
  }
}

function onAttachmentInput(e) {
  const file = e.target.files?.[0];
  if (file) uploadAttachment(file);
  e.target.value = '';
}

function onAttachmentDrop(e) {
  attachmentDragging.value = false;
  const file = e.dataTransfer?.files?.[0];
  if (file) uploadAttachment(file);
}

async function deleteAttachment(att) {
  if (!ticketId.value) return;
  if (!confirm('¿Eliminar este archivo?')) return;
  try {
    await api.delete(`/api/v/coach/plan-tickets/${ticketId.value}/attachments/${att.id}`);
    showToast('success', 'Archivo eliminado');
    await fetchAttachments();
  } catch (e) {
    showToast('error', 'No se pudo eliminar.');
  }
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

function hydrate(t) {
  if (!t) return;
  if (t.datos_generales) Object.assign(datosGenerales.value, t.datos_generales);
  if (t.plan_entrenamiento) {
    const pe = { ...t.plan_entrenamiento };
    if (!pe.implementos) pe.implementos = [];
    if (!pe.modalidad_cardio) pe.modalidad_cardio = [];
    if (!pe.split) pe.split = planEntrenamiento.value.split;
    else {
      // ensure each day exists
      for (const d of DIAS_SEMANA) {
        if (!pe.split[d.key]) pe.split[d.key] = { grupos: [], prioridad: '' };
        if (!pe.split[d.key].grupos) pe.split[d.key].grupos = [];
      }
    }
    planEntrenamiento.value = { ...planEntrenamiento.value, ...pe };
  }
  if (t.plan_nutricional) {
    const pn = { ...t.plan_nutricional };
    if (!pn.horarios) pn.horarios = [];
    planNutricional.value = { ...planNutricional.value, ...pn };
  }
  if (t.plan_habitos) {
    const ph = { ...t.plan_habitos };
    if (!ph.areas_foco) ph.areas_foco = [];
    planHabitos.value = { ...planHabitos.value, ...ph };
  }
  if (t.plan_ciclo) {
    planCiclo.value = { ...planCiclo.value, ...t.plan_ciclo };
  }
  if (t.plan_suplementacion) {
    const ps = { ...t.plan_suplementacion };
    if (!Array.isArray(ps.suplementos)) ps.suplementos = [];
    // Ensure each supplemento has all fields
    ps.suplementos = ps.suplementos.map(s => ({ ...emptySuplemento(), ...s }));
    planSuplementacion.value = { ...planSuplementacion.value, ...ps };
  }
  if (t.datos_generales?.plan) {
    // fine
  } else if (t.plan_type) {
    datosGenerales.value.plan = t.plan_type;
  }
}

// ============ Auto-save ============

let saveTimer = null;
function scheduleSave(payload) {
  if (!ticketId.value) return;
  if (!isEditable.value) return;
  savingIndicator.value = 'saving';
  clearTimeout(saveTimer);
  saveTimer = setTimeout(() => runSave(payload), 1500);
}

async function runSave(payload) {
  if (!ticketId.value) return;
  saving.value = true;
  try {
    const { data } = await api.put(`/api/v/coach/plan-tickets/${ticketId.value}`, payload);
    ticket.value = data.ticket;
    savingIndicator.value = 'saved';
    setTimeout(() => { if (savingIndicator.value === 'saved') savingIndicator.value = ''; }, 2000);
  } catch (e) {
    savingIndicator.value = '';
    showToast('error', 'No se pudo guardar.');
  } finally {
    saving.value = false;
  }
}

// Deep watchers for sections — per-field PUTs
watch(datosGenerales, (val) => scheduleSave({ datos_generales: val }), { deep: true });
watch(planEntrenamiento, (val) => scheduleSave({ plan_entrenamiento: val }), { deep: true });
watch(planNutricional, (val) => scheduleSave({ plan_nutricional: val }), { deep: true });
watch(planHabitos, (val) => scheduleSave({ plan_habitos: val }), { deep: true });
watch(planSuplementacion, (val) => scheduleSave({ plan_suplementacion: val }), { deep: true });
watch(planCiclo, (val) => {
  if (!isElite.value) return;
  scheduleSave({ plan_ciclo: val });
}, { deep: true });

// ============ Create ticket (new flow) ============

async function createTicket() {
  if (!newForm.value.client_id || !newForm.value.plan_type) {
    showToast('error', 'Selecciona un cliente y un tipo de plan.');
    return;
  }
  loading.value = true;
  try {
    const { data } = await api.post('/api/v/coach/plan-tickets', {
      client_id: newForm.value.client_id,
      plan_type: newForm.value.plan_type,
      category: newForm.value.category,
    });
    ticket.value = data.ticket;
    // Pre-fill datos_generales.plan
    datosGenerales.value.plan = newForm.value.plan_type;
    // Replace URL to edit mode
    await router.replace(`/coach/plan-tickets/${data.ticket.id}`);
    step.value = 1;
    showToast('success', 'Ticket creado. Completa el brief.');
  } catch (e) {
    showToast('error', 'No se pudo crear el ticket.');
  } finally {
    loading.value = false;
  }
}

// ============ Submit ============

async function submitTicket() {
  if (!ticketId.value) return;
  submitting.value = true;
  missingFields.value = [];
  try {
    // Force flush any pending save
    clearTimeout(saveTimer);
    await runSave({
      datos_generales: datosGenerales.value,
      plan_entrenamiento: planEntrenamiento.value,
      plan_nutricional: planNutricional.value,
      plan_habitos: planHabitos.value,
      plan_suplementacion: planSuplementacion.value,
      ...(isElite.value ? { plan_ciclo: planCiclo.value } : {}),
    });
    const { data } = await api.post(`/api/v/coach/plan-tickets/${ticketId.value}/submit`);
    ticket.value = data.ticket;
    showToast('success', 'Ticket enviado al equipo WellCore.');
    setTimeout(() => router.push('/coach/plan-tickets'), 800);
  } catch (e) {
    if (e.response?.status === 422) {
      missingFields.value = e.response.data?.missing || [];
      showToast('error', 'Faltan campos para enviar el ticket.');
    } else {
      showToast('error', 'No se pudo enviar el ticket.');
    }
  } finally {
    submitting.value = false;
  }
}

async function deleteDraft() {
  if (!ticketId.value) return;
  if (!confirm('¿Eliminar este borrador? No se puede deshacer.')) return;
  deleting.value = true;
  try {
    await api.delete(`/api/v/coach/plan-tickets/${ticketId.value}`);
    showToast('success', 'Borrador eliminado.');
    setTimeout(() => router.push('/coach/plan-tickets'), 500);
  } catch (e) {
    showToast('error', 'No se pudo eliminar.');
  } finally {
    deleting.value = false;
  }
}

// ============ UI helpers ============

function prev() { if (step.value > 0) step.value--; }
function next() {
  // For new tickets, creating on step 0
  if (isNew.value && step.value === 0 && !ticketId.value) {
    createTicket();
    return;
  }
  if (step.value < steps.value.length - 1) step.value++;
}

function toggleArrayItem(arr, value) {
  const idx = arr.indexOf(value);
  if (idx === -1) arr.push(value);
  else arr.splice(idx, 1);
}

// Horarios input
const newHorario = ref('');
function addHorario() {
  const v = newHorario.value.trim();
  if (!v) return;
  if (!planNutricional.value.horarios) planNutricional.value.horarios = [];
  if (!planNutricional.value.horarios.includes(v)) planNutricional.value.horarios.push(v);
  newHorario.value = '';
}
function removeHorario(idx) {
  planNutricional.value.horarios.splice(idx, 1);
}

function showToast(type, message) {
  toast.value = { type, message };
  setTimeout(() => { toast.value = null; }, 3500);
}

function humanLabel(str) {
  if (!str) return '';
  return String(str).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

onMounted(async () => {
  if (route.params.id) {
    await fetchTicket(route.params.id);
  } else {
    await fetchClients();
    loading.value = false;
  }
});

onBeforeUnmount(() => {
  clearTimeout(saveTimer);
});
</script>

<template>
  <CoachLayout>
    <div class="max-w-5xl mx-auto space-y-6">

      <!-- Toast -->
      <Transition name="fade">
        <div
          v-if="toast"
          class="fixed top-20 right-4 z-50 rounded-lg border px-4 py-3 shadow-lg text-sm font-medium"
          :class="{
            'border-emerald-500/30 bg-emerald-500/10 text-emerald-500': toast.type === 'success',
            'border-red-500/30 bg-red-500/10 text-red-400': toast.type === 'error',
            'border-blue-500/30 bg-blue-500/10 text-blue-500': toast.type === 'info',
          }"
        >
          {{ toast.message }}
        </div>
      </Transition>

      <!-- Header + progress -->
      <div class="space-y-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <button
              @click="router.push('/coach/plan-tickets')"
              class="mb-2 inline-flex items-center gap-1 text-xs font-medium text-wc-text-tertiary hover:text-wc-text"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
              </svg>
              Volver al listado
            </button>
            <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">
              {{ isNew ? 'NUEVO TICKET DE PLAN' : 'TICKET DE PLAN' }}
            </h1>
            <p v-if="ticket" class="mt-1 text-sm text-wc-text-secondary">
              {{ ticket.client_name || '...' }} · {{ humanLabel(ticket.plan_type) }}
            </p>
            <div v-if="ticket" class="mt-2 flex flex-wrap items-center gap-2">
              <DeadlineBadge :deadline="ticket.deadline_at" :status="ticket.status" />
              <span
                v-if="ticket.category"
                class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                :class="[categoryMeta(ticket.category).bg, categoryMeta(ticket.category).text]"
              >{{ categoryMeta(ticket.category).label }}</span>
              <span
                v-if="wasResubmitted"
                class="inline-flex items-center gap-1.5 rounded-full border border-orange-500/30 bg-orange-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-orange-400"
                :title="'Reenviado ' + formatDateTimeShort(ticket.resubmitted_at)"
              >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.36M2.985 19.644v-4.992h4.992m0 0-3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.183m0-4.991v4.99" />
                </svg>
                Editado tras envio · {{ formatDateTimeShort(ticket.resubmitted_at) }}
              </span>
            </div>
          </div>
          <div v-if="ticketId" class="text-xs text-wc-text-tertiary">
            <span v-if="savingIndicator === 'saving'" class="inline-flex items-center gap-1">
              <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-wc-accent"></span> Guardando...
            </span>
            <span v-else-if="savingIndicator === 'saved'" class="inline-flex items-center gap-1 text-emerald-500">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
              Guardado
            </span>
          </div>
        </div>

        <!-- Readonly banner -->
        <div v-if="ticket && !isEditable" class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-400 flex items-start gap-3">
          <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
          </svg>
          <div>
            <p class="font-semibold">Ticket en estado <strong>{{ humanLabel(ticket.status) }}</strong> — solo lectura</p>
            <p class="mt-0.5 text-xs text-red-400/80">{{ statusBannerMessage }}</p>
          </div>
        </div>

        <!-- Step progress -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center justify-between text-xs mb-2">
            <span class="font-semibold text-wc-text">Paso {{ step + 1 }} de {{ steps.length }} · {{ steps[step]?.label }}</span>
            <span class="text-wc-text-tertiary">{{ progressPct }}%</span>
          </div>
          <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div class="h-full bg-wc-accent transition-all duration-300" :style="{ width: progressPct + '%' }"></div>
          </div>
          <div class="mt-3 flex flex-wrap gap-1">
            <button
              v-for="(s, i) in steps"
              :key="s.key"
              @click="ticketId && (step = i)"
              :disabled="!ticketId"
              class="rounded-md px-2 py-1 text-[11px] font-medium transition-colors"
              :class="i === step ? 'bg-wc-accent text-white' : (i < step ? 'bg-wc-accent/20 text-wc-accent' : 'bg-wc-bg-secondary text-wc-text-tertiary')"
            >{{ i + 1 }}. {{ s.label }}</button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-32"></div>
      </div>

      <!-- Wizard content -->
      <template v-else>

        <!-- STEP: cliente -->
        <section v-if="currentStepKey === 'cliente'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">1. Cliente y tipo de plan</h2>

          <div v-if="isNew && !ticketId">
            <div class="space-y-4">
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</label>
                <select v-model="newForm.client_id" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="">Selecciona un cliente...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name || c.full_name || c.email }}</option>
                </select>
                <p v-if="loadingClients" class="mt-1 text-xs text-wc-text-tertiary">Cargando clientes...</p>
              </div>

              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de plan</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <label
                    v-for="opt in [
                      { value: 'esencial', label: 'Esencial', desc: 'Entrenamiento, nutricion, habitos y suplementacion.' },
                      { value: 'metodo', label: 'Metodo', desc: 'Plan completo con seguimiento avanzado.' },
                      { value: 'elite', label: 'Elite', desc: 'Plan completo + ciclo hormonal.' },
                    ]"
                    :key="opt.value"
                    class="cursor-pointer rounded-lg border-2 p-3 transition"
                    :class="newForm.plan_type === opt.value ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40'"
                  >
                    <input type="radio" v-model="newForm.plan_type" :value="opt.value" class="sr-only" />
                    <p class="text-sm font-semibold text-wc-text">{{ opt.label }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ opt.desc }}</p>
                  </label>
                </div>
              </div>

              <!-- Category radio -->
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de solicitud</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <label
                    v-for="opt in [
                      { value: 'plan_nuevo', label: 'Plan nuevo', desc: 'Cliente nuevo o plan completo desde cero. Requiere todas las secciones.' },
                      { value: 'ajuste_plan', label: 'Ajuste de plan', desc: 'Cliente existente que necesita ajustes. Solo llena las secciones que cambian.' },
                    ]"
                    :key="opt.value"
                    class="cursor-pointer rounded-lg border-2 p-3 transition"
                    :class="newForm.category === opt.value ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40'"
                  >
                    <input type="radio" v-model="newForm.category" :value="opt.value" class="sr-only" />
                    <p class="text-sm font-semibold text-wc-text">{{ opt.label }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ opt.desc }}</p>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="space-y-2">
            <div class="rounded-lg bg-wc-bg-secondary p-3">
              <p class="text-xs text-wc-text-tertiary">Cliente</p>
              <p class="text-sm font-semibold text-wc-text">{{ ticket?.client_name || '-' }}</p>
            </div>
            <div class="rounded-lg bg-wc-bg-secondary p-3">
              <p class="text-xs text-wc-text-tertiary">Tipo de plan</p>
              <p class="text-sm font-semibold text-wc-text">{{ humanLabel(ticket?.plan_type) }}</p>
            </div>
            <div class="rounded-lg bg-wc-bg-secondary p-3">
              <p class="text-xs text-wc-text-tertiary">Tipo de solicitud</p>
              <p class="text-sm font-semibold text-wc-text">{{ categoryMeta(ticket?.category).label }}</p>
            </div>
            <p class="text-xs text-wc-text-tertiary">El cliente y tipo de plan no se pueden modificar una vez creado el ticket.</p>
          </div>

          <!-- Duplicar desde ticket previo (solo en creacion, cliente elegido, y hay tickets completados previos) -->
          <div
            v-if="isNew && !ticketId && newForm.client_id && previousTickets.length > 0"
            class="mt-4 rounded-lg border border-blue-500/30 bg-blue-500/5 p-4 space-y-3"
          >
            <div class="flex items-start gap-2">
              <svg class="h-5 w-5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m0 0h5.625c.621 0 1.125.504 1.125 1.125v4.125M8.25 6.75h6" />
              </svg>
              <div class="flex-1">
                <p class="text-sm font-semibold text-wc-text">Duplicar desde ticket previo</p>
                <p class="text-xs text-wc-text-tertiary mt-0.5">Este cliente tiene planes previos. Puedes clonar uno para acelerar el brief.</p>
              </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
              <select
                v-model="selectedPrevTicketId"
                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option value="">Selecciona un ticket previo completado...</option>
                <option v-for="p in previousTickets" :key="p.id" :value="p.id">
                  #{{ p.id }} · {{ humanLabel(p.plan_type) }} · {{ p.submitted_at ? new Date(p.submitted_at).toLocaleDateString('es-MX') : 'sin fecha' }}
                </option>
              </select>
              <button
                type="button"
                @click="duplicateFromPrevious"
                :disabled="!selectedPrevTicketId || duplicatingPrev"
                class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 transition disabled:opacity-50"
              >{{ duplicatingPrev ? 'Duplicando...' : 'Duplicar y editar' }}</button>
            </div>
          </div>
          <div v-else-if="isNew && !ticketId && newForm.client_id && loadingPrev" class="mt-3 text-xs text-wc-text-tertiary">
            Buscando tickets previos del cliente...
          </div>
        </section>

        <!-- STEP: datos_generales -->
        <section v-else-if="currentStepKey === 'datos'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-display text-xl tracking-wide text-wc-text">2. Datos generales</h2>
            <button
              v-if="isEditable && (ticket?.client_id || newForm.client_id)"
              type="button"
              @click="autofillFromProfile"
              :disabled="autofillLoading"
              class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50 shadow-sm"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z" />
              </svg>
              {{ autofillLoading ? 'Cargando...' : 'Pre-llenar desde el perfil del cliente' }}
            </button>
          </div>
          <fieldset :disabled="!isEditable" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre del cliente</label>
                <input v-model="datosGenerales.nombre" type="text" :class="['w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent', isHighlighted('dg.nombre') && 'autofill-highlight']" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</label>
                <select v-model="datosGenerales.plan" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="">Selecciona...</option>
                  <option value="esencial">Esencial</option>
                  <option value="metodo">Metodo</option>
                  <option value="elite">Elite</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Edad</label>
                <input v-model.number="datosGenerales.edad" type="number" min="15" max="99" :class="['w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent', isHighlighted('dg.edad') && 'autofill-highlight']" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Genero</label>
                <div class="flex gap-2">
                  <label v-for="g in ['masculino','femenino','otro']" :key="g" class="flex-1 cursor-pointer rounded-lg border-2 px-3 py-2 text-center text-sm font-medium transition"
                    :class="datosGenerales.genero === g ? 'border-wc-accent bg-wc-accent/5 text-wc-text' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'">
                    <input type="radio" v-model="datosGenerales.genero" :value="g" class="sr-only" />
                    {{ humanLabel(g) }}
                  </label>
                </div>
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso (kg)</label>
                <input v-model.number="datosGenerales.peso" type="number" step="0.1" :class="['w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent', isHighlighted('dg.peso') && 'autofill-highlight']" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estatura (cm)</label>
                <input v-model.number="datosGenerales.estatura" type="number" step="1" :class="['w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent', isHighlighted('dg.estatura') && 'autofill-highlight']" />
              </div>
              <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel de actividad diario</label>
                <select v-model="datosGenerales.actividad_diaria" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="">Selecciona...</option>
                  <option v-for="n in NIVEL_ACTIVIDAD" :key="n.value" :value="n.value">{{ n.label }}</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo principal</label>
                <textarea v-model="datosGenerales.objetivo" rows="3" placeholder="Describe el objetivo del cliente en sus propias palabras..." :class="['w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent', isHighlighted('dg.objetivo') && 'autofill-highlight']"></textarea>
              </div>
            </div>
          </fieldset>
        </section>

        <!-- STEP: entrenamiento -->
        <section v-else-if="currentStepKey === 'entrenamiento'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">3. Plan de entrenamiento</h2>
          <div v-if="isAjuste" class="rounded-lg border border-purple-500/30 bg-purple-500/5 p-3 text-xs text-purple-400">
            Este es un ticket de ajuste. Solo llena esta seccion si hay algo que cambiar.
          </div>
          <fieldset :disabled="!isEditable" class="space-y-5">

            <!-- Lugar -->
            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lugar de entrenamiento</label>
              <div class="flex gap-2">
                <label v-for="l in ['gym','casa']" :key="l" class="flex-1 cursor-pointer rounded-lg border-2 px-3 py-2 text-center text-sm font-medium transition"
                  :class="planEntrenamiento.lugar === l ? 'border-wc-accent bg-wc-accent/5 text-wc-text' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'">
                  <input type="radio" v-model="planEntrenamiento.lugar" :value="l" class="sr-only" />
                  {{ humanLabel(l) }}
                </label>
              </div>
            </div>

            <!-- Implementos (si casa) -->
            <div v-if="planEntrenamiento.lugar === 'casa'">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Implementos disponibles</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="imp in IMPLEMENTOS_OPTIONS"
                  :key="imp"
                  type="button"
                  @click="toggleArrayItem(planEntrenamiento.implementos, imp)"
                  class="rounded-full border px-3 py-1 text-xs font-medium transition"
                  :class="planEntrenamiento.implementos.includes(imp) ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'"
                >{{ humanLabel(imp) }}</button>
              </div>
            </div>

            <!-- Dias + tiempos -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Dias por semana</label>
                <select v-model.number="planEntrenamiento.dias_semana" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option :value="null">Selecciona...</option>
                  <option v-for="n in [3,4,5,6]" :key="n" :value="n">{{ n }} dias</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tiempo pesas (min)</label>
                <input v-model.number="planEntrenamiento.tiempo_pesas_min" type="number" min="0" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tiempo cardio (min)</label>
                <input v-model.number="planEntrenamiento.tiempo_cardio_min" type="number" min="0" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
              </div>
            </div>

            <!-- Preferencia + modalidad -->
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Preferencia de cardio</label>
              <textarea v-model="planEntrenamiento.preferencia_cardio" rows="2" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>

            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Modalidad de cardio</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="m in MODALIDAD_CARDIO_OPTIONS"
                  :key="m"
                  type="button"
                  @click="toggleArrayItem(planEntrenamiento.modalidad_cardio, m)"
                  class="rounded-full border px-3 py-1 text-xs font-medium transition"
                  :class="planEntrenamiento.modalidad_cardio.includes(m) ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'"
                >{{ humanLabel(m) }}</button>
              </div>
            </div>

            <!-- Nivel -->
            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</label>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <label
                  v-for="n in [
                    { value: 'principiante', desc: 'Menos de 6 meses entrenando.' },
                    { value: 'intermedio', desc: '6-24 meses con tecnica solida.' },
                    { value: 'avanzado', desc: 'Mas de 2 años, carga alta.' },
                  ]"
                  :key="n.value"
                  class="cursor-pointer rounded-lg border-2 p-3 transition"
                  :class="planEntrenamiento.nivel === n.value ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-secondary'"
                >
                  <input type="radio" v-model="planEntrenamiento.nivel" :value="n.value" class="sr-only" />
                  <p class="text-sm font-semibold text-wc-text">{{ humanLabel(n.value) }}</p>
                  <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ n.desc }}</p>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lesiones</label>
                <textarea v-model="planEntrenamiento.lesiones" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Restricciones</label>
                <textarea v-model="planEntrenamiento.restricciones" rows="3" placeholder="Ej: No incluir peso muerto, no caminadora." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
              </div>
            </div>

            <!-- Split semanal -->
            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Split semanal</label>
              <div class="space-y-3">
                <div v-for="d in DIAS_SEMANA" :key="d.key" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                  <p class="mb-2 text-sm font-semibold text-wc-text">{{ d.label }}</p>
                  <div class="flex flex-wrap gap-1.5 mb-2">
                    <button
                      v-for="g in GRUPOS_MUSCULARES"
                      :key="g"
                      type="button"
                      @click="toggleArrayItem(planEntrenamiento.split[d.key].grupos, g)"
                      class="rounded-full border px-2.5 py-0.5 text-[11px] font-medium transition"
                      :class="planEntrenamiento.split[d.key].grupos.includes(g) ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary'"
                    >{{ humanLabel(g) }}</button>
                  </div>
                  <input
                    v-model="planEntrenamiento.split[d.key].prioridad"
                    type="text"
                    placeholder="Prioridad (opcional): ej. gluteo alto"
                    class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                  />
                </div>
              </div>
            </div>

          </fieldset>
        </section>

        <!-- STEP: nutricion -->
        <section v-else-if="currentStepKey === 'nutricion'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">4. Plan nutricional</h2>
          <div v-if="isAjuste" class="rounded-lg border border-purple-500/30 bg-purple-500/5 p-3 text-xs text-purple-400">
            Este es un ticket de ajuste. Solo llena esta seccion si hay algo que cambiar.
          </div>
          <fieldset :disabled="!isEditable" class="space-y-4">
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo nutricional</label>
              <textarea v-model="planNutricional.objetivo" rows="2" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cuantas comidas al dia</label>
                <input v-model.number="planNutricional.num_comidas" type="number" min="3" max="7" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodologia</label>
                <select v-model="planNutricional.metodologia" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="">Selecciona...</option>
                  <option v-for="m in METODOLOGIAS" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
                <p v-if="planNutricional.metodologia" class="mt-1 text-xs text-wc-text-tertiary">
                  {{ METODOLOGIAS.find(m => m.value === planNutricional.metodologia)?.desc }}
                </p>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Horarios de comida</label>
              <div class="flex gap-2">
                <input
                  v-model="newHorario"
                  type="text"
                  placeholder="Ej: 7:00 am"
                  @keyup.enter="addHorario"
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                />
                <button
                  type="button"
                  @click="addHorario"
                  class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
                >Agregar</button>
              </div>
              <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="(h, i) in planNutricional.horarios" :key="i" class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-medium text-wc-accent">
                  {{ h }}
                  <button type="button" @click="removeHorario(i)" class="text-wc-accent hover:opacity-75">×</button>
                </span>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Alimentos que NO incluir</label>
              <textarea v-model="planNutricional.alimentos_no_incluir" rows="2" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Alimentos a priorizar</label>
              <textarea v-model="planNutricional.alimentos_priorizar" rows="2" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Descripcion y configuracion de comidas</label>
              <textarea v-model="planNutricional.configuracion_comidas" rows="5" placeholder="Ej: desayuno con huevo y otras con avena; snack AM con frutas..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
          </fieldset>
        </section>

        <!-- STEP: habitos -->
        <section v-else-if="currentStepKey === 'habitos'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">5. Plan de habitos</h2>
          <div v-if="isAjuste" class="rounded-lg border border-purple-500/30 bg-purple-500/5 p-3 text-xs text-purple-400">
            Este es un ticket de ajuste. Solo llena esta seccion si hay algo que cambiar.
          </div>
          <fieldset :disabled="!isEditable" class="space-y-4">
            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Areas de foco</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="a in AREAS_FOCO_HABITOS"
                  :key="a"
                  type="button"
                  @click="toggleArrayItem(planHabitos.areas_foco, a)"
                  class="rounded-full border px-3 py-1 text-xs font-medium transition"
                  :class="planHabitos.areas_foco.includes(a) ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'"
                >{{ humanLabel(a) }}</button>
              </div>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Rutina matutina</label>
              <textarea v-model="planHabitos.rutina_matutina" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Rutina nocturna</label>
              <textarea v-model="planHabitos.rutina_nocturna" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Otros habitos</label>
              <textarea v-model="planHabitos.otros" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
          </fieldset>
        </section>

        <!-- STEP: suplementacion -->
        <section v-else-if="currentStepKey === 'suplementacion'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">Plan de suplementacion</h2>
          <div v-if="isAjuste" class="rounded-lg border border-purple-500/30 bg-purple-500/5 p-3 text-xs text-purple-400">
            Este es un ticket de ajuste. Solo llena esta seccion si hay algo que cambiar.
          </div>
          <fieldset :disabled="!isEditable" class="space-y-5">
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo del stack</label>
              <textarea
                v-model="planSuplementacion.objetivo"
                rows="2"
                placeholder="Para que se optimiza — ej. Rendimiento base, Recomposicion, Recuperacion..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              ></textarea>
            </div>

            <div>
              <div class="mb-2 flex items-center justify-between">
                <label class="block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Suplementos</label>
                <button
                  type="button"
                  @click="addSuplemento"
                  class="inline-flex items-center gap-1 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90"
                >
                  <span class="text-base leading-none">+</span> Anadir suplemento
                </button>
              </div>

              <div
                v-if="!planSuplementacion.suplementos || planSuplementacion.suplementos.length === 0"
                class="rounded-lg border-2 border-dashed border-red-500/30 bg-red-500/5 p-4 text-center text-xs text-red-400"
              >
                Agrega al menos un suplemento.
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="(sup, idx) in planSuplementacion.suplementos"
                  :key="idx"
                  class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 space-y-2"
                >
                  <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-semibold text-wc-text-tertiary">#{{ idx + 1 }}</p>
                    <button
                      type="button"
                      @click="removeSuplemento(idx)"
                      class="rounded-md border border-red-500/30 px-2 py-0.5 text-[11px] font-medium text-red-400 hover:bg-red-500/10"
                    >Eliminar</button>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</label>
                      <input
                        v-model="sup.nombre"
                        type="text"
                        placeholder="Ej: Proteina de Suero, Creatina Monohidrato"
                        class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                      />
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Dosis</label>
                      <input
                        v-model="sup.dosis"
                        type="text"
                        placeholder="Ej: 30g, 5g"
                        class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                      />
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Momento</label>
                      <input
                        v-model="sup.momento"
                        type="text"
                        placeholder="Ej: Post-entrenamiento, Antes de dormir"
                        class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                      />
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Frecuencia</label>
                      <select
                        v-model="sup.frecuencia"
                        class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                      >
                        <option value="">Selecciona...</option>
                        <option v-for="f in FRECUENCIA_SUPLEMENTO_OPTIONS" :key="f.value" :value="f.value">{{ f.label }}</option>
                      </select>
                    </div>
                    <div class="sm:col-span-2">
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Notas (opcional)</label>
                      <input
                        v-model="sup.notas"
                        type="text"
                        placeholder="Ej: tomar con agua, evitar con cafeina"
                        class="w-full rounded-md border border-wc-border bg-wc-bg-tertiary px-2 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Notas del coach para el stack (opcional)</label>
              <textarea
                v-model="planSuplementacion.notas_coach"
                rows="3"
                placeholder="Indicaciones generales, advertencias, periodo de ciclado..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              ></textarea>
            </div>
          </fieldset>
        </section>

        <!-- STEP: ciclo -->
        <section v-else-if="currentStepKey === 'ciclo'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <h2 class="font-display text-xl tracking-wide text-wc-text">6. Ciclo hormonal</h2>
          <fieldset :disabled="!isEditable" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha ultima menstruacion</label>
                <input v-model="planCiclo.fecha_ultima_menstruacion" type="date" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Duracion ciclo (dias)</label>
                <input v-model.number="planCiclo.duracion_ciclo_dias" type="number" min="15" max="60" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
              </div>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Sintomas</label>
              <textarea v-model="planCiclo.sintomas" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Anticonceptivo</label>
              <input v-model="planCiclo.anticonceptivo" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Notas adicionales</label>
              <textarea v-model="planCiclo.notas" rows="3" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
            </div>
          </fieldset>
        </section>

        <!-- STEP: adjuntos -->
        <section v-else-if="currentStepKey === 'adjuntos'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-5">
          <div>
            <h2 class="font-display text-xl tracking-wide text-wc-text">Adjuntos (opcional)</h2>
            <p class="mt-1 text-sm text-wc-text-tertiary">Fotos de progreso, laboratorios, documentos medicos, etc. Max 10MB por archivo.</p>
          </div>

          <!-- Upload dropzone -->
          <div v-if="isEditable">
            <div class="mb-2 flex flex-col sm:flex-row sm:items-center gap-2">
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Categoria del archivo</label>
              <select
                v-model="attachmentCategory"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
                <option v-for="o in ATTACHMENT_CATEGORY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
              </select>
            </div>

            <div
              @dragover.prevent="attachmentDragging = true"
              @dragleave.prevent="attachmentDragging = false"
              @drop.prevent="onAttachmentDrop"
              :class="attachmentDragging ? 'border-wc-accent/60 bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40'"
              class="relative rounded-xl border-2 border-dashed p-8 text-center transition"
            >
              <input
                type="file"
                @change="onAttachmentInput"
                :accept="ALLOWED_MIMES.join(',')"
                :disabled="uploadingAttachment"
                class="absolute inset-0 opacity-0 cursor-pointer"
              />
              <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/60" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              <p class="mt-2 text-sm font-medium text-wc-text">{{ uploadingAttachment ? 'Subiendo...' : 'Arrastra un archivo o haz click para subir' }}</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">JPG, PNG, WEBP, HEIC, PDF o DOCX · max 10MB</p>
            </div>
          </div>

          <!-- Attachments list -->
          <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Archivos ({{ attachments.length }})</p>
            <div v-if="loadingAttachments" class="animate-pulse rounded-lg border border-wc-border bg-wc-bg-secondary h-16"></div>
            <div v-else-if="attachments.length === 0" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-4 text-center text-xs text-wc-text-tertiary">
              Sin archivos adjuntos todavia.
            </div>
            <ul v-else class="space-y-2">
              <li
                v-for="att in attachments"
                :key="att.id"
                class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3"
              >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-bg-tertiary">
                  <svg v-if="mimeIcon(att.mime) === 'image'" class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
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
                    · {{ att.uploaded_by_name || 'Coach' }} · {{ formatRelative(att.created_at) }}
                  </p>
                </div>
                <a
                  :href="att.url"
                  target="_blank"
                  rel="noopener"
                  class="rounded-md border border-wc-border bg-wc-bg-tertiary px-2.5 py-1 text-[11px] font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text transition"
                >Ver</a>
                <button
                  v-if="isEditable"
                  type="button"
                  @click="deleteAttachment(att)"
                  class="rounded-md border border-red-500/30 px-2 py-1 text-[11px] font-medium text-red-400 hover:bg-red-500/10 transition"
                >Eliminar</button>
              </li>
            </ul>
          </div>
        </section>

        <!-- STEP: revision -->
        <section v-else-if="currentStepKey === 'revision'" class="space-y-5">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-4">
            <h2 class="font-display text-xl tracking-wide text-wc-text">Revision final</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Cliente</p>
                <p class="font-medium text-wc-text">{{ datosGenerales.nombre || ticket?.client_name || '-' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Plan</p>
                <p class="font-medium text-wc-text">{{ humanLabel(planType) }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Edad / Genero</p>
                <p class="font-medium text-wc-text">{{ datosGenerales.edad || '-' }} · {{ humanLabel(datosGenerales.genero) || '-' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Peso / Estatura</p>
                <p class="font-medium text-wc-text">{{ datosGenerales.peso || '-' }} kg · {{ datosGenerales.estatura || '-' }} cm</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Lugar entrenamiento</p>
                <p class="font-medium text-wc-text">{{ humanLabel(planEntrenamiento.lugar) || '-' }} · {{ planEntrenamiento.dias_semana || '-' }} dias</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Nivel</p>
                <p class="font-medium text-wc-text">{{ humanLabel(planEntrenamiento.nivel) || '-' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Nutricion</p>
                <p class="font-medium text-wc-text">{{ planNutricional.num_comidas || '-' }} comidas · {{ humanLabel(planNutricional.metodologia) || 'sin metodologia' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3">
                <p class="text-xs text-wc-text-tertiary">Habitos</p>
                <p class="font-medium text-wc-text">{{ planHabitos.areas_foco?.length || 0 }} areas de foco</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3 sm:col-span-2">
                <p class="text-xs text-wc-text-tertiary">Suplementacion</p>
                <p class="font-medium text-wc-text">
                  {{ planSuplementacion.suplementos?.length || 0 }} suplemento(s)
                  <span v-if="planSuplementacion.objetivo" class="text-wc-text-tertiary"> · {{ planSuplementacion.objetivo }}</span>
                </p>
                <ul v-if="planSuplementacion.suplementos?.length" class="mt-2 space-y-1 text-xs text-wc-text-secondary">
                  <li v-for="(s, i) in planSuplementacion.suplementos" :key="i" class="flex flex-wrap gap-x-2">
                    <span class="font-semibold text-wc-text">{{ s.nombre || '(sin nombre)' }}</span>
                    <span v-if="s.dosis">· {{ s.dosis }}</span>
                    <span v-if="s.momento">· {{ s.momento }}</span>
                    <span v-if="s.frecuencia">· {{ humanLabel(s.frecuencia) }}</span>
                  </li>
                </ul>
              </div>
              <div v-if="isElite" class="rounded-lg bg-wc-bg-secondary p-3 sm:col-span-2">
                <p class="text-xs text-wc-text-tertiary">Ciclo hormonal</p>
                <p class="font-medium text-wc-text">
                  {{ planCiclo.fecha_ultima_menstruacion || '-' }} · {{ planCiclo.duracion_ciclo_dias || '-' }} dias
                </p>
              </div>
            </div>
          </div>

          <!-- Comments thread (solo si el ticket existe y no es solo borrador nuevo) -->
          <PlanTicketComments
            v-if="ticketId && ticket && ticket.status !== 'borrador'"
            :endpoint-base="`/api/v/coach/plan-tickets/${ticketId}`"
            role="coach"
          />

          <!-- Missing fields list -->
          <div v-if="missingFields.length > 0" class="rounded-xl border border-red-500/30 bg-red-500/5 p-5">
            <p class="mb-2 font-semibold text-red-400">Campos faltantes:</p>
            <ul class="list-disc pl-5 text-sm text-red-400 space-y-1">
              <li v-for="(m, i) in missingFields" :key="i">{{ m }}</li>
            </ul>
          </div>

          <!-- Coach responsibility message -->
          <div class="rounded-xl border border-wc-accent/40 bg-wc-bg-tertiary p-5">
            <p class="mb-3 font-display text-base tracking-wide text-wc-accent">ANTES DE ENVIAR ESTE TICKET</p>
            <p v-if="isAjuste" class="mb-2 text-sm text-purple-400 font-medium">Describe con claridad que ajuste necesita el cliente.</p>
            <div class="space-y-2 text-sm text-wc-text-secondary">
              <p><span class="text-wc-accent">✓</span> Lo que tu escribes aqui define directamente la calidad del plan que recibira tu cliente.</p>
              <p><span class="text-wc-accent">✓</span> Esta validacion 1-a-1 con el cliente es una responsabilidad clave del coach — <strong class="text-wc-text">conversa de manera humana, cercana y analiza sus respuestas</strong> para darnos contexto real.</p>
              <p><span class="text-wc-accent">✓</span> Cuanto mas detalle y contexto brindes, mas personalizado y efectivo sera el plan.</p>
              <p><span class="text-wc-accent">✓</span> Es tu responsabilidad ante la empresa hacer esto bien. Tu trabajo aqui es el primer filtro que determina el exito del asesorado.</p>
              <p class="mt-3 border-t border-wc-border pt-3 text-wc-text">
                <strong>Lo que esperamos de ti:</strong> que seas excelente haciendolo. Los asesorados confian en tu profesionalismo — que ese sea el estandar que reflejen estos tickets.
              </p>
            </div>
          </div>

          <!-- Submit actions -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex gap-2 order-2 sm:order-1">
              <button
                v-if="ticket && ticket.status === 'borrador'"
                @click="deleteDraft"
                :disabled="deleting"
                class="rounded-lg border border-red-500/30 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-500/5 transition disabled:opacity-50"
              >{{ deleting ? 'Eliminando...' : 'Eliminar borrador' }}</button>
              <button
                @click="router.push('/coach/plan-tickets')"
                class="rounded-lg border border-wc-border px-4 py-2 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg-tertiary transition"
              >Guardar como borrador</button>
            </div>
            <button
              @click="submitTicket"
              :disabled="submitting || !isEditable || !ticketId"
              class="order-1 sm:order-2 inline-flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-6 py-3 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50"
            >
              <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
              {{ submitting ? 'Enviando...' : 'Enviar ticket' }}
            </button>
          </div>
        </section>

        <!-- Navigation buttons -->
        <div class="flex items-center justify-between pt-2">
          <button
            @click="prev"
            :disabled="step === 0"
            class="inline-flex items-center gap-1 rounded-lg border border-wc-border px-4 py-2 text-sm font-semibold text-wc-text-secondary hover:bg-wc-bg-tertiary transition disabled:opacity-50"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Anterior
          </button>
          <button
            v-if="currentStepKey !== 'revision'"
            @click="next"
            :disabled="(isNew && step === 0 && loading) || (!ticketId && step > 0)"
            class="inline-flex items-center gap-1 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition disabled:opacity-50"
          >
            {{ isNew && step === 0 && !ticketId ? 'Crear y continuar' : 'Siguiente' }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>
      </template>
    </div>
  </CoachLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Autofill-modified field highlight */
.autofill-highlight {
  border-color: rgb(234 179 8 / 0.8) !important;
  box-shadow: 0 0 0 2px rgb(234 179 8 / 0.15);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.autofill-highlight-wrapper {
  position: relative;
}
.autofill-highlight-wrapper::after {
  content: 'Modificado por autofill';
  position: absolute;
  top: -8px;
  right: 8px;
  background: rgb(234 179 8);
  color: #1f1f1f;
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 2px 6px;
  border-radius: 4px;
  pointer-events: none;
}
</style>
