<script setup>
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useApi } from '../composables/useApi';
import NotificationBell from '../components/NotificationBell.vue';
import CoachImpersonationBanner from '../components/CoachImpersonationBanner.vue';
import SuperadminImpersonationBanner from '../components/SuperadminImpersonationBanner.vue';
import RenewalBanner from '../components/RenewalBanner.vue';
import MedalUnlockCelebration from '../components/MedalUnlockCelebration.vue';
import LevelUpCelebration from '../components/LevelUpCelebration.vue';
import ToastContainer from '../components/ui/ToastContainer.vue';
import BentoCelebration from '../components/celebrations/BentoCelebration.vue';
import WcIcon from '../components/ui/WcIcon.vue';
import DashboardFab from '../components/dashboard/DashboardFab.vue';
import InstallPrompt from '../components/dashboard/InstallPrompt.vue';
import { useMedals } from '../composables/useMedals';
import { useImpersonation } from '../composables/useImpersonation';

// Celebraciones globales — disparadas desde cualquier vista via fetchMedals()
const { newMedal, levelUp, clearNewMedal, clearLevelUp, fetchMedals: initMedals } = useMedals();

const authStore = useAuthStore();
const api = useApi();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const stoppingImpersonation = ref(false);

const { anyImpersonation } = useImpersonation();

// Account status check — set to true when API returns 403 {inactive:true}
const accountInactive = ref(false);
const accountStatusValue = ref('inactivo');
const accountCheckDone = ref(false);

// Coach branding (P4)
const coachBrand = ref(null); // { name, logo_url, logo_url_webp, primary_color, nombre_comercial, tagline }

// Plan phase badge (topbar)
const planPhaseText = ref('');

// Responsive badge visibility — bypasses CSS specificity issues with compiled .tb-phase
const windowWidth = ref(window.innerWidth);
const onWindowResize = () => { windowWidth.value = window.innerWidth; };

let layoutAc = null;

onMounted(async () => {
    window.addEventListener('resize', onWindowResize, { passive: true });

    layoutAc = new AbortController();
    const ac = layoutAc;

    const [statusRes, coachRes, dashRes] = await Promise.allSettled([
        api.get('/api/v/client/account-status', { signal: ac.signal }),
        api.get('/api/v/client/my-coach',       { signal: ac.signal }),
        api.get('/api/v/client/dashboard',      { signal: ac.signal }),
    ]);

    // account-status
    if (statusRes.status === 'rejected') {
        const err = statusRes.reason;
        if (err?.response?.status === 403 && err.response?.data?.inactive) {
            accountInactive.value = true;
            accountStatusValue.value = err.response.data.status || 'inactivo';
        }
    }
    accountCheckDone.value = true;

    // my-coach
    if (coachRes.status === 'fulfilled' && coachRes.value?.status === 200 && coachRes.value.data) {
        coachBrand.value = coachRes.value.data;
    }

    // dashboard — plan phase badge (compact format to fit topbar on any screen)
    if (dashRes.status === 'fulfilled' && dashRes.value?.status === 200) {
        const d = dashRes.value.data;
        if (d?.currentWeek) {
            const firstWord = d.phaseName ? d.phaseName.trim().split(/\s+/)[0] : null;
            const phase = firstWord ? ` · ${firstWord}` : '';
            planPhaseText.value = `S${d.currentWeek}${phase}`;
        } else if (d?.planLabel) {
            planPhaseText.value = d.planLabel;
        }
    }

    // celebraciones — isFirstLoad=true, no dispara celebracion
    initMedals().catch(() => {});
});

const isImpersonating = computed(() => authStore.isImpersonating);

async function stopImpersonation() {
    stoppingImpersonation.value = true;
    // Read admin token BEFORE clearing auth
    const adminToken = localStorage.getItem('wc_admin_token');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/impersonate/stop';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        form.appendChild(csrfInput);
    }
    // Pass admin token as POST body fallback (session may not be available in SPA context)
    if (adminToken) {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'admin_token';
        tokenInput.value = adminToken;
        form.appendChild(tokenInput);
    }
    // Clear client auth from localStorage before redirecting
    authStore.clearAuth();
    document.body.appendChild(form);
    form.submit();
}

