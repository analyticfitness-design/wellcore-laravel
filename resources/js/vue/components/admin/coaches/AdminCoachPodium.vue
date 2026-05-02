<script setup>
import { computed } from 'vue';

const props = defineProps({
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

function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}
</script>

<template>
  <section class="podium-card">
    <header class="podium-head">
      <div class="head-titles">
        <h2 class="podium-title">PODIO DEL MES</h2>
        <span class="podium-eyebrow">RANKING POR TICKETS CERRADOS</span>
      </div>
    </header>

    <div v-if="isEmpty" class="podium-empty">
      <div class="empty-num">—</div>
      <p class="empty-msg">
        "Sin tickets completados este mes. El ranking se llenara cuando los coaches cierren tickets."
      </p>
      <p class="empty-hint">
        El podio se calcula sobre tickets de plan cerrados en el mes corriente.
      </p>
    </div>

    <div v-else class="podium-content">
      <div class="podium-grid">
        <article
          v-for="(coach, idx) in podium"
          :key="coach.coach_id || `pod-${idx}`"
          class="podium-tile"
          :class="`podium-tile--${podiumColor(idx)}`"
        >
          <span class="tile-rank">#{{ idx + 1 }}</span>
          <span class="tile-avatar">{{ avatarInitial(coach.name) }}</span>
          <div class="tile-name" :title="coach.name">{{ coach.name }}</div>
          <div class="tile-tickets">{{ coach.tickets_completados || 0 }}</div>
          <div class="tile-tickets-label">TICKETS</div>
          <div v-if="coach.clients" class="tile-clients">{{ coach.clients }} clientes</div>
        </article>
      </div>

      <ul v-if="restList.length" class="podium-rest">
        <li
          v-for="(coach, idx) in restList"
          :key="coach.coach_id || `rest-${idx}`"
          class="rest-row"
        >
          <span class="rest-rank">#{{ idx + 4 }}</span>
          <span class="rest-name">{{ coach.name }}</span>
          <span class="rest-tickets">{{ coach.tickets_completados || 0 }}</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.podium-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    padding: 18px;
}
.podium-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 12px;
}
.head-titles { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.podium-title {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.podium-eyebrow {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* ── Empty ────────────────────────────────────────────────────────────── */
.podium-empty {
    padding: 22px 8px 14px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px; font-weight: 700;
    color: var(--c-surface-2);
    letter-spacing: var(--ls-display, -0.02em);
    line-height: 1;
    margin-bottom: 10px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-weight: 300;
    font-size: 13px;
    color: var(--c-text-3);
    line-height: var(--lh-body, 1.65);
    margin: 0 0 10px;
    text-wrap: balance;
}
.empty-hint {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    opacity: 0.7;
    margin: 0;
}

/* ── Podium grid ──────────────────────────────────────────────────────── */
.podium-content {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.podium-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
}
.podium-tile {
    border-radius: var(--r-sm, 12px);
    padding: 14px 10px 12px;
    text-align: center;
    border: 1px solid var(--c-border);
    background: var(--c-surface-2);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.podium-tile--gold {
    border-color: rgba(212,160,76,0.35);
    background: linear-gradient(180deg, rgba(212,160,76,0.12), var(--c-surface-2));
}
.podium-tile--silver {
    border-color: rgba(163,163,163,0.28);
    background: linear-gradient(180deg, rgba(163,163,163,0.1), var(--c-surface-2));
}
.podium-tile--bronze {
    border-color: rgba(180,83,9,0.32);
    background: linear-gradient(180deg, rgba(180,83,9,0.1), var(--c-surface-2));
}

.tile-rank {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
}
.podium-tile--gold .tile-rank { color: var(--c-amber, #D4A80E); }

.tile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--c-accent-dim);
    border: 1px solid rgba(220,38,38,0.25);
    color: #F87171;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 14px; font-weight: 700;
    letter-spacing: var(--ls-display, -0.02em);
}
.podium-tile--gold .tile-avatar {
    background: rgba(212,160,76,0.12);
    border-color: rgba(212,160,76,0.35);
    color: var(--c-amber, #D4A80E);
}

.tile-name {
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    color: var(--c-text);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}
.tile-tickets {
    font-family: var(--font-display);
    font-size: 26px; font-weight: 700;
    color: #34D399;
    line-height: 1;
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
.tile-tickets-label {
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.tile-clients {
    font-family: var(--font-sans);
    font-size: 12px; font-weight: 400;
    color: var(--c-text-3);
    margin-top: 2px;
}

/* ── Rest list (4-5) ──────────────────────────────────────────────────── */
.podium-rest {
    list-style: none;
    margin: 0;
    padding: 8px 0 0;
    border-top: 1px solid var(--c-border);
    display: flex;
    flex-direction: column;
}
.rest-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    min-height: var(--tap-comfort, 48px);
    border-bottom: 1px solid var(--c-border);
}
.rest-row:last-child { border-bottom: none; }
.rest-rank {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.4px;
    color: var(--c-text-3);
    flex-shrink: 0;
    width: 28px;
}
.rest-name {
    flex: 1;
    font-family: var(--font-sans);
    font-size: 14px; font-weight: 500;
    color: var(--c-text-2);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.rest-tickets {
    font-family: var(--font-display);
    font-size: 14px; font-weight: 700;
    color: #34D399;
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
</style>
