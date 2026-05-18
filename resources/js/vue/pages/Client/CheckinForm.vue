<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import { useMedals } from '../../composables/useMedals';
import { useToast } from '../../composables/useToast';
import { useHaptics } from '../../composables/useHaptics';
import ClientLayout from '../../layouts/ClientLayout.vue';
import WcErrorState from '../../components/WcErrorState.vue';
import CheckinProgress from '../../components/checkin/CheckinProgress.vue';
import WcRangeSlider from '../../components/checkin/WcRangeSlider.vue';
import DaysPicker from '../../components/checkin/DaysPicker.vue';

const api = useApi();
const { t } = useI18n();
const { fetchMedals } = useMedals();
const toast = useToast();
const haptics = useHaptics();

// ─── Wizard configuration ─────────────────────────────────────────
const WIZARD_STEPS = computed(() => [
  { key: 'bienestar',    label: t('client_progress.checkin_step_wellbeing') },
  { key: 'entrenamiento', label: t('client_progress.checkin_step_training') },
  { key: 'nutricion',    label: t('client_progress.checkin_step_nutrition') },
  { key: 'comentario',   label: t('client_progress.checkin_step_notes') },
]);

const NUTRICION_OPTIONS = computed(() => [
  { value: 'Si',      label: t('client_progress.checkin_nutrition_followed_label'), hint: t('client_progress.checkin_nutrition_followed_hint') },
  { value: 'Parcial', label: t('client_progress.checkin_nutrition_partial_label'),  hint: t('client_progress.checkin_nutrition_partial_hint') },
  { value: 'No',      label: t('client_progress.checkin_nutrition_no_label'),       hint: t('client_progress.checkin_nutrition_no_hint') },
]);

const BIENESTAR_LABELS = computed(() => ({
  1: { word: t('client_progress.checkin_scale_very_bad'),  hint: t('client_progress.checkin_scale_very_bad_hint') },
  2: { word: t('client_progress.checkin_scale_bad'),       hint: t('client_progress.checkin_scale_bad_hint') },
  3: { word: t('client_progress.checkin_scale_ok'),        hint: t('client_progress.checkin_scale_ok_hint') },
  4: { word: t('client_progress.checkin_scale_good'),      hint: t('client_progress.checkin_scale_good_hint') },
  5: { word: t('client_progress.checkin_scale_very_good'), hint: t('client_progress.checkin_scale_very_good_hint') },
}));

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
const currentWeekLabel = computed(() => t('client_progress.checkin_week_label', { n: currentWeekNum.value }));
const MONTH_KEYS = ['month_jan','month_feb','month_mar','month_apr','month_may','month_jun','month_jul','month_aug','month_sep','month_oct','month_nov','month_dec'];
const currentDateLabel = computed(() => {
  const now = new Date();
  const monthName = t('client_progress.' + MONTH_KEYS[now.getMonth()]);
  return t('client_progress.date_format_long', {
    d: now.getDate(),
    month: monthName,
    year: now.getFullYear(),
  });
});

const bienestarMeta = computed(() => BIENESTAR_LABELS.value[bienestar.value] ?? BIENESTAR_LABELS.value[3]);
const totalSteps = computed(() => WIZARD_STEPS.value.length);
const isLastStep = computed(() => currentStep.value === totalSteps.value);
const isFirstStep = computed(() => currentStep.value === 1);

// Last submitted data for success overlay
const lastBienestar = ref(0);
const lastDiasEntrenados = ref(0);

// ─── Wizard navigation ────────────────────────────────────────────
function goToStep(step) {
  if (step < 1 || step > totalSteps.value) return;
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
  if (currentStep.value < totalSteps.value) goToStep(currentStep.value + 1);
}
function prevStep() {
  if (currentStep.value > 1) goToStep(currentStep.value - 1);
}

