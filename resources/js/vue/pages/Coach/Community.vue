<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useCoachAnnounce } from '../../composables/useCoachAnnounce';
import CoachLayout from '../../layouts/CoachLayout.vue';
import CoachAnnounceModal from '../../components/community/CoachAnnounceModal.vue';
import CoachCommunityTour from '../../components/community/CoachCommunityTour.vue';

import CoachLatidoTab from './community/CoachLatidoTab.vue';
import CoachPostsTab from './community/CoachPostsTab.vue';
import CoachConversacionesTab from './community/CoachConversacionesTab.vue';
import CoachPulsosTab from './community/CoachPulsosTab.vue';
import CoachLogrosTab from './community/CoachLogrosTab.vue';

const TABS = [
    { key: 'latido',         label: 'Latido del Equipo', component: CoachLatidoTab },
    { key: 'posts',          label: 'Posts',             component: CoachPostsTab },
    { key: 'conversaciones', label: 'Conversaciones',    component: CoachConversacionesTab },
    { key: 'pulsos',         label: 'Pulsos',            component: CoachPulsosTab },
    { key: 'logros',         label: 'Logros',            component: CoachLogrosTab },
];

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const announce = useCoachAnnounce();

const previousTabIndex = ref(0);
const activeTab = ref(TABS.find(t => t.key === route.hash.slice(1))?.key || 'latido');
const activeComponent = computed(() => TABS.find(t => t.key === activeTab.value)?.component);

const triggerPostsRefresh = ref(0);
let coachChannel = null;

function changeTab(key) {
    previousTabIndex.value = TABS.findIndex(t => t.key === activeTab.value);
    activeTab.value = key;
    router.replace({ hash: `#${key}` });
}

const transitionDirection = computed(() => {
    const newIdx = TABS.findIndex(t => t.key === activeTab.value);
    return newIdx > previousTabIndex.value ? 'right' : 'left';
});

function openAnnounce() {
    announce.open();
}

function quickMessageHandler(e) {
    const client = e.detail;
    const firstName = client?.client_name?.split(' ')[0] || client?.name?.split(' ')[0] || '';
    const days = client?.days_inactive || 'unos';
    announce.message.value = `Hola ${firstName}, vi que llevas ${days} días sin actividad. ¿Cómo te puedo ayudar?`;
    announce.mode.value = 'push';
    announce.open();
}

function handleActivity(event) {
    if (event.eventType === 'post_created' || event.event_type === 'post_created') {
        window.dispatchEvent(new CustomEvent('coach-community:new-post', { detail: event.payload }));
    }
}

onMounted(() => {
    if (window.Echo && authStore.userId) {
        coachChannel = window.Echo.private(`coach.${authStore.userId}.community`)
            .listen('.coach-community-activity', handleActivity)
            .listen('.post-pinned', () => triggerPostsRefresh.value++)
            .listen('.post-made-official', () => triggerPostsRefresh.value++)
            .listen('.post-reported', () => triggerPostsRefresh.value++);
    }
    window.addEventListener('coach-community:quick-message', quickMessageHandler);
    window.addEventListener('coach-community:open-announce', openAnnounce);
});

onBeforeUnmount(() => {
    window.removeEventListener('coach-community:quick-message', quickMessageHandler);
    window.removeEventListener('coach-community:open-announce', openAnnounce);
    if (coachChannel && window.Echo) {
        window.Echo.leave(`coach.${authStore.userId}.community`);
    }
});

watch(() => route.hash, (h) => {
    const key = h.slice(1);
    if (TABS.some(t => t.key === key) && key !== activeTab.value) {
        changeTab(key);
    }
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-4">
      <header class="space-y-1">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Comunidad</h1>
        <p class="text-sm text-wc-text-tertiary">
          La comunidad de tus clientes. Modera, motiva, conecta.
        </p>
      </header>

      <nav class="sticky top-16 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-wc-bg/80 backdrop-blur-xl border-b border-wc-border">
        <div class="flex items-center gap-1 overflow-x-auto pb-px">
          <button
            v-for="tab in TABS" :key="tab.key"
            @click="changeTab(tab.key)"
            :class="activeTab === tab.key
              ? 'border-wc-accent text-wc-text font-semibold'
              : 'border-transparent text-wc-text-tertiary hover:text-wc-text-secondary'"
            class="shrink-0 px-4 py-3 text-sm border-b-2 transition-colors"
          >{{ tab.label }}</button>
        </div>
      </nav>

      <div class="pt-2">
        <Transition
          mode="out-in"
          :enter-from-class="transitionDirection === 'right' ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4'"
          enter-active-class="duration-200 ease-out"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="duration-150 ease-in"
          :leave-to-class="transitionDirection === 'right' ? 'opacity-0 -translate-x-2' : 'opacity-0 translate-x-2'"
        >
          <component
            :is="activeComponent"
            :key="activeTab"
            :trigger-refresh="triggerPostsRefresh"
            @open-announce="openAnnounce"
          />
        </Transition>
      </div>

      <button
        @click="openAnnounce"
        class="hidden lg:flex fixed bottom-6 right-6 z-30 items-center gap-2 rounded-full bg-wc-accent text-white px-5 py-3 shadow-2xl hover:shadow-wc-accent/40 hover:scale-105 transition-all"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535" />
        </svg>
        <span class="text-sm font-semibold">Mensaje al equipo</span>
      </button>

      <CoachAnnounceModal />
      <CoachCommunityTour />
    </div>
  </CoachLayout>
</template>
