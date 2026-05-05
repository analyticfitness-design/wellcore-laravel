<script setup>
import { ref, computed, onUnmounted, onMounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import NotificationBell from '../components/NotificationBell.vue';
import CoachOnboardingTour from '../components/CoachOnboardingTour.vue';
import CoachContractGate from '../components/coach/CoachContractGate.vue';
import SuperadminImpersonationBanner from '../components/SuperadminImpersonationBanner.vue';

const props = defineProps({
    urgentCount: { type: Number, default: 0 }
});

const showTour = ref(false);

onMounted(() => {
    try {
        if (route.name !== 'coach-dashboard') return;
        if (localStorage.getItem('coach_tour_completed') === '1') return;

        // One show per login session: compare current token vs token when last shown.
        const currentToken = localStorage.getItem('wc_token') || '';
        const lastToken    = localStorage.getItem('coach_tour_last_token') || '';
        if (currentToken && currentToken === lastToken) return;

        const shown = parseInt(localStorage.getItem('coach_tour_shown_count') || '0', 10);
        if (shown >= 3) {
            localStorage.setItem('coach_tour_completed', '1');
            return;
        }
        localStorage.setItem('coach_tour_shown_count', String(shown + 1));
        localStorage.setItem('coach_tour_last_token', currentToken);
        setTimeout(() => { showTour.value = true; }, 400);
    } catch {}
});

function onTourDone() {
    showTour.value = false;
}

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const fabOpen = ref(false);

const sidebarCollapsed = ref(localStorage.getItem('coachSidebarCollapsed') === 'true');
function toggleSidebarCollapse() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('coachSidebarCollapsed', String(sidebarCollapsed.value));
}

const userName = computed(() => {
    return localStorage.getItem('wc_user_name') || 'Coach';
});

const userInitial = computed(() => {
    return (userName.value || 'C').charAt(0).toUpperCase();
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
    fabOpen.value = false;
});

onUnmounted(() => {
    if (unwatch) unwatch();
});

// Navigation sections — orden por flujo de adopción del coach
const navSections = [
    {
        label: 'Aprendizaje',
        items: [
            { name: 'Onboarding', to: '/coach/onboarding', icon: 'compass', routeName: 'coach-onboarding', isNew: true },
        ],
    },
    {
        label: 'Principal',
        items: [
            { name: 'Inicio', to: '/coach', icon: 'dashboard', routeName: 'coach-dashboard' },
            { name: 'Clientes', to: '/coach/clients', icon: 'clients', routeName: 'coach-clients' },
            { name: 'Check-ins', to: '/coach/checkins', icon: 'checkins', routeName: 'coach-checkins', badge: 'pendingCheckins' },
            { name: 'Fotos de Comida', to: '/coach/food-photos', icon: 'checkins', routeName: 'coach-food-photos' },
            { name: 'Mensajes', to: '/coach/messages', icon: 'messages', routeName: 'coach-messages', badge: 'unreadMessages' },
        ],
    },
    {
        label: 'Gestión',
        items: [
            { name: 'Tickets', to: '/coach/plan-tickets', icon: 'plans', routeName: 'coach-plan-tickets' },
            { name: 'Planes', to: '/coach/plans', icon: 'plans', routeName: 'coach-plans' },
            { name: 'Kanban', to: '/coach/kanban', icon: 'kanban', routeName: 'coach-kanban' },
            { name: 'Comprobantes', to: '/coach/comprobantes', icon: 'receipt', routeName: 'coach-comprobantes' },
        ],
    },
    {
        label: 'Crecimiento',
        items: [
            { name: 'Estrategia', to: '/coach/strategy', icon: 'strategy', routeName: 'coach-strategy', isNew: true },
            { name: 'Broadcast', to: '/coach/broadcast', icon: 'broadcast', routeName: 'coach-broadcast' },
            { name: 'Invitaciones', to: '/coach/invitations', icon: 'envelope', routeName: 'coach-invitations' },
            { name: 'Analítica', to: '/coach/analytics', icon: 'analytics', routeName: 'coach-analytics' },
        ],
    },
    {
        label: 'Personal',
        items: [
            { name: 'Notas', to: '/coach/notes', icon: 'notes', routeName: 'coach-notes' },
        ],
    },
];

function isActive(routeName) {
    return route.name === routeName;
}

const TOUR_MAP = {
    'coach-dashboard': 'dashboard',
    'coach-clients': 'clients',
    'coach-plan-tickets': 'plan-tickets',
    'coach-checkins': 'checkins',
    'coach-messages': 'messages',
};
function tourAttr(routeName) { return TOUR_MAP[routeName] || null; }

