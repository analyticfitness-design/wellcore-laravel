<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// ─── Bienestar options (static, module-level) ─────────────────────
const bienestarLabels = [
  { value: 1, label: 'Muy mal' },
  { value: 2, label: 'Mal' },
  { value: 3, label: 'Normal' },
  { value: 4, label: 'Bien' },
  { value: 5, label: 'Muy bien' },
];

// ─── Confetti pieces (static) ─────────────────────────────────────
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

// Form fields
const bienestar = ref(null);
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
  const months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
  return now.getDate() + ' de ' + months[now.getMonth()] + ', ' + now.getFullYear();
});

// Last submitted data for success overlay
const lastBienestar = ref(0);
const lastDiasEntrenados = ref(0);

function setBienestar(value) {
  bienestar.value = value;
}

// ─── Confetti control ─────────────────────────────────────────────
watch(showSuccess, (v) => {
  if (v) {
    showConfetti.value = true;
    confettiTimer = setTimeout(() => { showConfetti.value = false; }, 4000);
  } else {
    showConfetti.value = false;
    clearTimeout(confettiTimer);
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
  formErrors.value = {};

  // Client-side validation
  if (!bienestar.value) {
    formErrors.value.bienestar = 'Selecciona tu nivel de bienestar';
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

    // Reset form
    bienestar.value = null;
    diasEntrenados.value = 0;
    nutricion.value = 'Si';
    rpe.value = 5;
    comentario.value = '';

    // Refetch history
    await fetchCheckin();
  } catch (err) {
    if (err.response?.status === 422) {
      // Handle both Laravel validation errors and custom error messages
      if (err.response.data.errors) {
        const errors = err.response.data.errors;
        formErrors.value = {};
        for (const key in errors) {
          formErrors.value[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        }
      } else if (err.response.data.error) {
        formErrors.value.submit = err.response.data.error;
      }
    } else if (err.response?.data?.message) {
      formErrors.value.submit = err.response.data.message;
    } else {
      formErrors.value.submit = 'Error al enviar el check-in';
    }
  } finally {
    submitting.value = false;
  }
}

function dismissSuccess() {
  showSuccess.value = false;
}

// ─── Lifecycle ────────────────────────────────────────────────────
onMounted(fetchCheckin);

onBeforeUnmount(() => {
  clearTimeout(confettiTimer);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Title -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">CHECK-IN SEMANAL</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
          {{ currentWeekLabel }} &middot; {{ currentDateLabel }}
        </p>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-20 rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
          <div class="h-96 rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="fetchCheckin" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
      </div>

      <template v-else>
        <!-- Day restriction banner -->
        <div v-if="!isCheckinAvailable" class="flex items-start gap-4 rounded-xl border border-wc-accent/30 bg-wc-accent/10 px-5 py-4">
          <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/20">
            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-wc-accent">Check-in no disponible hoy</p>
            <p class="mt-0.5 text-sm text-wc-text-secondary">
              El check-in semanal estara disponible el proximo <span class="font-semibold text-wc-text">viernes o sabado</span>.
              Sigue entrenando -- la consistencia es tu superpoder!
            </p>
          </div>
        </div>

        <!-- Top-level submit error -->
        <p v-if="formErrors.submit" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">{{ formErrors.submit }}</p>

        <!-- Form -->
        <form @submit.prevent="submitCheckin" class="space-y-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">

          <!-- Bienestar (1-5) -->
          <div>
            <label class="mb-3 block text-sm font-medium text-wc-text">Bienestar general</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="opt in bienestarLabels"
                :key="opt.value"
                type="button"
                @click="setBienestar(opt.value)"
                :class="[
                  'flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition-all',
                  bienestar === opt.value
                    ? 'border-wc-accent bg-wc-accent text-white'
                    : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-text-tertiary'
                ]"
              >
                <span class="font-data">{{ opt.value }}</span>
                <span>{{ opt.label }}</span>
              </button>
            </div>
            <p v-if="formErrors.bienestar" class="mt-1 text-xs text-red-500">{{ formErrors.bienestar }}</p>
          </div>

          <!-- Dias Entrenados -->
          <div>
            <label for="diasEntrenados" class="mb-2 block text-sm font-medium text-wc-text">Dias entrenados esta semana</label>
            <div class="flex items-center gap-3">
              <input
                v-model.number="diasEntrenados"
                type="number"
                id="diasEntrenados"
                min="0"
                max="7"
                class="w-24 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-lg text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              />
              <span class="text-sm text-wc-text-tertiary">de 7 dias</span>
            </div>
            <p v-if="formErrors.dias_entrenados" class="mt-1 text-xs text-red-500">{{ formErrors.dias_entrenados }}</p>
          </div>

          <!-- Nutricion -->
          <div>
            <label for="nutricion" class="mb-2 block text-sm font-medium text-wc-text">Nutricion</label>
            <select
              v-model="nutricion"
              id="nutricion"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            >
              <option value="Si">Si, la segui bien</option>
              <option value="Parcial">Parcialmente</option>
              <option value="No">No la segui</option>
            </select>
            <p v-if="formErrors.nutricion" class="mt-1 text-xs text-red-500">{{ formErrors.nutricion }}</p>
          </div>

          <!-- RPE Slider -->
          <div>
            <label for="rpe" class="mb-2 block text-sm font-medium text-wc-text">
              RPE promedio
              <span class="ml-2 font-data text-lg font-semibold text-wc-accent">{{ rpe }}</span>
            </label>
            <input
              v-model.number="rpe"
              type="range"
              id="rpe"
              min="1"
              max="10"
              class="w-full accent-wc-accent"
            />
            <div class="mt-1 flex justify-between text-xs text-wc-text-tertiary">
              <span>1 - Muy facil</span>
              <span>10 - Maximo esfuerzo</span>
            </div>
            <p v-if="formErrors.rpe" class="mt-1 text-xs text-red-500">{{ formErrors.rpe }}</p>
          </div>

          <!-- Comentario -->
          <div>
            <label for="comentario" class="mb-2 block text-sm font-medium text-wc-text">Comentario para tu coach</label>
            <textarea
              v-model="comentario"
              id="comentario"
              rows="3"
              placeholder="Como te sentiste esta semana? Alguna molestia o logro?"
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            ></textarea>
            <p v-if="formErrors.comentario" class="mt-1 text-xs text-red-500">{{ formErrors.comentario }}</p>
          </div>

          <!-- Duplicate / inline submit error (inside form) -->
          <div v-if="formErrors.submit" class="flex items-center gap-3 rounded-xl border border-wc-accent/30 bg-wc-accent/10 p-4">
            <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <p class="text-sm font-medium text-wc-accent">{{ formErrors.submit }}</p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="!isCheckinAvailable || submitting"
            :title="!isCheckinAvailable ? 'Solo disponible viernes y sabado' : undefined"
            :aria-disabled="!isCheckinAvailable || undefined"
            class="btn-press w-full rounded-lg bg-wc-accent px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <template v-if="!isCheckinAvailable">
              <span class="inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                Disponible el viernes
              </span>
            </template>
            <template v-else-if="submitting">
              <span class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Enviando...
              </span>
            </template>
            <template v-else>Enviar Check-in</template>
          </button>
        </form>

        <!-- Recent Check-ins -->
        <div v-if="recentCheckins.length > 0">
          <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">CHECK-INS ANTERIORES</h2>
          <div class="space-y-3">
            <div v-for="(checkin, cIdx) in recentCheckins" :key="checkin.id || cIdx" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <!-- Header -->
              <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="font-data text-sm font-semibold text-wc-text">{{ checkin.week_label }}</span>
                  <span class="text-xs text-wc-text-tertiary">{{ checkin.checkin_date }}</span>
                </div>
                <span v-if="checkin.coach_reply" class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-500">Respondido</span>
              </div>

              <!-- Metrics Row -->
              <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div>
                  <p class="text-xs text-wc-text-tertiary">Bienestar</p>
                  <p class="font-data text-sm font-semibold text-wc-text">{{ checkin.bienestar }}/5</p>
                </div>
                <div>
                  <p class="text-xs text-wc-text-tertiary">Dias</p>
                  <p class="font-data text-sm font-semibold text-wc-text">{{ checkin.dias_entrenados }}/7</p>
                </div>
                <div>
                  <p class="text-xs text-wc-text-tertiary">Nutricion</p>
                  <p class="text-sm font-medium capitalize text-wc-text">{{ checkin.nutricion }}</p>
                </div>
                <div>
                  <p class="text-xs text-wc-text-tertiary">RPE</p>
                  <p class="font-data text-sm font-semibold text-wc-text">{{ checkin.rpe }}/10</p>
                </div>
              </div>

              <!-- Comentario -->
              <p v-if="checkin.comentario" class="mt-3 text-sm text-wc-text-secondary">{{ checkin.comentario }}</p>

              <!-- Coach Reply -->
              <div v-if="checkin.coach_reply" class="mt-3 rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-3">
                <div class="mb-1 flex items-center gap-2">
                  <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                  <span class="text-xs font-medium text-wc-accent">Respuesta del coach</span>
                  <span v-if="checkin.replied_at" class="text-xs text-wc-text-tertiary">{{ checkin.replied_at }}</span>
                </div>
                <p class="text-sm text-wc-text">{{ checkin.coach_reply }}</p>
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
                  <span class="wc-emoji-bounce block text-6xl mb-4" aria-hidden="true">&#x2705;</span>

                  <div class="mb-3 flex items-center justify-center gap-2">
                    <span class="font-display text-xl tracking-[0.25em] text-white/90">WELLCORE</span>
                    <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
                  </div>

                  <h2 id="checkin-success-title" class="font-sans text-2xl font-bold text-white mb-2">Check-in enviado!</h2>

                  <!-- Stats grid -->
                  <div class="my-5 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-3">
                      <p class="font-data text-2xl font-bold text-white">{{ lastDiasEntrenados }}</p>
                      <p class="mt-0.5 text-[11px] text-white/50">dias entrend.</p>
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

                  <p class="mb-6 text-sm text-white/70">Tu coach revisara tu reporte esta semana. Sigue asi!</p>

                  <button
                    @click="dismissSuccess"
                    class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
                  >PERFECTO!</button>
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
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-display text-lg tracking-widest text-wc-text">CHECK-IN SEMANAL</h3>
                  <button @click="dismissTutorial" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar" type="button">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>

                <!-- Step 1 -->
                <div v-show="tutorialStep === 1">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                    <div>
                      <p class="font-semibold text-wc-text text-sm">Que es el check-in?</p>
                      <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Es tu reporte semanal al coach. Con esta informacion tu coach ajusta tu plan de entrenamiento y nutricion para maximizar tus resultados semana a semana.</p>
                    </div>
                  </div>
                </div>

                <!-- Step 2 -->
                <div v-show="tutorialStep === 2">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                    <div>
                      <p class="font-semibold text-wc-text text-sm">Se honesto</p>
                      <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">No hay respuestas malas. Si tuviste una semana dificil, dilo. Tu coach solo puede ayudarte si conoce tu realidad -- no la version perfecta.</p>
                    </div>
                  </div>
                </div>

                <!-- Step 3 -->
                <div v-show="tutorialStep === 3">
                  <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                    <div>
                      <p class="font-semibold text-wc-text text-sm">Hazlo cada semana</p>
                      <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Los clientes que completan su check-in semanalmente progresan 3x mas rapido. El seguimiento constante es lo que diferencia los resultados promedio de los extraordinarios.</p>
                    </div>
                  </div>
                </div>

                <!-- Step indicators -->
                <div class="mt-4 flex justify-center gap-1.5">
                  <div
                    v-for="i in tutorialTotal"
                    :key="i"
                    class="h-1.5 rounded-full transition-all"
                    :class="i === tutorialStep ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"
                  ></div>
                </div>

                <!-- Navigation buttons -->
                <div class="mt-5 flex gap-3">
                  <button
                    v-if="tutorialStep > 1"
                    @click="prevTutorialStep"
                    type="button"
                    class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                  >Atras</button>
                  <button
                    v-if="tutorialStep < tutorialTotal"
                    @click="nextTutorialStep"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                  >Siguiente</button>
                  <button
                    v-if="tutorialStep === tutorialTotal"
                    @click="dismissTutorial"
                    type="button"
                    class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                  >Listo, comenzar!</button>
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
</style>
