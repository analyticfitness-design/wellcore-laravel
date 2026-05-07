<script setup>
import { ref, computed, onMounted, watch } from 'vue';

import ClientLayout from '../../layouts/ClientLayout.vue';
import MetricsHero from '../../components/metrics/MetricsHero.vue';
import StatCard from '../../components/metrics/StatCard.vue';
import WeightChart from '../../components/metrics/WeightChart.vue';
import AnalysisGrid from '../../components/metrics/AnalysisGrid.vue';
import MetricsForm from '../../components/metrics/MetricsForm.vue';
import CoachInterpretation from '../../components/metrics/CoachInterpretation.vue';
import CrossLinkPhotos from '../../components/metrics/CrossLinkPhotos.vue';

import { useMetrics } from '../../composables/metrics/useMetrics.js';
import { useFormMode } from '../../composables/metrics/useFormMode.js';
import { useMeasurementsForm } from '../../composables/metrics/useMeasurementsForm.js';
import { useCoachInterpretation } from '../../composables/metrics/useCoachInterpretation.js';

// ─── Data ───────────────────────────────────────────────────────────────────
const {
  loading, error,
  entries, latestEntry,
  weeklyCheckins, weeklyVolume,
  composition, photos,
  hasData, recordsCount,
  daysSinceLast, streak,
  refresh,
} = useMetrics();

const { mode, setQuick, setFull } = useFormMode();
const { interpretation, fetchInterpretation } = useCoachInterpretation();

// ─── Quick form state ────────────────────────────────────────────────────────
const quickPeso = ref('');
const quickError = ref('');
const saving = ref(false);

// ─── Full form state ─────────────────────────────────────────────────────────
const {
  form,
  errors: formErrors,
  saving: fullSaving,
  loadDraft,
  updateField,
  submit: submitFullForm,
  resetForm,
} = useMeasurementsForm();

// ─── Chart period ────────────────────────────────────────────────────────────
const chartPeriod = ref('30d');

// ─── Tutorial ────────────────────────────────────────────────────────────────
const showTutorial = ref(false);
const tutorialStep = ref(0);
const tutorialDismissed = ref(!!localStorage.getItem('wc_metrics_tutorial_done'));

// ─── Achievement overlay ──────────────────────────────────────────────────────
const showAchievement = ref(false);
const achievementMessage = ref('');

// ─── Computed ────────────────────────────────────────────────────────────────
const currentWeight = computed(() => latestEntry.value?.peso ?? null);
const weightChange = computed(() => {
  if (entries.value.length < 2) return null;
  const latest = Number(entries.value[0].peso);
  const prev = Number(entries.value[entries.value.length - 1].peso);
  return parseFloat((latest - prev).toFixed(1));
});
const lastDate = computed(() => latestEntry.value?.fecha ?? null);

const compositionData = computed(() => {
  if (!latestEntry.value?.porcentajeMusculo && !latestEntry.value?.porcentajeGrasa) return null;
  const musc = Number(latestEntry.value.porcentajeMusculo) || 0;
  const grasa = Number(latestEntry.value.porcentajeGrasa) || 0;
  const otro = Math.max(0, 100 - musc - grasa);
  return {
    musculo: musc,
    grasa,
    otro: parseFloat(otro.toFixed(1)),
    date: lastDate.value,
  };
});

// ─── Tutorial logic ───────────────────────────────────────────────────────────
onMounted(async () => {
  await refresh();
  fetchInterpretation();
  loadDraft();

  if (!tutorialDismissed.value && !hasData.value) {
    showTutorial.value = true;
  }
});

function dismissTutorial() {
  localStorage.setItem('wc_metrics_tutorial_done', '1');
  tutorialDismissed.value = true;
  showTutorial.value = false;
  fetch('/api/v/client/metrics/dismiss-tutorial', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  }).catch(() => {});
}

// ─── Form handlers ────────────────────────────────────────────────────────────
async function handleQuickSubmit() {
  quickError.value = '';
  const val = parseFloat(quickPeso.value);
  if (!quickPeso.value || isNaN(val) || val < 20 || val > 300) {
    quickError.value = 'Ingresa un peso válido (20–300 kg)';
    return;
  }
  saving.value = true;
  try {
    const res = await fetch('/api/v/client/metrics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ peso: val }),
    });
    if (!res.ok) throw new Error('Error al guardar');
    quickPeso.value = '';
    await refresh();
    triggerAchievement('¡Peso registrado!');
  } catch (e) {
    quickError.value = 'Error al guardar. Intenta de nuevo.';
  } finally {
    saving.value = false;
  }
}

