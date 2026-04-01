<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const plan = ref(null);
const hasMacros = ref(false);
const totalCalories = ref(0);
const proteinGrams = ref(0);
const carbGrams = ref(0);
const fatGrams = ref(0);
const macroPercentages = ref({ protein: 0, carbs: 0, fat: 0 });
const planObjetivo = ref('');
const meals = ref([]);
const currentWeight = ref(null);
const waterConsumed = ref(0);
const waterGoal = ref(3000);
const animateBars = ref(false);

// Fetch nutrition data
async function fetchNutrition() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/nutrition');
        const d = response.data;
        plan.value = d.plan || null;
        hasMacros.value = d.has_macros ?? false;
        totalCalories.value = d.total_calories || 0;
        proteinGrams.value = d.protein_grams || 0;
        carbGrams.value = d.carb_grams || 0;
        fatGrams.value = d.fat_grams || 0;
        macroPercentages.value = d.macro_percentages || { protein: 0, carbs: 0, fat: 0 };
        planObjetivo.value = d.plan_objetivo || '';
        meals.value = d.meals || [];
        currentWeight.value = d.current_weight || null;
        waterConsumed.value = d.water_consumed_ml || 0;
        waterGoal.value = d.water_goal_ml || 3000;

        setTimeout(() => { animateBars.value = true; }, 300);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el plan nutricional';
    } finally {
        loading.value = false;
    }
}

// Water tracker
const addingWater = ref(false);
async function addWater(ml) {
    addingWater.value = true;
    try {
        const response = await api.post('/api/v/client/nutrition/water', { ml });
        waterConsumed.value = response.data.water_consumed_ml ?? (waterConsumed.value + ml);
    } catch {
        // Fail silently
    } finally {
        addingWater.value = false;
    }
}

const waterPercent = computed(() => {
    return Math.min(100, Math.round((waterConsumed.value / waterGoal.value) * 100));
});

const waterDropsFilled = computed(() => {
    return Math.min(8, Math.round((waterConsumed.value / waterGoal.value) * 8));
});

// Meal icon helpers
function getMealIcon(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'sunrise';
    if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'lightning';
    if (n.includes('almuerzo') || n.includes('post-entreno')) return 'sun';
    if (n.includes('cena')) return 'moon';
    if (n.includes('snack') || n.includes('merienda')) return 'snack';
    return 'plate';
}

function getMealIconColor(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
    if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'bg-green-500/10 text-green-400';
    if (n.includes('almuerzo') || n.includes('post-entreno')) return 'bg-blue-500/10 text-blue-400';
    if (n.includes('cena')) return 'bg-violet-500/10 text-violet-400';
    return 'bg-wc-accent/10 text-wc-accent';
}

