<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';
import CoachOnboardingChecklist from '../../components/CoachOnboardingChecklist.vue';

import HeroAlertCard from '../../components/coach/ios/HeroAlertCard.vue';
import GroupedActionList from '../../components/coach/ios/GroupedActionList.vue';
import KpiTile from '../../components/coach/ios/KpiTile.vue';
import UrgenteCard from '../../components/coach/ios/UrgenteCard.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';
import DisclosureSection from '../../components/coach/ios/DisclosureSection.vue';
import PanelGroup from '../../components/coach/ios/PanelGroup.vue';
import MessageFeedItem from '../../components/coach/ios/MessageFeedItem.vue';

const { t, tm } = useI18n();
const api = useApi();
const router = useRouter();
const loading = ref(true);
const error = ref(null);

const weeklyDowShort = computed(() => tm('coach_home.weekly_dow_short'));
const weeklyDowLong = computed(() => tm('coach_home.weekly_dow_long'));

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

const urgentClientsCount = ref(0);
const todayDateLabel = ref('');
const openTickets = ref(0);
const openTicketsList = ref([]);
const todayActivity = ref([]);
const sparklines = ref({ clients: [], checkins: [], messages: [], tickets: [] });

const clientProgressData = ref([]);
const checkinFrequencyData = ref([]);

function computeGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return t('coach_nav.greeting_morning');
    if (hour < 18) return t('coach_nav.greeting_afternoon');
    return t('coach_nav.greeting_evening');
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

        urgentClientsCount.value = data.urgentClientsCount ?? 0;
        todayDateLabel.value = data.todayDateLabel ?? '';
        openTickets.value = data.openTickets ?? 0;
        openTicketsList.value = data.openTicketsList ?? [];
        todayActivity.value = data.todayActivity ?? [];
        sparklines.value = data.sparklines ?? { clients: [], checkins: [], messages: [], tickets: [] };

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
    } catch (e) {
        error.value = t('coach_home.error_loading');
    } finally {
        loading.value = false;
    }
}

onMounted(loadDashboard);

const POLL_INTERVAL_MS = 30_000;
let pollTimer = null;

function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
        if (document.visibilityState === 'visible') {
            loadDashboard();
        }
    }, POLL_INTERVAL_MS);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function handleVisibility() {
    if (document.visibilityState === 'visible') {
        loadDashboard();
        startPolling();
    } else {
        stopPolling();
    }
}

onMounted(() => {
    startPolling();
    document.addEventListener('visibilitychange', handleVisibility);
});

onBeforeUnmount(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', handleVisibility);
});

const quickActionItems = computed(() => [
    {
        id: 'checkins', label: t('coach_home.qa_checkins'), to: '/coach/checkins',
        iconColor: '#DC2626', iconStrokeColor: '#DC2626',
        badge: stats.value.pendingCheckins,
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    },
    {
        id: 'mensajes', label: t('coach_home.qa_messages'), to: '/coach/messages',
        iconColor: '#DC2626', iconStrokeColor: '#DC2626',
        badge: stats.value.unreadMessages,
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>',
    },
    {
        id: 'tickets', label: t('coach_home.qa_tickets'), to: '/coach/plan-tickets',
        iconColor: '#3B82F6', iconStrokeColor: '#60a5fa',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75a3.75 3.75 0 0 1-7.5 0V6m-2.25 6H5.625c-.621 0-1.125.504-1.125 1.125v3.75c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-3.75c0-.621-.504-1.125-1.125-1.125H18.375"/>',
    },
    {
        id: 'analitica', label: t('coach_home.qa_analytics'), to: '/coach/analytics',
        iconColor: '#F59E0B', iconStrokeColor: '#fbbf24',
        iconSvgPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
    },
]);

const heroChips = computed(() => {
    const c = [];
    if (stats.value.pendingCheckins > 0) c.push({ label: t('coach_home.hero_chip_pending_checkins', { n: stats.value.pendingCheckins }), urgent: true });
    if (stats.value.unreadMessages > 0) c.push({ label: t('coach_home.hero_chip_unread', { n: stats.value.unreadMessages }) });
    return c;
});

