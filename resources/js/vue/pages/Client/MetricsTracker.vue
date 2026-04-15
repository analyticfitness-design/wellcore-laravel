<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const api = useApi();

// ── State ──
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const saveError = ref(null);
const showSuccess = ref(false);
const showConfetti = ref(false);
const showGuide = ref(false);
const lastPeso = ref('');

// ── Tutorial ──
const showTutorial = ref(false);
const tutorialStep = ref(1);
const TUTORIAL_TOTAL = 3;

// ── Metrics data from API ──
const currentWeight = ref(null);
const weightChange = ref(null);
const history = ref([]);
const chartData = ref([]);
const weightTrend = ref([]);
const weeklyCheckins = ref([]);
const latestComposition = ref(null);
const trainingVolume = ref([]);

// ── Form ──
const form = ref({
  peso: '',
  porcentajeMusculo: '',
  porcentajeGrasa: '',
  notas: '',
  chest: '',
  waist: '',
  hip: '',
  thigh: '',
  arm: '',
});
const formErrors = ref({});

// ── Chart refs (module-level, not reactive — avoids proxy overhead) ──
let weightChartInstance = null;
let checkinChartInstance = null;
let compositionChartInstance = null;
let trainingChartInstance = null;
let confettiTimer = null;
let successTimer = null;

// ── Template refs for canvas elements ──
const weightChartRef = ref(null);
const checkinChartRef = ref(null);
const compositionChartRef = ref(null);
const trainingChartRef = ref(null);

// ── Computed: has data flags ──
const hasWeight = computed(() => weightTrend.value.length > 0);
const hasCheckins = computed(() => weeklyCheckins.value.length > 0);
const hasComposition = computed(() => latestComposition.value !== null);
const hasTraining = computed(() => trainingVolume.value.length > 0);

// ── Chart.js global defaults ──
function setChartDefaults() {
  Chart.defaults.color = '#a3a3a3';
  Chart.defaults.borderColor = '#262626';
  Chart.defaults.font.family = "'Barlow', sans-serif";
  Chart.defaults.font.size = 11;
}

// ── Fetch metrics ──
async function fetchMetrics() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/metrics');
    const d = response.data;
    currentWeight.value = d.currentWeight;
    weightChange.value = d.weightChange;
    history.value = d.history || [];
    chartData.value = d.chartData || [];
    weightTrend.value = d.weightTrend || [];
    weeklyCheckins.value = d.weeklyCheckins || [];
    latestComposition.value = d.latestComposition || null;
    trainingVolume.value = d.trainingVolume || [];
    showTutorial.value = d.showTutorial || false;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar metricas';
  } finally {
    loading.value = false;
  }
}

