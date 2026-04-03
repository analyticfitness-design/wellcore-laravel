<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';
import PlanOnboarding from '../../components/PlanOnboarding.vue';

const api = useApi();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const data = ref(null);
const showOnboarding = ref(false);

// Greeting (time-based, computed client-side)
const greeting = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? 'Buenos dias' : h < 18 ? 'Buenas tardes' : 'Buenas noches';
});

// Fetch dashboard data
async function fetchDashboard() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/client/dashboard');
        data.value = response.data;
        // Cache user name for layout
        if (response.data.clientName) {
            localStorage.setItem('wc_user_name', response.data.clientName);
        }
        if (!response.data.onboardingCompleted) {
            showOnboarding.value = true;
        }
        // Start check-in timer after data loads
        startCheckinTimer();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el dashboard';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchDashboard();
});

// ── Streak calendar helpers ──
function getCalendarColor(count) {
    if (count >= 5) return 'bg-wc-accent';
    if (count >= 4) return 'bg-wc-accent/80';
    if (count >= 3) return 'bg-wc-accent/60';
    if (count === 2) return 'bg-wc-accent/40';
    if (count === 1) return 'bg-wc-accent/20';
    return 'bg-wc-bg-secondary';
}

function generateCalendarDays() {
    const today = new Date();
    const days = [];
    const start = new Date(today);
    start.setDate(start.getDate() - 90);
    // Align to Monday (1 = Monday)
    const dayOfWeek = start.getDay();
    const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    start.setDate(start.getDate() - diff);

    const end = new Date(today);
    // Align end to Sunday
    const endDow = end.getDay();
    const endDiff = endDow === 0 ? 0 : 7 - endDow;
    end.setDate(end.getDate() + endDiff);

    const cursor = new Date(start);
    while (cursor <= end) {
        const dateStr = cursor.toISOString().split('T')[0];
        const isFuture = cursor > today;
        const ninetyDaysAgo = new Date(today);
        ninetyDaysAgo.setDate(ninetyDaysAgo.getDate() - 90);
        const isBeforeRange = cursor < ninetyDaysAgo;
        const isToday = cursor.toDateString() === today.toDateString();

        days.push({
            date: dateStr,
            displayDate: `${String(cursor.getDate()).padStart(2, '0')}/${String(cursor.getMonth() + 1).padStart(2, '0')}`,
            isFuture,
            isBeforeRange,
            isToday,
        });
        cursor.setDate(cursor.getDate() + 1);
    }
    return days;
}

const calendarDays = generateCalendarDays();

function getCalendarCount(dateStr) {
    if (!data.value?.streakCalendar) return 0;
    return data.value.streakCalendar[dateStr] || 0;
}

// ── Weight chart helpers ──
function getWeightBarHeight(weight, min, range) {
    if (range === 0) return 50;
    return ((weight - min) / range) * 70 + 30;
}

// ── Check-in countdown (module-level timer — not reactive) ──
const checkinHours = ref('00');
const checkinMinutes = ref('00');
const checkinSeconds = ref('00');
let checkinInterval = null;

function startCheckinTimer() {
    if (!data.value?.nextCheckinDate) return;
    // Only show live timer if check-in is within 24 hours and not past due
    if (data.value.daysUntilCheckin > 1 || data.value.daysUntilCheckin <= 0) return;

    const target = new Date(data.value.nextCheckinDate);
    if (isNaN(target.getTime())) return;

    function tick() {
        const now = new Date();
        let diff = Math.max(0, Math.floor((target - now) / 1000));
        checkinHours.value = String(Math.floor(diff / 3600)).padStart(2, '0');
        checkinMinutes.value = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
        checkinSeconds.value = String(diff % 60).padStart(2, '0');
    }

    tick();
    checkinInterval = setInterval(tick, 1000);
}

const showCheckinTimer = computed(() => {
    if (!data.value) return false;
    return data.value.daysUntilCheckin >= 0 && data.value.daysUntilCheckin <= 1;
});

onBeforeUnmount(() => {
    if (checkinInterval) clearInterval(checkinInterval);
});

// ── XP progress ──
const xpProgress = computed(() => {
    if (!data.value) return 0;
    return data.value.xpProgress || 0;
});

