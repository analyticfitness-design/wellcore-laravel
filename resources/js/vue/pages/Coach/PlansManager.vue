<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const activeTab = ref('my_templates');

// Templates
const templates = ref([]);
const templateSearch = ref('');
const templateTypeFilter = ref('');
const templateStats = ref({ total: 0, entrenamiento: 0, nutricion: 0, habitos: 0, ai_generated: 0 });

// Assigned plans
const assignedPlans = ref([]);

// AI generation
const generating = ref(false);
const aiMethodology = ref('hypertrophy');
const aiGoal = ref('');
const generatedPlan = ref(null);

const filteredTemplates = computed(() => {
    let list = templates.value;
    if (templateSearch.value) {
        const q = templateSearch.value.toLowerCase();
        list = list.filter(t => t.name.toLowerCase().includes(q));
    }
    if (templateTypeFilter.value) {
        list = list.filter(t => t.type === templateTypeFilter.value);
    }
    return list;
});

async function loadData() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/plans');
        templates.value = data.templates || [];
        assignedPlans.value = data.assigned || [];
        templateStats.value = data.stats || templateStats.value;
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

async function generatePlan() {
    generating.value = true;
    generatedPlan.value = null;
    try {
        const { data } = await api.post('/api/v/coach/plans/generate', {
            methodology: aiMethodology.value,
            goal: aiGoal.value,
        });
        generatedPlan.value = data.plan || null;
    } catch (e) {
        // silent
    } finally {
        generating.value = false;
    }
}

onMounted(loadData);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Gestion de Planes</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Crea, gestiona y asigna planes a tus clientes</p>
        </div>
      </div>

      <!-- Tab bar -->
      <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
        <button
          v-for="tab in [{ key: 'my_templates', label: 'Mis Templates' }, { key: 'assigned', label: 'Asignados' }, { key: 'generate', label: 'Generar con IA' }]"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="flex-1 rounded-md px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium whitespace-nowrap transition-colors"
          :class="activeTab === tab.key ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary'"
        >
          <svg v-if="tab.key === 'generate'" class="inline-block h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
          </svg>
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <template v-else>

        <!-- MY TEMPLATES TAB -->
        <template v-if="activeTab === 'my_templates'">
          <!-- Stats -->
          <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
              <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ templateStats.total }}</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Entrenamiento</p>
              <p class="mt-1 font-data text-2xl font-bold text-sky-500">{{ templateStats.entrenamiento }}</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion</p>
              <p class="mt-1 font-data text-2xl font-bold text-emerald-500">{{ templateStats.nutricion }}</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Habitos</p>
              <p class="mt-1 font-data text-2xl font-bold text-amber-500">{{ templateStats.habitos }}</p>
            </div>
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">IA Generados</p>
              <p class="mt-1 font-data text-2xl font-bold text-purple-500">{{ templateStats.ai_generated }}</p>
            </div>
          </div>

          <!-- Search & filter -->
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 max-w-sm">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input v-model="templateSearch" type="text" placeholder="Buscar templates..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
            </div>
            <select v-model="templateTypeFilter" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="">Todos los tipos</option>
              <option value="entrenamiento">Entrenamiento</option>
              <option value="nutricion">Nutricion</option>
              <option value="habitos">Habitos</option>
              <option value="suplementacion">Suplementacion</option>
            </select>
          </div>

          <!-- Templates list -->
          <div v-if="filteredTemplates.length > 0" class="space-y-3">
            <div
              v-for="tpl in filteredTemplates"
              :key="tpl.id"
              class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="tpl.type === 'entrenamiento' ? 'bg-sky-500/10' : tpl.type === 'nutricion' ? 'bg-emerald-500/10' : 'bg-amber-500/10'">
                <svg class="h-5 w-5" :class="tpl.type === 'entrenamiento' ? 'text-sky-500' : tpl.type === 'nutricion' ? 'text-emerald-500' : 'text-amber-500'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text">{{ tpl.name }}</p>
                <p class="text-xs text-wc-text-tertiary capitalize">{{ tpl.type }} -- {{ tpl.duration || 'N/A' }}</p>
              </div>
              <span v-if="tpl.ai_generated" class="inline-flex items-center gap-1 rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-500">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
                IA
              </span>
            </div>
          </div>
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">No se encontraron templates</p>
          </div>
        </template>

        <!-- ASSIGNED TAB -->
        <template v-if="activeTab === 'assigned'">
          <div v-if="assignedPlans.length > 0" class="space-y-3">
            <div v-for="plan in assignedPlans" :key="plan.id" class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                <span class="text-sm font-semibold text-wc-accent">{{ (plan.client_name || 'C').charAt(0) }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text">{{ plan.client_name }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ plan.plan_name }} -- {{ plan.type }}</p>
              </div>
              <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="plan.status === 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-wc-bg-secondary text-wc-text-tertiary'">
                {{ plan.status === 'active' ? 'Activo' : 'Finalizado' }}
              </span>
            </div>
          </div>
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">No hay planes asignados</p>
          </div>
        </template>

        <!-- GENERATE TAB -->
        <template v-if="activeTab === 'generate'">
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
              <h3 class="font-display text-lg tracking-wide text-wc-text">Generar plan con IA</h3>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Metodologia</label>
                <select v-model="aiMethodology" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                  <option value="hypertrophy">Hipertrofia</option>
                  <option value="strength">Fuerza</option>
                  <option value="endurance">Resistencia</option>
                  <option value="fat_loss">Perdida de grasa</option>
                  <option value="functional">Funcional</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo del cliente</label>
                <textarea v-model="aiGoal" rows="3" placeholder="Describe el objetivo, nivel, limitaciones..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"></textarea>
              </div>
              <button
                @click="generatePlan"
                :disabled="generating"
                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50"
              >
                <svg v-if="generating" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
                {{ generating ? 'Generando...' : 'Generar plan' }}
              </button>
            </div>

            <!-- Generated preview -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <h3 class="text-sm font-semibold text-wc-text mb-3">Preview</h3>
              <div v-if="generatedPlan" class="space-y-3 text-sm text-wc-text-secondary">
                <p class="font-medium text-wc-text">{{ generatedPlan.name }}</p>
                <p>{{ generatedPlan.description }}</p>
                <div v-if="generatedPlan.weeks" class="space-y-2">
                  <div v-for="(week, i) in generatedPlan.weeks" :key="i" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                    <p class="text-xs font-semibold text-wc-text">Semana {{ i + 1 }}</p>
                    <p class="text-xs text-wc-text-tertiary">{{ week.summary }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                </svg>
                <p class="mt-2 text-xs text-wc-text-tertiary">Configura los parametros y genera un plan</p>
              </div>
            </div>
          </div>
        </template>

      </template>
    </div>
  </CoachLayout>
</template>