// ── Date formatter ──
function formatDate(dateStr) {
  if (!dateStr) return '--';
  const d = new Date(dateStr);
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── Save metric ──
async function saveMetric() {
  saving.value = true;
  saveError.value = null;
  formErrors.value = {};

  try {
    await api.post('/api/v/client/metrics', {
      peso: form.value.peso || null,
      porcentaje_musculo: form.value.porcentajeMusculo || null,
      porcentaje_grasa: form.value.porcentajeGrasa || null,
      notas: form.value.notas || null,
      chest: form.value.chest || null,
      waist: form.value.waist || null,
      hip: form.value.hip || null,
      thigh: form.value.thigh || null,
      arm: form.value.arm || null,
    });

    // Capture peso for the overlay before resetting the form
    lastPeso.value = form.value.peso;

    // Reset form
    form.value = { peso: '', porcentajeMusculo: '', porcentajeGrasa: '', notas: '', chest: '', waist: '', hip: '', thigh: '', arm: '' };

    // Show achievement overlay with confetti
    showSuccess.value = true;
    showConfetti.value = true;
    clearTimeout(confettiTimer);
    confettiTimer = setTimeout(() => { showConfetti.value = false; }, 4000);

    // Refresh data
    await fetchMetrics();

    // Rebuild charts after data refresh
    await nextTick();
    createAllCharts();
  } catch (err) {
    if (err.response?.status === 422) {
      formErrors.value = err.response.data.errors || {};
      // Also set a general save error so the user sees feedback even if scrolled past the field
      const allMessages = Object.values(err.response.data.errors || {}).flat();
      if (allMessages.length) {
        saveError.value = allMessages.join(' ');
      }
    } else {
      saveError.value = err.response?.data?.message || 'Error al guardar el registro';
    }
  } finally {
    saving.value = false;
  }
}

function dismissSuccess() {
  showSuccess.value = false;
  showConfetti.value = false;
}

function dismissTutorial() {
  showTutorial.value = false;
}

// ── Chart bar height (CSS bar chart) ──
function getBarHeight(weight) {
  if (!chartData.value.length) return 50;
  const weights = chartData.value.map(e => Number(e.peso));
  const max = Math.max(...weights);
  const min = Math.min(...weights);
  const range = max - min || 1;
  return ((Number(weight) - min) / range) * 70 + 30;
}

// ── Chart.js creation functions ──
function createWeightChart() {
  if (!weightChartRef.value || !hasWeight.value) return;
  weightChartInstance?.destroy();

  weightChartInstance = new Chart(weightChartRef.value, {
    type: 'line',
    data: {
      labels: weightTrend.value.map(d => d.date),
      datasets: [{
        label: 'Peso (kg)',
        data: weightTrend.value.map(d => d.value),
        borderColor: '#DC2626',
        backgroundColor: 'rgba(220,38,38,0.08)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointBackgroundColor: '#DC2626',
        pointBorderColor: '#DC2626',
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.parsed.y} kg`
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
        y: { beginAtZero: false, grid: { color: '#262626' } }
      }
    }
  });
}

function createCheckinChart() {
  if (!checkinChartRef.value || !hasCheckins.value) return;
  checkinChartInstance?.destroy();

  const labels = weeklyCheckins.value.map(d => {
    const yw = String(d.week);
    const week = parseInt(yw.slice(4), 10);
    return `S${week}`;
  });

  checkinChartInstance = new Chart(checkinChartRef.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Check-ins',
        data: weeklyCheckins.value.map(d => d.cnt),
        backgroundColor: 'rgba(220,38,38,0.55)',
        borderColor: '#DC2626',
        borderWidth: 1,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, grid: { color: '#262626' }, ticks: { stepSize: 1 } }
      }
    }
  });
}

function createCompositionChart() {
  if (!compositionChartRef.value || !hasComposition.value) return;
  compositionChartInstance?.destroy();

  const comp = latestComposition.value;

  compositionChartInstance = new Chart(compositionChartRef.value, {
    type: 'doughnut',
    data: {
      labels: ['Grasa', 'Musculo', 'Otro'],
      datasets: [{
        data: [comp.grasa, comp.musculo, comp.otro],
        backgroundColor: ['#DC2626', '#3B82F6', '#525252'],
        borderColor: '#171717',
        borderWidth: 2,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: { padding: 12, boxWidth: 10 }
        },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.parsed}%`
          }
        }
      },
      cutout: '65%',
    }
  });
}

function createTrainingChart() {
  if (!trainingChartRef.value || !hasTraining.value) return;
  trainingChartInstance?.destroy();

  const labels = trainingVolume.value.map(d => {
    const yw = String(d.week);
    const week = parseInt(yw.slice(4), 10);
    return `S${week}`;
  });

  trainingChartInstance = new Chart(trainingChartRef.value, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Sesiones',
        data: trainingVolume.value.map(d => d.sessions),
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59,130,246,0.08)',
        fill: true,
        tension: 0.3,
        pointRadius: 4,
        pointBackgroundColor: '#3B82F6',
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          grid: { color: '#262626' },
          ticks: { stepSize: 1 }
        }
      }
    }
  });
}

function createAllCharts() {
  setChartDefaults();
  createWeightChart();
  createCheckinChart();
  createCompositionChart();
  createTrainingChart();
}

function destroyAllCharts() {
  weightChartInstance?.destroy();
  checkinChartInstance?.destroy();
  compositionChartInstance?.destroy();
  trainingChartInstance?.destroy();
  weightChartInstance = null;
  checkinChartInstance = null;
  compositionChartInstance = null;
  trainingChartInstance = null;
}

// ── Escape key handler for overlays ──
function onEscapeKey(e) {
  if (e.key === 'Escape') {
    if (showSuccess.value) dismissSuccess();
    if (showTutorial.value) dismissTutorial();
  }
}

