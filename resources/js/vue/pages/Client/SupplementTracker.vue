<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const supplementPlan = ref(null);
const supplements = ref([]);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const completedToday = ref(0);
const totalToday = ref(0);
const weeklyAdherence = ref(0);
const dailyAdherence = ref([]);
const togglingId = ref(null);

// Fetch supplements
async function fetchSupplements() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/supplements', {
            params: { date: selectedDate.value },
        });
        const d = response.data;
        supplementPlan.value = d.plan || null;
        supplements.value = d.supplements || [];
        completedToday.value = d.completed_today || 0;
        totalToday.value = d.total_today || 0;
        weeklyAdherence.value = d.weekly_adherence || 0;
        dailyAdherence.value = d.daily_adherence || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar suplementacion';
    } finally {
        loading.value = false;
    }
}

// Toggle supplement
async function toggleSupplement(supplementId) {
    togglingId.value = supplementId;
    try {
        const response = await api.post('/api/v/client/supplements/toggle', {
            supplement_id: supplementId,
            date: selectedDate.value,
        });
        const d = response.data;
        const supp = supplements.value.find(s => s.id === supplementId);
        if (supp) {
            supp.completed = d.completed ?? !supp.completed;
        }
        completedToday.value = d.completed_today ?? completedToday.value;
    } catch {
        // Fail silently
    } finally {
        togglingId.value = null;
    }
}

// Date navigation
const isToday = computed(() => {
    return selectedDate.value === new Date().toISOString().split('T')[0];
});

function goToDate(direction) {
    const d = new Date(selectedDate.value);
    if (direction === 'prev') {
        d.setDate(d.getDate() - 1);
    } else if (direction === 'next') {
        if (isToday.value) return;
        d.setDate(d.getDate() + 1);
    }
    selectedDate.value = d.toISOString().split('T')[0];
    fetchSupplements();
}

function goToToday() {
    selectedDate.value = new Date().toISOString().split('T')[0];
    fetchSupplements();
}

// Formatted date
const formattedDate = computed(() => {
    const d = new Date(selectedDate.value + 'T12:00:00');
    const options = { weekday: 'long', day: 'numeric', month: 'long' };
    const formatted = d.toLocaleDateString('es-CO', options);
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});

// Progress percentage
const dailyProgress = computed(() => {
    if (totalToday.value === 0) return 0;
    return Math.round((completedToday.value / totalToday.value) * 100);
});

// Group supplements by timing
const supplementsByTiming = computed(() => {
    const groups = {};
    const timingOrder = ['manana', 'pre', 'tarde', 'post', 'noche'];
    const timingLabels = {
        manana: 'Manana',
        pre: 'Pre-entreno',
        tarde: 'Tarde',
        post: 'Post-entreno',
        noche: 'Noche',
    };

    for (const supp of supplements.value) {
        const timing = supp.timing || 'manana';
        if (!groups[timing]) {
            groups[timing] = {
                label: timingLabels[timing] || timing,
                items: [],
            };
        }
        groups[timing].items.push(supp);
    }

    // Sort by timing order
    const sorted = {};
    for (const t of timingOrder) {
        if (groups[t]) sorted[t] = groups[t];
    }
    // Add any remaining
    for (const t in groups) {
        if (!sorted[t]) sorted[t] = groups[t];
    }
    return sorted;
});

// Adherence color
function adherenceColor(pct) {
    if (pct >= 80) return 'text-emerald-500';
    if (pct >= 50) return 'text-amber-400';
    return 'text-wc-accent';
}

