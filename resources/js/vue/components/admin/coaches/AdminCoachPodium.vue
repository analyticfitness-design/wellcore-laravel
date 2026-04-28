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
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
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
    font-size: 16px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.podium-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* ── Empty ────────────────────────────────────────────────────────────── */
.podium-empty {
    padding: 22px 8px 14px;
    text-align: center;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 10px;
    user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 10px;
    text-wrap: balance;
}
.empty-hint {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
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
    border-radius: 12px;
    padding: 14px 10px 12px;
    text-align: center;
    border: 1px solid var(--color-wc-border);
    background: rgba(24, 24, 24, 0.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.podium-tile--gold {
    border-color: rgba(212, 160, 76, 0.35);
    background: linear-gradient(180deg, rgba(212, 160, 76, 0.12), rgba(24, 24, 24, 0.6));
}
.podium-tile--silver {
    border-color: rgba(163, 163, 163, 0.28);
    background: linear-gradient(180deg, rgba(163, 163, 163, 0.1), rgba(24, 24, 24, 0.6));
}
.podium-tile--bronze {
    border-color: rgba(180, 83, 9, 0.32);
    background: linear-gradient(180deg, rgba(180, 83, 9, 0.1), rgba(24, 24, 24, 0.6));
}

.tile-rank {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.2em;
    color: var(--color-wc-text-tertiary);
}
.podium-tile--gold .tile-rank { color: var(--color-wc-gold, #C8A769); }

.tile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.24);
    color: var(--color-wc-red-text, #F87171);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-size: 14px;
    letter-spacing: 0.04em;
}
.podium-tile--gold .tile-avatar {
    background: rgba(212, 160, 76, 0.12);
    border-color: rgba(212, 160, 76, 0.35);
    color: var(--color-wc-gold, #C8A769);
}

.tile-name {
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 600;
    color: var(--color-wc-text);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}
.tile-tickets {
    font-family: var(--font-display);
    font-size: 26px;
    color: var(--color-wc-green-text, #34D399);
    line-height: 1;
}
.tile-tickets-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.tile-clients {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
}

/* ── Rest list (4-5) ──────────────────────────────────────────────────── */
.podium-rest {
    list-style: none;
    margin: 0;
    padding: 8px 0 0;
    border-top: 1px solid var(--color-wc-border);
    display: flex;
    flex-direction: column;
}
.rest-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.rest-row:last-child { border-bottom: none; }
.rest-rank {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    flex-shrink: 0;
    width: 28px;
}
.rest-name {
    flex: 1;
    font-size: 12px;
    font-weight: 500;
    color: var(--color-wc-text-secondary);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.rest-tickets {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px;
    font-weight: 700;
    color: var(--color-wc-green-text, #34D399);
}
</style>
