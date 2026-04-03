<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const showForm = ref(false);
const showSuccess = ref(false);

// History data
const history = ref([]);
const firstMeasurement = ref(null);
const latestMeasurement = ref(null);

// Form fields
const weightKg = ref('');
const chestCm = ref('');
const waistCm = ref('');
const hipsCm = ref('');
const thighCm = ref('');
const armCm = ref('');
const musclePct = ref('');
const fatPct = ref('');

const comparisonFields = [
    { key: 'weight_kg', label: 'Peso', unit: 'kg', lowerBetter: true },
    { key: 'waist_cm', label: 'Cintura', unit: 'cm', lowerBetter: true },
    { key: 'muscle_pct', label: 'Musculo', unit: '%', lowerBetter: false },
    { key: 'fat_pct', label: 'Grasa', unit: '%', lowerBetter: true },
    { key: 'chest_cm', label: 'Pecho', unit: 'cm', lowerBetter: false },
    { key: 'hips_cm', label: 'Cadera', unit: 'cm', lowerBetter: true },
    { key: 'thigh_cm', label: 'Muslo', unit: 'cm', lowerBetter: false },
    { key: 'arm_cm', label: 'Brazo', unit: 'cm', lowerBetter: false },
];

async function fetchMeasurements() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/measurements');
        history.value = response.data.history || [];
        firstMeasurement.value = response.data.firstMeasurement || null;
        latestMeasurement.value = response.data.latestMeasurement || null;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar mediciones';
    } finally {
        loading.value = false;
    }
}

function toggleForm() {
    showForm.value = !showForm.value;
    if (!showForm.value) resetForm();
}

function resetForm() {
    weightKg.value = '';
    chestCm.value = '';
    waistCm.value = '';
    hipsCm.value = '';
    thighCm.value = '';
    armCm.value = '';
    musclePct.value = '';
    fatPct.value = '';
}

async function save() {
    saving.value = true;
    error.value = null;
    try {
        await api.post('/api/v/rise/measurements', {
            weight_kg: weightKg.value || null,
            chest_cm: chestCm.value || null,
            waist_cm: waistCm.value || null,
            hips_cm: hipsCm.value || null,
            thigh_cm: thighCm.value || null,
            arm_cm: armCm.value || null,
            muscle_pct: musclePct.value || null,
            fat_pct: fatPct.value || null,
        });
        showForm.value = false;
        showSuccess.value = true;
        resetForm();
        await fetchMeasurements();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar';
    } finally {
        saving.value = false;
    }
}

function dismissSuccess() {
    showSuccess.value = false;
}

function getDiff(field) {
    if (!firstMeasurement.value || !latestMeasurement.value) return null;
    const first = firstMeasurement.value[field.key];
    const latest = latestMeasurement.value[field.key];
    if (first == null || latest == null) return null;
    return Math.round((latest - first) * 10) / 10;
}

function isImproved(field) {
    const diff = getDiff(field);
    if (diff === null || diff === 0) return null;
    return field.lowerBetter ? diff < 0 : diff > 0;
}

