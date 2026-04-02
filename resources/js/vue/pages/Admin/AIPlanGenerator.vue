<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ── Wizard state ──
const currentStep = ref(1);

// ── Step 1: Client selection ──
const clientSearch = ref('');
const searchLoading = ref(false);
const clients = ref([]);
const selectedClientId = ref(null);
const selectedClientData = ref(null);

// ── Step 2: Plan configuration ──
const planType = ref('');
const methodology = ref('');
const durationWeeks = ref(8);
const frequency = ref(4);

// Training params
const experienceLevel = ref('intermedio');
const trainingGoal = ref('hipertrofia');
const equipmentAvailable = ref([]);
const injuries = ref('');

// Nutrition params
const calorieTarget = ref(2200);
const proteinPct = ref(30);
const carbsPct = ref(45);
const fatPct = ref(25);
const dietaryRestrictions = ref('');
const mealsPerDay = ref(4);

// Habits params
const habitFocusAreas = ref([]);

// ── Step 3: AI generation ──
const generating = ref(false);
const planGenerated = ref(false);
const generatedPlan = ref(null);
const generatedPlanJson = ref('');
const showRawJson = ref(false);
const generationError = ref('');

// ── Step 4: Save & assign ──
const templateName = ref('');
const isPublic = ref(false);
const saveMode = ref('template_only');
const saving = ref(false);
const saved = ref(false);
const savedTemplateId = ref(null);
const savedAssignedId = ref(null);
const saveError = ref('');

// ── Static data ──
const STEPS = [
    { num: 1, label: 'Seleccionar', sublabel: 'Cliente' },
    { num: 2, label: 'Configurar', sublabel: 'Plan' },
    { num: 3, label: 'Generar', sublabel: 'IA' },
    { num: 4, label: 'Guardar', sublabel: 'Asignar' },
];

const PLAN_TYPES = [
    {
        key: 'entrenamiento',
        label: 'Entrenamiento',
        desc: 'Plan de ejercicio con periodizacion y progresion',
        color: 'red',
        iconPath: 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
    },
    {
        key: 'nutricion',
        label: 'Nutricion',
        desc: 'Plan alimenticio personalizado con macros y comidas',
        color: 'emerald',
        iconPath: 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C19.155 8.51 20 9.473 20 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265Z',
    },
    {
        key: 'habitos',
        label: 'Habitos',
        desc: 'Plan de habitos saludables progresivos',
        color: 'violet',
        iconPath: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z',
    },
];

const TRAINING_METHODOLOGIES = [
    { key: 'progressive_overload', name: 'Progressive Overload', desc: 'Incremento gradual de carga, volumen o intensidad en cada sesion para estimular adaptacion continua.' },
    { key: 'dup', name: 'DUP (Periodizacion Ondulante Diaria)', desc: 'Varia intensidad y volumen diariamente para maximizar adaptaciones neuromusculares.' },
    { key: 'block_periodization', name: 'Periodizacion por Bloques', desc: 'Divide el entrenamiento en mesociclos enfocados: acumulacion, transmutacion, realizacion.' },
    { key: 'linear_periodization', name: 'Periodizacion Lineal', desc: 'Progresion lineal de volumen alto/intensidad baja hacia volumen bajo/intensidad alta.' },
    { key: 'conjugate', name: 'Conjugate (Westside)', desc: 'Combina esfuerzo maximo y esfuerzo dinamico con rotacion de ejercicios accesorios.' },
    { key: 'gvt', name: 'German Volume Training', desc: '10 series de 10 repeticiones por ejercicio — hipertrofia extrema por volumen acumulado.' },
    { key: 'wendler_531', name: '5/3/1 Wendler', desc: 'Ciclos de 4 semanas basados en porcentajes del 1RM para fuerza progresiva sostenible.' },
    { key: 'starting_strength', name: 'Starting Strength', desc: 'Programa basico de fuerza con movimientos compuestos: sentadilla, press, peso muerto.' },
    { key: 'ppl', name: 'PPL (Push/Pull/Legs)', desc: 'Division en empuje, jalon y piernas — ideal para 3-6 dias por semana.' },
    { key: 'upper_lower', name: 'Upper/Lower Split', desc: 'Alterna tren superior e inferior — equilibrio entre frecuencia y recuperacion.' },
    { key: 'full_body', name: 'Full Body', desc: 'Entrena todo el cuerpo cada sesion — alta frecuencia muscular, ideal para 3 dias.' },
    { key: 'hiit', name: 'HIIT', desc: 'Intervalos de alta intensidad con periodos de recuperacion — quema calorica elevada.' },
    { key: 'crossfit_style', name: 'CrossFit-style', desc: 'WODs variados combinando fuerza, cardio y gimnasia — alto rendimiento funcional.' },
    { key: 'calisthenics', name: 'Calistenia', desc: 'Entrenamiento con peso corporal — progresiones de habilidad y fuerza relativa.' },
    { key: 'powerlifting', name: 'Powerlifting', desc: 'Enfoque en sentadilla, press banca y peso muerto — maximizar el 1RM.' },
    { key: 'hypertrophy_focused', name: 'Hipertrofia Enfocada', desc: 'Volumen moderado-alto con tecnicas de intensidad: drop sets, super series, TUT.' },
    { key: 'strength_endurance', name: 'Fuerza-Resistencia', desc: 'Combina cargas moderadas con volumen alto — ideal para deportes de resistencia.' },
];

const NUTRITION_METHODOLOGIES = [
    { key: 'iifym', name: 'Flexible Dieting (IIFYM)', desc: 'Si cabe en tus macros, lo puedes comer — flexibilidad con precision nutricional.' },
    { key: 'keto', name: 'Keto', desc: 'Muy baja en carbohidratos, alta en grasas — cetosis como fuente principal de energia.' },
    { key: 'reverse_diet', name: 'Reverse Diet', desc: 'Incremento gradual de calorias post-deficit para restaurar el metabolismo.' },
    { key: 'mediterranean', name: 'Mediterranea', desc: 'Basada en alimentos enteros, aceite de oliva, pescado — salud cardiovascular y longevidad.' },
];

