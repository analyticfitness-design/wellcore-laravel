<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAdminCommunity } from '../../../composables/useAdminCommunity';
import { useAuthStore } from '../../../stores/auth';
import CoachAnalyticsKPIBar from '../../../components/admin/community/CoachAnalyticsKPIBar.vue';
import TimeSeriesChart from '../../../components/admin/community/TimeSeriesChart.vue';

const props = defineProps({
    coachId: { type: [Number, null], default: null },
});

const { fetchCoachAnalytics, loading, error } = useAdminCommunity();
const authStore = useAuthStore();
const data = ref(null);
const subSection = ref('overview');

const SUB_SECTIONS = [
    { key: 'overview',    label: 'Overview' },
    { key: 'engagement',  label: 'Engagement' },
    { key: 'clients',     label: 'Clientes' },
    { key: 'audit',       label: 'Audit Trail' },
];

const coach = computed(() => data.value?.coach || null);
const kpis = computed(() => {
    const k = data.value?.kpis || {};
    return {
        active_communities: k.active_clients ?? 0,
        posts_30d: k.total_posts_30d ?? 0,
        engagements_30d: 0,
    };
});
const fullKpis = computed(() => data.value?.kpis || {});
const postsPerDay = computed(() => data.value?.posts_per_day_90d || []);
const engagementPerDay = computed(() => data.value?.engagement_per_day_90d || []);
const topClients = computed(() => data.value?.top_clients || []);
const alerts = computed(() => data.value?.alerts || []);
const recentAudit = computed(() => data.value?.recent_audit || []);

async function load() {
    if (!props.coachId) {
        data.value = null;
        return;
    }
    data.value = await fetchCoachAnalytics(props.coachId);
}

async function impersonate() {
    if (!props.coachId) return;
    try {
        await authStore.startImpersonation({ type: 'admin', targetId: props.coachId });
        window.location.href = '/coach/community';
    } catch (err) {
        // toast handled elsewhere
        console.error(err);
    }
}

watch(() => props.coachId, () => load());
onMounted(() => load());
</script>

<template>
  <div v-if="!coachId" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-12 text-center">
    <p class="font-display text-lg text-wc-text">Selecciona un coach</p>
    <p class="text-sm text-wc-text-tertiary mt-2 max-w-md mx-auto">
      Click en un coach desde Pulse Cross-Coach para ver el análisis detallado de su comunidad.
    </p>
  </div>

  <div v-else-if="loading && !data" class="space-y-4">
    <div class="h-24 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
    <div class="h-48 rounded-xl bg-wc-bg-tertiary animate-pulse"></div>
  </div>

  <div v-else-if="error" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-6 text-center">{{ error }}</div>

  <div v-else-if="data" class="space-y-5">
    <header class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5 flex items-center gap-4">
      <div class="h-14 w-14 rounded-full bg-wc-accent/15 flex items-center justify-center overflow-hidden">
        <img v-if="coach.avatar_url" :src="coach.avatar_url" :alt="coach.name" class="h-full w-full object-cover" />
        <span v-else class="text-lg font-semibold text-wc-accent">{{ (coach.name || '?').charAt(0) }}</span>
      </div>
      <div class="flex-1 min-w-0">
        <h2 class="font-display text-2xl text-wc-text">{{ coach.name }}</h2>
        <p class="text-xs text-wc-text-tertiary">Coach desde {{ coach.joined_at ? new Date(coach.joined_at).toLocaleDateString('es-CO') : '—' }}</p>
      </div>
      <div class="flex gap-2">
        <button @click="impersonate" class="rounded-full bg-wc-accent text-white px-4 py-2 text-xs font-semibold hover:bg-wc-accent/90">
          Impersonar
        </button>
      </div>
    </header>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Clientes activos</p>
        <p class="font-display text-2xl text-wc-text mt-1">{{ fullKpis.active_clients ?? 0 }}</p>
      </div>
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Posts 30d</p>
        <p class="font-display text-2xl text-wc-text mt-1">{{ fullKpis.total_posts_30d ?? 0 }}</p>
      </div>
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Engagement</p>
        <p class="font-display text-2xl text-wc-text mt-1">{{ Math.round((fullKpis.engagement_rate ?? 0) * 100) }}%</p>
      </div>
      <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Resp p50</p>
        <p class="font-display text-2xl text-wc-text mt-1">{{ fullKpis.response_time_p50_min ?? 0 }}min</p>
      </div>
    </div>

    <nav class="flex items-center gap-2 border-b border-wc-border">
      <button v-for="s in SUB_SECTIONS" :key="s.key" @click="subSection = s.key"
        :class="subSection === s.key ? 'border-wc-accent text-wc-text font-semibold' : 'border-transparent text-wc-text-tertiary'"
        class="px-3 py-2 text-sm border-b-2">{{ s.label }}</button>
    </nav>

    <div v-if="subSection === 'overview'" class="space-y-4">
      <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
        <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Posts/día últimos 90d</h3>
        <TimeSeriesChart v-if="postsPerDay.length" :data="postsPerDay" :height="220" />
      </div>
      <div v-if="alerts.length" class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-4">
        <h3 class="text-xs uppercase tracking-widest text-amber-700 dark:text-amber-400 mb-2">Alertas</h3>
        <div class="space-y-2">
          <p v-for="(a, i) in alerts" :key="i" class="text-sm text-wc-text">
            ⚠️ {{ a.client_name }} · {{ a.days }} días sin actividad
          </p>
        </div>
      </div>
    </div>

    <div v-else-if="subSection === 'engagement'" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
      <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Engagement diario 90d</h3>
      <TimeSeriesChart v-if="engagementPerDay.length" :data="engagementPerDay" :height="220" color="#10B981" />
    </div>

    <div v-else-if="subSection === 'clients'" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
      <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Top clientes contributors</h3>
      <div class="space-y-2">
        <div v-for="c in topClients" :key="c.client_id" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-wc-bg-tertiary">
          <span class="text-sm text-wc-text">{{ c.client_name }}</span>
          <span class="text-xs text-wc-text-tertiary">{{ c.posts }} posts</span>
        </div>
        <p v-if="!topClients.length" class="text-sm text-wc-text-tertiary text-center py-4">Sin contribuyentes destacados.</p>
      </div>
    </div>

    <div v-else-if="subSection === 'audit'" class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
      <h3 class="text-xs uppercase tracking-widest text-wc-text-tertiary mb-3">Audit trail (últimas 10)</h3>
      <div class="space-y-2">
        <div v-for="(a, i) in recentAudit" :key="i" class="text-sm text-wc-text-secondary">
          <span class="font-mono text-xs text-wc-text-tertiary">{{ new Date(a.created_at).toLocaleString('es-CO') }}</span>
          · {{ a.action_type }} → post #{{ a.target_id }}
        </div>
        <p v-if="!recentAudit.length" class="text-sm text-wc-text-tertiary text-center py-4">Sin acciones de moderación recientes.</p>
      </div>
    </div>
  </div>
</template>