// ── Trained this week ring ──
const trainedRingOffset = computed(() => {
    if (!data.value) return 251;
    const circumference = 251;
    const trained = Math.min(data.value.trainedThisWeek || 0, 7);
    return circumference - (circumference * trained / 7);
});

// ── Mission status class ──
function getMissionStatusClass(completed) {
    return completed
        ? 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
        : 'border-wc-border bg-wc-bg-tertiary hover:bg-wc-bg-secondary';
}

// ── Mission route mapping (API returns route keys, we map to Vue routes) ──
const missionRouteMap = {
    training: '/client/training',
    checkin: '/client/checkin',
    weight: '/client/metrics',
    nutrition: '/client/nutrition',
};

function getMissionRoute(mission) {
    return missionRouteMap[mission.key] || mission.route || '/client/dashboard';
}

// ── Recent activity type icons ──
function getActivityIconData(type) {
    switch (type) {
        case 'training':
            return {
                bgClass: 'bg-emerald-500/10',
                iconClass: 'text-emerald-500',
                path: 'm4.5 12.75 6 6 9-13.5',
            };
        case 'checkin':
            return {
                bgClass: 'bg-sky-500/10',
                iconClass: 'text-sky-500',
                path: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            };
        case 'payment':
            return {
                bgClass: 'bg-violet-500/10',
                iconClass: 'text-violet-500',
                path: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
            };
        default:
            return {
                bgClass: 'bg-wc-bg-secondary',
                iconClass: 'text-wc-text-tertiary',
                path: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            };
    }
}

// ── Plan-specific motivational quotes (client-side, varies by day of week) ──
const PLAN_QUOTES = {
    rise: [
        'El cambio que buscas empieza con la disciplina de hoy.',
        'La ciencia no miente: la constancia reescribe tu biologia.',
        'Cada sesion es datos. Cada dato te acerca a quien puedes ser.',
        'RISE no es un programa. Es una decision que tomas cada manana.',
        'Transformacion real requiere esfuerzo real. Hoy es ese dia.',
        'Tu cuerpo responde a la senales que le das. Dale las correctas.',
        'No buscamos perfeccion. Buscamos progreso medible, sostenido.',
    ],
    elite: [
        'Los elite no descansan en su objetivo, descansan para su objetivo.',
        'El rendimiento maximo no se improvisa: se construye rep a rep.',
        'Tu limite de ayer es tu punto de partida de hoy.',
        'Los que llegan al top hacen lo ordinario con extraordinaria consistencia.',
        'Intensidad sin estrategia es ruido. Tu entrenas con proposito.',
        'La excelencia no es un evento, es un habito que defiendes cada dia.',
        'Cuando todos se detienen, el elite da un paso mas. Da ese paso.',
    ],
    metodo: [
        'El metodo no es perfeccion, es consistencia implacable.',
        'Proceso sobre resultado. El resultado es consecuencia del proceso.',
        'Los habitos que construyes hoy son la persona que seras manana.',
        'No hay atajo al cuerpo que quieres. Hay un metodo. Este es el tuyo.',
        'La semana mas importante es la proxima. Empieza con esta sesion.',
        'Confia en el plan. Los resultados llegan cuando la disciplina se vuelve rutina.',
        'Un dia a la vez, una sesion a la vez. Eso es el metodo en accion.',
    ],
    presencial: [
        'La presencia lo es todo: cuerpo, mente y enfoque en cada sesion.',
        'Cada entrenamiento en persona es una inversion directa en ti.',
        'Tu coach esta aqui. Tu esfuerzo tambien tiene que estarlo.',
        'Lo que construyes en persona, nadie te lo puede quitar.',
        'Conexion real, resultados reales. Eso es lo que logras hoy.',
        'La disciplina que traes al gym se traduce en la vida que llevas afuera.',
        'Hoy no es un dia comun: es otro dia que eligiste mejorar.',
    ],
    esencial: [
        'Cada gran transformacion comenzo con un primer paso. El tuyo cuenta.',
        'No necesitas ser el mejor hoy. Solo necesitas ser mejor que ayer.',
        'La salud no es un destino, es un camino. Hoy caminas en la direccion correcta.',
        'Resultados reales vienen de acciones reales. Esta es una de ellas.',
        'No subestimes el poder de la constancia. Los cambios llegan.',
        'Tu cuerpo es capaz de mas de lo que crees. Hoy lo demuestras.',
        'Moverse es vivir. Seguir moviendose es prosperar.',
    ],
    trial: [
        'Bienvenido al inicio de algo diferente. Hoy cuentas.',
        'Los mejores viajes empiezan con curiosidad. La tuya te trajo aqui.',
        'Una semana puede cambiar una perspectiva. Esta es la tuya.',
        'No hay mejor momento para empezar que cuando ya empezaste.',
        'El primer paso siempre es el mas importante. Ya lo diste.',
        'Siente la diferencia que hace moverse con proposito.',
        'Esto es solo el comienzo. Y los comienzos son poderosos.',
    ],
};