const userName = computed(() => {
    // Attempt to read name from localStorage cache, fallback to 'Usuario'
    return localStorage.getItem('wc_user_name') || 'Usuario';
});

const userInitial = computed(() => {
    return (userName.value || 'U').charAt(0).toUpperCase();
});

function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('darkMode', isDark);
}

async function handleLogout() {
    loggingOut.value = true;
    try {
        await authStore.logout();
    } finally {
        loggingOut.value = false;
        router.push('/login');
    }
}

function closeSidebar() {
    sidebarOpen.value = false;
}

// Close sidebar on route change
const unwatch = router.afterEach(() => {
    sidebarOpen.value = false;
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', onWindowResize);
    layoutAc?.abort();
});

onUnmounted(() => {
    if (unwatch) unwatch();
});

// Navigation sections
const navSections = [
    {
        label: 'Entrenamiento',
        items: [
            { name: 'Dashboard', to: '/client', icon: 'wc-home', routeName: 'client-dashboard' },
            { name: 'Mi Plan', to: '/client/plan', icon: 'wc-calendar', routeName: 'client-plan' },
            { name: 'Entrenamiento', to: '/client/training', icon: 'wc-barbell', routeName: 'client-training' },
        ],
    },
    {
        label: 'Progreso',
        items: [
            { name: 'Metricas', to: '/client/metrics', icon: 'wc-chart-line-up', routeName: 'client-metrics' },
            { name: 'Fotos', to: '/client/photos', icon: 'wc-camera', routeName: 'client-photos' },
            { name: 'Logros', to: '/client/logros', icon: 'wc-trophy', routeName: 'client-logros' },
            { name: 'Bienestar', to: '/client/mindfulness', icon: 'wc-moon', routeName: 'client-mindfulness' },
        ],
    },
    {
        label: 'Social',
        items: [
            { name: 'Chat', to: '/client/chat', icon: 'wc-chat-bubble', routeName: 'client-chat' },
            { name: 'Comunidad', to: '/client/community', icon: 'wc-users', routeName: 'client-community' },
            { name: 'Retos', to: '/client/challenges', icon: 'wc-fire', routeName: 'client-challenges' },
            { name: 'Referidos', to: '/client/referrals', icon: 'wc-share', routeName: 'client-referrals' },
        ],
    },
    {
        label: 'Cuenta',
        items: [
            { name: 'Perfil', to: '/client/profile', icon: 'wc-user', routeName: 'client-profile' },
            { name: 'Configuracion', to: '/client/settings', icon: 'wc-settings', routeName: 'client-settings' },
        ],
    },
];

function isActive(routeName) {
    return route.name === routeName;
}

// Mobile bottom nav items
const bottomNav = [
    { name: 'Dashboard', to: '/client', icon: 'wc-home', routeName: 'client-dashboard' },
    { name: 'Plan', to: '/client/plan', icon: 'wc-calendar', routeName: 'client-plan' },
    { name: 'Metricas', to: '/client/metrics', icon: 'wc-chart-line-up', routeName: 'client-metrics' },
    { name: 'Chat', to: '/client/chat', icon: 'wc-chat-bubble', routeName: 'client-chat' },
    { name: 'Perfil', to: '/client/profile', icon: 'wc-user', routeName: 'client-profile' },
];
</script>

