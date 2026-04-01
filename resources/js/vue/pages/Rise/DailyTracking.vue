<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const successMsg = ref('');
const data = ref(null);

// Form fields
const trainingDone = ref(false);
const nutritionDone = ref(false);
const waterLiters = ref('');
const sleepHours = ref('');
const note = ref('');
const todaySaved = ref(false);
const savedAt = ref(null);

async function fetchTracking() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/tracking');
        data.value = response.data;

        // Hydrate today's entry if saved
        if (response.data.todayEntry) {
            const entry = response.data.todayEntry;
            trainingDone.value = !!entry.trainingDone;
            nutritionDone.value = !!entry.nutritionDone;
            waterLiters.value = entry.waterLiters || '';
            sleepHours.value = entry.sleepHours || '';
            note.value = entry.note || '';
            todaySaved.value = true;
            savedAt.value = entry.savedAt || null;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar tracking';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    successMsg.value = '';
    try {
        await api.post('/api/v/rise/tracking', {
            training_done: trainingDone.value,
            nutrition_done: nutritionDone.value,
            water_liters: waterLiters.value || null,
            sleep_hours: sleepHours.value || null,
            note: note.value || null,
        });
        todaySaved.value = true;
        savedAt.value = new Date().toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });
        successMsg.value = 'Registro guardado correctamente';
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    fetchTracking();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Tracking Diario</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Registra tu progreso diario en el programa RISE.</p>
      </div>

      <!-- Success message -->
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="successMsg" class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
          <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
          <p class="text-sm text-emerald-500">{{ successMsg }}</p>
        </div>
      </Transition>

      <!-- Weekly overview grid -->
      <div v-if="data?.weekDays" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Esta semana</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Tracking semanal</p>

        <div class="mt-5 grid grid-cols-7 gap-2 sm:gap-3">
          <div
            v-for="day in data.weekDays"
            :key="day.label"
            :class="['flex flex-col items-center gap-1.5 rounded-lg p-2', day.isToday ? 'bg-wc-accent/5 ring-1 ring-wc-accent/20' : '']"
          >
            <span :class="['text-[11px] font-medium', day.isToday ? 'text-wc-accent font-semibold' : 'text-wc-text-tertiary']">
              {{ day.label }}
            </span>

            <div v-if="day.trainingDone" class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/15">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </div>
            <div v-else-if="day.hasEntry" class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-accent/10">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </div>
            <div v-else :class="['flex h-9 w-9 items-center justify-center rounded-full border border-wc-border', day.isToday ? '!border-wc-accent/30' : '']">
              <div v-if="day.isToday" class="h-1.5 w-1.5 rounded-full bg-wc-accent"></div>
            </div>

            <div :class="['h-1 w-1 rounded-full', day.nutritionDone ? 'bg-amber-400' : 'bg-wc-border']"></div>
            <span v-if="day.waterLiters" class="text-[10px] font-medium text-sky-400">{{ day.waterLiters }}L</span>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-4 text-[11px] text-wc-text-tertiary">
          <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-emerald-500/40"></div>Entreno</div>
          <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-amber-400"></div>Nutricion</div>
          <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-sky-400"></div>Agua</div>
        </div>
      </div>

      <!-- Today's form -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-display text-lg tracking-wide text-wc-text">Hoy</h2>
            <p v-if="todaySaved && savedAt" class="mt-0.5 text-xs text-emerald-500">Guardado a las {{ savedAt }}</p>
          </div>
          <span v-if="todaySaved" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-500">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            Registrado
          </span>
        </div>

        <form @submit.prevent="save" class="mt-6 space-y-5">
          <!-- Training + Nutrition toggles -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label :class="['flex cursor-pointer items-center gap-4 rounded-lg border p-4 transition-colors', trainingDone ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-wc-border hover:border-wc-text-tertiary']">
              <input type="checkbox" v-model="trainingDone" class="sr-only">
              <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-lg', trainingDone ? 'bg-emerald-500/15' : 'bg-wc-bg-secondary']">
                <svg v-if="trainingDone" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <svg v-else class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">Entrenamiento</p>
                <p class="text-xs text-wc-text-tertiary">{{ trainingDone ? 'Completado' : 'No completado' }}</p>
              </div>
            </label>

            <label :class="['flex cursor-pointer items-center gap-4 rounded-lg border p-4 transition-colors', nutritionDone ? 'border-wc-accent/30 bg-wc-accent/5' : 'border-wc-border hover:border-wc-text-tertiary']">
              <input type="checkbox" v-model="nutritionDone" class="sr-only">
              <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-lg', nutritionDone ? 'bg-wc-accent/15' : 'bg-wc-bg-secondary']">
                <svg v-if="nutritionDone" class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <svg v-else class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">Nutricion</p>
                <p class="text-xs text-wc-text-tertiary">{{ nutritionDone ? 'Plan seguido' : 'No completado' }}</p>
              </div>
            </label>
          </div>

          <!-- Water + Sleep -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label for="waterLiters" class="block text-sm font-medium text-wc-text-secondary">Agua (litros)</label>
              <input
                type="number" step="0.1" min="0" max="10" id="waterLiters"
                v-model="waterLiters"
                placeholder="Ej: 2.5"
                class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
            </div>
            <div>
              <label for="sleepHours" class="block text-sm font-medium text-wc-text-secondary">Sueno (horas)</label>
              <input
                type="number" step="0.5" min="0" max="24" id="sleepHours"
                v-model="sleepHours"
                placeholder="Ej: 7.5"
                class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              >
            </div>
          </div>

          <!-- Note -->
          <div>
            <label for="note" class="block text-sm font-medium text-wc-text-secondary">Nota del dia (opcional)</label>
            <textarea
              id="note" v-model="note" rows="3"
              placeholder="Como te sentiste hoy? Algo que destacar?"
              class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
            ></textarea>
          </div>

          <!-- Submit -->
          <div class="flex items-center gap-3 pt-2">
            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all disabled:opacity-60"
            >
              <svg v-if="!saving" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              {{ saving ? 'Guardando...' : (todaySaved ? 'Actualizar registro' : 'Guardar registro') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </RiseLayout>
</template>
