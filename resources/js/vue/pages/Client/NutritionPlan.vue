<script setup>
import { ref, computed, onMounted } from 'vue';
import { Replace, X as XIcon, Search as SearchIcon, CheckCircle2, AlertTriangle, Flame, RefreshCw } from 'lucide-vue-next';
import { useApi } from '../../composables/useApi';
import { RECIPES } from '../../data/recipes';
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
const weightGoal = ref(null);
const waterConsumed = ref(0);
const waterGoal = ref(3000);
const animateBars = ref(false);
const coachNotes = ref(null);
const restDayInfo = ref(null);
const tips = ref([]);
const comidasSugeridas = ref([]);
const planSemanal = ref([]);
const hydrationNote = ref(null);
const showTutorial = ref(false);
const tutorialStep = ref(1);

// Accordion state per meal
const openMeals = ref({});
const openSugeridas = ref({});
const openRestDay = ref(false);
const openPlanDays = ref({});

function toggleMeal(index) {
  openMeals.value[index] = !openMeals.value[index];
}

function toggleSugerida(index) {
  openSugeridas.value[index] = !openSugeridas.value[index];
}

function togglePlanDay(index) {
  openPlanDays.value[index] = !openPlanDays.value[index];
}

// Fetch nutrition data
async function fetchNutrition() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/nutrition');
        const d = response.data;

        // API returns nested structure: macros, meals, extras, water, weight
        plan.value = d.has_plan ? (d.plan_raw || true) : null;

        const m = d.macros || {};
        hasMacros.value = m.has_macros ?? false;
        totalCalories.value = m.total_calories || 0;
        proteinGrams.value = m.protein_g || 0;
        carbGrams.value = m.carb_g || 0;
        fatGrams.value = m.fat_g || 0;
        macroPercentages.value = m.percentages || { protein: 0, carbs: 0, fat: 0 };

        meals.value = d.meals || [];

        const ex = d.extras || {};
        planObjetivo.value = ex.objetivo || '';
        coachNotes.value = ex.coach_notes || null;
        restDayInfo.value = ex.rest_day_info || null;
        tips.value = ex.tips || [];
        comidasSugeridas.value = ex.comidas_sugeridas || [];
        planSemanal.value = ex.plan_semanal || [];
        hydrationNote.value = ex.hydration_note || null;

        const w = d.water || {};
        waterConsumed.value = w.consumed_ml || 0;
        waterGoal.value = w.goal_ml || 3000;

        const wt = d.weight || {};
        currentWeight.value = wt.current_kg || null;
        weightGoal.value = wt.goal_kg || null;

        showTutorial.value = d.show_tutorial ?? false;

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
        const response = await api.post('/api/v/client/nutrition/water', { amount: ml });
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

// Weight delta
const weightDelta = computed(() => {
    if (currentWeight.value && weightGoal.value) {
        return Math.round((weightGoal.value - currentWeight.value) * 10) / 10;
    }
    return null;
});

// Meal helpers
function getMealIconColor(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
    if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'bg-green-500/10 text-green-400';
    if (n.includes('almuerzo') || n.includes('post-entreno')) return 'bg-blue-500/10 text-blue-400';
    if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
    if (n.includes('snack') || n.includes('merienda')) return 'bg-pink-500/10 text-pink-400';
    return 'bg-wc-accent/10 text-wc-accent';
}

function getSugeridaIconColor(nombre) {
    const n = (nombre || '').toLowerCase();
    if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
    if (n.includes('almuerzo') || n.includes('comida')) return 'bg-blue-500/10 text-blue-400';
    if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
    if (n.includes('pre-entreno') || n.includes('pre ')) return 'bg-emerald-500/10 text-emerald-400';
    if (n.includes('post')) return 'bg-orange-500/10 text-orange-400';
    if (n.includes('snack') || n.includes('merienda')) return 'bg-pink-500/10 text-pink-400';
    return 'bg-wc-accent/10 text-amber-400';
}

function extractTime(nombre) {
    const match = (nombre || '').match(/(\d{1,2}:\d{2}(?:am|pm)?)/i);
    return match ? match[1] : null;
}

function cleanMealName(nombre) {
    const time = extractTime(nombre);
    if (time) {
        return (nombre || '').replace('—', '').replace(time, '').trim().toUpperCase();
    }
    return (nombre || '').toUpperCase();
}

function formatAlimento(alimento) {
    if (typeof alimento === 'string') return alimento;
    if (typeof alimento === 'object' && alimento !== null) {
        const name = alimento.nombre || alimento.alimento || alimento.name || '';
        const qty = alimento.cantidad || alimento.porcion || alimento.quantity || alimento.amount || '';
        if (name && qty) return `${name} — ${qty}`;
        return name || qty || '';
    }
    return String(alimento);
}

// Tutorial
async function dismissTutorial() {
    showTutorial.value = false;
    try {
        await api.post('/api/v/client/nutrition/dismiss-tutorial');
    } catch {
        // non-critical
    }
}