<template>
  <div
    class="min-h-screen min-h-dvh bg-wc-bg text-wc-text"
    :style="coachBrand?.primary_color ? { '--coach-accent': coachBrand.primary_color } : {}"
  >

    <SuperadminImpersonationBanner />
    <!-- Coach-initiated impersonation banner (only visible in coach's browser session) -->
    <CoachImpersonationBanner />

    <!-- Admin impersonation banner -->
    <div v-if="isImpersonating" class="fixed top-0 left-0 right-0 z-[90] flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-medium text-black">
      <WcIcon name="wc-eye" :size="16" />
      Viendo portal como {{ userName }}
      <button
        @click="stopImpersonation"
        :disabled="stoppingImpersonation"
        class="rounded-md bg-black/20 px-3 py-1 text-xs font-semibold hover:bg-black/30 transition-colors"
      >
        {{ stoppingImpersonation ? 'Volviendo...' : 'Volver a Admin' }}
      </button>
    </div>

    <!-- Mobile sidebar overlay -->
    <Transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        @click="closeSidebar"
      ></div>
    </Transition>

    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-wc-border bg-wc-bg-secondary transition-transform duration-300 ease-in-out lg:translate-x-0"
    >
      <!-- Logo -->
      <div class="flex h-20 items-center justify-center gap-2 border-b border-wc-border px-4">
        <img src="/images/logo-client-dark.webp" alt="WellCore Fitness" class="hidden h-12 w-auto object-contain dark:block" />
        <img src="/images/logo-client-light.webp" alt="WellCore Fitness" class="block h-12 w-auto object-contain dark:hidden" />

        <!-- Coach logo (P4) -->
        <template v-if="coachBrand && coachBrand.logo_url">
          <span class="h-8 w-px bg-wc-border"></span>
          <picture :title="coachBrand.nombre_comercial || coachBrand.name">
            <source v-if="coachBrand.logo_url_webp" :srcset="coachBrand.logo_url_webp" type="image/webp" />
            <img :src="coachBrand.logo_url" :alt="coachBrand.nombre_comercial || coachBrand.name || 'Coach'" class="h-8 w-auto max-w-[72px] object-contain" />
          </picture>
        </template>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div v-for="section in navSections" :key="section.label">
          <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ section.label }}</p>
          <ul class="space-y-0.5">
            <li v-for="item in section.items" :key="item.routeName">
              <RouterLink
                :to="item.to"
                :class="[
                  'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  isActive(item.routeName)
                    ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text'
                    : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text'
                ]"
              >
                <WcIcon
                  :name="item.icon"
                  :size="26"
                  :class="[
                    'shrink-0 transition-all duration-200',
                    isActive(item.routeName)
                      ? ''
                      : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100'
                  ]"
                />
                {{ item.name }}
              </RouterLink>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Sidebar footer / Logout -->
      <div class="border-t border-wc-border px-3 py-3">
        <button
          @click="handleLogout"
          :disabled="loggingOut"
          class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors disabled:opacity-50"
        >
          <WcIcon name="wc-arrow-left" :size="22" class="shrink-0 grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-200" />
          {{ loggingOut ? 'Cerrando...' : 'Cerrar sesion' }}
        </button>
      </div>
    </aside>

    <!-- Main wrapper (offset by sidebar on lg+) -->
    <div class="lg:pl-60" :class="{ 'pt-10': anyImpersonation }">

      <!-- Plan renewal warning banner (shows only when plan is in grace window) -->
      <RenewalBanner />

      <!-- Top bar -->
      <header class="sticky z-30 flex h-16 items-center justify-between gap-3 border-b border-wc-border bg-wc-bg/80 px-4 backdrop-blur-xl sm:px-6" :class="anyImpersonation ? 'top-10' : 'top-0'">
        <!-- Left: hamburger + plan phase -->
        <div class="flex min-w-0 flex-1 items-center gap-3">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="shrink-0 flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text lg:hidden"
            aria-label="Abrir menu"
          >
            <WcIcon name="wc-menu" :size="20" />
          </button>
          <!-- Plan phase badge — v-if controls visibility via JS to bypass CSS specificity issues -->
          <div v-if="planPhaseText && windowWidth >= 640" class="tb-phase">
            <span>{{ planPhaseText }}</span>
          </div>
        </div>

        <!-- Right: dark mode, user info -->
        <div class="flex shrink-0 items-center gap-3">
          <!-- Notification Bell — SP-4 community notifications endpoint -->
          <NotificationBell endpoint="/api/v/notifications" :poll-interval="30000" />

          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
            title="Cambiar modo"
            aria-label="Cambiar modo oscuro"
          >
            <span class="dark:hidden"><WcIcon name="wc-moon" :size="20" /></span>
            <span class="hidden dark:block"><WcIcon name="wc-sun" :size="20" /></span>
          </button>

          <!-- User avatar + name -->
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-wc-accent/20">
              <span class="text-sm font-semibold text-wc-accent">{{ userInitial }}</span>
            </div>
            <span class="hidden text-sm font-medium text-wc-text sm:inline">{{ userName }}</span>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="px-4 py-6 pb-nav-safe sm:px-6 lg:px-8">

        <!-- Account inactive overlay -->
        <div v-if="accountInactive" class="flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center py-12">
          <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 text-center shadow-lg">

            <!-- Lock icon -->
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-wc-accent/20 bg-wc-accent/10">
              <WcIcon name="wc-lock" :size="40" class="text-wc-accent" />
            </div>

            <!-- Title & message -->
            <h1 class="mb-2 font-display text-3xl uppercase tracking-wide text-wc-text">Cuenta Inactiva</h1>
            <p class="mb-5 text-sm text-wc-text-secondary">
              Tu acceso al plan ha sido pausado. Para continuar con tu transformacion, renueva tu suscripcion.
            </p>

            <!-- Status badge -->
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-red-500/30 bg-red-500/10 px-4 py-1.5 text-sm font-medium text-red-400">
              <span class="h-2 w-2 rounded-full bg-red-400"></span>
              Estado: {{ accountStatusValue }}
            </div>

            <!-- What they recover -->
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg p-4 text-left">
              <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Al renovar recuperas acceso a:</p>
              <div v-for="item in ['Tu plan de entrenamiento personalizado', 'Plan de nutricion y recetas', 'Seguimiento con tu coach', 'Metricas, habitos y progreso']" :key="item" class="flex items-center gap-2 py-0.5 text-sm text-wc-text-secondary">
                <WcIcon name="wc-check" :size="16" class="shrink-0 text-wc-accent" />
                {{ item }}
              </div>
            </div>

            <!-- Contact coach CTA -->
            <a
              href="mailto:info@wellcorefitness.com?subject=Renovar%20mi%20plan%20WellCore&body=Hola%2C%20quisiera%20renovar%20mi%20suscripcion."
              class="mb-3 block w-full rounded-xl bg-wc-accent py-3 text-center text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90"
            >
              Contactar para Renovar
            </a>

            <!-- Logout -->
            <button
              @click="handleLogout"
              :disabled="loggingOut"
              class="block w-full rounded-xl border border-wc-border py-3 text-center text-sm font-medium text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary disabled:opacity-50"
            >
              {{ loggingOut ? 'Cerrando...' : 'Cerrar sesion' }}
            </button>
          </div>
        </div>

        <!-- Loading state while checking status -->
        <div v-else-if="!accountCheckDone" class="flex min-h-[calc(100vh-8rem)] items-center justify-center">
          <div class="h-8 w-8 animate-spin rounded-full border-2 border-wc-border border-t-wc-accent"></div>
        </div>

        <!-- Normal content -->
        <slot v-else />
      </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav-safe fixed inset-x-0 bottom-0 z-40 border-t border-wc-border bg-wc-bg/95 backdrop-blur-xl lg:hidden">
      <div class="flex items-center justify-around px-2 pb-2 pt-2">
        <RouterLink
          v-for="item in bottomNav"
          :key="item.routeName"
          :to="item.to"
          :class="[
            'group flex flex-col items-center gap-0.5 px-3 py-1 transition-colors',
            isActive(item.routeName) ? 'text-wc-accent' : 'text-wc-text-tertiary'
          ]"
        >
          <WcIcon
            :name="item.icon"
            :size="26"
            :class="[
              'shrink-0 transition-all duration-200',
              isActive(item.routeName)
                ? ''
                : 'grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100'
            ]"
          />
          <span class="text-[10px] font-medium">{{ item.name }}</span>
        </RouterLink>
      </div>
    </nav>

    <!-- Global celebration overlays — disparadas por fetchMedals() desde cualquier vista -->
    <MedalUnlockCelebration :medal="newMedal" @close="clearNewMedal" />
    <LevelUpCelebration :event="levelUp" @close="clearLevelUp" />

    <!-- BentoCelebration global singleton — vía useCelebration().celebrate() -->
    <BentoCelebration
      :global="true"
      @cta-click="(preset) => console.log('[celebration] cta:', preset)"
      @share="(evt) => console.log('[celebration] share:', evt)"
    />

    <!-- Global toast notifications -->
    <ToastContainer />

    <!-- Floating Action Button (mobile only, solo cuando cuenta activa) -->
    <DashboardFab v-if="!accountInactive && accountCheckDone" />

    <!-- PWA install prompt (condicional: beforeinstallprompt + 2+ visits) -->
    <InstallPrompt v-if="!accountInactive && accountCheckDone" />

  </div>
</template>
