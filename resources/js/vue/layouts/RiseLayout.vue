<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const stoppingImpersonation = ref(false);

const isImpersonating = computed(() => authStore.isImpersonating);

function stopImpersonation() {
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
    authStore.clearAuth();
    document.body.appendChild(form);
    form.submit();
}

const userName = computed(() => {
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
        label: 'RISE',
        items: [
            { name: 'Dashboard', to: '/rise', icon: 'dashboard', routeName: 'rise-dashboard' },
            { name: 'Mi Programa', to: '/rise/program', icon: 'program', routeName: 'rise-program' },
            { name: 'Tracking Diario', to: '/rise/tracking', icon: 'tracking', routeName: 'rise-tracking' },
        ],
    },
    {
        label: 'Mediciones',
        items: [
            { name: 'Mediciones', to: '/rise/measurements', icon: 'measurements', routeName: 'rise-measurements' },
            { name: 'Fotos', to: '/rise/photos', icon: 'photos', routeName: 'rise-photos' },
        ],
    },
    {
        label: 'Habitos',
        items: [
            { name: 'Habitos RISE', to: '/rise/habits', icon: 'habits', routeName: 'rise-habits' },
        ],
    },
    {
        label: 'General',
        items: [
            { name: 'Chat', to: '/rise/chat', icon: 'chat', routeName: 'rise-chat' },
            { name: 'Entrenamiento', to: '/rise/training', icon: 'training', routeName: 'rise-training' },
            { name: 'Nutricion', to: '/rise/nutrition', icon: 'nutrition', routeName: 'rise-nutrition' },
            { name: 'Perfil', to: '/rise/profile', icon: 'profile', routeName: 'rise-profile' },
        ],
    },
];

function isActive(routeName) {
    return route.name === routeName;
}

// Mobile bottom nav items
const bottomNav = [
    { name: 'Dashboard', to: '/rise', icon: 'dashboard', routeName: 'rise-dashboard' },
    { name: 'Programa', to: '/rise/program', icon: 'program', routeName: 'rise-program' },
    { name: 'Habitos', to: '/rise/habits', icon: 'habits', routeName: 'rise-habits' },
    { name: 'Chat', to: '/rise/chat', icon: 'chat', routeName: 'rise-chat' },
    { name: 'Perfil', to: '/rise/profile', icon: 'profile', routeName: 'rise-profile' },
];
</script>

<template>
  <div class="min-h-screen bg-wc-bg text-wc-text">

    <!-- Impersonation banner -->
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
      <!-- Logo with RISE branding -->
      <div class="flex h-16 items-center gap-3 border-b border-wc-border px-5">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-amber-600">
          <span class="font-display text-base leading-none text-white">R</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="font-display text-xl tracking-wider text-wc-text">RISE</span>
          <span class="rounded-full bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-widest text-amber-500">12W</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div v-for="section in navSections" :key="section.label">
          <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-amber-500/70">{{ section.label }}</p>
          <ul class="space-y-0.5">
            <li v-for="item in section.items" :key="item.routeName">
              <RouterLink
                :to="item.to"
                :class="[
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  isActive(item.routeName)
                    ? 'border-l-2 border-amber-500 bg-amber-500/10 text-wc-text'
                    : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text'
                ]"
              >
                <!-- Dashboard -->
                <svg v-if="item.icon === 'dashboard'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <!-- Program -->
                <svg v-else-if="item.icon === 'program'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
                <!-- Tracking -->
                <svg v-else-if="item.icon === 'tracking'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- Measurements -->
                <svg v-else-if="item.icon === 'measurements'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                </svg>
                <!-- Photos -->
                <svg v-else-if="item.icon === 'photos'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 0 3Z" />
                </svg>
                <!-- Habits -->
                <svg v-else-if="item.icon === 'habits'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                </svg>
                <!-- Chat -->
                <svg v-else-if="item.icon === 'chat'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <!-- Workout -->
                <svg v-else-if="item.icon === 'workout'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <!-- Training (calendar check) -->
                <svg v-else-if="item.icon === 'training'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
                <!-- Nutrition (fork and knife) -->
                <svg v-else-if="item.icon === 'nutrition'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                </svg>
                <!-- Profile -->
                <svg v-else-if="item.icon === 'profile'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
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

    <!-- Main wrapper (offset by sidebar on lg+, + impersonation banner) -->
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

        <!-- Right: dark mode, RISE badge, user info -->
        <div class="flex items-center gap-3">
          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
            title="Cambiar modo"
            aria-label="Cambiar modo oscuro"
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

          <!-- RISE badge -->
          <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-500/15 to-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-500">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            RISE
          </span>

          <!-- User avatar + name -->
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-amber-500/30 to-amber-600/20">
              <span class="text-sm font-semibold text-amber-500">{{ userInitial }}</span>
            </div>
            <span class="hidden text-sm font-medium text-wc-text sm:inline">{{ userName }}</span>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="px-4 py-6 pb-20 sm:px-6 lg:px-8 lg:pb-6">
        <slot />
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
            isActive(item.routeName) ? 'text-amber-500' : 'text-wc-text-tertiary'
          ]"
        >
          <!-- Dashboard -->
          <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
          <!-- Program -->
          <svg v-else-if="item.icon === 'program'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
          </svg>
          <!-- Habits -->
          <svg v-else-if="item.icon === 'habits'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
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