// ── Lifecycle ──
onMounted(async () => {
  window.addEventListener('keydown', onEscapeKey);
  await fetchMetrics();
  await nextTick();
  createAllCharts();
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onEscapeKey);
  destroyAllCharts();
  clearTimeout(confettiTimer);
  clearTimeout(successTimer);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">METRICAS CORPORALES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra y monitorea tu composicion corporal</p>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
          <div v-for="i in 3" :key="i" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
        </div>
        <div class="h-64 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
        <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
          <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </div>
        <p class="mt-4 text-sm text-wc-text-secondary">{{ error }}</p>
        <button @click="fetchMetrics" class="mt-4 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
          Reintentar
        </button>
      </div>

      <!-- Main content -->
      <template v-else>
        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
          <!-- Current Weight -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Peso actual</p>
            <p class="mt-1 font-display text-3xl text-wc-accent">
              {{ currentWeight ? Number(currentWeight).toFixed(1) : '--' }}
              <span class="text-sm text-wc-text-secondary">kg</span>
            </p>
          </div>

          <!-- Monthly Change -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Cambio mensual</p>
            <p :class="[
              'mt-1 font-display text-3xl',
              weightChange !== null
                ? (weightChange > 0 ? 'text-amber-500' : weightChange < 0 ? 'text-emerald-500' : 'text-wc-text')
                : 'text-wc-text'
            ]">
              <template v-if="weightChange !== null">
                {{ weightChange > 0 ? '+' : '' }}{{ Number(weightChange).toFixed(1) }}
                <span class="text-sm text-wc-text-secondary">kg</span>
              </template>
              <template v-else>--</template>
            </p>
          </div>

          <!-- Goal Placeholder -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Objetivo</p>
            <p class="mt-1 font-display text-3xl text-wc-text-tertiary">
              -- <span class="text-base font-normal">kg</span>
            </p>
            <p class="mt-1 text-sm text-wc-text-tertiary">Consulta con tu coach</p>
          </div>
        </div>

        <!-- Log Form -->
        <form @submit.prevent="saveMetric" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
          <h2 class="mb-4 text-lg font-semibold text-wc-text">Nuevo registro</h2>

          <!-- Save error -->
          <div v-if="saveError" class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ saveError }}
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Peso -->
            <div>
              <label for="peso" class="mb-1 block text-sm font-medium text-wc-text">Peso (kg) <span class="text-wc-accent">*</span></label>
              <input
                type="number"
                id="peso"
                v-model="form.peso"
                step="0.1"
                min="20"
                max="300"
                placeholder="75.0"
                class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
              <p v-if="formErrors.peso" class="mt-1 text-xs text-red-500">{{ formErrors.peso[0] }}</p>
            </div>

            <!-- % Musculo -->
            <div>
              <label for="porcentajeMusculo" class="mb-1 block text-sm font-medium text-wc-text">% Musculo</label>
              <input
                type="number"
                id="porcentajeMusculo"
                v-model="form.porcentajeMusculo"
                step="0.1"
                min="0"
                max="100"
                placeholder="40.0"
                class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
              <p v-if="formErrors.porcentaje_musculo" class="mt-1 text-xs text-red-500">{{ formErrors.porcentaje_musculo[0] }}</p>
            </div>

            <!-- % Grasa -->
            <div>
              <label for="porcentajeGrasa" class="mb-1 block text-sm font-medium text-wc-text">% Grasa</label>
              <input
                type="number"
                id="porcentajeGrasa"
                v-model="form.porcentajeGrasa"
                step="0.1"
                min="0"
                max="100"
                placeholder="18.0"
                class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 font-data text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
              <p v-if="formErrors.porcentaje_grasa" class="mt-1 text-xs text-red-500">{{ formErrors.porcentaje_grasa[0] }}</p>
            </div>

            <!-- Notas -->
            <div>
              <label for="notas" class="mb-1 block text-sm font-medium text-wc-text">Notas</label>
              <input
                type="text"
                id="notas"
                v-model="form.notas"
                placeholder="En ayunas, post-entrenamiento..."
                class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
            </div>
          </div>

          <!-- Body Measurements Section -->
          <div class="mt-6 border-t border-wc-border pt-5">
            <div class="mb-4 flex items-center gap-3">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </div>
              <div>
                <h3 class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Mediciones corporales</h3>
                <p class="text-sm text-wc-text-tertiary">Mide con cinta metrica flexible, en la manana</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
              <div>
                <label for="chest" class="mb-1 block text-sm font-medium text-wc-text">Pecho (cm)</label>
                <input type="number" id="chest" v-model="form.chest" step="0.1" min="30" max="200" placeholder="95.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="waist" class="mb-1 block text-sm font-medium text-wc-text">Cintura (cm)</label>
                <input type="number" id="waist" v-model="form.waist" step="0.1" min="30" max="200" placeholder="80.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="hip" class="mb-1 block text-sm font-medium text-wc-text">Cadera (cm)</label>
                <input type="number" id="hip" v-model="form.hip" step="0.1" min="30" max="200" placeholder="95.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="thigh" class="mb-1 block text-sm font-medium text-wc-text">Muslo (cm)</label>
                <input type="number" id="thigh" v-model="form.thigh" step="0.1" min="20" max="100" placeholder="55.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="arm" class="mb-1 block text-sm font-medium text-wc-text">Brazo (cm)</label>
                <input type="number" id="arm" v-model="form.arm" step="0.1" min="15" max="60" placeholder="32.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
            </div>

            <!-- Measurement guide -->
            <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
              <button @click="showGuide = !showGuide" type="button" class="flex w-full items-center justify-between text-left">
                <span class="text-sm font-semibold text-amber-400">Como tomar las mediciones correctamente</span>
                <svg :class="['h-4 w-4 text-amber-400 transition-transform', showGuide && 'rotate-180']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
              </button>
              <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="max-h-0 opacity-0"
                enter-to-class="max-h-96 opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="max-h-96 opacity-100"
                leave-to-class="max-h-0 opacity-0"
              >
                <div v-show="showGuide" class="mt-3 space-y-2 overflow-hidden text-sm text-wc-text-secondary">
                  <p><strong class="text-wc-text">Pecho:</strong> Cinta alrededor del torso a la altura de los pezones. Brazos relajados a los lados. No inflar el pecho.</p>
                  <p><strong class="text-wc-text">Cintura:</strong> En el punto mas estrecho del abdomen, generalmente 2-3 cm arriba del ombligo. Medir al exhalar normalmente.</p>
                  <p><strong class="text-wc-text">Cadera:</strong> En el punto mas ancho de los gluteos. Pies juntos, de pie recto.</p>
                  <p><strong class="text-wc-text">Muslo:</strong> En el punto mas grueso del muslo, generalmente justo debajo del gluteo. Pierna relajada sin flexionar.</p>
                  <p><strong class="text-wc-text">Brazo:</strong> Brazo relajado a un lado. Medir en el punto mas grueso del biceps sin flexionar.</p>
                  <p class="mt-2 text-amber-400/70">Medir siempre en las mismas condiciones: por la manana, antes de comer, mismo lado del cuerpo.</p>
                </div>
              </Transition>
            </div>
          </div>

          <div class="mt-5">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg disabled:opacity-50"
            >
              <span v-if="!saving">Guardar registro</span>
              <span v-else class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Guardando...
              </span>
            </button>
          </div>
        </form>

        <!-- Weight CSS Bar Chart (mini) -->
        <div v-if="chartData.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="mb-4 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Tendencia de peso</h2>

          <div class="flex items-end gap-1 sm:gap-2" style="height: 120px;">
            <div
              v-for="(entry, idx) in chartData"
              :key="idx"
              class="group relative flex flex-1 flex-col items-center justify-end"
              style="height: 100%;"
            >
              <div class="pointer-events-none absolute -top-8 z-10 hidden rounded bg-wc-bg-secondary px-2 py-1 text-xs font-medium text-wc-text shadow-lg group-hover:block">
                {{ Number(entry.peso).toFixed(1) }} kg
              </div>
              <div
                class="w-full rounded-t bg-wc-accent/80 transition-all group-hover:bg-wc-accent"
                :style="{ height: getBarHeight(entry.peso) + '%' }"
              ></div>
              <span class="mt-1 text-[10px] text-wc-text-tertiary">{{ formatDate(entry.date) }}</span>
            </div>
          </div>

          <div class="mt-2 flex justify-between text-xs text-wc-text-tertiary">
            <span>Min: {{ Math.min(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
            <span>Max: {{ Math.max(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
          </div>
        </div>

        <!-- Chart.js Charts Section (2x2 grid) -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <!-- 1. Weight Trend (Line, 90 days) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Peso Corporal</h3>
            <p class="text-sm text-wc-text-tertiary">Ultimos 90 dias</p>
            <div class="relative mt-4" style="height: 180px">
              <canvas ref="weightChartRef"></canvas>
              <p v-if="!hasWeight" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                Sin datos de peso aun
              </p>
            </div>
          </div>

          <!-- 2. Weekly Check-ins (Bar, 12 weeks) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Check-ins Semanales</h3>
            <p class="text-sm text-wc-text-tertiary">Ultimas 12 semanas</p>
            <div class="relative mt-4" style="height: 180px">
              <canvas ref="checkinChartRef"></canvas>
              <p v-if="!hasCheckins" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                Sin check-ins recientes
              </p>
            </div>
          </div>

          <!-- 3. Body Composition (Doughnut) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Composicion Corporal</h3>
            <p class="text-sm text-wc-text-tertiary">Ultima medicion</p>
            <div class="relative mx-auto mt-4 flex items-center justify-center" style="height: 180px; max-width: 260px">
              <canvas ref="compositionChartRef"></canvas>
              <p v-if="!hasComposition" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                Sin datos de composicion
              </p>
            </div>
          </div>

          <!-- 4. Training Volume (Line, 12 weeks) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h3 class="font-display text-lg tracking-wide text-wc-text">Volumen de Entrenamiento</h3>
            <p class="text-sm text-wc-text-tertiary">Sesiones por semana</p>
            <div class="relative mt-4" style="height: 180px">
              <canvas ref="trainingChartRef"></canvas>
              <p v-if="!hasTraining" class="absolute inset-0 flex items-center justify-center text-sm text-wc-text-tertiary">
                Sin sesiones registradas
              </p>
            </div>
          </div>
        </div>

        <!-- History Table -->
        <div v-if="history.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div class="border-b border-wc-border px-5 py-3">
            <h2 class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Historial</h2>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-wc-border text-left">
                  <th class="px-3 py-3 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary sm:px-5">Fecha</th>
                  <th class="px-3 py-3 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary sm:px-5">Peso</th>
                  <th class="px-3 py-3 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary sm:px-5">Musc%</th>
                  <th class="px-3 py-3 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary sm:px-5">Grasa%</th>
                  <th class="hidden px-3 py-3 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary sm:table-cell sm:px-5">Notas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr
                  v-for="(entry, idx) in history"
                  :key="idx"
                  class="hover:bg-wc-bg-secondary/50"
                >
                  <td class="whitespace-nowrap px-3 py-3 text-sm text-wc-text-tertiary sm:px-5">
                    {{ formatDate(entry.date || entry.log_date) }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-3 font-data text-sm text-wc-text-secondary sm:px-5">
                    {{ entry.peso ? Number(entry.peso).toFixed(1) + ' kg' : '--' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-3 font-data text-sm text-wc-text-secondary sm:px-5">
                    {{ entry.porcentaje_musculo ? Number(entry.porcentaje_musculo).toFixed(1) + '%' : '--' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-3 font-data text-sm text-wc-text-secondary sm:px-5">
                    {{ entry.porcentaje_grasa ? Number(entry.porcentaje_grasa).toFixed(1) + '%' : '--' }}
                  </td>
                  <td class="hidden max-w-[200px] truncate px-3 py-3 text-sm text-wc-text-tertiary sm:table-cell sm:px-5"
                      :title="entry.notas || ''">
                    {{ entry.notas || '--' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <!-- ===== ACHIEVEMENT OVERLAY: METRICAS ===== -->
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
        @click.self="dismissSuccess"
      >
        <!-- Confetti -->
        <div v-if="showConfetti" class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
          <div class="wc-confetti" style="left:8%;background:#DC2626;animation:wc-confetti-fall 2.8s ease-in forwards 0.1s;"></div>
          <div class="wc-confetti" style="left:22%;background:#F59E0B;animation:wc-confetti-fall 3.2s ease-in forwards 0.3s;border-radius:50%;"></div>
          <div class="wc-confetti" style="left:38%;background:#10B981;animation:wc-confetti-fall 2.5s ease-in forwards 0s;"></div>
          <div class="wc-confetti" style="left:52%;background:#DC2626;animation:wc-confetti-fall 3s ease-in forwards 0.5s;border-radius:50%;"></div>
          <div class="wc-confetti" style="left:65%;background:#8B5CF6;animation:wc-confetti-fall 2.7s ease-in forwards 0.2s;"></div>
          <div class="wc-confetti" style="left:78%;background:#F59E0B;animation:wc-confetti-fall 3.4s ease-in forwards 0.4s;border-radius:50%;"></div>
          <div class="wc-confetti" style="left:90%;background:#10B981;animation:wc-confetti-fall 2.6s ease-in forwards 0.15s;"></div>
          <div class="wc-confetti" style="left:45%;background:#8B5CF6;animation:wc-confetti-fall 3.1s ease-in forwards 0.6s;"></div>
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
            aria-labelledby="metrics-success-title"
          >
            <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);" aria-hidden="true"></div>

            <div class="relative z-10 p-8">
              <span class="wc-emoji-bounce mb-4 block text-6xl" aria-hidden="true">&#x1F4CA;</span>

              <div class="mb-3 flex items-center justify-center gap-2">
                <span class="font-display text-xl tracking-[0.25em] text-white/90">WELLCORE</span>
                <span class="h-2 w-2 rounded-full bg-white/30" aria-hidden="true"></span>
              </div>

              <h2 id="metrics-success-title" class="mb-2 font-sans text-2xl font-bold text-white">Metricas guardadas!</h2>

              <div v-if="lastPeso && Number(lastPeso) > 0" class="my-5 rounded-xl border border-white/10 bg-white/[0.06] px-5 py-4">
                <p class="font-data text-3xl font-bold text-white">{{ Number(lastPeso).toFixed(1) }} <span class="text-lg font-normal text-white/50">kg</span></p>
                <p class="mt-0.5 text-xs text-white/50">peso registrado</p>
              </div>
              <div v-else class="my-5"></div>

              <p class="mb-6 text-sm text-white/70">El seguimiento consistente es la base del progreso.</p>

              <button
                @click="dismissSuccess"
                class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black"
              >
                PERFECTO!
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
    <!-- ===== /ACHIEVEMENT OVERLAY ===== -->

    <!-- ===== ONBOARDING TUTORIAL: METRICAS ===== -->
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
      >
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
          <!-- Header -->
          <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-lg tracking-widest text-wc-text">TUS METRICAS</h3>
            <button @click="dismissTutorial" class="text-wc-text-tertiary transition-colors hover:text-wc-text" aria-label="Cerrar">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Step 1 -->
          <div v-show="tutorialStep === 1">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">1</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">Registra tu peso</p>
                <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">Pesate en ayunas, despues de ir al bano y antes de desayunar. Siempre a la misma hora para tener datos comparables semana a semana.</p>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div v-show="tutorialStep === 2">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">2</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">Las fluctuaciones son normales</p>
                <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">El peso puede variar 1-3 kg en un dia por agua, comida y sal. Lo que importa es la tendencia de semanas, no el numero de un dia especifico.</p>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div v-show="tutorialStep === 3">
            <div class="flex items-start gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">3</div>
              <div>
                <p class="text-sm font-semibold text-wc-text">El peso no es todo</p>
                <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">La escala no distingue musculo de grasa. Registra tambien tus medidas y fotos de progreso — la transformacion visual siempre supera a los numeros.</p>
              </div>
            </div>
          </div>

          <!-- Step indicators -->
          <div class="mt-4 flex justify-center gap-1.5">
            <div
              v-for="i in TUTORIAL_TOTAL"
              :key="i"
              class="h-1.5 rounded-full transition-all"
              :class="i === tutorialStep ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-bg-tertiary'"
            ></div>
          </div>

          <!-- Navigation buttons -->
          <div class="mt-5 flex gap-3">
            <button
              v-show="tutorialStep > 1"
              @click="tutorialStep--"
              type="button"
              class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
            >Atras</button>
            <button
              v-show="tutorialStep < TUTORIAL_TOTAL"
              @click="tutorialStep++"
              type="button"
              class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
            >Siguiente</button>
            <button
              v-show="tutorialStep === TUTORIAL_TOTAL"
              @click="dismissTutorial"
              type="button"
              class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover"
            >Entendido!</button>
          </div>
        </div>
      </div>
    </Transition>
    <!-- ===== /ONBOARDING TUTORIAL ===== -->
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
