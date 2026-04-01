<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const saveError = ref(null);
const showSuccess = ref(false);
const showGuide = ref(false);

// Metrics data from API
const currentWeight = ref(null);
const weightChange = ref(null);
const history = ref([]);
const chartData = ref([]);

// Form
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

// Fetch metrics
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
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar metricas';
    } finally {
        loading.value = false;
    }
}

// Save metric
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

        // Reset form
        form.value = { peso: '', porcentajeMusculo: '', porcentajeGrasa: '', notas: '', chest: '', waist: '', hip: '', thigh: '', arm: '' };

        // Show success
        showSuccess.value = true;
        setTimeout(() => { showSuccess.value = false; }, 3000);

        // Refresh data
        await fetchMetrics();
    } catch (err) {
        if (err.response?.status === 422) {
            formErrors.value = err.response.data.errors || {};
        } else {
            saveError.value = err.response?.data?.message || 'Error al guardar el registro';
        }
    } finally {
        saving.value = false;
    }
}

// Chart helpers
function getBarHeight(weight) {
    if (!chartData.value.length) return 50;
    const weights = chartData.value.map(e => Number(e.peso));
    const max = Math.max(...weights);
    const min = Math.min(...weights);
    const range = max - min || 1;
    return ((Number(weight) - min) / range) * 70 + 30;
}

onMounted(() => {
    fetchMetrics();
});
</script>

<template>
  <ClientLayout>
    <!-- Success Toast -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="showSuccess"
        class="fixed bottom-24 right-4 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 shadow-lg backdrop-blur-sm lg:bottom-6 lg:right-6"
      >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Registro guardado correctamente</span>
      </div>
    </Transition>

    <div class="space-y-6">
      <!-- Title -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">METRICAS CORPORALES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Registra y monitorea tu composicion corporal</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
          <div v-for="i in 3" :key="i" class="h-24 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
        </div>
        <div class="h-64 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
        <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
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

      <!-- Content -->
      <template v-else>
        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
          <!-- Current Weight -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Peso actual</p>
            <p class="mt-1 font-data text-3xl font-semibold text-wc-text">
              {{ currentWeight ? Number(currentWeight).toFixed(1) : '--' }}
              <span class="text-base font-normal text-wc-text-tertiary">kg</span>
            </p>
          </div>

          <!-- Monthly Change -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Cambio mensual</p>
            <p :class="[
              'mt-1 font-data text-3xl font-semibold',
              weightChange !== null
                ? (weightChange > 0 ? 'text-amber-500' : weightChange < 0 ? 'text-emerald-500' : 'text-wc-text')
                : 'text-wc-text'
            ]">
              <template v-if="weightChange !== null">
                {{ weightChange > 0 ? '+' : '' }}{{ Number(weightChange).toFixed(1) }}
                <span class="text-base font-normal text-wc-text-tertiary">kg</span>
              </template>
              <template v-else>--</template>
            </p>
          </div>

          <!-- Goal Placeholder -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
            <p class="mt-1 font-data text-3xl font-semibold text-wc-text-tertiary">
              -- <span class="text-base font-normal">kg</span>
            </p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Consulta con tu coach</p>
          </div>
        </div>

        <!-- Log Form -->
        <form @submit.prevent="saveMetric" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Nuevo registro</h2>

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
                <h3 class="text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Mediciones corporales</h3>
                <p class="text-xs text-wc-text-tertiary">Mide con cinta metrica flexible, en la manana</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
              <div>
                <label for="chest" class="mb-1 block text-xs font-medium text-wc-text">Pecho (cm)</label>
                <input type="number" id="chest" v-model="form.chest" step="0.1" min="30" max="200" placeholder="95.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="waist" class="mb-1 block text-xs font-medium text-wc-text">Cintura (cm)</label>
                <input type="number" id="waist" v-model="form.waist" step="0.1" min="30" max="200" placeholder="80.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="hip" class="mb-1 block text-xs font-medium text-wc-text">Cadera (cm)</label>
                <input type="number" id="hip" v-model="form.hip" step="0.1" min="30" max="200" placeholder="95.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="thigh" class="mb-1 block text-xs font-medium text-wc-text">Muslo (cm)</label>
                <input type="number" id="thigh" v-model="form.thigh" step="0.1" min="20" max="100" placeholder="55.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label for="arm" class="mb-1 block text-xs font-medium text-wc-text">Brazo (cm)</label>
                <input type="number" id="arm" v-model="form.arm" step="0.1" min="15" max="60" placeholder="32.0"
                  class="w-full rounded-xl border border-wc-border bg-wc-bg-secondary px-3 py-2 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
            </div>

            <!-- Measurement guide -->
            <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
              <button @click="showGuide = !showGuide" type="button" class="flex w-full items-center justify-between text-left">
                <span class="text-xs font-semibold text-amber-400">Como tomar las mediciones correctamente</span>
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
                <div v-show="showGuide" class="mt-3 space-y-2 overflow-hidden text-xs text-wc-text-secondary">
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

        <!-- Weight Chart (CSS bar chart) -->
        <div v-if="chartData.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Tendencia de peso</h2>

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
              <span class="mt-1 text-[10px] text-wc-text-tertiary">{{ entry.date }}</span>
            </div>
          </div>

          <div class="mt-2 flex justify-between text-xs text-wc-text-tertiary">
            <span>Min: {{ Math.min(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
            <span>Max: {{ Math.max(...chartData.map(e => Number(e.peso))).toFixed(1) }} kg</span>
          </div>
        </div>

        <!-- Weight History Table -->
        <div v-if="history.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Historial de registros</h2>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-wc-border">
                  <th class="pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                  <th class="pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso</th>
                  <th class="hidden pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">% Musculo</th>
                  <th class="hidden pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">% Grasa</th>
                  <th class="hidden pb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary md:table-cell">Notas</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(entry, idx) in history"
                  :key="idx"
                  class="border-b border-wc-border/50 last:border-0"
                >
                  <td class="py-3 pr-4 font-data text-wc-text">{{ entry.date }}</td>
                  <td class="py-3 pr-4 font-data font-semibold text-wc-text">{{ Number(entry.peso).toFixed(1) }} kg</td>
                  <td class="hidden py-3 pr-4 font-data text-wc-text-secondary sm:table-cell">{{ entry.porcentajeMusculo ? Number(entry.porcentajeMusculo).toFixed(1) + '%' : '--' }}</td>
                  <td class="hidden py-3 pr-4 font-data text-wc-text-secondary sm:table-cell">{{ entry.porcentajeGrasa ? Number(entry.porcentajeGrasa).toFixed(1) + '%' : '--' }}</td>
                  <td class="hidden py-3 text-wc-text-tertiary md:table-cell">{{ entry.notas || '--' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>