const motivationalQuote = computed(() => {
    const day = new Date().getDay(); // 0 (Sun) – 6 (Sat)
    const planType = (data.value?.planType || 'esencial').toLowerCase();
    const quotes = PLAN_QUOTES[planType] ?? PLAN_QUOTES.esencial;
    return quotes[day % quotes.length];
});

// ── Weekly summary motivational text ──
const weeklySummaryMessage = computed(() => {
    if (!data.value) return {};
    const w = data.value.lastWeekWorkouts || 0;
    if (w >= 5) return { label: 'Semana excepcional', colorClass: 'text-emerald-600 dark:text-emerald-400', desc: `${w} entrenamientos completados. Sigue asi.` };
    if (w >= 3) return { label: 'Buen ritmo', colorClass: 'text-sky-600 dark:text-sky-400', desc: `${w} entrenamientos esta semana. Vas por buen camino.` };
    if (w >= 1) return { label: 'En camino', colorClass: 'text-amber-600 dark:text-amber-400', desc: 'Cada sesion cuenta. Intenta sumar una mas esta semana.' };
    return { label: 'Nueva semana', colorClass: 'text-wc-accent', desc: 'Es un nuevo comienzo. Tu primera sesion te espera.' };
});

// ── Completed missions count ──
const completedMissionsCount = computed(() => {
    if (!data.value?.dailyMissions) return 0;
    return data.value.dailyMissions.filter(m => m.completed).length;
});

// ── Week markers for plan progress bar (desktop) ──
const weekMarkers = computed(() => {
    if (!data.value) return [];
    const total = data.value.totalWeeks || 12;
    const active = data.value.weeksActive || 0;
    const markers = [];
    for (let i = 1; i <= total; i++) {
        markers.push({
            week: i,
            isActive: i <= active,
            showLabel: i % 3 === 0,
        });
    }
    return markers;
});
</script>

