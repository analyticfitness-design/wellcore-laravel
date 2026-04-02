<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ─── List state ─────────────────────────────────────────────────────────────
const loading   = ref(true);
const error     = ref(null);
const plans     = ref([]);
const stats     = ref({ total: 0, entrenamiento: 0, nutricion: 0, habitos: 0, suplementacion: 0, ciclo: 0, ai_generated: 0 });
const coaches   = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

// ─── Filters ────────────────────────────────────────────────────────────────
const search       = ref('');
const typeFilter   = ref('all');
const coachFilter  = ref('all');
const publicFilter = ref('all');
const aiFilter     = ref('all');
const sortBy       = ref('created_at');
const sortDir      = ref('desc');
const page         = ref(1);

// ─── Form modal ─────────────────────────────────────────────────────────────
const showFormModal   = ref(false);
const editingId       = ref(null);
const formName        = ref('');
const formPlanType    = ref('entrenamiento');
const formMethodology = ref('');
const formDescription = ref('');
const formContentJson = ref('');
const formIsPublic    = ref(false);
const formCoachId     = ref('');
const formSaving      = ref(false);
const formErrors      = ref({});

// ─── View modal ─────────────────────────────────────────────────────────────
const showViewModal  = ref(false);
const viewingPlan    = ref(null);
const viewingLoading = ref(false);
const showJson       = ref(false);

// ─── Delete modal ───────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletingId      = ref(null);
const deleteLoading   = ref(false);

// ─── Type display helpers ────────────────────────────────────────────────────
const TYPE_COLORS = {
    entrenamiento:  'bg-sky-500/10 text-sky-400',
    nutricion:      'bg-emerald-500/10 text-emerald-400',
    habitos:        'bg-violet-500/10 text-violet-400',
    suplementacion: 'bg-amber-500/10 text-amber-400',
    ciclo:          'bg-pink-500/10 text-pink-400',
};
const TYPE_LABELS = {
    entrenamiento:  'Entrenamiento',
    nutricion:      'Nutricion',
    habitos:        'Habitos',
    suplementacion: 'Suplementacion',
    ciclo:          'Ciclo',
};

function typeColor(t) { return TYPE_COLORS[t] ?? 'bg-wc-bg-secondary text-wc-text-secondary'; }
function typeLabel(t) { return TYPE_LABELS[t] ?? (t ? t.charAt(0).toUpperCase() + t.slice(1) : ''); }

// ─── Sort toggle ────────────────────────────────────────────────────────────
function sortByColumn(col) {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value  = col;
        sortDir.value = 'desc';
    }
    page.value = 1;
    fetchPlans();
}

// ─── Fetch list ─────────────────────────────────────────────────────────────
async function fetchPlans() {
    loading.value = true;
    error.value   = null;
    try {
        const params = new URLSearchParams({
            search:  search.value,
            type:    typeFilter.value,
            coach:   coachFilter.value,
            public:  publicFilter.value,
            ai:      aiFilter.value,
            sort_by: sortBy.value,
            sort_dir: sortDir.value,
            page:    page.value,
        });
        const res = await api.get(`/api/v/admin/plans?${params}`);
        plans.value     = res.data.plans     ?? [];
        stats.value     = res.data.stats     ?? stats.value;
        coaches.value   = res.data.coaches   ?? [];
        pagination.value = res.data.pagination ?? pagination.value;
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Error al cargar planes';
    } finally {
        loading.value = false;
    }
}

// ─── Debounced search ────────────────────────────────────────────────────────
let debounceTimer = null;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { page.value = 1; fetchPlans(); }, 300);
});
watch([typeFilter, coachFilter, publicFilter, aiFilter], () => {
    page.value = 1;
    fetchPlans();
});

// ─── Create / Edit modal ────────────────────────────────────────────────────
function openCreate() {
    editingId.value       = null;
    formName.value        = '';
    formPlanType.value    = 'entrenamiento';
    formMethodology.value = '';
    formDescription.value = '';
    formContentJson.value = '';
    formIsPublic.value    = false;
    formCoachId.value     = '';
    formErrors.value      = {};
    showFormModal.value   = true;
}

