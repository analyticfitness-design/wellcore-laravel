<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  // [{ coach_id, name, clients, tickets_completados, ... }, ...] o vacio
  coaches: { type: Array, default: () => [] },
});

const isEmpty = computed(() => !props.coaches || props.coaches.length === 0);

const podium = computed(() => (props.coaches || []).slice(0, 3));
const restList = computed(() => (props.coaches || []).slice(3, 5));

function podiumColor(idx) {
  if (idx === 0) return 'gold';
  if (idx === 1) return 'silver';
  if (idx === 2) return 'bronze';
  return 'neutral';
}
</script>

<template>
  <section class="top-coaches">
    <header class="coaches-header">
      <h2 class="coaches-title">TOP COACHES</h2>
      <span class="coaches-period">ESTE MES</span>
    </header>

    <!-- Empty state — caso prod actual: top_coaches_month vacio porque
         ningun ticket fue completado este mes -->
    <div v-if="isEmpty" class="coaches-empty">
      <div class="coaches-empty-num">—</div>
      <p class="coaches-empty-msg">
        "Sin tickets completados este mes. El ranking se llenara cuando los coaches cierren tickets."
      </p>
      <RouterLink to="/admin/coaches" class="coaches-empty-cta">
        VER COACHES <span aria-hidden="true">→</span>
      </RouterLink>
    </div>

    <!-- Podium top 3 + rest -->
    <div v-else class="coaches-content">
      <div class="podium">
        <article
          v-for="(coach, idx) in podium"
          :key="coach.coach_id || idx"
          class="podium-card"
          :class="`podium-card--${podiumColor(idx)}`"
        >
          <div class="podium-rank">#{{ idx + 1 }}</div>
          <div class="podium-name">{{ coach.name }}</div>
          <div class="podium-tickets">{{ coach.tickets_completados || 0 }}</div>
          <div class="podium-tickets-label">TICKETS</div>
          <div v-if="coach.clients" class="podium-clients">{{ coach.clients }} clientes</div>
        </article>
      </div>

      <ul v-if="restList.length" class="coaches-rest">
        <li
          v-for="(coach, idx) in restList"
          :key="coach.coach_id || idx + 3"
          class="coach-row"
        >
          <span class="coach-row-rank">#{{ idx + 4 }}</span>
          <span class="coach-row-name">{{ coach.name }}</span>
          <span class="coach-row-tickets">{{ coach.tickets_completados || 0 }}</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
/* ============================================================================
   AdminTopCoaches — podio top 3 + lista 4-5 / empty state editorial.
   v2: Oswald ranks/labels, Raleway names, tokens v2.
   ============================================================================ */

.top-coaches {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    padding: 18px;
}
.coaches-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.coaches-title {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.coaches-period {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* ── Empty state ──────────────────────────────────────────────────────── */
.coaches-empty {
    padding: 18px 8px 14px;
    text-align: center;
}
.coaches-empty-num {
    font-family: var(--font-display);
    font-size: 56px; font-weight: 700;
    color: var(--c-surface-2);
    letter-spacing: var(--ls-display, -0.02em);
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.coaches-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-weight: 300;
    font-size: 13px;
    color: var(--c-text-3);
    line-height: var(--lh-body, 1.65);
    margin: 0 0 16px;
    text-wrap: balance;
}
.coaches-empty-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 11px; font-weight: 600;
    letter-spacing: 1.6px;
    color: var(--c-text-2);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid var(--c-border);
    padding-bottom: 4px;
    min-height: 44px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.coaches-empty-cta:hover {
    color: var(--c-text);
    border-bottom-color: var(--c-accent);
}

/* ── Content (podium + rest) ───────────────────────────────────────────── */
.coaches-content {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.podium {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
}
.podium-card {
    border-radius: var(--r-sm, 12px);
    padding: 12px 10px;
    text-align: center;
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
}
.podium-card--gold {
    border-color: rgba(212,160,76,0.3);
    background: linear-gradient(180deg, rgba(212,160,76,0.1), var(--c-surface-2));
}
.podium-card--silver {
    border-color: rgba(163,163,163,0.25);
    background: linear-gradient(180deg, rgba(163,163,163,0.08), var(--c-surface-2));
}
.podium-card--bronze {
    border-color: rgba(180,83,9,0.3);
    background: linear-gradient(180deg, rgba(180,83,9,0.08), var(--c-surface-2));
}
.podium-rank {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
    margin-bottom: 4px;
}
.podium-card--gold .podium-rank { color: var(--c-amber, #D4A80E); }
.podium-name {
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    color: var(--c-text);
    margin-bottom: 8px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.podium-tickets {
    font-family: var(--font-display);
    font-size: 24px; font-weight: 700;
    color: #34D399;
    line-height: 1;
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
.podium-tickets-label {
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-top: 2px;
}
.podium-clients {
    font-family: var(--font-sans);
    font-size: 12px; font-weight: 400;
    color: var(--c-text-3);
    margin-top: 6px;
}

/* ── Rest list (rank 4-5) ─────────────────────────────────────────────── */
.coaches-rest {
    list-style: none;
    margin: 0;
    padding: 0;
    border-top: 1px solid var(--c-border);
}
.coach-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    min-height: var(--tap-comfort, 48px);
    border-bottom: 1px solid var(--c-border);
}
.coach-row:last-child { border-bottom: none; }
.coach-row-rank {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
    flex-shrink: 0;
    width: 28px;
}
.coach-row-name {
    flex: 1;
    font-family: var(--font-sans);
    font-size: 15px; font-weight: 500;
    color: var(--c-text-2);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.coach-row-tickets {
    font-family: var(--font-display);
    font-size: 14px; font-weight: 700;
    color: #34D399;
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
</style>
