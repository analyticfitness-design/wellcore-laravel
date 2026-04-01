<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const records = ref([]);
const totalPrs = ref(0);
const totalExercises = ref(0);
const thisMonth = ref(0);

// Filters
const search = ref('');
const category = ref('all');

// Categories
const categories = [
    { key: 'all', label: 'Todos' },
    { key: 'fuerza', label: 'Fuerza' },
    { key: 'cardio', label: 'Cardio' },
    { key: 'calistenia', label: 'Calistenia' },
    { key: 'flexibilidad', label: 'Flexibilidad' },
];

// Fetch records
async function fetchRecords() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/records', {
            params: {
                search: search.value || undefined,
                category: category.value !== 'all' ? category.value : undefined,
            },
        });
        const d = response.data;
        records.value = d.records || [];
        totalPrs.value = d.total_prs || 0;
        totalExercises.value = d.total_exercises || 0;
        thisMonth.value = d.this_month || 0;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar records';
    } finally {
        loading.value = false;
    }
}

// Debounced search
let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchRecords();
    }, 300);
}

function onCategoryChange(cat) {
    category.value = cat;
    fetchRecords();
}

onMounted(() => {
    fetchRecords();
});

// Helpers
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' });
}

function getCategoryColor(cat) {
    const colors = {
        fuerza: 'bg-red-500/10 text-red-400',
        cardio: 'bg-orange-500/10 text-orange-400',
        calistenia: 'bg-blue-500/10 text-blue-400',
        flexibilidad: 'bg-green-500/10 text-green-400',
        endurance: 'bg-violet-500/10 text-violet-400',
        strength: 'bg-red-500/10 text-red-400',
        lift: 'bg-amber-500/10 text-amber-400',
    };
    return colors[cat] || 'bg-wc-bg-secondary text-wc-text-secondary';
}

function formatImprovement(value) {
    if (!value) return '';
    const num = parseFloat(value);
    if (num > 0) return `+${num}`;
    return `${num}`;
}
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="space-y-2">
          <div class="h-9 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
          <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-3">
        <div v-for="i in 3" :key="i" class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-12 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div v-for="i in 5" :key="'r'+i" class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchRecords"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">PERSONAL RECORDS</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Registra y compite contra ti mismo. Cada PR es una victoria.</p>
        </div>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-3 gap-3">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-wc-accent">{{ totalPrs }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">PRs Actuales</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">{{ totalExercises }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ejercicios</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-yellow-400">{{ thisMonth }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Este Mes</p>
        </div>
      </div>

      <!-- Search + category filter -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search"
            @input="onSearchInput"
            type="text"
            placeholder="Buscar ejercicio..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
        <div class="flex gap-2 overflow-x-auto">
          <button
            v-for="cat in categories"
            :key="cat.key"
            @click="onCategoryChange(cat.key)"
            class="shrink-0 rounded-lg border border-wc-border px-3 py-2 text-sm font-medium transition-colors"
            :class="category === cat.key
              ? 'bg-wc-accent text-white border-wc-accent'
              : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          >
            {{ cat.label }}
          </button>
        </div>
      </div>

      <!-- Records list -->
      <div v-if="records.length > 0" class="space-y-3">
        <div
          v-for="pr in records"
          :key="pr.id"
          class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/30"
        >
          <!-- Trophy icon -->
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="pr.is_current ? 'bg-yellow-500/10' : 'bg-wc-bg-secondary'">
            <span v-if="pr.is_current" class="text-lg">&#127942;</span>
            <span v-else class="text-lg opacity-40">&#129352;</span>
          </div>

          <!-- Exercise info -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-semibold text-wc-text">{{ pr.exercise }}</h3>
              <span
                v-if="pr.is_current"
                class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-yellow-400"
              >PR</span>
            </div>
            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-wc-text-tertiary">
              <span class="rounded px-1.5 py-0.5 text-[10px] font-medium" :class="getCategoryColor(pr.category)">
                {{ pr.category ? pr.category.charAt(0).toUpperCase() + pr.category.slice(1) : 'General' }}
              </span>
              <span>{{ formatDate(pr.achieved_at) }}</span>
              <span v-if="pr.notes" class="max-w-[200px] truncate">{{ pr.notes }}</span>
            </div>
          </div>

          <!-- Value -->
          <div class="shrink-0 text-right">
            <p class="font-data text-lg font-bold text-wc-text">{{ pr.value }}</p>
            <p v-if="pr.unit" class="text-[10px] text-wc-text-tertiary">{{ pr.unit }}</p>
            <p
              v-if="pr.improvement"
              class="mt-0.5 text-xs font-semibold"
              :class="parseFloat(pr.improvement) > 0 ? 'text-emerald-500' : 'text-wc-accent'"
            >
              {{ formatImprovement(pr.improvement) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-yellow-500/10">
          <span class="text-4xl">&#127942;</span>
        </div>
        <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">
          {{ search || category !== 'all' ? 'SIN RESULTADOS' : 'SIN RECORDS AUN' }}
        </h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">
          {{ search || category !== 'all'
            ? 'Intenta con otros filtros de busqueda.'
            : 'Tus records personales apareceran aqui cuando los registres.' }}
        </p>
      </div>
    </div>
  </ClientLayout>
</template>
