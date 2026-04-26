<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import CoachOnboardingChecklist from '../../components/CoachOnboardingChecklist.vue';

const api = useApi();
const loading = ref(true);
const error = ref(null);

const greeting = ref('');
const coachName = ref('');
const coachDaysOld = ref(null);

const stats = ref({
    activeClients: 0,
    pendingCheckins: 0,
    unreadMessages: 0,
    ticketsThisMonth: 0,
});
const attentionClients = ref([]);
const recentMessages = ref([]);

// New fields
const urgentClientsCount = ref(0);
const todayDateLabel = ref('');
const openTickets = ref(0);
const openTicketsList = ref([]);
const todayActivity = ref([]);
const sparklines = ref({ clients: [], checkins: [], messages: [], tickets: [] });
const fabOpen = ref(false);

// Chart data (preserved from original)
const clientProgressData = ref([]);
const checkinFrequencyData = ref([]);

// Animated counters
const animatedCounters = ref({
    activeClients: 0,
    pendingCheckins: 0,
    unreadMessages: 0,
    ticketsThisMonth: 0,
    openTickets: 0,
});

// Module-level handles — not reactive
let counterObserver = null;
let counterAnimationFrames = [];

function animateCounter(key, target, duration = 1200) {
    const start = animatedCounters.value[key];
    const startTime = performance.now();

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
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
                    animateCounter('openTickets', openTickets.value, 1000);
                    counterObserver?.disconnect();
                }
            });
        },
        { threshold: 0.2 }
    );

    counterObserver.observe(statsGrid);
}

