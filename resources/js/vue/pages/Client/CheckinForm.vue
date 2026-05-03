<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, useTemplateRef } from 'vue';
import { useApi } from '../../composables/useApi';
import { useMedals } from '../../composables/useMedals';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';
import ClientLayout from '../../layouts/ClientLayout.vue';
import CheckinProgress from '../../components/checkin/CheckinProgress.vue';
import WcRangeSlider from '../../components/checkin/WcRangeSlider.vue';
import DaysPicker from '../../components/checkin/DaysPicker.vue';

const api = useApi();
const { fetchMedals } = useMedals();
const toast = useToast();
const haptics = useHaptics();

// ─── Wizard configuration ─────────────────────────────────────────
const WIZARD_STEPS = [
  { key: 'bienestar',    label: 'Bienestar' },
  { key: 'entrenamiento', label: 'Entreno' },
  { key: 'nutricion',    label: 'Nutrición' },
  { key: 'comentario',   label: 'Notas' },
];

const NUTRICION_OPTIONS = [
  { value: 'Si',      label: 'La seguí bien',   hint: 'Apegué mi plan al menos 80% de la semana.' },
  { value: 'Parcial', label: 'Parcialmente',     hint: 'Tuve algunos desvíos pero me mantuve en general.' },
  { value: 'No',      label: 'No la seguí',      hint: 'Esta semana se me complicó. Necesito apoyo.' },
];

const BIENESTAR_LABELS = {
  1: { word: 'Muy mal',  hint: 'Cansado, sin energía, ánimo bajo.' },
  2: { word: 'Mal',      hint: 'No fue una buena semana en general.' },
  3: { word: 'Normal',   hint: 'Equilibrado: ni alto ni bajo.' },
  4: { word: 'Bien',     hint: 'Buena energía y ánimo la mayor parte.' },
  5: { word: 'Muy bien', hint: 'Semana excelente, energía top.' },
};

// ─── Confetti pieces (static, module-level) ───────────────────────
const confettiPieces = [
  { left: '8%',  color: '#DC2626', delay: '0.1s', duration: '2.8s', round: false },
  { left: '22%', color: '#F59E0B', delay: '0.3s', duration: '3.2s', round: true },
  { left: '38%', color: '#10B981', delay: '0s',   duration: '2.5s', round: false },
  { left: '52%', color: '#DC2626', delay: '0.5s', duration: '3s',   round: true },
  { left: '65%', color: '#8B5CF6', delay: '0.2s', duration: '2.7s', round: false },
  { left: '78%', color: '#F59E0B', delay: '0.4s', duration: '3.4s', round: true },
  { left: '90%', color: '#10B981', delay: '0.15s', duration: '2.6s', round: false },
  { left: '45%', color: '#8B5CF6', delay: '0.6s', duration: '3.1s', round: false },
];

// ─── Module-level mutable handles (NOT reactive) ──────────────────
let confettiTimer = null;

// ─── State ────────────────────────────────────────────────────────
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const showSuccess = ref(false);
const showConfetti = ref(false);
const isCheckinAvailable = ref(false);
const alreadySubmitted = ref(false);

// Tutorial
const showTutorial = ref(false);
const tutorialStep = ref(1);
const tutorialTotal = 3;

// Wizard
const currentStep = ref(1);
const stepRoot = useTemplateRef('stepRoot');

// Form fields
const bienestar = ref(3);
const diasEntrenados = ref(0);
const nutricion = ref('Si');
const rpe = ref(5);
const comentario = ref('');
const formErrors = ref({});

// Recent check-ins
const recentCheckins = ref([]);

// Current week info
const currentWeekNum = computed(() => {
  const now = new Date();
  const d = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));
  const dayNum = d.getUTCDay() || 7;
  d.setUTCDate(d.getUTCDate() + 4 - dayNum);
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
  return Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
});
const currentWeekLabel = computed(() => 'Semana ' + currentWeekNum.value);
const currentDateLabel = computed(() => {
  const now = new Date();
  const months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
  return now.getDate() + ' de ' + months[now.getMonth()] + ', ' + now.getFullYear();
});

const bienestarMeta = computed(() => BIENESTAR_LABELS[bienestar.value] ?? BIENESTAR_LABELS[3]);
const totalSteps = WIZARD_STEPS.length;
const isLastStep = computed(() => currentStep.value === totalSteps);
const isFirstStep = computed(() => currentStep.value === 1);