onMounted(() => {
    fetchNutrition();
});
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-28 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-40 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div v-for="i in 3" :key="'m'+i" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
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
        @click="fetchNutrition"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">NUTRICION</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu plan nutricional personalizado por tu coach</p>
      </div>

      <!-- No plan state -->
      <div v-if="!plan" class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12" />
          </svg>
        </div>
        <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN PLAN NUTRICIONAL</h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">Tu coach aun no ha asignado un plan nutricional. Contactalo para mas informacion.</p>
      </div>

      <template v-else>
        <!-- Macro Stat Cards -->
        <div v-if="hasMacros" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <!-- Calories -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 bg-emerald-500"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Calorias</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ totalCalories.toLocaleString() }}</p>
            <p class="mt-0.5 text-xs font-medium text-emerald-500">kcal / dia</p>
          </div>

          <!-- Protein -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 bg-wc-accent"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Proteina</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ proteinGrams }}<span class="text-lg font-normal">g</span></p>
            <p class="mt-0.5 text-xs font-medium text-wc-accent">{{ macroPercentages.protein }}% del total</p>
          </div>

          <!-- Carbs -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 bg-blue-500"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Carbos</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ carbGrams }}<span class="text-lg font-normal">g</span></p>
            <p class="mt-0.5 text-xs font-medium text-blue-500">{{ macroPercentages.carbs }}% del total</p>
          </div>

          <!-- Fat -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 bg-amber-500"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Grasas</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ fatGrams }}<span class="text-lg font-normal">g</span></p>
            <p class="mt-0.5 text-xs font-medium text-amber-500">{{ macroPercentages.fat }}% del total</p>
          </div>
        </div>

        <!-- Macro visual bars -->
        <div v-if="hasMacros" class="flex h-2 w-full overflow-hidden rounded-full">
          <div class="h-full bg-wc-accent transition-all duration-700 delay-100" :style="{ width: animateBars ? `${macroPercentages.protein}%` : '0%' }"></div>
          <div class="h-full bg-blue-500 transition-all duration-700 delay-200" :style="{ width: animateBars ? `${macroPercentages.carbs}%` : '0%' }"></div>
          <div class="h-full bg-amber-500 transition-all duration-700 delay-300" :style="{ width: animateBars ? `${macroPercentages.fat}%` : '0%' }"></div>
        </div>

        <!-- Current weight -->
        <div v-if="currentWeight" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Peso actual</p>
              <p class="font-data text-2xl font-bold text-wc-text">{{ currentWeight }} <span class="text-sm font-normal text-wc-text-tertiary">kg</span></p>
            </div>
          </div>
        </div>

        <!-- Plan Objective -->
        <div v-if="planObjetivo" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.048 8.287 8.287 0 0 0 9 9.6a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo del plan</p>
              <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ planObjetivo }}</p>
            </div>
          </div>
        </div>

        <!-- Water Tracker -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5S7.5 9 7.5 13.5a4.5 4.5 0 0 0 9 0C16.5 9 12 3.5 12 3.5Z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Agua hoy</p>
                <p class="font-data text-lg font-bold text-wc-text">
                  {{ (waterConsumed / 1000).toFixed(1) }}L
                  <span class="text-sm font-normal text-wc-text-tertiary">/ {{ (waterGoal / 1000).toFixed(1) }}L</span>
                </p>
              </div>
            </div>
            <span class="font-data text-2xl font-bold" :class="waterPercent >= 100 ? 'text-emerald-500' : 'text-blue-500'">
              {{ waterPercent }}%
            </span>
          </div>

          <!-- Water progress bar -->
          <div class="mb-4 h-3 w-full overflow-hidden rounded-full bg-wc-bg">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="waterPercent >= 100 ? 'bg-emerald-500' : 'bg-blue-500'"
              :style="{ width: `${waterPercent}%` }"
            ></div>
          </div>

          <!-- Water drops indicator -->
          <div class="mb-4 flex items-center justify-center gap-1.5">
            <div
              v-for="i in 8"
              :key="i"
              class="h-4 w-3 rounded-b-full transition-colors duration-300"
              :class="i <= waterDropsFilled ? 'bg-blue-500' : 'bg-wc-bg'"
            ></div>
          </div>

          <!-- Add water buttons -->
          <div class="flex items-center gap-2">
            <button
              v-for="amount in [250, 500, 1000]"
              :key="amount"
              @click="addWater(amount)"
              :disabled="addingWater"
              class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm font-medium text-wc-text transition-colors hover:border-blue-500/50 hover:text-blue-500 disabled:opacity-50"
            >
              +{{ amount >= 1000 ? `${amount / 1000}L` : `${amount}ml` }}
            </button>
          </div>
        </div>

        <!-- Meals -->
        <div v-if="meals.length > 0">
          <h3 class="mb-3 font-display text-xl tracking-wide text-wc-text">COMIDAS DEL DIA</h3>
          <div class="space-y-2">
            <div
              v-for="(meal, index) in meals"
              :key="index"
              class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
            >
              <div class="flex items-center gap-3 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="getMealIconColor(meal.nombre)">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <h4 class="text-sm font-semibold text-wc-text">{{ meal.nombre }}</h4>
                  <p v-if="meal.hora" class="text-xs text-wc-text-tertiary">{{ meal.hora }}</p>
                </div>
                <div v-if="meal.calorias" class="text-right">
                  <p class="font-data text-sm font-bold text-wc-text">{{ meal.calorias }}</p>
                  <p class="text-[10px] text-wc-text-tertiary">kcal</p>
                </div>
              </div>

              <!-- Alimentos -->
              <div v-if="meal.alimentos && meal.alimentos.length" class="border-t border-wc-border/50 px-4 py-3">
                <div v-for="(alimento, ai) in meal.alimentos" :key="ai" class="flex items-center justify-between py-1">
                  <span class="text-xs text-wc-text-secondary">{{ alimento.nombre || alimento }}</span>
                  <span v-if="alimento.cantidad" class="text-xs text-wc-text-tertiary">{{ alimento.cantidad }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>