onMounted(() => {
    fetchSupplements();
});
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-14 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3">
        <div v-for="i in 2" :key="i" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
      <div v-for="i in 4" :key="'s'+i" class="h-16 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
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
        @click="fetchSupplements"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">SUPLEMENTACION</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu protocolo de suplementacion disenado por tu coach</p>
      </div>

      <!-- No plan state -->
      <div v-if="!supplementPlan" class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
          </svg>
        </div>
        <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN PROTOCOLO</h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">Tu coach aun no ha configurado un protocolo de suplementacion.</p>
      </div>

      <template v-else>
        <!-- Date Selector -->
        <div class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
          <button
            @click="goToDate('prev')"
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-secondary transition-colors hover:text-wc-text"
            aria-label="Dia anterior"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </button>

          <div class="text-center">
            <p class="font-data text-sm font-semibold text-wc-text">{{ formattedDate }}</p>
            <button v-if="!isToday" @click="goToToday" class="text-[10px] text-wc-accent hover:underline">Ir a hoy</button>
            <p v-else class="text-[10px] font-medium text-wc-accent">Hoy</p>
          </div>

          <button
            @click="goToDate('next')"
            :disabled="isToday"
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-secondary transition-colors"
            :class="isToday ? 'cursor-not-allowed opacity-30' : 'hover:text-wc-text'"
            aria-label="Dia siguiente"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <!-- Progress Overview -->
        <div class="grid grid-cols-2 gap-3">
          <!-- Daily Progress -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Hoy</p>
            <div class="mt-2 flex items-end gap-2">
              <span class="font-data text-3xl font-bold" :class="completedToday === totalToday && totalToday > 0 ? 'text-emerald-500' : 'text-wc-text'">
                {{ completedToday }}/{{ totalToday }}
              </span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="completedToday === totalToday && totalToday > 0 ? 'bg-emerald-500' : 'bg-wc-accent'"
                :style="{ width: `${dailyProgress}%` }"
              ></div>
            </div>
          </div>

          <!-- Weekly Adherence -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Semana</p>
            <div class="mt-2 flex items-end gap-1">
              <span class="font-data text-3xl font-bold" :class="adherenceColor(weeklyAdherence)">
                {{ weeklyAdherence }}%
              </span>
              <span class="mb-1 text-xs text-wc-text-tertiary">adherencia</span>
            </div>
            <!-- Mini sparkline -->
            <div class="mt-2 flex h-4 items-end gap-0.5">
              <div
                v-for="(day, index) in dailyAdherence"
                :key="index"
                class="flex-1 rounded-t-sm transition-all"
                :class="day.isSelected ? 'bg-wc-accent' : (day.pct >= 100 ? 'bg-emerald-500' : (day.pct > 0 ? 'bg-wc-bg-secondary' : 'bg-wc-bg-secondary/50'))"
                :style="{ height: `${Math.max(15, day.pct)}%` }"
                :title="`${day.day}: ${day.pct}%`"
              ></div>
            </div>
          </div>
        </div>

        <!-- Supplement Cards by Timing -->
        <div v-for="(group, timing) in supplementsByTiming" :key="timing" class="space-y-2">
          <div class="flex items-center gap-2">
            <!-- Timing icon -->
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-wc-bg-secondary">
              <svg v-if="timing === 'manana'" class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
              </svg>
              <svg v-else-if="timing === 'pre' || timing === 'post'" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
              <svg v-else-if="timing === 'noche'" class="h-4 w-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
              </svg>
              <svg v-else class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ group.label }}</h3>
          </div>

          <div class="space-y-1.5">
            <button
              v-for="supp in group.items"
              :key="supp.id"
              @click="toggleSupplement(supp.id)"
              :disabled="togglingId === supp.id"
              class="flex w-full items-center gap-3 rounded-xl border p-3.5 transition-all"
              :class="supp.completed
                ? 'border-emerald-500/30 bg-emerald-500/5'
                : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary'"
            >
              <!-- Checkmark -->
              <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-all duration-300"
                :class="supp.completed ? 'bg-emerald-500 text-white' : 'border-2 border-wc-border'"
              >
                <svg v-if="supp.completed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>

              <!-- Info -->
              <div class="min-w-0 flex-1 text-left">
                <h4 class="text-sm font-medium text-wc-text">{{ supp.name }}</h4>
                <p v-if="supp.dosage" class="text-xs text-wc-text-tertiary">{{ supp.dosage }}</p>
              </div>

              <!-- Adherence badge -->
              <span v-if="supp.adherence_pct !== undefined" class="font-data text-xs font-bold" :class="adherenceColor(supp.adherence_pct)">
                {{ supp.adherence_pct }}%
              </span>
            </button>
          </div>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>