// Last submitted data for success overlay
const lastBienestar = ref(0);
const lastDiasEntrenados = ref(0);

// ─── Wizard navigation ────────────────────────────────────────────
function goToStep(step) {
  if (step < 1 || step > totalSteps) return;
  currentStep.value = step;
  haptics.light?.();
  // Scroll suave al inicio del paso
  nextTick(() => {
    if (stepRoot.value && typeof stepRoot.value.scrollIntoView === 'function') {
      stepRoot.value.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
}
function nextStep() {
  if (!validateCurrentStep()) return;
  if (currentStep.value < totalSteps) goToStep(currentStep.value + 1);
}
function prevStep() {
  if (currentStep.value > 1) goToStep(currentStep.value - 1);
}

// Per-step client validation (light — backend remains source of truth)
function validateCurrentStep() {
  formErrors.value = {};
  if (currentStep.value === 1) {
    if (!bienestar.value || bienestar.value < 1 || bienestar.value > 5) {
      formErrors.value.bienestar = 'Selecciona tu nivel de bienestar (1-5)';
      return false;
    }
  }
  if (currentStep.value === 2) {
    if (diasEntrenados.value < 0 || diasEntrenados.value > 7) {
      formErrors.value.dias_entrenados = 'Días entrenados debe estar entre 0 y 7';
      return false;
    }
    if (rpe.value < 1 || rpe.value > 10) {
      formErrors.value.rpe = 'RPE debe estar entre 1 y 10';
      return false;
    }
  }
  if (currentStep.value === 3) {
    if (!['Si','Parcial','No'].includes(nutricion.value)) {
      formErrors.value.nutricion = 'Selecciona una opción de nutrición';
      return false;
    }
  }
  if (currentStep.value === 4) {
    if (comentario.value.length > 1000) {
      formErrors.value.comentario = `El comentario no puede superar 1000 caracteres (${comentario.value.length}/1000)`;
      return false;
    }
  }
  return true;
}

// ─── Confetti control ─────────────────────────────────────────────
watch(showSuccess, (v) => {
  clearTimeout(confettiTimer);
  if (v) {
    showConfetti.value = true;
    confettiTimer = setTimeout(() => { showConfetti.value = false; }, 4000);
  } else {
    showConfetti.value = false;
  }
});

// ─── Tutorial ─────────────────────────────────────────────────────
function nextTutorialStep() {
  if (tutorialStep.value < tutorialTotal) tutorialStep.value++;
}
function prevTutorialStep() {
  if (tutorialStep.value > 1) tutorialStep.value--;
}
function dismissTutorial() {
  showTutorial.value = false;
  tutorialStep.value = 1;
}

// ─── Fetch ────────────────────────────────────────────────────────
async function fetchCheckin() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/checkin');
    const d = response.data;
    isCheckinAvailable.value = d.is_checkin_available ?? false;
    alreadySubmitted.value = d.already_submitted ?? false;
    showTutorial.value = d.show_tutorial ?? false;
    recentCheckins.value = d.recent_checkins ?? [];
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar check-in';
  } finally {
    loading.value = false;
  }
}

// ─── Submit ───────────────────────────────────────────────────────
async function submitCheckin() {
  if (submitting.value) return;
  if (!validateCurrentStep()) return;

  // Validación final agregada (todos los pasos)
  if (!bienestar.value) {
    formErrors.value.bienestar = 'Selecciona tu nivel de bienestar';
    goToStep(1);
    return;
  }

  submitting.value = true;
  try {
    await api.post('/api/v/client/checkin', {
      bienestar: bienestar.value,
      dias_entrenados: diasEntrenados.value,
      nutricion: nutricion.value,
      rpe: rpe.value,
      comentario: comentario.value,
    });

    lastBienestar.value = bienestar.value;
    lastDiasEntrenados.value = diasEntrenados.value;
    showSuccess.value = true;
    toast.success('Check-in enviado.');
    haptics.pattern?.('success');
    fetchMedals().catch(() => {});

    // Reset form
    bienestar.value = 3;
    diasEntrenados.value = 0;
    nutricion.value = 'Si';
    rpe.value = 5;
    comentario.value = '';
    currentStep.value = 1;

    await fetchCheckin();
  } catch (err) {
    if (err.response?.status === 422) {
      if (err.response.data.errors) {
        const errors = err.response.data.errors;
        formErrors.value = {};
        for (const key in errors) {
          formErrors.value[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        }
        // Lleva al usuario al paso del primer error
        if (formErrors.value.bienestar) goToStep(1);
        else if (formErrors.value.dias_entrenados || formErrors.value.rpe) goToStep(2);
        else if (formErrors.value.nutricion) goToStep(3);
        else if (formErrors.value.comentario) goToStep(4);
      } else if (err.response.data.error) {
        formErrors.value.submit = err.response.data.error;
      } else if (err.response.data.message) {
        formErrors.value.submit = err.response.data.message;
      }
      toast.apiError(err, 'Revisa los datos del formulario.');
    } else {
      formErrors.value.submit = err.response?.data?.message || 'Error al enviar el check-in';
      toast.apiError(err, 'No pudimos enviar tu check-in.');
    }
  } finally {
    submitting.value = false;
  }
}

function dismissSuccess() {
  showSuccess.value = false;
}

// ─── Recent checkin badges ────────────────────────────────────────
function nutricionBadgeText(value) {
  if (value === 'Si') return 'Nutri 100%';
  if (value === 'Parcial') return 'Nutri Parcial';
  if (value === 'No') return 'Nutri No';
  return value;
}

// ─── Lifecycle ────────────────────────────────────────────────────
onMounted(fetchCheckin);

onBeforeUnmount(() => {
  clearTimeout(confettiTimer);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6 pb-24 md:pb-6">
      <!-- Title -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="font-display text-3xl uppercase tracking-wide text-wc-text">Check-in semanal</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            {{ currentWeekLabel }} &middot; {{ currentDateLabel }}
          </p>
        </div>
        <p class="hidden text-right text-xs text-wc-text-secondary sm:block">
          Tu coach responde en<br /><span class="font-semibold text-wc-text">menos de 24 h</span>
        </p>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-14 rounded-card border border-wc-border bg-wc-bg-tertiary"></div>
          <div class="h-96 rounded-card border border-wc-border bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-card border border-wc-border bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-card border border-red-500/30 bg-red-500/10 p-6 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="fetchCheckin" class="wc-btn-primary mt-4">Reintentar</button>
      </div>

      <template v-else>
        <!-- Day restriction banner -->
        <div v-if="!isCheckinAvailable" class="flex items-start gap-4 rounded-card border border-wc-accent/30 bg-wc-accent/10 px-5 py-4">
          <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/20">
            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-wc-accent">Check-in no disponible hoy</p>
            <p class="mt-0.5 text-sm text-wc-text-secondary">
              El check-in semanal estará disponible el próximo <span class="font-semibold text-wc-text">viernes o sábado</span>.
              Sigue entrenando — la consistencia es tu superpoder.
            </p>
          </div>
        </div>

        <!-- Top-level submit error -->
        <p v-if="formErrors.submit" class="rounded-card border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">{{ formErrors.submit }}</p>

        <!-- ===== WIZARD WRAPPER ===== -->
        <section
          ref="stepRoot"
          class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 shadow-sm sm:p-6 pb-32 sm:pb-6"
          aria-labelledby="checkin-wizard-title"
        >
          <h2 id="checkin-wizard-title" class="sr-only">Formulario de check-in</h2>

          <!-- Progress -->
          <CheckinProgress
            :steps="WIZARD_STEPS"
            :current="currentStep"
            @go="goToStep"
            class="mb-6"
          />

          <!-- Step panels -->
          <Transition name="wizard-fade" mode="out-in">
            <!-- ── Step 1: Bienestar ── -->
            <form
              v-if="currentStep === 1"
              key="step-1"
              @submit.prevent="nextStep"
              class="space-y-5"
              novalidate
            >
              <header>
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">Bienestar general</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">¿Cómo te has sentido en general esta semana? (energía, ánimo, descanso)</p>
              </header>

              <WcRangeSlider
                v-model="bienestar"
                label="Tu nivel de bienestar"
                :min="1"
                :max="5"
                left-label="Muy mal"
                right-label="Muy bien"
                suffix="/5"
                id="cf-bienestar"
              />

              <div class="rounded-card border border-wc-border bg-wc-bg-secondary p-4">
                <p class="font-display text-base uppercase tracking-wide text-wc-accent">{{ bienestarMeta.word }}</p>
                <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">{{ bienestarMeta.hint }}</p>
              </div>

              <p v-if="formErrors.bienestar" class="text-xs text-red-500">{{ formErrors.bienestar }}</p>

              <div class="hidden sm:flex sm:justify-end">
                <button
                  type="submit"
                  class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
                >
                  Siguiente
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </button>
              </div>
            </form>

            <!-- ── Step 2: Entrenamiento ── -->
            <form
              v-else-if="currentStep === 2"
              key="step-2"
              @submit.prevent="nextStep"
              class="space-y-6"
              novalidate
            >
              <header>
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">Entrenamiento</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">¿Cuántos días entrenaste? ¿Cómo se sintió la carga?</p>
              </header>

              <div>
                <p class="mb-2 wc-caption">Días entrenados</p>
                <DaysPicker v-model="diasEntrenados" :max="7" />
                <p class="mt-2 text-xs text-wc-text-tertiary">
                  <span class="font-data font-semibold text-wc-text">{{ diasEntrenados }}</span>
                  de 7 días &middot; {{ diasEntrenados >= 4 ? 'Excelente consistencia.' : diasEntrenados >= 2 ? 'Vas construyendo el hábito.' : 'Esta semana fue tranquila — recupérate bien.' }}
                </p>
                <p v-if="formErrors.dias_entrenados" class="mt-1 text-xs text-red-500">{{ formErrors.dias_entrenados }}</p>
              </div>

              <WcRangeSlider
                v-model="rpe"
                label="RPE promedio de la semana"
                :min="1"
                :max="10"
                left-label="Muy fácil (1)"
                right-label="Máximo esfuerzo (10)"
                suffix="/10"
                id="cf-rpe"
              />
              <p v-if="formErrors.rpe" class="text-xs text-red-500">{{ formErrors.rpe }}</p>

              <div class="hidden gap-3 sm:flex sm:justify-between">
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← Atrás</button>
                <button type="submit" class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover">
                  Siguiente
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </button>
              </div>
            </form>

            <!-- ── Step 3: Nutrición ── -->
            <form
              v-else-if="currentStep === 3"
              key="step-3"
              @submit.prevent="nextStep"
              class="space-y-5"
              novalidate
            >
              <header>
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">Nutrición</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">¿Qué tan bien seguiste tu plan nutricional esta semana?</p>
              </header>

              <div role="radiogroup" aria-label="Adherencia al plan nutricional" class="space-y-2">
                <button
                  v-for="opt in NUTRICION_OPTIONS"
                  :key="opt.value"
                  type="button"
                  role="radio"
                  :aria-checked="nutricion === opt.value"
                  @click="nutricion = opt.value"
                  :class="[
                    'flex w-full items-start gap-3 rounded-card border p-4 text-left transition-all',
                    nutricion === opt.value
                      ? 'border-wc-accent bg-wc-accent/10 shadow-[inset_0_0_0_1px_var(--color-wc-accent)]'
                      : 'border-wc-border bg-wc-bg-secondary hover:border-wc-accent/40'
                  ]"
                >
                  <span
                    :class="[
                      'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                      nutricion === opt.value ? 'border-wc-accent bg-wc-accent' : 'border-wc-border bg-wc-bg'
                    ]"
                  >
                    <span
                      v-if="nutricion === opt.value"
                      class="h-2 w-2 rounded-full bg-white"
                      aria-hidden="true"
                    ></span>
                  </span>
                  <span class="min-w-0 flex-1">
                    <span :class="['block text-sm font-semibold', nutricion === opt.value ? 'text-wc-accent' : 'text-wc-text']">{{ opt.label }}</span>
                    <span class="mt-0.5 block text-xs leading-relaxed text-wc-text-secondary">{{ opt.hint }}</span>
                  </span>
                </button>
              </div>

              <p v-if="formErrors.nutricion" class="text-xs text-red-500">{{ formErrors.nutricion }}</p>

              <div class="hidden gap-3 sm:flex sm:justify-between">
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← Atrás</button>
                <button type="submit" class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover">
                  Siguiente
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </button>
              </div>
            </form>

            <!-- ── Step 4: Comentario + Submit ── -->
            <form
              v-else-if="currentStep === 4"
              key="step-4"
              @submit.prevent="submitCheckin"
              class="space-y-5"
              novalidate
            >
              <header>
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">Notas para tu coach</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">Cuéntale cómo te fue, qué dudas tienes o qué quieres ajustar. (opcional)</p>
              </header>

              <div>
                <label for="cf-comentario" class="sr-only">Comentario para tu coach</label>
                <textarea
                  v-model="comentario"
                  id="cf-comentario"
                  rows="5"
                  maxlength="1000"
                  placeholder="Ej: Esta semana sentí el press inclinado más fuerte, llegué a 80kg × 8. La nutrición estuvo al 75% por una salida de trabajo. ¿Podemos ajustar las porciones del almuerzo?"
                  class="w-full rounded-card border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
                ></textarea>
                <div class="mt-1 flex items-center justify-between text-[11px] text-wc-text-tertiary">
                  <p>Tu coach lo recibe al instante.</p>
                  <p class="font-data tabular-nums">{{ comentario.length }} / 1000</p>
                </div>
                <p v-if="formErrors.comentario" class="mt-1 text-xs text-red-500">{{ formErrors.comentario }}</p>
              </div>

              <!-- Resumen previo al envío -->
              <div class="rounded-card border border-wc-border bg-wc-bg-secondary p-4">
                <p class="mb-3 wc-caption">Resumen</p>
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">Bienestar</dt>
                    <dd class="font-data text-base font-semibold text-wc-text">{{ bienestar }}/5</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">Días</dt>
                    <dd class="font-data text-base font-semibold text-wc-text">{{ diasEntrenados }}/7</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">Nutrición</dt>
                    <dd class="text-base font-semibold capitalize text-wc-text">{{ nutricion === 'Si' ? 'Bien' : nutricion === 'Parcial' ? 'Parcial' : 'No' }}</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">RPE</dt>
                    <dd class="font-data text-base font-semibold text-wc-text">{{ rpe }}/10</dd>
                  </div>
                </dl>
              </div>

              <!-- Inline submit error -->
              <div v-if="formErrors.submit" class="flex items-center gap-3 rounded-card border border-wc-accent/30 bg-wc-accent/10 p-4">
                <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm font-medium text-wc-accent">{{ formErrors.submit }}</p>
              </div>

              <div class="hidden gap-3 sm:flex sm:justify-between">
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← Atrás</button>
                <button
                  type="submit"
                  :disabled="!isCheckinAvailable || submitting"
                  :title="!isCheckinAvailable ? 'Solo disponible viernes y sábado' : undefined"
                  :aria-disabled="!isCheckinAvailable || undefined"
                  class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:cursor-not-allowed disabled:opacity-40"
                >
                  <template v-if="submitting">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Enviando...
                  </template>
                  <template v-else-if="!isCheckinAvailable">Disponible el viernes</template>
                  <template v-else>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Enviar check-in
                  </template>
                </button>
              </div>
            </form>
          </Transition>
        </section>
        <!-- ===== /WIZARD ===== -->

        <!-- ===== Mobile sticky CTA ===== -->
        <div class="fixed inset-x-0 z-40 border-t border-wc-border bg-wc-bg/95 backdrop-blur sm:hidden" style="bottom: calc(4rem + env(safe-area-inset-bottom));" v-if="!loading && !error">
          <div class="mx-auto flex max-w-screen-sm gap-2 p-3">
            <button
              v-if="!isFirstStep"
              type="button"
              @click="prevStep"
              :disabled="submitting"
              class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-50"
              aria-label="Paso anterior"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </button>
            <button
              v-if="!isLastStep"
              type="button"
              @click="nextStep"
              class="btn-press flex-1 rounded-button bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
            >
              Siguiente paso
            </button>
            <button
              v-else
              type="button"
              @click="submitCheckin"
              :disabled="!isCheckinAvailable || submitting"
              :title="!isCheckinAvailable ? 'Solo disponible viernes y sábado' : undefined"
              :aria-disabled="!isCheckinAvailable || undefined"
              class="btn-press flex flex-1 items-center justify-center gap-2 rounded-button bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover disabled:cursor-not-allowed disabled:opacity-40"
            >
              <template v-if="submitting">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Enviando...
              </template>
              <template v-else-if="!isCheckinAvailable">Disponible el viernes</template>
              <template v-else>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                Enviar check-in
              </template>
            </button>
          </div>
        </div>

        <!-- ===== Recent Check-ins ===== -->
        <div v-if="recentCheckins.length > 0">
          <h2 class="mb-4 font-display text-xl uppercase tracking-wide text-wc-text">Check-ins anteriores</h2>
          <div class="space-y-3">
            <div v-for="(checkin, cIdx) in recentCheckins" :key="checkin.id || cIdx" class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <!-- Header -->
              <div class="mb-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <span class="font-data text-sm font-semibold text-wc-text">{{ checkin.week_label }}</span>
                  <span class="text-xs text-wc-text-tertiary">{{ checkin.checkin_date }}</span>
                </div>
                <span v-if="checkin.coach_reply" class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-500">Respondido</span>
                <span v-else class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-yellow-500">En revisión</span>
              </div>

              <!-- Quick badges -->
              <div class="mb-3 flex flex-wrap gap-1.5">
                <span class="rounded bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold text-wc-accent">Bienestar {{ checkin.bienestar }}/5</span>
                <span class="rounded bg-blue-500/10 px-2 py-0.5 text-[10px] font-bold text-blue-400">RPE {{ checkin.rpe }}/10</span>
                <span class="rounded bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-500">{{ nutricionBadgeText(checkin.nutricion) }}</span>
                <span class="rounded bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-500">{{ checkin.dias_entrenados }}/7 días</span>
              </div>

              <!-- Comentario -->
              <p v-if="checkin.comentario" class="text-sm leading-relaxed text-wc-text-secondary">{{ checkin.comentario }}</p>

              <!-- Coach Reply -->
              <div v-if="checkin.coach_reply" class="mt-3 rounded-card border border-wc-accent/20 bg-wc-accent/5 p-3">
                <div class="mb-1 flex items-center gap-2">
                  <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                  <span class="text-[11px] font-semibold uppercase tracking-wide text-wc-accent">Respuesta del coach</span>
                  <span v-if="checkin.replied_at" class="text-[11px] text-wc-text-tertiary">{{ checkin.replied_at }}</span>
                </div>
                <p class="text-sm leading-relaxed text-wc-text">{{ checkin.coach_reply }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- ===== ACHIEVEMENT OVERLAY: CHECK-IN ===== -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="showSuccess"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.85);"
            @keydown.escape.window="dismissSuccess"
          >
            <!-- Confetti -->
            <div v-if="showConfetti" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
              <div
                v-for="(piece, pIdx) in confettiPieces"
                :key="pIdx"
                class="wc-confetti"
                :style="{
                  left: piece.left,
                  background: piece.color,
                  borderRadius: piece.round ? '50%' : '0',
                  animation: `wc-confetti-fall ${piece.duration} ease-in forwards ${piece.delay}`,
                }"
              ></div>
            </div>

            <!-- Card -->
            <Transition
              enter-active-class="transition ease-out duration-400"
              enter-from-class="opacity-0 scale-90"
              enter-to-class="opacity-100 scale-100"
            >
              <div
                v-if="showSuccess"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl text-center"
                style="background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%);"
                role="dialog"
                aria-modal="true"
                aria-labelledby="checkin-success-title"
              >
                <!-- Shimmer -->
                <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);" aria-hidden="true"></div>

                <div class="relative z-10 p-8">
                  <span class="wc-emoji-bounce mb-4 block text-6xl" aria-hidden="true">&#x2705;</span>

                  <div class="mb-3 flex items-center justify-center gap-2">
                    <span class="font-display text-xl uppercase tracking-[0.25em] text-white/90">WellCore</span>
                    <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
                  </div>

                  <h2 id="checkin-success-title" class="mb-2 font-sans text-2xl font-bold text-white">Check-in enviado</h2>

                  <!-- Stats grid -->
                  <div class="my-5 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ lastDiasEntrenados }}</p>
                      <p class="mt-0.5 text-[11px] text-white/50">días entren.</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ lastBienestar }}/5</p>
                      <p class="mt-0.5 text-[11px] text-white/50">bienestar</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">S{{ currentWeekNum }}</p>
                      <p class="mt-0.5 text-[11px] text-white/50">semana</p>
                    </div>
                  </div>

                  <p class="mb-6 text-sm text-white/70">Tu coach revisará tu reporte esta semana. Sigue así.</p>

                  <button
                    @click="dismissSuccess"
                    class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg uppercase tracking-wider text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
                  >Perfecto</button>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>
      </Teleport>
      <!-- ===== /ACHIEVEMENT OVERLAY: CHECK-IN ===== -->

      <!-- ===== ONBOARDING TUTORIAL: CHECK-IN ===== -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="showTutorial"
            class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
            @keydown.escape.window="dismissTutorial"
          >
            <Transition
              enter-active-class="transition ease-out duration-300"
              enter-from-class="opacity-0 translate-y-8"
              enter-to-class="opacity-100 translate-y-0"
            >
              <div v-if="showTutorial" class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                  <h3 class="font-display text-lg uppercase tracking-widest text-wc-text">Check-in semanal</h3>
                  <button @click="dismissTutorial" class="text-wc-text-tertiary transition-colors hover:text-wc-text" aria-label="Cerrar" type="button">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>

                <!-- Step 1 -->
                <div v-show="tutorialStep === 1">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">1</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">¿Qué es el check-in?</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">Es tu reporte semanal al coach. Con esta información tu coach ajusta tu plan de entrenamiento y nutrición para maximizar tus resultados semana a semana.</p>
                    </div>
                  </div>
                </div>

                <!-- Step 2 -->
                <div v-show="tutorialStep === 2">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">2</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">Sé honesto</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">No hay respuestas malas. Si tuviste una semana difícil, dilo. Tu coach solo puede ayudarte si conoce tu realidad — no la versión perfecta.</p>
                    </div>
                  </div>
                </div>

                <!-- Step 3 -->
                <div v-show="tutorialStep === 3">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">3</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">Hazlo cada semana</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">Los clientes que completan su check-in semanalmente progresan 3× más rápido. El seguimiento constante es lo que diferencia los resultados promedio de los extraordinarios.</p>
                    </div>
                  </div>
                </div>

                <!-- Step indicators -->
                <div class="mt-4 flex justify-center gap-1.5">
                  <div
                    v-for="i in tutorialTotal"
                    :key="i"
                    class="h-1.5 rounded-full transition-all"
                    :class="i === tutorialStep ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-bg-tertiary'"
                  ></div>
                </div>

                <!-- Navigation buttons -->
                <div class="mt-5 flex gap-3">
                  <button
                    v-if="tutorialStep > 1"
                    @click="prevTutorialStep"
                    type="button"
                    class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
                  >Atrás</button>
                  <button
                    v-if="tutorialStep < tutorialTotal"
                    @click="nextTutorialStep"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
                  >Siguiente</button>
                  <button
                    v-if="tutorialStep === tutorialTotal"
                    @click="dismissTutorial"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
                  >Listo, comenzar</button>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>
      </Teleport>
      <!-- ===== /ONBOARDING TUTORIAL: CHECK-IN ===== -->
    </div>
  </ClientLayout>
</template>

<style scoped>
@keyframes wc-confetti-fall {
  0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
  100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}
.wc-confetti {
  position: absolute;
  top: -10px;
  width: 10px;
  height: 10px;
}
@keyframes wc-emoji-bounce {
  0%, 100% { transform: scale(1) rotate(-3deg); }
  50%      { transform: scale(1.15) rotate(3deg); }
}
.wc-emoji-bounce {
  animation: wc-emoji-bounce 2s ease-in-out infinite;
  display: inline-block;
}

/* Wizard step transitions */
.wizard-fade-enter-active,
.wizard-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}
.wizard-fade-enter-from {
  opacity: 0;
  transform: translateX(8px);
}
.wizard-fade-leave-to {
  opacity: 0;
  transform: translateX(-8px);
}

@media (prefers-reduced-motion: reduce) {
  .wc-emoji-bounce {
    animation: none !important;
  }
  .wizard-fade-enter-active,
  .wizard-fade-leave-active {
    transition: none !important;
  }
}
</style>