async function openEdit(plan) {
    editingId.value       = plan.id;
    formName.value        = plan.name;
    formPlanType.value    = plan.plan_type;
    formMethodology.value = plan.methodology ?? '';
    formDescription.value = plan.description ?? '';
    formIsPublic.value    = !!plan.is_public;
    formCoachId.value     = plan.coach_id ? String(plan.coach_id) : '';
    formErrors.value      = {};

    // Fetch full content_json for the edit form
    try {
        const res = await api.get(`/api/v/admin/plans/${plan.id}`);
        const content = res.data.plan?.content_json;
        formContentJson.value = content
            ? JSON.stringify(content, null, 2)
            : '';
    } catch {
        formContentJson.value = '';
    }

    showFormModal.value = true;
}

function closeForm() {
    showFormModal.value = false;
    editingId.value     = null;
    formErrors.value    = {};
}

async function savePlan() {
    formErrors.value = {};

    // Client-side JSON validation
    if (!formContentJson.value.trim()) {
        formErrors.value = { content_json: ['El contenido JSON es obligatorio.'] };
        return;
    }
    let parsedJson;
    try {
        parsedJson = JSON.parse(formContentJson.value);
    } catch (e) {
        formErrors.value = { content_json: ['JSON invalido: ' + e.message] };
        return;
    }

    formSaving.value = true;
    try {
        const payload = {
            name:         formName.value,
            plan_type:    formPlanType.value,
            methodology:  formMethodology.value || null,
            description:  formDescription.value || null,
            content_json: parsedJson,
            is_public:    formIsPublic.value,
            coach_id:     formCoachId.value ? Number(formCoachId.value) : null,
        };

        if (editingId.value) {
            await api.put(`/api/v/admin/plans/${editingId.value}`, payload);
        } else {
            await api.post('/api/v/admin/plans', payload);
        }

        closeForm();
        await fetchPlans();
    } catch (e) {
        if (e.response?.status === 422) {
            formErrors.value = e.response.data.errors ?? {};
        } else {
            formErrors.value = { _global: [e.response?.data?.message ?? 'Error al guardar'] };
        }
    } finally {
        formSaving.value = false;
    }
}

// ─── View modal ─────────────────────────────────────────────────────────────
async function openView(plan) {
    showViewModal.value  = true;
    viewingPlan.value    = null;
    viewingLoading.value = true;
    showJson.value       = false;
    try {
        const res = await api.get(`/api/v/admin/plans/${plan.id}`);
        viewingPlan.value = res.data.plan ?? null;
    } catch {
        viewingPlan.value = null;
    } finally {
        viewingLoading.value = false;
    }
}

function closeView() {
    showViewModal.value = false;
    viewingPlan.value   = null;
}

function viewJsonText(plan) {
    if (!plan?.content_json) return '';
    return JSON.stringify(plan.content_json, null, 2);
}

// ─── Delete modal ───────────────────────────────────────────────────────────
function confirmDelete(id) {
    deletingId.value    = id;
    showDeleteModal.value = true;
}

function closeDelete() {
    showDeleteModal.value = false;
    deletingId.value      = null;
}

async function deletePlan() {
    if (!deletingId.value) return;
    deleteLoading.value = true;
    try {
        await api.delete(`/api/v/admin/plans/${deletingId.value}`);
        closeDelete();
        await fetchPlans();
    } catch {
        // keep modal open on error
    } finally {
        deleteLoading.value = false;
    }
}

// ─── Pagination ──────────────────────────────────────────────────────────────
function goToPage(p) {
    if (p < 1 || p > pagination.value.last_page) return;
    page.value = p;
    fetchPlans();
}

// ─── Plan content renderers ──────────────────────────────────────────────────
function getWeeks(plan) {
    return plan?.content_json?.weeks ?? [];
}
function getMeals(plan) {
    return plan?.content_json?.meal_plan ?? [];
}
function getHabits(plan) {
    return plan?.content_json?.habits ?? [];
}
function getSupplements(plan) {
    return plan?.content_json?.supplements ?? plan?.content_json?.items ?? [];
}