const HABIT_AREAS = [
    { key: 'sleep', name: 'Sueno', desc: 'Optimizar calidad y duracion del sueno (7-9h)' },
    { key: 'hydration', name: 'Hidratacion', desc: 'Consumo adecuado de agua segun peso corporal' },
    { key: 'stress', name: 'Manejo del Estres', desc: 'Tecnicas de respiracion, meditacion, journaling' },
    { key: 'mobility', name: 'Movilidad', desc: 'Rutinas de estiramiento y movilidad articular' },
    { key: 'nutrition_habits', name: 'Habitos Alimenticios', desc: 'Comer consciente, prep de comidas, horarios' },
    { key: 'recovery', name: 'Recuperacion', desc: 'Foam rolling, banos de contraste, descanso activo' },
];

const EQUIPMENT_OPTIONS = ['Gym completo', 'Mancuernas', 'Barra', 'Kettlebells', 'Bandas elasticas', 'Maquinas cable', 'TRX', 'Solo peso corporal'];

const TRAINING_GOALS = [
    { value: 'hipertrofia', label: 'Hipertrofia (masa muscular)' },
    { value: 'fuerza', label: 'Fuerza maxima' },
    { value: 'resistencia', label: 'Resistencia muscular' },
    { value: 'perdida_grasa', label: 'Perdida de grasa' },
    { value: 'rendimiento', label: 'Rendimiento deportivo' },
];

const EXPERIENCE_LEVELS = [
    { value: 'principiante', label: 'Principiante (< 1 ano)' },
    { value: 'intermedio', label: 'Intermedio (1-3 anos)' },
    { value: 'avanzado', label: 'Avanzado (3+ anos)' },
];

// ── Computed ──
const macroTotal = computed(() => proteinPct.value + carbsPct.value + fatPct.value);

const canAdvanceStep1 = computed(() => !!selectedClientId.value);
const canAdvanceStep2 = computed(() => {
    if (!planType.value) return false;
    if (planType.value === 'entrenamiento' && !methodology.value) return false;
    if (planType.value === 'nutricion' && !methodology.value) return false;
    if (planType.value === 'habitos' && habitFocusAreas.value.length === 0) return false;
    return true;
});
const canAdvanceStep3 = computed(() => planGenerated.value);

const methodologyLabel = computed(() => {
    if (planType.value === 'entrenamiento') {
        return TRAINING_METHODOLOGIES.find(m => m.key === methodology.value)?.name ?? methodology.value;
    }
    if (planType.value === 'nutricion') {
        return NUTRITION_METHODOLOGIES.find(m => m.key === methodology.value)?.name ?? methodology.value;
    }
    return habitFocusAreas.value.map(a => HABIT_AREAS.find(h => h.key === a)?.name ?? a).join(', ');
});

// ── Debounced client search ──
let debounceTimer = null;

watch(clientSearch, () => {
    clearTimeout(debounceTimer);
    if (clientSearch.value.length < 2) {
        clients.value = [];
        return;
    }
    debounceTimer = setTimeout(searchClients, 300);
});

async function searchClients() {
    searchLoading.value = true;
    try {
        const response = await api.get('/api/v/admin/clients', {
            params: { search: clientSearch.value, per_page: 20, status: 'activo' },
        });
        clients.value = response.data.clients ?? response.data.data ?? [];
    } catch {
        clients.value = [];
    } finally {
        searchLoading.value = false;
    }
}

// ── Step 1 actions ──
function selectClient(client) {
    selectedClientId.value = client.id;
    const dias = client.profile?.dias_disponibles ?? client.dias_disponibles ?? [];
    selectedClientData.value = {
        id: client.id,
        name: client.name,
        email: client.email,
        plan: client.plan ?? '-',
        status: client.status ?? '-',
        age: client.age ?? client.profile?.age ?? null,
        city: client.city ?? client.profile?.ciudad ?? '-',
        peso: client.profile?.peso ?? client.peso ?? null,
        altura: client.profile?.altura ?? client.altura ?? null,
        objetivo: client.profile?.objetivo ?? client.objetivo ?? '-',
        nivel: client.profile?.nivel ?? client.nivel ?? '-',
        lugar_entreno: client.profile?.lugar_entreno ?? client.lugar_entreno ?? '-',
        dias_disponibles: Array.isArray(dias) ? dias : [],
        restricciones: client.profile?.restricciones ?? client.restricciones ?? '',
        fecha_inicio: client.fecha_inicio ?? '-',
    };
    if (Array.isArray(dias) && dias.length > 0) {
        frequency.value = dias.length;
    }
}

function clearClient() {
    selectedClientId.value = null;
    selectedClientData.value = null;
}

// ── Step 2 actions ──
function selectPlanType(type) {
    planType.value = type;
    methodology.value = '';
    habitFocusAreas.value = [];
}

function toggleEquipment(item) {
    const idx = equipmentAvailable.value.indexOf(item);
    if (idx >= 0) {
        equipmentAvailable.value.splice(idx, 1);
    } else {
        equipmentAvailable.value.push(item);
    }
}

function toggleHabitArea(key) {
    const idx = habitFocusAreas.value.indexOf(key);
    if (idx >= 0) {
        habitFocusAreas.value.splice(idx, 1);
    } else {
        habitFocusAreas.value.push(key);
    }
}

// ── Step navigation ──
function nextStep() {
    if (currentStep.value === 1 && !canAdvanceStep1.value) return;
    if (currentStep.value === 2 && !canAdvanceStep2.value) return;
    if (currentStep.value === 3 && !canAdvanceStep3.value) return;
    if (currentStep.value < 4) {
        currentStep.value++;
    }
    if (currentStep.value === 4 && !templateName.value) {
        templateName.value = buildDefaultTemplateName();
    }
}

function prevStep() {
    if (currentStep.value > 1) currentStep.value--;
}

function goToStep(num) {
    if (num < currentStep.value) currentStep.value = num;
}

