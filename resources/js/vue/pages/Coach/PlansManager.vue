<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import { RouterLink } from 'vue-router';

const router = useRouter();

const api = useApi();
const loading = ref(true);
const activeTab = ref('my_templates');

// Templates
const templates = ref([]);
const templateSearch = ref('');
const templateTypeFilter = ref('');
const templateStats = ref({ total: 0, entrenamiento: 0, nutricion: 0, habitos: 0 });

// Assigned plans
const assignedPlans = ref([]);

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

onMounted(loadData);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <WcPageHeader contextLabel="ÁREA DE TRABAJO" title="PLANES" subtitle="Tus templates y planes asignados" />

      <!-- Plan Tickets CTA — alert bar -->
      <div class="flex items-center justify-between px-5 py-3 rounded-card border-l-4 bg-wc-accent/10 border-wc-accent mb-5">
        <div class="flex items-center gap-2 text-sm text-wc-text">
          <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
          </svg>
          Los planes nuevos se crean desde Tickets de Plan
        </div>
        <button @click="router.push('/coach/plan-tickets/nuevo')" class="text-sm font-medium text-wc-accent hover:underline shrink-0">
          Crear ticket →
        </button>
      </div>

      <!-- Tab bar -->
      <div class="flex items-center gap-1 border-b border-wc-border">
        <button
          v-for="tab in [{ key: 'my_templates', label: 'Mis Templates' }, { key: 'assigned', label: 'Asignados' }]"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors border-b-2 -mb-px"
          :class="activeTab === tab.key ? 'border-wc-accent text-wc-text' : 'border-transparent text-wc-text-tertiary hover:text-wc-text'"
        >
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

        <!-- MY TEMPLATES TAB (read-only archive) -->
        <template v-if="activeTab === 'my_templates'">
          <!-- Stats (4 cards, no IA counter) -->
          <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">Total</p>
              <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ templateStats.total }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">Entrenamiento</p>
              <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ templateStats.entrenamiento }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">Nutricion</p>
              <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ templateStats.nutricion }}</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">Habitos</p>
              <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ templateStats.habitos }}</p>
            </div>
          </div>

          <!-- Search & filter -->
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 max-w-sm">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input v-model="templateSearch" type="text" placeholder="Buscar templates..." class="w-full rounded-button border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
            </div>
            <select v-model="templateTypeFilter" class="rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              <option value="">Todos los tipos</option>
              <option value="entrenamiento">Entrenamiento</option>
              <option value="nutricion">Nutricion</option>
              <option value="habitos">Habitos</option>
              <option value="suplementacion">Suplementacion</option>
            </select>
          </div>

          <!-- Templates list (read-only) -->
          <div v-if="filteredTemplates.length > 0" class="space-y-3">
            <div
              v-for="tpl in filteredTemplates"
              :key="tpl.id"
              class="flex items-center gap-4 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 hover:border-wc-accent/40 transition-colors"
            >
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-button bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-wc-text">{{ tpl.name }}</p>
                <p class="text-xs text-wc-text-tertiary capitalize">{{ tpl.type }} -- {{ tpl.duration || 'N/A' }}</p>
              </div>
            </div>
          </div>
          <div v-else class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">No se encontraron templates</p>
          </div>
        </template>

        <!-- ASSIGNED TAB -->
        <template v-if="activeTab === 'assigned'">
          <div v-if="assignedPlans.length > 0" class="space-y-3">
            <div v-for="plan in assignedPlans" :key="plan.id" class="flex items-center gap-4 rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
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
          <div v-else class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <p class="text-sm text-wc-text-tertiary">No hay planes asignados</p>
          </div>
        </template>

      </template>
    </div>
  </CoachLayout>
</template>
