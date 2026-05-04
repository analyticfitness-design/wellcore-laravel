<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useApi } from '../../composables/useApi';
import { useCancellableFetch } from '../../composables/useCancellableFetch';
import ClientLayout from '../../layouts/ClientLayout.vue';
import { Chart, registerables } from 'chart.js';
import MetricsHero from '../../components/metrics/MetricsHero.vue';
import WcStatCard from '../../components/ui/wellcore/WcStatCard.vue';

Chart.register(...registerables);

const api = useApi();
const { getSignal: getMetricsSignal } = useCancellableFetch();

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
  const signal = getMetricsSignal();
  try {
    const response = await api.get('/api/v/client/metrics', { signal });
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
    if (err.name !== 'CanceledError' && err.name !== 'AbortError') {
      error.value = err.response?.data?.message || 'Error al cargar metricas';
    }
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
  if (saving.value) return;
  saveError.value = null;
  formErrors.value = {};

  // Client-side validation: peso is required
  if (!form.value.peso) {
    formErrors.value = { peso: ['El peso es obligatorio.'] };
    return;
  }

  saving.value = true;

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
    <!-- ═══════════ LOADING STATE ═══════════ -->
    <div v-if="loading" class="wc-shell wc-shell--metrics">
      <main class="scroll">
        <div style="grid-column:span 12; padding:var(--s20);">
          <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px;">
              <div v-for="i in 4" :key="i" style="height:96px; border-radius:16px; background:var(--wc-bg3); animation:pulse 1.5s ease-in-out infinite;"></div>
            </div>
            <div style="height:260px; border-radius:16px; background:var(--wc-bg3); animation:pulse 1.5s ease-in-out infinite;"></div>
            <div style="height:180px; border-radius:16px; background:var(--wc-bg3); animation:pulse 1.5s ease-in-out infinite;"></div>
          </div>
        </div>
      </main>
    </div>

    <!-- ═══════════ ERROR STATE ═══════════ -->
    <div v-else-if="error" class="wc-shell wc-shell--metrics">
      <main class="scroll">
        <div style="grid-column:span 12; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px var(--s20);">
          <div style="width:64px; height:64px; border-radius:16px; background:rgba(220,38,38,.10); display:flex; align-items:center; justify-content:center;">
            <svg style="width:32px; height:32px; color:var(--wc-accent);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
          <p style="margin-top:16px; font-size:14px; color:var(--wc-text-2);">{{ error }}</p>
          <button @click="fetchMetrics" class="btn btn-accent" style="margin-top:16px;">Reintentar</button>
        </div>
      </main>
    </div>

    <!-- ═══════════ MAIN CONTENT ═══════════ -->
    <div v-else class="wc-shell wc-shell--metrics">
      <main class="scroll">

        <!-- Hero -->
        <MetricsHero
          :current-weight="currentWeight"
          :weight-change="weightChange"
        />

        <!-- Stats Grid -->
        <div class="stats-grid section wc-card-metrics-stats" :style="{ animationDelay: '180ms' }">
          <WcStatCard
            variant="red"
            label="Peso actual"
            :value="currentWeight ? Number(currentWeight).toFixed(1) : '--'"
            unit="kg"
            sub="último registro"
          />
          <WcStatCard
            :variant="weightChange !== null ? (weightChange > 0 ? 'amber' : 'green') : 'red'"
            label="Cambio mensual"
            :value="weightChange !== null ? (weightChange > 0 ? '+' : '') + Number(weightChange).toFixed(1) : '--'"
            unit="kg"
            sub="este mes"
          />
          <WcStatCard
            variant="purple"
            label="Objetivo"
            value="--"
            unit="kg"
            sub="Consulta al coach"
          />
          <WcStatCard
            variant="green"
            label="Registros"
            :value="history.length"
            sub="historial total"
          />
        </div>

        <!-- Weight Chart (primary, wide) -->
        <section class="card section wc-card-metrics-weight" :style="{ animationDelay: '220ms' }">
          <div class="card-head">
            <div class="card-head-left">
              <span class="card-title">Peso Corporal</span>
            </div>
            <span class="card-meta">Últimos 90 días</span>
          </div>
          <div class="metrics-chart-wrap" style="position:relative;">
            <canvas ref="weightChartRef"></canvas>
            <p
              v-if="!hasWeight"
              style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--wc-text-3);"
            >
              Sin datos de peso aún
            </p>
          </div>
        </section>

        <!-- Analysis Charts (2x2 grid) -->
        <section class="card section wc-card-metrics-charts" :style="{ animationDelay: '260ms' }">
          <div class="card-head">
            <div class="card-head-left">
              <span class="card-title">Análisis</span>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:1px; background:var(--wc-border);">
            <!-- Check-ins Semanales -->
            <div style="background:var(--wc-bg2); padding:16px 20px;">
              <p style="font:600 12px/1 var(--fd); text-transform:uppercase; letter-spacing:.06em; color:var(--wc-text);">Check-ins Semanales</p>
              <p style="font-size:11px; color:var(--wc-text-3); margin-top:2px;">Últimas 12 semanas</p>
              <div style="position:relative; margin-top:14px; height:180px;">
                <canvas ref="checkinChartRef"></canvas>
                <p
                  v-if="!hasCheckins"
                  style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--wc-text-3);"
                >
                  Sin check-ins recientes
                </p>
              </div>
            </div>

            <!-- Composición Corporal -->
            <div style="background:var(--wc-bg2); padding:16px 20px;">
              <p style="font:600 12px/1 var(--fd); text-transform:uppercase; letter-spacing:.06em; color:var(--wc-text);">Composición Corporal</p>
              <p style="font-size:11px; color:var(--wc-text-3); margin-top:2px;">Última medición</p>
              <div style="position:relative; margin-top:14px; height:180px; display:flex; align-items:center; justify-content:center;">
                <canvas ref="compositionChartRef"></canvas>
                <p
                  v-if="!hasComposition"
                  style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--wc-text-3);"
                >
                  Sin datos de composición
                </p>
              </div>
            </div>

            <!-- Volumen de Entrenamiento -->
            <div style="background:var(--wc-bg2); padding:16px 20px; grid-column:span 2;">
              <p style="font:600 12px/1 var(--fd); text-transform:uppercase; letter-spacing:.06em; color:var(--wc-text);">Volumen de Entrenamiento</p>
              <p style="font-size:11px; color:var(--wc-text-3); margin-top:2px;">Sesiones por semana</p>
              <div style="position:relative; margin-top:14px; height:180px;">
                <canvas ref="trainingChartRef"></canvas>
                <p
                  v-if="!hasTraining"
                  style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:13px; color:var(--wc-text-3);"
                >
                  Sin sesiones registradas
                </p>
              </div>
            </div>

            <!-- Tendencia de Peso (CSS bar chart mini) -->
            <div v-if="chartData.length > 0" style="background:var(--wc-bg2); padding:16px 20px; grid-column:span 2;">
              <p style="font:600 12px/1 var(--fd); text-transform:uppercase; letter-spacing:.06em; color:var(--wc-text-3); margin-bottom:12px;">Tendencia de peso</p>
              <div style="display:flex; align-items:flex-end; gap:4px; height:120px;">
                <div
                  v-for="(entry, idx) in chartData"
                  :key="idx"
                  style="position:relative; flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;"
                  class="group"
                >
                  <div
                    style="width:100%; border-radius:4px 4px 0 0; background:rgba(220,38,38,0.6); transition:background 200ms;"
                    :style="{ height: getBarHeight(entry.peso) + '%' }"
                  ></div>
                  <span style="margin-top:4px; font-size:9px; color:var(--wc-text-3);">{{ formatDate(entry.date) }}</span>
                </div>
              </div>
              <div style="margin-top:8px; display:flex; justify-content:space-between; font-size:11px; color:var(--wc-text-3); font-family:var(--fm);">
                <span>Min: {{ Math.min(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
                <span>Max: {{ Math.max(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Log Form -->
        <section class="card section wc-card-metrics-form" :style="{ animationDelay: '300ms' }">
          <div class="card-head">
            <div class="card-head-left">
              <span class="card-title">Nuevo Registro</span>
            </div>
          </div>

          <form @submit.prevent="saveMetric" class="metrics-form">

            <!-- Save error banner -->
            <div
              v-if="saveError"
              style="margin-bottom:16px; border-radius:10px; border:1px solid rgba(220,38,38,.30); background:rgba(220,38,38,.08); padding:12px 16px; font-size:13px; color:#FCA5A5;"
            >
              {{ saveError }}
            </div>

            <!-- Primary fields grid -->
            <div class="metrics-form-grid">
              <!-- Peso -->
              <div class="metrics-field">
                <label for="peso" class="metrics-label">
                  Peso (kg) <span style="color:var(--wc-accent);">*</span>
                </label>
                <input
                  type="number"
                  id="peso"
                  v-model.number="form.peso"
                  step="0.1"
                  min="20"
                  max="300"
                  placeholder="75.0"
                  class="metrics-input"
                >
                <p v-if="formErrors.peso" class="metrics-error">{{ formErrors.peso[0] }}</p>
              </div>

              <!-- % Musculo -->
              <div class="metrics-field">
                <label for="porcentajeMusculo" class="metrics-label">% Músculo</label>
                <input
                  type="number"
                  id="porcentajeMusculo"
                  v-model.number="form.porcentajeMusculo"
                  step="0.1"
                  min="0"
                  max="100"
                  placeholder="40.0"
                  class="metrics-input"
                >
                <p v-if="formErrors.porcentaje_musculo" class="metrics-error">{{ formErrors.porcentaje_musculo[0] }}</p>
              </div>

              <!-- % Grasa -->
              <div class="metrics-field">
                <label for="porcentajeGrasa" class="metrics-label">% Grasa</label>
                <input
                  type="number"
                  id="porcentajeGrasa"
                  v-model.number="form.porcentajeGrasa"
                  step="0.1"
                  min="0"
                  max="100"
                  placeholder="18.0"
                  class="metrics-input"
                >
                <p v-if="formErrors.porcentaje_grasa" class="metrics-error">{{ formErrors.porcentaje_grasa[0] }}</p>
              </div>

              <!-- Notas -->
              <div class="metrics-field">
                <label for="notas" class="metrics-label">Notas</label>
                <input
                  type="text"
                  id="notas"
                  v-model="form.notas"
                  placeholder="En ayunas, post-entrenamiento..."
                  class="metrics-input"
                >
              </div>
            </div>

            <!-- Body Measurements Section -->
            <div style="margin-top:24px; border-top:1px solid var(--wc-border); padding-top:20px;">
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                <div style="width:32px; height:32px; flex-shrink:0; border-radius:10px; background:rgba(220,38,38,.10); display:flex; align-items:center; justify-content:center;">
                  <svg style="width:16px; height:16px; color:var(--wc-accent);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                </div>
                <div>
                  <p style="font:600 11px/1 var(--fs); letter-spacing:.06em; text-transform:uppercase; color:var(--wc-text-2);">Mediciones corporales</p>
                  <p style="font-size:12px; color:var(--wc-text-3); margin-top:2px;">Mide con cinta métrica flexible, en la mañana</p>
                </div>
              </div>

              <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px;">
                <!-- Pecho -->
                <div class="metrics-field">
                  <label for="chest" class="metrics-label">Pecho (cm)</label>
                  <input type="number" id="chest" v-model.number="form.chest" step="0.1" min="30" max="200" placeholder="95.0" class="metrics-input">
                </div>
                <!-- Cintura -->
                <div class="metrics-field">
                  <label for="waist" class="metrics-label">Cintura (cm)</label>
                  <input type="number" id="waist" v-model.number="form.waist" step="0.1" min="30" max="200" placeholder="80.0" class="metrics-input">
                </div>
                <!-- Cadera -->
                <div class="metrics-field">
                  <label for="hip" class="metrics-label">Cadera (cm)</label>
                  <input type="number" id="hip" v-model.number="form.hip" step="0.1" min="30" max="200" placeholder="95.0" class="metrics-input">
                </div>
                <!-- Muslo -->
                <div class="metrics-field">
                  <label for="thigh" class="metrics-label">Muslo (cm)</label>
                  <input type="number" id="thigh" v-model.number="form.thigh" step="0.1" min="20" max="100" placeholder="55.0" class="metrics-input">
                </div>
                <!-- Brazo -->
                <div class="metrics-field">
                  <label for="arm" class="metrics-label">Brazo (cm)</label>
                  <input type="number" id="arm" v-model.number="form.arm" step="0.1" min="15" max="60" placeholder="32.0" class="metrics-input">
                </div>
              </div>

              <!-- Measurement guide accordion -->
              <div style="margin-top:16px; border-radius:12px; border:1px solid rgba(245,158,11,.20); background:rgba(245,158,11,.05); padding:14px 16px;">
                <button @click="showGuide = !showGuide" type="button" style="display:flex; width:100%; align-items:center; justify-content:space-between; text-align:left; background:none; border:none; cursor:pointer; padding:0;">
                  <span style="font-size:13px; font-weight:600; color:#F59E0B;">Cómo tomar las mediciones correctamente</span>
                  <svg
                    :style="{ transform: showGuide ? 'rotate(180deg)' : 'none', transition: 'transform 200ms', width:'16px', height:'16px', color:'#F59E0B' }"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                  >
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
                  <div v-show="showGuide" style="margin-top:12px; overflow:hidden; display:flex; flex-direction:column; gap:8px; font-size:13px; color:var(--wc-text-2); line-height:1.5;">
                    <p><strong style="color:var(--wc-text);">Pecho:</strong> Cinta alrededor del torso a la altura de los pezones. Brazos relajados a los lados. No inflar el pecho.</p>
                    <p><strong style="color:var(--wc-text);">Cintura:</strong> En el punto más estrecho del abdomen, generalmente 2-3 cm arriba del ombligo. Medir al exhalar normalmente.</p>
                    <p><strong style="color:var(--wc-text);">Cadera:</strong> En el punto más ancho de los glúteos. Pies juntos, de pie recto.</p>
                    <p><strong style="color:var(--wc-text);">Muslo:</strong> En el punto más grueso del muslo, generalmente justo debajo del glúteo. Pierna relajada sin flexionar.</p>
                    <p><strong style="color:var(--wc-text);">Brazo:</strong> Brazo relajado a un lado. Medir en el punto más grueso del bíceps sin flexionar.</p>
                    <p style="margin-top:4px; color:rgba(245,158,11,.70);">Medir siempre en las mismas condiciones: por la mañana, antes de comer, mismo lado del cuerpo.</p>
                  </div>
                </Transition>
              </div>
            </div>

            <!-- Submit button -->
            <div style="margin-top:20px;">
              <button
                type="submit"
                :disabled="saving"
                class="btn btn-accent"
                style="opacity:1;"
                :style="{ opacity: saving ? 0.5 : 1 }"
              >
                <span v-if="!saving">Guardar registro</span>
                <span v-else style="display:inline-flex; align-items:center; gap:8px;">
                  <svg style="width:16px; height:16px; animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  Guardando...
                </span>
              </button>
            </div>
          </form>
        </section>

        <!-- History Table -->
        <section v-if="history.length > 0" class="card section wc-card-metrics-history" :style="{ animationDelay: '340ms' }">
          <div class="card-head">
            <div class="card-head-left">
              <span class="card-title">Historial</span>
            </div>
            <span class="card-meta">{{ history.length }} registros</span>
          </div>

          <div style="overflow-x:auto;">
            <table class="metrics-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Peso</th>
                  <th>Musc%</th>
                  <th>Grasa%</th>
                  <th>Notas</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(entry, idx) in history"
                  :key="idx"
                >
                  <td style="white-space:nowrap; color:var(--wc-text-3);">
                    {{ formatDate(entry.date || entry.log_date) }}
                  </td>
                  <td style="white-space:nowrap; font-variant-numeric:tabular-nums;">
                    {{ entry.peso ? Number(entry.peso).toFixed(1) + ' kg' : '--' }}
                  </td>
                  <td style="white-space:nowrap; font-variant-numeric:tabular-nums;">
                    {{ entry.porcentaje_musculo ? Number(entry.porcentaje_musculo).toFixed(1) + '%' : '--' }}
                  </td>
                  <td style="white-space:nowrap; font-variant-numeric:tabular-nums;">
                    {{ entry.porcentaje_grasa ? Number(entry.porcentaje_grasa).toFixed(1) + '%' : '--' }}
                  </td>
                  <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" :title="entry.notas || ''">
                    {{ entry.notas || '--' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </main>

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

                <h2 id="metrics-success-title" class="mb-2 font-sans text-2xl font-bold text-white">Métricas guardadas!</h2>

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
                  <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">Pésate en ayunas, después de ir al baño y antes de desayunar. Siempre a la misma hora para tener datos comparables semana a semana.</p>
                </div>
              </div>
            </div>

            <!-- Step 2 -->
            <div v-show="tutorialStep === 2">
              <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">2</div>
                <div>
                  <p class="text-sm font-semibold text-wc-text">Las fluctuaciones son normales</p>
                  <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">El peso puede variar 1-3 kg en un día por agua, comida y sal. Lo que importa es la tendencia de semanas, no el número de un día específico.</p>
                </div>
              </div>
            </div>

            <!-- Step 3 -->
            <div v-show="tutorialStep === 3">
              <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-sm font-bold text-white">3</div>
                <div>
                  <p class="text-sm font-semibold text-wc-text">El peso no es todo</p>
                  <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">La escala no distingue músculo de grasa. Registra también tus medidas y fotos de progreso — la transformación visual siempre supera a los números.</p>
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
              >Atrás</button>
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

    </div>
    <!-- ===== /wc-shell--metrics ===== -->
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
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
