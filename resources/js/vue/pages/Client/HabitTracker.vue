<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();

// ─── State ──────────────────────────────────────────────────────────────────
const loading        = ref(true);
const error          = ref(null);
const todayHabits    = ref({});
const totalHabits    = ref(0);
const completedToday = ref(0);
const heatmapData    = ref([]);   // array of 30 objects: {date, day, count, total, level}
const weeklyData     = ref([]);   // array of 7 objects
const togglingHabit  = ref(null);
const showConfetti   = ref(false);
const hoveredDay     = ref(null); // {date, count, total, x, y}

// ─── Fetch ───────────────────────────────────────────────────────────────────
async function fetchHabits() {
    loading.value = true;
    error.value   = null;
    try {
        const response       = await api.get('/api/v/client/habits');
        const d              = response.data;
        todayHabits.value    = d.today_habits    ?? {};
        totalHabits.value    = d.total_habits    ?? 0;
        completedToday.value = d.completed_today ?? 0;
        heatmapData.value    = d.heatmap_data    ?? [];
        weeklyData.value     = d.weekly_data     ?? [];
    } catch (err) {
        error.value = err.response?.data?.message ?? 'Error al cargar los habitos';
    } finally {
        loading.value = false;
    }
}

// ─── Toggle ──────────────────────────────────────────────────────────────────
async function toggleHabit(type) {
    togglingHabit.value = type;
    try {
        const response = await api.post('/api/v/client/habits/toggle', { type });
        const d        = response.data;
        if (todayHabits.value[type]) {
            todayHabits.value[type].completed = d.completed ?? !todayHabits.value[type].completed;
        }
        completedToday.value = d.completed_today ?? completedToday.value;
        if (d.streak !== undefined && todayHabits.value[type]) {
            todayHabits.value[type].streak = d.streak;
        }
        if (completedToday.value === totalHabits.value && totalHabits.value > 0) {
            showConfetti.value = true;
            setTimeout(() => { showConfetti.value = false; }, 3000);
        }
    } catch {
        // fail silently
    } finally {
        togglingHabit.value = null;
    }
}

onMounted(fetchHabits);

// ─── Derived ─────────────────────────────────────────────────────────────────
const circumference = 2 * Math.PI * 34;

const ringOffset = computed(() => {
    if (totalHabits.value === 0) return circumference;
    return circumference * (1 - completedToday.value / totalHabits.value);
});

const allComplete = computed(() =>
    completedToday.value === totalHabits.value && totalHabits.value > 0
);

const formattedDate = computed(() => {
    const formatted = new Date().toLocaleDateString('es-CO', {
        weekday: 'long', day: 'numeric', month: 'long',
    });
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});

// ─── Heatmap helpers ─────────────────────────────────────────────────────────
const TODAY_STR = new Date().toISOString().split('T')[0];

// level 0–4 from backend; map to Tailwind bg classes
const LEVEL_CLASSES = [
    'bg-wc-bg-secondary',     // 0 — empty
    'bg-emerald-900',         // 1 — low
    'bg-emerald-700',         // 2 — mid-low
    'bg-emerald-500',         // 3 — mid-high
    'bg-emerald-400',         // 4 — full
];

function heatmapClass(day) {
    return LEVEL_CLASSES[day.level ?? 0] ?? LEVEL_CLASSES[0];
}

function isToday(dateStr) {
    return dateStr === TODAY_STR;
}

function formatTooltipDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('es-CO', { weekday: 'short', day: 'numeric', month: 'short' });
}

function showTooltip(day, event) {
    const rect = event.target.getBoundingClientRect();
    hoveredDay.value = {
        ...day,
        x: rect.left + rect.width / 2,
        y: rect.top - 8,
    };
}

function hideTooltip() {
    hoveredDay.value = null;
}

// ─── Compliance helpers ───────────────────────────────────────────────────────
function complianceBarClass(pct) {
    if (pct >= 70) return 'bg-emerald-500';
    if (pct >= 40) return 'bg-amber-500';
    return 'bg-wc-accent';
}

function complianceTextClass(pct) {
    if (pct >= 70) return 'text-emerald-400';
    if (pct >= 40) return 'text-amber-400';
    return 'text-red-400';
}

function complianceDays(pct) {
    // Backend gives exact compliance % over 30 days
    return Math.round((pct / 100) * 30);
}

// ─── Habit metadata ──────────────────────────────────────────────────────────
function getHabitLabel(type) {
    const map = {
        agua: 'Agua', sueno: 'Sueno', entrenamiento: 'Entrenamiento',
        nutricion: 'Nutricion', suplementos: 'Suplementos',
    };
    return map[type] ?? type;
}
</script>