async function handleFullSubmit() {
  const ok = await submitFullForm();
  if (ok) {
    await refresh();
    triggerAchievement('¡Registro completo guardado!');
    setQuick();
  }
}

function handleFormUpdate({ key, value }) {
  updateField(key, value);
}

function triggerAchievement(message) {
  achievementMessage.value = message;
  showAchievement.value = true;
  setTimeout(() => { showAchievement.value = false; }, 3000);
}
</script>

<template>
  <ClientLayout>
  <div class="mv2">
    <!-- Tutorial overlay (first-time, no data) -->
    <Transition name="overlay">
      <div v-if="showTutorial" class="tutorial-overlay" role="dialog" aria-modal="true" aria-label="Bienvenida a Métricas">
        <div class="tutorial-card">
          <div class="tutorial-icon">📊</div>
          <template v-if="tutorialStep === 0">
            <h2 class="tutorial-h">Bienvenido a Métricas</h2>
            <p class="tutorial-p">Registra tu peso y medidas para visualizar tu progreso a lo largo del tiempo.</p>
          </template>
          <template v-else-if="tutorialStep === 1">
            <h2 class="tutorial-h">Modo rápido vs. completo</h2>
            <p class="tutorial-p">Usa el modo rápido para registrar solo el peso. El modo completo incluye medidas corporales y composición.</p>
          </template>
          <template v-else>
            <h2 class="tutorial-h">Consistencia = resultados</h2>
            <p class="tutorial-p">Registra al menos una vez por semana, en ayunas y en las mismas condiciones, para obtener datos precisos.</p>
          </template>
          <div class="tutorial-dots">
            <span v-for="i in 3" :key="i" class="tutorial-dot" :class="{ 'tutorial-dot--active': tutorialStep === i - 1 }"></span>
          </div>
          <div class="tutorial-actions">
            <button v-if="tutorialStep < 2" class="tutorial-btn tutorial-btn--primary" @click="tutorialStep++">Siguiente</button>
            <button v-else class="tutorial-btn tutorial-btn--primary" @click="dismissTutorial">Comenzar</button>
            <button class="tutorial-btn tutorial-btn--ghost" @click="dismissTutorial">Saltar</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Achievement toast -->
    <Transition name="toast">
      <div v-if="showAchievement" class="achievement-toast" role="status" aria-live="polite">
        <span class="achievement-icon">🎯</span>
        <span class="achievement-msg">{{ achievementMessage }}</span>
      </div>
    </Transition>

    <!-- Loading skeleton -->
    <div v-if="loading" class="mv2-skeleton">
      <div class="skel skel--hero"></div>
      <div class="mv2-stat-row">
        <div class="skel skel--card" v-for="i in 3" :key="i"></div>
      </div>
      <div class="skel skel--chart"></div>
    </div>

    <template v-else>
      <!-- Hero -->
      <MetricsHero
        :currentWeight="currentWeight"
        :weightChange="weightChange"
        :streak="streak"
        :lastDate="lastDate"
      />

      <!-- Coach interpretation (if exists) -->
      <CoachInterpretation :interpretation="interpretation" />

      <!-- Photos crosslink -->
      <CrossLinkPhotos :photos="photos" />

      <!-- Stat cards row -->
      <div class="mv2-stat-row">
        <StatCard
          label="Peso actual"
          :value="currentWeight ? Number(currentWeight).toFixed(1) : '—'"
          unit="kg"
          variant="hero"
          :delta="weightChange"
        />
        <StatCard
          label="Registros totales"
          :value="recordsCount"
          variant="counter"
        />
        <StatCard
          label="Días desde último"
          :value="daysSinceLast !== null ? daysSinceLast : '—'"
          unit="días"
          variant="default"
        />
      </div>

      <!-- Weight chart -->
      <WeightChart
        :entries="entries"
        :period="chartPeriod"
        @period-change="chartPeriod = $event"
      />

      <!-- Analysis grid (streak + composition) -->
      <AnalysisGrid
        :weeklyCheckins="weeklyCheckins"
        :composition="compositionData"
        :trainingVolume="weeklyVolume"
      />

      <!-- Log form -->
      <MetricsForm
        :mode="mode"
        :quickValue="quickPeso"
        :quickError="quickError"
        :form="form"
        :formErrors="formErrors"
        :saving="mode === 'quick' ? saving : fullSaving"
        @update:quickValue="quickPeso = $event"
        @update:form="handleFormUpdate"
        @quick-submit="handleQuickSubmit"
        @full-submit="handleFullSubmit"
        @expand="setFull"
        @collapse="setQuick"
      />
    </template>
  </div>
  </ClientLayout>