function sparklinePoints(data, width = 60, height = 24) {
    if (!data || data.length < 2) return '';
    const max = Math.max(...data, 1);
    return data.map((v, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - (v / max) * (height - 4) - 2;
        return `${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
}

function computeGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos dias';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
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

        // New fields
        urgentClientsCount.value = data.urgentClientsCount ?? 0;
        todayDateLabel.value = data.todayDateLabel ?? '';
        openTickets.value = data.openTickets ?? 0;
        openTicketsList.value = data.openTicketsList ?? [];
        todayActivity.value = data.todayActivity ?? [];
        sparklines.value = data.sparklines ?? { clients: [], checkins: [], messages: [], tickets: [] };

        // Fallback: fetch tickets this month if API doesn't include it
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

onMounted(loadDashboard);

onBeforeUnmount(() => {
    counterObserver?.disconnect();
    counterAnimationFrames.forEach(id => cancelAnimationFrame(id));
    counterAnimationFrames = [];
});
</script>

<template>
  <CoachLayout :urgent-count="urgentClientsCount">
    <div>

      <!-- MOBILE: Hero + Quick Actions (lg:hidden) -->
      <div class="lg:hidden pt-3">
        <!-- Hero card: rojo si hay urgentes, oscuro si todo OK -->
        <div
          :class="urgentClientsCount > 0 ? 'wc-hero-accent' : 'bg-wc-bg-tertiary'"
          class="wc-noise rounded-card border-l-[3px] p-4 mb-3 border border-wc-border"
          :style="{ borderLeftColor: urgentClientsCount > 0 ? 'var(--color-wc-accent)' : 'rgba(220,38,38,0.35)' }"
        >
          <div class="font-mono text-[10px] text-wc-text-tertiary uppercase tracking-wider">{{ todayDateLabel }}</div>
          <div v-if="urgentClientsCount > 0" class="mt-2">
            <div class="font-display text-xl uppercase text-wc-text">{{ urgentClientsCount }} CLIENTES NECESITAN ATENCIÓN</div>
            <div class="text-sm text-wc-text-secondary mt-0.5">{{ stats.pendingCheckins }} check-ins · {{ stats.unreadMessages }} sin leer</div>
          </div>
          <div v-else class="mt-2 font-display text-xl uppercase text-wc-text-secondary">AL DÍA · SIN PENDIENTES</div>
          <div class="mt-3">
            <div class="h-1.5 rounded-full bg-wc-border overflow-hidden">
              <div
                class="h-full rounded-full bg-wc-accent transition-all duration-700"
                :style="{ width: urgentClientsCount > 0 ? '25%' : '100%', opacity: urgentClientsCount > 0 ? '1' : '0.4' }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Quick actions strip (scroll horizontal) -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 mb-3">
          <RouterLink to="/coach/checkins" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 relative rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="11" width="13" height="13" rx="2"></rect><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path></svg>
            <span class="text-[10px] font-medium text-wc-text-secondary">Check-ins</span>
            <span v-if="stats.pendingCheckins > 0" class="absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-wc-accent text-[8px] font-bold text-white flex items-center justify-center">{{ stats.pendingCheckins }}</span>
          </RouterLink>
          <RouterLink to="/coach/messages" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 relative rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>
            <span class="text-[10px] font-medium text-wc-text-secondary">Mensajes</span>
            <span v-if="stats.unreadMessages > 0" class="absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-wc-accent text-[8px] font-bold text-white flex items-center justify-center">{{ stats.unreadMessages }}</span>
          </RouterLink>
          <RouterLink to="/coach/plan-tickets/nuevo" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span class="text-[10px] font-medium text-wc-text-secondary">Tickets</span>
          </RouterLink>
          <RouterLink to="/coach/analytics" class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 min-w-[72px] h-[72px]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            <span class="text-[10px] font-medium text-wc-text-secondary">Analítica</span>
          </RouterLink>
        </div>
      </div>

      <!-- DESKTOP: Alert bar + Hero heading (hidden lg:block) -->
      <div class="hidden lg:block pt-6">
        <!-- Alert bar: solo si hay urgentes -->
        <div v-if="urgentClientsCount > 0" class="flex items-center justify-between px-5 py-3 rounded-card border-l-4 bg-wc-accent/10 border-wc-accent mb-5">
          <div class="flex items-center gap-2 text-sm text-wc-text">
            <svg class="w-4 h-4 text-wc-accent shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <span>{{ urgentClientsCount }} clientes necesitan atención urgente hoy</span>
          </div>
          <RouterLink to="/coach/checkins" class="text-sm font-medium text-wc-accent hover:underline shrink-0">Ver todos →</RouterLink>
        </div>

        <!-- H1 Hero desktop -->
        <div class="mb-6">
          <div class="font-mono text-xs text-wc-text-tertiary uppercase tracking-widest mb-1">{{ todayDateLabel }}</div>
          <h1 class="font-display text-4xl uppercase tracking-wide text-wc-text">HOY</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            <template v-if="urgentClientsCount > 0">
              {{ urgentClientsCount }} clientes en riesgo · {{ stats.pendingCheckins }} check-ins pendientes · {{ stats.unreadMessages }} mensajes sin leer
            </template>
            <template v-else>Todo al día · Sin pendientes urgentes</template>
          </p>
          <div class="mt-4 flex items-center gap-2">
            <RouterLink to="/coach/checkins" class="inline-flex items-center gap-2 rounded-button border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
              Revisar check-ins
            </RouterLink>
            <RouterLink to="/coach/messages" class="inline-flex items-center gap-2 rounded-button border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
              Enviar mensaje
            </RouterLink>
            <RouterLink to="/coach/plan-tickets/nuevo" class="inline-flex items-center gap-2 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
              + Crear ticket
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          <div v-for="n in 4" :key="n" class="animate-pulse rounded-card border border-wc-border bg-wc-bg-tertiary h-[130px]"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-card border border-red-500/30 bg-red-500/5 p-8 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="loadDashboard" class="mt-4 inline-flex items-center gap-2 rounded-button bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Main content -->
      <template v-else>

        <!-- KPI Stats Grid: 2x2 mobile, 4x1 desktop — WellCore dark + red -->
        <div id="stats-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          <!-- Clientes activos — rojo primario -->
          <div class="wc-stat-primary wc-noise stat-card relative rounded-card overflow-hidden p-4 border border-wc-border">
            <svg v-if="sparklines.clients.length" class="absolute top-3 right-3 opacity-65" width="60" height="24" viewBox="0 0 60 24" aria-hidden="true">
              <polyline :points="sparklinePoints(sparklines.clients)" fill="none" stroke="#DC2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="mt-6 font-display text-4xl leading-none text-wc-text font-data">{{ animatedCounters.activeClients }}</div>
            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">CLIENTES ACTIVOS</div>
          </div>

          <!-- Check-ins — cálido (atención/pendiente) -->
          <div class="wc-stat-warm wc-noise stat-card relative rounded-card overflow-hidden p-4 border border-wc-border">
            <svg v-if="sparklines.checkins.length" class="absolute top-3 right-3 opacity-65" width="60" height="24" viewBox="0 0 60 24" aria-hidden="true">
              <polyline :points="sparklinePoints(sparklines.checkins)" fill="none" stroke="#B45309" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="mt-6 font-display text-4xl leading-none text-wc-text font-data">{{ animatedCounters.pendingCheckins }}</div>
            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">CHECK-INS</div>
          </div>

          <!-- Mensajes — rojo primario -->
          <div class="wc-stat-primary wc-noise stat-card relative rounded-card overflow-hidden p-4 border border-wc-border">
            <svg v-if="sparklines.messages.length" class="absolute top-3 right-3 opacity-65" width="60" height="24" viewBox="0 0 60 24" aria-hidden="true">
              <polyline :points="sparklinePoints(sparklines.messages)" fill="none" stroke="#DC2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="mt-6 font-display text-4xl leading-none text-wc-text font-data">{{ animatedCounters.unreadMessages }}</div>
            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">MENSAJES</div>
          </div>

          <!-- Tickets — muted oscuro con toque rojo -->
          <div class="wc-stat-muted wc-noise stat-card relative rounded-card overflow-hidden p-4 border border-wc-border">
            <svg v-if="sparklines.tickets.length" class="absolute top-3 right-3 opacity-65" width="60" height="24" viewBox="0 0 60 24" aria-hidden="true">
              <polyline :points="sparklinePoints(sparklines.tickets)" fill="none" stroke="rgba(220,38,38,0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="mt-6 font-display text-4xl leading-none text-wc-text font-data">{{ animatedCounters.openTickets }}</div>
            <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">TICKETS ABIERTOS</div>
          </div>
        </div>

        <!-- Onboarding checklist -->
        <div>
          <CoachOnboardingChecklist :days-old="coachDaysOld" />
        </div>

        <!-- Main content grid: 8-4 desktop -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-5">

          <!-- Left column (8/12) -->
          <div class="lg:col-span-8">

            <!-- ATENCIÓN URGENTE -->
            <section class="mb-5">
              <div class="flex items-center justify-between mb-3">
                <h2 class="font-display text-sm uppercase tracking-wider text-wc-text">ATENCIÓN URGENTE</h2>
                <span v-if="urgentClientsCount > 0" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-wc-accent/15 text-wc-accent">{{ urgentClientsCount }} CLIENTES</span>
              </div>

              <template v-if="attentionClients.length">
                <div v-for="client in attentionClients" :key="client.id" class="mb-2 rounded-card border-l-[3px] bg-wc-bg-tertiary border border-wc-border p-3" style="border-left-color:var(--color-wc-accent)">
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                      <div class="w-8 h-8 rounded-full bg-wc-accent/20 flex items-center justify-center text-wc-accent text-xs font-bold shrink-0">
                        {{ (client.name || 'C').charAt(0).toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <div class="font-medium text-wc-text text-sm truncate">{{ client.name }}</div>
                        <div class="text-[11px] text-wc-text-tertiary">Sin responder: {{ client.oldest_checkin }}</div>
                      </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                      <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400">{{ client.pending_checkins }}d</span>
                      <RouterLink to="/coach/checkins" class="text-[10px] font-medium text-wc-accent hover:underline">Responder →</RouterLink>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="rounded-card border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                <div class="text-2xl mb-2">✓</div>
                <div class="font-medium text-wc-text text-sm">Todos los check-ins respondidos</div>
                <div class="text-xs text-wc-text-tertiary">Buen trabajo</div>
              </div>
            </section>

            <!-- ACTIVIDAD HOY -->
            <section class="mb-5">
              <h2 class="font-display text-sm uppercase tracking-wider text-wc-text mb-3">ACTIVIDAD HOY</h2>
              <template v-if="todayActivity.length">
                <div class="relative">
                  <div class="absolute left-[15px] top-0 bottom-0 w-px bg-wc-border"></div>
                  <div class="space-y-3 pl-8">
                    <div v-for="(event, i) in todayActivity" :key="i" class="relative">
                      <div
                        class="absolute -left-[25px] w-3 h-3 rounded-full border-2"
                        :class="{
                          'bg-emerald-500 border-wc-bg': event.type === 'checkin',
                          'bg-blue-500 border-wc-bg': event.type === 'training',
                          'bg-wc-accent border-wc-bg': event.type === 'message',
                        }"
                      ></div>
                      <div class="text-sm text-wc-text">
                        <span v-if="event.type === 'checkin'">{{ event.client_name }} envió su check-in semanal</span>
                        <span v-else-if="event.type === 'training'">{{ event.client_name }} registró entrenamiento</span>
                        <span v-else>Nuevo mensaje de {{ event.client_name }}</span>
                      </div>
                      <div class="text-[11px] text-wc-text-tertiary">{{ event.time_ago }}</div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-wc-text-tertiary">Sin actividad en las últimas 24 horas</div>
            </section>

            <!-- GRÁFICAS (colapsadas) -->
            <section class="mb-5">
              <details class="group">
                <summary class="flex items-center gap-2 cursor-pointer list-none">
                  <h2 class="font-display text-sm uppercase tracking-wider text-wc-text">ANÁLISIS DE LA SEMANA</h2>
                  <svg class="w-3 h-3 text-wc-text-tertiary ml-auto transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </summary>
                <div class="mt-3 grid lg:grid-cols-2 gap-4">
                  <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                    <div class="text-xs font-medium text-wc-text-secondary mb-3">Check-ins · 7 días</div>
                    <svg viewBox="0 0 200 60" class="w-full h-16" aria-hidden="true">
                      <polyline v-if="sparklines.checkins.length" :points="sparklinePoints(sparklines.checkins, 200, 56)" fill="none" stroke="var(--color-wc-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                    <div class="text-xs font-medium text-wc-text-secondary mb-3">Mensajes · 7 días</div>
                    <svg viewBox="0 0 200 60" class="w-full h-16" aria-hidden="true">
                      <polyline v-if="sparklines.messages.length" :points="sparklinePoints(sparklines.messages, 200, 56)" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                </div>
              </details>
            </section>

          </div>

          <!-- Right column (4/12) -->
          <div class="lg:col-span-4 mt-4 lg:mt-0">

            <!-- MENSAJES RECIENTES -->
            <section class="pb-5 mb-6 border-b border-wc-border/60">
              <div class="flex items-center justify-between mb-3">
                <h2 class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">MENSAJES</h2>
                <RouterLink to="/coach/messages" class="text-[11px] text-wc-accent hover:underline">Ver todos →</RouterLink>
              </div>
              <template v-if="recentMessages.length">
                <div v-for="(msg, i) in recentMessages" :key="i" class="flex items-center gap-2 py-2 border-b border-wc-border last:border-0">
                  <div class="relative shrink-0">
                    <div class="w-8 h-8 rounded-full bg-wc-bg-secondary flex items-center justify-center text-xs font-bold text-wc-text">
                      {{ (msg.client_name || 'C').charAt(0).toUpperCase() }}
                    </div>
                    <span v-if="!msg.is_read" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-wc-accent border-2 border-wc-bg-tertiary"></span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-1">
                      <span class="text-sm font-medium text-wc-text truncate">{{ msg.client_name }}</span>
                      <span class="text-[10px] text-wc-text-tertiary shrink-0">{{ msg.time_ago }}</span>
                    </div>
                    <div class="text-xs text-wc-text-tertiary truncate">{{ msg.message }}</div>
                  </div>
                </div>
              </template>
              <div v-else class="text-sm text-wc-text-tertiary">Sin mensajes recientes</div>
            </section>

            <!-- TICKETS -->
            <section class="mb-5">
              <div class="flex items-center justify-between mb-3">
                <h2 class="font-sans text-xs font-bold uppercase tracking-widest text-wc-text-secondary">TICKETS</h2>
                <RouterLink to="/coach/plan-tickets" class="text-[11px] text-wc-accent hover:underline">Ver todos →</RouterLink>
              </div>
              <template v-if="openTicketsList.length">
                <div v-for="ticket in openTicketsList" :key="ticket.id" class="flex items-center justify-between py-2 border-b border-wc-border last:border-0">
                  <div class="min-w-0">
                    <div class="text-sm font-medium text-wc-text truncate">{{ ticket.title }}</div>
                    <div class="text-[11px] text-wc-text-tertiary">{{ ticket.client_name }} · {{ ticket.created_ago }}</div>
                  </div>
                  <span class="ml-2 shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :class="ticket.priority === 'urgent' || ticket.priority === 'high' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400'">
                    {{ (ticket.priority || 'low').toUpperCase() }}
                  </span>
                </div>
              </template>
              <div v-else class="text-sm text-wc-text-tertiary">Sin tickets abiertos</div>
            </section>

          </div>
        </div>
      </template>

    </div>
  </CoachLayout>
</template>
