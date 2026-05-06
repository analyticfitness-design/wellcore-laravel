<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import { localDateStr } from '../../composables/useDate';
import ClientLayout from '../../layouts/ClientLayout.vue';
import WcErrorState from '../../components/WcErrorState.vue';
import PlanOnboarding from '../../components/PlanOnboarding.vue';
import DashboardHero from '../../components/dashboard/DashboardHero.vue';
import DashboardPlanAlert from '../../components/dashboard/DashboardPlanAlert.vue';
import DashboardStats from '../../components/dashboard/DashboardStats.vue';
import DashboardGroupPulse from '../../components/dashboard/DashboardGroupPulse.vue';
import DashboardCheckin from '../../components/dashboard/DashboardCheckin.vue';
import DashboardMissions from '../../components/dashboard/DashboardMissions.vue';
import DashboardCoach from '../../components/dashboard/DashboardCoach.vue';
import DashboardActivity from '../../components/dashboard/DashboardActivity.vue';
import DashboardTimeline from '../../components/dashboard/DashboardTimeline.vue';
import DashboardHeatmap from '../../components/dashboard/DashboardHeatmap.vue';
import DashboardWeight from '../../components/dashboard/DashboardWeight.vue';
import DashboardWeeklySummary from '../../components/dashboard/DashboardWeeklySummary.vue';
import DashboardWeeklyGrid from '../../components/dashboard/DashboardWeeklyGrid.vue';
import PullToRefreshIndicator from '../../components/dashboard/PullToRefreshIndicator.vue';
import { useStaggerIn } from '../../composables/dashboard/useStaggerIn';
import { usePullToRefresh } from '../../composables/dashboard/usePullToRefresh';
import { useHaptics } from '../../composables/useHaptics';
import { useGroupPulse } from '../../composables/useGroupPulse';

// Stagger entry: aplica data-stagger-index + fade-in progresivo a las secciones
// hijas directas del contenedor. Respeta prefers-reduced-motion.
const staggerRoot = useStaggerIn();
const haptics = useHaptics();

// Singleton compartido con DashboardGroupPulse (in-flight dedup garantiza
// single-flight aunque ambos consumers monten en paralelo).
const { summary: groupPulseSummary } = useGroupPulse();

// Pull-to-refresh: refetch dashboard + haptic success al completar
const { pullDistance, isRefreshing } = usePullToRefresh(async () => {
    await fetchDashboard();
    haptics.pattern('success');
});

const api = useApi();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const data = ref(null);
const showOnboarding = ref(false);

// Profile completion banner — dismissed for 7 days via localStorage
const PROFILE_BANNER_KEY = 'wc_profile_banner_dismissed';
const profileBannerDismissed = ref((() => {
    const ts = localStorage.getItem(PROFILE_BANNER_KEY);
    if (!ts) return false;
    return Date.now() - parseInt(ts) < 7 * 24 * 60 * 60 * 1000;
})());

function dismissProfileBanner() {
    profileBannerDismissed.value = true;
    localStorage.setItem(PROFILE_BANNER_KEY, String(Date.now()));
}

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

// ── Streak calendar: genera los 90 días alineados Lun-Dom para el heatmap.
//    El color/count lo calcula DashboardHeatmap a partir de data.streakCalendar.
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
        const dateStr = localDateStr(cursor);
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