// Mobile bottom nav — null = FAB spacer
const bottomNav = [
    { name: 'Inicio', to: '/coach', icon: 'dashboard', routeName: 'coach-dashboard' },
    { name: 'Clientes', to: '/coach/clients', icon: 'clients', routeName: 'coach-clients' },
    null, // FAB spacer
    { name: 'Check-ins', to: '/coach/checkins', icon: 'checkins', routeName: 'coach-checkins', badge: 'pendingCheckins' },
    { name: 'Mensajes', to: '/coach/messages', icon: 'messages', routeName: 'coach-messages', badge: 'unreadMessages' },
];
</script>

<template>
  <div class="min-h-screen bg-wc-bg text-wc-text">
    <SuperadminImpersonationBanner />
    <CoachContractGate />

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
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-[4.5rem]' : 'lg:w-60']"
      class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-wc-border bg-wc-bg-secondary transition-all duration-300 ease-in-out lg:translate-x-0"
    >
      <!-- Logo (theme-aware) -->
      <div class="flex h-16 items-center gap-3 border-b border-wc-border px-5 overflow-hidden">
        <img
          src="/images/logo-coach-dark.png"
          alt="WellCore"
          class="hidden h-9 w-9 object-contain shrink-0 dark:block"
        />
        <img
          src="/images/logo-coach-light.png"
          alt="WellCore"
          class="block h-9 w-9 object-contain shrink-0 dark:hidden"
        />
        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="font-display text-xl tracking-wider text-wc-text">WELLCORE</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div v-for="section in navSections" :key="section.label">
          <p :class="sidebarCollapsed ? 'lg:hidden' : ''" class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">{{ section.label }}</p>
          <ul class="space-y-0.5">
            <li v-for="item in section.items" :key="item.routeName">
              <RouterLink
                :to="item.to"
                :data-tour="tourAttr(item.routeName)"
                :class="[
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  sidebarCollapsed ? 'justify-center' : '',
                  isActive(item.routeName)
                    ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text'
                    : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text'
                ]"
              >
                <!-- Dashboard -->
                <svg v-if="item.icon === 'dashboard'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <!-- Clients -->
                <svg v-else-if="item.icon === 'clients'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <!-- Kanban -->
                <svg v-else-if="item.icon === 'kanban'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <!-- Check-ins -->
                <svg v-else-if="item.icon === 'checkins'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <!-- Messages -->
                <svg v-else-if="item.icon === 'messages'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <!-- Broadcast -->
                <svg v-else-if="item.icon === 'broadcast'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                </svg>
                <!-- Plans -->
                <svg v-else-if="item.icon === 'plans'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
                <!-- Analytics -->
                <svg v-else-if="item.icon === 'analytics'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <!-- Notes -->
                <svg v-else-if="item.icon === 'notes'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <!-- Profile -->
                <svg v-else-if="item.icon === 'profile'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <!-- Brand -->
                <svg v-else-if="item.icon === 'brand'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>
                <!-- Features -->
                <svg v-else-if="item.icon === 'features'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.048.58.024 1.194-.14 1.743" />
                </svg>
                <!-- Resources -->
                <svg v-else-if="item.icon === 'resources'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <!-- Envelope (Invitations) -->
                <svg v-else-if="item.icon === 'envelope'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <!-- Receipt (Comprobantes) -->
                <svg v-else-if="item.icon === 'receipt'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <!-- Strategy -->
                <svg v-else-if="item.icon === 'strategy'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                </svg>
                <!-- Compass (Onboarding) -->
                <svg v-else-if="item.icon === 'compass'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.5 9.5-2 5-5 2 2-5 5-2z" />
                </svg>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">{{ item.name }}</span>
                <span v-if="item.isNew" :class="sidebarCollapsed ? 'lg:hidden' : ''" class="ml-auto rounded-full bg-wc-accent px-1.5 py-0.5 text-[9px] font-display uppercase tracking-wide text-white">Nuevo</span>
              </RouterLink>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Sidebar footer -->
      <div class="border-t border-wc-border px-3 py-3">
        <button
          @click="handleLogout"
          :disabled="loggingOut"
          :class="sidebarCollapsed ? 'justify-center' : ''"
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors disabled:opacity-50"
        >
          <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
          <span :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ loggingOut ? 'Cerrando...' : 'Cerrar sesion' }}</span>
        </button>
      </div>

      <!-- Collapse toggle (desktop only) -->
      <div class="hidden lg:block border-t border-wc-border p-3">
        <button @click="toggleSidebarCollapse()" class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary transition-colors text-sm">
          <svg class="w-4 h-4 transition-transform shrink-0" :class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg>
          <span v-if="!sidebarCollapsed" class="text-xs font-medium">Colapsar</span>
        </button>
      </div>
    </aside>

    <!-- Main wrapper (dynamic margin based on sidebar state) -->
    <div
      :class="sidebarCollapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-60'"
      class="min-h-screen transition-all duration-300 pb-24 lg:pb-8"
    >

      <!-- Top bar -->
      <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-wc-border bg-wc-bg/80 px-4 backdrop-blur-xl sm:px-6">
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
          <!-- Urgent count badge (mobile topbar) -->
          <span v-if="props.urgentCount > 0" class="lg:hidden inline-flex items-center gap-1 rounded-full bg-wc-accent/15 px-2 py-0.5 text-[10px] font-bold text-wc-accent">
            <span class="w-1.5 h-1.5 rounded-full bg-wc-accent animate-pulse"></span>
            {{ props.urgentCount }}
          </span>
        </div>

        <!-- Right: dark mode, coach badge, user info -->
        <div class="flex items-center gap-3">
          <!-- Notification bell -->
          <NotificationBell endpoint="/api/v/coach/notifications" />

          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text"
            title="Cambiar modo"
            aria-label="Cambiar modo oscuro"
          >
            <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
          </button>

          <!-- Coach badge -->
          <span class="hidden sm:inline-flex rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">
            Coach
          </span>

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
      <main class="px-4 sm:px-6 py-2 lg:py-6">
        <slot />
      </main>
    </div>

    <!-- Mobile bottom nav -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 border-t pb-safe"
         style="background:var(--color-wc-bg-secondary); border-color:var(--color-wc-border)">
      <div class="flex items-center justify-around h-16 px-2 relative">
        <template v-for="(item, i) in bottomNav" :key="i">
          <!-- FAB spacer -->
          <div v-if="item === null" class="w-14" aria-hidden="true"></div>
          <!-- Nav item -->
          <RouterLink
            v-else
            :to="item.to"
            class="nav-tap flex flex-col items-center gap-0.5 py-2 px-3 transition-colors relative"
            :class="isActive(item.routeName) ? 'text-wc-accent' : 'text-wc-text-tertiary'"
          >
            <!-- Dashboard -->
            <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            <!-- Clients -->
            <svg v-else-if="item.icon === 'clients'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <!-- Check-ins -->
            <svg v-else-if="item.icon === 'checkins'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <!-- Messages -->
            <svg v-else-if="item.icon === 'messages'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            <span class="text-[9px] font-semibold">{{ item.name }}</span>
          </RouterLink>
        </template>
      </div>

      <!-- FAB button -->
      <button
        @click="fabOpen = !fabOpen"
        class="absolute left-1/2 -translate-x-1/2 -top-7 w-14 h-14 rounded-full bg-wc-accent shadow-lg flex items-center justify-center transition-transform"
        :class="fabOpen ? 'rotate-45' : ''"
        aria-label="Acciones rápidas"
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
      </button>
    </nav>

    <!-- FAB backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="fabOpen" class="lg:hidden fixed inset-0 z-40 bg-black/60" @click="fabOpen = false"></div>
    </Transition>

    <!-- FAB bottom sheet -->
    <Transition
      enter-active-class="transition-transform duration-300"
      enter-from-class="translate-y-full"
      enter-to-class="translate-y-0"
      leave-active-class="transition-transform duration-200"
      leave-from-class="translate-y-0"
      leave-to-class="translate-y-full"
    >
      <div v-if="fabOpen" class="lg:hidden fixed bottom-0 inset-x-0 z-50 pb-safe rounded-t-2xl border-t"
           style="background:var(--color-wc-bg-secondary); border-color:var(--color-wc-border)">
        <div class="p-4 space-y-1">
          <div class="w-10 h-1 rounded-full bg-wc-border mx-auto mb-4"></div>
          <RouterLink to="/coach/invitations" @click="fabOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-card hover:bg-wc-bg-tertiary transition-colors">
            <div class="w-10 h-10 rounded-lg bg-wc-accent/15 flex items-center justify-center shrink-0">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-wc-accent)" stroke-width="2" stroke-linecap="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
            </div>
            <div>
              <div class="font-medium text-wc-text text-sm">Agregar cliente</div>
              <div class="text-xs text-wc-text-tertiary">Invitar nuevo cliente al programa</div>
            </div>
          </RouterLink>
          <RouterLink to="/coach/messages" @click="fabOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-card hover:bg-wc-bg-tertiary transition-colors">
            <div class="w-10 h-10 rounded-lg bg-blue-500/15 flex items-center justify-center shrink-0">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>
            </div>
            <div>
              <div class="font-medium text-wc-text text-sm">Enviar broadcast</div>
              <div class="text-xs text-wc-text-tertiary">Mensaje a todos los clientes</div>
            </div>
          </RouterLink>
          <RouterLink to="/coach/checkins" @click="fabOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-card hover:bg-wc-bg-tertiary transition-colors">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/15 flex items-center justify-center shrink-0">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg>
            </div>
            <div>
              <div class="font-medium text-wc-text text-sm">Revisar check-ins</div>
              <div class="text-xs text-wc-text-tertiary">Responder check-ins pendientes</div>
            </div>
          </RouterLink>
        </div>
      </div>
    </Transition>

    <!-- First-visit onboarding tour -->
    <CoachOnboardingTour v-if="showTour" @done="onTourDone" />

  </div>
</template>
