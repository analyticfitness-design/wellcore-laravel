<script setup>
import { ref, computed, onUnmounted, onMounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useCoachIosShell } from '../composables/useCoachIosShell';
import NotificationBell from '../components/NotificationBell.vue';
import CoachOnboardingTour from '../components/CoachOnboardingTour.vue';
import CoachContractGate from '../components/coach/CoachContractGate.vue';
import SuperadminImpersonationBanner from '../components/SuperadminImpersonationBanner.vue';

import TopBarIos from '../components/coach/ios/TopBarIos.vue';
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue';
import BottomTabBar from '../components/coach/ios/BottomTabBar.vue';
import ActionSheet from '../components/coach/ios/ActionSheet.vue';
import CmdPalette from '../components/coach/ios/CmdPalette.vue';
import ToastContainer from '../components/ui/ToastContainer.vue';

const props = defineProps({
    urgentCount: { type: Number, default: 0 }
});

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const shell = useCoachIosShell();

const showTour = ref(false);
const sidebarOpen = ref(false);
const loggingOut = ref(false);

const sidebarCollapsed = ref(localStorage.getItem('coachSidebarCollapsed') === 'true');
function toggleSidebarCollapse() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('coachSidebarCollapsed', String(sidebarCollapsed.value));
}

const userName = computed(() => localStorage.getItem('wc_user_name') || 'Coach');
const userInitial = computed(() => (userName.value || 'C').charAt(0).toUpperCase());

const todayDateLabel = computed(() => {
    const d = new Date();
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return `${dias[d.getDay()]}, ${d.getDate()} ${meses[d.getMonth()]}.`;
});

