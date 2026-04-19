<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useApi } from '../composables/useApi';
import NotificationBell from '../components/NotificationBell.vue';
import CoachImpersonationBanner from '../components/CoachImpersonationBanner.vue';

const authStore = useAuthStore();
const api = useApi();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const stoppingImpersonation = ref(false);

// Account status check — set to true when API returns 403 {inactive:true}
const accountInactive = ref(false);
const accountStatusValue = ref('inactivo');
const accountCheckDone = ref(false);

onMounted(async () => {
    try {
        await api.get('/api/v/client/account-status');
    } catch (err) {
        if (err.response?.status === 403 && err.response?.data?.inactive) {
            accountInactive.value = true;
            accountStatusValue.value = err.response.data.status || 'inactivo';
        }
    } finally {
        accountCheckDone.value = true;
    }
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

onUnmounted(() => {
    if (unwatch) unwatch();
});

// Navigation sections
const navSections = [
    {
        label: 'Entrenamiento',
        items: [
            { name: 'Dashboard', to: '/client', icon: 'dashboard', routeName: 'client-dashboard' },
            { name: 'Mi Plan', to: '/client/plan', icon: 'plan', routeName: 'client-plan' },
            { name: 'Entrenamiento', to: '/client/training', icon: 'training', routeName: 'client-training' },
        ],
    },
    {
        label: 'Progreso',
        items: [
            { name: 'Metricas', to: '/client/metrics', icon: 'metrics', routeName: 'client-metrics' },
            { name: 'Fotos', to: '/client/photos', icon: 'photos', routeName: 'client-photos' },
            { name: 'Bienestar', to: '/client/mindfulness', icon: 'mindfulness', routeName: 'client-mindfulness' },
        ],
    },
    {
        label: 'Social',
        items: [
            { name: 'Chat', to: '/client/chat', icon: 'chat', routeName: 'client-chat' },
            { name: 'Comunidad', to: '/client/community', icon: 'community', routeName: 'client-community' },
            { name: 'Retos', to: '/client/challenges', icon: 'challenges', routeName: 'client-challenges' },
            { name: 'Referidos', to: '/client/referrals', icon: 'referrals', routeName: 'client-referrals' },
        ],
    },
    {
        label: 'Cuenta',
        items: [
            { name: 'Perfil', to: '/client/profile', icon: 'profile', routeName: 'client-profile' },
            { name: 'Configuracion', to: '/client/settings', icon: 'settings', routeName: 'client-settings' },
        ],
    },
];

function isActive(routeName) {
    return route.name === routeName;
}

// Mobile bottom nav items
const bottomNav = [
    { name: 'Dashboard', to: '/client', icon: 'dashboard', routeName: 'client-dashboard' },
    { name: 'Plan', to: '/client/plan', icon: 'plan', routeName: 'client-plan' },
    { name: 'Metricas', to: '/client/metrics', icon: 'metrics', routeName: 'client-metrics' },
    { name: 'Chat', to: '/client/chat', icon: 'chat', routeName: 'client-chat' },
    { name: 'Perfil', to: '/client/profile', icon: 'profile', routeName: 'client-profile' },
];
</script>

<template>
  <div class="min-h-screen bg-wc-bg text-wc-text">

    <!-- Coach-initiated impersonation banner (only visible in coach's browser session) -->
    <CoachImpersonationBanner />

    <!-- Admin impersonation banner -->
    <div v-if="isImpersonating" class="fixed top-0 left-0 right-0 z-[90] flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-medium text-black">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
      </svg>
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
      <div class="flex h-20 items-center justify-center border-b border-wc-border px-4">
        <img src="/images/logo-client-dark.webp" alt="WellCore Fitness" class="hidden h-16 w-auto object-contain dark:block" />
        <img src="/images/logo-client-light.webp" alt="WellCore Fitness" class="block h-16 w-auto object-contain dark:hidden" />
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
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  isActive(item.routeName)
                    ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text'
                    : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text'
                ]"
              >
                <!-- Dashboard -->
                <svg v-if="item.icon === 'dashboard'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <!-- Plan -->
                <svg v-else-if="item.icon === 'plan'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
                <!-- Training -->
                <svg v-else-if="item.icon === 'training'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <!-- Nutrition -->
                <svg v-else-if="item.icon === 'nutrition'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                </svg>
                <!-- Habits -->
                <svg v-else-if="item.icon === 'habits'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- Metrics -->
                <svg v-else-if="item.icon === 'metrics'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                </svg>
                <!-- Photos -->
                <svg v-else-if="item.icon === 'photos'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 0 3Z" />
                </svg>
                <!-- Chat -->
                <svg v-else-if="item.icon === 'chat'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <!-- Community -->
                <svg v-else-if="item.icon === 'community'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                <!-- Challenges -->
                <svg v-else-if="item.icon === 'challenges'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                </svg>
                <!-- Profile -->
                <svg v-else-if="item.icon === 'profile'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <!-- Settings -->
                <svg v-else-if="item.icon === 'settings'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <!-- Mindfulness -->
                <svg v-else-if="item.icon === 'mindfulness'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                <!-- Referrals -->
                <svg v-else-if="item.icon === 'referrals'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
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
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors disabled:opacity-50"
        >
          <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
          {{ loggingOut ? 'Cerrando...' : 'Cerrar sesion' }}
        </button>
      </div>
    </aside>

    <!-- Main wrapper (offset by sidebar on lg+) -->
    <div class="lg:pl-60" :class="{ 'pt-10': isImpersonating }">

      <!-- Top bar -->
      <header class="sticky z-30 flex h-16 items-center justify-between border-b border-wc-border bg-wc-bg/80 px-4 backdrop-blur-xl sm:px-6" :class="isImpersonating ? 'top-10' : 'top-0'">
        <!-- Left: hamburger (mobile) -->
        <div class="flex items-center gap-3">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text lg:hidden"
            aria-label="Abrir menu"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>

        <!-- Right: dark mode, user info -->
        <div class="flex items-center gap-3">
          <!-- Notification Bell -->
          <NotificationBell />

          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
            title="Cambiar modo"
          >
            <!-- Moon (light mode) -->
            <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <!-- Sun (dark mode) -->
            <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
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
      <main class="px-4 py-6 pb-20 sm:px-6 lg:px-8 lg:pb-6">

        <!-- Account inactive overlay -->
        <div v-if="accountInactive" class="flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center py-12">
          <div class="w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-8 text-center shadow-lg">

            <!-- Lock icon -->
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-wc-accent/20 bg-wc-accent/10">
              <svg class="h-10 w-10 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
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
                <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
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
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-wc-border bg-wc-bg/95 backdrop-blur-xl lg:hidden">
      <div class="flex items-center justify-around px-2 pb-2 pt-2">
        <RouterLink
          v-for="item in bottomNav"
          :key="item.routeName"
          :to="item.to"
          :class="[
            'flex flex-col items-center gap-0.5 px-3 py-1 transition-colors',
            isActive(item.routeName) ? 'text-wc-accent' : 'text-wc-text-tertiary'
          ]"
        >
          <!-- Dashboard -->
          <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
          <!-- Plan -->
          <svg v-else-if="item.icon === 'plan'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
          </svg>
          <!-- Metrics -->
          <svg v-else-if="item.icon === 'metrics'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
          </svg>
          <!-- Chat -->
          <svg v-else-if="item.icon === 'chat'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
          </svg>
          <!-- Profile -->
          <svg v-else-if="item.icon === 'profile'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
          <span class="text-[10px] font-medium">{{ item.name }}</span>
        </RouterLink>
      </div>
    </nav>

  </div>
</template>