function buildDefaultTemplateName() {
    const client = selectedClientData.value?.name ?? 'Cliente';
    const type = planType.value.charAt(0).toUpperCase() + planType.value.slice(1);
    const method = methodologyLabel.value ? ` — ${methodologyLabel.value}` : '';
    return `${client} — Plan ${type}${method} ${durationWeeks.value}s`;
}

// ── Step 3: Generate ──
async function generatePlan() {
    generating.value = true;
    generationError.value = '';
    planGenerated.value = false;
    generatedPlan.value = null;
    generatedPlanJson.value = '';

    const payload = {
        plan_type: planType.value,
        methodology: methodology.value || null,
        duration_weeks: durationWeeks.value,
        frequency: frequency.value,
        experience_level: planType.value === 'entrenamiento' ? experienceLevel.value : null,
        training_goal: planType.value === 'entrenamiento' ? trainingGoal.value : null,
        injuries: planType.value === 'entrenamiento' ? injuries.value : null,
        calorie_target: planType.value === 'nutricion' ? calorieTarget.value : null,
        protein_pct: planType.value === 'nutricion' ? proteinPct.value : null,
        carbs_pct: planType.value === 'nutricion' ? carbsPct.value : null,
        fat_pct: planType.value === 'nutricion' ? fatPct.value : null,
        meals_per_day: planType.value === 'nutricion' ? mealsPerDay.value : null,
        dietary_restrictions: planType.value === 'nutricion' ? dietaryRestrictions.value : null,
        habit_focus_areas: planType.value === 'habitos' ? habitFocusAreas.value : null,
        target_client_id: selectedClientId.value ?? null,
    };

    try {
        const response = await api.post('/api/v/admin/ai-generator', payload);
        generatedPlan.value = response.data.plan ?? response.data;
        generatedPlanJson.value = response.data.planJson ?? JSON.stringify(generatedPlan.value, null, 2);
        planGenerated.value = true;
    } catch (err) {
        generationError.value = err.response?.data?.message ?? 'Error al generar el plan con IA.';
    } finally {
        generating.value = false;
    }
}

function updateGeneratedJson() {
    try {
        const decoded = JSON.parse(generatedPlanJson.value);
        generatedPlan.value = decoded;
        generationError.value = '';
    } catch (e) {
        generationError.value = 'JSON invalido: ' + e.message;
    }
}

// ── Step 4: Save ──
async function savePlan() {
    if (!templateName.value.trim()) {
        saveError.value = 'El nombre de la plantilla es obligatorio.';
        return;
    }
    saving.value = true;
    saveError.value = '';

    try {
        const response = await api.post('/api/v/admin/ai-generator', {
            plan_type: planType.value,
            methodology: methodology.value || null,
            duration_weeks: durationWeeks.value,
            frequency: frequency.value,
            experience_level: planType.value === 'entrenamiento' ? experienceLevel.value : null,
            training_goal: planType.value === 'entrenamiento' ? trainingGoal.value : null,
            injuries: planType.value === 'entrenamiento' ? injuries.value : null,
            calorie_target: planType.value === 'nutricion' ? calorieTarget.value : null,
            protein_pct: planType.value === 'nutricion' ? proteinPct.value : null,
            carbs_pct: planType.value === 'nutricion' ? carbsPct.value : null,
            fat_pct: planType.value === 'nutricion' ? fatPct.value : null,
            meals_per_day: planType.value === 'nutricion' ? mealsPerDay.value : null,
            dietary_restrictions: planType.value === 'nutricion' ? dietaryRestrictions.value : null,
            habit_focus_areas: planType.value === 'habitos' ? habitFocusAreas.value : null,
            target_client_id: selectedClientId.value ?? null,
            template_name: templateName.value,
            is_public: isPublic.value,
            save_mode: saveMode.value,
        });
        savedTemplateId.value = response.data.savedTemplateId ?? null;
        savedAssignedId.value = response.data.savedAssignedId ?? null;
        saved.value = true;
    } catch (err) {
        if (err.response?.status === 422) {
            const errs = err.response.data.errors ?? {};
            saveError.value = Object.values(errs).flat().join(' ');
        } else {
            saveError.value = err.response?.data?.message ?? 'Error al guardar el plan.';
        }
    } finally {
        saving.value = false;
    }
}

