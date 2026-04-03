<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const successMsg = ref('');
const data = ref(null);

// Form
const habitsDone = ref({});
const water = ref('');
const sleep = ref('');
const steps = ref('');
const notes = ref('');
const todaySaved = ref(false);
const savedAt = ref(null);

const habitColors = [
    { border: 'border-emerald-500/30', bg: 'bg-emerald-500/5', iconBg: 'bg-emerald-500/15', icon: 'text-emerald-500' },
    { border: 'border-wc-accent/30', bg: 'bg-wc-accent/5', iconBg: 'bg-wc-accent/15', icon: 'text-wc-accent' },
    { border: 'border-violet-500/30', bg: 'bg-violet-500/5', iconBg: 'bg-violet-500/15', icon: 'text-violet-500' },
    { border: 'border-sky-500/30', bg: 'bg-sky-500/5', iconBg: 'bg-sky-500/15', icon: 'text-sky-500' },
    { border: 'border-amber-500/30', bg: 'bg-amber-500/5', iconBg: 'bg-amber-500/15', icon: 'text-amber-500' },
    { border: 'border-orange-500/30', bg: 'bg-orange-500/5', iconBg: 'bg-orange-500/15', icon: 'text-orange-500' },
];

function getHabitColor(idx) {
    return habitColors[idx % habitColors.length];
}

const habitsPlan = computed(() => data.value?.habitsPlan || []);

const completedCount = computed(() => {
    return Object.values(habitsDone.value).filter(Boolean).length;
});

const progressPct = computed(() => {
    const total = habitsPlan.value.length;
    if (total === 0) return 0;
    return Math.round((completedCount.value / total) * 100);
});

async function fetchHabits() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/habits');
        data.value = response.data;

        // Hydrate today
        if (response.data.todayEntry) {
            const entry = response.data.todayEntry;
            habitsDone.value = entry.habitsDone || {};
            water.value = entry.water || '';
            sleep.value = entry.sleep || '';
            steps.value = entry.steps || '';
            notes.value = entry.notes || '';
            todaySaved.value = true;
            savedAt.value = entry.savedAt || null;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar habitos';
    } finally {
        loading.value = false;
    }
}

function toggleHabit(index) {
    habitsDone.value = { ...habitsDone.value, [index]: !habitsDone.value[index] };
}

