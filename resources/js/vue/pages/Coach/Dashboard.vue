<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import CoachOnboardingChecklist from '../../components/CoachOnboardingChecklist.vue';

const api = useApi();
const loading = ref(true);
const error = ref(null);

const greeting = ref('');
const coachName = ref('');
const coachDaysOld = ref(null); // P5.3: days since coach account created_at

const stats = ref({
    activeClients: 0,
    pendingCheckins: 0,
    unreadMessages: 0,
    ticketsThisMonth: 0,
});
const attentionClients = ref([]);
const recentMessages = ref([]);

// Chart data
const clientProgressData = ref([]);
const checkinFrequencyData = ref([]);

// Animated counter targets — module-level to avoid proxy overhead
const animatedCounters = ref({
    activeClients: 0,
    pendingCheckins: 0,
    unreadMessages: 0,
    ticketsThisMonth: 0,
});
let counterObserver = null;
let counterAnimationFrames = [];

const maxProgressSessions = computed(() => {
    if (clientProgressData.value.length === 0) return 1;
    return Math.max(...clientProgressData.value.map(d => d.sessions), 1);
});

const maxCheckinCount = computed(() => {
    if (checkinFrequencyData.value.length === 0) return 1;
    return Math.max(...checkinFrequencyData.value.map(d => d.count), 1);
});

// SVG line chart coordinates for check-in frequency
const checkinPointCoords = computed(() => {
    const data = checkinFrequencyData.value;
    if (!data || data.length === 0) return [];
    const max = Math.max(...data.map(d => d.count), 1);
    const padding = 20;
    const chartHeight = 200 - padding * 2;
    const stepX = data.length > 1 ? ((data.length - 1) * 80) / (data.length - 1) : 0;

    return data.map((d, i) => ({
        x: padding + i * stepX,
        y: padding + chartHeight - (d.count / max) * chartHeight,
    }));
});

const checkinLinePoints = computed(() => {
    return checkinPointCoords.value.map(p => `${p.x},${p.y}`).join(' ');
});

const checkinAreaPoints = computed(() => {
    const coords = checkinPointCoords.value;
    if (coords.length === 0) return '';
    const padding = 20;
    const first = coords[0];
    const last = coords[coords.length - 1];
    const linePoints = coords.map(p => `${p.x},${p.y}`).join(' ');
    return `${first.x},${200 - padding} ${linePoints} ${last.x},${200 - padding}`;
});

function animateCounter(key, target, duration = 1200) {
    const start = animatedCounters.value[key];
    const startTime = performance.now();

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease-out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        animatedCounters.value[key] = Math.round(start + (target - start) * eased);

        if (progress < 1) {
            const frameId = requestAnimationFrame(step);
            counterAnimationFrames.push(frameId);
        }
    }

    const frameId = requestAnimationFrame(step);
    counterAnimationFrames.push(frameId);
}

function setupCounterObserver() {
    const statsGrid = document.getElementById('stats-grid');
    if (!statsGrid) return;

    counterObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter('activeClients', stats.value.activeClients);
                    animateCounter('pendingCheckins', stats.value.pendingCheckins, 1000);
                    animateCounter('unreadMessages', stats.value.unreadMessages, 1000);
                    animateCounter('ticketsThisMonth', stats.value.ticketsThisMonth);
                    counterObserver?.disconnect();
                }
            });
        },
        { threshold: 0.2 }
    );

    counterObserver.observe(statsGrid);
}

async function loadDashboard() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get('/api/v/coach/dashboard');

        greeting.value = data.greeting || computeGreeting();
        coachName.value = data.coachName || localStorage.getItem('wc_user_name')?.split(' ')[0] || 'Coach';
        coachDaysOld.value = data.coachDaysOld ?? null;

        stats.value = {
            activeClients: data.activeClients ?? 0,
            pendingCheckins: data.pendingCheckins ?? 0,
            unreadMessages: data.unreadMessages ?? 0,
            ticketsThisMonth: 0,
        };
        attentionClients.value = data.attentionClients || [];
        recentMessages.value = data.recentMessages || [];
        clientProgressData.value = data.clientProgressData || [];
        checkinFrequencyData.value = data.checkinFrequencyData || [];

        // Fetch plan tickets created in the last 30 days (in parallel, non-blocking already awaited)
        try {
            const tr = await api.get('/api/v/coach/plan-tickets');
            const list = tr.data?.tickets || [];
            const cutoff = Date.now() - 30 * 24 * 60 * 60 * 1000;
            stats.value.ticketsThisMonth = list.filter(t => {
                const created = t.created_at ? new Date(t.created_at).getTime() : 0;
                return created >= cutoff;
            }).length;
        } catch (_) {
            stats.value.ticketsThisMonth = 0;
        }

        await nextTick();
        setupCounterObserver();
    } catch (e) {
        error.value = 'Error al cargar el dashboard. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
}

function computeGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos dias';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
}

onMounted(loadDashboard);