// ── Plan-specific motivational quotes (client-side, varies by day of week) ──
const PLAN_QUOTES = {
    rise: [
        'El cambio que buscas empieza con la disciplina de hoy.',
        'La ciencia no miente: la constancia reescribe tu biología.',
        'Cada sesión es datos. Cada dato te acerca a quien puedes ser.',
        'RISE no es un programa. Es una decisión que tomas cada mañana.',
        'Transformación real requiere esfuerzo real. Hoy es ese día.',
        'Tu cuerpo responde a las señales que le das. Dale las correctas.',
        'No buscamos perfección. Buscamos progreso medible, sostenido.',
    ],
    elite: [
        'Los elite no descansan en su objetivo, descansan para su objetivo.',
        'El rendimiento máximo no se improvisa: se construye rep a rep.',
        'Tu límite de ayer es tu punto de partida de hoy.',
        'Los que llegan al top hacen lo ordinario con extraordinaria consistencia.',
        'Intensidad sin estrategia es ruido. Tú entrenas con propósito.',
        'La excelencia no es un evento, es un hábito que defiendes cada día.',
        'Cuando todos se detienen, el elite da un paso más. Da ese paso.',
    ],
    metodo: [
        'El método no es perfección, es consistencia implacable.',
        'Proceso sobre resultado. El resultado es consecuencia del proceso.',
        'Los hábitos que construyes hoy son la persona que serás mañana.',
        'No hay atajo al cuerpo que quieres. Hay un método. Este es el tuyo.',
        'La semana más importante es la próxima. Empieza con esta sesión.',
        'Confía en el plan. Los resultados llegan cuando la disciplina se vuelve rutina.',
        'Un día a la vez, una sesión a la vez. Eso es el método en acción.',
    ],
    presencial: [
        'La presencia lo es todo: cuerpo, mente y enfoque en cada sesión.',
        'Cada entrenamiento en persona es una inversión directa en ti.',
        'Tu coach está aquí. Tu esfuerzo también tiene que estarlo.',
        'Lo que construyes en persona, nadie te lo puede quitar.',
        'Conexión real, resultados reales. Eso es lo que logras hoy.',
        'La disciplina que traes al gym se traduce en la vida que llevas afuera.',
        'Hoy no es un día común: es otro día que elegiste mejorar.',
    ],
    esencial: [
        'Cada gran transformación comenzó con un primer paso. El tuyo cuenta.',
        'No necesitas ser el mejor hoy. Solo necesitas ser mejor que ayer.',
        'La salud no es un destino, es un camino. Hoy caminas en la dirección correcta.',
        'Resultados reales vienen de acciones reales. Esta es una de ellas.',
        'No subestimes el poder de la constancia. Los cambios llegan.',
        'Tu cuerpo es capaz de más de lo que crees. Hoy lo demuestras.',
        'Moverse es vivir. Seguir moviéndose es prosperar.',
    ],
    trial: [
        'Bienvenido al inicio de algo diferente. Hoy cuentas.',
        'Los mejores viajes empiezan con curiosidad. La tuya te trajo aquí.',
        'Una semana puede cambiar una perspectiva. Esta es la tuya.',
        'No hay mejor momento para empezar que cuando ya empezaste.',
        'El primer paso siempre es el más importante. Ya lo diste.',
        'Siente la diferencia que hace moverse con propósito.',
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
    if (w >= 5) return { label: 'Semana excepcional', colorClass: 'text-emerald-600 dark:text-emerald-400', desc: `${w} entrenamientos completados. Sigue así.` };
    if (w >= 3) return { label: 'Buen ritmo', colorClass: 'text-sky-600 dark:text-sky-400', desc: `${w} entrenamientos esta semana. Vas por buen camino.` };
    if (w >= 1) return { label: 'En camino', colorClass: 'text-amber-600 dark:text-amber-400', desc: 'Cada sesión cuenta. Intenta sumar una más esta semana.' };
    return { label: 'Nueva semana', colorClass: 'text-wc-accent', desc: 'Es un nuevo comienzo. Tu primera sesión te espera.' };
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
    <!-- Pull-to-refresh indicator (mobile only) -->
    <PullToRefreshIndicator :distance="pullDistance" :refreshing="isRefreshing" />

    <!-- Loading Skeleton -->
    <div v-if="loading" class="wc-shell space-y-6">
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
    <WcErrorState v-else-if="error" :message="error" @retry="fetchDashboard" />

    <!-- Dashboard Content (Target Mobile Redesign) -->
    <div v-else-if="data" class="wc-shell wc-shell--dashboard">
      <main ref="staggerRoot" class="scroll">

      <!-- §1 HERO -->
      <DashboardHero :data="data" :greeting="greeting" :motivational-quote="motivationalQuote" />

      <!-- §2 QUOTE diaria del coach -->
      <section v-if="data.dailyQuote" class="quote section" :style="{ animationDelay: '90ms' }">
        <p class="quote-text">{{ data.dailyQuote }}</p>
        <span class="quote-src">— Tu Coach</span>
      </section>

      <!-- §3 PLAN INDICATOR / banner sin plan -->
      <DashboardPlanAlert :data="data" />

      <!-- §4 BANNER CHECK-IN PENDIENTE (sólo cuando vencido) -->
      <DashboardCheckin
        :data="data"
        :show-checkin-timer="showCheckinTimer"
        :checkin-hours="checkinHours"
        :checkin-minutes="checkinMinutes"
        :checkin-seconds="checkinSeconds"
      />

      <!-- §5 STATS GRID 2x2 -->
      <DashboardStats :data="data" :xp-progress="xpProgress" :trained-ring-offset="trainedRingOffset" />

      <!-- §5b LATIDO DEL GRUPO (span 12 desktop) — self-contained: usa useGroupPulse internamente -->
      <DashboardGroupPulse />

      <!-- §6 PROGRESS TIMELINE (span 8 desktop) + §7 COACH (span 4 desktop) lado a lado -->
      <DashboardTimeline :data="data" :week-markers="weekMarkers" />
      <DashboardCoach :data="data" />

      <!-- §8 MISSIONS (span 12, grid 4 cards desktop) -->
      <DashboardMissions
        :missions="data.dailyMissions || []"
        :peer-counts="groupPulseSummary?.user_vs_group?.missions_peers ?? {}"
      />

      <!-- §9 HEATMAP (span 8) + §10 WEIGHT (span 4) lado a lado -->
      <DashboardHeatmap
        :data="data"
        :calendar-days="calendarDays"
        :user-vs-group="(groupPulseSummary?.group_size ?? 0) > 1
          ? (groupPulseSummary?.user_vs_group?.weekly_workouts ?? null)
          : null"
      />
      <DashboardWeight :weight-chart-data="data.weightChartData || []" />

      <!-- §11 WEEK (span 4) + §12 ACTIVITY (span 5) + §13 SUMMARY (span 3) -->
      <DashboardWeeklyGrid :week-days="data.weekDays || []" />
      <DashboardActivity :activities="data.recentActivity || []" />
      <DashboardWeeklySummary :data="data" :weekly-summary-message="weeklySummaryMessage" />

      <!-- §14 PROFILE COMPLETION (estilo target — solo cuando score < 80%) -->
      <section
        v-if="data.profileCompletion && data.profileCompletion.score < 80 && !profileBannerDismissed"
        class="card section wc-card-dashboard-profile"
        :style="{ animationDelay: '580ms' }"
      >
        <div class="card-head">
          <div class="card-head-left">
            <span class="card-title">Perfil de comunidad</span>
          </div>
          <span class="card-meta tnum">{{ data.profileCompletion.score }}% completo</span>
        </div>
        <div class="profile-row">
          <div class="profile-art">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"></circle>
              <path d="M4 21a8 8 0 0 1 16 0"></path>
            </svg>
          </div>
          <div class="profile-body">
            <div class="profile-title">Completa tu perfil</div>
            <div v-if="data.profileCompletion.missing.length" class="profile-sub">
              Falta: {{ data.profileCompletion.missing.slice(0, 3).map(m => m.label).join(', ') }}{{ data.profileCompletion.missing.length > 3 ? '…' : '' }}
            </div>
          </div>
          <button class="profile-cta" @click="$router.push('/client/profile')">
            Completar
          </button>
          <button class="profile-close" @click="dismissProfileBanner" aria-label="Cerrar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 18 18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </section>

      <!-- Espaciado final -->
      <div style="height: 16px" aria-hidden="true"></div>

      </main>
    </div>

    <!-- BLOQUES SECUNDARIOS LEGADO (onboarding primeros pasos) — fuera del wrapper .wc-shell -->
    <div v-if="data && !loading && !error" class="space-y-6 mt-4">

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!-- 3d. PRIMEROS PASOS — solo primeros 3 días                      -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="data.gettingStarted?.show" class="rounded-xl border border-violet-500/20 bg-gradient-to-br from-violet-500/05 to-wc-bg-tertiary p-5">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/15">
              <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div>
              <h2 class="font-display text-base tracking-wider text-wc-text">PRIMEROS PASOS</h2>
              <p class="text-xs text-wc-text-tertiary">Completa tu perfil de inicio</p>
            </div>
          </div>
          <span v-if="data.gettingStarted.daysLeft > 0"
            class="shrink-0 rounded-full bg-violet-500/15 px-2.5 py-0.5 text-xs font-semibold text-violet-400">
            {{ data.gettingStarted.daysLeft }} día{{ data.gettingStarted.daysLeft !== 1 ? 's' : '' }} restante{{ data.gettingStarted.daysLeft !== 1 ? 's' : '' }}
          </span>
        </div>

        <!-- Checklist -->
        <div class="space-y-2.5">
          <!-- Fotos de progreso -->
          <button
            @click="$router.push('/client/photos')"
            class="flex w-full items-center gap-3 rounded-xl border p-3.5 text-left transition-all"
            :class="data.gettingStarted.hasPhotos
              ? 'border-emerald-500/20 bg-emerald-500/05 cursor-default'
              : 'border-wc-border bg-wc-bg hover:border-violet-500/40 hover:bg-violet-500/05'"
          >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
              :class="data.gettingStarted.hasPhotos ? 'bg-emerald-500' : 'bg-wc-bg-secondary border border-wc-border'">
              <svg v-if="data.gettingStarted.hasPhotos" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              <span v-else class="text-xs font-bold text-wc-text-tertiary">1</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold" :class="data.gettingStarted.hasPhotos ? 'text-emerald-400 line-through opacity-70' : 'text-wc-text'">
                Sube tus fotos de progreso
              </p>
              <p class="text-xs text-wc-text-tertiary">Frente, perfil y espalda para comparar tu avance</p>
            </div>
            <svg v-if="!data.gettingStarted.hasPhotos" class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>

          <!-- Métricas -->
          <button
            @click="$router.push('/client/metrics')"
            class="flex w-full items-center gap-3 rounded-xl border p-3.5 text-left transition-all"
            :class="data.gettingStarted.hasMetrics
              ? 'border-emerald-500/20 bg-emerald-500/05 cursor-default'
              : 'border-wc-border bg-wc-bg hover:border-violet-500/40 hover:bg-violet-500/05'"
          >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
              :class="data.gettingStarted.hasMetrics ? 'bg-emerald-500' : 'bg-wc-bg-secondary border border-wc-border'">
              <svg v-if="data.gettingStarted.hasMetrics" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              <span v-else class="text-xs font-bold text-wc-text-tertiary">2</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold" :class="data.gettingStarted.hasMetrics ? 'text-emerald-400 line-through opacity-70' : 'text-wc-text'">
                Registra tu peso y métricas
              </p>
              <p class="text-xs text-wc-text-tertiary">Tu punto de partida es el dato más importante</p>
            </div>
            <svg v-if="!data.gettingStarted.hasMetrics" class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <!-- Completado -->
        <div v-if="data.gettingStarted.hasPhotos && data.gettingStarted.hasMetrics"
          class="mt-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 flex items-center gap-3">
          <svg class="h-5 w-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <p class="text-sm font-semibold text-emerald-400">Perfil inicial completo. Tu punto de partida queda registrado.</p>
        </div>
      </div>

      <!-- Espaciado final para que el contenido no quede pegado al bottom nav mobile -->
      <div class="h-4 sm:h-0" aria-hidden="true"></div>

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
