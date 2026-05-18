<script setup>
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useGroupPulse } from '../../composables/useGroupPulse';
import { useReducedMotion } from '../../composables/useReducedMotion';

const router = useRouter();
const { t } = useI18n();
const { summary, loading, fetchSummary } = useGroupPulse();
const reducedMotion = useReducedMotion();

onMounted(() => fetchSummary());

const stats = computed(() => summary.value?.stats ?? null);
const topEvents = computed(() => summary.value?.top_events ?? []);
const bpm = computed(() => summary.value?.bpm ?? 60);
const activeNow = computed(() => summary.value?.active_now ?? 0);
const isQuiet = computed(() => Boolean(summary.value?.is_quiet));
const groupSize = computed(() => summary.value?.group_size ?? 0);

// Heartbeat duration scales with BPM (60 BPM = 1s per beat).
// Reduced motion → 0s desactiva la animación CSS por completo.
const heartbeatStyle = computed(() => ({
    animationDuration: reducedMotion.value ? '0s' : `${60 / bpm.value}s`,
}));

// Meta line: cuando el grupo es solo el usuario (1) o no hay actividad,
// mostramos copy más humano que "0 activos · 50 BPM".
const metaText = computed(() => {
    if (groupSize.value <= 1) return t('client_home.gp_group_growing');
    if (isQuiet.value) return t('client_home.gp_group_resting');
    return t('client_home.gp_active_bpm', { n: activeNow.value, bpm: bpm.value });
});

function goToCommunity() {
    // Vue router registra /client/community (NO /comunidad). Sin /client el
    // CTA aterrizaba en 404 — bug encontrado en smoke audit 2026-05-05.
    router.push({ path: '/client/community', query: { tab: 'latido' } });
}
</script>

<template>
  <section
    v-if="summary && stats"
    class="card section grain wc-card-group-pulse"
    :style="{ animationDelay: '260ms' }"
  >
    <div class="card-head">
      <div class="card-head-left">
        <span class="gp-pulse-dot" :style="heartbeatStyle" aria-hidden="true"></span>
        <span class="card-title">{{ t('client_home.gp_title') }}</span>
      </div>
      <span class="card-meta">{{ metaText }}</span>
    </div>

    <div class="gp-stats">
      <div class="stat-card red">
        <div class="stat-label">{{ t('client_home.gp_workouts_today') }}</div>
        <div class="stat-value tight tnum">{{ stats.workouts_today }}</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">{{ t('client_home.gp_prs_week') }}</div>
        <div class="stat-value tight tnum">{{ stats.prs_week }}</div>
      </div>
      <div class="stat-card amber">
        <div class="stat-label">{{ t('client_home.gp_achievements_today') }}</div>
        <div class="stat-value tight tnum">{{ stats.achievements_today }}</div>
      </div>
    </div>

    <div v-if="topEvents.length" class="gp-events">
      <div
        v-for="(ev, idx) in topEvents"
        :key="idx"
        class="gp-event"
        :data-type="ev.type"
      >
        <span class="gp-event-text">
          <strong v-if="ev.client_name">{{ ev.client_name }}</strong>
          <span>{{ ev.headline }}</span>
        </span>
        <span v-if="ev.minutes_ago !== undefined" class="gp-event-time tnum">
          {{ t('client_home.gp_minutes_ago', { n: ev.minutes_ago }) }}
        </span>
      </div>
    </div>

    <button type="button" class="gp-see-all" @click="goToCommunity">
      {{ t('client_home.gp_see_all') }}
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M13 5l7 7-7 7"/>
      </svg>
    </button>
  </section>

  <section
    v-else-if="loading"
    class="card section wc-card-group-pulse"
    aria-busy="true"
    :style="{ animationDelay: '260ms' }"
  >
    <div class="gp-skel-line" style="width:42%"></div>
    <div class="gp-skel-line" style="width:88%"></div>
    <div class="gp-skel-line" style="width:64%"></div>
  </section>
</template>

<style scoped>
/* Component-specific only. All tokens come from .wc-shell scope. */
.wc-card-group-pulse {
    padding: var(--s16) var(--s20);
}

/* Animated pulsing dot — replaces emoji-like heart with brand-token red.
   El rgba con valor #DC2626 está hardcoded por las transparencias de glow,
   no es un override del token sino la versión semitransparente del mismo accent. */
.gp-pulse-dot {
    width: 12px;
    height: 12px;
    border-radius: var(--r-pill);
    background: var(--wc-accent);
    box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6), 0 0 6px rgba(220, 38, 38, 0.5);
    animation: gp-beat 1.2s ease-in-out infinite;
}
@keyframes gp-beat {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.55), 0 0 6px rgba(220, 38, 38, 0.5); }
    50% { transform: scale(1.35); box-shadow: 0 0 0 5px rgba(220, 38, 38, 0), 0 0 12px rgba(220, 38, 38, 0.3); }
}
@media (prefers-reduced-motion: reduce) {
    .gp-pulse-dot { animation: none; }
}

/* Stats grid reuses .stat-card variant pattern (red/green/amber/purple)
   from DashboardStats.vue — definitions live in wc-shell.css.
   Este componente solo las dispone 3-up. */
.gp-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--s8);
    margin-top: var(--s12);
    margin-bottom: var(--s16);
}

/* Events list — usa --wc-bg3 para fondo sutil + accent left rule.
   Variantes por tipo de evento via data-type. */
.gp-events {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: var(--s12);
}
.gp-event {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--s8);
    padding: var(--s8) var(--s12);
    background: var(--wc-bg3);
    border: 1px solid var(--wc-border);
    border-left: 2px solid var(--wc-accent);
    border-radius: var(--r-sm);
    font: 400 13px/1.4 var(--fs);
    color: var(--wc-text-2);
    transition: background 200ms var(--ease-out), border-color 200ms var(--ease-out);
}
.gp-event:hover {
    background: var(--wc-bg4);
}
.gp-event[data-type="aggregate"] { border-left-color: var(--wc-text-3); }
.gp-event[data-type="streak_milestone"] { border-left-color: var(--wc-amber); }
.gp-event[data-type="achievement"] { border-left-color: var(--wc-purple); }
.gp-event-text strong {
    color: var(--wc-text);
    font-weight: 600;
    margin-right: 4px;
}
.gp-event-time {
    color: var(--wc-text-3);
    font-size: 11px;
    flex-shrink: 0;
    text-transform: lowercase;
}

/* "Ver todo" CTA — minimal, accent text, arrow icon. */
.gp-see-all {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: auto;
    padding: 6px 0;
    background: transparent;
    border: 0;
    color: var(--wc-accent);
    font: 600 12px/1 var(--fs);
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: color 200ms var(--ease-out), gap 200ms var(--ease-out);
}
.gp-see-all:hover {
    color: var(--wc-accent-2);
    gap: 10px;
}
.gp-see-all svg { transition: transform 200ms var(--ease-out); }

/* Skeleton — mismo primitive usado en otros dashboards */
.gp-skel-line {
    height: 14px;
    background: linear-gradient(90deg, var(--wc-bg3), var(--wc-bg4), var(--wc-bg3));
    background-size: 200% 100%;
    border-radius: var(--r-sm);
    margin-bottom: var(--s8);
    animation: gp-shimmer 1.4s ease-in-out infinite;
}
@keyframes gp-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
@media (prefers-reduced-motion: reduce) {
    .gp-skel-line { animation: none; }
}
</style>
