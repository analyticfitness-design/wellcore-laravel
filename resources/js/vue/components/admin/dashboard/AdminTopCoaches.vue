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
   Solo desktop (mobile lo oculta el padre con lg:hidden si quiere).
   ============================================================================ */

.top-coaches {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
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
    font-size: 13px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.coaches-period {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* ── Empty state ──────────────────────────────────────────────────────── */
.coaches-empty {
    padding: 18px 8px 14px;
    text-align: center;
}
.coaches-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.coaches-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}
.coaches-empty-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    color: var(--color-wc-text-secondary);
    text-decoration: none;
    text-transform: uppercase;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.coaches-empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
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
    border-radius: 10px;
    padding: 12px 10px;
    text-align: center;
    border: 1px solid var(--color-wc-border);
    background: rgba(24, 24, 24, 0.6);
}
.podium-card--gold {
    border-color: rgba(212, 160, 76, 0.3);
    background: linear-gradient(180deg, rgba(212, 160, 76, 0.1), rgba(24, 24, 24, 0.6));
}
.podium-card--silver {
    border-color: rgba(163, 163, 163, 0.25);
    background: linear-gradient(180deg, rgba(163, 163, 163, 0.08), rgba(24, 24, 24, 0.6));
}
.podium-card--bronze {
    border-color: rgba(180, 83, 9, 0.3);
    background: linear-gradient(180deg, rgba(180, 83, 9, 0.08), rgba(24, 24, 24, 0.6));
}
.podium-rank {
    font-family: var(--font-mono, monospace);
    font-size: 10px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 4px;
}
.podium-card--gold .podium-rank { color: var(--color-wc-gold, #C8A769); }
.podium-name {
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 700;
    color: var(--color-wc-text);
    margin-bottom: 8px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.podium-tickets {
    font-family: var(--font-display);
    font-size: 24px;
    color: var(--color-wc-green-text, #34D399);
    line-height: 1;
}
.podium-tickets-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
}
.podium-clients {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    color: var(--color-wc-text-tertiary);
    margin-top: 6px;
}

/* ── Rest list (rank 4-5) ─────────────────────────────────────────────── */
.coaches-rest {
    list-style: none;
    margin: 0;
    padding: 0;
    border-top: 1px solid var(--color-wc-border);
}
.coach-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.coach-row:last-child { border-bottom: none; }
.coach-row-rank {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
    width: 28px;
}
.coach-row-name {
    flex: 1;
    font-size: 12px;
    font-weight: 500;
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.coach-row-tickets {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px;
    font-weight: 700;
    color: var(--color-wc-green-text, #34D399);
}
</style>