function startNew() {
    currentStep.value = 1;
    clientSearch.value = '';
    clients.value = [];
    selectedClientId.value = null;
    selectedClientData.value = null;
    planType.value = '';
    methodology.value = '';
    durationWeeks.value = 8;
    frequency.value = 4;
    experienceLevel.value = 'intermedio';
    trainingGoal.value = 'hipertrofia';
    equipmentAvailable.value = [];
    injuries.value = '';
    calorieTarget.value = 2200;
    proteinPct.value = 30;
    carbsPct.value = 45;
    fatPct.value = 25;
    dietaryRestrictions.value = '';
    mealsPerDay.value = 4;
    habitFocusAreas.value = [];
    generating.value = false;
    planGenerated.value = false;
    generatedPlan.value = null;
    generatedPlanJson.value = '';
    showRawJson.value = false;
    generationError.value = '';
    templateName.value = '';
    isPublic.value = false;
    saveMode.value = 'template_only';
    saving.value = false;
    saved.value = false;
    savedTemplateId.value = null;
    savedAssignedId.value = null;
    saveError.value = '';
}

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wider text-wc-text">GENERADOR AI DE PLANES</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Crea planes personalizados con inteligencia artificial en 4 pasos</p>
        </div>
        <button v-if="saved" @click="startNew"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nuevo Plan
        </button>
      </div>

      <!-- Step Indicator -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 sm:p-6">
        <div class="flex items-center justify-between">
          <template v-for="step in STEPS" :key="step.num">
            <div class="flex items-center" :class="step.num < 4 ? 'flex-1' : ''">
              <button @click="goToStep(step.num)"
                class="flex flex-col items-center gap-1"
                :class="step.num < currentStep ? 'cursor-pointer' : 'cursor-default'">
                <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300"
                  :class="step.num === currentStep
                    ? 'bg-red-600 text-white ring-4 ring-red-600/20'
                    : (step.num < currentStep ? 'bg-emerald-500 text-white' : 'bg-wc-bg-tertiary text-wc-text-tertiary')">
                  <svg v-if="step.num < currentStep" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                  <span v-else>{{ step.num }}</span>
                </div>
                <span class="hidden text-xs font-semibold sm:block"
                  :class="step.num === currentStep ? 'text-red-500' : (step.num < currentStep ? 'text-emerald-500' : 'text-wc-text-tertiary')">
                  {{ step.label }}
                </span>
                <span class="hidden text-[10px] text-wc-text-tertiary sm:block">{{ step.sublabel }}</span>
              </button>
              <!-- Connector line -->
              <div v-if="step.num < 4" class="mx-2 h-0.5 flex-1 rounded transition-colors duration-300"
                :class="step.num < currentStep ? 'bg-emerald-500' : 'bg-wc-border'"></div>
            </div>
          </template>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════ -->
      <!-- Step 1: Client Selection                      -->
      <!-- ══════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="currentStep === 1" class="space-y-6" key="step1">

          <!-- Search box -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Buscar Cliente</h2>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input v-model="clientSearch" type="text"
                placeholder="Buscar por nombre o email (min. 2 caracteres)..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg py-3 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500" />
            </div>

            <!-- Loading -->
            <div v-if="searchLoading" class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="n in 3" :key="n" class="animate-pulse rounded-lg border border-wc-border bg-wc-bg-tertiary h-20"></div>
            </div>

            <!-- Results -->
            <div v-else-if="clientSearch.length >= 2 && clients.length > 0" class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <button v-for="client in clients" :key="client.id" @click="selectClient(client)"
                class="flex items-center gap-3 rounded-lg border p-3 text-left transition-all"
                :class="selectedClientId === client.id
                  ? 'border-red-500 bg-red-500/10'
                  : 'border-wc-border bg-wc-bg hover:border-red-500/50 hover:bg-wc-bg-tertiary'">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600/20 text-sm font-bold text-red-400">
                  {{ client.name?.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-medium text-wc-text">{{ client.name }}</p>
                  <p class="truncate text-xs text-wc-text-tertiary">{{ client.email }}</p>
                  <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                      :class="client.status === 'activo' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-yellow-500/10 text-yellow-400'">
                      {{ client.status ? client.status.charAt(0).toUpperCase() + client.status.slice(1) : '-' }}
                    </span>
                    <span class="text-[10px] text-wc-text-tertiary">{{ client.plan ? client.plan.charAt(0).toUpperCase() + client.plan.slice(1) : '-' }}</span>
                  </div>
                </div>
              </button>
            </div>

            <p v-else-if="clientSearch.length >= 2 && !searchLoading" class="mt-4 text-center text-sm text-wc-text-tertiary">
              No se encontraron clientes activos
            </p>
          </div>

          <!-- Selected client profile card -->
          <Transition name="fade">
            <div v-if="selectedClientData" class="rounded-xl border border-red-500/30 bg-wc-bg-secondary p-6">
              <div class="flex items-start justify-between">
                <h2 class="text-lg font-semibold text-wc-text">Cliente Seleccionado</h2>
                <button @click="clearClient" class="text-xs text-red-400 hover:text-red-300">Cambiar</button>
              </div>
              <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.name }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</p>
                  <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ selectedClientData.plan }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Edad</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.age ?? '-' }} anos</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Status</p>
                  <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                    :class="selectedClientData.status === 'activo' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-yellow-500/10 text-yellow-400'">
                    {{ selectedClientData.status ? selectedClientData.status.charAt(0).toUpperCase() + selectedClientData.status.slice(1) : '-' }}
                  </span>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.peso ? selectedClientData.peso + ' kg' : '-' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Altura</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.altura ? selectedClientData.altura + ' cm' : '-' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.objetivo }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                  <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ selectedClientData.nivel }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lugar Entreno</p>
                  <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ selectedClientData.lugar_entreno }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Dias Disponibles</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">
                    {{ selectedClientData.dias_disponibles.length > 0 ? selectedClientData.dias_disponibles.length + ' dias' : '-' }}
                  </p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.city }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Inicio</p>
                  <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData.fecha_inicio }}</p>
                </div>
              </div>
              <div v-if="selectedClientData.restricciones" class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Restricciones</p>
                <p class="mt-1 text-sm text-yellow-400">{{ selectedClientData.restricciones }}</p>
              </div>
            </div>
          </Transition>

        </div>
      </Transition>

      <!-- ══════════════════════════════════════════════ -->
      <!-- Step 2: Plan Configuration                    -->
      <!-- ══════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="currentStep === 2" class="space-y-6" key="step2">

          <!-- Plan Type Selection -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Tipo de Plan</h2>
            <div class="grid gap-4 sm:grid-cols-3">
              <button v-for="pt in PLAN_TYPES" :key="pt.key" @click="selectPlanType(pt.key)"
                class="group flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all"
                :class="planType === pt.key
                  ? (pt.color === 'red' ? 'border-red-500 bg-red-500/10' : pt.color === 'emerald' ? 'border-emerald-500 bg-emerald-500/10' : 'border-violet-500 bg-violet-500/10')
                  : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary'">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl transition-colors"
                  :class="planType === pt.key
                    ? (pt.color === 'red' ? 'bg-red-500/20 text-red-400' : pt.color === 'emerald' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-violet-500/20 text-violet-400')
                    : 'bg-wc-bg-tertiary text-wc-text-tertiary group-hover:text-wc-text'">
                  <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="pt.iconPath" />
                  </svg>
                </div>
                <div>
                  <p class="font-semibold text-wc-text">{{ pt.label }}</p>
                  <p class="mt-1 text-xs text-wc-text-tertiary">{{ pt.desc }}</p>
                </div>
              </button>
            </div>
          </div>

          <!-- Training Methodology -->
          <Transition name="fade">
            <div v-if="planType === 'entrenamiento'" class="space-y-6">
              <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
                <h2 class="mb-4 text-lg font-semibold text-wc-text">Metodologia de Entrenamiento</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  <button v-for="m in TRAINING_METHODOLOGIES" :key="m.key" @click="methodology = m.key"
                    class="flex items-start gap-3 rounded-lg border p-3 text-left transition-all"
                    :class="methodology === m.key ? 'border-red-500 bg-red-500/10' : 'border-wc-border bg-wc-bg hover:border-red-500/50'">
                    <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full mt-2"
                      :class="methodology === m.key ? 'bg-red-400' : 'bg-wc-text-tertiary'"></div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-wc-text">{{ m.name }}</p>
                      <p class="mt-0.5 text-xs leading-relaxed text-wc-text-tertiary">{{ m.desc }}</p>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Training Parameters -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
                <h2 class="mb-4 text-lg font-semibold text-wc-text">Parametros de Entrenamiento</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Meta Principal</label>
                    <select v-model="trainingGoal" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                      <option v-for="g in TRAINING_GOALS" :key="g.value" :value="g.value">{{ g.label }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel de Experiencia</label>
                    <select v-model="experienceLevel" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                      <option v-for="l in EXPERIENCE_LEVELS" :key="l.value" :value="l.value">{{ l.label }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lesiones / Limitaciones</label>
                    <input v-model="injuries" type="text" placeholder="Ej: dolor lumbar, tendinitis hombro..."
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500" />
                  </div>
                </div>
                <!-- Equipment -->
                <div class="mt-4">
                  <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Equipamiento Disponible</label>
                  <div class="flex flex-wrap gap-2">
                    <button v-for="eq in EQUIPMENT_OPTIONS" :key="eq" @click="toggleEquipment(eq)"
                      class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                      :class="equipmentAvailable.includes(eq)
                        ? 'border-red-500 bg-red-500/10 text-red-400'
                        : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50'">
                      {{ eq }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </Transition>

          <!-- Nutrition Methodology -->
          <Transition name="fade">
            <div v-if="planType === 'nutricion'" class="space-y-6">
              <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
                <h2 class="mb-4 text-lg font-semibold text-wc-text">Enfoque Nutricional</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                  <button v-for="m in NUTRITION_METHODOLOGIES" :key="m.key" @click="methodology = m.key"
                    class="flex flex-col items-center gap-3 rounded-lg border p-4 text-center transition-all"
                    :class="methodology === m.key ? 'border-emerald-500 bg-emerald-500/10' : 'border-wc-border bg-wc-bg hover:border-emerald-500/50'">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                      :class="methodology === m.key ? 'bg-emerald-500/20 text-emerald-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary'">
                      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">{{ m.name }}</p>
                      <p class="mt-1 text-xs text-wc-text-tertiary">{{ m.desc }}</p>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Nutrition Parameters -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
                <h2 class="mb-4 text-lg font-semibold text-wc-text">Parametros Nutricionales</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Calorias Objetivo</label>
                    <input v-model.number="calorieTarget" type="number" min="1200" max="5000" step="100"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Comidas al Dia</label>
                    <select v-model.number="mealsPerDay" class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                      <option v-for="i in [3,4,5,6]" :key="i" :value="i">{{ i }} comidas</option>
                    </select>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Restricciones Dieteticas</label>
                    <input v-model="dietaryRestrictions" type="text" placeholder="Ej: sin lacteos, sin gluten..."
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                  </div>
                </div>
                <!-- Macro Split -->
                <div class="mt-4">
                  <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Distribucion de Macros</label>
                  <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                      <label class="mb-1 block text-xs text-wc-text-secondary">Proteina: {{ proteinPct }}%</label>
                      <input v-model.number="proteinPct" type="range" min="15" max="50" step="5" class="w-full accent-red-500" />
                    </div>
                    <div>
                      <label class="mb-1 block text-xs text-wc-text-secondary">Carbohidratos: {{ carbsPct }}%</label>
                      <input v-model.number="carbsPct" type="range" min="10" max="60" step="5" class="w-full accent-amber-500" />
                    </div>
                    <div>
                      <label class="mb-1 block text-xs text-wc-text-secondary">Grasas: {{ fatPct }}%</label>
                      <input v-model.number="fatPct" type="range" min="15" max="60" step="5" class="w-full accent-blue-500" />
                    </div>
                  </div>
                  <p v-if="macroTotal !== 100" class="mt-2 text-xs text-yellow-400">Total: {{ macroTotal }}% — debe sumar 100%</p>
                  <p v-else class="mt-2 text-xs text-emerald-400">Total: 100%</p>
                </div>
              </div>
            </div>
          </Transition>

          <!-- Habits Focus Areas -->
          <Transition name="fade">
            <div v-if="planType === 'habitos'" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
              <h2 class="mb-2 text-lg font-semibold text-wc-text">Areas de Enfoque</h2>
              <p class="mb-4 text-xs text-wc-text-tertiary">Selecciona una o mas areas para el plan de habitos</p>
              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <button v-for="area in HABIT_AREAS" :key="area.key" @click="toggleHabitArea(area.key)"
                  class="flex items-start gap-3 rounded-lg border p-4 text-left transition-all"
                  :class="habitFocusAreas.includes(area.key)
                    ? 'border-violet-500 bg-violet-500/10'
                    : 'border-wc-border bg-wc-bg hover:border-violet-500/50'">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                    :class="habitFocusAreas.includes(area.key) ? 'bg-violet-500/20 text-violet-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary'">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-wc-text">{{ area.name }}</p>
                    <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ area.desc }}</p>
                  </div>
                </button>
              </div>
            </div>
          </Transition>

          <!-- Duration & Frequency (all plan types) -->
          <Transition name="fade">
            <div v-if="planType" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
              <h2 class="mb-4 text-lg font-semibold text-wc-text">Duracion y Frecuencia</h2>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Duracion (semanas)</label>
                  <div class="flex gap-2">
                    <button v-for="w in [4, 8, 12, 16]" :key="w" @click="durationWeeks = w"
                      class="flex-1 rounded-lg border py-2.5 text-center text-sm font-medium transition-colors"
                      :class="durationWeeks === w
                        ? 'border-red-500 bg-red-500/10 text-red-400'
                        : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50'">
                      {{ w }}s
                    </button>
                  </div>
                </div>
                <div>
                  <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Frecuencia (dias/semana)</label>
                  <div class="flex gap-2">
                    <button v-for="d in [2, 3, 4, 5, 6]" :key="d" @click="frequency = d"
                      class="flex-1 rounded-lg border py-2.5 text-center text-sm font-medium transition-colors"
                      :class="frequency === d
                        ? 'border-red-500 bg-red-500/10 text-red-400'
                        : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50'">
                      {{ d }}d
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </Transition>

        </div>
      </Transition>

      <!-- ══════════════════════════════════════════════ -->
      <!-- Step 3: AI Generation                         -->
      <!-- ══════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="currentStep === 3" class="space-y-6" key="step3">

          <!-- Configuration Summary -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Resumen de Configuracion</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</p>
                <p class="mt-1 text-sm font-medium text-wc-text">{{ selectedClientData?.name ?? '-' }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de Plan</p>
                <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ planType }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">
                  {{ planType === 'habitos' ? 'Areas de Enfoque' : 'Metodologia' }}
                </p>
                <p class="mt-1 text-sm font-medium text-wc-text">{{ methodologyLabel }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Duracion</p>
                <p class="mt-1 text-sm font-medium font-data text-wc-text">{{ durationWeeks }} sem / {{ frequency }} dias</p>
              </div>
            </div>

            <!-- Training extra summary -->
            <div v-if="planType === 'entrenamiento'" class="mt-3 grid gap-3 sm:grid-cols-3">
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Meta</p>
                <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ trainingGoal.replace('_', ' ') }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ experienceLevel }}</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Equipamiento</p>
                <p class="mt-1 text-sm font-medium text-wc-text">{{ equipmentAvailable.length > 0 ? equipmentAvailable.join(', ') : 'Gym completo' }}</p>
              </div>
            </div>

            <!-- Nutrition extra summary -->
            <div v-if="planType === 'nutricion'" class="mt-3 grid gap-3 sm:grid-cols-3">
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Calorias</p>
                <p class="mt-1 font-data text-sm font-medium text-wc-text">{{ calorieTarget.toLocaleString() }} kcal</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Macros</p>
                <p class="mt-1 font-data text-sm font-medium text-wc-text">P{{ proteinPct }}% / C{{ carbsPct }}% / G{{ fatPct }}%</p>
              </div>
              <div class="rounded-lg bg-wc-bg p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Comidas</p>
                <p class="mt-1 font-data text-sm font-medium text-wc-text">{{ mealsPerDay }} al dia</p>
              </div>
            </div>
          </div>

          <!-- Generate Button (not yet generated) -->
          <div v-if="!planGenerated" class="flex justify-center">
            <button @click="generatePlan" :disabled="generating"
              class="group relative inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 px-8 py-4 text-base font-bold text-white shadow-lg shadow-red-600/25 transition-all hover:from-red-500 hover:to-red-600 hover:shadow-xl hover:shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
              <svg v-if="!generating" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z" />
              </svg>
              <svg v-else class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <span>{{ generating ? 'Generando plan...' : 'Generar Plan con IA' }}</span>
            </button>
          </div>

          <!-- Generating skeleton / progress feedback -->
          <Transition name="fade">
            <div v-if="generating" class="rounded-xl border border-wc-border bg-wc-bg-secondary p-8 text-center">
              <svg class="mx-auto h-12 w-12 animate-spin text-red-600/40" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <p class="mt-4 text-sm text-wc-text-secondary">Generando plan personalizado con IA...</p>
              <p class="mt-1 text-xs text-wc-text-tertiary">Esto puede tomar 15-30 segundos</p>
            </div>
          </Transition>

          <!-- Generation Error -->
          <Transition name="fade">
            <div v-if="generationError" class="rounded-lg border border-red-500/30 bg-red-500/10 p-4">
              <p class="text-sm text-red-400">{{ generationError }}</p>
            </div>
          </Transition>

          <!-- Generated Plan Preview -->
          <Transition name="fade">
            <div v-if="planGenerated && generatedPlan" class="rounded-xl border border-emerald-500/30 bg-wc-bg-secondary p-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-lg font-semibold text-wc-text">Plan Generado</h2>
                    <p class="text-xs text-wc-text-tertiary">
                      {{ generatedPlan.generated_by === 'template' ? 'Generado con plantilla estructurada (API key no configurada)' : 'Generado con Claude AI' }}
                    </p>
                  </div>
                </div>
                <button @click="showRawJson = !showRawJson"
                  class="rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors">
                  {{ showRawJson ? 'Vista Formato' : 'Ver JSON' }}
                </button>
              </div>

              <!-- Raw JSON editor -->
              <div v-if="showRawJson" class="mt-4">
                <textarea v-model="generatedPlanJson" @change="updateGeneratedJson" rows="20" spellcheck="false"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg p-4 font-mono text-xs text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                <p v-if="generationError" class="mt-1 text-xs text-red-400">{{ generationError }}</p>
              </div>

              <!-- Formatted Preview -->
              <div v-else class="mt-4 space-y-4">

                <!-- ENTRENAMIENTO preview -->
                <template v-if="planType === 'entrenamiento' && generatedPlan.weeks">
                  <div v-for="(week, wIdx) in generatedPlan.weeks" :key="wIdx" class="rounded-lg border border-wc-border bg-wc-bg p-4">
                    <h3 class="text-sm font-bold text-red-400">
                      Semana {{ week.week ?? (wIdx + 1) }}
                      <span v-if="week.focus" class="ml-2 font-normal text-wc-text-tertiary">— {{ week.focus }}</span>
                    </h3>
                    <div v-if="week.sessions" class="mt-3 space-y-3">
                      <div v-for="(session, sIdx) in week.sessions" :key="sIdx" class="rounded-lg bg-wc-bg-secondary p-3">
                        <div class="flex items-center gap-2">
                          <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/20 text-[10px] font-bold text-red-400">
                            D{{ session.day ?? (sIdx + 1) }}
                          </span>
                          <span class="text-sm font-semibold text-wc-text">{{ session.name ?? 'Sesion' }}</span>
                          <span v-if="session.muscle_groups" class="text-xs text-wc-text-tertiary">
                            {{ Array.isArray(session.muscle_groups) ? session.muscle_groups.join(', ') : session.muscle_groups }}
                          </span>
                        </div>
                        <div v-if="session.exercises" class="mt-2 overflow-x-auto">
                          <table class="w-full text-xs">
                            <thead>
                              <tr class="text-wc-text-tertiary">
                                <th class="pb-1 text-left font-semibold">Ejercicio</th>
                                <th class="pb-1 text-center font-semibold">Series</th>
                                <th class="pb-1 text-center font-semibold">Reps</th>
                                <th class="pb-1 text-center font-semibold">Descanso</th>
                                <th class="pb-1 text-left font-semibold">Notas</th>
                              </tr>
                            </thead>
                            <tbody class="text-wc-text">
                              <template v-for="(ex, eIdx) in session.exercises" :key="eIdx">
                                <tr v-if="ex && typeof ex === 'object'" class="border-t border-wc-border/50">
                                  <td class="py-1.5 font-medium">{{ ex.name ?? '-' }}</td>
                                  <td class="py-1.5 text-center font-data">{{ ex.sets ?? '-' }}</td>
                                  <td class="py-1.5 text-center font-data">{{ ex.reps ?? '-' }}</td>
                                  <td class="py-1.5 text-center font-data">{{ ex.rest ?? '-' }}</td>
                                  <td class="py-1.5 text-wc-text-tertiary">{{ ex.notes ?? '' }}</td>
                                </tr>
                              </template>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="generatedPlan.progression_notes" class="rounded-lg bg-wc-bg p-3">
                    <p class="text-xs font-semibold text-wc-text-tertiary">Progresion</p>
                    <p class="mt-1 text-sm text-wc-text">{{ generatedPlan.progression_notes }}</p>
                  </div>
                </template>

                <!-- NUTRICION preview -->
                <template v-else-if="planType === 'nutricion'">
                  <div v-if="generatedPlan.macros" class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-wc-bg p-3 text-center">
                      <p class="text-[10px] font-semibold uppercase text-wc-text-tertiary">Calorias</p>
                      <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ (generatedPlan.calories ?? 0).toLocaleString() }}</p>
                      <p class="text-[10px] text-wc-text-tertiary">kcal/dia</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3 text-center">
                      <p class="text-[10px] font-semibold uppercase text-red-400">Proteina</p>
                      <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ generatedPlan.macros.protein_g ?? 0 }}g</p>
                      <p class="text-[10px] text-wc-text-tertiary">{{ generatedPlan.macros.protein_pct ?? 0 }}%</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3 text-center">
                      <p class="text-[10px] font-semibold uppercase text-amber-400">Carbos</p>
                      <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ generatedPlan.macros.carbs_g ?? 0 }}g</p>
                      <p class="text-[10px] text-wc-text-tertiary">{{ generatedPlan.macros.carbs_pct ?? 0 }}%</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3 text-center">
                      <p class="text-[10px] font-semibold uppercase text-blue-400">Grasas</p>
                      <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ generatedPlan.macros.fat_g ?? 0 }}g</p>
                      <p class="text-[10px] text-wc-text-tertiary">{{ generatedPlan.macros.fat_pct ?? 0 }}%</p>
                    </div>
                  </div>
                  <div v-if="generatedPlan.meal_plan">
                    <div v-for="(meal, mIdx) in generatedPlan.meal_plan" :key="mIdx" class="rounded-lg border border-wc-border bg-wc-bg p-4 mb-3">
                      <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-emerald-400">{{ meal.name ?? 'Comida' }}</h3>
                        <span class="text-xs text-wc-text-tertiary">{{ meal.time ?? '' }} &middot; ~{{ meal.calories ?? 0 }} kcal</span>
                      </div>
                      <div v-if="meal.foods" class="mt-2 space-y-1">
                        <div v-for="(food, fIdx) in meal.foods" :key="fIdx" class="flex items-center justify-between text-xs">
                          <span class="text-wc-text">{{ food.name ?? '' }} <span class="text-wc-text-tertiary">({{ food.quantity ?? '' }})</span></span>
                          <span class="font-data text-wc-text-tertiary">P{{ food.protein ?? 0 }} C{{ food.carbs ?? 0 }} G{{ food.fat ?? 0 }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- HABITOS preview -->
                <template v-else-if="planType === 'habitos'">
                  <div v-if="generatedPlan.habits">
                    <div v-for="(habit, hIdx) in generatedPlan.habits" :key="hIdx" class="rounded-lg border border-wc-border bg-wc-bg p-4 mb-3">
                      <div class="flex items-center gap-2">
                        <span class="rounded-full bg-violet-500/10 px-2 py-0.5 text-[10px] font-semibold text-violet-400">{{ habit.area ?? '' }}</span>
                        <span class="text-xs text-wc-text-tertiary">{{ habit.frequency ?? '' }}</span>
                      </div>
                      <p class="mt-2 text-sm font-medium text-wc-text">{{ habit.habit ?? '' }}</p>
                      <div class="mt-2 flex items-center gap-4 text-xs text-wc-text-tertiary">
                        <span>Metrica: {{ habit.metric ?? '' }}</span>
                        <span>Meta: {{ habit.target ?? '' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-if="generatedPlan.daily_routine" class="rounded-lg border border-wc-border bg-wc-bg p-4">
                    <h3 class="text-sm font-bold text-violet-400">Rutina Diaria</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                      <div v-for="(label, key) in { morning: 'Manana', afternoon: 'Tarde', evening: 'Noche' }" :key="key">
                        <template v-if="generatedPlan.daily_routine[key]">
                          <p class="text-xs font-semibold text-wc-text-secondary">{{ label }}</p>
                          <ul class="mt-1 space-y-1">
                            <li v-for="(item, iIdx) in generatedPlan.daily_routine[key]" :key="iIdx"
                              class="flex items-start gap-1.5 text-xs text-wc-text">
                              <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-500"></span>
                              {{ item }}
                            </li>
                          </ul>
                        </template>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Generic summary fallback -->
                <div v-if="generatedPlan.summary" class="rounded-lg bg-wc-bg p-4">
                  <p class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ generatedPlan.summary }}</p>
                </div>

              </div>

              <!-- Regenerate -->
              <div class="mt-6 flex justify-center">
                <button @click="generatePlan" :disabled="generating"
                  class="inline-flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors disabled:opacity-50">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                  </svg>
                  Regenerar Plan
                </button>
              </div>
            </div>
          </Transition>

        </div>
      </Transition>

      <!-- ══════════════════════════════════════════════ -->
      <!-- Step 4: Save & Assign                         -->
      <!-- ══════════════════════════════════════════════ -->
      <Transition name="fade" mode="out-in">
        <div v-if="currentStep === 4" class="space-y-6" key="step4">

          <!-- Not yet saved -->
          <template v-if="!saved">
            <!-- Template name + visibility -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
              <h2 class="mb-4 text-lg font-semibold text-wc-text">Guardar Plan</h2>
              <div class="space-y-4">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre de la Plantilla</label>
                  <input v-model="templateName" type="text" maxlength="160"
                    placeholder="Nombre descriptivo para esta plantilla..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500" />
                  <p v-if="saveError && !templateName" class="mt-1 text-xs text-red-400">{{ saveError }}</p>
                </div>
                <div class="flex items-center gap-3">
                  <button @click="isPublic = !isPublic" role="switch" :aria-checked="isPublic"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                    :class="isPublic ? 'bg-red-600' : 'bg-wc-bg-tertiary'">
                    <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
                      :class="isPublic ? 'translate-x-5' : 'translate-x-0'"></span>
                  </button>
                  <span class="text-sm text-wc-text">Plantilla publica (visible para otros coaches)</span>
                </div>
              </div>
            </div>

            <!-- Save Mode -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
              <h2 class="mb-4 text-lg font-semibold text-wc-text">Modo de Guardado</h2>
              <div class="grid gap-4 sm:grid-cols-2">
                <button @click="saveMode = 'template_only'"
                  class="flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all"
                  :class="saveMode === 'template_only' ? 'border-red-500 bg-red-500/10' : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary'">
                  <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                    :class="saveMode === 'template_only' ? 'bg-red-500/20 text-red-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary'">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-semibold text-wc-text">Solo Plantilla</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Guardar como plantilla reutilizable sin asignar al cliente</p>
                  </div>
                </button>

                <button @click="saveMode = 'template_and_assign'"
                  class="flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all"
                  :class="saveMode === 'template_and_assign' ? 'border-emerald-500 bg-emerald-500/10' : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary'">
                  <div class="flex h-12 w-12 items-center justify-center rounded-xl"
                    :class="saveMode === 'template_and_assign' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary'">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                  </div>
                  <div>
                    <p class="font-semibold text-wc-text">Plantilla + Asignar</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Guardar y asignar directamente a {{ selectedClientData?.name ?? 'el cliente' }}</p>
                  </div>
                </button>
              </div>
            </div>

            <!-- Save error -->
            <Transition name="fade">
              <div v-if="saveError" class="rounded-lg border border-red-500/30 bg-red-500/10 p-4">
                <p class="text-sm text-red-400">{{ saveError }}</p>
              </div>
            </Transition>

            <!-- Save Button -->
            <div class="flex justify-center">
              <button @click="savePlan" :disabled="saving"
                class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-8 py-4 text-base font-bold text-white shadow-lg shadow-emerald-600/25 transition-all hover:from-emerald-500 hover:to-emerald-600 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                <svg v-if="!saving" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                </svg>
                <svg v-else class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>{{ saving ? 'Guardando...' : (saveMode === 'template_and_assign' ? 'Guardar y Asignar Plan' : 'Guardar Plantilla') }}</span>
              </button>
            </div>
          </template>

          <!-- Success state -->
          <Transition name="fade">
            <div v-if="saved" class="rounded-xl border border-emerald-500/30 bg-wc-bg-secondary p-8 text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20">
                <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <h2 class="mt-4 text-xl font-bold text-wc-text">Plan Guardado Exitosamente</h2>
              <p class="mt-2 text-sm text-wc-text-secondary">
                {{ saveMode === 'template_and_assign'
                  ? `La plantilla ha sido creada y el plan fue asignado a ${selectedClientData?.name ?? 'el cliente'}.`
                  : 'La plantilla ha sido creada y esta disponible para asignar.' }}
              </p>
              <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a v-if="savedTemplateId" href="/admin/planes"
                  class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                  </svg>
                  Ver Plantillas
                </a>
                <a v-if="savedAssignedId && selectedClientId" :href="`/admin/cliente/${selectedClientId}`"
                  class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                  Ver Cliente
                </a>
                <button @click="startNew"
                  class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-500 transition-colors">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                  </svg>
                  Crear Otro Plan
                </button>
              </div>
            </div>
          </Transition>

        </div>
      </Transition>

      <!-- ══════════════════════════════════════════════ -->
      <!-- Navigation Footer                             -->
      <!-- ══════════════════════════════════════════════ -->
      <div v-if="!saved" class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <button @click="prevStep" :disabled="currentStep <= 1"
          class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
          :class="currentStep > 1 ? 'bg-wc-bg-tertiary text-wc-text hover:bg-wc-border' : 'cursor-not-allowed text-wc-text-tertiary'">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
          Atras
        </button>

        <span class="text-xs font-medium text-wc-text-tertiary">Paso {{ currentStep }} de 4</span>

        <button v-if="currentStep < 4" @click="nextStep"
          class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-red-500"
          :class="(currentStep === 1 && !canAdvanceStep1) || (currentStep === 2 && !canAdvanceStep2) || (currentStep === 3 && !canAdvanceStep3) ? 'opacity-50 cursor-not-allowed' : ''">
          Siguiente
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
          </svg>
        </button>
        <div v-else></div>
      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