// ─── Init ───────────────────────────────────────────────────────────────────
onMounted(fetchPlans);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">GESTION DE PLANES</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Templates de planes de entrenamiento, nutricion, habitos y mas.</p>
        </div>
        <button @click="openCreate"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
          + Nuevo Template
        </button>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">{{ stats.total }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-sky-500/30 bg-sky-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-sky-400">{{ stats.entrenamiento }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Entrena.</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-emerald-400">{{ stats.nutricion }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion</p>
        </div>
        <div class="rounded-xl border border-violet-500/30 bg-violet-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-violet-400">{{ stats.habitos }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Habitos</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-amber-400">{{ stats.suplementacion }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Suplem.</p>
        </div>
        <div class="rounded-xl border border-pink-500/30 bg-pink-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-pink-400">{{ stats.ciclo }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ciclo</p>
        </div>
        <div class="rounded-xl border border-purple-500/30 bg-purple-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-purple-400">{{ stats.ai_generated }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">AI Gen.</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3">
        <!-- Search -->
        <div class="relative min-w-48 flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input v-model="search" type="text" placeholder="Buscar por nombre, metodologia..."
                 class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        <!-- Type filter -->
        <select v-model="typeFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="all">Todos los tipos</option>
          <option value="entrenamiento">Entrenamiento</option>
          <option value="nutricion">Nutricion</option>
          <option value="habitos">Habitos</option>
          <option value="suplementacion">Suplementacion</option>
          <option value="ciclo">Ciclo</option>
        </select>

        <!-- Coach filter -->
        <select v-model="coachFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="all">Todos los coaches</option>
          <option v-for="coach in coaches" :key="coach.id" :value="String(coach.id)">{{ coach.name }}</option>
        </select>

        <!-- Public filter -->
        <select v-model="publicFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="all">Visibilidad</option>
          <option value="yes">Publicos</option>
          <option value="no">Privados</option>
        </select>

        <!-- AI filter -->
        <select v-model="aiFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
          <option value="all">Origen</option>
          <option value="yes">AI Generado</option>
          <option value="no">Manual</option>
        </select>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div v-for="n in 5" :key="n" class="flex animate-pulse gap-4 border-b border-wc-border px-4 py-4 last:border-0">
            <div class="h-4 w-48 rounded bg-wc-bg-secondary"></div>
            <div class="h-4 w-20 rounded bg-wc-bg-secondary"></div>
            <div class="ml-auto h-4 w-16 rounded bg-wc-bg-secondary"></div>
          </div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchPlans" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Table -->
      <div v-else class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-wc-border">
                <th class="px-4 py-3 text-left">
                  <button @click="sortByColumn('name')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                    Nombre
                    <svg v-if="sortBy === 'name'" class="h-3 w-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                    </svg>
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button @click="sortByColumn('plan_type')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                    Tipo
                    <svg v-if="sortBy === 'plan_type'" class="h-3 w-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                    </svg>
                  </button>
                </th>
                <th class="hidden px-4 py-3 text-left sm:table-cell">
                  <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodologia</span>
                </th>
                <th class="hidden px-4 py-3 text-left md:table-cell">
                  <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</span>
                </th>
                <th class="hidden px-4 py-3 text-center sm:table-cell">
                  <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Badges</span>
                </th>
                <th class="hidden px-4 py-3 text-left lg:table-cell">
                  <button @click="sortByColumn('created_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                    Fecha
                    <svg v-if="sortBy === 'created_at'" class="h-3 w-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                    </svg>
                  </button>
                </th>
                <th class="px-4 py-3 text-right">
                  <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <template v-if="plans.length">
                <tr v-for="plan in plans" :key="plan.id" class="transition-colors hover:bg-wc-bg-secondary/50">
                  <!-- Name + description -->
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-wc-text">{{ plan.name }}</div>
                    <div v-if="plan.description" class="mt-0.5 line-clamp-1 text-xs text-wc-text-tertiary">
                      {{ plan.description.slice(0, 60) }}{{ plan.description.length > 60 ? '…' : '' }}
                    </div>
                  </td>

                  <!-- Type badge -->
                  <td class="px-4 py-3">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeColor(plan.plan_type)">
                      {{ typeLabel(plan.plan_type) }}
                    </span>
                  </td>

                  <!-- Methodology -->
                  <td class="hidden px-4 py-3 sm:table-cell">
                    <span class="text-xs text-wc-text-secondary">{{ plan.methodology || '—' }}</span>
                  </td>

                  <!-- Coach -->
                  <td class="hidden px-4 py-3 md:table-cell">
                    <span class="text-xs text-wc-text-secondary">{{ plan.coach_name || '—' }}</span>
                  </td>

                  <!-- Badges: AI + Public -->
                  <td class="hidden px-4 py-3 text-center sm:table-cell">
                    <div class="flex items-center justify-center gap-1.5">
                      <span v-if="plan.ai_generated" class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-400">AI</span>
                      <span v-if="plan.is_public" class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Publico</span>
                      <span v-else class="inline-flex rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-semibold text-wc-text-tertiary">Privado</span>
                    </div>
                  </td>

                  <!-- Date -->
                  <td class="hidden px-4 py-3 lg:table-cell">
                    <span class="text-xs text-wc-text-tertiary">{{ plan.created_at }}</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <!-- View -->
                      <button @click="openView(plan)" title="Ver contenido"
                              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-sky-500 hover:text-sky-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                      </button>
                      <!-- Edit -->
                      <button @click="openEdit(plan)" title="Editar"
                              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-accent">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                      </button>
                      <!-- Delete -->
                      <button @click="confirmDelete(plan.id)" title="Eliminar"
                              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-red-500 hover:text-red-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="7" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
                  No se encontraron templates con los filtros seleccionados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2">
        <button @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-sm text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-40">
          &larr;
        </button>
        <span class="text-sm text-wc-text-secondary">
          Pag {{ pagination.current_page }} / {{ pagination.last_page }}
          <span class="text-wc-text-tertiary">({{ pagination.total }} total)</span>
        </span>
        <button @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-sm text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-40">
          &rarr;
        </button>
      </div>

    </div>

    <!-- ==================== CREATE / EDIT MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeForm"></div>
        <Transition name="slide-up">
          <div v-if="showFormModal" class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">
                {{ editingId ? 'EDITAR TEMPLATE' : 'NUEVO TEMPLATE' }}
              </h2>
              <button @click="closeForm" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
              </button>
            </div>

            <!-- Global error -->
            <div v-if="formErrors._global" class="mb-4 rounded-lg border border-red-500/20 bg-red-500/5 p-3">
              <p class="text-xs text-red-400">{{ formErrors._global[0] }}</p>
            </div>

            <form @submit.prevent="savePlan" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Nombre <span class="text-wc-accent">*</span></label>
                <input v-model="formName" type="text" placeholder="Nombre del template"
                       class="w-full rounded-lg border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:outline-none"
                       :class="formErrors.name ? 'border-red-500' : 'border-wc-border focus:border-wc-accent'" />
                <p v-if="formErrors.name" class="mt-1 text-xs text-wc-accent">{{ formErrors.name[0] }}</p>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Type -->
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Tipo <span class="text-wc-accent">*</span></label>
                  <select v-model="formPlanType"
                          class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                    <option value="entrenamiento">Entrenamiento</option>
                    <option value="nutricion">Nutricion</option>
                    <option value="habitos">Habitos</option>
                    <option value="suplementacion">Suplementacion</option>
                    <option value="ciclo">Ciclo</option>
                  </select>
                  <p v-if="formErrors.plan_type" class="mt-1 text-xs text-wc-accent">{{ formErrors.plan_type[0] }}</p>
                </div>

                <!-- Coach -->
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Coach</label>
                  <select v-model="formCoachId"
                          class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                    <option value="">Sin asignar</option>
                    <option v-for="coach in coaches" :key="coach.id" :value="String(coach.id)">{{ coach.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Methodology -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Metodologia</label>
                <input v-model="formMethodology" type="text" placeholder="Push/Pull/Legs, Full Body, etc."
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
              </div>

              <!-- Description -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Descripcion</label>
                <textarea v-model="formDescription" rows="2" placeholder="Breve descripcion del plan..."
                          class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
              </div>

              <!-- Content JSON -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Contenido JSON <span class="text-wc-accent">*</span></label>
                <textarea v-model="formContentJson" rows="10" placeholder='{"weeks": [{"day": 1, "exercises": [...]}]}'
                          class="w-full resize-y rounded-lg border bg-wc-bg-tertiary px-3 py-2.5 font-mono text-xs text-wc-text placeholder-wc-text-tertiary focus:outline-none"
                          :class="formErrors.content_json ? 'border-red-500' : 'border-wc-border focus:border-wc-accent'"></textarea>
                <p v-if="formErrors.content_json" class="mt-1 text-xs text-wc-accent">{{ formErrors.content_json[0] }}</p>
              </div>

              <!-- Public toggle -->
              <div class="flex items-center gap-3">
                <button type="button" @click="formIsPublic = !formIsPublic"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                        :class="formIsPublic ? 'bg-emerald-500' : 'bg-wc-bg-tertiary'"
                        role="switch" :aria-checked="formIsPublic">
                  <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200"
                        :class="formIsPublic ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
                <span class="text-sm text-wc-text-secondary">Template publico</span>
              </div>

              <!-- Actions -->
              <div class="flex gap-3 pt-1">
                <button type="button" @click="closeForm"
                        class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text">
                  Cancelar
                </button>
                <button type="submit" :disabled="formSaving"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70">
                  <svg v-if="formSaving" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  <span>{{ formSaving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Crear Template') }}</span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== VIEW CONTENT MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeView"></div>
        <Transition name="slide-up">
          <div v-if="showViewModal" class="relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">

            <!-- Loading state inside view modal -->
            <template v-if="viewingLoading">
              <div class="flex h-40 items-center justify-center">
                <svg class="h-8 w-8 animate-spin text-wc-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
              </div>
            </template>

            <template v-else-if="viewingPlan">
              <!-- Header -->
              <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                  <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ viewingPlan.name.toUpperCase() }}</h2>
                  <div class="mt-1.5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="typeColor(viewingPlan.plan_type)">
                      {{ typeLabel(viewingPlan.plan_type) }}
                    </span>
                    <span v-if="viewingPlan.ai_generated" class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-400">AI Generado</span>
                    <span v-if="viewingPlan.is_public" class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Publico</span>
                    <span v-if="viewingPlan.coach_name" class="text-xs text-wc-text-tertiary">por {{ viewingPlan.coach_name }}</span>
                  </div>
                </div>
                <button @click="closeView" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
              </div>

              <!-- Metadata -->
              <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodologia</p>
                  <p class="mt-1 text-sm text-wc-text">{{ viewingPlan.methodology || '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</p>
                  <p class="mt-1 text-sm text-wc-text">{{ viewingPlan.coach_name || '—' }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Creado</p>
                  <p class="mt-1 text-sm text-wc-text">{{ viewingPlan.created_at }}</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Actualizado</p>
                  <p class="mt-1 text-sm text-wc-text">{{ viewingPlan.updated_at }}</p>
                </div>
              </div>

              <!-- Description -->
              <div v-if="viewingPlan.description" class="mb-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <h4 class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Descripcion</h4>
                <p class="text-sm leading-relaxed text-wc-text">{{ viewingPlan.description }}</p>
              </div>

              <!-- Plan Content -->
              <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="mb-3 flex items-center justify-between">
                  <h4 class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Contenido del Plan</h4>
                  <button @click="showJson = !showJson" class="text-xs text-wc-text-tertiary transition-colors hover:text-wc-text">
                    {{ showJson ? 'Vista interactiva' : 'Ver JSON' }}
                  </button>
                </div>

                <!-- Interactive view -->
                <div v-show="!showJson">
                  <!-- Entrenamiento: weeks/sessions/exercises -->
                  <template v-if="viewingPlan.plan_type === 'entrenamiento' && getWeeks(viewingPlan).length">
                    <div v-for="(week, wi) in getWeeks(viewingPlan)" :key="wi" class="mb-4">
                      <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Semana {{ week.week ?? wi + 1 }}
                      </p>
                      <div v-for="(session, si) in (week.sessions ?? [])" :key="si" class="mb-3 rounded-lg border border-wc-border bg-wc-bg p-3">
                        <p class="mb-2 text-xs font-medium text-wc-text">{{ session.name ?? ('Sesion ' + (si + 1)) }}</p>
                        <div class="space-y-1">
                          <div v-for="(ex, ei) in (session.exercises ?? [])" :key="ei"
                               class="flex items-center justify-between rounded bg-wc-bg-secondary px-2 py-1.5 text-xs">
                            <span class="font-medium text-wc-text">{{ ex.name ?? ex.exercise ?? '—' }}</span>
                            <span class="font-data text-wc-text-tertiary">
                              {{ ex.sets ? ex.sets + ' x ' : '' }}{{ ex.reps ?? ex.duration ?? '' }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Nutricion: meal_plan -->
                  <template v-else-if="viewingPlan.plan_type === 'nutricion' && getMeals(viewingPlan).length">
                    <div v-for="(meal, mi) in getMeals(viewingPlan)" :key="mi" class="mb-3 rounded-lg border border-wc-border bg-wc-bg p-3">
                      <p class="mb-2 text-xs font-medium text-wc-text">{{ meal.name ?? ('Comida ' + (mi + 1)) }}</p>
                      <div class="space-y-1">
                        <div v-for="(food, fi) in (meal.foods ?? [])" :key="fi"
                             class="flex items-center justify-between rounded bg-wc-bg-secondary px-2 py-1.5 text-xs">
                          <span class="font-medium text-wc-text">{{ food.name ?? food.food ?? '—' }}</span>
                          <span class="font-data text-wc-text-tertiary">{{ food.amount ?? food.quantity ?? '' }}</span>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- Habitos -->
                  <template v-else-if="viewingPlan.plan_type === 'habitos' && getHabits(viewingPlan).length">
                    <div class="space-y-1.5">
                      <div v-for="(habit, hi) in getHabits(viewingPlan)" :key="hi"
                           class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg px-3 py-2">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-violet-400"></span>
                        <span class="flex-1 text-xs text-wc-text">{{ habit.name ?? habit.habit ?? habit }}</span>
                        <span v-if="habit.frequency" class="text-[10px] text-wc-text-tertiary">{{ habit.frequency }}</span>
                      </div>
                    </div>
                  </template>

                  <!-- Suplementacion / Ciclo -->
                  <template v-else-if="getSupplements(viewingPlan).length">
                    <div class="space-y-1.5">
                      <div v-for="(item, ii) in getSupplements(viewingPlan)" :key="ii"
                           class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg px-3 py-2">
                        <span class="text-xs font-medium text-wc-text">{{ item.name ?? item.supplement ?? item }}</span>
                        <span v-if="item.dose ?? item.amount" class="font-data text-xs text-wc-text-tertiary">{{ item.dose ?? item.amount }}</span>
                      </div>
                    </div>
                  </template>

                  <!-- Fallback: no recognized structure -->
                  <p v-else class="text-sm text-wc-text-tertiary">Sin contenido estructurado reconocible.</p>
                </div>

                <!-- JSON view -->
                <div v-show="showJson">
                  <pre class="max-h-96 overflow-auto rounded-lg bg-wc-bg p-4 font-mono text-xs leading-relaxed text-wc-text">{{ viewJsonText(viewingPlan) }}</pre>
                </div>
              </div>
            </template>

            <!-- Error state -->
            <template v-else>
              <div class="flex h-40 items-center justify-center">
                <p class="text-sm text-wc-text-tertiary">No se pudo cargar el plan.</p>
              </div>
              <div class="mt-4 flex justify-center">
                <button @click="closeView" class="rounded-lg border border-wc-border px-4 py-2 text-sm text-wc-text-secondary hover:text-wc-text">Cerrar</button>
              </div>
            </template>

          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== DELETE CONFIRMATION MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeDelete"></div>
        <div class="relative z-10 w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 text-center shadow-2xl">
          <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </div>
          <h3 class="mb-2 font-display text-xl tracking-wide text-wc-text">ELIMINAR TEMPLATE</h3>
          <p class="mb-5 text-sm text-wc-text-secondary">Esta accion no se puede deshacer. El template sera eliminado permanentemente.</p>
          <div class="flex gap-3">
            <button @click="closeDelete"
                    class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text">
              Cancelar
            </button>
            <button @click="deletePlan" :disabled="deleteLoading"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:opacity-70">
              <svg v-if="deleteLoading" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <span>{{ deleteLoading ? 'Eliminando...' : 'Eliminar' }}</span>
            </button>
          </div>
        </div>
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