</template>

<style scoped>
.mv2 { max-width: 900px; margin: 0 auto; padding: 24px 16px 64px; }

/* Stat row */
.mv2-stat-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}
@media (max-width: 639px) {
  .mv2-stat-row { grid-template-columns: 1fr; }
}

/* Skeleton */
.mv2-skeleton { display: flex; flex-direction: column; gap: 16px; }
.skel {
  border-radius: 12px;
  background: var(--color-wc-bg-secondary);
  animation: skel-pulse 1.4s ease infinite;
}
.skel--hero { height: 80px; }
.skel--card { height: 100px; }
.skel--chart { height: 240px; }
@keyframes skel-pulse {
  0%, 100% { opacity: .6; }
  50% { opacity: 1; }
}

/* Tutorial overlay */
.tutorial-overlay {
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,.72); backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center; padding: 24px;
}
.tutorial-card {
  width: 100%; max-width: 400px;
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border);
  border-radius: 20px; padding: 32px 28px;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  text-align: center;
}
.tutorial-icon { font-size: 36px; line-height: 1; }
.tutorial-h {
  font-family: var(--font-display);
  font-size: 20px; font-weight: 400; letter-spacing: .04em; text-transform: uppercase;
  color: var(--color-wc-text); margin: 0;
}
.tutorial-p { font-size: 14px; color: var(--color-wc-text-secondary); line-height: 1.6; margin: 0; }
.tutorial-dots { display: flex; gap: 6px; margin-top: 4px; }
.tutorial-dot {
  width: 6px; height: 6px; border-radius: 999px;
  background: var(--color-wc-border);
  transition: background .2s;
}
.tutorial-dot--active { background: var(--color-wc-accent); }
.tutorial-actions { display: flex; gap: 10px; margin-top: 8px; width: 100%; }
.tutorial-btn {
  flex: 1; min-height: 44px; border-radius: 10px; border: none; cursor: pointer;
  font-family: var(--font-display); font-size: 14px; font-weight: 400; letter-spacing: .06em; text-transform: uppercase;
  transition: background .12s;
}
.tutorial-btn--primary { background: var(--color-wc-accent); color: #fff; }
.tutorial-btn--primary:hover { background: var(--color-wc-accent-hover); }
.tutorial-btn--ghost {
  background: var(--color-wc-bg-tertiary); color: var(--color-wc-text-secondary);
  border: 1px solid var(--color-wc-border);
}
.tutorial-btn--ghost:hover { background: var(--color-wc-bg); }

/* Achievement toast */
.achievement-toast {
  position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
  z-index: 300; display: flex; align-items: center; gap: 10px;
  padding: 12px 20px; border-radius: 12px;
  background: var(--color-wc-bg-secondary);
  border: 1px solid rgba(16,185,129,.30);
  box-shadow: 0 8px 32px rgba(0,0,0,.40);
  white-space: nowrap;
}
.achievement-icon { font-size: 18px; }
.achievement-msg { font-size: 14px; font-weight: 600; color: var(--color-wc-text); }

/* Overlay transition */
.overlay-enter-active, .overlay-leave-active { transition: opacity .25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
/* Toast transition */
.toast-enter-active { transition: opacity .2s ease, transform .2s ease; }
.toast-leave-active { transition: opacity .3s ease, transform .3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(-50%) translateY(12px); }
.toast-leave-to   { opacity: 0; transform: translateX(-50%) translateY(12px); }
</style>