<template>
  <ClientLayout>

    <!-- ── Loading skeleton ───────────────────────────────────────────────── -->
    <div v-if="loading" class="space-y-6">
      <div class="space-y-2">
        <div class="h-9 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-40 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>
      <div class="h-28 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div v-for="i in 5" :key="i" class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
    </div>

    <!-- ── Error state ────────────────────────────────────────────────────── -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchHabits"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none"
      >
        Reintentar
      </button>
    </div>

    <!-- ── Main content ───────────────────────────────────────────────────── -->
    <div v-else class="space-y-6">

      <!-- Confetti overlay -->
      <div v-if="showConfetti" class="pointer-events-none fixed inset-0 z-50">
        <div
          v-for="i in 12" :key="i"
          class="absolute"
          :style="{
            left: `${10 + (i * 7.3) % 80}%`,
            top: '-10px',
            animation: `confetti-fall ${2.5 + (i * 0.13)}s ease-in forwards ${i * 0.1}s`,
          }"
        >
          <div
            class="h-2 w-2 rounded-full"
            :class="['bg-wc-accent','bg-emerald-500','bg-amber-400','bg-violet-500'][i % 4]"
          ></div>
        </div>
      </div>

      <!-- Title -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">HABITOS DIARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">{{ formattedDate }}</p>
      </div>

      <!-- Today progress ring -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="flex items-center gap-5">
          <div class="relative h-20 w-20 shrink-0">
            <svg class="h-full w-full -rotate-90" viewBox="0 0 80 80">
              <circle cx="40" cy="40" r="34" fill="none" stroke="currentColor" stroke-width="5" class="text-wc-bg-secondary" />
              <circle
                cx="40" cy="40" r="34" fill="none" stroke-width="5" stroke-linecap="round"
                :class="allComplete ? 'text-emerald-500' : 'text-wc-accent'"
                stroke="currentColor"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="ringOffset"
                style="transition: stroke-dashoffset 0.8s ease-out"
              />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="font-data text-lg font-bold" :class="allComplete ? 'text-emerald-500' : 'text-wc-text'">
                {{ completedToday }}/{{ totalHabits }}
              </span>
            </div>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-wc-text">Progreso de hoy</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">
              <template v-if="allComplete">Todos tus habitos completados. Excelente trabajo.</template>
              <template v-else-if="completedToday > 0">{{ totalHabits - completedToday }} pendiente{{ (totalHabits - completedToday) > 1 ? 's' : '' }}</template>
              <template v-else>Comienza tu dia marcando tus habitos</template>
            </p>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div
                class="h-full rounded-full transition-all duration-700"
                :class="allComplete ? 'bg-emerald-500' : 'bg-wc-accent'"
                :style="{ width: `${totalHabits > 0 ? Math.round((completedToday / totalHabits) * 100) : 0}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Habit cards ──────────────────────────────────────────────────── -->
      <div class="space-y-3">
        <button
          v-for="(habit, type) in todayHabits"
          :key="type"
          @click="toggleHabit(type)"
          :disabled="togglingHabit === type"
          class="flex w-full items-center gap-4 rounded-xl border p-4 transition-all"
          :class="habit.completed
            ? 'border-emerald-500/30 bg-emerald-500/5'
            : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary'"
        >
          <!-- Icon -->
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-all duration-300"
            :class="habit.completed ? 'bg-emerald-500 text-white scale-110' : 'bg-wc-bg-secondary text-wc-text-tertiary'"
          >
            <svg v-if="type === 'agua'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5S7.5 9 7.5 13.5a4.5 4.5 0 0 0 9 0C16.5 9 12 3.5 12 3.5Z" />
            </svg>
            <svg v-else-if="type === 'sueno'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <svg v-else-if="type === 'entrenamiento'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            <svg v-else-if="type === 'nutricion'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12" />
            </svg>
            <svg v-else-if="type === 'suplementos'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </div>

          <!-- Label + streak + compliance -->
          <div class="min-w-0 flex-1 text-left">
            <h3 class="text-sm font-semibold text-wc-text">{{ habit.label || getHabitLabel(type) }}</h3>
            <p v-if="habit.streak" class="mt-0.5 text-xs text-wc-text-tertiary">
              Racha: <span class="font-data font-bold" :class="habit.streak >= 7 ? 'text-wc-accent' : 'text-wc-text-secondary'">{{ habit.streak }}</span> dias
            </p>
          </div>

          <!-- Checkmark -->
          <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-all duration-300"
            :class="habit.completed ? 'bg-emerald-500 text-white' : 'border-2 border-wc-border'"
          >
            <svg v-if="habit.completed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
          </div>
        </button>
      </div>

      <!-- ── Compliance por hábito (30 días) ─────────────────────────────── -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cumplimiento — Ultimos 30 dias</h3>
        <div class="space-y-3">
          <div v-for="(habit, type) in todayHabits" :key="`compliance-${type}`">
            <div class="mb-1.5 flex items-center justify-between">
              <span class="text-sm font-medium text-wc-text">{{ habit.label || getHabitLabel(type) }}</span>
              <div class="flex items-center gap-2">
                <span class="font-data text-xs text-wc-text-tertiary">
                  {{ complianceDays(habit.compliance ?? 0) }}/30 dias
                </span>
                <span
                  class="font-data text-sm font-bold tabular-nums"
                  :class="complianceTextClass(habit.compliance ?? 0)"
                >
                  {{ habit.compliance ?? 0 }}%
                </span>
              </div>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div
                class="h-full rounded-full transition-all duration-700"
                :class="complianceBarClass(habit.compliance ?? 0)"
                :style="{ width: `${habit.compliance ?? 0}%` }"
              ></div>
            </div>
          </div>
        </div>
        <!-- Legend -->
        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5">
          <div class="flex items-center gap-1.5">
            <div class="h-2 w-6 rounded-full bg-emerald-500"></div>
            <span class="text-[10px] text-wc-text-tertiary">&ge; 70% excelente</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="h-2 w-6 rounded-full bg-amber-500"></div>
            <span class="text-[10px] text-wc-text-tertiary">40–69% en progreso</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="h-2 w-6 rounded-full bg-wc-accent"></div>
            <span class="text-[10px] text-wc-text-tertiary">&lt; 40% mejorar</span>
          </div>
        </div>
      </div>

      <!-- ── Heatmap 30 días ─────────────────────────────────────────────── -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Actividad — Ultimos 30 dias</h3>

        <!-- Grid: 10 cols on mobile, 15 cols on sm+ -->
        <div class="grid grid-cols-10 gap-1.5 sm:grid-cols-15">
          <div
            v-for="day in heatmapData"
            :key="day.date"
            class="group relative aspect-square cursor-default rounded-sm transition-all duration-150"
            :class="[
              heatmapClass(day),
              isToday(day.date) ? 'ring-2 ring-wc-accent ring-offset-1 ring-offset-wc-bg-tertiary' : '',
              'hover:brightness-125',
            ]"
            @mouseenter="showTooltip(day, $event)"
            @mouseleave="hideTooltip"
          >
            <!-- Day number (visible on larger cells when sm grid) -->
            <span class="absolute inset-0 hidden items-center justify-center text-[8px] font-data font-semibold text-white/60 sm:flex">
              {{ day.day }}
            </span>
          </div>
        </div>

        <!-- Tooltip (fixed position via JS) -->
        <Teleport to="body">
          <Transition name="fade">
            <div
              v-if="hoveredDay"
              class="pointer-events-none fixed z-50 -translate-x-1/2 -translate-y-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 shadow-xl"
              :style="{ left: `${hoveredDay.x}px`, top: `${hoveredDay.y}px` }"
            >
              <p class="text-xs font-semibold text-wc-text">{{ formatTooltipDate(hoveredDay.date) }}</p>
              <p class="mt-0.5 text-[11px] text-wc-text-secondary">
                <template v-if="hoveredDay.count === 0">Sin habitos registrados</template>
                <template v-else>
                  <span class="font-data font-bold text-emerald-400">{{ hoveredDay.count }}</span>
                  /{{ hoveredDay.total }} habitos completados
                </template>
              </p>
            </div>
          </Transition>
        </Teleport>

        <!-- Legend row -->
        <div class="mt-3 flex items-center justify-end gap-1.5">
          <span class="text-[10px] text-wc-text-tertiary">Menos</span>
          <div v-for="cls in LEVEL_CLASSES" :key="cls" class="h-3 w-3 rounded-sm" :class="cls"></div>
          <span class="text-[10px] text-wc-text-tertiary">Mas</span>
        </div>
      </div>

      <!-- ── Weekly bar chart ────────────────────────────────────────────── -->
      <div v-if="weeklyData.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Esta semana</h3>
        <div class="flex items-end gap-1">
          <div
            v-for="day in weeklyData"
            :key="day.date"
            class="flex-1 text-center"
            :class="day.isFuture ? 'opacity-30' : ''"
          >
            <div class="relative mx-auto mb-1 w-full" style="height: 48px;">
              <div
                class="absolute bottom-0 w-full rounded-t-sm transition-all duration-500"
                :class="day.completed >= day.total && !day.isFuture ? 'bg-emerald-500'
                      : day.completed > 0 ? 'bg-wc-accent/60'
                      : 'bg-wc-bg-secondary'"
                :style="{ height: day.isFuture ? '4px' : `${Math.max(4, Math.round((day.completed / (day.total || 1)) * 48))}px` }"
              ></div>
            </div>
            <span
              class="text-[10px]"
              :class="day.isToday ? 'font-bold text-wc-accent' : 'text-wc-text-tertiary'"
            >
              {{ day.dayName }}
            </span>
          </div>
        </div>
      </div>

    </div>
  </ClientLayout>
</template>

<style scoped>
@keyframes confetti-fall {
  0%   { transform: translateY(-20px) rotate(0deg);   opacity: 1; }
  100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
}

.fade-enter-active,
.fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }
</style>