onBeforeUnmount(() => {
    counterObserver?.disconnect();
    counterAnimationFrames.forEach(id => cancelAnimationFrame(id));
    counterAnimationFrames = [];
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Greeting section -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
            {{ greeting || 'Hola' }}, {{ coachName || 'Coach' }}
          </h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Panel de coach -- resumen de tu equipo</p>
        </div>
        <div class="hidden sm:flex items-center gap-2">
          <RouterLink
            to="/coach/checkins"
            class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Revisar check-ins
          </RouterLink>
          <RouterLink
            to="/coach/messages"
            class="btn-press inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            Enviar mensaje
          </RouterLink>
          <RouterLink
            to="/coach/plan-tickets/nuevo"
            class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586M12 12.75h.008v.008H12v-.008ZM12 15.75h.008v.008H12v-.008ZM12 18.75h.008v.008H12v-.008Z" />
            </svg>
            Crear ticket de plan
          </RouterLink>
        </div>
      </div>

      <!-- P5.3 Onboarding checklist (first 7 days only, dismissible) -->
      <CoachOnboardingChecklist :days-old="coachDaysOld" />

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
              <div class="h-3 w-20 rounded bg-wc-border/50"></div>
              <div class="h-8 w-8 rounded-lg bg-wc-border/30"></div>
            </div>
            <div class="mt-4 h-8 w-12 rounded bg-wc-border/50"></div>
            <div class="mt-2 h-3 w-16 rounded bg-wc-border/30"></div>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div v-for="n in 2" :key="'chart-'+n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 h-72"></div>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 h-64 lg:col-span-2"></div>
          <div class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 h-64"></div>
        </div>
      </template>

      <!-- Error state -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/5 p-8 text-center">
        <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-red-400">{{ error }}</p>
        <button
          @click="loadDashboard"
          class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
          </svg>
          Reintentar
        </button>
      </div>

      <!-- Main content -->
      <template v-else>

        <!-- Stats cards with glow + hover lift + animated counters -->
        <div id="stats-grid" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
          <!-- Active Clients -->
          <div class="card-hover-lift stat-glow-emerald rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ animatedCounters.activeClients }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">asignados a ti</p>
          </div>

          <!-- Pending Check-ins -->
          <div class="card-hover-lift stat-glow-amber rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins pendientes</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold" :class="stats.pendingCheckins > 0 ? 'text-orange-500' : 'text-wc-text'">{{ animatedCounters.pendingCheckins }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">sin responder</p>
          </div>

          <!-- Unread Messages -->
          <div class="card-hover-lift stat-glow-red rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Mensajes</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold" :class="stats.unreadMessages > 0 ? 'text-wc-accent' : 'text-wc-text'">{{ animatedCounters.unreadMessages }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">no leidos</p>
          </div>

          <!-- Tickets this month -->
          <RouterLink
            to="/coach/plan-tickets"
            class="card-hover-lift stat-glow-violet rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 block cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tickets</span>
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
              </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ animatedCounters.ticketsThisMonth }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">este mes</p>
          </RouterLink>
        </div>

        <!-- Charts: Client Progress + Check-in Frequency -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

          <!-- Client Progress (Horizontal Bar Chart — CSS) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-semibold text-wc-text">Progreso de Clientes</h3>
              <span class="text-xs text-wc-text-tertiary">Ultimas 4 semanas</span>
            </div>

            <div v-if="clientProgressData.length > 0" class="space-y-3">
              <div v-for="(item, idx) in clientProgressData" :key="idx" class="group flex items-center gap-3">
                <span class="w-16 shrink-0 truncate text-right text-xs font-medium text-wc-text-secondary">{{ item.name }}</span>
                <div class="relative h-6 flex-1 overflow-hidden rounded bg-wc-bg-secondary">
                  <div
                    class="absolute inset-y-0 left-0 rounded bg-wc-accent/70 transition-all duration-1000 ease-out group-hover:bg-wc-accent"
                    :style="{ width: (item.sessions / maxProgressSessions * 100) + '%' }"
                  ></div>
                </div>
                <span class="w-6 shrink-0 text-right font-data text-xs font-bold text-wc-text">{{ item.sessions }}</span>
              </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center h-52 text-center">
              <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
              <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de entrenamiento</p>
            </div>
          </div>

          <!-- Check-in Frequency (Line-area Chart — CSS/SVG) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-semibold text-wc-text">Frecuencia de Check-ins</h3>
              <span class="text-xs text-wc-text-tertiary">Ultimas 8 semanas</span>
            </div>

            <div v-if="checkinFrequencyData.length > 0" class="relative h-52">
              <!-- SVG line chart -->
              <svg class="h-full w-full" :viewBox="`0 0 ${(checkinFrequencyData.length - 1) * 80 + 40} 200`" preserveAspectRatio="none">
                <!-- Grid lines -->
                <line x1="0" y1="50" :x2="(checkinFrequencyData.length - 1) * 80 + 40" y2="50"
                      stroke="currentColor" stroke-width="0.5" class="text-wc-border" />
                <line x1="0" y1="100" :x2="(checkinFrequencyData.length - 1) * 80 + 40" y2="100"
                      stroke="currentColor" stroke-width="0.5" class="text-wc-border" />
                <line x1="0" y1="150" :x2="(checkinFrequencyData.length - 1) * 80 + 40" y2="150"
                      stroke="currentColor" stroke-width="0.5" class="text-wc-border" />

                <!-- Area fill -->
                <polygon
                  :points="checkinAreaPoints"
                  fill="rgba(139, 92, 246, 0.08)"
                />

                <!-- Line -->
                <polyline
                  :points="checkinLinePoints"
                  fill="none"
                  stroke="#8B5CF6"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="checkin-line"
                />

                <!-- Data points -->
                <circle
                  v-for="(pt, idx) in checkinPointCoords"
                  :key="idx"
                  :cx="pt.x"
                  :cy="pt.y"
                  r="4"
                  fill="#8B5CF6"
                  stroke="#8B5CF6"
                  stroke-width="1"
                  class="transition-all duration-200 hover:r-[7]"
                />
              </svg>

              <!-- X-axis labels -->
              <div class="mt-2 flex justify-between px-1">
                <span
                  v-for="(item, idx) in checkinFrequencyData"
                  :key="idx"
                  class="text-[10px] text-wc-text-tertiary"
                >{{ item.week }}</span>
              </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center h-52 text-center">
              <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de check-ins</p>
            </div>
          </div>
        </div>

        <!-- Attention clients + Recent messages -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

          <!-- Clients needing attention -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
              <h2 class="font-display text-lg tracking-wide text-wc-text">Clientes que necesitan atencion</h2>
              <RouterLink to="/coach/clients" class="text-xs font-medium text-wc-accent hover:underline">Ver todos</RouterLink>
            </div>

            <div v-if="attentionClients.length > 0" class="mt-4 space-y-3">
              <div
                v-for="client in attentionClients"
                :key="client.id"
                class="card-hover-lift flex items-center gap-4 rounded-lg border border-wc-border bg-wc-bg-secondary p-3"
              >
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                  <span class="text-sm font-semibold text-wc-accent">{{ (client.name || 'C').charAt(0) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-wc-text truncate">{{ client.name }}</p>
                    <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                      {{ client.plan }}
                    </span>
                  </div>
                  <div class="mt-0.5 flex items-center gap-3 text-xs text-wc-text-tertiary">
                    <span class="flex items-center gap-1">
                      <svg class="h-3 w-3 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                      {{ client.pending_checkins }} check-in{{ client.pending_checkins > 1 ? 's' : '' }} pendiente{{ client.pending_checkins > 1 ? 's' : '' }}
                    </span>
                    <span>{{ client.oldest_checkin }}</span>
                  </div>
                </div>
                <RouterLink
                  to="/coach/checkins"
                  class="btn-press flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-accent hover:border-wc-accent/30 transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                  </svg>
                </RouterLink>
              </div>
            </div>

            <div v-else class="mt-6 flex flex-col items-center py-8 text-center">
              <svg class="h-10 w-10 text-emerald-500/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              <p class="mt-2 text-sm text-wc-text-tertiary">Todos los check-ins respondidos</p>
              <p class="text-xs text-wc-text-tertiary">Buen trabajo</p>
            </div>
          </div>

          <!-- Recent messages -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
              <h2 class="font-display text-lg tracking-wide text-wc-text">Mensajes recientes</h2>
              <RouterLink to="/coach/messages" class="text-xs font-medium text-wc-accent hover:underline">Ver todos</RouterLink>
            </div>

            <ul v-if="recentMessages.length > 0" class="mt-4 space-y-3">
              <li v-for="(msg, idx) in recentMessages" :key="idx" class="flex items-start gap-3">
                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="msg.is_read ? 'bg-wc-bg-secondary' : 'bg-wc-accent/10'">
                  <svg class="h-3.5 w-3.5" :class="msg.is_read ? 'text-wc-text-tertiary' : 'text-wc-accent'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium" :class="msg.is_read ? 'text-wc-text' : 'text-wc-accent'">{{ msg.client_name }}</p>
                  <p class="text-xs text-wc-text-secondary truncate">{{ msg.message }}</p>
                  <p class="text-[11px] text-wc-text-tertiary">{{ msg.time_ago }}</p>
                </div>
                <div v-if="!msg.is_read" class="mt-2 h-2 w-2 shrink-0 rounded-full bg-wc-accent"></div>
              </li>
            </ul>

            <div v-else class="mt-6 flex flex-col items-center py-4 text-center">
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
              </svg>
              <p class="mt-2 text-sm text-wc-text-tertiary">Sin mensajes recientes</p>
            </div>
          </div>
        </div>

        <!-- Mobile quick actions (sm:hidden) -->
        <div class="grid grid-cols-1 gap-3 sm:hidden">
          <RouterLink
            to="/coach/checkins"
            class="btn-press flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Revisar check-ins
          </RouterLink>
          <RouterLink
            to="/coach/messages"
            class="btn-press flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            Enviar mensaje
          </RouterLink>
          <RouterLink
            to="/coach/clients"
            class="btn-press flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Ver mis clientes
          </RouterLink>
        </div>

      </template>

    </div>
  </CoachLayout>
</template>