// Per-step client validation (light — backend remains source of truth)
function validateCurrentStep() {
  formErrors.value = {};
  if (currentStep.value === 1) {
    if (!bienestar.value || bienestar.value < 1 || bienestar.value > 5) {
      formErrors.value.bienestar = t('client_progress.checkin_err_wellbeing_required');
      return false;
    }
  }
  if (currentStep.value === 2) {
    if (diasEntrenados.value < 0 || diasEntrenados.value > 7) {
      formErrors.value.dias_entrenados = t('client_progress.checkin_err_days_range');
      return false;
    }
    if (rpe.value < 1 || rpe.value > 10) {
      formErrors.value.rpe = t('client_progress.checkin_err_rpe_range');
      return false;
    }
  }
  if (currentStep.value === 3) {
    if (!['Si','Parcial','No'].includes(nutricion.value)) {
      formErrors.value.nutricion = t('client_progress.checkin_err_nutrition_required');
      return false;
    }
  }
  if (currentStep.value === 4) {
    if (comentario.value.length > 1000) {
      formErrors.value.comentario = t('client_progress.checkin_err_notes_max', { n: comentario.value.length });
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
    error.value = err.response?.data?.message || t('client_progress.checkin_err_load');
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
    formErrors.value.bienestar = t('client_progress.checkin_err_wellbeing_required_short');
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
    toast.success(t('client_progress.checkin_toast_sent'));
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
      toast.apiError(err, t('client_progress.checkin_toast_form_review'));
    } else {
      formErrors.value.submit = err.response?.data?.message || t('client_progress.checkin_err_send_generic');
      toast.apiError(err, t('client_progress.checkin_toast_send_failed'));
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
  if (value === 'Si') return t('client_progress.checkin_recent_nutrition_full');
  if (value === 'Parcial') return t('client_progress.checkin_recent_nutrition_partial');
  if (value === 'No') return t('client_progress.checkin_recent_nutrition_no');
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
    <div class="wc-shell wc-shell--checkin">
    <div class="space-y-6 pb-24 md:pb-6 px-4">
      <!-- Title -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="font-display text-3xl uppercase tracking-wide text-wc-text">{{ t('client_progress.checkin_title') }}</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            {{ currentWeekLabel }} &middot; {{ currentDateLabel }}
          </p>
        </div>
        <p class="hidden text-right text-xs text-wc-text-secondary sm:block">
          {{ t('client_progress.checkin_coach_replies_prefix') }}<br /><span class="font-semibold text-wc-text">{{ t('client_progress.checkin_coach_replies_value') }}</span>
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

            <WcErrorState v-else-if="error" :message="error" @retry="fetchCheckin" />

      <template v-else>
        <!-- Day restriction banner -->
        <div v-if="!isCheckinAvailable" class="flex items-start gap-4 rounded-card border border-wc-accent/30 bg-wc-accent/10 px-5 py-4">
          <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/20">
            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-wc-accent">{{ t('client_progress.checkin_not_available_title') }}</p>
            <p class="mt-0.5 text-sm text-wc-text-secondary">
              {{ t('client_progress.checkin_not_available_body_prefix') }} <span class="font-semibold text-wc-text">{{ t('client_progress.checkin_not_available_body_days') }}</span>{{ t('client_progress.checkin_not_available_body_suffix') }}
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
          <h2 id="checkin-wizard-title" class="sr-only">{{ t('client_progress.checkin_form_title_sr') }}</h2>

          <!-- Progress -->
          <CheckinProgress
            :steps="WIZARD_STEPS"
            :current="currentStep"
            :aria-label="t('client_progress.checkin_progress_aria')"
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
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">{{ t('client_progress.checkin_q_wellbeing_title') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ t('client_progress.checkin_q_wellbeing_hint') }}</p>
              </header>

              <WcRangeSlider
                v-model="bienestar"
                :label="t('client_progress.checkin_q_wellbeing_label')"
                :min="1"
                :max="5"
                :left-label="t('client_progress.checkin_scale_very_bad')"
                :right-label="t('client_progress.checkin_scale_very_good')"
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
                  {{ t('client_progress.checkin_btn_next') }}
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
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">{{ t('client_progress.checkin_q_training_title') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ t('client_progress.checkin_q_training_hint') }}</p>
              </header>

              <div>
                <p class="mb-2 wc-caption">{{ t('client_progress.checkin_q_days_trained') }}</p>
                <DaysPicker v-model="diasEntrenados" :max="7" :aria-label="t('client_progress.checkin_q_days_picker_aria')" />
                <p class="mt-2 text-xs text-wc-text-tertiary">
                  <span class="font-data font-semibold text-wc-text">{{ diasEntrenados }}</span>
                  {{ t('client_progress.checkin_days_of_7') }} &middot; {{ diasEntrenados >= 4 ? t('client_progress.checkin_days_excellent') : diasEntrenados >= 2 ? t('client_progress.checkin_days_building') : t('client_progress.checkin_days_quiet') }}
                </p>
                <p v-if="formErrors.dias_entrenados" class="mt-1 text-xs text-red-500">{{ formErrors.dias_entrenados }}</p>
              </div>

              <WcRangeSlider
                v-model="rpe"
                :label="t('client_progress.checkin_q_rpe_label')"
                :min="1"
                :max="10"
                :left-label="t('client_progress.checkin_rpe_left')"
                :right-label="t('client_progress.checkin_rpe_right')"
                suffix="/10"
                id="cf-rpe"
              />
              <p v-if="formErrors.rpe" class="text-xs text-red-500">{{ formErrors.rpe }}</p>

              <div class="hidden gap-3 sm:flex sm:justify-between">
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← {{ t('client_progress.checkin_btn_back') }}</button>
                <button type="submit" class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover">
                  {{ t('client_progress.checkin_btn_next') }}
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
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">{{ t('client_progress.checkin_q_nutrition_title') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ t('client_progress.checkin_q_nutrition_hint') }}</p>
              </header>

              <div role="radiogroup" :aria-label="t('client_progress.checkin_nutrition_aria')" class="space-y-2">
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
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← {{ t('client_progress.checkin_btn_back') }}</button>
                <button type="submit" class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover">
                  {{ t('client_progress.checkin_btn_next') }}
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
                <p class="font-display text-lg uppercase tracking-wider text-wc-text">{{ t('client_progress.checkin_q_notes_title') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ t('client_progress.checkin_q_notes_hint') }}</p>
              </header>

              <div>
                <label for="cf-comentario" class="sr-only">{{ t('client_progress.checkin_notes_label_sr') }}</label>
                <textarea
                  v-model="comentario"
                  id="cf-comentario"
                  rows="5"
                  maxlength="1000"
                  :placeholder="t('client_progress.checkin_notes_placeholder')"
                  class="w-full rounded-card border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/30"
                ></textarea>
                <div class="mt-1 flex items-center justify-between text-[11px] text-wc-text-tertiary">
                  <p>{{ t('client_progress.checkin_notes_realtime_hint') }}</p>
                  <p class="font-data tabular-nums">{{ comentario.length }} / 1000</p>
                </div>
                <p v-if="formErrors.comentario" class="mt-1 text-xs text-red-500">{{ formErrors.comentario }}</p>
              </div>

              <!-- Resumen previo al envío -->
              <div class="rounded-card border border-wc-border bg-wc-bg-secondary p-4">
                <p class="mb-3 wc-caption">{{ t('client_progress.checkin_summary_title') }}</p>
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">{{ t('client_progress.checkin_summary_wellbeing') }}</dt>
                    <dd class="font-data text-base font-semibold text-wc-text">{{ bienestar }}/5</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">{{ t('client_progress.checkin_summary_days') }}</dt>
                    <dd class="font-data text-base font-semibold text-wc-text">{{ diasEntrenados }}/7</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">{{ t('client_progress.checkin_summary_nutrition') }}</dt>
                    <dd class="text-base font-semibold capitalize text-wc-text">{{ nutricion === 'Si' ? t('client_progress.checkin_summary_nutrition_well') : nutricion === 'Parcial' ? t('client_progress.checkin_summary_nutrition_partial') : t('client_progress.checkin_summary_nutrition_no') }}</dd>
                  </div>
                  <div>
                    <dt class="text-[11px] text-wc-text-tertiary">{{ t('client_progress.checkin_summary_rpe') }}</dt>
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
                <button type="button" @click="prevStep" class="rounded-button border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text">← {{ t('client_progress.checkin_btn_back') }}</button>
                <button
                  type="submit"
                  :disabled="!isCheckinAvailable || submitting"
                  :title="!isCheckinAvailable ? t('client_progress.checkin_btn_unavailable_title') : undefined"
                  :aria-disabled="!isCheckinAvailable || undefined"
                  class="btn-press inline-flex items-center gap-2 rounded-button bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:cursor-not-allowed disabled:opacity-40"
                >
                  <template v-if="submitting">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    {{ t('client_progress.checkin_btn_sending') }}
                  </template>
                  <template v-else-if="!isCheckinAvailable">{{ t('client_progress.checkin_btn_unavailable') }}</template>
                  <template v-else>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    {{ t('client_progress.checkin_btn_submit') }}
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
              :aria-label="t('client_progress.checkin_btn_back_aria')"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </button>
            <button
              v-if="!isLastStep"
              type="button"
              @click="nextStep"
              :disabled="submitting"
              class="btn-press flex-1 rounded-button bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ t('client_progress.checkin_btn_next_step') }}
            </button>
            <button
              v-else
              type="button"
              @click="submitCheckin"
              :disabled="!isCheckinAvailable || submitting"
              :title="!isCheckinAvailable ? t('client_progress.checkin_btn_unavailable_title') : undefined"
              :aria-disabled="!isCheckinAvailable || undefined"
              class="btn-press flex flex-1 items-center justify-center gap-2 rounded-button bg-wc-accent py-3 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover disabled:cursor-not-allowed disabled:opacity-40"
            >
              <template v-if="submitting">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                {{ t('client_progress.checkin_btn_sending') }}
              </template>
              <template v-else-if="!isCheckinAvailable">{{ t('client_progress.checkin_btn_unavailable') }}</template>
              <template v-else>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                {{ t('client_progress.checkin_btn_submit') }}
              </template>
            </button>
          </div>
        </div>

        <!-- ===== Recent Check-ins ===== -->
        <div v-if="recentCheckins.length > 0">
          <h2 class="mb-4 font-display text-xl uppercase tracking-wide text-wc-text">{{ t('client_progress.checkin_recent_title') }}</h2>
          <div class="space-y-3">
            <div v-for="(checkin, cIdx) in recentCheckins" :key="checkin.id || cIdx" class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
              <!-- Header -->
              <div class="mb-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <span class="font-data text-sm font-semibold text-wc-text">{{ checkin.week_label }}</span>
                  <span class="text-xs text-wc-text-tertiary">{{ checkin.checkin_date }}</span>
                </div>
                <span v-if="checkin.coach_reply" class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-500">{{ t('client_progress.checkin_recent_status_replied') }}</span>
                <span v-else class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-yellow-500">{{ t('client_progress.checkin_recent_status_pending') }}</span>
              </div>

              <!-- Quick badges -->
              <div class="mb-3 flex flex-wrap gap-1.5">
                <span class="rounded bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold text-wc-accent">{{ t('client_progress.checkin_recent_badge_wellbeing', { value: checkin.bienestar }) }}</span>
                <span class="rounded bg-blue-500/10 px-2 py-0.5 text-[10px] font-bold text-blue-400">{{ t('client_progress.checkin_recent_badge_rpe', { value: checkin.rpe }) }}</span>
                <span class="rounded bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-500">{{ nutricionBadgeText(checkin.nutricion) }}</span>
                <span class="rounded bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-500">{{ t('client_progress.checkin_recent_badge_days', { value: checkin.dias_entrenados }) }}</span>
              </div>

              <!-- Comentario -->
              <p v-if="checkin.comentario" class="text-sm leading-relaxed text-wc-text-secondary">{{ checkin.comentario }}</p>

              <!-- Coach Reply -->
              <div v-if="checkin.coach_reply" class="mt-3 rounded-card border border-wc-accent/20 bg-wc-accent/5 p-3">
                <div class="mb-1 flex items-center gap-2">
                  <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                  <span class="text-[11px] font-semibold uppercase tracking-wide text-wc-accent">{{ t('client_progress.checkin_recent_coach_reply_label') }}</span>
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
                    <span class="font-display text-xl uppercase tracking-[0.25em] text-white/90">{{ t('client_progress.checkin_success_brand') }}</span>
                    <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
                  </div>

                  <h2 id="checkin-success-title" class="mb-2 font-sans text-2xl font-bold text-white">{{ t('client_progress.checkin_success_title') }}</h2>

                  <!-- Stats grid -->
                  <div class="my-5 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ lastDiasEntrenados }}</p>
                      <p class="mt-0.5 text-[11px] text-white/50">{{ t('client_progress.checkin_success_days_short') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ lastBienestar }}/5</p>
                      <p class="mt-0.5 text-[11px] text-white/50">{{ t('client_progress.checkin_success_wellbeing_short') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ t('client_progress.checkin_success_week_prefix') }}{{ currentWeekNum }}</p>
                      <p class="mt-0.5 text-[11px] text-white/50">{{ t('client_progress.checkin_success_week_short') }}</p>
                    </div>
                  </div>

                  <p class="mb-6 text-sm text-white/70">{{ t('client_progress.checkin_success_body') }}</p>

                  <button
                    @click="dismissSuccess"
                    class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg uppercase tracking-wider text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
                  >{{ t('client_progress.checkin_success_dismiss') }}</button>
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
                  <h3 class="font-display text-lg uppercase tracking-widest text-wc-text">{{ t('client_progress.checkin_tutorial_title') }}</h3>
                  <button @click="dismissTutorial" class="text-wc-text-tertiary transition-colors hover:text-wc-text" :aria-label="t('client_progress.checkin_tutorial_close_aria')" type="button">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>

                <!-- Step 1 -->
                <div v-show="tutorialStep === 1">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">1</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">{{ t('client_progress.checkin_tutorial_step1_title') }}</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">{{ t('client_progress.checkin_tutorial_step1_body') }}</p>
                    </div>
                  </div>
                </div>

                <!-- Step 2 -->
                <div v-show="tutorialStep === 2">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">2</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">{{ t('client_progress.checkin_tutorial_step2_title') }}</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">{{ t('client_progress.checkin_tutorial_step2_body') }}</p>
                    </div>
                  </div>
                </div>

                <!-- Step 3 -->
                <div v-show="tutorialStep === 3">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">3</div>
                    <div>
                      <p class="text-sm font-semibold text-wc-text">{{ t('client_progress.checkin_tutorial_step3_title') }}</p>
                      <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary">{{ t('client_progress.checkin_tutorial_step3_body') }}</p>
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
                  >{{ t('client_progress.checkin_tutorial_back') }}</button>
                  <button
                    v-if="tutorialStep < tutorialTotal"
                    @click="nextTutorialStep"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
                  >{{ t('client_progress.checkin_tutorial_next') }}</button>
                  <button
                    v-if="tutorialStep === tutorialTotal"
                    @click="dismissTutorial"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
                  >{{ t('client_progress.checkin_tutorial_start') }}</button>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>
      </Teleport>
      <!-- ===== /ONBOARDING TUTORIAL: CHECK-IN ===== -->
    </div>
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