onMounted(() => {
    fetchMeasurements();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <div v-else class="space-y-6">

      <!-- Page header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mediciones</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Registra y monitorea tus mediciones corporales.</p>
        </div>
        <button
          @click="toggleForm"
          class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-4 py-2 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nueva medicion
        </button>
      </div>

      <!-- Measurement form (collapsible) -->
      <div v-if="showForm" class="rounded-xl border border-wc-accent/20 bg-wc-bg-tertiary p-5 sm:p-6">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Nueva medicion</h2>

        <form @submit.prevent="save" class="mt-5 space-y-5">
          <div>
            <label for="weight_kg" class="block text-sm font-medium text-wc-text-secondary">Peso (kg) <span class="text-wc-accent">*</span></label>
            <input type="number" step="0.1" id="weight_kg" v-model="weightKg" placeholder="Ej: 75.5"
              class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent sm:max-w-xs">
          </div>

          <div>
            <p class="text-sm font-medium text-wc-text-secondary">Medidas corporales (cm)</p>
            <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
              <div>
                <label class="block text-xs text-wc-text-tertiary">Pecho</label>
                <input type="number" step="0.1" v-model="chestCm" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label class="block text-xs text-wc-text-tertiary">Cintura</label>
                <input type="number" step="0.1" v-model="waistCm" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label class="block text-xs text-wc-text-tertiary">Cadera</label>
                <input type="number" step="0.1" v-model="hipsCm" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label class="block text-xs text-wc-text-tertiary">Muslo</label>
                <input type="number" step="0.1" v-model="thighCm" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label class="block text-xs text-wc-text-tertiary">Brazo</label>
                <input type="number" step="0.1" v-model="armCm" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
            </div>
          </div>

          <div>
            <p class="text-sm font-medium text-wc-text-secondary">Composicion corporal (%)</p>
            <div class="mt-2 grid grid-cols-2 gap-3 sm:max-w-sm">
              <div>
                <label class="block text-xs text-wc-text-tertiary">Musculo</label>
                <input type="number" step="0.1" v-model="musclePct" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
              <div>
                <label class="block text-xs text-wc-text-tertiary">Grasa</label>
                <input type="number" step="0.1" v-model="fatPct" placeholder="--"
                  class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3 border-t border-wc-border pt-4">
            <button type="submit" :disabled="saving"
              class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all disabled:opacity-60">
              {{ saving ? 'Guardando...' : 'Guardar medicion' }}
            </button>
            <button type="button" @click="toggleForm"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
              Cancelar
            </button>
          </div>
        </form>
      </div>

      <!-- First vs Latest comparison -->
      <div v-if="firstMeasurement && latestMeasurement" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Progreso: Inicio vs Actual</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">{{ firstMeasurement.date }} vs {{ latestMeasurement.date }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
          <template v-for="field in comparisonFields" :key="field.key">
            <div v-if="firstMeasurement[field.key] != null && latestMeasurement[field.key] != null" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
              <p class="text-xs font-medium text-wc-text-tertiary">{{ field.label }}</p>
              <p class="mt-1 font-data text-lg font-bold text-wc-text">
                {{ latestMeasurement[field.key] }}<span class="text-xs font-normal text-wc-text-tertiary">{{ field.unit }}</span>
              </p>
              <p :class="['mt-0.5 text-xs font-medium', isImproved(field) === true ? 'text-emerald-500' : isImproved(field) === false ? 'text-wc-accent' : 'text-wc-text-tertiary']">
                <template v-if="getDiff(field) !== 0">
                  {{ getDiff(field) > 0 ? '+' : '' }}{{ getDiff(field) }}{{ field.unit }}
                </template>
                <template v-else>Sin cambio</template>
              </p>
            </div>
          </template>
        </div>
      </div>

      <!-- Success overlay -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.85);" @keydown.escape="dismissSuccess" role="dialog" aria-modal="true">
            <div class="relative w-full max-w-sm overflow-hidden rounded-2xl text-center" style="background: linear-gradient(160deg, #0C1015 0%, #131F2B 50%, #0C1015 100%);">
              <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(ellipse at 50% -5%, rgba(255,255,255,0.08) 0%, transparent 60%);"></div>
              <div class="relative z-10 p-8">
                <div class="mb-3 flex items-center justify-center gap-2">
                  <span class="font-display text-xl tracking-[0.25em] text-white/90">WELLCORE</span>
                </div>
                <h2 class="font-sans text-2xl font-bold text-white mb-2">Medidas guardadas</h2>
                <div v-if="weightKg" class="my-5 rounded-xl border border-white/10 bg-white/[0.06] px-5 py-4">
                  <p class="font-display text-3xl text-white" style="line-height:1">{{ parseFloat(weightKg).toFixed(1) }} <span class="text-lg font-normal text-white/50">kg</span></p>
                  <p class="mt-0.5 text-xs text-white/50">peso registrado</p>
                </div>
                <p class="mb-6 text-sm text-white/70">Cada medida es evidencia de tu transformacion.</p>
                <button @click="dismissSuccess" class="w-full rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-black">
                  LISTO
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- History table -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Historial de mediciones</h2>

        <div v-if="history.length > 0" class="mt-4 overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-wc-border">
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Pecho</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cintura</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cadera</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Muslo</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Brazo</th>
                <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Musculo%</th>
                <th class="whitespace-nowrap pb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Grasa%</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <tr v-for="(row, index) in history" :key="index" :class="index === 0 ? 'bg-wc-accent/5' : ''">
                <td class="whitespace-nowrap py-3 pr-4 font-medium text-wc-text">
                  {{ row.date }}
                  <span v-if="index === 0" class="ml-1 text-[10px] font-semibold text-wc-accent">ULTIMO</span>
                </td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text">{{ row.weight_kg ? row.weight_kg + ' kg' : '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.chest_cm ?? '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.waist_cm ?? '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.hips_cm ?? '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.thigh_cm ?? '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.arm_cm ?? '--' }}</td>
                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ row.muscle_pct ? row.muscle_pct + '%' : '--' }}</td>
                <td class="whitespace-nowrap py-3 font-data text-wc-text-secondary">{{ row.fat_pct ? row.fat_pct + '%' : '--' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="mt-6 flex flex-col items-center py-8 text-center">
          <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
          </svg>
          <p class="mt-3 text-sm font-medium text-wc-text">Sin mediciones registradas</p>
          <p class="mt-1 text-xs text-wc-text-tertiary">Registra tu primera medicion para comenzar a monitorear tu progreso.</p>
          <button @click="toggleForm"
            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-4 py-2 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Primera medicion
          </button>
        </div>
      </div>
    </div>
  </RiseLayout>
</template>
