<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useModerationQueue } from '../../composables/useModerationQueue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminPulseCrossCoachTab from './community/AdminPulseCrossCoachTab.vue';
import AdminLiveFeedCommunityTab from './community/AdminLiveFeedCommunityTab.vue';
import AdminBroadcastCenterTab from './community/AdminBroadcastCenterTab.vue';
import AdminModerationQueueTab from './community/AdminModerationQueueTab.vue';
import AdminAnalyticsCoachTab from './community/AdminAnalyticsCoachTab.vue';

const TABS = [
    { key: 'pulse',          label: 'Pulse Cross-Coach', component: AdminPulseCrossCoachTab },
    { key: 'live-feed',      label: 'Live Feed',         component: AdminLiveFeedCommunityTab },
    { key: 'broadcast',      label: 'Broadcast Center',  component: AdminBroadcastCenterTab },
    { key: 'moderation',     label: 'Moderation',        component: AdminModerationQueueTab },
    { key: 'analytics',      label: 'Analytics Coach',   component: AdminAnalyticsCoachTab },
];

const route = useRoute();
const router = useRouter();
const moderation = useModerationQueue();

const previousTabIndex = ref(0);

function parseHash(hash) {
    const parts = hash.slice(1).split('-');
    const key = parts[0] || 'pulse';
    const coachId = parts[1] ? parseInt(parts[1], 10) : null;
    return { key, coachId };
}

const initialHash = parseHash(route.hash);
const activeTab = ref(TABS.find(t => t.key === initialHash.key)?.key || 'pulse');
const selectedCoachId = ref(initialHash.coachId);

const activeComponent = computed(() => TABS.find(t => t.key === activeTab.value)?.component);

function changeTab(key, coachId = null) {
    previousTabIndex.value = TABS.findIndex(t => t.key === activeTab.value);
    activeTab.value = key;
    if (key === 'analytics' && coachId) {
        selectedCoachId.value = coachId;
        router.replace({ hash: `#analytics-${coachId}` });
    } else {
        router.replace({ hash: `#${key}` });
    }
}

function onDrillDown(coachId) {
    changeTab('analytics', coachId);
}

const transitionDirection = computed(() => {
    const newIdx = TABS.findIndex(t => t.key === activeTab.value);
    return newIdx > previousTabIndex.value ? 'right' : 'left';
});

let adminChannel = null;

onMounted(() => {
    moderation.fetchQueue();

    if (window.Echo) {
        adminChannel = window.Echo.private('admin.community')
            .listen('.post-reported', () => moderation.fetchQueue({ force: true }))
            .listen('.broadcast-sent', () => {})
            .listen('.post-made-official', () => {});
    }
});

onBeforeUnmount(() => {
    if (adminChannel && window.Echo) window.Echo.leave('admin.community');
});

watch(() => route.hash, (h) => {
    const parsed = parseHash(h);
    if (TABS.some(t => t.key === parsed.key) && (parsed.key !== activeTab.value || parsed.coachId !== selectedCoachId.value)) {
        changeTab(parsed.key, parsed.coachId);
    }
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-4 p-4 sm:p-6">
      <header>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Comunidad WellCore</h1>
        <p class="text-sm text-wc-text-tertiary mt-1">Cross-coach analytics, broadcast, moderation.</p>
      </header>

      <nav class="sticky top-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 bg-wc-bg/80 backdrop-blur-xl border-b border-wc-border">
        <div class="flex items-center gap-1 overflow-x-auto pb-px">
          <button
            v-for="tab in TABS" :key="tab.key"
            @click="changeTab(tab.key)"
            :class="activeTab === tab.key ? 'border-wc-accent text-wc-text font-semibold' : 'border-transparent text-wc-text-tertiary hover:text-wc-text-secondary'"
            class="shrink-0 px-4 py-3 text-sm border-b-2 transition-colors flex items-center gap-2"
          >
            {{ tab.label }}
            <span v-if="tab.key === 'moderation' && moderation.pendingCount.value > 0" class="rounded-full bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 min-w-[20px] text-center">
              {{ moderation.pendingCount.value }}
            </span>
          </button>
        </div>
      </nav>

      <div class="pt-2">
        <Transition mode="out-in"
          :enter-from-class="transitionDirection === 'right' ? 'opacity-0 translate-x-4' : 'opacity-0 -translate-x-4'"
          enter-active-class="duration-200 ease-out"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="duration-150 ease-in"
          :leave-to-class="transitionDirection === 'right' ? 'opacity-0 -translate-x-2' : 'opacity-0 translate-x-2'"
        >
          <component :is="activeComponent" :key="activeTab" :coach-id="selectedCoachId" @drill-down="onDrillDown" />
        </Transition>
      </div>
    </div>
  </AdminLayout>
</template>