// Food icon helper — maps food keywords to emojis
function foodIcon(name) {
  const lower = (name || '').toLowerCase();
  const map = [
    [['pollo','pechuga','chicken','pavo'], '\u{1F357}'],
    [['carne','res','steak','lomo','cerdo'], '\u{1F969}'],
    [['salm\u00F3n','salmon','at\u00FAn','atun','tilapia','pescado','corvina','trucha'], '\u{1F41F}'],
    [['huevo','clara','claras'], '\u{1F95A}'],
    [['yogur','yogurt','leche'], '\u{1F95B}'],
    [['queso','reques\u00F3n','requeson'], '\u{1F9C0}'],
    [['avena','granola','oatmeal'], '\u{1F963}'],
    [['arroz','rice','quinoa'], '\u{1F35A}'],
    [['pasta'], '\u{1F35D}'],
    [['pan','tostada'], '\u{1F35E}'],
    [['arepa','tortilla'], '\u{1FAD3}'],
    [['papa'], '\u{1F954}'],
    [['batata','camote'], '\u{1F360}'],
    [['banana','banano','pl\u00E1tano','platano'], '\u{1F34C}'],
    [['manzana'], '\u{1F34E}'],
    [['fresa','fresas'], '\u{1F353}'],
    [['fruta','frutas'], '\u{1F347}'],
    [['br\u00F3coli','brocoli'], '\u{1F966}'],
    [['espinaca','lechuga'], '\u{1F96C}'],
    [['ensalada','vegetal','vegetales'], '\u{1F957}'],
    [['tomate'], '\u{1F345}'],
    [['aguacate','avocado'], '\u{1F951}'],
    [['nuez','nueces','almendra','man\u00ED','mani'], '\u{1F95C}'],
    [['aceite','oliva'], '\u{1FAD2}'],
    [['prote\u00EDna','proteina','whey'], '\u{1F9EA}'],
    [['agua'], '\u{1F4A7}'],
    [['caf\u00E9','cafe'], '\u2615'],
    [['miel'], '\u{1F36F}'],
  ];
  for (const [keywords, emoji] of map) {
    if (keywords.some(k => lower.includes(k))) return emoji;
  }
  return null;
}

function getAlimentoName(alimento) {
  if (typeof alimento === 'string') return alimento;
  if (typeof alimento === 'object' && alimento !== null) {
    return alimento.nombre || alimento.alimento || alimento.name || '';
  }
  return String(alimento);
}

// ─── RECIPE SWAPS ────────────────────────────────────────────────────────
// Live data from macros-today endpoint (includes swap state per meal)
const macrosToday = ref(null);
const loadingMacros = ref(false);
const swapModalMeal = ref(null); // {name, calories, protein, carbs, fat, swapped, swap_id?}
const swapSearch = ref('');
const applyingSwap = ref(false);
const toast = ref(null); // {type:'success'|'error', msg}
let toastTimer = null;

async function loadMacrosToday() {
  loadingMacros.value = true;
  try {
    const r = await api.get('/api/v/client/nutrition/macros-today');
    macrosToday.value = r.data;
  } catch (e) {
    macrosToday.value = null;
  } finally {
    loadingMacros.value = false;
  }
}

