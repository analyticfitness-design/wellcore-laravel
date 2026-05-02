<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
    coaches: { type: Array, default: () => [] },
});

const router = useRouter();

const isEmpty = computed(() => !props.coaches || props.coaches.length === 0);
const podium  = computed(() => (props.coaches || []).slice(0, 3));
const restList = computed(() => (props.coaches || []).slice(3, 5));

function podiumColor(idx) {
    if (idx === 0) return 'gold';
    if (idx === 1) return 'silver';
    return 'bronze';
}
function avatarInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase() || '?';
}
function formatScore(score) {
    return score !== null && score !== undefined ? Number(score).toFixed(1) : '—';
}
function goToCoach(coachId) {
    if (coachId) router.push(`/admin/coaches/${coachId}`);
}
</script>

<template>
  <section class="top-coaches-card">
    <header class="tc-head">
      <div class="tc-titles">
        <h2 class="tc-title">TOP COACHES</h2>
        <span class="tc-eyebrow">POR VOLUMEN DE MENSAJES</span>
      </div>
    </header>

    <div v-if="isEmpty" class="tc-empty">
      <div class="empty-num">—</div>
      <p class="empty-msg">
        "Sin mensajes registrados en el período. El ranking aparece cuando los coaches empiezan a comunicarse."
      </p>
    </div>

    <div v-else class="tc-content">
      <!-- Podio top 3 -->
      <div class="podium-grid">
        <article
          v-for="(coach, idx) in podium"
          :key="coach.coach_id || `pod-${idx}`"
          class="podium-tile"
          :class="`podium-tile--${podiumColor(idx)}`"
          role="button"
          tabindex="0"
          :aria-label="`Coach ${coach.name}, posición ${idx + 1}`"
          @click="goToCoach(coach.coach_id)"
          @keydown.enter="goToCoach(coach.coach_id)"
        >
          <span class="tile-rank">#{{ idx + 1 }}</span>
          <span class="tile-avatar">{{ avatarInitial(coach.name) }}</span>
          <div class="tile-name" :title="coach.name">{{ coach.name }}</div>
          <div class="tile-metric">{{ coach.msg_count.toLocaleString('es-CO') }}</div>
          <div class="tile-metric-label">MSGS</div>
          <div class="tile-score">
            <span class="score-label">SATISF.</span>
            <span class="score-val" :title="coach.avg_score === null ? 'Encuesta aún no implementada' : undefined">
              {{ formatScore(coach.avg_score) }}
            </span>
          </div>
        </article>
      </div>

      <!-- Posiciones 4-5 -->
      <ul v-if="restList.length" class="rest-list">
        <li
          v-for="(coach, idx) in restList"
          :key="coach.coach_id || `rest-${idx}`"
          class="rest-row"
          role="button"
          tabindex="0"
          @click="goToCoach(coach.coach_id)"
          @keydown.enter="goToCoach(coach.coach_id)"
        >
          <span class="rest-rank">#{{ idx + 4 }}</span>
          <span class="rest-name">{{ coach.name }}</span>
          <span class="rest-msgs">{{ coach.msg_count.toLocaleString('es-CO') }}</span>
          <span class="rest-score">{{ formatScore(coach.avg_score) }}</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.top-coaches-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17,17,17,0.7);
    padding: 18px;
}
.tc-head { margin-bottom: 14px; }
.tc-title {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase;
    color: var(--c-text); margin: 0 0 2px;
}
.tc-eyebrow {
    font-family: var(--font-display);
    font-size: 7px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.tc-empty { padding: 24px 8px; text-align: center; }
.empty-num {
    font-family: var(--font-display); font-size: 56px;
    color: var(--c-surface-2); letter-spacing: 0.1em;
    line-height: 1; margin-bottom: 12px; user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-size: 12px;
    color: var(--c-text-3); margin: 0; text-wrap: balance;
}

.podium-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}
.podium-tile {
    border-radius: 12px;
    padding: 12px 10px;
    display: flex; flex-direction: column; gap: 2px;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    min-width: 0;
}
.podium-tile--gold   { background: rgba(200,167,105,0.09); border: 1px solid rgba(200,167,105,0.28); }
.podium-tile--silver { background: rgba(148,163,184,0.07); border: 1px solid rgba(148,163,184,0.22); }
.podium-tile--bronze { background: rgba(180,120,60,0.07);  border: 1px solid rgba(180,120,60,0.22); }
.podium-tile:hover { border-color: rgba(255,255,255,0.18); }

.tile-rank {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.tile-avatar {
    font-family: var(--font-display);
    font-size: 22px; letter-spacing: 0.04em;
    color: var(--c-text-2);
    line-height: 1.1;
}
.podium-tile--gold   .tile-avatar { color: #C8A769; }
.podium-tile--silver .tile-avatar { color: #94A3B8; }
.podium-tile--bronze .tile-avatar { color: #B4783C; }

.tile-name {
    font-family: var(--font-sans);
    font-size: 11px; font-weight: 600;
    color: var(--c-text);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.tile-metric {
    font-family: var(--font-display);
    font-size: 22px; letter-spacing: 0.03em;
    color: var(--c-text); line-height: 1.1;
}
.tile-metric-label {
    font-family: var(--font-display);
    font-size: 7px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3); margin-bottom: 2px;
}
.tile-score {
    display: flex; gap: 4px; align-items: baseline;
}
.score-label {
    font-family: var(--font-display);
    font-size: 7px; letter-spacing: 1.0px; text-transform: uppercase;
    color: var(--c-text-3);
}
.score-val {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 600;
    color: var(--c-text-2);
    font-feature-settings: 'tnum' 1;
}

.rest-list {
    list-style: none; margin: 0; padding: 0;
    border-top: 1px solid var(--c-border);
}
.rest-row {
    display: grid;
    grid-template-columns: 24px 1fr 56px 36px;
    gap: 8px;
    align-items: center;
    padding: 9px 2px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    cursor: pointer;
    transition: background 0.12s var(--ease-out, ease);
    border-radius: 6px;
}
.rest-row:last-child { border-bottom: none; }
.rest-row:hover { background: rgba(255,255,255,0.02); }
.rest-rank {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.0px;
    color: var(--c-text-3);
}
.rest-name {
    font-family: var(--font-sans);
    font-size: 12px; font-weight: 500;
    color: var(--c-text-2);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.rest-msgs {
    font-family: var(--font-display);
    font-size: 12px; font-weight: 600;
    color: var(--c-text);
    text-align: right;
    font-feature-settings: 'tnum' 1;
}
.rest-score {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 500;
    color: var(--c-text-3);
    text-align: right;
    font-feature-settings: 'tnum' 1;
}
</style>