const heroChipsDesktop = computed(() => {
    const c = [];
    if (stats.value.pendingCheckins > 0) c.push({ label: t('coach_home.hero_chip_pending_checkins_desktop', { n: stats.value.pendingCheckins }), urgent: true });
    if (stats.value.unreadMessages > 0) c.push({ label: t('coach_home.hero_chip_unread', { n: stats.value.unreadMessages }) });
    return c;
});

function pluralFromPipe(key, count, params = {}) {
    const raw = t(key, params);
    const parts = raw.split('|');
    return count === 1 ? parts[0] : (parts[1] || parts[0]);
}

const heroTitle = computed(() => {
    if (urgentClientsCount.value > 0) {
        return pluralFromPipe('coach_home.hero_clients_needing_attention', urgentClientsCount.value, { n: urgentClientsCount.value });
    }
    return t('coach_home.hero_title_all_clear');
});

const heroEyebrow = computed(() => urgentClientsCount.value > 0 ? t('coach_home.hero_eyebrow_attention') : t('coach_home.hero_eyebrow_on_track'));

function ticketBadgeClass(priority) {
    return ['urgent', 'high'].includes(priority)
        ? 'bg-red-500/20 text-red-400'
        : 'bg-amber-500/20 text-amber-400';
}
</script>

<template>
  <CoachLayout :urgent-count="urgentClientsCount">
    <!-- ==================== MOBILE ==================== -->
    <div class="lg:hidden flex flex-col gap-4 pt-3">

      <template v-if="loading">
        <div class="rounded-[22px] h-[140px] animate-pulse" style="background: var(--s2);" />
        <div class="grid grid-cols-2 gap-2">
          <div v-for="n in 4" :key="n" class="aspect-square rounded-[14px] animate-pulse" style="background: var(--s2);" />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div v-for="n in 4" :key="n" class="rounded-[14px] h-[100px] animate-pulse" style="background: var(--s2);" />
        </div>
      </template>

      <div v-else-if="error" class="rounded-[14px] border border-red-500/30 bg-red-500/5 p-8 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="loadDashboard" class="mt-4 action-pill">{{ t('coach_home.retry') }}</button>
      </div>

      <template v-else>
        <HeroAlertCard
          :variant="urgentClientsCount > 0 ? 'urgent' : 'success'"
          :eyebrow="heroEyebrow"
          :title="heroTitle"
          :chips="heroChips"
          @click="router.push('/coach/checkins')"
        />

        <GroupedActionList layout="mobile" :items="quickActionItems" />

        <div class="grid grid-cols-2 gap-2 anim-entry anim-entry-3">
          <KpiTile :label="t('coach_home.kpi_active_clients')" :value="stats.activeClients" :sparkline="sparklines.clients" />
          <KpiTile :label="t('coach_home.kpi_pending_checkins')" :value="stats.pendingCheckins" accent :sparkline="sparklines.checkins" />
          <KpiTile :label="t('coach_home.kpi_unread_messages')" :value="stats.unreadMessages" :sparkline="sparklines.messages" />
          <KpiTile :label="t('coach_home.kpi_open_tickets')" :value="openTickets" :muted="openTickets === 0" :sparkline="sparklines.tickets" />
        </div>

        <section class="anim-entry anim-entry-4">
          <header class="section-header">
            <span class="section-title">{{ t('coach_home.attention_title') }}</span>
            <span v-if="urgentClientsCount > 0" class="section-badge">
              {{ pluralFromPipe('coach_home.attention_count', urgentClientsCount, { n: urgentClientsCount }) }}
            </span>
          </header>
          <div v-if="attentionClients.length" class="flex flex-col gap-2">
            <UrgenteCard
              v-for="client in attentionClients"
              :key="client.id"
              :client-name="client.name"
              :sub-text="t('coach_home.attention_sub_unanswered', { value: client.oldest_checkin || t('coach_home.attention_pending_placeholder') })"
              :eta-label="t('coach_home.attention_eta_days', { n: client.pending_checkins })"
              @click="router.push(`/coach/clients/${client.id}`)"
              @cta-click="router.push('/coach/checkins')"
            />
          </div>
          <EmptyState
            v-else
            kind="success"
            :title="t('coach_home.attention_empty_title')"
            :subtitle="t('coach_home.attention_empty_sub')"
          />
        </section>

        <section class="anim-entry anim-entry-5">
          <header class="section-header">
            <span class="section-title">{{ t('coach_home.activity_title') }}</span>
          </header>
          <div v-if="todayActivity.length" class="panel">
            <div class="relative p-4">
              <div class="absolute left-[15px] top-4 bottom-4 w-px" style="background: var(--b1);" />
              <div class="flex flex-col gap-3 pl-8">
                <div v-for="(event, i) in todayActivity" :key="i" class="relative">
                  <span
                    class="absolute -left-[25px] top-1.5 w-3 h-3 rounded-full border-2 border-wc-bg"
                    :class="{
                      'bg-emerald-500': event.type === 'checkin',
                      'bg-blue-500': event.type === 'training',
                      'bg-wc-accent': event.type === 'message',
                    }"
                  />
                  <p class="text-sm text-wc-text">
                    <template v-if="event.type === 'checkin'">{{ t('coach_home.activity_event_checkin', { name: event.client_name }) }}</template>
                    <template v-else-if="event.type === 'training'">{{ t('coach_home.activity_event_training', { name: event.client_name }) }}</template>
                    <template v-else>{{ t('coach_home.activity_event_message', { name: event.client_name }) }}</template>
                  </p>
                  <p class="text-[11px]" style="color: var(--color-wc-text-3);">{{ event.time_ago }}</p>
                </div>
              </div>
            </div>
          </div>
          <EmptyState
            v-else
            kind="activity"
            :title="t('coach_home.activity_empty_title')"
            :subtitle="t('coach_home.activity_empty_sub')"
          />
        </section>

        <DisclosureSection
          :title="t('coach_home.weekly_title')"
          class="anim-entry anim-entry-6"
        >
          <div class="pt-3">
            <p class="text-[10px] font-semibold tracking-[0.07em] uppercase mb-2" style="color: var(--color-wc-text-3);">
              {{ t('coach_home.weekly_checkins_label') }}
            </p>
            <svg width="100%" height="56" viewBox="0 0 320 56" preserveAspectRatio="none" fill="none" :aria-label="t('coach_home.weekly_aria')">
              <rect x="10"  y="36" width="28" height="12" rx="4" fill="rgba(220,38,38,0.15)" />
              <rect x="56"  y="26" width="28" height="22" rx="4" fill="rgba(220,38,38,0.25)" />
              <rect x="102" y="16" width="28" height="32" rx="4" fill="rgba(220,38,38,0.35)" />
              <rect x="148" y="8"  width="28" height="40" rx="4" fill="rgba(220,38,38,0.5)" />
              <rect x="194" y="22" width="28" height="26" rx="4" fill="rgba(220,38,38,0.3)" />
              <rect x="240" y="30" width="28" height="18" rx="4" fill="rgba(220,38,38,0.2)" />
              <rect x="286" y="20" width="28" height="28" rx="4" fill="rgba(220,38,38,0.35)" />
              <text x="24"  y="54" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="8" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowShort[0] }}</text>
              <text x="70"  y="54" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="8" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowShort[1] }}</text>
              <text x="116" y="54" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="8" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowShort[2] }}</text>
              <text x="162" y="54" text-anchor="middle" fill="rgba(250,250,250,0.55)" font-size="8" font-family="Raleway,sans-serif" font-weight="700">{{ weeklyDowShort[3] }}</text>
              <text x="208" y="54" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="8" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowShort[4] }}</text>
              <text x="254" y="54" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="8" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowShort[5] }}</text>
              <text x="300" y="54" text-anchor="middle" fill="rgba(250,250,250,0.55)" font-size="8" font-family="Raleway,sans-serif" font-weight="700">{{ weeklyDowShort[6] }}</text>
            </svg>
          </div>
        </DisclosureSection>

        <PanelGroup
          v-if="recentMessages.length"
          :title="t('coach_home.messages_title')"
          :link="'/coach/messages'"
          :link-label="t('coach_home.messages_see_all')"
          class="anim-entry anim-entry-7"
          @link-click="router.push('/coach/messages')"
        >
          <MessageFeedItem
            v-for="(msg, i) in recentMessages"
            :key="msg.id || i"
            :client-name="msg.client_name"
            :avatar-tone="i % 2 === 0 ? 'gold' : 'purple'"
            :time-ago="msg.time_ago"
            :body="msg.message"
            :is-pr="msg.is_pr || /PR/i.test(msg.message)"
            :is-unread="!msg.is_read"
            @click="router.push('/coach/messages')"
          />
        </PanelGroup>

        <section class="anim-entry anim-entry-7">
          <header class="section-header">
            <span class="section-title">{{ t('coach_home.tickets_title') }}</span>
            <a href="/coach/plan-tickets" class="section-link" @click.prevent="router.push('/coach/plan-tickets')">
              {{ t('coach_home.tickets_see_all') }}
            </a>
          </header>
          <div v-if="openTicketsList.length" class="panel">
            <div
              v-for="ticket in openTicketsList"
              :key="ticket.id"
              class="panel-row flex items-center justify-between gap-2 cursor-pointer hover:bg-[var(--s2)] transition"
              style="transition-duration: var(--t-tap);"
              @click="router.push(`/coach/plan-tickets/${ticket.id}`)"
            >
              <div class="min-w-0">
                <p class="text-sm font-medium text-wc-text truncate">{{ ticket.title }}</p>
                <p class="text-[11px]" style="color: var(--color-wc-text-3);">{{ ticket.client_name }} · {{ ticket.created_ago }}</p>
              </div>
              <span
                class="ml-2 shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded"
                :class="ticketBadgeClass(ticket.priority)"
              >
                {{ (ticket.priority || 'low').toUpperCase() }}
              </span>
            </div>
          </div>
          <EmptyState
            v-else
            kind="tickets"
            :title="t('coach_home.tickets_empty_title')"
            :subtitle="t('coach_home.tickets_empty_sub')"
          />
        </section>

        <CoachOnboardingChecklist :days-old="coachDaysOld" />
      </template>
    </div>

    <!-- ==================== DESKTOP ==================== -->
    <div class="hidden lg:flex flex-col gap-6 pt-6">

      <template v-if="loading">
        <div class="rounded-[20px] h-[100px] animate-pulse" style="background: var(--s2);" />
        <div class="grid grid-cols-4 gap-3">
          <div v-for="n in 4" :key="n" class="rounded-[14px] h-[80px] animate-pulse" style="background: var(--s2);" />
        </div>
        <div class="grid grid-cols-4 gap-3">
          <div v-for="n in 4" :key="n" class="rounded-[14px] h-[100px] animate-pulse" style="background: var(--s2);" />
        </div>
      </template>

      <div v-else-if="error" class="rounded-[14px] border border-red-500/30 bg-red-500/5 p-8 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="loadDashboard" class="mt-4 action-pill">{{ t('coach_home.retry') }}</button>
      </div>

      <template v-else>
        <HeroAlertCard
          layout="desktop"
          :variant="urgentClientsCount > 0 ? 'urgent' : 'success'"
          :eyebrow="urgentClientsCount > 0 ? t('coach_home.hero_eyebrow_attention_desktop') : t('coach_home.hero_eyebrow_on_track')"
          :title="heroTitle"
          :chips="heroChipsDesktop"
          :cta-label="urgentClientsCount > 0 ? t('coach_home.hero_cta_review') : t('coach_home.hero_cta_checkins')"
          @click="router.push('/coach/checkins')"
          @cta-click="router.push('/coach/checkins')"
        />

        <GroupedActionList layout="desktop" :items="quickActionItems" />

        <div class="grid grid-cols-4 gap-3 anim-entry anim-entry-3">
          <KpiTile :label="t('coach_home.kpi_active_clients')" :value="stats.activeClients" :sparkline="sparklines.clients" />
          <KpiTile :label="t('coach_home.kpi_pending_checkins')" :value="stats.pendingCheckins" accent :sparkline="sparklines.checkins" />
          <KpiTile :label="t('coach_home.kpi_unread_messages')" :value="stats.unreadMessages" :sparkline="sparklines.messages" />
          <KpiTile :label="t('coach_home.kpi_open_tickets_desktop')" :value="openTickets" :muted="openTickets === 0" :sparkline="sparklines.tickets" />
        </div>

        <div class="grid grid-cols-2 gap-4 anim-entry anim-entry-4">
          <section>
            <header class="section-header">
              <span class="section-title">{{ t('coach_home.attention_title') }}</span>
              <span v-if="urgentClientsCount > 0" class="section-badge">
                {{ pluralFromPipe('coach_home.attention_count', urgentClientsCount, { n: urgentClientsCount }) }}
              </span>
            </header>
            <div class="panel">
              <div v-if="attentionClients.length" class="flex flex-col">
                <UrgenteCard
                  v-for="(client, i) in attentionClients"
                  :key="client.id"
                  :client-name="client.name"
                  :sub-text="t('coach_home.attention_sub_unanswered', { value: client.oldest_checkin || t('coach_home.attention_pending_placeholder') })"
                  :eta-label="t('coach_home.attention_eta_days', { n: client.pending_checkins })"
                  :class="i > 0 ? 'border-t border-[var(--b1)] rounded-none' : ''"
                  @click="router.push(`/coach/clients/${client.id}`)"
                  @cta-click="router.push('/coach/checkins')"
                />
              </div>
              <EmptyState
                v-else
                kind="success"
                :title="t('coach_home.attention_empty_title')"
                :subtitle="t('coach_home.attention_empty_sub')"
              />
            </div>
          </section>

          <section>
            <header class="section-header">
              <span class="section-title">{{ t('coach_home.activity_title') }}</span>
            </header>
            <div class="panel">
              <div v-if="todayActivity.length" class="p-4">
                <div class="relative">
                  <div class="absolute left-[15px] top-1.5 bottom-1.5 w-px" style="background: var(--b1);" />
                  <div class="flex flex-col gap-3 pl-8">
                    <div v-for="(event, i) in todayActivity" :key="i" class="relative">
                      <span
                        class="absolute -left-[25px] top-1.5 w-3 h-3 rounded-full border-2 border-wc-bg"
                        :class="{
                          'bg-emerald-500': event.type === 'checkin',
                          'bg-blue-500': event.type === 'training',
                          'bg-wc-accent': event.type === 'message',
                        }"
                      />
                      <p class="text-sm text-wc-text">
                        <template v-if="event.type === 'checkin'">{{ t('coach_home.activity_event_checkin_short', { name: event.client_name }) }}</template>
                        <template v-else-if="event.type === 'training'">{{ t('coach_home.activity_event_training', { name: event.client_name }) }}</template>
                        <template v-else>{{ t('coach_home.activity_event_message', { name: event.client_name }) }}</template>
                      </p>
                      <p class="text-[11px]" style="color: var(--color-wc-text-3);">{{ event.time_ago }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <EmptyState
                v-else
                kind="activity"
                :title="t('coach_home.activity_empty_title')"
                :subtitle="t('coach_home.activity_empty_sub')"
              />
            </div>
          </section>
        </div>

        <DisclosureSection
          :title="t('coach_home.weekly_title')"
          :max-height-open="240"
          class="anim-entry anim-entry-5"
        >
          <div class="pt-3">
            <p class="text-[10px] font-semibold tracking-[0.07em] uppercase mb-3" style="color: var(--color-wc-text-3);">
              {{ t('coach_home.weekly_checkins_label') }}
            </p>
            <svg width="100%" height="60" viewBox="0 0 700 60" preserveAspectRatio="none" fill="none" :aria-label="t('coach_home.weekly_aria')">
              <rect x="20"  y="38" width="60" height="14" rx="4" fill="rgba(220,38,38,0.15)" />
              <rect x="120" y="26" width="60" height="26" rx="4" fill="rgba(220,38,38,0.25)" />
              <rect x="220" y="14" width="60" height="38" rx="4" fill="rgba(220,38,38,0.35)" />
              <rect x="320" y="6"  width="60" height="46" rx="4" fill="rgba(220,38,38,0.55)" />
              <rect x="420" y="22" width="60" height="30" rx="4" fill="rgba(220,38,38,0.3)" />
              <rect x="520" y="32" width="60" height="20" rx="4" fill="rgba(220,38,38,0.2)" />
              <rect x="620" y="18" width="60" height="34" rx="4" fill="rgba(220,38,38,0.35)" />
              <text x="50"  y="58" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="9" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowLong[0] }}</text>
              <text x="150" y="58" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="9" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowLong[1] }}</text>
              <text x="250" y="58" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="9" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowLong[2] }}</text>
              <text x="350" y="58" text-anchor="middle" fill="rgba(250,250,250,0.6)"  font-size="9" font-family="Raleway,sans-serif" font-weight="700">{{ weeklyDowLong[3] }}</text>
              <text x="450" y="58" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="9" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowLong[4] }}</text>
              <text x="550" y="58" text-anchor="middle" fill="rgba(250,250,250,0.35)" font-size="9" font-family="Raleway,sans-serif" font-weight="600">{{ weeklyDowLong[5] }}</text>
              <text x="650" y="58" text-anchor="middle" fill="rgba(250,250,250,0.6)"  font-size="9" font-family="Raleway,sans-serif" font-weight="700">{{ weeklyDowLong[6] }}</text>
            </svg>
          </div>
        </DisclosureSection>

        <div class="grid grid-cols-2 gap-4 anim-entry anim-entry-6">
          <PanelGroup
            :title="t('coach_home.messages_title')"
            :link="'/coach/messages'"
            @link-click="router.push('/coach/messages')"
          >
            <MessageFeedItem
              v-for="(msg, i) in recentMessages"
              :key="msg.id || i"
              :client-name="msg.client_name"
              :avatar-tone="i % 2 === 0 ? 'gold' : 'purple'"
              :time-ago="msg.time_ago"
              :body="msg.message"
              :is-pr="msg.is_pr || /PR/i.test(msg.message)"
              :is-unread="!msg.is_read"
              @click="router.push('/coach/messages')"
            />
            <EmptyState
              v-if="!recentMessages.length"
              kind="messages"
              :title="t('coach_home.messages_empty_title')"
              :subtitle="t('coach_home.messages_empty_sub')"
            />
          </PanelGroup>

          <section>
            <header class="section-header">
              <span class="section-title">{{ t('coach_home.tickets_title') }}</span>
              <a href="/coach/plan-tickets" class="section-link" @click.prevent="router.push('/coach/plan-tickets')">
                {{ t('coach_home.tickets_see_all') }}
              </a>
            </header>
            <div class="panel">
              <div v-if="openTicketsList.length">
                <div
                  v-for="ticket in openTicketsList"
                  :key="ticket.id"
                  class="panel-row flex items-center justify-between gap-2 cursor-pointer hover:bg-[var(--s2)] transition"
                  style="transition-duration: var(--t-tap);"
                  @click="router.push(`/coach/plan-tickets/${ticket.id}`)"
                >
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-wc-text truncate">{{ ticket.title }}</p>
                    <p class="text-[11px]" style="color: var(--color-wc-text-3);">{{ ticket.client_name }} · {{ ticket.created_ago }}</p>
                  </div>
                  <span
                    class="ml-2 shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :class="ticketBadgeClass(ticket.priority)"
                  >
                    {{ (ticket.priority || 'low').toUpperCase() }}
                  </span>
                </div>
              </div>
              <EmptyState
                v-else
                kind="tickets"
                :title="t('coach_home.tickets_empty_title')"
                :subtitle="t('coach_home.tickets_empty_sub')"
              />
            </div>
          </section>
        </div>

        <CoachOnboardingChecklist :days-old="coachDaysOld" />
      </template>
    </div>
  </CoachLayout>
</template>