<template>
  <ClientLayout>
    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-6">
      <!-- Greeting skeleton -->
      <div class="space-y-2">
        <div class="h-9 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="h-5 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>

      <!-- Quote skeleton -->
      <div class="h-14 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Plan alert skeleton -->
      <div class="h-14 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Stats skeleton -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Progress bar skeleton -->
      <div class="h-28 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Calendar skeleton -->
      <div class="h-40 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Chart skeleton -->
      <div class="h-64 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Coach skeleton -->
      <div class="h-16 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Check-in countdown skeleton -->
      <div class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Weekly summary skeleton -->
      <div class="h-48 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>

      <!-- Missions skeleton -->
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-20 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>

      <!-- Weekly overview + recent activity skeleton -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="h-56 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary lg:col-span-2"></div>
        <div class="h-56 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
        <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
      </div>
      <h2 class="mt-4 font-display text-xl tracking-wide text-wc-text">Error al cargar</h2>
      <p class="mt-2 text-sm text-wc-text-secondary">{{ error }}</p>
      <button
        @click="fetchDashboard"
        class="mt-6 rounded-xl bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-wc-accent-hover focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
      >
        Reintentar
      </button>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="data" class="space-y-6">

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- GREETING SECTION + QUICK ACTIONS (desktop)                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- M64 — Hero Premium: orbe rojo + grain + glass -->
      <div class="wc-hero-premium relative overflow-hidden rounded-2xl">
        <!-- Orbe rojo superior -->
        <div class="pointer-events-none absolute -top-10 left-1/2 h-52 w-80 -translate-x-1/2 rounded-full opacity-100" style="background:radial-gradient(ellipse,rgba(220,38,38,0.28) 0%,transparent 70%);filter:blur(32px);z-index:0;"></div>
        <!-- Contenido glass -->
        <div class="relative z-10 flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between" style="backdrop-filter:blur(24px) saturate(1.8);">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
            {{ greeting }}, {{ data.clientName }}
          </h1>
          <div v-if="data.planLabel" class="mt-2 flex items-center gap-2">
            <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">
              Plan {{ data.planLabel }}
            </span>
          </div>
          <!-- Plan-specific motivational phrase -->
          <div class="mt-3 flex items-start gap-2">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-wc-accent/60" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Z" />
            </svg>
            <p class="text-sm italic leading-snug text-wc-text-secondary/70">{{ motivationalQuote }}</p>
          </div>
        </div>

        <!-- Quick actions (desktop) -->
        <div class="hidden items-center gap-2 sm:flex">
          <RouterLink
            to="/client/plan"
            class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Registrar entrenamiento
          </RouterLink>
          <RouterLink
            to="/client/checkin"
            class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary"
          >
            Hacer check-in
          </RouterLink>
        </div>
        </div><!-- /glass content -->
      </div><!-- /wc-hero-premium -->

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- DAILY MOTIVATIONAL QUOTE                                      -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.dailyQuote" class="flex items-start gap-3 rounded-xl border border-wc-border/50 bg-wc-bg-tertiary/50 px-4 py-3">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 mt-0.5">
          <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm italic text-wc-text-tertiary leading-relaxed">
            &ldquo;{{ data.dailyQuote }}&rdquo;
          </p>
          <p class="mt-1 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary/60">&mdash; Tu coach</p>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- PLAN ALERT                                                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="!data.hasActivePlan" class="flex items-start gap-4 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
          <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-wc-text">No tienes un plan activo</p>
          <p class="mt-0.5 text-sm text-wc-text-tertiary">Contacta a tu coach para que te asigne un plan de entrenamiento.</p>
        </div>
        <RouterLink
          to="/client/chat"
          class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover"
        >
          Contactar coach
        </RouterLink>
      </div>
      <div v-else class="flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
          <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <span class="text-sm text-wc-text-tertiary">
          Plan
          <span v-if="data.planPhase" class="font-semibold capitalize text-wc-text">{{ data.planPhase }}</span>
          activo &mdash; Dia <span class="font-semibold text-wc-text">{{ data.planDaysActive }}</span>
        </span>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- STATS CARDS                                                   -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Streak with Flame -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium uppercase tracking-wider text-wc-text-tertiary">Racha</span>
            <div :class="['flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10', (data.streakDays || 0) >= 3 ? 'animate-pulse' : '']">
              <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-display text-4xl text-wc-accent" style="line-height:1">{{ data.streakDays || 0 }}</p>
          <p class="mt-1 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">dias consecutivos</p>
        </div>

        <!-- Check-ins this month -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-display text-4xl text-wc-accent" style="line-height:1">{{ data.checkinsThisMonth || 0 }}</p>
          <p class="mt-1 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">este mes</p>
        </div>

        <!-- XP + Level -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium uppercase tracking-wider text-wc-text-tertiary">Nivel {{ data.level || 1 }}</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-display text-4xl text-wc-accent" style="line-height:1">{{ (data.xpTotal || 0).toLocaleString() }}</p>
          <p class="mt-1 text-sm font-semibold uppercase tracking-widest text-wc-text-tertiary">XP total</p>
          <!-- XP Progress bar -->
          <div class="mt-3">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div
                class="h-full rounded-full bg-violet-500 transition-all duration-500"
                :style="{ width: xpProgress + '%' }"
              ></div>
            </div>
            <p class="mt-1 text-[10px] text-wc-text-tertiary">
              {{ ((data.xpTotal || 0) % (data.xpForNextLevel || 200)).toLocaleString() }} / {{ (data.xpForNextLevel || 200).toLocaleString() }} XP
            </p>
          </div>
        </div>

        <!-- Days trained this week — Progress Ring -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-transform hover:-translate-y-0.5">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium uppercase tracking-wider text-wc-text-tertiary">Esta semana</span>
          </div>
          <div class="mt-3 flex items-center gap-3">
            <svg width="60" height="60" viewBox="0 0 86 86" class="shrink-0">
              <circle cx="43" cy="43" r="40" fill="none" stroke="var(--color-wc-border)" stroke-width="6" />
              <circle
                cx="43" cy="43" r="40" fill="none" stroke="#DC2626" stroke-width="6"
                stroke-linecap="round"
                :stroke-dasharray="251"
                :stroke-dashoffset="trainedRingOffset"
                class="transition-all duration-700"
                style="transform: rotate(-90deg); transform-origin: center;"
              />
              <text x="43" y="43" text-anchor="middle" dominant-baseline="central"
                    fill="var(--color-wc-text)" font-family="var(--font-data)" font-size="18" font-weight="700">
                {{ data.trainedThisWeek || 0 }}/7
              </text>
            </svg>
            <div>
              <p class="text-sm text-wc-text-tertiary">dias entrenados</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- PLAN PROGRESS TIMELINE                                        -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.hasActivePlan" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="font-display text-lg tracking-wide text-wc-text">Tu progreso</h2>
          <span class="text-sm text-wc-text-tertiary">
            Semana {{ Math.min(data.weeksActive || 0, data.totalWeeks || 12) }} de {{ data.totalWeeks || 12 }}
          </span>
        </div>

        <div class="relative">
          <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div
              class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700 ease-out"
              :style="{ width: (data.progressPercent || 0) + '%' }"
            ></div>
          </div>
          <div
            class="absolute top-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-700"
            :style="{ left: (data.progressPercent || 0) + '%' }"
          >
            <div class="h-5 w-5 rounded-full border-[3px] border-wc-accent bg-wc-bg-tertiary shadow-lg shadow-wc-accent/30"></div>
          </div>
        </div>

        <!-- Week markers -->
        <div class="mt-3 flex items-center justify-between">
          <div class="text-left">
            <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Inicio</p>
            <p class="font-data text-sm text-wc-text">{{ data.startDate || '--' }}</p>
          </div>
          <!-- Desktop week dots -->
          <div class="hidden items-center gap-0 flex-1 mx-4 sm:flex">
            <div v-for="marker in weekMarkers" :key="marker.week" class="flex flex-1 flex-col items-center">
              <div
                :class="marker.isActive ? 'h-1.5 w-1.5 rounded-full bg-wc-accent/60' : 'h-1 w-1 rounded-full bg-wc-border'"
              ></div>
              <span v-if="marker.showLabel" class="mt-1 text-xs text-wc-text-tertiary">{{ marker.week }}</span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
              {{ (data.weeksActive || 0) >= (data.totalWeeks || 12) ? 'Continuo' : 'Semana 12' }}
            </p>
            <p class="font-data text-sm text-wc-text">{{ data.progressPercent || 0 }}%</p>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- STREAK CALENDAR (90-day heatmap)                              -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-500/10">
              <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-wc-text">Racha de entrenamiento</h3>
            <span v-if="(data.calendarStreak || 0) > 0" class="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2 py-0.5 text-xs font-bold text-orange-500">
              {{ data.calendarStreak }} dia{{ data.calendarStreak !== 1 ? 's' : '' }} seguido{{ data.calendarStreak !== 1 ? 's' : '' }}
            </span>
          </div>
          <span class="hidden text-sm text-wc-text-tertiary sm:inline">Ultimos 90 dias</span>
        </div>

        <!-- Calendar grid -->
        <div class="flex gap-0.5 overflow-x-auto pb-1">
          <!-- Day labels -->
          <div class="flex flex-col gap-0.5 pr-1 shrink-0">
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">L</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">M</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">X</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">J</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">V</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">S</span>
            <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">D</span>
          </div>

          <!-- Grid -->
          <div class="grid grid-flow-col grid-rows-7 gap-0.5 flex-1">
            <div
              v-for="day in calendarDays"
              :key="day.date"
              :class="[
                'h-2.5 w-2.5 rounded-[2px] sm:h-3 sm:w-3 sm:rounded-sm transition-all duration-150 hover:scale-125 hover:z-10 relative',
                day.isFuture || day.isBeforeRange
                  ? 'bg-wc-bg-secondary/30'
                  : getCalendarColor(getCalendarCount(day.date)),
                day.isToday ? 'ring-1 ring-wc-text/30' : ''
              ]"
              :style="day.isFuture ? 'opacity: 0.2' : ''"
              :title="day.displayDate + (getCalendarCount(day.date) ? ' - ' + getCalendarCount(day.date) + ' sesion(es)' : '')"
            ></div>
          </div>
        </div>

        <!-- Legend -->
        <div class="mt-2 flex items-center justify-between">
          <span class="text-xs text-wc-text-tertiary sm:hidden">Ultimos 90 dias</span>
          <div class="ml-auto flex items-center gap-1 text-xs text-wc-text-tertiary">
            <span>Menos</span>
            <div class="h-2 w-2 rounded-[2px] bg-wc-bg-secondary sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
            <div class="h-2 w-2 rounded-[2px] bg-wc-accent/40 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
            <div class="h-2 w-2 rounded-[2px] bg-wc-accent/70 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
            <div class="h-2 w-2 rounded-[2px] bg-wc-accent sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
            <span>Mas</span>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- WEIGHT CHART (CSS bar chart)                                  -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.weightChartData && data.weightChartData.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-wc-text">Tendencia de peso</h3>
          <span class="text-sm text-wc-text-tertiary">Ultimos 90 dias</span>
        </div>

        <div class="flex items-end justify-center gap-1 sm:gap-2 overflow-x-auto" style="height: 140px;">
          <div
            v-for="(entry, idx) in data.weightChartData"
            :key="idx"
            class="group relative flex w-8 sm:w-10 shrink-0 flex-col items-center justify-end"
            style="height: 100%;"
          >
            <!-- Tooltip -->
            <div class="pointer-events-none absolute -top-8 z-10 hidden rounded bg-wc-bg-secondary px-2 py-1 text-xs font-medium text-wc-text shadow-lg group-hover:block">
              {{ Number(entry.weight).toFixed(1) }} kg
            </div>
            <!-- Bar -->
            <div
              class="w-full rounded-t bg-wc-accent/80 transition-all group-hover:bg-wc-accent"
              :style="{
                height: getWeightBarHeight(
                  entry.weight,
                  Math.min(...data.weightChartData.map(e => e.weight)),
                  Math.max(...data.weightChartData.map(e => e.weight)) - Math.min(...data.weightChartData.map(e => e.weight)) || 1
                ) + '%'
              }"
            ></div>
            <!-- Label -->
            <span class="mt-1 w-full truncate text-center text-xs text-wc-text-tertiary">
              {{ entry.date ? String(entry.date).slice(0, 5) : '' }}
            </span>
          </div>
        </div>

        <div class="mt-2 flex justify-between text-sm text-wc-text-tertiary">
          <span>Min: {{ Math.min(...data.weightChartData.map(e => e.weight)).toFixed(1) }} kg</span>
          <span>Max: {{ Math.max(...data.weightChartData.map(e => e.weight)).toFixed(1) }} kg</span>
        </div>
      </div>
      <!-- Weight chart empty state -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="flex flex-col items-center justify-center h-48 text-center">
          <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
          </svg>
          <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de peso aun</p>
          <RouterLink to="/client/metrics" class="mt-2 text-sm font-medium text-wc-accent hover:underline">Registrar peso</RouterLink>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- COACH CARD                                                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.coachName" class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
          <span class="font-display text-sm tracking-wide text-wc-accent">{{ data.coachInitials || 'WC' }}</span>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium uppercase tracking-wider text-wc-text-tertiary">Tu coach</p>
          <p class="truncate text-sm font-semibold text-wc-text">{{ data.coachName }}</p>
        </div>
        <RouterLink
          to="/client/chat"
          class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover shadow-lg shadow-wc-accent/20"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
          </svg>
          Enviar mensaje
        </RouterLink>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- CHECK-IN COUNTDOWN                                            -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <RouterLink
        v-if="data.daysUntilCheckin !== undefined"
        to="/client/checkin"
        :class="[
          'group block rounded-xl border p-4 sm:p-5 transition-colors',
          data.daysUntilCheckin <= 0
            ? 'border-wc-accent/40 bg-wc-accent/10 hover:bg-wc-accent/15'
            : data.daysUntilCheckin <= 2
              ? 'border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/15'
              : 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
        ]"
      >
        <div class="flex items-center gap-4">
          <!-- Icon -->
          <div :class="[
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
            data.daysUntilCheckin <= 0 ? 'bg-wc-accent/20' : data.daysUntilCheckin <= 2 ? 'bg-amber-500/20' : 'bg-emerald-500/15'
          ]">
            <svg v-if="data.daysUntilCheckin <= 0" class="h-5 w-5 text-wc-accent animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <svg v-else-if="data.daysUntilCheckin <= 2" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <svg v-else class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>

          <!-- Text -->
          <div class="min-w-0 flex-1">
            <p v-if="data.daysUntilCheckin <= 0" class="text-sm font-semibold uppercase tracking-wide text-wc-accent">Check-in pendiente</p>
            <p v-else-if="data.daysUntilCheckin <= 2" class="text-sm font-semibold text-amber-600 dark:text-amber-400">
              Check-in en {{ data.daysUntilCheckin }} dia{{ data.daysUntilCheckin !== 1 ? 's' : '' }}
            </p>
            <p v-else class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
              Proximo check-in en {{ data.daysUntilCheckin }} dias
            </p>
            <p v-if="data.daysUntilCheckin <= 0" class="mt-0.5 text-sm text-wc-text-tertiary">Tu check-in semanal esta listo. Envialo ahora.</p>
            <p v-else class="mt-0.5 text-sm text-wc-text-tertiary capitalize">{{ data.nextCheckinDate || '' }}</p>
          </div>

          <!-- Live countdown timer (if < 24h) -->
          <div
            v-if="showCheckinTimer && data.daysUntilCheckin > 0"
            :class="[
              'hidden items-center gap-1 font-data text-lg font-bold tabular-nums sm:flex',
              data.daysUntilCheckin <= 2 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'
            ]"
          >
            <span>{{ checkinHours }}</span><span class="text-wc-text-tertiary">:</span>
            <span>{{ checkinMinutes }}</span><span class="text-wc-text-tertiary">:</span>
            <span>{{ checkinSeconds }}</span>
          </div>

          <!-- Arrow -->
          <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </RouterLink>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- WEEKLY SUMMARY (last week)                                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.hasLastWeekData" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center gap-2">
          <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
            <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
          </div>
          <h2 class="text-sm font-semibold text-wc-text">Resumen semanal</h2>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
          <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekWorkouts || 0 }}</p>
            <p class="mt-0.5 text-sm text-wc-text-tertiary">Entrenamientos</p>
          </div>
          <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekCheckins || 0 }}</p>
            <p class="mt-0.5 text-sm text-wc-text-tertiary">Check-ins</p>
          </div>
          <div class="col-span-2 rounded-xl bg-wc-bg-secondary px-4 py-3 text-center sm:col-span-1">
            <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekWeight || '--' }}</p>
            <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ data.lastWeekWeight ? 'kg actuales' : 'Sin registro' }}</p>
          </div>
        </div>

        <!-- Motivational text based on performance -->
        <div class="mt-4 rounded-xl border border-wc-accent/10 bg-wc-accent/5 px-4 py-2.5">
          <p class="text-sm text-wc-text-tertiary">
            <span :class="['font-semibold', weeklySummaryMessage.colorClass]">{{ weeklySummaryMessage.label }}</span>
            &mdash; {{ weeklySummaryMessage.desc }}
          </p>
        </div>
      </div>
      <!-- Weekly summary empty state (first week) -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-3 flex items-center gap-2">
          <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
            <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
          </div>
          <h2 class="text-sm font-semibold text-wc-text">Resumen semanal</h2>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-wc-accent/10 bg-wc-accent/5 px-4 py-3">
          <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
          </svg>
          <p class="text-sm text-wc-text-tertiary">
            <span class="font-semibold text-wc-text">Tu primera semana</span> &mdash; Completa tu primer entrenamiento y check-in para ver tu resumen aqui.
          </p>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- DAILY MISSIONS                                                -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.dailyMissions && data.dailyMissions.length > 0">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-wc-text">Misiones diarias</h2>
          <span class="text-sm text-wc-text-tertiary">
            {{ completedMissionsCount }}/{{ data.dailyMissions.length }} completadas
          </span>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <RouterLink
            v-for="(mission, idx) in data.dailyMissions"
            :key="mission.key || idx"
            :to="getMissionRoute(mission)"
            :class="[
              'group flex items-center gap-3 rounded-xl border p-4 transition-colors',
              getMissionStatusClass(mission.completed)
            ]"
          >
            <!-- Status icon -->
            <div :class="[
              'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
              mission.completed ? 'bg-emerald-500/15' : 'border-2 border-wc-border'
            ]">
              <!-- Completed checkmark -->
              <svg v-if="mission.completed" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              <!-- Type-specific icons for incomplete missions -->
              <template v-else>
                <!-- Dumbbell icon for training -->
                <svg v-if="mission.key === 'training' || mission.icon === 'dumbbell'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <!-- Check-in icon -->
                <svg v-else-if="mission.key === 'checkin' || mission.icon === 'checkin'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- Scale icon for weight -->
                <svg v-else-if="mission.key === 'weight' || mission.icon === 'scale'" class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 4.97Z" />
                </svg>
                <!-- Nutrition/book icon -->
                <svg v-else class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
              </template>
            </div>

            <!-- Text -->
            <div class="min-w-0 flex-1">
              <p :class="['text-sm font-medium leading-tight', mission.completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-wc-text']">
                {{ mission.title }}
              </p>
              <p class="mt-0.5 text-sm text-wc-text-tertiary">
                {{ mission.completed ? 'Completada' : 'Pendiente' }}
              </p>
            </div>

            <!-- Arrow (incomplete only) -->
            <svg v-if="!mission.completed" class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </RouterLink>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- WEEKLY TRAINING OVERVIEW + RECENT ACTIVITY                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <!-- Weekly training overview (Mon-Sun with checkmarks) -->
        <div v-if="data.weekDays && data.weekDays.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
          <h2 class="text-sm font-semibold text-wc-text">Semana de entrenamiento</h2>
          <p class="mt-1 text-sm text-wc-text-tertiary">Semana {{ new Date().toLocaleDateString('es', { year: 'numeric' }).split('/').pop() }}</p>

          <div class="mt-5 flex items-center justify-between gap-2 sm:justify-start sm:gap-4">
            <div v-for="(day, idx) in data.weekDays" :key="idx" class="flex flex-col items-center gap-2">
              <span :class="['text-sm font-medium text-wc-text-tertiary', day.isToday ? '!text-wc-accent !font-semibold' : '']">
                {{ day.label }}
              </span>
              <!-- Completed day -->
              <div v-if="day.completed" class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 sm:h-12 sm:w-12">
                <svg class="h-5 w-5 text-emerald-500 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <!-- Pending day -->
              <div v-else :class="['flex h-10 w-10 items-center justify-center rounded-full border-2 border-wc-border sm:h-12 sm:w-12', day.isToday ? '!border-wc-accent/40' : '']">
                <div v-if="day.isToday" class="h-2 w-2 rounded-full bg-wc-accent"></div>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="mt-5 flex items-center gap-4 text-sm text-wc-text-tertiary">
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/40"></div>
              Completado
            </div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full border border-wc-border"></div>
              Pendiente
            </div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full bg-wc-accent"></div>
              Hoy
            </div>
          </div>
        </div>

        <!-- Recent activity -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="font-display text-lg tracking-wide text-wc-text">Actividad reciente</h2>

          <ul v-if="data.recentActivity && data.recentActivity.length > 0" class="mt-4 space-y-3">
            <li v-for="(activity, idx) in data.recentActivity" :key="idx" class="flex items-start gap-3">
              <!-- Type-specific icon -->
              <div :class="['mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full', getActivityIconData(activity.type).bgClass]">
                <svg :class="['h-3.5 w-3.5', getActivityIconData(activity.type).iconClass]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="getActivityIconData(activity.type).path" />
                </svg>
              </div>
              <!-- Text -->
              <div class="min-w-0 flex-1">
                <p class="text-sm text-wc-text">{{ activity.description }}</p>
                <p class="text-sm text-wc-text-tertiary">{{ activity.timeAgo }}</p>
              </div>
            </li>
          </ul>

          <!-- Empty activity state -->
          <div v-else class="mt-6 flex flex-col items-center py-4 text-center">
            <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="mt-2 text-sm text-wc-text-tertiary">Sin actividad reciente</p>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- MOBILE QUICK ACTIONS                                          -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 gap-3 sm:hidden">
        <RouterLink
          to="/client/plan"
          class="flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Registrar entrenamiento
        </RouterLink>
        <RouterLink
          to="/client/checkin"
          class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          Hacer check-in
        </RouterLink>
        <RouterLink
          to="/client/plan"
          class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          Ver plan
        </RouterLink>
      </div>

    </div>

    <!-- Plan Onboarding modal -->
    <PlanOnboarding
      v-if="showOnboarding && data"
      :plan-type="data.planType"
      :client-name="data.clientName"
      @completed="showOnboarding = false"
    />
  </ClientLayout>
</template>