async function save() {
    saving.value = true;
    successMsg.value = '';
    try {
        await api.post('/api/v/rise/habits', {
            habits_done: habitsDone.value,
            water: water.value || null,
            sleep: sleep.value || null,
            steps: steps.value || null,
            notes: notes.value || null,
        });
        todaySaved.value = true;
        savedAt.value = new Date().toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });
        successMsg.value = 'Habitos guardados correctamente';
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    fetchHabits();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-20 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">HABITOS DIARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">{{ new Date().toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
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

      <!-- Stats summary -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/15">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
            </div>
            <div>
              <p class="font-display text-2xl text-wc-accent" style="line-height:1">{{ data?.currentStreak ?? 0 }}</p>
              <p class="text-sm uppercase tracking-wider text-wc-text-tertiary">Racha</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div>
              <p class="font-display text-2xl text-wc-accent" style="line-height:1">{{ data?.completedDays ?? 0 }}</p>
              <p class="text-sm uppercase tracking-wider text-wc-text-tertiary">Dias</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15">
              <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
              </svg>
            </div>
            <div>
              <p class="font-display text-2xl text-wc-accent" style="line-height:1">{{ data?.avgWater ?? '--' }}<span class="text-xs font-normal text-wc-text-tertiary">L</span></p>
              <p class="text-sm uppercase tracking-wider text-wc-text-tertiary">Agua prom.</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/15">
              <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
              </svg>
            </div>
            <div>
              <p class="font-display text-2xl text-wc-accent" style="line-height:1">{{ data?.avgSleep ?? '--' }}<span class="text-xs font-normal text-wc-text-tertiary">h</span></p>
              <p class="text-sm uppercase tracking-wider text-wc-text-tertiary">Sueno prom.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly grid -->
      <div v-if="data?.weekDays" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Esta semana</h2>
        <div class="mt-5 grid grid-cols-7 gap-2 sm:gap-3">
          <div
            v-for="day in data.weekDays"
            :key="day.label"
            :class="['flex flex-col items-center gap-1.5 rounded-lg p-2', day.isToday ? 'bg-wc-accent/5 ring-1 ring-wc-accent/20' : '']"
          >
            <span :class="['text-sm font-medium', day.isToday ? 'text-wc-accent font-semibold' : 'text-wc-text-tertiary']">{{ day.label }}</span>
            <div v-if="day.hasEntry" :class="['flex h-9 w-9 items-center justify-center rounded-full', day.habitCount >= (day.total * 0.8) ? 'bg-emerald-500/15 text-emerald-500' : 'bg-wc-accent/15 text-wc-accent']">
              <span class="text-xs font-bold">{{ day.habitCount }}</span>
            </div>
            <div v-else :class="['flex h-9 w-9 items-center justify-center rounded-full border border-wc-border', day.isToday ? '!border-wc-accent/30' : '']">
              <div v-if="day.isToday" class="h-1.5 w-1.5 rounded-full bg-wc-accent"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's form -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-display text-lg tracking-wide text-wc-text">Hoy</h2>
            <p v-if="todaySaved && savedAt" class="mt-0.5 text-sm text-emerald-500">Guardado a las {{ savedAt }}</p>
          </div>
          <span v-if="todaySaved" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-500">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            Registrado
          </span>
        </div>

        <!-- Progress indicator -->
        <div v-if="habitsPlan.length > 0" class="mt-5">
          <div class="flex items-center justify-between text-sm text-wc-text-tertiary mb-2">
            <span>{{ completedCount }}/{{ habitsPlan.length }} habitos</span>
            <span>{{ progressPct }}%</span>
          </div>
          <div class="h-1.5 w-full rounded-full bg-wc-border overflow-hidden mb-4">
            <div class="h-full rounded-full bg-wc-accent transition-all duration-300" :style="{ width: progressPct + '%' }"></div>
          </div>
        </div>

        <form @submit.prevent="save" class="mt-6 space-y-5">
          <!-- Dynamic habits -->
          <div v-if="habitsPlan.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <label
              v-for="(habit, index) in habitsPlan"
              :key="index"
              :class="[
                'flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors',
                habitsDone[index] ? getHabitColor(index).border + ' ' + getHabitColor(index).bg : 'border-wc-border hover:border-wc-text-tertiary'
              ]"
              @click.prevent="toggleHabit(index)"
            >
              <div :class="['flex h-9 w-9 shrink-0 items-center justify-center rounded-lg', habitsDone[index] ? getHabitColor(index).iconBg : 'bg-wc-bg-secondary']">
                <svg v-if="habitsDone[index]" :class="['h-[18px] w-[18px]', getHabitColor(index).icon]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <svg v-else class="h-[18px] w-[18px] text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">{{ habit.nombre || habit.name }}</p>
                <p class="text-sm text-wc-text-tertiary">
                  {{ habitsDone[index] ? 'Completado' : 'Pendiente' }}
                  <template v-if="habit.frecuencia"> &middot; {{ habit.frecuencia }}</template>
                </p>
              </div>
            </label>
          </div>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-6 text-center">
            <p class="text-sm text-wc-text-tertiary">Tu coach esta definiendo tus habitos personalizados. Apareceran aqui pronto.</p>
            <p class="mt-2 text-sm text-wc-text-tertiary">Por ahora, registra agua, sueno y pasos abajo.</p>
          </div>

          <!-- Water + Sleep + Steps -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label for="water" class="block text-sm font-medium text-wc-text-secondary">Agua (litros)</label>
              <input type="number" step="0.1" min="0" max="10" id="water" v-model="water" placeholder="Ej: 2.5"
                class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
            </div>
            <div>
              <label for="sleep" class="block text-sm font-medium text-wc-text-secondary">Sueno (horas)</label>
              <input type="number" step="0.5" min="0" max="24" id="sleep" v-model="sleep" placeholder="Ej: 7.5"
                class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
            </div>
            <div>
              <label for="steps" class="block text-sm font-medium text-wc-text-secondary">Pasos</label>
              <input type="number" min="0" max="100000" id="steps" v-model="steps" placeholder="Ej: 8000"
                class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-wc-text-secondary">Notas del dia (opcional)</label>
            <textarea id="notes" v-model="notes" rows="3" placeholder="Como te sentiste hoy? Algo que destacar?"
              class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"></textarea>
          </div>

          <!-- Submit -->
          <div class="flex items-center gap-3 pt-2">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-all disabled:opacity-60"
            >
              <span v-if="!saving" class="flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                {{ todaySaved ? 'Actualizar habitos' : 'Guardar habitos' }}
              </span>
              <span v-else>Guardando...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </RiseLayout>
</template>