onMounted(() => {
    try {
        if (route.name !== 'coach-dashboard') return;
        if (localStorage.getItem('coach_tour_completed') === '1') return;
        const currentToken = localStorage.getItem('wc_token') || '';
        const lastToken = localStorage.getItem('coach_tour_last_token') || '';
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

function onTourDone() { showTour.value = false; }

async function handleLogout() {
    loggingOut.value = true;
    try { await authStore.logout(); }
    finally {
        loggingOut.value = false;
        router.push('/login');
    }
}

function closeSidebar() { sidebarOpen.value = false; }

// Close sidebar on route change
const unwatch = router.afterEach(() => {
    sidebarOpen.value = false;
});
onUnmounted(() => { if (unwatch) unwatch(); });

// Navigation sections (preservado idéntico)
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
        label: 'Comunidad',
        items: [
            { name: 'Comunidad', to: '/coach/community', icon: 'community', routeName: 'coach-community', isNew: true },
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
            { name: 'Notificaciones', to: '/coach/notifications', icon: 'bell', routeName: 'coach-notifications' },
        ],
    },
];

function isActive(routeName) { return route.name === routeName; }

const TOUR_MAP = {
    'coach-dashboard': 'dashboard',
    'coach-clients': 'clients',
    'coach-plan-tickets': 'plan-tickets',
    'coach-checkins': 'checkins',
    'coach-messages': 'messages',
};
function tourAttr(routeName) { return TOUR_MAP[routeName] || null; }

// Bottom nav iOS — 4 tabs reales (sin FAB spacer)
const bottomTabs = computed(() => [
    {
        routeName: 'coach-dashboard', to: '/coach', label: 'Inicio',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
    },
    {
        routeName: 'coach-clients', to: '/coach/clients', label: 'Clientes',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
    },
    {
        routeName: 'coach-checkins', to: '/coach/checkins', label: 'Check-ins',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        badge: props.urgentCount > 0 ? props.urgentCount : null,
    },
    {
        routeName: 'coach-messages', to: '/coach/messages', label: 'Mensajes',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>',
    },
]);

// Action sheet items (reemplaza el FAB sheet)
const actionSheetItems = [
    {
        id: 'announce', label: 'Mensaje al equipo',
        iconColor: '#DC2626', iconStrokeColor: '#f87171',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11l18-5v12L3 13v-2zm0 0v3a2 2 0 002 2h3"/>',
        onClick: () => window.dispatchEvent(new CustomEvent('coach-community:open-announce')),
    },
    {
        id: 'crear-checkin', label: 'Crear check-in',
        iconColor: '#10B981', iconStrokeColor: '#34d399',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        onClick: () => router.push('/coach/checkins'),
    },
    {
        id: 'enviar-broadcast', label: 'Enviar broadcast',
        iconColor: '#3B82F6', iconStrokeColor: '#60a5fa',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
        onClick: () => router.push('/coach/broadcast'),
    },
    {
        id: 'invitar', label: 'Invitar cliente',
        iconColor: '#F59E0B', iconStrokeColor: '#fbbf24',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>',
        onClick: () => router.push('/coach/invitations'),
    },
    {
        id: 'crear-ticket', label: 'Crear ticket',
        iconColor: '#A78BFA', iconStrokeColor: '#A78BFA',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75a3.75 3.75 0 0 1-7.5 0V6m-2.25 6H5.625c-.621 0-1.125.504-1.125 1.125v3.75c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-3.75c0-.621-.504-1.125-1.125-1.125H18.375"/>',
        onClick: () => router.push('/coach/plan-tickets/nuevo'),
    },
];

// Cmd+K palette sections (desktop)
const cmdPaletteSections = computed(() => [
    {
        label: 'Acciones rápidas',
        items: actionSheetItems.map((a, i) => ({
            ...a,
            kbd: ['M', 'C', 'B', 'I', 'T'][i] || undefined,
        })),
    },
    {
        label: 'Navegación',
        items: navSections.flatMap(g => g.items.map(it => ({
            id: `nav-${it.routeName}`,
            label: `Ir a ${it.name}`,
            to: it.to,
        }))),
    },
]);
</script>

<template>
  <div class="coach-ios min-h-screen bg-wc-bg text-wc-text">
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

    <!-- Sidebar (preservado funcional; refinamiento visual en Fase 4) -->
    <aside
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-[4.5rem]' : 'lg:w-60']"
      class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-wc-border bg-wc-bg-secondary transition-all duration-300 ease-in-out lg:translate-x-0"
    >
      <!-- Logo with conic ring accent (iOS signature) -->
      <div class="flex h-16 items-center gap-2.5 border-b border-wc-border px-5 overflow-hidden">
        <span class="ring-conic-accent w-9 h-9 shrink-0 inline-block">
          <span class="absolute inset-[2px] rounded-full bg-wc-accent flex items-center justify-center z-[1]">
            <svg viewBox="0 0 48 48" fill="none" class="w-5 h-5" aria-hidden="true">
              <path d="M10 19 L16 33 L22 22 L26 22 L32 33 L38 19" stroke="#fff" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="24" cy="22" r="2" fill="#fff"/>
            </svg>
          </span>
        </span>
        <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="font-display text-lg tracking-[0.12em] font-bold text-wc-text">WELLCORE</span>
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
                <svg v-if="item.icon === 'dashboard'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <svg v-else-if="item.icon === 'clients'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <svg v-else-if="item.icon === 'kanban'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <svg v-else-if="item.icon === 'checkins'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <svg v-else-if="item.icon === 'messages'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <svg v-else-if="item.icon === 'broadcast'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3" />
                </svg>
                <svg v-else-if="item.icon === 'plans'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
                <svg v-else-if="item.icon === 'analytics'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <svg v-else-if="item.icon === 'notes'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                <svg v-else-if="item.icon === 'envelope'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <svg v-else-if="item.icon === 'receipt'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" />
                </svg>
                <svg v-else-if="item.icon === 'strategy'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                </svg>
                <svg v-else-if="item.icon === 'compass'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <circle cx="12" cy="12" r="9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.5 9.5-2 5-5 2 2-5 5-2z" />
                </svg>
                <svg v-else-if="item.icon === 'community'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <svg v-else-if="item.icon === 'bell'" class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="truncate">{{ item.name }}</span>
                <span v-if="item.isNew" :class="sidebarCollapsed ? 'lg:hidden' : ''" class="nav-badge-nuevo">Nuevo</span>
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
          <span :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ loggingOut ? 'Cerrando...' : 'Cerrar sesión' }}</span>
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

    <!-- MAIN WRAPPER -->
    <div
      :class="sidebarCollapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-60'"
      class="min-h-screen transition-all duration-300 lg:pb-8"
      style="padding-bottom: calc(49px + env(safe-area-inset-bottom, 0px) + 16px);"
    >
      <!-- TOPBAR mobile (iOS) -->
      <TopBarIos
        class="lg:hidden"
        variant="mobile"
        :date-label="todayDateLabel"
        :urgent-count="props.urgentCount"
        :coach-initial="userInitial"
        :coach-name="userName"
        @menu-open="sidebarOpen = !sidebarOpen"
        @action-pill-click="shell.openActionSheet()"
        @theme-toggle="shell.toggleDark()"
        @bell-click="router.push('/coach/notifications')"
        @avatar-click="router.push('/coach/profile')"
      >
        <template #bell-icon>
          <NotificationBell endpoint="/api/v/coach/notifications" />
        </template>
        <template #language>
          <LanguageSwitcher />
        </template>
      </TopBarIos>

      <!-- TOPBAR desktop (iOS) -->
      <TopBarIos
        class="hidden lg:flex"
        variant="desktop"
        :date-label="todayDateLabel"
        :coach-initial="userInitial"
        :coach-name="userName"
        @cmd-k-open="shell.openCmdPalette()"
        @actions-btn-click="shell.openCmdPalette()"
        @theme-toggle="shell.toggleDark()"
        @bell-click="router.push('/coach/notifications')"
        @avatar-click="router.push('/coach/profile')"
      >
        <template #bell-icon>
          <NotificationBell endpoint="/api/v/coach/notifications" />
        </template>
        <template #language>
          <LanguageSwitcher />
        </template>
      </TopBarIos>

      <!-- Page content -->
      <main class="px-4 sm:px-6 py-2 lg:py-6">
        <slot />
      </main>
    </div>

    <!-- BOTTOM NAV mobile (iOS, sin FAB) -->
    <BottomTabBar
      class="lg:hidden"
      :tabs="bottomTabs"
    />

    <!-- ACTION SHEET (reemplaza FAB) -->
    <ActionSheet
      v-model:open="shell.actionSheetOpen.value"
      title="Acciones rápidas"
      :actions="actionSheetItems"
    />

    <!-- CMD+K palette (desktop) -->
    <CmdPalette
      v-model:open="shell.cmdPaletteOpen.value"
      :sections="cmdPaletteSections"
    />

    <!-- Onboarding tour (preservado) -->
    <CoachOnboardingTour v-if="showTour" @done="onTourDone" />

    <!-- Global toast notifications -->
    <ToastContainer />
  </div>
</template>