// Find the matching meal from macros-today by fuzzy name match against plan meal name
function findTodayMeal(planMealName) {
  if (!macrosToday.value?.meals) return null;
  const clean = (s) => (s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
  const target = clean(cleanMealName(planMealName));
  return macrosToday.value.meals.find(m => {
    const mn = clean(m.name);
    return mn === target || mn.includes(target) || target.includes(mn);
  }) || null;
}

function openSwapModal(planMeal) {
  const todayMeal = findTodayMeal(planMeal.nombre);
  // Fallback: synthesize from plan meal macros if macros-today doesn't have it
  swapModalMeal.value = todayMeal || {
    name: cleanMealName(planMeal.nombre),
    calories: planMeal.calorias || 0,
    protein: planMeal.macros?.proteina || 0,
    carbs: planMeal.macros?.carbohidratos || 0,
    fat: planMeal.macros?.grasas || 0,
    swapped: false,
    swap_id: null,
  };
  swapSearch.value = '';
}

function closeSwapModal() {
  swapModalMeal.value = null;
}

// Compatibility: ideal (±15% cal), aceptable (±30%), else bad
function compatibility(recipe, meal) {
  if (!meal || !meal.calories) return 'bad';
  const diff = Math.abs(recipe.macros.cal - meal.calories) / meal.calories;
  if (diff <= 0.15) return 'ideal';
  if (diff <= 0.30) return 'aceptable';
  return 'bad';
}

const compatOrder = { ideal: 0, aceptable: 1, bad: 2 };

const swapCandidates = computed(() => {
  if (!swapModalMeal.value) return [];
  const q = swapSearch.value.trim().toLowerCase();
  return RECIPES
    .filter(r => !q || r.name.toLowerCase().includes(q) || r.description.toLowerCase().includes(q))
    .map(r => ({ recipe: r, score: compatibility(r, swapModalMeal.value) }))
    .sort((a, b) => compatOrder[a.score] - compatOrder[b.score]);
});

function showToast(type, msg) {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { type, msg };
  toastTimer = setTimeout(() => { toast.value = null; }, 3500);
}

async function applySwap(recipe) {
  if (!swapModalMeal.value || applyingSwap.value) return;
  applyingSwap.value = true;
  const meal = swapModalMeal.value;
  try {
    await api.post('/api/v/client/nutrition/swap', {
      recipe_id: recipe.id,
      recipe_name: recipe.name,
      original_meal_name: meal.name,
      recipe_macros: {
        calories: recipe.macros.cal,
        protein: recipe.macros.protein,
        carbs: recipe.macros.carbs,
        fat: recipe.macros.fat,
      },
      original_macros: {
        calories: meal.calories,
        protein: meal.protein,
        carbs: meal.carbs,
        fat: meal.fat,
      },
    });
    await loadMacrosToday();
    closeSwapModal();
    showToast('success', `Reemplazado por ${recipe.name}`);
  } catch (e) {
    showToast('error', 'Error al aplicar el reemplazo. Intenta de nuevo.');
  } finally {
    applyingSwap.value = false;
  }
}

async function undoSwap(planMeal) {
  const todayMeal = findTodayMeal(planMeal.nombre);
  if (!todayMeal?.swap_id) return;
  try {
    await api.delete(`/api/v/client/nutrition/swap/${todayMeal.swap_id}`);
    await loadMacrosToday();
    showToast('success', 'Reemplazo deshecho');
  } catch (e) {
    showToast('error', 'No se pudo deshacer');
  }
}

function isMealSwapped(planMeal) {
  return findTodayMeal(planMeal.nombre)?.swapped === true;
}

function swappedRecipeName(planMeal) {
  return findTodayMeal(planMeal.nombre)?.recipe_name || '';
}

onMounted(() => {
    fetchNutrition();
    loadMacrosToday();
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
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-emerald-500/10">
          <svg class="h-10 w-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265z" />
          </svg>
        </div>
        <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">TU PLAN ESTA EN CAMINO</h3>
        <p class="mx-auto mt-2 max-w-xs text-sm text-wc-text-secondary">Tu coach esta disenando tu plan de nutricion. Te notificaremos cuando este listo.</p>
      </div>

      <template v-else>
        <!-- ─── MACRO STAT CARDS ─────────────────────────────────────────── -->
        <div v-if="hasMacros" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <!-- Calories -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 wc-macro-calories-bg"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Calorias</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ totalCalories.toLocaleString() }}</p>
            <p class="mt-0.5 text-xs font-medium wc-macro-calories">kcal / dia</p>
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
            <div class="absolute inset-x-0 top-0 h-0.5 wc-macro-carbs-bg"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Carbos</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ carbGrams }}<span class="text-lg font-normal">g</span></p>
            <p class="mt-0.5 text-xs font-medium wc-macro-carbs">{{ macroPercentages.carbs }}% del total</p>
          </div>

          <!-- Fat -->
          <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
            <div class="absolute inset-x-0 top-0 h-0.5 wc-macro-fat-bg"></div>
            <p class="text-[10px] font-medium uppercase tracking-widest text-wc-text-tertiary">Grasas</p>
            <p class="mt-1 font-data text-3xl font-bold tabular-nums text-wc-text">{{ fatGrams }}<span class="text-lg font-normal">g</span></p>
            <p class="mt-0.5 text-xs font-medium wc-macro-fat">{{ macroPercentages.fat }}% del total</p>
          </div>
        </div>

        <!-- Macro visual bars -->
        <div v-if="hasMacros" class="flex h-2 w-full overflow-hidden rounded-full">
          <div class="h-full bg-wc-accent transition-all duration-700 delay-100" :style="{ width: animateBars ? `${macroPercentages.protein}%` : '0%' }"></div>
          <div class="h-full wc-macro-carbs-bg transition-all duration-700 delay-200" :style="{ width: animateBars ? `${macroPercentages.carbs}%` : '0%' }"></div>
          <div class="h-full wc-macro-fat-bg transition-all duration-700 delay-300" :style="{ width: animateBars ? `${macroPercentages.fat}%` : '0%' }"></div>
        </div>

        <!-- ─── OBJETIVO ──────────────────────────────────────────────────── -->
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

        <!-- ─── COMIDAS DEL DIA (accordion) ───────────────────────────────── -->
        <div v-if="meals.length > 0">
          <h3 class="mb-3 font-display text-xl tracking-wide text-wc-text">COMIDAS DEL DIA</h3>
          <div class="space-y-2">
            <div
              v-for="(meal, index) in meals"
              :key="index"
              class="overflow-hidden rounded-xl border bg-wc-bg-secondary transition-colors"
              :class="isMealSwapped(meal) ? 'border-wc-accent/40' : 'border-wc-border'"
            >
              <!-- Swapped marker — fine gradient line + glass chip -->
              <div v-if="isMealSwapped(meal)" class="relative">
                <div class="h-px w-full bg-gradient-to-r from-wc-accent/0 via-wc-accent/40 to-wc-accent/0"></div>
                <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                  <div class="group/chip flex min-w-0 items-center gap-2.5 rounded-full border border-white/[0.06] bg-white/[0.02] px-3 py-1 backdrop-blur-sm">
                    <span class="relative flex h-1 w-1 shrink-0">
                      <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-60"></span>
                      <span class="relative inline-flex h-1 w-1 rounded-full bg-wc-accent"></span>
                    </span>
                    <span class="font-display text-[9px] tracking-[0.22em] text-white/40">REEMPLAZADO POR</span>
                    <span class="min-w-0 truncate font-display text-xs tracking-wider text-wc-text">{{ swappedRecipeName(meal) }}</span>
                    <button
                      @click.stop="undoSwap(meal)"
                      class="ml-1 flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-white/30 opacity-0 transition-all duration-300 hover:text-wc-accent group-hover/chip:opacity-100"
                      aria-label="Deshacer reemplazo"
                    >
                      <XIcon :size="10" :stroke-width="2" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Header (clickable) -->
              <button
                @click="toggleMeal(index)"
                class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary"
              >
                <!-- Number badge -->
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="getMealIconColor(meal.nombre)">
                  <span class="font-data text-sm font-bold">{{ index + 1 }}</span>
                </div>

                <!-- Name + time -->
                <div class="min-w-0 flex-1">
                  <p class="truncate font-display text-sm tracking-wide text-wc-text">
                    {{ cleanMealName(meal.nombre) }}
                  </p>
                  <p v-if="extractTime(meal.nombre) || meal.hora" class="text-[11px] text-wc-text-tertiary">
                    {{ extractTime(meal.nombre) || meal.hora }}
                  </p>
                </div>

                <!-- Macro chips (desktop) -->
                <div class="hidden items-center gap-1.5 sm:flex">
                  <span v-if="meal.macros?.proteina > 0"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    style="background:rgba(220,38,38,0.12); color:#F87171;">
                    P {{ meal.macros.proteina }}g
                  </span>
                  <span v-if="meal.macros?.carbohidratos > 0"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    style="background:rgba(59,130,246,0.12); color:#60A5FA;">
                    C {{ meal.macros.carbohidratos }}g
                  </span>
                  <span v-if="meal.macros?.grasas > 0"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    style="background:rgba(245,158,11,0.12); color:#FBBF24;">
                    G {{ meal.macros.grasas }}g
                  </span>
                </div>

                <!-- Swap CTA — ghost / subtle -->
                <span
                  @click.stop="openSwapModal(meal)"
                  role="button"
                  :title="isMealSwapped(meal) ? 'Cambiar por otra receta' : 'Cambiar por receta'"
                  class="wc-swap-ghost ml-2 group/swap inline-flex items-center gap-2 rounded-full px-2.5 py-1.5 text-white/40 transition-all duration-300 ease-out hover:text-wc-accent hover:bg-white/[0.04]"
                >
                  <Replace :size="13" :stroke-width="2" class="transition-transform duration-300 group-hover/swap:rotate-180" />
                  <span class="hidden font-display text-[10px] tracking-[0.2em] sm:inline">CAMBIAR</span>
                </span>

                <!-- Calories + chevron -->
                <div class="ml-2 flex shrink-0 items-center gap-3">
                  <span v-if="meal.calorias > 0" class="font-data text-sm font-bold tabular-nums text-wc-text">
                    {{ meal.calorias }}<span class="text-xs font-normal text-wc-text-tertiary"> kcal</span>
                  </span>
                  <svg class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                       :class="{ 'rotate-180': openMeals[index] }"
                       fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                  </svg>
                </div>
              </button>

              <!-- Body (expandable) -->
              <Transition name="accordion">
                <div v-show="openMeals[index]" class="border-t border-wc-border">
                  <div class="space-y-3 p-4">

                    <!-- Mobile macro chips -->
                    <div class="flex flex-wrap gap-1.5 sm:hidden">
                      <span v-if="meal.macros?.proteina > 0"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        style="background:rgba(220,38,38,0.12); color:#F87171;">
                        P {{ meal.macros.proteina }}g
                      </span>
                      <span v-if="meal.macros?.carbohidratos > 0"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        style="background:rgba(59,130,246,0.12); color:#60A5FA;">
                        C {{ meal.macros.carbohidratos }}g
                      </span>
                      <span v-if="meal.macros?.grasas > 0"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                        style="background:rgba(245,158,11,0.12); color:#FBBF24;">
                        G {{ meal.macros.grasas }}g
                      </span>
                    </div>

                    <!-- Alimentos -->
                    <ul v-if="meal.alimentos && meal.alimentos.length" class="space-y-1.5">
                      <li v-for="(alimento, ai) in meal.alimentos" :key="ai" class="flex items-start gap-2.5">
                        <span v-if="foodIcon(getAlimentoName(alimento))" class="shrink-0 text-base leading-relaxed">{{ foodIcon(getAlimentoName(alimento)) }}</span>
                        <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                        <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formatAlimento(alimento) }}</span>
                      </li>
                    </ul>

                    <!-- Nota -->
                    <div v-if="meal.notas" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3.5 py-3">
                      <p class="text-xs leading-relaxed text-wc-text-tertiary">{{ meal.notas }}</p>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- ─── PLAN POR DIA (plan_semanal) ─────────────────────────────── -->
        <div v-if="planSemanal.length > 0">
          <h3 class="mb-3 font-display text-xl tracking-wide text-wc-text">PLAN POR DIA</h3>
          <div class="space-y-2">
            <div
              v-for="(diaItem, di) in planSemanal"
              :key="di"
              class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
            >
              <button
                @click="togglePlanDay(di)"
                class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                :aria-expanded="openPlanDays[di] ? 'true' : 'false'"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                  <span class="font-data text-sm font-bold text-wc-accent">{{ di + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-display text-sm tracking-wide text-wc-text">{{ (diaItem.dia || 'Dia').toUpperCase() }}</p>
                  <p v-if="diaItem.comidas && diaItem.comidas.length" class="text-[11px] text-wc-text-tertiary">
                    {{ diaItem.comidas.length }} comida{{ diaItem.comidas.length !== 1 ? 's' : '' }}
                  </p>
                </div>
                <svg
                  class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                  :class="{ 'rotate-180': openPlanDays[di] }"
                  fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <Transition name="accordion">
                <div v-show="openPlanDays[di]" class="border-t border-wc-border">
                  <div class="space-y-2 p-4">
                    <div
                      v-for="(comida, ci) in (diaItem.comidas || [])"
                      :key="ci"
                      class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3"
                    >
                      <p class="mb-1.5 font-display text-xs tracking-wide text-wc-text">{{ (comida.nombre || 'Comida').toUpperCase() }}</p>
                      <ul v-if="comida.alimentos && comida.alimentos.length" class="space-y-1">
                        <li v-for="(alimento, ai) in comida.alimentos" :key="ai" class="flex items-start gap-2">
                          <span v-if="foodIcon(getAlimentoName(alimento))" class="shrink-0 text-base leading-relaxed">{{ foodIcon(getAlimentoName(alimento)) }}</span>
                          <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                          <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formatAlimento(alimento) }}</span>
                        </li>
                      </ul>
                      <ul v-else-if="comida.opciones && comida.opciones.length" class="space-y-1">
                        <li v-for="(opcion, oi) in comida.opciones" :key="oi" class="flex items-start gap-2">
                          <span v-if="foodIcon(opcion)" class="shrink-0 text-base leading-relaxed">{{ foodIcon(opcion) }}</span>
                          <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                          <span class="text-sm leading-relaxed text-wc-text-secondary">{{ opcion }}</span>
                        </li>
                      </ul>
                      <p v-if="comida.notas" class="mt-2 text-xs text-wc-text-tertiary">{{ comida.notas }}</p>
                    </div>
                    <p v-if="!diaItem.comidas || diaItem.comidas.length === 0" class="py-2 text-center text-sm text-wc-text-tertiary">
                      Sin comidas definidas para este dia.
                    </p>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- ─── TIPS NUTRICIONALES ────────────────────────────────────────── -->
        <div v-if="tips.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <h3 class="font-display text-xl tracking-wide text-wc-text">TIPS NUTRICIONALES</h3>
          <ul class="mt-4 space-y-2.5">
            <li v-for="(tip, ti) in tips" :key="ti" class="flex items-start gap-3">
              <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <p class="text-sm leading-relaxed text-wc-text-secondary">{{ tip }}</p>
            </li>
          </ul>
        </div>

        <!-- ─── COMIDAS SUGERIDAS (opciones multiples) ────────────────────── -->
        <div v-if="comidasSugeridas.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <h3 class="font-display text-xl tracking-wide text-wc-text">COMIDAS SUGERIDAS</h3>
          <p class="mt-1 text-xs text-wc-text-tertiary">Opciones de alimentos por momento del dia</p>

          <div class="mt-4 space-y-2">
            <div
              v-for="(comidaSug, si) in comidasSugeridas"
              :key="si"
              class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary"
            >
              <button
                @click="toggleSugerida(si)"
                class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-secondary/50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                :aria-expanded="openSugeridas[si] ? 'true' : 'false'"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="getSugeridaIconColor(comidaSug.nombre)">
                  <span class="font-data text-sm font-bold">{{ si + 1 }}</span>
                </div>

                <div class="flex-1">
                  <p class="font-display text-sm tracking-wide text-wc-text">{{ (comidaSug.nombre || 'Comida').toUpperCase() }}</p>
                  <p v-if="comidaSug.opciones && comidaSug.opciones.length" class="text-[11px] text-wc-text-tertiary">
                    {{ comidaSug.opciones.length }} opcion{{ comidaSug.opciones.length !== 1 ? 'es' : '' }}
                  </p>
                </div>

                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                     :class="{ 'rotate-180': openSugeridas[si] }"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <Transition name="accordion">
                <div v-show="openSugeridas[si]" class="border-t border-wc-border/50">
                  <ul class="space-y-2 p-4">
                    <li v-for="(opcion, oi) in (comidaSug.opciones || [])" :key="oi" class="flex items-start gap-2.5">
                      <span v-if="foodIcon(opcion)" class="shrink-0 text-base leading-relaxed">{{ foodIcon(opcion) }}</span>
                      <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                      <span class="text-sm leading-relaxed text-wc-text-secondary">{{ opcion }}</span>
                    </li>
                  </ul>
                </div>
              </Transition>
            </div>
          </div>
        </div>

        <!-- ─── HIDRATACION ────────────────────────────────────────────────── -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-xl tracking-wide text-wc-text">HIDRATACION</h3>
            <div class="flex items-baseline gap-1">
              <span class="font-data text-2xl font-bold tabular-nums text-wc-text">{{ (waterConsumed / 1000).toFixed(1) }}</span>
              <span class="text-xs text-wc-text-tertiary">/ {{ (waterGoal / 1000).toFixed(1) }}L</span>
            </div>
          </div>

          <!-- Progress bar (gradient) -->
          <div class="mb-4 h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
            <div
              class="h-full rounded-full transition-all duration-500"
              style="background: linear-gradient(90deg, #3B82F6, #06B6D4);"
              :style="{ width: `${waterPercent}%` }"
            ></div>
          </div>

          <!-- Water drops (SVG) -->
          <div class="mb-4 flex items-center justify-center gap-2">
            <svg
              v-for="i in 8"
              :key="i"
              class="h-7 w-5 transition-all duration-300"
              :class="i <= waterDropsFilled ? 'opacity-100' : 'opacity-20'"
              viewBox="0 0 20 28"
              fill="none"
            >
              <path
                d="M10 2C10 2 3 11 3 17C3 20.866 6.134 24 10 24C13.866 24 17 20.866 17 17C17 11 10 2 10 2Z"
                :fill="i <= waterDropsFilled ? '#3B82F6' : '#374151'"
              />
            </svg>
          </div>

          <!-- Water buttons -->
          <div class="flex flex-col gap-2">
            <div class="flex gap-2">
              <button
                @click="addWater(250)"
                :disabled="addingWater"
                class="flex-1 rounded-lg border border-blue-500/30 bg-blue-500/10 py-2.5 text-sm font-semibold text-blue-400 transition active:scale-95 hover:bg-blue-500/20 disabled:opacity-50"
              >
                + 250 mL
              </button>
              <button
                @click="addWater(500)"
                :disabled="addingWater"
                class="flex-1 rounded-lg border border-blue-500/30 bg-blue-500/10 py-2.5 text-sm font-semibold text-blue-400 transition active:scale-95 hover:bg-blue-500/20 disabled:opacity-50"
              >
                + 500 mL
              </button>
            </div>
            <p v-if="hydrationNote" class="text-center text-[11px] text-wc-text-tertiary">{{ hydrationNote }}</p>
          </div>
        </div>

        <!-- ─── DIA DE DESCANSO ───────────────────────────────────────────── -->
        <div v-if="restDayInfo" class="space-y-1">
          <button
            @click="openRestDay = !openRestDay"
            class="flex w-full items-center justify-between rounded-xl border border-wc-border bg-wc-bg-secondary px-5 py-4 text-left transition hover:bg-wc-bg-tertiary"
          >
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background:rgba(139,92,246,0.12);">
                <svg class="h-4 w-4" style="color:#A78BFA;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
              </div>
              <div>
                <p class="font-display text-sm tracking-wide text-wc-text">DIA DE DESCANSO</p>
                <p v-if="restDayInfo.calorias_objetivo > 0" class="text-xs text-wc-text-tertiary">
                  ~{{ restDayInfo.calorias_objetivo.toLocaleString() }} kcal
                </p>
              </div>
            </div>
            <svg class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200" :class="{ 'rotate-180': openRestDay }"
                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          <Transition name="accordion">
            <div v-show="openRestDay" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
              <div class="space-y-2 p-4">
                <p v-if="restDayInfo.descripcion" class="text-sm text-wc-text-secondary">{{ restDayInfo.descripcion }}</p>
                <ul v-if="restDayInfo.ajustes && restDayInfo.ajustes.length" class="mt-2 space-y-1.5">
                  <li v-for="(ajuste, ai) in restDayInfo.ajustes" :key="ai" class="flex items-start gap-2 text-sm text-wc-text-tertiary">
                    <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-purple-400"></span>
                    {{ ajuste }}
                  </li>
                </ul>
              </div>
            </div>
          </Transition>
        </div>

        <!-- ─── NOTAS DEL COACH ────────────────────────────────────────────── -->
        <div v-if="coachNotes" class="overflow-hidden rounded-xl border border-wc-accent/30 bg-wc-bg-secondary">
          <div class="flex items-center gap-3 border-b border-wc-accent/20 px-5 py-3.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
              </svg>
            </div>
            <p class="font-display text-sm tracking-wide text-wc-accent">NOTAS DE TU COACH</p>
          </div>
          <div class="p-5">
            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ coachNotes }}</p>
          </div>
        </div>

        <!-- ─── PESO ───────────────────────────────────────────────────────── -->
        <div v-if="currentWeight || weightGoal" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
          <h3 class="mb-4 font-display text-xl tracking-wide text-wc-text">PESO</h3>
          <div class="grid grid-cols-2 gap-3">
            <div v-if="currentWeight" class="rounded-lg bg-wc-bg-tertiary p-4 text-center">
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Actual</p>
              <p class="mt-1 font-data text-2xl font-bold tabular-nums text-wc-text">{{ currentWeight.toFixed(1) }}</p>
              <p class="text-xs text-wc-text-tertiary">kg</p>
            </div>
            <div v-if="weightGoal" class="rounded-lg bg-wc-bg-tertiary p-4 text-center">
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
              <p class="mt-1 font-data text-2xl font-bold tabular-nums text-wc-text">{{ weightGoal.toFixed(1) }}</p>
              <p class="text-xs text-wc-text-tertiary">kg</p>
            </div>
          </div>
          <!-- Weight delta -->
          <div v-if="weightDelta !== null" class="mt-3 flex items-center justify-center rounded-lg border border-wc-border px-4 py-3">
            <template v-if="weightDelta === 0">
              <span class="text-sm font-semibold text-emerald-400">En tu peso objetivo</span>
            </template>
            <template v-else>
              <span class="font-data text-lg font-bold tabular-nums" :class="weightDelta > 0 ? 'text-blue-400' : 'text-emerald-400'">
                {{ weightDelta > 0 ? '+' : '' }}{{ weightDelta.toFixed(1) }} kg
              </span>
              <span class="ml-2 text-xs text-wc-text-tertiary">{{ weightDelta > 0 ? 'por ganar' : 'por perder' }}</span>
            </template>
          </div>
        </div>
      </template>
    </div>

    <!-- ===== ONBOARDING TUTORIAL ===== -->
    <Transition name="fade">
      <div
        v-if="showTutorial"
        class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
        @keydown.escape="dismissTutorial"
      >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">

          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-lg tracking-widest text-wc-text">TU NUTRICION</h3>
            <button @click="dismissTutorial" class="text-wc-text-tertiary transition-colors hover:text-wc-text" aria-label="Cerrar">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Step 1 -->
          <div v-show="tutorialStep === 1">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">1</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">Tu plan de macros</p>
                <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">Aqui encontraras tus objetivos diarios de proteina, carbohidratos y grasas. Tu coach los calculo especificamente para tus metas.</p>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div v-show="tutorialStep === 2">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">2</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">Hidratacion</p>
                <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">Registra cada vaso de agua que tomas. La hidratacion adecuada mejora el rendimiento hasta un 20% y acelera la recuperacion muscular.</p>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div v-show="tutorialStep === 3">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">3</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">Sigue el plan con consistencia</p>
                <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">No necesitas ser perfecto — apunta a cumplir tus macros el 80% del tiempo. La consistencia a largo plazo supera la perfeccion a corto plazo.</p>
              </div>
            </div>
          </div>

          <!-- Dots -->
          <div class="mt-4 flex justify-center gap-1.5">
            <div
              v-for="i in 3"
              :key="i"
              class="h-1.5 rounded-full transition-all"
              :class="i === tutorialStep ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-bg-tertiary'"
            ></div>
          </div>

          <!-- Buttons -->
          <div class="mt-5 flex gap-3">
            <button
              v-show="tutorialStep > 1"
              @click="tutorialStep--"
              class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
              type="button"
            >
              Atras
            </button>
            <button
              v-show="tutorialStep < 3"
              @click="tutorialStep++"
              class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
              type="button"
            >
              Siguiente
            </button>
            <button
              v-show="tutorialStep === 3"
              @click="dismissTutorial"
              class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
              type="button"
            >
              Entendido!
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ===== SWAP RECIPE MODAL ===== -->
    <Transition name="fade">
      <div
        v-if="swapModalMeal"
        @click.self="closeSwapModal"
        @keydown.escape="closeSwapModal"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4 backdrop-blur-md"
      >
        <div class="wc-card-hero wc-grain wc-orb-tr relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl">
          <!-- Header -->
          <div class="relative flex items-start justify-between gap-4 px-8 pt-7 pb-5">
            <div class="min-w-0">
              <p class="font-display text-[10px] tracking-[0.28em] text-wc-accent/80">REEMPLAZAR</p>
              <h2 class="mt-1.5 truncate font-display text-3xl tracking-wide text-wc-text">{{ swapModalMeal.name }}</h2>
              <p class="mt-2 font-data text-[11px] tabular-nums tracking-wider text-white/40">
                <span class="text-white/60">{{ swapModalMeal.calories }}</span> KCAL
                <span class="mx-1.5 text-white/20">·</span>
                {{ swapModalMeal.protein }}P
                <span class="mx-1.5 text-white/20">·</span>
                {{ swapModalMeal.carbs }}C
                <span class="mx-1.5 text-white/20">·</span>
                {{ swapModalMeal.fat }}G
              </p>
            </div>
            <button
              @click="closeSwapModal"
              class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/[0.06] bg-white/[0.02] text-white/40 backdrop-blur-sm transition-all duration-300 hover:border-wc-accent/40 hover:text-wc-accent hover:bg-white/[0.04]"
              aria-label="Cerrar"
            >
              <XIcon :size="16" :stroke-width="2" />
            </button>
          </div>

          <!-- Divider -->
          <div class="h-px w-full bg-gradient-to-r from-transparent via-white/[0.08] to-transparent"></div>

          <!-- Search -->
          <div class="px-8 pt-5 pb-2">
            <div class="group/search relative">
              <SearchIcon :size="15" :stroke-width="2" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/30 transition-colors group-focus-within/search:text-wc-accent/70" />
              <input
                v-model="swapSearch"
                type="text"
                placeholder="Buscar receta"
                class="w-full rounded-2xl border border-white/[0.06] bg-white/[0.03] py-3 pl-11 pr-4 text-sm text-wc-text placeholder-white/25 transition-all duration-300 focus:border-wc-accent/40 focus:bg-white/[0.04] focus:outline-none focus:shadow-[0_0_0_4px_rgba(220,38,38,0.08)]"
              />
            </div>
          </div>

          <!-- Recipe grid -->
          <div class="grid flex-1 grid-cols-1 gap-5 overflow-y-auto px-8 py-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="({ recipe: r, score }, idx) in swapCandidates"
              :key="r.id"
              :style="{ animationDelay: (idx * 30) + 'ms' }"
              class="wc-stagger-enter group/card relative flex flex-col overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02] backdrop-blur-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-wc-accent/30 hover:bg-white/[0.035] hover:shadow-[0_8px_32px_-12px_rgba(220,38,38,0.25)]"
              :class="{ 'opacity-40 hover:opacity-60': score === 'bad' }"
            >
              <!-- Emoji panel -->
              <div class="relative flex aspect-[5/3] items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] to-transparent"></div>
                <span class="relative text-5xl opacity-90 transition-transform duration-500 ease-out group-hover/card:scale-110">{{ r.emoji }}</span>
              </div>

              <!-- Body -->
              <div class="flex flex-1 flex-col gap-3 px-4 pb-4">
                <!-- Compatibility micro-label -->
                <div class="flex items-center gap-1.5">
                  <template v-if="score === 'ideal'">
                    <span class="h-1 w-1 rounded-full bg-emerald-400"></span>
                    <span class="font-display text-[9px] tracking-[0.22em] text-emerald-400/80">IDEAL</span>
                  </template>
                  <template v-else-if="score === 'aceptable'">
                    <span class="h-1 w-1 rounded-full bg-amber-400"></span>
                    <span class="font-display text-[9px] tracking-[0.22em] text-amber-400/80">ACEPTABLE</span>
                  </template>
                  <template v-else>
                    <span class="h-1 w-1 rounded-full bg-white/20"></span>
                    <span class="font-display text-[9px] tracking-[0.22em] text-white/30">FUERA DE RANGO</span>
                  </template>
                </div>

                <h4 class="font-display text-base tracking-wide text-wc-text line-clamp-2">{{ r.name }}</h4>

                <p class="font-data text-[11px] tabular-nums tracking-wider text-white/40">
                  <span class="text-white/60">{{ r.macros.cal }}</span> KCAL
                  <span class="mx-1 text-white/15">·</span>{{ r.macros.protein }}P
                  <span class="mx-1 text-white/15">·</span>{{ r.macros.carbs }}C
                  <span class="mx-1 text-white/15">·</span>{{ r.macros.fat }}G
                </p>

                <button
                  @click="applySwap(r)"
                  :disabled="applyingSwap"
                  class="mt-auto flex items-center justify-center gap-2 rounded-xl border border-white/[0.06] bg-transparent px-3 py-2.5 font-display text-[11px] tracking-[0.2em] text-white/60 transition-all duration-300 ease-out hover:border-wc-accent/40 hover:text-wc-accent hover:shadow-[0_0_20px_-4px_rgba(220,38,38,0.4)] disabled:opacity-40"
                >
                  <Replace :size="11" :stroke-width="2" />
                  REEMPLAZAR
                </button>
              </div>
            </div>

            <div v-if="swapCandidates.length === 0" class="col-span-full rounded-2xl border border-white/[0.06] bg-white/[0.02] p-12 text-center">
              <p class="font-display text-xs tracking-[0.2em] text-white/30">SIN RESULTADOS PARA "{{ swapSearch.toUpperCase() }}"</p>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ===== TOAST ===== -->
    <Transition name="toast">
      <div
        v-if="toast"
        class="pointer-events-none fixed inset-x-0 bottom-8 z-[90] flex justify-center px-4"
      >
        <div
          class="pointer-events-auto flex items-center gap-2.5 rounded-full border border-white/[0.08] bg-black/60 px-5 py-2.5 shadow-[0_8px_32px_-8px_rgba(0,0,0,0.6)] backdrop-blur-xl"
        >
          <span class="flex h-4 w-4 items-center justify-center">
            <CheckCircle2 v-if="toast.type === 'success'" :size="14" :stroke-width="2.25" class="text-emerald-400" />
            <AlertTriangle v-else :size="14" :stroke-width="2.25" class="text-wc-accent" />
          </span>
          <span class="text-xs font-medium tracking-wide text-white/80">{{ toast.msg }}</span>
        </div>
      </div>
    </Transition>
  </ClientLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.toast-enter-active, .toast-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(8px); }

.wc-swap-ghost:hover :deep(svg) { filter: drop-shadow(0 0 6px rgba(220,38,38,0.5)); }

.accordion-enter-active { transition: max-height 0.3s ease, opacity 0.3s ease; }
.accordion-leave-active { transition: max-height 0.2s ease, opacity 0.2s ease; }
.accordion-enter-from, .accordion-leave-to { max-height: 0; opacity: 0; overflow: hidden; }
.accordion-enter-to, .accordion-leave-from { max-height: 600px; opacity: 1; }

/* Macro color token classes */
.wc-macro-calories { color: #10B981; }
.wc-macro-calories-bg { background-color: #10B981; }
.wc-macro-carbs { color: #3B82F6; }
.wc-macro-carbs-bg { background-color: #3B82F6; }
.wc-macro-fat { color: #F59E0B; }
.wc-macro-fat-bg { background-color: #F59E0B; }
</style>
