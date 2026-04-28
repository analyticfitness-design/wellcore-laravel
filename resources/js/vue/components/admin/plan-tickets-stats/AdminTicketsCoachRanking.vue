<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  coaches: { type: Array, default: () => [] },
});

const isEmpty = computed(() => !props.coaches || props.coaches.length === 0);
const podium  = computed(() => (props.coaches || []).slice(0, 3));
const rest    = computed(() => (props.coaches || []).slice(3, 5));

function podiumClass(idx) {
  return ['podium-card--gold', 'podium-card--silver', 'podium-card--bronze'][idx] || '';
}
function rankLabel(idx) {
  return ['ORO', 'PLATA', 'BRONCE'][idx] || `#${idx + 1}`;
}
</script>

<template>
  <section class="ranking-card">
    <header class="ranking-head">
      <h2 class="ranking-title">RANKING COACHES</h2>
      <span class="ranking-sub">por tickets aprobados</span>
    </header>

    <!-- Empty state -->
    <div v-if="isEmpty" class="ranking-empty">
      <div class="ranking-empty-num">—</div>
      <p class="ranking-empty-msg">
        "Sin tickets completados en el periodo. El ranking se llenara con actividad del pipeline."
      </p>
      <RouterLink to="/admin/coaches" class="ranking-empty-cta">
        VER COACHES →
      </RouterLink>
    </div>

    <!-- Podium + rest -->
    <div v-else class="ranking-content">
      <div class="podium">
        <RouterLink
          v-for="(coach, idx) in podium"
          :key="coach.coach_id"
          to="/admin/coaches"
          class="podium-card"
          :class="podiumClass(idx)"
          :title="`Ver coaches — ${coach.name}`"
        >
          <span class="podium-medal">{{ rankLabel(idx) }}</span>
          <span class="podium-name">{{ coach.name }}</span>
          <span class="podium-count">{{ coach.approved_count }}</span>
          <span class="podium-count-label">APROBADOS</span>
          <span v-if="coach.rejected_count > 0" class="podium-rejection">
            {{ coach.rejection_pct }}% rechazo
          </span>
        </RouterLink>
      </div>

      <ul v-if="rest.length" class="rest-list">
        <li
          v-for="(coach, idx) in rest"
          :key="coach.coach_id"
          class="rest-row"
        >
          <span class="rest-rank">#{{ idx + 4 }}</span>
          <span class="rest-name">{{ coach.name }}</span>
          <span class="rest-count">{{ coach.approved_count }}</span>
          <span class="rest-pct" :class="coach.rejection_pct > 30 ? 'rest-pct--high' : ''">
            {{ coach.rejection_pct }}%
          </span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.ranking-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.ranking-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 8px;
}
.ranking-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.ranking-sub {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* Empty state */
.ranking-empty {
    padding: 18px 8px 14px;
    text-align: center;
}
.ranking-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.ranking-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55;
    margin: 0 0 16px;
    text-wrap: balance;
}
.ranking-empty-cta {
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
.ranking-empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

/* Podium */
.podium {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
}
.podium-card {
    border-radius: 10px;
    padding: 12px 10px;
    text-align: center;
    border: 1px solid var(--color-wc-border);
    background: rgba(24, 24, 24, 0.6);
    display: flex;
    flex-direction: column;
    gap: 3px;
    text-decoration: none;
    transition: border-color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
.podium-card:hover { border-color: var(--color-wc-border-2); background: rgba(28,28,28,0.8); }
.podium-card--gold   { border-color: rgba(212,160,76,0.3); background: linear-gradient(180deg, rgba(212,160,76,0.09), rgba(24,24,24,0.6)); }
.podium-card--silver { border-color: rgba(163,163,163,0.25); background: linear-gradient(180deg, rgba(163,163,163,0.07), rgba(24,24,24,0.6)); }
.podium-card--bronze { border-color: rgba(180,83,9,0.3); background: linear-gradient(180deg, rgba(180,83,9,0.07), rgba(24,24,24,0.6)); }

.podium-medal {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.podium-card--gold .podium-medal { color: var(--color-wc-gold, #C8A769); }

.podium-name {
    font-size: 11px;
    font-weight: 700;
    color: var(--color-wc-text);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    margin-top: 4px;
}
.podium-count {
    font-family: var(--font-display);
    font-size: 24px;
    color: var(--color-wc-green-text, #34D399);
    line-height: 1;
    margin-top: 4px;
}
.podium-count-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.podium-rejection {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
}

/* Rest list */
.rest-list {
    list-style: none;
    margin: 0;
    padding: 0;
    border-top: 1px solid var(--color-wc-border);
}
.rest-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
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
.rest-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 13px;
    font-weight: 700;
    color: var(--color-wc-green-text, #34D399);
    font-variant-numeric: tabular-nums;
}
.rest-pct {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    color: var(--color-wc-text-tertiary);
    width: 38px;
    text-align: right;
}
.rest-pct--high { color: var(--color-wc-red-text, #F87171); }
</style>
